@extends('layouts.auth')

@section('content')
    <div class="mb-7 text-center">
        <h2 class="text-2xl font-bold mb-2">Şifre Sıfırlama</h2>
        <p class="text-gray-600 dark:text-gray-400">Hesabınız için yeni bir şifre belirleyin</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="form-label">E-posta Adresi</label>
            <input id="email" 
                type="email" 
                class="form-control @error('email') is-invalid @enderror" 
                name="email" 
                value="{{ old('email', $request->email) }}" 
                required 
                autofocus 
                autocomplete="username" 
                readonly>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="form-label">Yeni Şifre</label>
            <input id="password" 
                type="password" 
                class="form-control @error('password') is-invalid @enderror" 
                name="password" 
                required 
                autocomplete="new-password" 
                placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Şifre Tekrar</label>
            <input id="password_confirmation" 
                type="password" 
                class="form-control @error('password_confirmation') is-invalid @enderror" 
                name="password_confirmation" 
                required 
                autocomplete="new-password" 
                placeholder="••••••••">
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-key me-2"></i>
                Şifreyi Sıfırla
            </button>
        </div>
    </form>

    <div class="text-center mt-4">
        <p class="text-muted mb-0">
            <a href="{{ route('login') }}" class="text-decoration-none fw-medium">
                <i class="bi bi-arrow-left me-1"></i>
                Giriş sayfasına dön
            </a>
        </p>
    </div>
@endsection 