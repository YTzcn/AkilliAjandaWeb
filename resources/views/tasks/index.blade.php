@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Görevler</h1>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Yeni görev</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('tasks.index') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted">Durum</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Bekliyor</option>
                        <option value="in-progress" @selected(($filters['status'] ?? '') === 'in-progress')>Devam ediyor</option>
                        <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Tamamlandı</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Öncelik</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="1" @selected(($filters['priority'] ?? '') == '1')>Düşük</option>
                        <option value="2" @selected(($filters['priority'] ?? '') == '2')>Orta</option>
                        <option value="3" @selected(($filters['priority'] ?? '') == '3')>Yüksek</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tamamlandı</label>
                    <select name="is_completed" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        <option value="0" @selected(($filters['is_completed'] ?? '') === '0' || ($filters['is_completed'] ?? '') === false)>Hayır</option>
                        <option value="1" @selected(($filters['is_completed'] ?? '') === '1' || ($filters['is_completed'] ?? '') === true)>Evet</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Kategori</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">Tümü</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Son tarih (başlangıç)</label>
                    <input type="date" name="due_from" class="form-control form-control-sm" value="{{ $filters['due_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Son tarih (bitiş)</label>
                    <input type="date" name="due_to" class="form-control form-control-sm" value="{{ $filters['due_to'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Sırala</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="due_date" @selected(($filters['sort'] ?? 'due_date') === 'due_date')>Son tarih</option>
                        <option value="priority" @selected(($filters['sort'] ?? '') === 'priority')>Öncelik</option>
                        <option value="created_at" @selected(($filters['sort'] ?? '') === 'created_at')>Oluşturulma</option>
                        <option value="title" @selected(($filters['sort'] ?? '') === 'title')>Başlık</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Yön</label>
                    <select name="dir" class="form-select form-select-sm">
                        <option value="asc" @selected(($filters['dir'] ?? 'asc') === 'asc')>Artan</option>
                        <option value="desc" @selected(($filters['dir'] ?? '') === 'desc')>Azalan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrele</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm w-100">Sıfırla</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Başlık</th>
                        <th>Son tarih</th>
                        <th>Öncelik</th>
                        <th>Durum</th>
                        <th>Tamamlandı</th>
                        <th>Kategoriler</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>
                                <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none fw-medium">{{ $task->title }}</a>
                            </td>
                            <td>{{ $task->due_date ? $task->due_date->format('d.m.Y H:i') : '—' }}</td>
                            <td>
                                @php $pl = [1 => 'Düşük', 2 => 'Orta', 3 => 'Yüksek']; @endphp
                                <span class="badge bg-secondary-subtle text-secondary">{{ $pl[$task->priority] ?? $task->priority }}</span>
                            </td>
                            <td>{{ $task->status }}</td>
                            <td>{{ $task->is_completed ? 'Evet' : 'Hayır' }}</td>
                            <td>
                                @foreach($task->categories as $c)
                                    <span class="badge bg-light text-dark border">{{ $c->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Görev bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
