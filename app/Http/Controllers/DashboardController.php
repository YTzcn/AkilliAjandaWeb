<?php

namespace App\Http\Controllers;

use App\Services\EventService;
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
     * DashboardController constructor.
     *
     * @param EventService $eventService
     * @param TaskService $taskService
     */
    public function __construct(
        EventService $eventService,
        TaskService $taskService
    ) {
        $this->eventService = $eventService;
        $this->taskService = $taskService;
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

        return view('dashboard', compact('upcomingEvents', 'pendingTasks'));
    }
} 