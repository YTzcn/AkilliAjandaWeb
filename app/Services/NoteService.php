<?php

namespace App\Services;

use App\Models\Note;
use App\Repositories\NoteRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class NoteService
{
    /**
     * @var NoteRepository
     */
    protected $noteRepository;

    /**
     * NoteService constructor.
     *
     * @param NoteRepository $noteRepository
     */
    public function __construct(NoteRepository $noteRepository)
    {
        $this->noteRepository = $noteRepository;
    }

    /**
     * Get all notes for the authenticated user.
     *
     * @return Collection
     */
    public function getAllNotes(): Collection
    {
        return $this->noteRepository->allByUser(Auth::id());
    }

    /**
     * Create a new note.
     *
     * @param array $data
     * @return Note
     */
    public function createNote(array $data): Note
    {
        $data['user_id'] = Auth::id();
        return $this->noteRepository->create($data);
    }

    /**
     * Update an existing note.
     *
     * @param int $noteId
     * @param array $data
     * @return Note|null
     */
    public function updateNote(int $noteId, array $data): ?Note
    {
        return $this->noteRepository->update($noteId, $data);
    }

    /**
     * Delete a note.
     *
     * @param int $noteId
     * @return bool
     */
    public function deleteNote(int $noteId): bool
    {
        return $this->noteRepository->deleteById($noteId);
    }

    /**
     * Get a specific note by ID.
     *
     * @param int $noteId
     * @return Note|null
     */
    public function getNoteById(int $noteId): ?Note
    {
        return $this->noteRepository->findById($noteId);
    }

    /**
     * Search notes by content or title.
     *
     * @param string $searchTerm
     * @return Collection
     */
    public function searchNotes(string $searchTerm): Collection
    {
        return $this->noteRepository->searchNotes(Auth::id(), $searchTerm);
    }
} 