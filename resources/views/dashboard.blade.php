@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Başlık ve Tarih -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Hoş Geldiniz, {{ Auth::user()->name }}</h1>
        <div class="text-muted">{{ Carbon\Carbon::now()->locale('tr')->isoFormat('LL') }}</div>
    </div>

    <div class="row">
        <!-- İstatistikler -->
        <div class="col-12 mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                                        <i class="bi bi-calendar-event text-primary fs-4"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Yaklaşan Etkinlikler</h6>
                                    <h3 class="mb-0">{{ $upcomingEvents->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                                        <i class="bi bi-check2-square text-warning fs-4"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Bekleyen Görevler</h6>
                                    <h3 class="mb-0">{{ $pendingTasks->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yaklaşan Etkinlikler -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Yaklaşan Etkinlikler</h5>
                    <a href="{{ route('events.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Yeni Ekle
                    </a>
                </div>
                <div class="card-body">
                    @if($upcomingEvents->isEmpty())
                        <p class="text-muted text-center my-5">Yaklaşan etkinlik bulunmuyor.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($upcomingEvents as $event)
                                <div class="list-group-item border-0 ps-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-primary bg-opacity-10 p-2 rounded text-center" style="min-width: 60px;">
                                                <div class="small text-primary">{{ Carbon\Carbon::parse($event->start_date)->format('M') }}</div>
                                                <div class="fw-bold">{{ Carbon\Carbon::parse($event->start_date)->format('d') }}</div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $event->title }}</h6>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ Carbon\Carbon::parse($event->start_date)->format('H:i') }} - 
                                                {{ Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bekleyen Görevler -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Bekleyen Görevler</h5>
                    <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Yeni Ekle
                    </a>
                </div>
                <div class="card-body">
                    @if($pendingTasks->isEmpty())
                        <p class="text-muted text-center my-5">Bekleyen görev bulunmuyor.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($pendingTasks as $task)
                                <div class="list-group-item border-0 ps-0">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="task{{ $task->id }}">
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-1">{{ $task->title }}</h6>
                                            @if($task->due_date)
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    Son Tarih: {{ Carbon\Carbon::parse($task->due_date)->format('d.m.Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 