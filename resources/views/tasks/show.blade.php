@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 640px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ $task->title }}</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-primary">Düzenle</a>
            <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary">Liste</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-2">{{ $task->description ?: 'Açıklama yok.' }}</p>
            <p><strong>Son tarih:</strong> {{ $task->due_date?->format('d.m.Y H:i') }}</p>
            <p><strong>Öncelik:</strong> {{ $task->priority }} &nbsp; <strong>Durum:</strong> {{ $task->status }}</p>
            <p><strong>Kategoriler:</strong>
                @forelse($task->categories as $c)
                    <span class="badge bg-secondary">{{ $c->name }}</span>
                @empty
                    —
                @endforelse
            </p>
        </div>
    </div>
</div>
@endsection
