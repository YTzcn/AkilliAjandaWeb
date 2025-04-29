<section class="mb-4">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Profil Bilgileri') }}</h2>
            <p class="text-muted small">{{ __('Hesap bilgilerinizi ve e-posta adresinizi güncelleyin.') }}</p>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('profile.update') }}" id="send-verification" class="mt-2">
                @csrf
            </form>
            
            <form method="post" action="{{ route('profile.update') }}" class="mt-2">
                @csrf
                @method('patch')
                
                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Ad Soyad') }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('E-posta') }}</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="mt-2">
                            <p class="text-danger">
                                {{ __('E-posta adresiniz doğrulanmadı.') }}
                            </p>
                            
                            <button form="send-verification" class="btn btn-outline-primary">
                                {{ __('Doğrulama e-postasını tekrar gönder') }}
                            </button>
                        </div>
                    @endif
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>{{ __('Kaydet') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section> 