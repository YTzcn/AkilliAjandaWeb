@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">{{ __('Takvim Senkronizasyonu') }}</h1>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>{{ __('Başarılı!') }}</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>{{ __('Hata!') }}</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">{{ __('Google Takvim Senkronizasyonu') }}</h3>
        </div>
        <div class="card-body">
            @if (!$isConnectedToGoogle)
                <div class="alert alert-warning mb-4" role="alert">
                    <p>{{ __('Google Takvim ile bağlantı kurulmamış. Lütfen önce Google hesabınızı bağlayın.') }}</p>
                    <div class="mt-3">
                        <a href="{{ route('google.auth') }}" class="btn btn-primary">
                            <i class="bi bi-google me-2"></i>{{ __('Google ile Bağlan') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="alert alert-success mb-4" role="alert">
                    <p>{{ __('Google Takvim ile bağlantınız kurulmuş. Aşağıdaki seçenekler ile senkronizasyon işlemlerini gerçekleştirebilirsiniz.') }}</p>
                    <div class="mt-3">
                        <form action="{{ route('google.disconnect') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-2"></i>{{ __('Google Bağlantısını Kaldır') }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <!-- Google'a Etkinlik Senkronizasyonu -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('Etkinlikleri Google Takvim\'e Aktar') }}</h4>
                            </div>
                            <div class="card-body">
                                <p class="card-text text-muted mb-4">{{ __('Uygulamadaki etkinliklerinizi Google Takvim\'e aktarın.') }}</p>
                                
                                <form action="{{ route('calendar.sync.google') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-arrow-up-right-square me-2"></i>{{ __('Dışa Aktar') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Google'dan Etkinlik İçe Aktarma -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('Google Takvim\'den Etkinlikleri İçe Aktar') }}</h4>
                            </div>
                            <div class="card-body">
                                <p class="card-text text-muted mb-4">{{ __('Google Takvim\'deki etkinliklerinizi uygulamaya aktarın.') }}</p>
                                
                                <form action="{{ route('calendar.import.google') }}" method="POST">
                                    @csrf
                                    <div class="row mb-3">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label for="start_date" class="form-label">{{ __('Başlangıç Tarihi') }}</label>
                                            <input type="date" id="start_date" name="start_date" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="end_date" class="form-label">{{ __('Bitiş Tarihi') }}</label>
                                            <input type="date" id="end_date" name="end_date" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-text mb-3">{{ __('Tarih seçilmezse son 30 gün ve gelecek 60 gün içindeki etkinlikler içe aktarılacaktır.') }}</div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-arrow-down-left-square me-2"></i>{{ __('İçe Aktar') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 