@extends('layouts.auth')

@section('content')
<div
    class="mx-auto"
    style="max-width: 36rem;"
    x-data="{ step: 1 }"
>
    <p class="text-muted small mb-2">
        <a href="{{ route('help.index') }}" class="text-decoration-none">Yardım merkezi</a>
        <span class="mx-1">·</span>
        <span>Adım <span x-text="step"></span> / 4</span>
    </p>
    <h1 class="h3 fw-bold mb-4">Akıllı Ajanda’ya hoş geldiniz</h1>

    <div class="progress mb-4" style="height: 0.35rem;" role="progressbar" aria-valuemin="1" aria-valuemax="4" :aria-valuenow="step">
        <div class="progress-bar" :style="'width: ' + (step / 4 * 100) + '%'"></div>
    </div>

    <div x-show="step === 1" x-cloak class="mb-4">
        <h2 class="h5">Gösterge paneli</h2>
        <p class="text-secondary">Ana sayfada yaklaşan görev ve etkinlik özetlerini görürsünüz. Tarih aralığını değiştirerek haftanıza odaklanabilirsiniz.</p>
    </div>
    <div x-show="step === 2" x-cloak class="mb-4">
        <h2 class="h5">Etkinlikler ve görevler</h2>
        <p class="text-secondary">Etkinlikler takvimde zaman dilimiyle; görevler son tarih ve öncelikle listelenir. Kategorilerle her ikisini de gruplayabilirsiniz.</p>
    </div>
    <div x-show="step === 3" x-cloak class="mb-4">
        <h2 class="h5">Arama ve raporlar</h2>
        <p class="text-secondary">Üst çubuktan hızlı arama yapın; dönem raporu ile CSV, PDF veya yazdırılabilir özet alın.</p>
    </div>
    <div x-show="step === 4" x-cloak class="mb-4">
        <h2 class="h5">Hazırsınız</h2>
        <p class="text-secondary mb-3">Devam ederek temel özelliklere kısa turu tamamlamış olursunuz. İstediğiniz zaman <strong>Yardım</strong> sayfasına dönebilirsiniz.</p>
        <form method="post" action="{{ route('onboarding.complete') }}" class="border rounded-3 p-3 bg-light">
            @csrf
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="1" name="confirm" id="onb-confirm" required>
                <label class="form-check-label" for="onb-confirm">Bilgileri okudum, panele geçmek istiyorum.</label>
            </div>
            @error('confirm')
                <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-primary mt-3 w-100">Panele git</button>
        </form>
    </div>

    <div class="d-flex gap-2 justify-content-between align-items-center" x-show="step < 4">
        <button type="button" class="btn btn-light" @click="step = Math.max(1, step - 1)" :disabled="step === 1">Geri</button>
        <button type="button" class="btn btn-primary" @click="step++">İleri</button>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
@endpush
