<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use App\Services\NoteService;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * @var EventService
     */
    protected $eventService;

    /**
     * @var TaskService
     */
    protected $taskService;

    /**
     * @var NoteService
     */
    protected $noteService;

    /**
     * DashboardController constructor.
     *
     * @param EventService $eventService
     * @param TaskService $taskService
     * @param NoteService $noteService
     */
    public function __construct(
        EventService $eventService,
        TaskService $taskService,
        NoteService $noteService
    ) {
        $this->eventService = $eventService;
        $this->taskService = $taskService;
        $this->noteService = $noteService;
    }

    /**
     * Display the dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $today = Carbon::today()->format('Y-m-d');
        $oneWeekLater = Carbon::today()->addWeek()->format('Y-m-d');

        $upcomingEvents = $this->eventService->getEventsForDateRange($today, $oneWeekLater);
        $pendingTasks = $this->taskService->getPendingTasks();
        $recentNotes = $this->noteService->getAllNotes()->sortByDesc('created_at')->take(5);

        return view('dashboard', compact('upcomingEvents', 'pendingTasks', 'recentNotes'));
    }
} 