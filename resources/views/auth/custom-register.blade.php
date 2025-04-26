<x-auth>
    <div class="text-center mb-5">
        <h2 class="fw-bold mb-3">Hesap Oluştur</h2>
        <p class="text-muted">Ajandanızı yönetmek için ücretsiz hesap oluşturun</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="form-label">Ad Soyad</label>
            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Adınız Soyadınız">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email" class="form-label">E-posta Adresi</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="ornek@email.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="form-label">Şifre</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Şifre Tekrar</label>
            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••">
        </div>

        <div class="d-grid gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>
                Kayıt Ol
            </button>
        </div>
    </form>

    <div class="text-center">
        <p class="text-muted mb-0">
            Zaten hesabınız var mı?
            <a href="{{ route('login') }}" class="text-decoration-none fw-medium">
                Giriş Yap
            </a>
        </p>
    </div>
</x-auth> 