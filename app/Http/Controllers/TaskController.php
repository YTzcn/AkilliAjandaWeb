<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * @var TaskService
     */
    protected $taskService;

    /**
     * TaskController constructor.
     *
     * @param TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the tasks.
     *
     * @return View
     */
    public function index(): View
    {
        $pendingTasks = $this->taskService->getPendingTasks();
        $completedTasks = $this->taskService->getCompletedTasks();
        
        return view('tasks.index', compact('pendingTasks', 'completedTasks'));
    }

    /**
     * Show the form for creating a new task.
     *
     * @return View
     */
    public function create(): View
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created task in storage.
     *
     * @param StoreTaskRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $this->taskService->createTask($request->validated());
        return redirect()->route('tasks.index')->with('success', 'Görev başarıyla oluşturuldu.');
    }

    /**
     * Display the specified task.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $task = $this->taskService->getTaskById($id);
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $task = $this->taskService->getTaskById($id);
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified task in storage.
     *
     * @param UpdateTaskRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateTaskRequest $request, int $id): RedirectResponse
    {
        $this->taskService->updateTask($id, $request->validated());
        return redirect()->route('tasks.index')->with('success', 'Görev başarıyla güncellendi.');
    }

    /**
     * Remove the specified task from storage.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->taskService->deleteTask($id);
        return redirect()->route('tasks.index')->with('success', 'Görev başarıyla silindi.');
    }

    /**
     * Mark the task as completed.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function complete(int $id): RedirectResponse
    {
        $this->taskService->markAsCompleted($id);
        return redirect()->route('tasks.index')->with('success', 'Görev tamamlandı olarak işaretlendi.');
    }

    /**
     * Mark the task as pending.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function pending(int $id): RedirectResponse
    {
        $this->taskService->markAsPending($id);
        return redirect()->route('tasks.index')->with('success', 'Görev beklemede olarak işaretlendi.');
    }
} 