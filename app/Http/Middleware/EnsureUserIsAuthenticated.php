<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAuthenticated
{
    /**
     * Kullanıcının giriş yapmış olup olmadığını kontrol eder.
     * Giriş yapmamışsa login sayfasına yönlendirir.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            // Eğer AJAX isteği ise JSON yanıtı döndür
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            
            // Dil desteği varsa dil parametresini URL'ye ekle
            return redirect()->route('login')->with('error', 'Bu sayfaya erişmek için giriş yapmanız gerekiyor.');
        }

        return $next($request);
    }
} 