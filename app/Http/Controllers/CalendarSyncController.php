<?php

namespace App\Http\Controllers;

use App\Services\CalendarSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarSyncController extends Controller
{
    protected $calendarSyncService;

    /**
     * CalendarSyncController constructor.
     *
     * @param CalendarSyncService $calendarSyncService
     */
    public function __construct(CalendarSyncService $calendarSyncService)
    {
        $this->calendarSyncService = $calendarSyncService;
    }

    /**
     * Takvim senkronizasyon sayfasını göster
     *
     * @return View
     */
    public function syncPage(): View
    {
        $user = Auth::user();
        $isConnectedToGoogle = $this->calendarSyncService->isUserConnectedToGoogle($user);

        return view('calendar.sync', [
            'isConnectedToGoogle' => $isConnectedToGoogle
        ]);
    }

    /**
     * Etkinlikleri Google Takvim'e senkronize et
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncWithGoogle()
    {
        $user = Auth::user();
        $result = $this->calendarSyncService->syncEventsToGoogle($user);

        if ($result['success']) {
            return redirect()->route('calendar.sync')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('calendar.sync')
                ->with('error', $result['message']);
        }
    }

    /**
     * Google Takvim'den etkinlikleri içe aktar
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importFromGoogle(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $user = Auth::user();
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : null;
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : null;

        $result = $this->calendarSyncService->importEventsFromGoogle($user, $startDate, $endDate);

        if ($result['success']) {
            return redirect()->route('calendar.sync')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('calendar.sync')
                ->with('error', $result['message']);
        }
    }

    /**
     * Google Takvim ile senkronizasyon ayarları sayfasını göster
     *
     * @return View
     */
    public function settings(): View
    {
        $user = Auth::user();
        $isConnectedToGoogle = $this->calendarSyncService->isUserConnectedToGoogle($user);

        return view('calendar.settings', [
            'isConnectedToGoogle' => $isConnectedToGoogle
        ]);
    }
}
