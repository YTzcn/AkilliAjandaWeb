@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 640px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $event->title }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('events.edit', $event) }}" class="btn btn-outline-primary btn-sm">Düzenle</a>
            <a href="{{ route('events.index') }}" class="btn btn-outline-secondary btn-sm">Listeye dön</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Başlangıç</dt>
                <dd class="col-sm-8">{{ $event->start_date?->format('d.m.Y H:i') }}</dd>
                <dt class="col-sm-4 text-muted">Bitiş</dt>
                <dd class="col-sm-8">{{ $event->end_date?->format('d.m.Y H:i') }}</dd>
                @if($event->location)
                    <dt class="col-sm-4 text-muted">Konum</dt>
                    <dd class="col-sm-8">{{ $event->location }}</dd>
                @endif
                @if($event->description)
                    <dt class="col-sm-4 text-muted">Açıklama</dt>
                    <dd class="col-sm-8">{{ $event->description }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
