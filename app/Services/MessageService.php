<?php

namespace App\Services;

use App\Repositories\MessageRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MessageService
{
    /**
     * @var MessageRepository
     */
    protected MessageRepository $messageRepository;

    /**
     * MessageService constructor.
     *
     * @param MessageRepository $messageRepository
     */
    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * Yeni bir mesaj kaydı oluşturur
     *
     * @param array $data
     * @return Model|null
     */
    public function createMessage(array $data): ?Model
    {
        return $this->messageRepository->create($data);
    }

    /**
     * Belirli bir tarih aralığındaki mesajları getirir
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $userId
     * @return Collection
     */
    public function getMessagesByDateRange(?string $startDate = null, ?string $endDate = null, ?int $userId = null): Collection
    {
        $startDate = $startDate ?? Carbon::today()->startOfDay();
        $endDate = $endDate ?? Carbon::today()->endOfDay();

        return $this->messageRepository->getMessagesByDateRange($startDate, $endDate, $userId);
    }

    /**
     * Kullanıcının son mesajlarını getirir
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getLatestUserMessages(int $userId, int $limit = 10): Collection
    {
        return $this->messageRepository->getLatestMessagesByUser($userId, $limit);
    }

    /**
     * Belirli bir mesaj tipine göre mesajları getirir
     *
     * @param string $type
     * @param int|null $userId
     * @return Collection
     */
    public function getMessagesByType(string $type, ?int $userId = null): Collection
    {
        return $this->messageRepository->getMessagesByType($type, $userId);
    }

    /**
     * Başarısız mesajları getirir
     *
     * @param int|null $userId
     * @return Collection
     */
    public function getFailedMessages(?int $userId = null): Collection
    {
        return $this->messageRepository->getFailedMessages($userId);
    }

    /**
     * Bugünün mesajlarını getirir
     *
     * @param int|null $userId
     * @return Collection
     */
    public function getTodaysMessages(?int $userId = null): Collection
    {
        return $this->messageRepository->getTodaysMessages($userId);
    }

    /**
     * Mesaj istatistiklerini getirir
     *
     * @param int|null $userId
     * @return array
     */
    public function getMessageStatistics(?int $userId = null): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        $todayMessages = $this->messageRepository->getMessagesByDateRange(
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay(),
            $userId
        );

        $weeklyMessages = $this->messageRepository->getMessagesByDateRange(
            $startOfWeek,
            Carbon::now(),
            $userId
        );

        $monthlyMessages = $this->messageRepository->getMessagesByDateRange(
            $startOfMonth,
            Carbon::now(),
            $userId
        );

        $failedMessages = $this->messageRepository->getFailedMessages($userId);

        return [
            'today_count' => $todayMessages->count(),
            'weekly_count' => $weeklyMessages->count(),
            'monthly_count' => $monthlyMessages->count(),
            'failed_count' => $failedMessages->count(),
            'message_types' => $monthlyMessages->groupBy('message_type')
                ->map(fn($messages) => $messages->count()),
            'success_rate' => $monthlyMessages->isEmpty() ? 0 : 
                ($monthlyMessages->where('is_successful', true)->count() / $monthlyMessages->count()) * 100
        ];
    }
} 