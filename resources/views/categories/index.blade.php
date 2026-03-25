@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 720px;">
    <h1 class="h4 mb-4">Kategoriler</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">Yeni kategori</div>
        <div class="card-body">
            <form method="post" action="{{ route('categories.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Ad</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="100">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Açıklama (isteğe bağlı)</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}" maxlength="500">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Renk</label>
                    <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', '#6c757d') }}" title="Etiket rengi">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Ekle</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <ul class="list-group list-group-flush">
            @forelse($categories as $cat)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        @if($cat->color)
                            <span class="rounded-circle d-inline-block border" style="width:14px;height:14px;background:{{ $cat->color }};" title="{{ $cat->color }}"></span>
                        @endif
                        <strong>{{ $cat->name }}</strong>
                        @if($cat->description)
                            <div class="small text-muted">{{ $cat->description }}</div>
                        @endif
                    </div>
                    <form method="post" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Silinsin mi?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Sil</button>
                    </form>
                </li>
            @empty
                <li class="list-group-item text-muted">Henüz kategori yok. Yukarıdan ekleyin.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
