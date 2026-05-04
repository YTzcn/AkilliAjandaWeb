<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTaskFiltersRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Services\CategoryService;
use App\Services\TaskService;
use App\Support\TaskListFilterNormalizer;
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
     */
    public function index(IndexTaskFiltersRequest $request): View
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 25);
        if ($perPage < 5) {
            $perPage = 5;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }
        $tasks = $this->taskService->getFilteredTasksPaginated($validated, $perPage);
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
            'per_page' => (string) $perPage,
        ];
        $categories = $this->categoryService->listForUser();

        return view('tasks.index', [
            'tasks' => $tasks,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): View
    {
        $categories = $this->categoryService->listForUser();

        return view('tasks.create', compact('categories'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $this->taskService->createTask($request->validated());

        return redirect()->route('tasks.index')->with('success', 'Görev başarıyla oluşturuldu.');
    }

    /**
     * Display the specified task.
     */
    public function show(int $id): View
    {
        $task = $this->taskService->getTaskById($id);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(int $id): View
    {
        $task = $this->taskService->getTaskById($id);
        $categories = $this->categoryService->listForUser();

        return view('tasks.edit', compact('task', 'categories'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, int $id): RedirectResponse
    {
        $this->taskService->updateTask($id, $request->validated());

        return redirect()->route('tasks.index')->with('success', 'Görev başarıyla güncellendi.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->taskService->deleteTask($id);

        return redirect()->route('tasks.index')->with('success', 'Görev başarıyla silindi.');
    }

    /**
     * Mark the task as completed.
     */
    public function complete(int $id): RedirectResponse
    {
        $this->taskService->markAsCompleted($id);

        return redirect()->route('tasks.index')->with('success', 'Görev tamamlandı olarak işaretlendi.');
    }

    /**
     * Mark the task as pending.
     */
    public function pending(int $id): RedirectResponse
    {
        $this->taskService->markAsPending($id);

        return redirect()->route('tasks.index')->with('success', 'Görev beklemede olarak işaretlendi.');
    }
}
