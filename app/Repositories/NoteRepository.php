<?php

namespace App\Repositories;

use App\Models\Note;
use Illuminate\Database\Eloquent\Collection;

class NoteRepository extends BaseRepository
{
    /**
     * NoteRepository constructor.
     *
     * @param Note $model
     */
    public function __construct(Note $model)
    {
        parent::__construct($model);
    }

    /**
     * Search notes by title or content.
     *
     * @param int $userId
     * @param string $searchTerm
     * @return Collection
     */
    public function searchNotes(int $userId, string $searchTerm): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
} 