@extends('layouts.auth')

@section('content')
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">Şifreyi Onayla</h2>
        <p class="text-muted">Bu alanı görmek için lütfen şifrenizi tekrar girin</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label">Şifre</label>
            <input
                id="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                required
                autocomplete="current-password"
                autofocus
                placeholder="••••••••"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-shield-check me-2"></i>
                Onayla
            </button>
        </div>
    </form>
@endsection
