@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 640px;">
    <h1 class="h4 mb-4">Yeni etkinlik</h1>
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('events.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Başlık</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    @error('description')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Başlangıç</label>
                        <input type="datetime-local" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                        @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bitiş</label>
                        <input type="datetime-local" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                        @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konum</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" maxlength="255">
                    @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                    <a href="{{ route('events.index') }}" class="btn btn-outline-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
