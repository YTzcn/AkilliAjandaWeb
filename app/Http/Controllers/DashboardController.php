<?php

namespace App\Http\Controllers;

use App\Services\DashboardSummaryService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardSummaryService $dashboardSummaryService
    ) {}

    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $data = $this->dashboardSummaryService->buildPresentation($weekStart, $weekEnd);

        return view('dashboard', [
            'summary' => $data->summary,
            'upcomingEvents' => $data->upcomingEvents,
            'pendingTasks' => $data->pendingTasks,
        ]);
    }
}
