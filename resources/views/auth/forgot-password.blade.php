@extends('layouts.auth')

@section('content')
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">Şifremi Unuttum</h2>
        <p class="text-muted">Şifrenizi sıfırlamak için e-posta adresinizi girin</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text border-end-0" style="background: transparent;">
                    <i class="bi bi-envelope text-muted"></i>
                </span>
                <input id="email" 
                    type="email" 
                    class="form-control border-start-0 @error('email') is-invalid @enderror" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus 
                    placeholder="ornek@email.com"
                    style="border-radius: 0 0.75rem 0.75rem 0;">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-grid mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-envelope me-2"></i>
                Sıfırlama Bağlantısı Gönder
            </button>
        </div>
    </form>

    <div class="text-center">
        <p class="text-muted mb-0">
            <a href="{{ route('login') }}" class="text-decoration-none fw-medium">
                <i class="bi bi-arrow-left me-1"></i>
                Giriş sayfasına dön
            </a>
        </p>
    </div>
@endsection 