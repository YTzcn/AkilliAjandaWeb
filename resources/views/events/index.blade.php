@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0">Etkinlikler</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('events.date-range') }}" class="btn btn-outline-primary">
                <i class="bi bi-calendar-range me-1"></i> Tarih aralığı
            </a>
            <a href="{{ route('events.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Yeni etkinlik
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="get" action="{{ route('events.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Arama</label>
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ $filters['q'] ?? '' }}" placeholder="Başlık veya notlarda ara">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Kategoriye göre</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == (string) $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrele</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('events.index') }}" class="btn btn-outline-secondary btn-sm w-100">Sıfırla</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Başlık</th>
                        <th>Başlangıç</th>
                        <th>Bitiş</th>
                        <th>Konum</th>
                        <th>Kategoriler</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td>
                                <a href="{{ route('events.show', $event) }}" class="text-decoration-none">{{ $event->title }}</a>
                            </td>
                            <td>{{ $event->start_date?->format('d.m.Y H:i') }}</td>
                            <td>{{ $event->end_date?->format('d.m.Y H:i') }}</td>
                            <td>{{ $event->location ?? '—' }}</td>
                            <td>
                                @forelse($event->categories as $c)
                                    <span class="badge rounded-pill me-1 border" @if($c->color) style="background-color: {{ $c->color }}22;border-color: {{ $c->color }}!important;color: #212529;" @endif>{{ $c->name }}</span>
                                @empty
                                    <span class="text-muted small">—</span>
                                @endforelse
                            </td>
                            <td class="text-end">
                                <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                                <form method="post" action="{{ route('events.destroy', $event) }}" class="d-inline" onsubmit="return confirm('Bu etkinliği silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted text-center py-4">Henüz etkinlik yok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
