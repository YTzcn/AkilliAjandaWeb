<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Giriş yapmış kullanıcı tanıtımı bitirmeden uygulama sayfalarına erişemez.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        if ($user->onboarding_completed_at !== null) {
            return $next($request);
        }

        if ($request->routeIs('onboarding.*', 'help.*')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Onboarding gerekli.',
                'redirect' => route('onboarding.show'),
            ], 409);
        }

        return redirect()->route('onboarding.show');
    }
}
