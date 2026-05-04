@extends('layouts.app')

@section('content')
<div class="container py-3" style="max-width: 52rem;">
    <header class="mb-4">
        <h1 class="h2 fw-bold">Yardım merkezi</h1>
        <p class="text-secondary mb-0">Sık sorulan sorular ve kısa kullanım notları.</p>
    </header>

    <nav aria-label="İçindekiler" class="card mb-4 shadow-sm">
        <div class="card-header fw-semibold">İçindekiler</div>
        <div class="card-body py-2">
            <ul class="mb-0 small">
                <li><a href="#baslangic" class="text-decoration-none">Başlangıç</a></li>
                <li><a href="#gorevler-etkinlikler" class="text-decoration-none">Görevler ve etkinlikler</a></li>
                <li><a href="#arama-rapor" class="text-decoration-none">Arama ve raporlar</a></li>
                <li><a href="#guvenlik" class="text-decoration-none">Hesap ve güvenlik</a></li>
            </ul>
        </div>
    </nav>

    <article class="card mb-3 shadow-sm" id="baslangic">
        <div class="card-header fw-semibold">Başlangıç</div>
        <div class="card-body">
            <p class="mb-2">Giriş yaptıktan sonra <strong>Gösterge paneli</strong> yaklaşan işlerinize özet sunar. Sol menüden modüller arasında geçiş yapabilirsiniz.</p>
            <p class="mb-0 text-muted small">İlk girişte kısa tanıtım turunu tamamlamanız istenebilir; istediğiniz zaman bu sayfaya dönebilirsiniz.</p>
        </div>
    </article>

    <article class="card mb-3 shadow-sm" id="gorevler-etkinlikler">
        <div class="card-header fw-semibold">Görevler ve etkinlikler</div>
        <div class="card-body">
            <ul class="mb-0">
                <li><strong>Görevler:</strong> son tarih, durum ve önceliğe göre filtreleyin; tamamlananları işaretleyin.</li>
                <li><strong>Etkinlikler:</strong> başlangıç/bitiş zamanı ve konum alanlarını kullanın; gün/hafta görünümü takvim sayfasında yer alır.</li>
                <li><strong>Kategoriler:</strong> hem görev hem etkinlik kayıtlarına birden fazla etiket atayabilirsiniz.</li>
            </ul>
        </div>
    </article>

    <article class="card mb-3 shadow-sm" id="arama-rapor">
        <div class="card-header fw-semibold">Arama ve raporlar</div>
        <div class="card-body">
            <ul class="mb-0">
                <li>Üst çubuktaki <strong>hızlı arama</strong> kutusu görev ve etkinlik başlıklarında metin arar (kısa gecikmeyle).</li>
                <li><strong>Raporlar</strong> menüsünden tarih aralığı seçip özet, CSV, PDF veya yazdırılabilir HTML alabilirsiniz.</li>
            </ul>
        </div>
    </article>

    <article class="card mb-4 shadow-sm" id="guvenlik">
        <div class="card-header fw-semibold">Hesap ve güvenlik</div>
        <div class="card-body">
            <p class="mb-0">Profil sayfasından şifrenizi güncelleyebilirsiniz. Ortak cihazlarda oturumu kapatmayı unutmayın.</p>
        </div>
    </article>

    <p class="text-muted small">
        <a href="{{ route('dashboard') }}" class="text-decoration-none">Panele dön</a>
    </p>
</div>
@endsection
