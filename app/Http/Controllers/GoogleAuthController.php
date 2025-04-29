<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use App\Services\CalendarSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    protected $googleCalendarService;
    protected $calendarSyncService;

    /**
     * GoogleAuthController constructor.
     *
     * @param GoogleCalendarService $googleCalendarService
     * @param CalendarSyncService $calendarSyncService
     */
    public function __construct(
        GoogleCalendarService $googleCalendarService,
        CalendarSyncService $calendarSyncService
    ) {
        $this->googleCalendarService = $googleCalendarService;
        $this->calendarSyncService = $calendarSyncService;
    }

    /**
     * Google yetkilendirme sayfasına yönlendir
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        try {
            $user = Auth::user();
            $this->googleCalendarService->setupClient($user);
            $authUrl = $this->googleCalendarService->createAuthUrl();
            
            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            Log::error('Google yetkilendirme hatası: ' . $e->getMessage());
            return redirect()->route('profile.edit')
                ->with('error', 'Google yetkilendirme başlatılamadı: ' . $e->getMessage());
        }
    }

    /**
     * Google'dan dönen callback'i işle
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            if ($request->has('error')) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Google yetkilendirme iptal edildi: ' . $request->get('error'));
            }

            if (!$request->has('code')) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Yetkilendirme kodu alınamadı.');
            }

            $user = Auth::user();
            $this->googleCalendarService->setupClient($user);
            $token = $this->googleCalendarService->getAccessToken($request->get('code'));

            $user->google_token = json_encode($token);
            $user->save();

            return redirect()->route('profile.edit')
                ->with('success', 'Google Takvim başarıyla bağlandı.');
        } catch (\Exception $e) {
            Log::error('Google callback hatası: ' . $e->getMessage());
            return redirect()->route('profile.edit')
                ->with('error', 'Google yetkilendirme tamamlanamadı: ' . $e->getMessage());
        }
    }

    /**
     * Google bağlantısını kaldır
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnectGoogle()
    {
        try {
            $user = Auth::user();
            $user->google_token = null;
            $user->save();

            return redirect()->route('profile.edit')
                ->with('success', 'Google Takvim bağlantısı kaldırıldı.');
        } catch (\Exception $e) {
            Log::error('Google bağlantısı kaldırma hatası: ' . $e->getMessage());
            return redirect()->route('profile.edit')
                ->with('error', 'Google Takvim bağlantısı kaldırılamadı: ' . $e->getMessage());
        }
    }
}
