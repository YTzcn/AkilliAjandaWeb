<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalendarSyncService
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Kullanıcının Google ile bağlantı durumunu kontrol et
     *
     * @param User $user
     * @return bool
     */
    public function isUserConnectedToGoogle(User $user): bool
    {
        return !empty($user->google_token);
    }

    /**
     * Google istemcisini hazırla
     *
     * @param User $user
     * @return bool
     */
    public function setupGoogleClient(User $user): bool
    {
        return $this->googleCalendarService->setupClient($user);
    }

    /**
     * Google yetkilendirme URL'sini al
     *
     * @return string
     */
    public function getGoogleAuthUrl(): string
    {
        return $this->googleCalendarService->createAuthUrl();
    }

    /**
     * Tüm etkinlikleri Google Takvim'e senkronize et
     *
     * @param User $user
     * @return array
     */
    public function syncEventsToGoogle(User $user): array
    {
        if (!$this->setupGoogleClient($user)) {
            return [
                'success' => false,
                'message' => 'Google bağlantısı kurulamadı. Lütfen hesabınızı yeniden bağlayın.',
                'synced' => 0
            ];
        }

        try {
            $events = Event::where('user_id', $user->id)
                           ->where(function ($query) {
                                $query->where('synced_to_google', false)
                                      ->orWhereNull('google_event_id');
                            })
                           ->get();

            $syncedCount = 0;

            foreach ($events as $event) {
                $success = $this->googleCalendarService->syncEventToGoogle($event);
                if ($success) {
                    $syncedCount++;
                }
            }

            return [
                'success' => true,
                'message' => $syncedCount . ' etkinlik Google Takvim\'e senkronize edildi.',
                'synced' => $syncedCount
            ];
        } catch (\Exception $e) {
            Log::error('Google Takvime Senkronizasyon Hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Senkronizasyon sırasında bir hata oluştu: ' . $e->getMessage(),
                'synced' => 0
            ];
        }
    }

    /**
     * Google Takvim'den etkinlikleri içe aktar
     *
     * @param User $user
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function importEventsFromGoogle(User $user, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        if (!$this->setupGoogleClient($user)) {
            return [
                'success' => false,
                'message' => 'Google bağlantısı kurulamadı. Lütfen hesabınızı yeniden bağlayın.',
                'imported' => 0
            ];
        }

        try {
            // Varsayılan tarih aralığı: Son 30 gün ve gelecek 60 gün
            $startDate = $startDate ?? Carbon::now()->subDays(30);
            $endDate = $endDate ?? Carbon::now()->addDays(60);

            $importedEvents = $this->googleCalendarService->importEventsFromGoogle($startDate, $endDate);

            return [
                'success' => true,
                'message' => count($importedEvents) . ' etkinlik Google Takvim\'den içe aktarıldı.',
                'imported' => count($importedEvents),
                'events' => $importedEvents
            ];
        } catch (\Exception $e) {
            Log::error('Google Takvimden İçe Aktarma Hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'İçe aktarma sırasında bir hata oluştu: ' . $e->getMessage(),
                'imported' => 0
            ];
        }
    }

    /**
     * Silinen etkinlikleri Google Takvim'den kaldır
     *
     * @param Event $event
     * @return bool
     */
    public function deleteEventFromGoogle(Event $event): bool
    {
        $user = $event->user;
        if (!$this->setupGoogleClient($user)) {
            return false;
        }

        return $this->googleCalendarService->deleteEventFromGoogle($event);
    }

    /**
     * Görevleri Google Görevler listesine senkronize et
     * Not: Spatie/laravel-google-calendar paketi şu anda Tasks API için destek sağlamıyor
     * Bu yüzden bu kısmı sadece yapı olarak tanımlıyoruz
     *
     * @param User $user
     * @return array
     */
    public function syncTasksToGoogle(User $user): array
    {
        // Google Tasks API için ayrı bir servis sınıfı gerekebilir
        return [
            'success' => false,
            'message' => 'Google Tasks entegrasyonu şu anda mevcut değil.',
            'synced' => 0
        ];
    }
} 