<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmailVerificationCodeController extends Controller
{
    /**
     * Doğrulama kodunu göster.
     */
    public function show(): View
    {
        return view('auth.verify-code');
    }

    /**
     * Yeni bir doğrulama kodu gönder.
     */
    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // 6 haneli rastgele kod oluştur
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(30);
        
        // Kodu ve son kullanma tarihini kaydet
        $user->update([
            'email_verification_code' => $code,
            'email_verification_code_expires_at' => $expiresAt,
        ]);
        
        // E-posta gönder
        Mail::to($user->email)->send(new VerificationCodeMail($code, $expiresAt, $user->name));
        
        return back()->with('status', 'verification-code-sent');
    }

    /**
     * Doğrulama kodunu kontrol et.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        
        if ($user->email_verification_code !== $request->code) {
            return back()->withErrors(['code' => 'Doğrulama kodu hatalı.']);
        }
        
        if ($user->email_verification_code_expires_at->isPast()) {
            return back()->withErrors(['code' => 'Doğrulama kodunun süresi dolmuş.']);
        }
        
        if ($user->markEmailAsVerified()) {
            // Doğrulama kodunu temizle
            $user->update([
                'email_verification_code' => null,
                'email_verification_code_expires_at' => null,
            ]);
        }
        
        return redirect()->route('dashboard')->with('status', 'verification-successful');
    }
}
