<section class="mb-4">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Google Takvim Entegrasyonu') }}</h2>
            <p class="text-muted small">{{ __('Google Takvim ile hesabınızı bağlayarak etkinliklerinizi senkronize edebilirsiniz.') }}</p>
        </div>
        <div class="card-body">
            @if(auth()->user()->google_token)
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>{{ __('Google Takvim hesabınız bağlandı.') }}</div>
                </div>

                <div class="d-flex gap-2">
                    <form method="post" action="{{ route('google.disconnect') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            {{ __('Bağlantıyı Kaldır') }}
                        </button>
                    </form>
                    <a href="{{ route('calendar.sync') }}" class="btn btn-primary">
                        {{ __('Takvim Senkronizasyonu') }}
                    </a>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>{{ __('Google Takvim hesabınız henüz bağlanmadı.') }}</div>
                </div>

                <div>
                    <a href="{{ route('google.auth') }}" class="btn btn-primary">
                        <i class="bi bi-google me-2"></i>{{ __('Google ile Bağlan') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section> 