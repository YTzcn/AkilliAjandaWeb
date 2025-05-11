<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MessageController extends Controller
{
    /**
     * @var MessageService
     */
    protected MessageService $messageService;

    /**
     * MessageController constructor.
     *
     * @param MessageService $messageService
     */
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Kullanıcının mesaj geçmişini listeler
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $limit = $request->get('limit', 10);

        try {
            $messages = $this->messageService->getLatestUserMessages($userId, $limit);

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mesajlar alınırken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Belirli bir tarih aralığındaki mesajları listeler
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getByDateRange(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date'
            ]);

            $userId = auth()->id();
            $messages = $this->messageService->getMessagesByDateRange(
                $request->get('start_date'),
                $request->get('end_date'),
                $userId
            );

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mesajlar alınırken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Belirli bir tipteki mesajları listeler
     *
     * @param Request $request
     * @param string $type
     * @return JsonResponse
     */
    public function getByType(Request $request, string $type): JsonResponse
    {
        try {
            $userId = auth()->id();
            $messages = $this->messageService->getMessagesByType($type, $userId);

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mesajlar alınırken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Başarısız mesajları listeler
     *
     * @return JsonResponse
     */
    public function getFailedMessages(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $messages = $this->messageService->getFailedMessages($userId);

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Başarısız mesajlar alınırken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bugünün mesajlarını listeler
     *
     * @return JsonResponse
     */
    public function getTodaysMessages(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $messages = $this->messageService->getTodaysMessages($userId);

            return response()->json([
                'status' => 'success',
                'data' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bugünün mesajları alınırken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mesaj istatistiklerini getirir
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $statistics = $this->messageService->getMessageStatistics($userId);

            return response()->json([
                'status' => 'success',
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'İstatistikler alınırken bir hata oluştu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
