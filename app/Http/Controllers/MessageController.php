<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\View\View;
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
        $this->middleware('auth');
    }

    /**
     * Mesaj geçmişini görüntüler
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();
        $limit = $request->get('limit', 10);
        $messages = $this->messageService->getLatestUserMessages($userId, $limit);

        return view('messages.index', [
            'messages' => $messages,
            'statistics' => $this->messageService->getMessageStatistics($userId)
        ]);
    }

    /**
     * Tarih aralığına göre mesajları görüntüler
     *
     * @param Request $request
     * @return View
     */
    public function dateRange(Request $request): View
    {
        $userId = auth()->id();
        $startDate = $request->get('start_date', Carbon::today()->startOfDay());
        $endDate = $request->get('end_date', Carbon::today()->endOfDay());

        $messages = $this->messageService->getMessagesByDateRange($startDate, $endDate, $userId);

        return view('messages.date-range', [
            'messages' => $messages,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    /**
     * Mesaj tipine göre mesajları görüntüler
     *
     * @param Request $request
     * @param string $type
     * @return View
     */
    public function byType(Request $request, string $type): View
    {
        $userId = auth()->id();
        $messages = $this->messageService->getMessagesByType($type, $userId);

        return view('messages.by-type', [
            'messages' => $messages,
            'type' => $type
        ]);
    }

    /**
     * Başarısız mesajları görüntüler
     *
     * @return View
     */
    public function failed(): View
    {
        $userId = auth()->id();
        $messages = $this->messageService->getFailedMessages($userId);

        return view('messages.failed', [
            'messages' => $messages
        ]);
    }

    /**
     * Bugünün mesajlarını görüntüler
     *
     * @return View
     */
    public function today(): View
    {
        $userId = auth()->id();
        $messages = $this->messageService->getTodaysMessages($userId);

        return view('messages.today', [
            'messages' => $messages
        ]);
    }

    /**
     * Mesaj istatistiklerini görüntüler
     *
     * @return View
     */
    public function statistics(): View
    {
        $userId = auth()->id();
        $statistics = $this->messageService->getMessageStatistics($userId);

        return view('messages.statistics', [
            'statistics' => $statistics
        ]);
    }
}
