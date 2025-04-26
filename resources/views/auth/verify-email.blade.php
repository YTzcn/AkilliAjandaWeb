@extends('layouts.auth')

@section('content')
    <div class="mb-7 text-center">
        <h2 class="text-2xl font-bold mb-2">E-posta Doğrulama</h2>
        <p class="text-gray-600 dark:text-gray-400">Hesabınızı kullanmaya başlamadan önce e-posta adresinizi doğrulayın</p>
    </div>

    <div class="mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 text-sm text-blue-800 dark:bg-blue-900/30 dark:border-blue-800 dark:text-blue-300">
        {{ __('Kaydolduğunuz için teşekkür ederiz! Başlamadan önce, size gönderdiğimiz bağlantıya tıklayarak e-posta adresinizi doğrulayabilir misiniz? E-postayı almadıysanız, size memnuniyetle başka bir tane göndereceğiz.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300">
            {{ __('Kayıt sırasında verdiğiniz e-posta adresine yeni bir doğrulama bağlantısı gönderildi.') }}
        </div>
    @endif

    <div class="mt-8 flex flex-col space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-envelope me-2"></i>
                    {{ __('Doğrulama E-postasını Yeniden Gönder') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <div class="d-grid">
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right me-2"></i>
                    {{ __('Çıkış Yap') }}
                </button>
            </div>
        </form>
    </div>
@endsection 