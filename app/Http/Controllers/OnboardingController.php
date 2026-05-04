<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->onboarding_completed_at !== null) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.show');
    }

    public function complete(Request $request): RedirectResponse
    {
        if ($request->user()->onboarding_completed_at !== null) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'confirm' => ['accepted'],
        ], [
            'confirm.accepted' => 'Devam etmek için kutuyu işaretleyin.',
        ]);

        $request->user()->forceFill([
            'onboarding_completed_at' => now(),
        ])->save();

        return redirect()->route('dashboard')->with('status', 'Hoş geldiniz! Tanıtım tamamlandı.');
    }
}
