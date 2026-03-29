@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0">Etkinlikler — tarih aralığı</h1>
        <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">Tüm etkinlikler</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('events.date-range') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Başlangıç tarihi</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $startDate) }}">
                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bitiş tarihi</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $endDate) }}">
                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Listele</button>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                        <tr>
                            <td><a href="{{ route('events.show', $event) }}" class="text-decoration-none">{{ $event->title }}</a></td>
                            <td>{{ $event->start_date?->format('d.m.Y H:i') }}</td>
                            <td>{{ $event->end_date?->format('d.m.Y H:i') }}</td>
                            <td>{{ $event->location ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center py-4">
                                @if(request()->hasAny(['start_date', 'end_date']))
                                    Bu aralıkta etkinlik bulunamadı.
                                @else
                                    Aralık seçip «Listele» ile arayın.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
