<?php

namespace App\Http\Controllers;

use App\Services\EventService;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
        $today = Carbon::today();
        $oneWeekLater = Carbon::today()->addWeek();

        $upcomingEvents = $this->eventService->getEventsForDateRange(
            $today->format('Y-m-d H:i:s'),
            $oneWeekLater->format('Y-m-d H:i:s')
        );
        
        $pendingTasks = $this->taskService->getPendingTasks();

        return view('dashboard', compact('upcomingEvents', 'pendingTasks'));
    }
} 