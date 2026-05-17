@extends('cms::backend.layout.app', ['title' => $theme ? 'Edit Tema' : 'Tambah Tema'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-paint-brush" aria-hidden="true"></i> {{ $theme ? 'Edit Tema' : 'Tambah Tema' }}</h3>
            <div class="pull-right">
                <a href="{{ route('theme.index') }}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Batal</a>
            </div>
        </div>
        <div class="col-lg-12">
            @include('cms::backend.layout.error')
            <form autocomplete="off" action="{{ $theme ? route('theme.update', $theme->id) : route('theme.store') }}" method="post">
                @csrf
                @if($theme)
                    @method('PUT')
                @endif
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white">Informasi Tema</div>
                    <div class="card-body">
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Nama Tema</label>
                            <input class="form-control form-control-sm" name="name" type="text" placeholder="Masukkan Nama Tema" value="{{ $theme ? $theme->name : old('name') }}" required>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Path (Folder Name)</label>
                            <input class="form-control form-control-sm" name="path" type="text" placeholder="Contoh: tema-baru" value="{{ $theme ? $theme->path : old('path') }}" required {{ $theme ? 'readonly' : '' }}>
                            <small class="text-muted">Folder akan dibuat di <code>resource_path('views/template/')</code></small>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Git URL (GitHub Repository)</label>
                            <input class="form-control form-control-sm" name="git" type="url" placeholder="https://github.com/user/repo" value="{{ $theme ? $theme->git : old('git') }}" required>
                            <small class="text-muted">Pastikan repository publik dan menggunakan branch <code>main</code> atau <code>master</code></small>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Preview URL (Optional)</label>
                            <input class="form-control form-control-sm" name="preview" type="text" placeholder="https://demo.tema.com" value="{{ $theme ? $theme->preview : old('preview') }}">
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Status</label><br>
                            @foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif'] as $key => $val)
                                <input name="status" type="radio" value="{{ $key }}" {{ (($theme && $theme->status == $key) || old('status', 'active') == $key) ? 'checked' : '' }}> {{ $val }} &nbsp; &nbsp;
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-group mt-2 mb-2 text-right">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ $theme ? 'Update Tema' : 'Simpan & Download Tema' }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
