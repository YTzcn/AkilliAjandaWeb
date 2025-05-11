<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use App\Services\CalendarSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Event;

/**
 * @OA\Tag(
 *     name="Google Takvim",
 *     description="Google Takvim entegrasyonu işlemleri"
 * )
 */
class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;
    protected $calendarSyncService;

    /**
     * GoogleCalendarController constructor.
     *
     * @param GoogleCalendarService $googleCalendarService
     * @param CalendarSyncService $calendarSyncService
     */
    public function __construct(
        GoogleCalendarService $googleCalendarService,
        CalendarSyncService $calendarSyncService
    ) {
        $this->googleCalendarService = $googleCalendarService;
        $this->calendarSyncService = $calendarSyncService;
    }

    /**
     * Kullanıcının Google ile bağlantı durumunu kontrol eder
     * 
     * @OA\Get(
     *     path="/api/google/connection-status",
     *     summary="Google Takvim bağlantı durumunu kontrol eder",
     *     description="Kullanıcının Google Takvim bağlantı durumunu döner",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="connected", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function connectionStatus()
    {
        $user = Auth::user();
        $isConnected = $this->calendarSyncService->isUserConnectedToGoogle($user);

        return response()->json([
            'status' => 'success',
            'connected' => $isConnected
        ]);
    }

    /**
     * Google yetkilendirme URL'sini döner
     * 
     * @OA\Get(
     *     path="/api/google/auth-url",
     *     summary="Google yetkilendirme URL'sini döner",
     *     description="Google Takvim entegrasyonu için yetkilendirme URL'sini döner",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Yetkilendirme URL'si",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="auth_url", type="string", example="https://accounts.google.com/o/oauth2/auth?..."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google yetkilendirme başlatılamadı")
     *         )
     *     )
     * )
     */
    public function getAuthUrl()
    {
        try {
            $user = Auth::user();
            $this->googleCalendarService->setupClient($user);
            $authUrl = $this->calendarSyncService->getGoogleAuthUrl();
            
            return response()->json([
                'status' => 'success',
                'auth_url' => $authUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Google yetkilendirme hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Google yetkilendirme başlatılamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Google'dan dönen yetkilendirme kodunu işler
     * 
     * @OA\Post(
     *     path="/api/google/callback",
     *     summary="Google'dan dönen yetkilendirme kodunu işler",
     *     description="Google'dan dönen yetkilendirme kodunu kullanarak token alır ve kaydeder",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="4/0AWgavde..."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Google Takvim başarıyla bağlandı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Geçersiz istek",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Yetkilendirme kodu gereklidir")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google yetkilendirme tamamlanamadı")
     *         )
     *     )
     * )
     */
    public function handleCallback(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
            ]);

            $user = Auth::user();
            $this->googleCalendarService->setupClient($user);
            $token = $this->googleCalendarService->getAccessToken($request->code);

            $user->google_token = json_encode($token);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Google Takvim başarıyla bağlandı'
            ]);
        } catch (\Exception $e) {
            Log::error('Google callback hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Google yetkilendirme tamamlanamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Google bağlantısını kaldırır
     * 
     * @OA\Post(
     *     path="/api/google/disconnect",
     *     summary="Google bağlantısını kaldırır",
     *     description="Kullanıcının Google Takvim bağlantısını kaldırır",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Google Takvim bağlantısı kaldırıldı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google Takvim bağlantısı kaldırılamadı")
     *         )
     *     )
     * )
     */
    public function disconnect()
    {
        try {
            $user = Auth::user();
            $user->google_token = null;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Google Takvim bağlantısı kaldırıldı'
            ]);
        } catch (\Exception $e) {
            Log::error('Google bağlantısı kaldırma hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Google Takvim bağlantısı kaldırılamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Google Takvimden etkinlikleri listeler
     * 
     * @OA\Get(
     *     path="/api/google/events",
     *     summary="Google Takvimden etkinlikleri listeler",
     *     description="Belirli tarih aralığındaki Google Takvim etkinliklerini listeler",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Başlangıç tarihi (Y-m-d formatında)",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2023-10-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Bitiş tarihi (Y-m-d formatında)",
     *         required=true,
     *         @OA\Schema(type="string", format="date", example="2023-10-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="events", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Geçersiz istek",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google Takvim bağlantısı bulunamadı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Etkinlikler listelenirken bir hata oluştu")
     *         )
     *     )
     * )
     */
    public function listEvents(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            ]);

            $user = Auth::user();
            $isConnected = $this->googleCalendarService->setupClient($user);

            if (!$isConnected) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Google Takvim bağlantısı bulunamadı'
                ], 400);
            }

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            
            $events = $this->googleCalendarService->listEvents($startDate, $endDate);
            
            // API yanıtı için Google Calendar etkinliklerini düzenle
            $formattedEvents = collect($events)->map(function ($event) {
                $startDateTime = Carbon::parse($event->start->dateTime ?? $event->start->date);
                $endDateTime = Carbon::parse($event->end->dateTime ?? $event->end->date);
                
                return [
                    'id' => $event->getId(),
                    'title' => $event->getSummary(),
                    'description' => $event->getDescription(),
                    'start_date' => $startDateTime->toDateTimeString(),
                    'end_date' => $endDateTime->toDateTimeString(),
                    'location' => $event->getLocation(),
                    'all_day' => !isset($event->start->dateTime)
                ];
            });

            return response()->json([
                'status' => 'success',
                'events' => $formattedEvents
            ]);
        } catch (\Exception $e) {
            Log::error('Google Takvim etkinlikleri listeleme hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Etkinlikler listelenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Google Takvimden etkinlikleri içe aktarır
     * 
     * @OA\Post(
     *     path="/api/google/import-events",
     *     summary="Google Takvimden etkinlikleri içe aktarır",
     *     description="Google Takvimden etkinlikleri belirli tarih aralığında içe aktarır",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"start_date", "end_date"},
     *             @OA\Property(property="start_date", type="string", format="date", example="2023-10-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2023-10-31"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Etkinlikler başarıyla içe aktarıldı"),
     *             @OA\Property(property="imported_count", type="integer", example=5),
     *             @OA\Property(property="events", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Geçersiz istek",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google Takvim bağlantısı bulunamadı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Etkinlikler içe aktarılırken bir hata oluştu")
     *         )
     *     )
     * )
     */
    public function importEvents(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'date_format:Y-m-d',
                'end_date' => 'date_format:Y-m-d|after_or_equal:start_date',
            ]);

            $user = Auth::user();
            $startDate = $request->has('start_date') ? Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->has('end_date') ? Carbon::parse($request->end_date)->endOfDay() : null;
            
            $result = $this->calendarSyncService->importEventsFromGoogle($user, $startDate, $endDate);
            
            if (!$result['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message']
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'imported_count' => $result['imported'],
                'events' => $result['events']
            ]);
        } catch (\Exception $e) {
            Log::error('Google Takvim etkinlikleri içe aktarma hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Etkinlikler içe aktarılırken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Etkinliği Google Takvim'e senkronize eder
     * 
     * @OA\Post(
     *     path="/api/google/sync-event",
     *     summary="Etkinliği Google Takvim'e senkronize eder",
     *     description="Belirtilen etkinliği Google Takvim'e senkronize eder veya günceller",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"event_id"},
     *             @OA\Property(property="event_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Etkinlik başarıyla Google Takvim'e senkronize edildi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Geçersiz istek",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google Takvim bağlantısı bulunamadı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kaynak bulunamadı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Etkinlik bulunamadı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Etkinlik senkronize edilirken bir hata oluştu")
     *         )
     *     )
     * )
     */
    public function syncEventToGoogle(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'required|integer|exists:events,id',
            ]);

            $user = Auth::user();
            $isConnected = $this->googleCalendarService->setupClient($user);

            if (!$isConnected) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Google Takvim bağlantısı bulunamadı'
                ], 400);
            }

            $event = Event::where('id', $request->event_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$event) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Etkinlik bulunamadı'
                ], 404);
            }

            $success = $this->googleCalendarService->syncEventToGoogle($event);

            if (!$success) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Etkinlik senkronize edilirken bir hata oluştu'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Etkinlik başarıyla Google Takvim\'e senkronize edildi',
                'event' => $event
            ]);
        } catch (\Exception $e) {
            Log::error('Google Takvim etkinlik senkronizasyon hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Etkinlik senkronize edilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Etkinliği Google Takvim'den kaldırır
     * 
     * @OA\Delete(
     *     path="/api/google/remove-event/{event_id}",
     *     summary="Etkinliği Google Takvim'den kaldırır",
     *     description="Belirtilen etkinliği Google Takvim'den kaldırır",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         description="Etkinlik ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Etkinlik başarıyla Google Takvim'den kaldırıldı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Geçersiz istek",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google Takvim bağlantısı bulunamadı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kaynak bulunamadı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Etkinlik bulunamadı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Etkinlik kaldırılırken bir hata oluştu")
     *         )
     *     )
     * )
     */
    public function removeEventFromGoogle($eventId)
    {
        try {
            $user = Auth::user();
            $event = Event::where('id', $eventId)
                ->where('user_id', $user->id)
                ->first();

            if (!$event) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Etkinlik bulunamadı'
                ], 404);
            }

            if (!$event->google_event_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bu etkinlik Google Takvim ile senkronize edilmemiş'
                ], 400);
            }

            $success = $this->calendarSyncService->deleteEventFromGoogle($event);

            if (!$success) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Etkinlik Google Takvim\'den kaldırılırken bir hata oluştu'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Etkinlik başarıyla Google Takvim\'den kaldırıldı',
                'event' => $event
            ]);
        } catch (\Exception $e) {
            Log::error('Google Takvim etkinlik silme hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Etkinlik Google Takvim\'den kaldırılırken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tüm etkinlikleri Google Takvim'e senkronize eder
     * 
     * @OA\Post(
     *     path="/api/google/sync-all-events",
     *     summary="Tüm etkinlikleri Google Takvim'e senkronize eder",
     *     description="Kullanıcının tüm etkinliklerini Google Takvim'e senkronize eder",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Etkinlikler başarıyla senkronize edildi"),
     *             @OA\Property(property="synced_count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Geçersiz istek",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google Takvim bağlantısı bulunamadı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Etkinlikler senkronize edilirken bir hata oluştu")
     *         )
     *     )
     * )
     */
    public function syncAllEventsToGoogle()
    {
        try {
            $user = Auth::user();
            $result = $this->calendarSyncService->syncEventsToGoogle($user);

            if (!$result['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'] 
                ], 400);
            }

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'synced_count' => $result['synced']
            ]);
        } catch (\Exception $e) {
            Log::error('Google Takvim etkinlik toplu senkronizasyon hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Etkinlikler senkronize edilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Görevleri Google Görevler listesine senkronize eder
     * 
     * @OA\Post(
     *     path="/api/google/sync-tasks",
     *     summary="Görevleri Google Görevler listesine senkronize eder",
     *     description="Kullanıcının görevlerini Google Görevler listesine senkronize eder",
     *     tags={"Google Takvim"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Görevler başarıyla senkronize edildi"),
     *             @OA\Property(property="synced_count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Geçersiz istek",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Google bağlantısı bulunamadı")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Görevler senkronize edilirken bir hata oluştu")
     *         )
     *     )
     * )
     */
    public function syncTasksToGoogle()
    {
        try {
            $user = Auth::user();
            $result = $this->calendarSyncService->syncTasksToGoogle($user);

            return response()->json([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'synced_count' => $result['synced']
            ], $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Google Görevler senkronizasyon hatası: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Görevler senkronize edilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
} 