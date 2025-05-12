<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

/**
 * @OA\Tag(
 *     name="Kimlik Doğrulama",
 *     description="Kullanıcı kaydı, girişi ve hesap yönetimi için API endpoint'leri"
 * )
 */
class AuthController extends Controller
{
    /**
     * Kullanıcı kaydı
     * 
     * @OA\Post(
     *     path="/api/register",
     *     summary="Yeni kullanıcı kaydı",
     *     description="Yeni bir kullanıcı hesabı oluşturur ve doğrulama kodu gönderir",
     *     tags={"Kimlik Doğrulama"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı kayıt",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Kayıt başarılı. Lütfen e-posta adresinizi doğrulayın."),
     *             @OA\Property(property="token", type="string", example="1|abcdef123456..."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasyon hatası",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_code' => sprintf('%06d', mt_rand(0, 999999)),
            'email_verification_code_expires_at' => now()->addMinutes(30),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail(
            $user->email_verification_code, 
            $user->email_verification_code_expires_at, 
            $user->name
        ));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Kayıt başarılı. Lütfen e-posta adresinizi doğrulayın.',
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Kullanıcı girişi
     * 
     * @OA\Post(
     *     path="/api/login",
     *     summary="Kullanıcı girişi",
     *     description="E-posta ve şifre ile giriş yapar",
     *     tags={"Kimlik Doğrulama"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı giriş",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="token", type="string", example="1|abcdef123456..."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Kimlik doğrulama hatası",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="E-posta adresinizi doğrulamanız gerekmektedir.")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        try {
            $request->authenticate();

            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * E-posta doğrulama
     * 
     * @OA\Post(
     *     path="/api/verify-email",
     *     summary="E-posta doğrulama",
     *     description="Gönderilen 6 haneli kod ile e-posta adresini doğrular",
     *     tags={"Kimlik Doğrulama"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","code"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="code", type="string", example="123456"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı doğrulama",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="E-posta adresi başarıyla doğrulandı.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Geçersiz kod",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Geçersiz veya süresi dolmuş doğrulama kodu.")
     *         )
     *     )
     * )
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = User::where('email', $request->email)
            ->where('email_verification_code', $request->code)
            ->where('email_verification_code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Geçersiz veya süresi dolmuş doğrulama kodu.'
            ], 400);
        }

        $user->email_verified_at = now();
        $user->email_verification_code = null;
        $user->email_verification_code_expires_at = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'E-posta adresi başarıyla doğrulandı.'
        ]);
    }

    /**
     * Doğrulama kodunu yeniden gönder
     * 
     * @OA\Post(
     *     path="/api/resend-verification",
     *     summary="Doğrulama kodunu yeniden gönder",
     *     description="E-posta adresine yeni bir doğrulama kodu gönderir",
     *     tags={"Kimlik Doğrulama"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kod başarıyla gönderildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Doğrulama kodu yeniden gönderildi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="E-posta adresi zaten doğrulanmış.")
     *         )
     *     )
     * )
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kullanıcı bulunamadı.'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'E-posta adresi zaten doğrulanmış.'
            ], 400);
        }

        $user->email_verification_code = sprintf('%06d', mt_rand(0, 999999));
        $user->email_verification_code_expires_at = now()->addMinutes(30);
        $user->save();

        Mail::to($user->email)->send(new VerificationCodeMail(
            $user->email_verification_code, 
            $user->email_verification_code_expires_at, 
            $user->name
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Doğrulama kodu yeniden gönderildi.'
        ]);
    }

    /**
     * Şifremi unuttum
     * 
     * @OA\Post(
     *     path="/api/forgot-password",
     *     summary="Şifremi unuttum",
     *     description="E-posta adresine şifre sıfırlama bağlantısı gönderir",
     *     tags={"Kimlik Doğrulama"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bağlantı gönderildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Şifre sıfırlama bağlantısı gönderildi.")
     *         )
     *     )
     * )
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'message' => 'Şifre sıfırlama bağlantısı gönderildi.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __($status)
        ], 400);
    }

    /**
     * Şifre sıfırlama
     * 
     * @OA\Post(
     *     path="/api/reset-password",
     *     summary="Şifre sıfırlama",
     *     description="Yeni şifre belirler",
     *     tags={"Kimlik Doğrulama"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","email","password","password_confirmation"},
     *             @OA\Property(property="token", type="string", example="abcdef123456..."),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Şifre başarıyla sıfırlandı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Şifre başarıyla sıfırlandı.")
     *         )
     *     )
     * )
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'success',
                'message' => 'Şifre başarıyla sıfırlandı.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __($status)
        ], 400);
    }

    /**
     * Şifre değiştirme
     * 
     * @OA\Post(
     *     path="/api/change-password",
     *     summary="Şifre değiştirme",
     *     description="Mevcut şifreyi değiştirir",
     *     tags={"Kimlik Doğrulama"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password","password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="oldpassword123"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Şifre başarıyla değiştirildi",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Şifre başarıyla değiştirildi.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Hata",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Mevcut şifre yanlış.")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mevcut şifre yanlış.'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Şifre başarıyla değiştirildi.'
        ]);
    }

    /**
     * Kullanıcı çıkışı
     * 
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Çıkış yap",
     *     description="Mevcut oturumu sonlandırır",
     *     tags={"Kimlik Doğrulama"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı çıkış",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Başarıyla çıkış yapıldı.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Başarıyla çıkış yapıldı.'
        ]);
    }

    /**
     * Kullanıcı bilgileri
     * 
     * @OA\Get(
     *     path="/api/user",
     *     summary="Kullanıcı bilgileri",
     *     description="Giriş yapmış kullanıcının bilgilerini getirir",
     *     tags={"Kimlik Doğrulama"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Başarılı",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()
        ]);
    }
}
