@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">{{ __('Profil') }}</h1>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ __('Profil bilgileriniz güncellendi.') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ __('Şifreniz güncellendi.') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Profil Güncelleme Formu -->
            @include('profile.partials.update-profile-form')
            
            <!-- Şifre Güncelleme Formu -->
            @include('profile.partials.update-password-form')
            
            <!-- Google Takvim Bağlantısı -->
            @include('profile.partials.google-calendar-form')
            
            <!-- Hesap Silme Formu -->
            @include('profile.partials.delete-user-form')
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Hesap Özeti') }}</h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted">{{ $user->email }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h5>{{ __('Etkinlikler') }}</h5>
                        <p>{{ $user->events()->count() }} {{ __('etkinlik') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h5>{{ __('Görevler') }}</h5>
                        <p>{{ $user->tasks()->count() }} {{ __('görev') }}</p>
                        <p>{{ $user->tasks()->where('is_completed', true)->count() }} {{ __('tamamlandı') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Hızlı Bağlantılar') }}</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('calendar.sync') }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-plus me-2"></i>{{ __('Takvim Senkronizasyonu') }}
                        </a>
                     
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 