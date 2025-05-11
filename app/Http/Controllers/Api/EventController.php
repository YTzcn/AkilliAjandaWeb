<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CalendarEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Etkinlikler",
 *     description="Etkinlik yönetimi için API endpoint'leri"
 * )
 */
class EventController extends Controller
{
    protected $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="Etkinlikleri listele",
     *     tags={"Etkinlikler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="start",
     *         in="query",
     *         description="Başlangıç tarihi",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end",
     *         in="query",
     *         description="Bitiş tarihi",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $events = $this->service->getCalendarEvents([
            'start' => $request->input('start'),
            'end' => $request->input('end')
        ]);

        return response()->json($events);
    }

    /**
     * @OA\Post(
     *     path="/api/events",
     *     summary="Yeni etkinlik oluştur",
     *     tags={"Etkinlikler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CalendarEventRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="event", type="object")
     *         )
     *     )
     * )
     */
    public function store(CalendarEventRequest $request): JsonResponse
    {
        $event = $this->service->handleCalendarCreate($request->validated());

        return response()->json([
            'message' => 'Etkinlik başarıyla oluşturuldu.',
            'event' => $this->service->formatForCalendar($event)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/events/{event}",
     *     summary="Etkinlik detaylarını getir",
     *     tags={"Etkinlikler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Etkinlik ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function show(Event $event): JsonResponse
    {
        return response()->json([
            'event' => $this->service->formatForCalendar($event)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/events/{event}",
     *     summary="Etkinlik güncelle",
     *     tags={"Etkinlikler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Etkinlik ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CalendarEventRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="event", type="object")
     *         )
     *     )
     * )
     */
    public function update(CalendarEventRequest $request, Event $event): JsonResponse
    {
        $event = $this->service->handleCalendarUpdate($event, $request->validated());

        return response()->json([
            'message' => 'Etkinlik başarıyla güncellendi.',
            'event' => $this->service->formatForCalendar($event)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/events/{event}",
     *     summary="Etkinlik sil",
     *     tags={"Etkinlikler"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="event",
     *         in="path",
     *         description="Etkinlik ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Event $event): JsonResponse
    {
        $this->service->handleCalendarDelete($event);

        return response()->json([
            'message' => 'Etkinlik başarıyla silindi.'
        ]);
    }    
} 