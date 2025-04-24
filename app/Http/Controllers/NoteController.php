<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Services\NoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoteController extends Controller
{
    /**
     * @var NoteService
     */
    protected $noteService;

    /**
     * NoteController constructor.
     *
     * @param NoteService $noteService
     */
    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the notes.
     *
     * @return View
     */
    public function index(): View
    {
        $notes = $this->noteService->getAllNotes();
        return view('notes.index', compact('notes'));
    }

    /**
     * Show the form for creating a new note.
     *
     * @return View
     */
    public function create(): View
    {
        return view('notes.create');
    }

    /**
     * Store a newly created note in storage.
     *
     * @param StoreNoteRequest $request
     * @return RedirectResponse
     */
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $this->noteService->createNote($request->validated());
        return redirect()->route('notes.index')->with('success', 'Not başarıyla oluşturuldu.');
    }

    /**
     * Display the specified note.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $note = $this->noteService->getNoteById($id);
        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified note.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $note = $this->noteService->getNoteById($id);
        return view('notes.edit', compact('note'));
    }

    /**
     * Update the specified note in storage.
     *
     * @param UpdateNoteRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateNoteRequest $request, int $id): RedirectResponse
    {
        $this->noteService->updateNote($id, $request->validated());
        return redirect()->route('notes.index')->with('success', 'Not başarıyla güncellendi.');
    }

    /**
     * Remove the specified note from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->noteService->deleteNote($id);
        return redirect()->route('notes.index')->with('success', 'Not başarıyla silindi.');
    }

    /**
     * Search for notes.
     *
     * @param Request $request
     * @return View
     */
    public function search(Request $request): View
    {
        $request->validate([
            'search' => 'required|string|min:3'
        ]);

        $notes = $this->noteService->searchNotes($request->search);
        return view('notes.search', compact('notes'));
    }
} 