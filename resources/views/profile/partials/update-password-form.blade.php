<section class="mb-4">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Şifre Güncelleme') }}</h2>
            <p class="text-muted small">{{ __('Güvenliğiniz için hesabınızda güçlü bir şifre kullandığınızdan emin olun.') }}</p>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('password.update') }}" class="mt-2">
                @csrf
                @method('put')
                
                <div class="mb-3">
                    <label for="current_password" class="form-label">{{ __('Mevcut Şifre') }}</label>
                    <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="current_password" name="current_password" autocomplete="current-password">
                    @error('current_password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Yeni Şifre') }}</label>
                    <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="password" name="password" autocomplete="new-password">
                    @error('password', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">{{ __('Şifre Tekrarı') }}</label>
                    <input type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                    @error('password_confirmation', 'updatePassword')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key-fill me-2"></i>{{ __('Şifreyi Güncelle') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section> 