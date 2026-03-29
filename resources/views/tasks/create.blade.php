@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 640px;">
    <h1 class="h4 mb-4">Yeni görev</h1>
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('tasks.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Başlık</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Son tarih</label>
                    <input type="datetime-local" name="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}" required>
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Öncelik</label>
                        <select name="priority" class="form-select">
                            <option value="1" @selected(old('priority', '2') == '1')>Düşük</option>
                            <option value="2" @selected(old('priority', '2') == '2')>Orta</option>
                            <option value="3" @selected(old('priority', '2') == '3')>Yüksek</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="pending" @selected(old('status', 'pending') === 'pending')>Bekliyor</option>
                            <option value="in-progress" @selected(old('status') === 'in-progress')>Devam ediyor</option>
                            <option value="completed" @selected(old('status') === 'completed')>Tamamlandı</option>
                            <option value="cancelled" @selected(old('status') === 'cancelled')>İptal</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategoriler</label>
                    <select name="category_ids[]" class="form-select" multiple size="4">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(collect(old('category_ids', []))->contains($cat->id))>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Çoklu seçim için Ctrl/Cmd tuşunu kullanın.</div>
                </div>
                <div class="mb-3 form-check">
                    <input type="hidden" name="is_completed" value="0">
                    <input type="checkbox" name="is_completed" value="1" class="form-check-input" id="is_completed" @checked(old('is_completed'))>
                    <label class="form-check-label" for="is_completed">Tamamlandı</label>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
