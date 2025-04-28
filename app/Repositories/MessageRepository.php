<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class MessageRepository extends BaseRepository
{
    /**
     * MessageRepository constructor.
     *
     * @param Message $model
     */
    public function __construct(Message $model)
    {
        parent::__construct($model);
    }

    /**
     * Belirli bir tarih aralığındaki mesajları getirir
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $userId
     * @return Collection
     */
    public function getMessagesByDateRange(string $startDate, string $endDate, ?int $userId = null): Collection
    {
        $query = $this->model->whereBetween('created_at', [$startDate, $endDate]);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Belirli bir kullanıcının son mesajlarını getirir
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getLatestMessagesByUser(int $userId, int $limit = 10): Collection
    {
        return $this->model->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
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
        $query = $this->model->where('message_type', $type);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Başarısız mesajları getirir
     *
     * @param int|null $userId
     * @return Collection
     */
    public function getFailedMessages(?int $userId = null): Collection
    {
        $query = $this->model->where('is_successful', false);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Bugünün mesajlarını getirir
     *
     * @param int|null $userId
     * @return Collection
     */
    public function getTodaysMessages(?int $userId = null): Collection
    {
        $today = Carbon::today();
        $query = $this->model->whereDate('created_at', $today);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
} 