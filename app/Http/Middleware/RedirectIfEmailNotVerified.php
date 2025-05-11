<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfEmailNotVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
            // Oturumu sonlandır
            if (Auth::check()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return $request->expectsJson()
                ? abort(403, 'E-posta adresinizi doğrulamanız gerekmektedir.')
                : Redirect::route('login')
                    ->withErrors(['email' => 'E-posta adresinizi doğrulamanız gerekmektedir.'])
                    ->withInput(['email' => $request->user()?->email]);
        }

        return $next($request);
    }
} 