<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTaskFiltersRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Support\TaskListFilterNormalizer;
use App\Services\CategoryService;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * TaskController constructor.
     */
    public function __construct(
        protected TaskService $taskService,
        protected CategoryService $categoryService
    ) {}

    /**
     * Display a listing of the tasks.
     *
     * @return View
     */
    public function index(IndexTaskFiltersRequest $request): View
    {
        $validated = $request->validated();
        $tasks = $this->taskService->getFilteredTasks($validated);
        $normalized = TaskListFilterNormalizer::normalize($validated);
        $filters = [
            'status' => $validated['status'] ?? '',
            'priority' => array_key_exists('priority', $validated) && $validated['priority'] !== ''
                ? (string) $validated['priority'] : '',
            'is_completed' => array_key_exists('is_completed', $validated) ? (string) $validated['is_completed'] : '',
            'due_from' => $validated['due_from'] ?? '',
            'due_to' => $validated['due_to'] ?? '',
            'due_date' => $validated['due_date'] ?? '',
            'category_id' => isset($validated['category_id']) ? (string) $validated['category_id'] : '',
            'q' => isset($validated['q']) ? (string) $validated['q'] : '',
            'sort' => $normalized['sort'],
            'dir' => $normalized['dir'],
        ];
        $categories = $this->categoryService->listForUser();

        return view('tasks.index', compact('tasks', 'categories', 'filters'));
    }

    /**
     * Show the form for creating a new task.
     *
     * @return View
     */
    public function create(): View
    {
        $categories = $this->categoryService->listForUser();

        return view('tasks.create', compact('categories'));
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
        $categories = $this->categoryService->listForUser();

        return view('tasks.edit', compact('task', 'categories'));
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