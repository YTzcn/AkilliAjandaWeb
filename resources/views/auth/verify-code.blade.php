@extends('layouts.auth')

@section('content')
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">E-posta Doğrulama</h2>
        <p class="text-muted">Size gönderilen 6 haneli doğrulama kodunu girin</p>
    </div>

    @if (session('status') === 'verification-code-sent')
        <div class="alert alert-success" role="alert">
            Yeni doğrulama kodu e-posta adresinize gönderildi.
        </div>
    @endif

    <form method="POST" action="{{ route('verification.verify-code') }}">
        @csrf

        <div class="mb-4">
            <label for="code" class="form-label">Doğrulama Kodu</label>
            <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" 
                name="code" value="{{ old('code') }}" required autofocus maxlength="6" 
                pattern="[0-9]{6}" placeholder="000000">
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>
                Doğrula
            </button>
        </div>
    </form>

    <form method="POST" action="{{ route('verification.send-code') }}" class="text-center">
        @csrf
        <p class="text-muted mb-0">
            Kod almadınız mı?
            <button type="submit" class="btn btn-link p-0 m-0 align-baseline text-decoration-none">
                Yeni kod gönder
            </button>
        </p>
    </form>
@endsection 