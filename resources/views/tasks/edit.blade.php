@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 640px;">
    <h1 class="h4 mb-4">Görevi düzenle</h1>
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('tasks.update', $task) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Başlık</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $task->title) }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $task->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Son tarih</label>
                    <input type="datetime-local" name="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $task->due_date?->format('Y-m-d\TH:i')) }}" required>
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Öncelik</label>
                        <select name="priority" class="form-select">
                            @foreach([1 => 'Düşük', 2 => 'Orta', 3 => 'Yüksek'] as $v => $label)
                                <option value="{{ $v }}" @selected(old('priority', $task->priority) == $v)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="pending" @selected(old('status', $task->status) === 'pending')>Bekliyor</option>
                            <option value="in-progress" @selected(old('status', $task->status) === 'in-progress')>Devam ediyor</option>
                            <option value="completed" @selected(old('status', $task->status) === 'completed')>Tamamlandı</option>
                            <option value="cancelled" @selected(old('status', $task->status) === 'cancelled')>İptal</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategoriler</label>
                    <select name="category_ids[]" class="form-select" multiple size="4">
                        @php $selected = old('category_ids', $task->categories->pluck('id')->all()); @endphp
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(in_array($cat->id, $selected, true))>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 form-check">
                    <input type="hidden" name="is_completed" value="0">
                    <input type="checkbox" name="is_completed" value="1" class="form-check-input" id="is_completed" @checked(old('is_completed', $task->is_completed))>
                    <label class="form-check-label" for="is_completed">Tamamlandı</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">Geri</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
