@extends('cms::backend.layout.app', ['title' => get_post_type('title_crud')])

@section('content')

        <form onsubmit="return false;">
            <div class="row">
                <div class="col-lg-12">
                    <h3 style="font-weight:normal">
                        <i class="fa {{ $module->icon }}"></i> {{ get_post_type('title_crud') }}

                        <div class="btn-group pull-right">
                            <button type="button" onclick="location.href='{{ route(get_post_type()) }}'"
                                class="btn btn-danger btn-sm">
                                <i class="fa fa-undo"></i> Kembali
                            </button>
                            <a href="{{ route($module->name . '.edit', $post->id) }}" class="btn btn-warning btn-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        </div>
                    </h3>
                    <br>

                    @if (!empty($post && $module->web->detail && $post->title && $post->status == 'publish') && $module->public)
                        <div style="border-left:3px solid green" class="alert alert-success">
                            <b>URL : </b>
                            <a href="{{ url($post->url) }}" target="_blank">
                                <u>{{ url($post->url) }}</u>
                            </a>
                        </div>
                    @endif

                </div>

                <div class="col-lg-9">

                    {{-- TITLE --}}
                    <div class="form-group">
                        <label>{{ $module->datatable->data_title }}</label>
                        <input type="text" class="form-control form-control-lg" value="{{ $post->title ?? '' }}" disabled>
                    </div>
                    @if ($pp = $module->form->post_parent)
                        <div class="form-group">
                            <label for="">{{ $module->form->post_parent['0'] }}</label><br>
                            <h6>{{ $post->parent?->title }} {{ $post->parent?->parent?->title ? ' - ' . $post->parent?->parent?->title : null }} {{ $post->parent?->parent?->parent ? ' - ' . $post->parent?->parent?->parent?->title : null }}</h6>
                        </div>
                    @endif
                    {{-- CONTENT --}}
                    @if ($module->form->editor)
                        <div class="form-group">
                            <h5>Isi:</h5>
                            <style>
                                .isi img {width:100%}
                            </style>
                           <div class="isi" style="max-height: 60vh;overflow:auto;padding:0 10px 0 0">
                            {!!$post->content ?? '' !!}
                           </div>
                        </div>
                    @endif

                    {{-- CUSTOM FIELD --}}
                    @if ($module->form->custom_field)
                        @include('cms::backend.posts.custom_field.form')
                    @endif

                    {{-- LOOPING DATA --}}
                    @if ($module->form->looping_data)
                        @include('cms::backend.posts.looping_data.form')
                    @endif

                </div>

                <div class="col-lg-3">

                    {{-- THUMBNAIL --}}
                    @if ($module->form->thumbnail)
                        <div class="card">
                            <p class="card-header">
                                <i class="fa fa-image"></i> Gambar
                            </p>

                            <img class="img-responsive" style="width:100%" src="{{ $post->thumbnail }}" />

                            @if ($module->web->index && $module->web->detail)
                                <div style="padding:10px">
                                    <label>Keterangan Gambar</label>
                                    <textarea class="form-control form-control-sm"
                                        disabled>{{ $post->media_description ?? '' }}</textarea>
                                </div>
                            @endif
                        </div>
                    @endif


                    {{-- REDIRECT --}}
                    @if ($module->web->detail || $module->name == 'banner')
                        <small>Pengalihan URL</small>
                        <input type="text" class="form-control form-control-sm" value="{{ $post->redirect_to ?? '' }}" disabled>

                        <small>Deskripsi</small>
                        <textarea class="form-control form-control-sm" disabled>{{ $post->description ?? '' }}</textarea>

                        <small>Kata Kunci</small>
                        <input type="text" class="form-control form-control-sm" value="{{ $post->keyword ?? '' }}" disabled>
                    @endif


                    {{-- CATEGORY --}}
                    @if ($module->form->category)
                        <small>Kategori</small>
                        <input type="text" class="form-control form-control-sm" value="{{ $post->category->name ?? '-' }}" disabled>
                    @endif


                    {{-- SORT --}}
                    @if ($module->web->sortable)
                        <small>Urutan</small>
                        <input type="text" class="form-control form-control-sm" value="{{ $post->sort ?? '' }}" disabled>
                    @endif


                    {{-- STATUS --}}
                    <div class="mt-3">
                        <small>Status</small>
                        <input type="text" class="form-control form-control-sm" value="{{ ucfirst($post->status) }}" disabled>
                    </div>

                    {{-- PINNED --}}
                    <div class="mt-2">
                        <small>Disematkan</small>
                        <input type="text" class="form-control form-control-sm"
                            value="{{ $post->pinned == 'Y' ? 'Ya' : 'Tidak' }}" disabled>
                    </div>

                    {{-- KOMENTAR --}}
                    @if ($module->web->detail)
                        <div class="mt-2">
                            <small>Izinkan Komentar</small>
                            <input type="text" class="form-control form-control-sm"
                                value="{{ $post->allow_comment == 'Y' ? 'Ya' : 'Tidak' }}" disabled>
                        </div>
                    @endif

                </div>
            </div>
        </form>
    @push('scripts')
        @include('cms::backend.layout.js')
            <script>
                document.addEventListener("DOMContentLoaded", function () {

                    // Disable semua input
                    document.querySelectorAll('input, textarea, select').forEach(function (el) {
                        el.setAttribute('disabled', true);
                    });

                    // Hilangkan file upload
                    document.querySelectorAll('input[type="file"]').forEach(function (el) {
                        el.remove();
                    });

                    // Hilangkan checkbox & radio agar tidak bisa klik
                    document.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (el) {
                        el.setAttribute('disabled', true);
                    });

                });
            </script>
    @endpush
@endsection