@extends('layouts.app')

@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
    :root {
        --rp-ink: #1a1814;
        --rp-ink-soft: #4a453c;
        --rp-paper: #f7f4ee;
        --rp-paper-deep: #ebe6dc;
        --rp-surface: rgba(255, 255, 255, 0.72);
        --rp-accent: #c45c3e;
        --rp-accent-deep: #9a3d28;
        --rp-teal: #2d6a6a;
        --rp-teal-soft: #e8f2f2;
        --rp-gold: #b8860b;
        --rp-line: rgba(26, 24, 20, 0.08);
        --rp-shadow: 0 24px 48px -24px rgba(26, 24, 20, 0.25);
        --rp-radius: 1.25rem;
        --rp-font-display: "Cormorant Garamond", Georgia, serif;
        --rp-font-ui: "DM Sans", system-ui, sans-serif;
    }

    .report-page {
        font-family: var(--rp-font-ui);
        color: var(--rp-ink);
        margin: -1rem -1.5rem 0;
        padding: 0 0 3rem;
        background-color: var(--rp-paper);
        background-image:
            radial-gradient(ellipse 120% 80% at 100% -10%, rgba(196, 92, 62, 0.09), transparent 55%),
            radial-gradient(ellipse 90% 60% at -5% 110%, rgba(45, 106, 106, 0.08), transparent 50%),
            repeating-linear-gradient(0deg, transparent, transparent 3px, rgba(26, 24, 20, 0.012) 3px, rgba(26, 24, 20, 0.012) 4px);
        min-height: calc(100vh - 4rem);
    }

    .report-page .report-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1.5rem 0;
    }

    .report-hero {
        position: relative;
        padding: 2.25rem 2rem 2rem;
        border-radius: var(--rp-radius);
        background: var(--rp-surface);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--rp-line);
        box-shadow: var(--rp-shadow);
        overflow: hidden;
    }

    .report-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(196, 92, 62, 0.06) 0%, transparent 42%, rgba(45, 106, 106, 0.05) 100%);
        pointer-events: none;
    }

    .report-hero__eyebrow {
        position: relative;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.22em;
        text-transform: uppercase;
        color: var(--rp-teal);
        margin-bottom: 0.5rem;
    }

    .report-hero h1 {
        position: relative;
        font-family: var(--rp-font-display);
        font-weight: 600;
        font-size: clamp(2rem, 4vw, 2.75rem);
        line-height: 1.12;
        letter-spacing: -0.02em;
        margin: 0 0 0.75rem;
        color: var(--rp-ink);
    }

    .report-hero__lead {
        position: relative;
        font-size: 0.95rem;
        color: var(--rp-ink-soft);
        max-width: 38rem;
        line-height: 1.55;
        margin-bottom: 1.5rem;
    }

    .report-hero__actions {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: center;
    }

    .report-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 1rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 999px;
        text-decoration: none;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        border: none;
        cursor: pointer;
    }

    .report-btn--primary {
        background: linear-gradient(145deg, var(--rp-accent) 0%, var(--rp-accent-deep) 100%);
        color: #fff;
        box-shadow: 0 4px 14px -4px rgba(196, 92, 62, 0.55);
    }

    .report-btn--primary:hover {
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 8px 22px -6px rgba(196, 92, 62, 0.55);
    }

    .report-btn--ghost {
        background: rgba(255, 255, 255, 0.85);
        color: var(--rp-ink);
        border: 1px solid var(--rp-line);
    }

    .report-btn--ghost:hover {
        background: #fff;
        color: var(--rp-ink);
        border-color: rgba(26, 24, 20, 0.12);
        transform: translateY(-1px);
    }

    .report-filter {
        margin-top: 1.75rem;
        padding: 1.35rem 1.5rem;
        border-radius: calc(var(--rp-radius) - 0.25rem);
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid var(--rp-line);
    }

    .report-filter label {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--rp-ink-soft);
    }

    .report-filter .form-control {
        border-radius: 0.65rem;
        border: 1px solid rgba(26, 24, 20, 0.12);
        background: rgba(255, 255, 255, 0.9);
        font-size: 0.875rem;
    }

    .report-filter .form-control:focus {
        border-color: var(--rp-teal);
        box-shadow: 0 0 0 3px rgba(45, 106, 106, 0.15);
    }

    .report-bento {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin: 2rem 0;
    }

    @media (max-width: 991px) {
        .report-bento { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 575px) {
        .report-bento { grid-template-columns: 1fr; }
    }

    .report-stat {
        position: relative;
        padding: 1.35rem 1.25rem;
        border-radius: var(--rp-radius);
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid var(--rp-line);
        box-shadow: 0 12px 28px -18px rgba(26, 24, 20, 0.2);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .report-stat:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px -20px rgba(26, 24, 20, 0.28);
    }

    .report-stat::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 3px 3px 0 0;
        opacity: 0.9;
    }

    .report-stat--total::after { background: linear-gradient(90deg, var(--rp-ink), var(--rp-ink-soft)); }
    .report-stat--done::after { background: linear-gradient(90deg, #2d6a4f, #3d8b6a); }
    .report-stat--open::after { background: linear-gradient(90deg, var(--rp-gold), #d4a84b); }
    .report-stat--events::after { background: linear-gradient(90deg, var(--rp-teal), #3a8f8f); }

    .report-stat__label {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--rp-ink-soft);
        margin-bottom: 0.35rem;
    }

    .report-stat__value {
        font-family: var(--rp-font-display);
        font-size: 2.35rem;
        font-weight: 700;
        line-height: 1;
        color: var(--rp-ink);
    }

    .report-stat--done .report-stat__value { color: #2d6a4f; }
    .report-stat--open .report-stat__value { color: #8a6a1f; }
    .report-stat--events .report-stat__value { color: var(--rp-teal); }

    .report-panel {
        border-radius: var(--rp-radius);
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid var(--rp-line);
        box-shadow: var(--rp-shadow);
        overflow: hidden;
        height: 100%;
    }

    .report-panel__head {
        padding: 1rem 1.25rem;
        font-family: var(--rp-font-display);
        font-size: 1.35rem;
        font-weight: 600;
        border-bottom: 1px solid var(--rp-line);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(247, 244, 238, 0.5) 100%);
    }

    .report-table-wrap { overflow-x: auto; }

    .report-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }

    .report-table thead th {
        text-align: left;
        padding: 0.75rem 1.25rem;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--rp-ink-soft);
        background: var(--rp-paper-deep);
        border-bottom: 1px solid var(--rp-line);
    }

    .report-table tbody td {
        padding: 0.85rem 1.25rem;
        border-bottom: 1px solid var(--rp-line);
        vertical-align: middle;
    }

    .report-table tbody tr:last-child td { border-bottom: none; }

    .report-table tbody tr {
        transition: background 0.15s ease;
    }

    .report-table tbody tr:hover {
        background: rgba(45, 106, 106, 0.04);
    }

    .report-table a {
        color: var(--rp-ink);
        font-weight: 500;
        text-decoration: none;
        border-bottom: 1px solid rgba(196, 92, 62, 0.35);
        transition: color 0.15s ease, border-color 0.15s ease;
    }

    .report-table a:hover {
        color: var(--rp-accent-deep);
        border-bottom-color: var(--rp-accent-deep);
    }

    .report-pill {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    .report-pill--ok { background: var(--rp-teal-soft); color: var(--rp-teal); }
    .report-pill--warn { background: #fdf6e3; color: #8a6a1f; }
    .report-pill--muted { background: rgba(26, 24, 20, 0.06); color: var(--rp-ink-soft); }

    .report-empty {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--rp-ink-soft);
        font-style: italic;
    }

    @keyframes reportFadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .report-stagger > * {
        opacity: 0;
        animation: reportFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .report-stagger > *:nth-child(1) { animation-delay: 0.05s; }
    .report-stagger > *:nth-child(2) { animation-delay: 0.1s; }
    .report-stagger > *:nth-child(3) { animation-delay: 0.15s; }
    .report-stagger > *:nth-child(4) { animation-delay: 0.2s; }
    .report-stagger > *:nth-child(5) { animation-delay: 0.25s; }
    .report-stagger > *:nth-child(6) { animation-delay: 0.3s; }
    .report-stagger > *:nth-child(7) { animation-delay: 0.35s; }
    .report-stagger > *:nth-child(8) { animation-delay: 0.4s; }

    .report-analytics {
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        gap: 1.25rem;
        margin: 2rem 0 1.5rem;
        align-items: start;
    }
    @media (max-width: 991px) {
        .report-analytics { grid-template-columns: 1fr; }
    }

    .report-narrative {
        border-radius: var(--rp-radius);
        padding: 1.5rem 1.35rem;
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid var(--rp-line);
        box-shadow: var(--rp-shadow);
    }
    .report-narrative h2 {
        font-family: var(--rp-font-display);
        font-size: 1.45rem;
        font-weight: 600;
        margin: 0 0 1rem;
        color: var(--rp-ink);
    }
    .report-narrative ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .report-narrative li {
        position: relative;
        padding-left: 1.15rem;
        margin-bottom: 0.65rem;
        font-size: 0.9rem;
        line-height: 1.5;
        color: var(--rp-ink-soft);
    }
    .report-narrative li::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0.55em;
        width: 0.35rem;
        height: 0.35rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--rp-accent), var(--rp-teal));
    }

    .report-ring-wrap {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-top: 1.35rem;
        padding-top: 1.25rem;
        border-top: 1px dashed var(--rp-line);
    }
    .report-ring {
        --pct: 0;
        width: 5.5rem;
        height: 5.5rem;
        border-radius: 50%;
        background: conic-gradient(var(--rp-teal) calc(var(--pct) * 1%), rgba(26, 24, 20, 0.08) 0);
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .report-ring__hole {
        width: 3.35rem;
        height: 3.35rem;
        border-radius: 50%;
        background: var(--rp-paper);
        display: grid;
        place-items: center;
        font-family: var(--rp-font-display);
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--rp-ink);
    }
    .ring-caption { font-size: 0.78rem; color: var(--rp-ink-soft); max-width: 14rem; line-height: 1.45; }

    .report-viz {
        border-radius: var(--rp-radius);
        padding: 1.35rem 1.25rem;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid var(--rp-line);
        box-shadow: var(--rp-shadow);
    }
    .report-viz h3 {
        font-family: var(--rp-font-display);
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0 0 0.85rem;
    }
    .report-viz h3 + * { margin-top: 0; }

    .rp-heat {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.35rem;
        margin-bottom: 1.25rem;
    }
    .rp-heat__cell {
        text-align: center;
        font-size: 0.58rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--rp-ink-soft);
        padding-bottom: 0.2rem;
    }
    .rp-heat__bar {
        height: 3.25rem;
        border-radius: 0.35rem;
        background: rgba(26, 24, 20, 0.06);
        position: relative;
        overflow: hidden;
    }
    .rp-heat__fill {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 0.35rem;
        background: linear-gradient(180deg, rgba(45, 106, 106, 0.35), var(--rp-teal));
        transition: height 0.35s ease;
    }

    .rp-prio-row { margin-bottom: 0.65rem; }
    .rp-prio-row__top {
        display: flex;
        justify-content: space-between;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--rp-ink-soft);
        margin-bottom: 0.2rem;
    }
    .rp-prio-track {
        height: 0.55rem;
        border-radius: 999px;
        background: rgba(26, 24, 20, 0.07);
        overflow: hidden;
    }
    .rp-prio-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--rp-gold), var(--rp-accent));
    }

    .report-rail { margin-top: 0.25rem; }
    .report-rail__row {
        display: grid;
        grid-template-columns: 5.5rem 1fr;
        gap: 0.5rem;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.72rem;
    }
    .report-rail__label { color: var(--rp-ink-soft); font-weight: 600; }
    .report-rail__track {
        display: flex;
        height: 0.65rem;
        border-radius: 999px;
        overflow: hidden;
        background: rgba(26, 24, 20, 0.06);
    }
    .report-rail__seg--t { background: rgba(45, 106, 106, 0.85); }
    .report-rail__seg--e { background: rgba(196, 92, 62, 0.75); }

    .report-meta-line {
        font-size: 0.78rem;
        color: var(--rp-ink-soft);
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--rp-line);
    }

    .report-details {
        border-radius: var(--rp-radius);
        border: 1px dashed rgba(26, 24, 20, 0.18);
        background: rgba(255, 255, 255, 0.45);
        padding: 0.25rem 1rem 1rem;
        margin-top: 0.5rem;
    }
    .report-details summary {
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.85rem 0;
        list-style: none;
        color: var(--rp-ink);
    }
    .report-details summary::-webkit-details-marker { display: none; }
    .report-details summary::after {
        content: " ▸";
        color: var(--rp-accent);
    }
    .report-details[open] summary::after { content: " ▾"; }
</style>
@endsection

@section('content')
<div class="report-page">
    <div class="report-inner report-stagger">
        <header class="report-hero">
            <p class="report-hero__eyebrow">Özet · Dönem analizi</p>
            <h1>Dönem raporu</h1>
            <p class="report-hero__lead">
                Seçtiğin aralıkta <strong>son tarihi</strong> bu pencereye düşen görevler ve takviminde <strong>kesişen</strong> etkinlikler bir arada.
                PDF indir veya <strong>yazdırılabilir raporu</strong> açıp tarayıcıda <em>Yazdır → PDF olarak kaydet</em> kullanabilirsin.
            </p>
            <div class="report-hero__actions">
                <a href="{{ route('reports.export.csv', request()->only(['date_from', 'date_to'])) }}" class="report-btn report-btn--primary">
                    <i class="bi bi-filetype-csv"></i> CSV indir
                </a>
                <a href="{{ route('reports.export.pdf', request()->only(['date_from', 'date_to'])) }}" class="report-btn report-btn--primary">
                    <i class="bi bi-filetype-pdf"></i> PDF indir
                </a>
                <a href="{{ route('reports.export.html', request()->only(['date_from', 'date_to'])) }}" class="report-btn report-btn--ghost">
                    <i class="bi bi-printer"></i> Yazdırılabilir rapor
                </a>
            </div>

            <div class="report-filter">
                <form method="get" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label d-block mb-1" for="rp-from">Başlangıç</label>
                        <input id="rp-from" type="date" name="date_from" class="form-control @error('date_from') is-invalid @enderror" value="{{ old('date_from', $dateFrom) }}" required>
                        @error('date_from')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label d-block mb-1" for="rp-to">Bitiş</label>
                        <input id="rp-to" type="date" name="date_to" class="form-control @error('date_to') is-invalid @enderror" value="{{ old('date_to', $dateTo) }}" required>
                        @error('date_to')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 col-lg-auto">
                        <button type="submit" class="report-btn report-btn--primary px-4">
                            <i class="bi bi-funnel"></i> Göster
                        </button>
                    </div>
                </form>
            </div>
        </header>

        <div class="report-bento">
            <div class="report-stat report-stat--total">
                <div class="report-stat__label">Görev (toplam)</div>
                <div class="report-stat__value">{{ $report->taskTotal }}</div>
            </div>
            <div class="report-stat report-stat--done">
                <div class="report-stat__label">Tamamlanan</div>
                <div class="report-stat__value">{{ $report->taskCompleted }}</div>
            </div>
            <div class="report-stat report-stat--open">
                <div class="report-stat__label">Bekleyen</div>
                <div class="report-stat__value">{{ $report->taskPending }}</div>
            </div>
            <div class="report-stat report-stat--events">
                <div class="report-stat__label">Etkinlik (kesişen)</div>
                <div class="report-stat__value">{{ $report->eventTotal }}</div>
            </div>
        </div>

        @php
            $in = $report->insights;
            $wdLabels = [1 => 'Pzt', 2 => 'Sal', 3 => 'Çar', 4 => 'Per', 5 => 'Cum', 6 => 'Cmt', 7 => 'Paz'];
            $prioLabels = [1 => 'Düşük', 2 => 'Orta', 3 => 'Yüksek'];
            $prioOrder = [3, 2, 1];
            $prioMax = max(1, max($in->tasksByPriority));
            $bys = $in->eventsByWeekdayIso;
            $heatMax = max(1, max(array_values($bys) ?: [0]));
            $bucketTotals = collect($in->weekBuckets)->map(fn ($b) => ($b['task_count'] ?? 0) + ($b['event_count'] ?? 0));
            $railMax = max(1, (int) $bucketTotals->max());
        @endphp

        <div class="report-analytics">
            <div class="report-narrative">
                <h2>Ne anlatıyor bu dönem?</h2>
                <ul>
                    @forelse($in->narrativeBullets as $line)
                        <li>{{ $line }}</li>
                    @empty
                        <li>Seçilen aralık için özet üretilemedi; filtreleri kontrol edebilirsin.</li>
                    @endforelse
                </ul>
                <div class="report-ring-wrap">
                    <div class="report-ring" style="--pct: {{ (int) $in->completionRatePercent }}">
                        <div class="report-ring__hole">{{ $in->completionRatePercent }}%</div>
                    </div>
                    <p class="ring-caption mb-0">
                        Son tarihi bu pencereye düşen görevlerde <strong>tamamlanma oranı</strong>.
                        Açık görevlerde yüksek öncelik: <strong>{{ $in->highPriorityOpenCount }}</strong>,
                        gecikmiş açık: <strong>{{ $in->overdueOpenCount }}</strong>.
                    </p>
                </div>
            </div>

            <div class="report-viz">
                <h3>Haftanın gününe göre yoğunluk</h3>
                <p class="small text-muted mb-2">Görev son tarihi ve etkinlik başlangıcının hafta içi dağılımı (aynı günde üst üste binen kayıtlar).</p>
                <div class="rp-heat" role="img" aria-label="Haftanın günlerine göre görev ve etkinlik yoğunluğu">
                    @foreach($wdLabels as $iso => $short)
                        @php $cnt = (int) ($bys[$iso] ?? 0); @endphp
                        <div>
                            <div class="rp-heat__cell">{{ $short }}</div>
                            <div class="rp-heat__bar" title="{{ $cnt }} kayıt">
                                <div class="rp-heat__fill" style="height: {{ min(100, round($cnt / $heatMax * 100)) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($in->busiestWeekdayLabel)
                    <p class="small text-muted mb-3 mb-lg-0">En yoğun gün: <strong>{{ $in->busiestWeekdayLabel }}</strong> ({{ $in->busiestWeekdayCount }} kayıt).</p>
                @endif

                <h3 class="mt-lg-3">Görev öncelik dağılımı</h3>
                @foreach($prioOrder as $p)
                    @php $n = (int) ($in->tasksByPriority[$p] ?? 0); @endphp
                    <div class="rp-prio-row">
                        <div class="rp-prio-row__top">
                            <span>{{ $prioLabels[$p] }} ({{ $p }})</span>
                            <span>{{ $n }}</span>
                        </div>
                        <div class="rp-prio-track">
                            <div class="rp-prio-fill" style="width: {{ min(100, round($n / $prioMax * 100)) }}%"></div>
                        </div>
                    </div>
                @endforeach

                @if(count($in->weekBuckets))
                    <h3 class="mt-3">7 günlük dilimler (görev son tarihi · kesişen etkinlik)</h3>
                    <div class="report-rail">
                        @foreach($in->weekBuckets as $bucket)
                            @php
                                $tc = (int) ($bucket['task_count'] ?? 0);
                                $ec = (int) ($bucket['event_count'] ?? 0);
                                $scale = min(100, round(($tc + $ec) / $railMax * 100));
                            @endphp
                            <div class="report-rail__row">
                                <span class="report-rail__label">{{ $bucket['label'] }}</span>
                                <div>
                                    <div class="report-rail__track" style="width: {{ $scale }}%; min-width: {{ ($tc + $ec) > 0 ? '12%' : '0' }}">
                                        @if($tc + $ec > 0)
                                            <span class="report-rail__seg report-rail__seg--t" style="flex: {{ $tc }}"></span>
                                            <span class="report-rail__seg report-rail__seg--e" style="flex: {{ $ec }}"></span>
                                        @endif
                                    </div>
                                    <span class="text-muted" style="font-size: 0.65rem;">{{ $tc }} görev · {{ $ec }} etkinlik</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mb-0 text-muted" style="font-size: 0.65rem;">
                        <span class="d-inline-block me-2"><span class="d-inline-block rounded me-1 align-middle" style="width:0.55rem;height:0.55rem;background:var(--rp-teal)"></span> görev</span>
                        <span class="d-inline-block"><span class="d-inline-block rounded me-1 align-middle" style="width:0.55rem;height:0.55rem;background:rgba(196,92,62,0.75)"></span> etkinlik</span>
                    </p>
                @endif

                <div class="report-meta-line">
                    @if($in->avgEventDurationHours !== null)
                        Ortalama etkinlik süresi: <strong>{{ $in->avgEventDurationHours }}</strong> saat
                        @if($in->totalEventHoursRounded > 0)
                            · Tahmini toplam süre: <strong>{{ $in->totalEventHoursRounded }}</strong> saat
                        @endif
                    @else
                        Etkinlik süresi için yeterli veri yok.
                    @endif
                    @if($in->multiDayEventCount > 0)
                        · Çok günlük etkinlik: <strong>{{ $in->multiDayEventCount }}</strong>
                    @endif
                </div>
            </div>
        </div>

        <details class="report-details">
            <summary>Tüm görev ve etkinlik satırları (detay tablolar)</summary>
            <div class="row g-4 align-items-stretch pt-2">
                <div class="col-lg-6">
                    <div class="report-panel h-100">
                        <div class="report-panel__head">Görevler</div>
                        <div class="report-table-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Başlık</th>
                                        <th>Son tarih</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($report->tasks as $task)
                                        <tr>
                                            <td><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td>
                                            <td class="text-nowrap">{{ $task->due_date?->format('d.m.Y H:i') ?? '—' }}</td>
                                            <td>
                                                @if($task->is_completed)
                                                    <span class="report-pill report-pill--ok">Tamamlandı</span>
                                                @elseif($task->status === 'in-progress')
                                                    <span class="report-pill report-pill--warn">Devam ediyor</span>
                                                @else
                                                    <span class="report-pill report-pill--muted">{{ $task->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="report-empty">Bu aralıkta görev yok.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="report-panel h-100">
                        <div class="report-panel__head">Etkinlikler</div>
                        <div class="report-table-wrap">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Başlık</th>
                                        <th>Başlangıç</th>
                                        <th>Bitiş</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($report->events as $event)
                                        <tr>
                                            <td><a href="{{ route('events.show', $event) }}">{{ $event->title }}</a></td>
                                            <td class="text-nowrap">{{ $event->start_date?->format('d.m.Y H:i') ?? '—' }}</td>
                                            <td class="text-nowrap">{{ $event->end_date?->format('d.m.Y H:i') ?? '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="report-empty">Bu aralıkta etkinlik yok.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </details>
    </div>
</div>
@endsection
