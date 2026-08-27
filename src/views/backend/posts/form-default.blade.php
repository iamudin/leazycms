@extends('cms::backend.layout.app', ['title' => get_post_type('title_crud')])

@section('content')
<style>
    /* Modern Editor Layout Styles */
    .editor-top-bar {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        padding: 10px 16px;
        margin-bottom: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }
    .editor-card {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        padding: 14px 16px;
        margin-bottom: 14px;
        transition: all 0.2s ease;
    }
    .editor-sidebar-card {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        padding: 12px 14px;
        margin-bottom: 12px;
    }
    .editor-sidebar-card-header {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
    }
    .btn-back-soft {
        background: #e0685fff;
        color: #ffffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 7px 16px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s;
    }
    .btn-back-soft:hover {
        background: #e2e8f0;
        color: #1e293b;
        text-decoration: none;
    }
    .post-url-card {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 8px 14px;
        color: #166534;
        font-size: 13px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
    }
    .post-url-card a {
        color: #15803d;
        font-weight: 600;
        text-decoration: none;
    }
    .post-url-card a:hover {
        text-decoration: underline;
    }
    .copy-url-badge {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11.5px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .copy-url-badge:hover {
        background: #bbf7d0;
    }
    .title-box-container {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 14px;
        transition: all 0.2s ease;
    }
    .title-box-container:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }
    .autosize-title {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        font-size: 19px !important;
        font-weight: 700 !important;
        color: #0f172a !important;
        line-height: 1.4 !important;
        background: transparent !important;
    }
    .autosize-title::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }
    .status-toggle-card {
        background: #ffffff;
        border: 1px solid #eef2f6;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        padding: 5px;
        margin-bottom: 12px;
    }
    .status-toggle-group {
        display: flex;
        background: #f1f5f9;
        border-radius: 8px;
        padding: 3px;
        gap: 3px;
        border: none;
        width: 100%;
    }
    .status-toggle-btn {
        flex: 1;
        border: none !important;
        border-radius: 6px !important;
        padding: 8px 10px !important;
        font-size: 12.5px !important;
        font-weight: 700 !important;
        color: #64748b !important;
        background: transparent !important;
        box-shadow: none !important;
        text-align: center;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        user-select: none;
        margin: 0 !important;
        line-height: 1.2;
    }
    .status-toggle-btn:hover:not(.active) {
        color: #1e293b !important;
        background: rgba(255, 255, 255, 0.6) !important;
    }
    .status-toggle-btn.active.btn-publish {
        background: #10b981 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25) !important;
    }
    .status-toggle-btn.active.btn-draft {
        background: #f7a63dff !important;
        color: #334155 !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06) !important;
        border: 1px solid #e2e8f0 !important;
    }
    .soft-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }
    .soft-control {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 8px !important;
        font-size: 13px !important;
        padding: 8px 12px !important;
        color: #1e293b !important;
        background-color: #ffffff !important;
        transition: all 0.2s ease;
    }
    .soft-control:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12) !important;
        outline: none !important;
    }
    .thumbnail-box {
        position: relative;
        background: #f8fafc;
        border-radius: 10px;
        overflow: hidden;
        border: 1.5px dashed #cbd5e1;
    }
    .note-editor.note-frame {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 12px !important;
        overflow: hidden;
    }
    .note-editor .note-toolbar {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 8px 12px !important;
    }
    .select2-container--default .select2-selection--single {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 8px !important;
        height: 36px !important;
        padding: 4px 8px !important;
        background-color: #ffffff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        font-size: 13px !important;
        line-height: 26px !important;
        padding-left: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-right: 18px !important;
        color: #ef4444 !important;
        font-weight: bold !important;
    }
    .select2-dropdown {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08) !important;
        font-size: 13px !important;
        overflow: hidden !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6 !important;
    }
    .select2-container--default .select2-selection--multiple {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 8px !important;
        min-height: 36px !important;
        padding: 2px 6px !important;
    }
    .app-content {
        background-color: #f0f0f0ff !important;
    }
</style>

<form class="editorForm" action="{{ route(get_post_type() . '.update', $post->id) }}" method="POST" enctype="multipart/form-data">
    <div class="row">
        {{-- Top Header Section --}}
        <div class="col-lg-12">
            <div class="editor-top-bar d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-lg p-2 mr-2" style="width: 36px; height: 36px; border-radius: 8px;">
                        <i class="fa {{ $module->icon }} font-size-16"></i>
                    </div>
                    <div>
                        <h4 class="m-0 font-weight-bold text-dark" style="font-size: 17px; letter-spacing: -0.2px;">
                            {{ get_post_type('title_crud') }}
                        </h4>
                        <small class="text-muted">{{ $module->title }}</small>
                    </div>
                </div>
                <div class="btn-group">
                    @if(View::exists('template.' . template() . '.' . $post->type . '.' . $post->slug))
                        <a href="{{ route('appearance.editor') . '?edit=' . enc64('/' . $post->type . '/' . $post->slug . '.blade.php') }}"
                            class="btn btn-warning btn-sm mr-2" style="border-radius: 8px; font-weight: 600;">
                            <i class="fa fa-edit mr-1"></i> Edit Halaman
                            {!! help('Tombol ini akan muncul ketika ' . $module->title . ' ini memiliki custom page pada tampilan. Klik untuk mulai mengedit') !!}
                        </a>
                    @endif
                    <button type="button" onclick="location.href='{{ route(get_post_type()) }}'" class="btn btn-back-soft" data-toggle="tooltip" title="Kembali Ke Index Data">
                        <i class="fa fa-arrow-left mr-1"></i> Kembali
                    </button>
                </div>
            </div>

            @php
                $showUrl = !empty($post && $module->web->detail && $post->title && $post->status == 'publish') && $module->public;
                $postUrl = config('modules.multisite_enabled') && $post->tenant ? 'https://' . $post->tenant->domain . '/' . $post->url : url($post->url);
            @endphp

            @if($module->web->detail && $module->public)
                <div id="post-url-bar" class="post-url-card" style="{{ $showUrl ? '' : 'display:none;' }}">
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <span class="badge badge-success px-2 py-1 mr-2" style="border-radius: 6px; font-size: 11px;">URL</span>
                        <a title="Kunjungi URL" data-toggle="tooltip" href="{{ $postUrl }}" target="_blank" class="post-url-link">
                            <i class="url">{{ $showUrl ? str($postUrl)->limit(150, ' ...') : '' }}</i>
                        </a>
                        <span class="custom-url"></span>
                        <i class="fa fa-edit text-success ml-2 pointer" style="cursor: pointer;" data-post-url="{{ $postUrl }}" data-slug="{{ $post->slug }}" onclick="enableCustomSlugEdit(this.dataset.postUrl, this.dataset.slug)"></i>
                    </div>
                    <div>
                        <span title="Klik Untuk Menyalin alamat URL {{ $module->title }}" data-toggle="tooltip" class="pointer copy copy-url-badge" data-copy="{{ $postUrl }}">
                            <i class="fa fa-copy mr-1"></i> Salin URL
                        </span>
                    </div>
                </div>

                @push('scripts')
                    <script>
                        function enableCustomSlugEdit(postUrl, slug) {
                            const urlElement = document.querySelector('.url');
                            const customUrlElement = document.querySelector('.custom-url');
                            const editButton = document.querySelector('.post-url-card .fa-edit');

                            const baseUrl = postUrl.replace(slug, '');
                            urlElement.innerHTML = baseUrl;

                            customUrlElement.innerHTML = `
                                <input type='text' name='custom_slug' autofocus
                                    style='border: 1px solid #86efac; border-radius: 6px; color: #166534; padding: 2px 6px; width: 260px; background: #fff; font-size: 13px;'
                                    value='${slug}'
                                    maxlength='100'
                                    oninput="validateAndUpdateSlug('${baseUrl}', this)">
                                <i class="fa fa-check text-success ml-2 pointer" onclick="finalizeSlugEdit('${baseUrl}', this)" style="cursor:pointer;"></i>
                            `;

                            if (editButton) {
                                editButton.style.display = 'none';
                            }
                        }

                        function validateAndUpdateSlug(baseUrl, inputElement) {
                            let newSlug = inputElement.value.replace(/[^a-z\-\^0-9]/g, '');
                            if (newSlug && !/^[a-z0-9]/.test(newSlug[0])) {
                                newSlug = newSlug.slice(1);
                            }
                            while (/--/.test(newSlug)) {
                                newSlug = newSlug.replace(/--/g, '-');
                            }
                            if (newSlug.length > 100) {
                                newSlug = newSlug.slice(0, 100);
                            }
                            inputElement.value = newSlug;

                            const urlElement = document.querySelector('.url');
                            urlElement.innerHTML = `${baseUrl}`;
                        }

                        function finalizeSlugEdit(baseUrl, checkButton) {
                            const inputElement = document.querySelector('.custom-url input');
                            const editButton = document.querySelector('.post-url-card .fa-edit');

                            if (inputElement) {
                                let slug = inputElement.value;
                                if (slug.endsWith('-')) {
                                    slug = slug.slice(0, -1);
                                }

                                inputElement.value = slug;
                                inputElement.setAttribute('type', 'hidden');
                            }

                            if (checkButton) {
                                checkButton.style.display = 'none';
                            }

                            if (editButton) {
                                editButton.style.display = 'inline';
                            }

                            const urlElement = document.querySelector('.url');
                            const slug = inputElement.value;
                            urlElement.innerHTML = `${baseUrl}${slug}`;
                        }
                    </script>
                @endpush
            @endif

            @include('cms::backend.layout.error')

            <!-- Mobile Status Toggle (Visible only on small screens) -->
            <div class="d-block d-lg-none mb-3">
                <div class="status-toggle-card">
                    <div class="status-toggle-group" data-toggle="buttons">
                        <label onclick="handleStatusSubmit(this)" class="status-toggle-btn btn-publish {{ (!$post || $post->status == 'publish') ? 'active' : '' }}">
                            <input type="radio" name="status" value="publish" {{ (!$post || $post->status == 'publish') ? 'checked' : '' }} required style="display:none;">
                            <i class="fa fa-globe"></i> Publikasikan
                        </label>
                        <label onclick="handleStatusSubmit(this)" class="status-toggle-btn btn-draft {{ ($post && $post->status == 'draft') ? 'active' : '' }}">
                            <input type="radio" name="status" value="draft" {{ ($post && $post->status == 'draft') ? 'checked' : '' }} required style="display:none;">
                            <i class="fa fa-archive"></i> Draft
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Left Column: Content, Title, Custom Fields --}}
        <div class="col-lg-9">
            {{-- Title Input Card --}}
            <div class="title-box-container">
                @if(isset($module->form?->editable_title) && $module->form?->editable_title == true || !isset($module->form?->editable_title))
                    <textarea data-toggle="tooltip" minlength="5" maxlength="200" title="Masukkan {{ $module->datatable->data_title }}" required name="title"
                        placeholder="Masukkan {{ $module->datatable->data_title }}..." rows="1"
                        class="form-control autosize-title"
                        style="resize: none; overflow: hidden; min-height: 38px;">{{ !empty(old('title')) ? old('title') : ($post->title ?? null) }}</textarea>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <small class="text-muted title-info-text" style="font-size: 11.5px;">
                            <i class="fa fa-info-circle text-primary"></i> Minimal 5 karakter (diluar spasi) & maksimal 200 karakter
                        </small>
                        <small id="title-char-counter" class="text-muted font-weight-bold" style="font-size: 11.5px;">
                            <span id="title-char-count">0</span>/200
                        </small>
                    </div>
                @else
                    <input type="hidden" name="title" value="{{ $post->title ?? null }}">
                    <small class="text-muted">{{ $module->datatable->data_title }}</small>
                    <h3 class="font-weight-bold text-dark mt-1">{{ $post->title ?? null }}</h3>
                @endif
            </div>

            {{-- Main Editor Card --}}
            @if ($module->form->editor)
                    @if (isset($module->form->ai_generator) && $module->form->ai_generator)
                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" style="border-radius: 8px; font-weight: 600;" data-toggle="modal" data-target="#promptGeneratorModal" onclick="$('#prompt_topic').val($('[name=title]').val())">
                            <i class="fa fa-magic mr-1"></i> AI Prompt Generator
                        </button>
                    @endif
                <div class="editor-card p-0">

                    @php
                        $isTenantOnMainDomain = config('modules.multisite_enabled') && !empty($post) && !empty($post->tenant_id) && is_main_domain() && $post->tenant;
                    @endphp
                    @if($post->type == 'docs')
                        @php 
                            $type = "application/x-httpd-php"; 
                            $content = $post->content ?? '';
                            if ($isTenantOnMainDomain) {
                                $content = preg_replace('/src="\/media\//i', 'src="https://' . $post->tenant->domain . '/media/', $content);
                            }
                        @endphp
                        <textarea name="content" placeholder="Dokumentasi..." id="editor" class="custom_html">{{ $content }}</textarea>
                        @include('cms::backend.layout.codemirrorjs')
                    @else
                        @php
                            $content = !empty(old('content')) ? old('content') : ($post->content ?? '');
                            if ($isTenantOnMainDomain) {
                                $content = preg_replace('/src="\/media\//i', 'src="https://' . $post->tenant->domain . '/media/', $content);
                            }
                        @endphp
                        <textarea name="content" placeholder="Keterangan lengkap..." id="editor">{{ $content }}</textarea>
                    @endif
                </div>
            @endif

            {{-- Post Parent Card --}}
            @if ($pp = $module->form->post_parent)
                <?php
                if (isset($pp[1])) {
                    if (isset($pp[2]) && $pp[2] != 'all') {
                        $par = query()->withwherehas('category', function ($q) use ($pp) {
                            $q->where('slug', $pp[2]);
                        })
                            ->whereType($pp[1])
                            ->with('parent.parent.parent')
                            ->published()
                            ->select('id', 'title', 'parent_id')
                            ->whereNotIn('id', [$post->id])
                            ->get();
                    } else {
                        $par = query()->whereType($pp[1])
                            ->with('parent.parent.parent')
                            ->published()
                            ->select('id', 'title', 'parent_id')
                            ->whereNotIn('id', [$post->id])
                            ->get();
                    }
                }
                ?>
                <div class="editor-card">
                    <label class="soft-label">{{ $pp[0] }}</label>
                    <select @if (isset($pp[3]) && $pp[3] == 'required') required @endif data-live-search="true"
                        class="selectpicker form-control soft-control" name="parent_id">
                        <option value="">-- Pilih --</option>
                        @foreach ($par as $row)
                            <option @if ($post && $post->parent_id == $row->id) selected @endif value="{{ $row->id }}">
                                {{ $row->title }}
                                {{ $row->parent ? ' - ' . $row->parent->title . ($row->parent->parent ? ' - ' . $row->parent->parent->title : '') : ''}}
                            </option>
                        @endforeach
                    </select>
                </div>
                @push('styles')
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
                @endpush
                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-*.min.js"></script>
                @endpush
            @endif

            {{-- Custom Fields Container --}}
            @if ($module->form->custom_field)
                <div id="custom-fields-container" class="editor-card p-3">
                    @include('cms::backend.posts.custom_field.form')
                </div>
            @endif

            {{-- Looping Data Container --}}
            @if ($module->form->looping_data)
                <div id="looping-data-container" class="editor-card p-3">
                    @include('cms::backend.posts.looping_data.form')
                </div>
            @endif
        </div>

        {{-- Right Column: Sticky Sidebar --}}
        <div class="col-lg-3">
            <div class="sticky-sidebar-content" style="position: -webkit-sticky; position: sticky; top: 65px; z-index: 10;">
                
                {{-- Desktop Status Toggle --}}
                <div class="d-none d-lg-block mb-3">
                    <div class="status-toggle-card">
                        <div class="status-toggle-group" data-toggle="buttons">
                            <label onclick="handleStatusSubmit(this)" class="status-toggle-btn btn-publish {{ (!$post || $post->status == 'publish') ? 'active' : '' }}">
                                <input type="radio" name="status" value="publish" {{ (!$post || $post->status == 'publish') ? 'checked' : '' }} required style="display:none;">
                                <i class="fa fa-globe"></i> Publikasikan
                            </label>
                            <label onclick="handleStatusSubmit(this)" class="status-toggle-btn btn-draft {{ ($post && $post->status == 'draft') ? 'active' : '' }}">
                                <input type="radio" name="status" value="draft" {{ ($post && $post->status == 'draft') ? 'checked' : '' }} required style="display:none;">
                                <i class="fa fa-archive"></i> Draft
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Thumbnail Card --}}
                @if ($module->form->thumbnail)
                    <div class="editor-sidebar-card">
                        <div class="editor-sidebar-card-header">
                            <i class="fa fa-image text-primary"></i> Foto {{ current_module()->title }}
                        </div>
                        
                        <div class="thumbnail-box">
                            <img class="img-responsive w-100" style="width: 100%; height: auto; display: block; margin: 0; padding: 0;" id="thumb" src="{{ $post->thumbnail }}" />
                            <div class="upload-overlay">
                                <input accept="image/png,image/jpeg,image/webp,image/gif" type="file"
                                    class="compress-image form-control-file form-control-sm" name="media" value="" style="display: none;">
                            </div>
                        </div>

                        <style>
                            .upload-overlay {
                                position: absolute;
                                top: 0;
                                left: 0;
                                right: 0;
                                bottom: 0;
                                padding: 0 !important;
                                margin: 0 !important;
                                pointer-events: none;
                            }
                            .upload-overlay .global-file-wrapper {
                                position: absolute;
                                top: 10px;
                                right: 10px;
                                margin: 0 !important;
                                padding: 0 !important;
                                display: flex;
                                z-index: 10;
                                pointer-events: auto;
                            }
                            .upload-overlay .btn-open-gmedia {
                                width: 36px;
                                height: 36px;
                                border-radius: 50%;
                                padding: 0;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0;
                                background: rgba(255, 255, 255, 0.95);
                                border: 1px solid #cbd5e1;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                                color: #1e293b;
                                transition: all 0.2s;
                                cursor: pointer;
                            }
                            .upload-overlay .btn-open-gmedia:hover {
                                background: #ffffff;
                                color: #2563eb;
                            }
                            .upload-overlay .btn-open-gmedia i {
                                font-size: 14px;
                                margin: 0;
                            }
                            .upload-overlay .btn-open-gmedia i.fa-folder-open::before {
                                content: "\f040"; /* Pencil icon */
                            }
                            .upload-overlay .btn-clear-gmedia {
                                width: 36px;
                                height: 36px;
                                border-radius: 50%;
                                padding: 0;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                background: rgba(255, 255, 255, 0.95);
                                border: 1px solid #cbd5e1;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                                margin-left: 6px !important;
                                transition: all 0.2s;
                                cursor: pointer;
                            }
                            .upload-overlay .btn-clear-gmedia:hover {
                                background: #ffffff;
                                color: #ef4444;
                            }
                            .upload-overlay .media-preview-area {
                                display: none !important;
                            }
                        </style>

                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                $('#thumb').data('original-src', $('#thumb').attr('src'));
                                
                                $('.upload-overlay').on('change', 'input[type="file"]', function() {
                                    if (this.files && this.files.length > 0) {
                                        let file = this.files[0];
                                        if (file.type.startsWith('image/')) {
                                            $('#thumb').attr('src', URL.createObjectURL(file));
                                        }
                                    }
                                });
                                
                                $('.upload-overlay').on('click', '.btn-clear-gmedia', function() {
                                    $('#thumb').attr('src', $('#thumb').data('original-src'));
                                });
                                
                                let observer = new MutationObserver(function(mutations) {
                                    mutations.forEach(function(mutation) {
                                        if (mutation.addedNodes) {
                                            mutation.addedNodes.forEach(function(node) {
                                                if (node.tagName === 'INPUT' && node.classList.contains('gmedia-hidden')) {
                                                    let val = node.value;
                                                    let ext = val.split('.').pop().toLowerCase();
                                                    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'].includes(ext)) {
                                                        $('#thumb').attr('src', val);
                                                    }
                                                }
                                            });
                                        }
                                    });
                                });
                                
                                let overlayNode = document.querySelector('.upload-overlay');
                                if (overlayNode) {
                                    observer.observe(overlayNode, { childList: true, subtree: true });
                                }
                            });
                        </script>

                        @if ($module->web->detail || $module->name == 'banner')
                            <div class="mt-2 pt-2 border-top">
                                <textarea maxlength="200" placeholder="Keterangan Gambar..." rows="2" class="form-control soft-control"
                                    name="media_description">{{ !empty(old('media_description')) ? old('media_description') : ($post->media_description ?? '') }}</textarea>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Redirect & SEO Card --}}
                @if ($module->web->detail || $modname = $module->name == 'banner')
                    <div class="editor-sidebar-card">
                        <div class="form-group mb-0">
                            <label class="soft-label">
                                Pengalihan URL {!! help('Opsi Jika Ingin Mengalihkan Konten Ini ke suatu halaman web atau url') !!}
                            </label>
                            <input type="url" id="redirect_to" class="form-control soft-control" name="redirect_to"
                                placeholder="https://"
                                value="{{ !empty(old('redirect_to')) ? old('redirect_to') : ($post->redirect_to ?? '') }}"
                                oninput="validateRedirectUrl(this)" pattern="https?://.+">
                            <small class="text-danger mt-1" id="redirect_error" style="display:none;">Wajib diawali http:// atau https://</small>
                        </div>
                    </div>
                    
                    <script>
                        function validateRedirectUrl(input) {
                            let val = input.value.trim();
                            if (val === '') {
                                $('#redirect_error').hide();
                                $(input).removeClass('is-invalid');
                                return;
                            }
                            if (val.length > 0 && !val.toLowerCase().startsWith('h')) {
                                input.value = 'http://' + val;
                                val = input.value;
                            }
                            const regex = /^https?:\/\/[^\s/$.?#].[^\s]*$/i;
                            if (regex.test(val)) {
                                $('#redirect_error').hide();
                                $(input).removeClass('is-invalid');
                            } else {
                                $('#redirect_error').show();
                                $(input).addClass('is-invalid');
                            }
                        }
                    </script>

                    @if(!isset($modname))
                        <div class="editor-sidebar-card">
                            <div class="editor-sidebar-card-header">
                                <i class="fa fa-search text-primary"></i> SEO Meta (Opsional)
                            </div>
                            <div class="form-group mb-2">
                                <label class="soft-label">Deskripsi {!! help('Opsi deskripsi singkat tentang konten yang dapat ditelusuri oleh mesin pencarian') !!}</label>
                                <textarea maxlength="200" placeholder="Tulis deskripsi ringkas..." rows="2" class="form-control soft-control"
                                    name="description">{{ !empty(old('description')) ? old('description') : ($post->description ?? '') }}</textarea>
                            </div>
                            <div class="form-group mb-0">
                                <label class="soft-label">Kata Kunci {!! help('Kata kunci tentang konten yang dapat ditelusuri oleh mesin pencarian') !!}</label>
                                <input placeholder="Keyword1, Keyword2..." type="text" class="form-control soft-control"
                                    name="keyword" value="{{ !empty(old('keyword')) ? old('keyword') : ($post->keyword ?? '') }}">
                            </div>
                        </div>
                    @endif

                    @if ($module->form->tag)
                        <div class="editor-sidebar-card">
                            <label class="soft-label">Tags {!! help('Penanda untuk memudahkan pencarian topik') !!}</label>
                            <select name="tags[]" id="select2" class="form-control form-control-select" multiple>
                                @foreach($tags as $row)
                                    <option {{ in_array($row->id, $post->tags->pluck('id')->toArray()) ? 'selected' : '' }}
                                        value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endif

                {{-- Category Card --}}
                @if ($module->form->category)
                    <div class="editor-sidebar-card">
                        <div class="editor-sidebar-card-header">
                            <i class="fa fa-folder text-primary"></i> Kategori {{ $module->title }}
                        </div>
                        @php
                            $dbCategories = config('modules.multisite_enabled') ? (is_main_domain() ? $category->load('tenant') : $category->where('tenant_id', tenant()->id)) : $category;
                            $defaultCategories = config('modules.default_category.' . $post->type, []);
                            $dbCategoryNames = $dbCategories->pluck('name')->map(fn($n) => strtolower($n))->toArray();
                            $unregisteredDefaults = array_filter($defaultCategories, fn($name) => !in_array(strtolower($name), $dbCategoryNames));
                        @endphp

                        <div class="form-group mb-2">
                            <select class="form-control select2-category" name="category_id" id="category_select2" style="width: 100%;">
                                <option value="">-- Pilih Kategori --</option>
                                @if($dbCategories->count() > 0)
                                    @foreach ($dbCategories as $row)
                                        <option value="{{ $row->id }}" 
                                            {{ $row->id == $post->category_id ? 'selected' : '' }}
                                            {{ config('modules.multisite_enabled') && is_main_domain() && $row->tenant_id !== $post->tenant_id ? 'disabled' : '' }}>
                                            {{ $row->name }}
                                            @if(config('modules.multisite_enabled') && is_main_domain() && $row->tenant_id)
                                                - [{{ $row->tenant->domain ?? 'Unknown Domain' }}]
                                            @endif
                                        </option>
                                    @endforeach
                                @endif

                                @if(count($unregisteredDefaults) > 0)
                                    @foreach ($unregisteredDefaults as $defCatName)
                                        <option value="{{ $defCatName }}" {{ (old('category_id') == $defCatName || (isset($post->category_id) && !is_numeric($post->category_id) && $post->category_id == $defCatName)) ? 'selected' : '' }}>
                                            {{ $defCatName }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="d-flex align-items-center justify-content-between pt-1">
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#addCategoryModal" class="text-primary font-weight-bold" style="text-decoration: none; font-size: 11.5px;">
                                <i class="fa fa-plus-circle mr-1"></i> Tambah Baru
                            </a>
                            <a href="{{ route($post->type.'.category') }}" class="text-muted" style="text-decoration: none; font-size: 11.5px;">
                                <i class="fa fa-cog mr-1"></i> Kelola Kategori
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Sort & Switches Card --}}
                <div class="editor-sidebar-card">
                    @if ($module->web->sortable)
                        <div class="form-group mb-3">
                            <label class="soft-label">Urutan {!! help('Urutan konten yang akan ditampilkan') !!}</label>
                            <select class="form-control soft-control" name="sort">
                                @php $count = query()->onType(get_post_type())->count();@endphp
                                @for ($i = 1; $i <= $count; $i++)
                                    <option value="{{ $i }}" {{ $post->sort == $i ? 'selected=selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    @endif

                    @if ($module->web->detail)
                        <div class="custom-control custom-switch mb-2.5">
                            <input type="checkbox" class="custom-control-input" id="switch-password" name="password" value="Y" {{ $post && !empty($post->password) ? 'checked=checked' : '' }}>
                            <label class="custom-control-label font-weight-600 text-dark small" for="switch-password">
                                Batasi Akses (PIN) {!! help('Jika dicentang, Pengunjung wajib memasukkan kode PIN utk melihat. Klik icon merah disamping untuk menyalin kode rahasia') !!}
                            </label>
                            @if(!empty($post->password))
                                <i class="fa fa-copy copy text-danger pointer ml-1" title="Klik untuk menyalin kode" data-copy="{{ dec64($post->password) }}" style="cursor:pointer;"></i>
                            @endif
                        </div>
                        <div class="custom-control custom-switch mb-2.5">
                            <input type="checkbox" class="custom-control-input" id="switch-comment" name="allow_comment" value="Y"
                                {{ $post && $post->allow_comment == 'Y' ? 'checked=checked' : '' }}>
                            <label class="custom-control-label font-weight-600 text-dark small" for="switch-comment">
                                Izinkan Komentar {!! help('Jika dicentang, maka pengunjung bisa mengirim komentar pada postingan ini') !!}
                            </label>
                        </div>
                    @endif

                    <div class="custom-control custom-switch mb-1">
                        <input type="checkbox" class="custom-control-input" id="switch-pinned" name="pinned" value="Y" {{ $post && $post->pinned == 'Y' ? 'checked=checked' : '' }}>
                        <label class="custom-control-label font-weight-600 text-dark small" for="switch-pinned">
                            Sematkan {!! help('Jika dicentang, maka postingan ini akan menjadi prioritas dihalaman jika dikondisikan pada template') !!}
                        </label>
                    </div>
                </div>

            </div>
        </div>
    </div>
</form>

@if ($post->mime != 'html' && $post->type != 'docs' && $module->form->editor)
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    @endpush
    @include('cms::backend.layout.summernote')
@endif

@push('scripts')
    <script>
        function updateTitleInfo() {
            let el = document.querySelector('textarea[name="title"]');
            if (!el) return;

            // Auto resize
            el.style.height = 'auto';
            let borderOffset = el.offsetHeight - el.clientHeight;
            el.style.height = (el.scrollHeight + borderOffset) + 'px';

            // Character count & validation info
            let val = el.value || '';
            let totalLen = val.length;
            let nonSpaceLen = val.replace(/\s+/g, '').length;

            let counterEl = document.getElementById('title-char-count');
            let helperEl = document.querySelector('.title-info-text');

            if (counterEl) {
                counterEl.textContent = totalLen;
                if (totalLen >= 200) {
                    $('#title-char-counter').removeClass('text-muted text-success').addClass('text-danger font-weight-bold');
                } else {
                    $('#title-char-counter').removeClass('text-danger font-weight-bold').addClass('text-muted');
                }
            }

            if (helperEl) {
                if (totalLen === 0) {
                    helperEl.className = 'text-muted title-info-text';
                    helperEl.innerHTML = '<i class="fa fa-info-circle text-primary"></i> Minimal 5 karakter (diluar spasi) & maksimal 200 karakter';
                } else if (nonSpaceLen < 5) {
                    helperEl.className = 'text-danger title-info-text';
                    helperEl.innerHTML = '<i class="fa fa-exclamation-circle"></i> Minimal 5 karakter diluar spasi (saat ini: ' + nonSpaceLen + ' karakter)';
                } else if (totalLen >= 200) {
                    helperEl.className = 'text-warning title-info-text';
                    helperEl.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Mencapai batas maksimal 200 karakter';
                } else {
                    helperEl.className = 'text-muted title-info-text';
                    helperEl.innerHTML = '<i class="fa fa-info-circle text-primary"></i> Minimal 5 karakter (diluar spasi) & maksimal 200 karakter';
                }
            }
        }

        $(document).ready(function() {
            updateTitleInfo();

            $(document).on('input', 'textarea[name="title"]', function() {
                updateTitleInfo();
            });

            $(window).on('resize', function() {
                updateTitleInfo();
            });

            $(document).on('keydown', 'textarea[name="title"]', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if ($('#editor').length && $.fn.summernote) {
                        $('#editor').summernote('focus');
                    }
                }
            });

            if ($.fn.select2) {
                $('.select2-category').select2({
                    placeholder: "-- Pilih Kategori --",
                    width: '100%',
                    allowClear: true
                });
            }
        });

        $('.editorForm').on('submit', function (e) {
            e.preventDefault();

            let $titleInput = $('[name="title"]');
            if ($titleInput.length && $titleInput.attr('type') !== 'hidden') {
                let titleVal = $titleInput.val() || '';
                let nonSpaceLen = titleVal.replace(/\s+/g, '').length;
                if (nonSpaceLen < 5) {
                    notif('{{ $module->datatable->data_title }} minimal 5 karakter di luar spasi!', 'danger');
                    $titleInput.focus();
                    $('.status-toggle-btn').each(function () {
                        let $label = $(this);
                        let $input = $label.find('input');
                        let val = $input.val();
                        $label.css('pointer-events', 'auto').fadeTo(200, 1);
                        let $icon = $label.find('i');
                        $icon.removeClass('fa-spinner fa-spin');
                        if (val === 'publish') {
                            $icon.addClass('fa-globe');
                            $label.contents().filter(function () {
                                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                            }).each(function () {
                                this.nodeValue = ' Publikasikan';
                            });
                        } else if (val === 'draft') {
                            $icon.addClass('fa-archive');
                            $label.contents().filter(function () {
                                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                            }).each(function () {
                                this.nodeValue = ' Draft';
                            });
                        }
                    });
                    return false;
                }
            }

            if (typeof window.editor !== 'undefined' && window.editor.save) {
                window.editor.save();
            }
            $('.text-save').html('Menyimpan...');
            $('.btn-primary').attr('disabled', 'disabled');
            let form = this;
            let actionUrl = $(form).attr('action');
            let formData = new FormData(form);
            formData.append('_method', 'PUT');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    notif('Berhasil menyimpan perubahan!', 'success');
                    $('.text-save').html('Simpan');
                    $('.btn-primary').removeAttr('disabled');

                    if (typeof response === 'string' && response.includes('<html')) {
                        let newDoc = new DOMParser().parseFromString(response, 'text/html');

                        // Update Thumbnail Image
                        let newThumb = newDoc.getElementById('thumb');
                        if (newThumb && document.getElementById('thumb')) {
                            document.getElementById('thumb').src = newThumb.src;
                            $('#thumb').data('original-src', newThumb.src);
                            $('input[name="media"]').val('');
                        }

                        // Update Custom Fields Container
                        let newCustomFields = newDoc.getElementById('custom-fields-container');
                        if (newCustomFields && document.getElementById('custom-fields-container')) {
                            $('#custom-fields-container').html(newCustomFields.innerHTML);
                        }

                        // Update Looping Data Container
                        let newLoopingData = newDoc.getElementById('looping-data-container');
                        if (newLoopingData && document.getElementById('looping-data-container')) {
                            $('#looping-data-container').html(newLoopingData.innerHTML);
                        }

                        // Clear any lingering Gmedia preview wrappers
                        $('.btn-clear-gmedia').click();

                        // Update URL bar dynamically
                        let newUrlBar = newDoc.getElementById('post-url-bar');
                        let currentUrlBar = document.getElementById('post-url-bar');
                        if (currentUrlBar && newUrlBar) {
                            if (newUrlBar.style.display === 'none' && currentUrlBar.style.display !== 'none') {
                                $(currentUrlBar).slideUp('fast', function() {
                                    currentUrlBar.innerHTML = newUrlBar.innerHTML;
                                });
                            } else if (newUrlBar.style.display !== 'none' && currentUrlBar.style.display === 'none') {
                                currentUrlBar.innerHTML = newUrlBar.innerHTML;
                                $(currentUrlBar).slideDown('fast');
                            } else {
                                currentUrlBar.innerHTML = newUrlBar.innerHTML;
                            }
                            let newLink = newUrlBar.querySelector('.post-url-link');
                            if (newLink) {
                                currentUrlBar.querySelector('.post-url-link').href = newLink.href;
                            }
                            let newEdit = newUrlBar.querySelector('.fa-edit');
                            let curEdit = currentUrlBar.querySelector('.fa-edit');
                            if (newEdit && curEdit) {
                                curEdit.dataset.postUrl = newEdit.dataset.postUrl;
                                curEdit.dataset.slug = newEdit.dataset.slug;
                            }
                            let newCopy = newUrlBar.querySelector('.copy');
                            let curCopy = currentUrlBar.querySelector('.copy');
                            if (newCopy && curCopy) {
                                curCopy.dataset.copy = newCopy.dataset.copy;
                            }
                            if (newUrlBar.style.display === 'none') {
                                $(currentUrlBar).slideUp(300);
                            } else {
                                $(currentUrlBar).slideDown(300);
                            }
                        }
                    }

                    // Reset status buttons state
                    $('.status-toggle-btn').each(function () {
                        let $label = $(this);
                        let $input = $label.find('input');
                        let val = $input.val();
                        $label.css('pointer-events', 'auto').fadeTo(200, 1);
                        let $icon = $label.find('i');
                        $icon.removeClass('fa-spinner fa-spin');
                        if (val === 'publish') {
                            $icon.addClass('fa-globe');
                            $label.contents().filter(function () {
                                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                            }).each(function () {
                                this.nodeValue = ' Publikasikan';
                            });
                        } else if (val === 'draft') {
                            $icon.addClass('fa-archive');
                            $label.contents().filter(function () {
                                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                            }).each(function () {
                                this.nodeValue = ' Draft';
                            });
                        }
                    });
                },
                error: function (xhr) {
                    try {
                        let res = JSON.parse(xhr.responseText);
                        let allMsg = [];
                        if (res.errors) {
                            Object.values(res.errors).forEach(arrMsg => {
                                allMsg = allMsg.concat(arrMsg);
                            });
                            let finalMsg = allMsg.join('<br>');
                            notif(finalMsg, 'danger');
                        } else if (res.message) {
                            notif(res.message, 'danger');
                        } else {
                            notif('Gagal menyimpan perubahan!', 'danger');
                        }
                    } catch (e) {
                        notif('Gagal menyimpan perubahan!', 'danger');
                    }

                    $('.text-save').html('Simpan');
                    $('.btn-primary').removeAttr('disabled');

                    $('.status-toggle-btn').each(function () {
                        let $label = $(this);
                        let $input = $label.find('input');
                        let val = $input.val();
                        $label.css('pointer-events', 'auto').fadeTo(200, 1);
                        let $icon = $label.find('i');
                        $icon.removeClass('fa-spinner fa-spin');
                        if (val === 'publish') {
                            $icon.addClass('fa-globe');
                            $label.contents().filter(function () {
                                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                            }).each(function () {
                                this.nodeValue = ' Publikasikan';
                            });
                        } else if (val === 'draft') {
                            $icon.addClass('fa-archive');
                            $label.contents().filter(function () {
                                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                            }).each(function () {
                                this.nodeValue = ' Draft';
                            });
                        }
                    });
                }
            });
        });
    </script>
    @include('cms::backend.layout.js')
    <script>
        function handleStatusSubmit(btn) {
            let $btn = $(btn);
            $btn.find('input').prop('checked', true);
            let val = $btn.find('input').val();

            // Toggle active visual class on buttons
            $btn.siblings('.status-toggle-btn').removeClass('active');
            $btn.addClass('active');

            let $icon = $btn.find('i');
            $icon.removeClass('fa-globe fa-archive').addClass('fa-spinner fa-spin');

            $btn.contents().filter(function () {
                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
            }).each(function () {
                this.nodeValue = val === 'publish' ? ' Diproses...' : ' Menyimpan...';
            });

            $btn.siblings('label').css('pointer-events', 'none').fadeTo(200, 0.5);
            $('.editorForm').submit();
        }
    </script>
@endpush

@if ($module->form->editor && isset($module->form->ai_generator) && $module->form->ai_generator)
    <!-- AI Prompt Generator Modal -->
    <div class="modal fade" id="promptGeneratorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header bg-primary text-white p-3">
                    <h5 class="modal-title font-weight-bold" style="font-size: 16px;"><i class="fa fa-magic mr-1.5"></i> AI Content Prompt Generator</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info small" style="border-radius: 8px;">
                        Gunakan asisten ini untuk menyusun instruksi (prompt) ke AI. Anda bisa menggunakannya untuk berbagai jenis tulisan: artikel blog, berita, opini, cerita, hingga materi promosi.
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="soft-label">Topik / Judul Tulisan</label>
                            <input type="text" id="prompt_topic" class="form-control soft-control" placeholder="Contoh: Manfaat AI untuk Produktivitas...">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="soft-label">Konteks / Latar Belakang (Opsional)</label>
                            <input type="text" id="prompt_context" class="form-control soft-control" placeholder="Contoh: Tahun 2024 di Indonesia, atau situasi spesifik...">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="soft-label">Target Pembaca / Audiens (Opsional)</label>
                            <input type="text" id="prompt_audience" class="form-control soft-control" placeholder="Contoh: Masyarakat umum, Anak muda, Profesional...">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="soft-label">Poin Utama / Pesan yang Ingin Disampaikan</label>
                            <textarea id="prompt_what" class="form-control soft-control" rows="3" placeholder="Contoh: Menjelaskan apa itu AI, contoh penggunaannya sehari-hari, dan dampaknya bagi efisiensi kerja..."></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="soft-label">Gaya Bahasa (Tone of Voice)</label>
                            <select id="prompt_style" class="form-control soft-control">
                                <option value="Informatif dan edukatif">Edukasi / Informatif</option>
                                <option value="Santai, ramah, dan komunikatif layaknya blogger">Santai & Komunikatif</option>
                                <option value="Jurnalistik formal layaknya reporter berita">Berita Formal</option>
                                <option value="Kreatif, imajinatif, dan bercerita">Kreatif / Storytelling</option>
                                <option value="Persuasif dan menarik untuk promosi (Copywriting)">Promosi / Persuasif</option>
                                <option value="Resmi dan kaku layaknya dokumen formal/akademik">Resmi / Akademik</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="soft-label">Call to Action / Penutup (Opsional)</label>
                            <input type="text" id="prompt_cta" class="form-control soft-control" placeholder="Contoh: Ajak pembaca untuk berkomentar atau mencoba produk...">
                        </div>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-primary btn-block mb-3" style="border-radius: 8px; font-weight: 600; padding: 10px;" onclick="generatePromptText()">
                        <i class="fa fa-cogs mr-1"></i> Generate Prompt
                    </button>
                    <div class="form-group mb-0">
                        <label class="soft-label">Hasil Prompt (Salin teks ini ke ChatGPT / AI Anda)</label>
                        <textarea id="prompt_result" class="form-control soft-control" rows="8" style="background:#f8fafc;" readonly></textarea>
                        <div class="mt-3">
                            <button type="button" class="btn btn-success btn-sm" style="border-radius: 6px; font-weight: 600;" onclick="copyPrompt()">
                                <i class="fa fa-copy mr-1"></i> Salin ke Clipboard
                            </button>
                            <button type="button" class="btn btn-info btn-sm ml-2" style="border-radius: 6px; font-weight: 600;" onclick="generateDirectToEditor()">
                                <i class="fa fa-magic mr-1"></i> Generate Langsung ke Editor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function generatePromptText() {
            let topic = $('#prompt_topic').val() || '[Topik Belum Diisi]';
            let context = $('#prompt_context').val();
            let audience = $('#prompt_audience').val();
            let what = $('#prompt_what').val() || '[Poin Utama Belum Diisi]';
            let style = $('#prompt_style').val();
            let cta = $('#prompt_cta').val();
            
            let prompt = "Bertindaklah sebagai penulis profesional. Tolong buatkan sebuah konten/artikel dengan detail instruksi berikut:\n\n";
            prompt += "- Topik Utama / Judul: " + topic + "\n";
            if(context) prompt += "- Konteks / Latar Belakang: " + context + "\n";
            if(audience) prompt += "- Target Pembaca: " + audience + "\n";
            prompt += "- Poin-Poin Penting yang Wajib Disampaikan:\n  " + what.replace(/\n/g, "\n  ") + "\n\n";
            prompt += "- Gaya Bahasa Tulisan: " + style + "\n";
            if(cta) prompt += "- Penutup / Call-to-Action: " + cta + "\n\n";
            prompt += "Tulis konten tersebut dengan struktur paragraf yang rapi, transisi antar ide yang mengalir dengan baik, dan panjang teks yang memadai agar pesan tersampaikan secara utuh.";
            
            $('#prompt_result').val(prompt);
        }
        
        function copyPrompt() {
            let copyText = document.getElementById("prompt_result");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            
            let btn = $(event.currentTarget);
            let oldHtml = btn.html();
            btn.html('<i class="fa fa-check"></i> Tersalin!');
            btn.removeClass('btn-success').addClass('btn-info');
            setTimeout(function(){
                btn.html(oldHtml);
                btn.removeClass('btn-info').addClass('btn-success');
            }, 2000);
        }

        function generateDirectToEditor() {
            let promptText = $('#prompt_result').val();
            if(!promptText) {
                alert("Silakan klik 'Generate Prompt' terlebih dahulu.");
                return;
            }
            
            $('#aiPrompt').val(promptText);
            $('#promptGeneratorModal').modal('hide');
            setTimeout(function() {
                $('#btnGenerateAI').click();
            }, 300);
        }
    </script>
@endif

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
      <form id="formAddCategory">
      <div class="modal-header bg-primary text-white p-3">
        <h5 class="modal-title font-weight-bold" style="font-size: 16px;" id="addCategoryModalLabel">
            <i class="fa fa-plus-circle mr-1.5"></i> Tambah Kategori Baru
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-4">
          <div class="form-group mb-0">
            <label for="categoryName" class="soft-label">Nama Kategori</label>
            <input type="text" class="form-control soft-control" id="categoryName" name="name" required placeholder="Masukkan nama kategori baru...">
            <input type="hidden" name="status" value="publish">
          </div>
      </div>
      <div class="modal-footer p-3 bg-light">
        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" style="border-radius: 6px;">Batal</button>
        <button type="submit" class="btn btn-primary btn-sm px-4 font-weight-bold" id="btnSaveCategory" style="border-radius: 6px;">Simpan Kategori</button>
      </div>
      </form>
    </div>
  </div>
</div>

@if(current_module()->form->category)
@push('scripts')
<script>
    $('#formAddCategory').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btnSaveCategory');
        
        btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: "{{ route($post->type . '.category.store') }}",
            type: "POST",
            data: $(this).serialize() + "&_token={{ csrf_token() }}",
            success: function(res) {
                if(res.success) {
                    $('#addCategoryModal').modal('hide');
                    $('#categoryName').val('');
                    
                    let newOption = new Option(res.data.name, res.data.id, true, true);
                    $('#category_select2').append(newOption).trigger('change');
                    
                    btn.attr('disabled', false).html('Simpan Kategori');
                } else {
                    alert('Gagal menambahkan kategori');
                    btn.attr('disabled', false).html('Simpan Kategori');
                }
            },
            error: function(err) {
                let msg = 'Terjadi kesalahan.';
                if(err.responseJSON && err.responseJSON.message) {
                    msg = err.responseJSON.message;
                }
                alert(msg);
                btn.attr('disabled', false).html('Simpan Kategori');
            }
        });
    });
</script>
@endpush
@endif

@endsection