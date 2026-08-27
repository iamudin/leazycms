@extends('cms::backend.layout.app', ['title' => get_post_type('title_crud')])
@section('content')
    <form class="editorForm" action="{{ route(get_post_type() . '.update', $post->id) }}" method="POST"
        enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal">
                    <i class="fa {{ $module->icon }}" aria-hidden="true"></i> {{ get_post_type('title_crud') }}
                    <div class="btn-group pull-right">
                        @if(View::exists('template.' . template() . '.' . $post->type . '.' . $post->slug))
                            <a href="{{ route('appearance.editor') . '?edit=' . enc64('/' . $post->type . '/' . $post->slug . '.blade.php') }}"
                                class="btn btn-warning btn-sm"> <i class="fa fa-edit"></i> Edit Halaman
                                {!! help('Tombol ini akan muncul ketika ' . $module->title . ' ini memiliki custom page pada tampilan. Klik untuk mulai mengedit') !!}</a>
                        @endif

                        <button type="button" onclick="location.href='{{ route(get_post_type()) }}'"
                            class="btn btn-danger btn-sm " data-toggle="tooltip" title="Kembali Ke Index Data"> <i
                                class="fa fa-undo" aria-hidden></i>
                            Kembali</button>


                    </div>
                </h3>
                <br>
                @php
                    $showUrl = !empty($post && $module->web->detail && $post->title && $post->status == 'publish') && $module->public;
                    $postUrl = config('modules.multisite_enabled') && $post->tenant ? 'https://' . $post->tenant->domain . '/' . $post->url : url($post->url);
                @endphp
                @if($module->web->detail && $module->public)
                    <div id="post-url-bar" style="border-left:3px solid green;{{ $showUrl ? '' : 'display:none' }}"
                        class="alert alert-success"><b>URL : </b><a title="Kunjungi URL" data-toggle="tooltip"
                            href="{{ $postUrl }}" target="_blank" class="post-url-link"><i
                                class="url"><u>{{ $showUrl ? str($postUrl)->limit(150, ' ...') : '' }}</u></i> </a><span
                            class="custom-url"></span> <i class="fa fa-edit ml-2 pointer" data-post-url="{{ $postUrl }}"
                            data-slug="{{ $post->slug }}" onclick="enableCustomSlugEdit(this.dataset.postUrl, this.dataset.slug)"></i><span title="Klik Untuk Menyalin alamat URL {{ $module->title }}"
                            data-toggle="tooltip" class="pointer copy pull-right badge badge-primary"
                            data-copy="{{ $postUrl }}"><i class="fa fa-copy" aria-hidden></i> <b>Salin</b></span></div>
                    @push('scripts')
                        <script>

                            function enableCustomSlugEdit(postUrl, slug) {
                                const urlElement = document.querySelector('.url');
                                const customUrlElement = document.querySelector('.custom-url');
                                const editButton = document.querySelector('.fa-edit');

                                const baseUrl = postUrl.replace(slug, '');
                                urlElement.innerHTML = baseUrl;

                                customUrlElement.innerHTML = `
                                                                                                                                            <input type='text' name='custom_slug' autofocus
                                                                                                                                                   style='border:none;border-radius:5px;color:#004A43;width:300px;background:transparent'
                                                                                                                                                   value='${slug}'
                                                                                                                                                   maxlength='100'
                                                                                                                                                   oninput="validateAndUpdateSlug('${baseUrl}', this)">
                                                                                                                                            <i class="fa fa-check ml-2 pointer" onclick="finalizeSlugEdit('${baseUrl}', this)"></i>
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
                                const editButton = document.querySelector('.fa-edit');

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
                <div class="d-block d-lg-none mb-3 mt-3">
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label onclick="handleStatusSubmit(this)"
                            class="btn btn-outline-success {{ (!$post || $post->status == 'publish') ? 'active' : '' }}">
                            <input type="radio" name="status" value="publish" {{ (!$post || $post->status == 'publish') ? 'checked' : '' }} required>
                            <i class="fa fa-globe"></i> Publikasikan
                        </label>
                        <label onclick="handleStatusSubmit(this)"
                            class="btn btn-outline-secondary {{ ($post && $post->status == 'draft') ? 'active' : '' }}">
                            <input type="radio" name="status" value="draft" {{ ($post && $post->status == 'draft') ? 'checked' : '' }} required>
                            <i class="fa fa-archive"></i> Draft
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="form-group">
                    @if(isset($module->form?->editable_title) && $module->form?->editable_title == true || !isset($module->form?->editable_title))
                        <textarea data-toggle="tooltip" minlength="5"  maxlength="200" title="Masukkan {{ $module->datatable->data_title }}" required name="title"
                            placeholder="Masukkan {{ $module->datatable->data_title }}" rows="1"
                            class="form-control form-control-lg autosize-title"
                            style="resize: none; overflow: hidden; min-height: 48px; line-height: 1.4;">{{ !empty(old('title')) ? old('title') : ($post->title ?? null) }}</textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted title-info-text">
                                <i class="fa fa-info-circle"></i> Minimal 5 karakter (diluar spasi) & maksimal 200 karakter
                            </small>
                            <small id="title-char-counter" class="text-muted">
                                <span id="title-char-count">0</span>/200
                            </small>
                        </div>
                    @else
                        <input type="hidden" name="title" value="{{ $post->title ?? null }}">
                        <label for="">{{ $module->datatable->data_title }}</label>
                        <h3>{{ $post->title ?? null }}</h3>
                    @endif

                </div>

                @if ($module->form->editor)
                    <div class="form-group">
                        @if (isset($module->form->ai_generator) && $module->form->ai_generator)
                        <button type="button" class="btn btn-sm btn-outline-primary mb-2" data-toggle="modal" data-target="#promptGeneratorModal" onclick="$('#prompt_topic').val($('[name=title]').val())">
                            <i class="fa fa-magic"></i> AI Prompt Generator
                        </button>
                        @endif

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
                            <textarea name="content" placeholder="Dokumentasi" id="editor"
                                class="custom_html">{{ $content }}</textarea>
                            @include('cms::backend.layout.codemirrorjs')
                        @else
                            @php
                                $content = !empty(old('content')) ? old('content') : ($post->content ?? '');
                                if ($isTenantOnMainDomain) {
                                    $content = preg_replace('/src="\/media\//i', 'src="https://' . $post->tenant->domain . '/media/', $content);
                                }
                            @endphp
                            <textarea name="content" placeholder="Keterangan..."
                                id="editor">{{ $content }}</textarea>
                        @endif
                    </div>
                @endif

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
                            <h6>{{ $pp[0] }}</h6>
                            <select @if (isset($pp[3]) && $pp[3] == 'required') required @endif data-live-search="true"
                                class="selectpicker form-control" name="parent_id">
                                <option value="">--pilih--</option>

                                @foreach ($par as $row)
                                    <option @if ($post && $post->parent_id == $row->id) selected @endif value="{{ $row->id }}">
                                        {{ $row->title }}
                                        {{ $row->parent ? ' - ' . $row->parent->title . ($row->parent->parent ? ' - ' . $row->parent->parent->title : '') : ''}}
                                    </option>
                                @endforeach

                            </select>
                            @push('styles')
                                <link rel="stylesheet"
                                    href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
                            @endpush
                            @push('scripts')
                                <!-- Latest compiled and minified JavaScript -->
                                <script
                                    src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>

                                <!-- (Optional) Latest compiled and minified JavaScript translation files -->
                                <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-*.min.js"></script>
                            @endpush

                @endif

                @if ($module->form->custom_field)
                    <div id="custom-fields-container">
                        @include('cms::backend.posts.custom_field.form')
                    </div>
                @endif
                @if ($module->form->looping_data)
                    <div id="looping-data-container">
                        @include('cms::backend.posts.looping_data.form')
                    </div>
                @endif
            </div>
            <div class="col-lg-3">
                <div class="sticky-sidebar-content" style="position: -webkit-sticky; position: sticky; top: 65px; z-index: 10; max-height: calc(100vh - 80px); overflow-y: auto; overflow-x: hidden; padding-bottom: 20px;">
                    <!-- Desktop Status Toggle (Visible only on large screens) -->
                <div class="d-none d-lg-block">
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        <label onclick="handleStatusSubmit(this)"
                            class="btn btn-md btn-outline-success {{ (!$post || $post->status == 'publish') ? 'active' : '' }}">
                            <input type="radio" name="status" value="publish" {{ (!$post || $post->status == 'publish') ? 'checked' : '' }} required>
                            <i class="fa fa-globe"></i> Publikasikan
                        </label>
                        <label onclick="handleStatusSubmit(this)"
                            class="btn btn-md  btn-outline-secondary {{ ($post && $post->status == 'draft') ? 'active' : '' }}">
                            <input type="radio" name="status" value="draft" {{ ($post && $post->status == 'draft') ? 'checked' : '' }} required>
                            <i class="fa fa-archive"></i> Draft
                        </label>
                    </div>
                </div>

                @if ($module->form->thumbnail)
                    <div class="card mt-3">
                        <div class="card-header font-weight-bold"> <i class="fa fa-image" aria-hidden></i> Foto {{current_module()->title}}</div>
                        <div class="position-relative" style="background: #f8f9fa;">
                            <img class="img-responsive w-100" style="border:none; min-height: 150px; object-fit: cover;" id="thumb" src="{{ $post->thumbnail }}" />
                            <div class="upload-overlay">
                                <input accept="image/png,image/jpeg,image/webp,image/gif" type="file"
                                    class="compress-image form-control-file form-control-sm" name="media" value="" style="width: 100%;">
                            </div>
                        </div>
                        <style>
                            .upload-overlay .global-file-wrapper {
                                position: absolute;
                                top: 15px;
                                right: 15px;
                                margin: 0;
                                display: flex;
                                z-index: 10;
                            }
                            .upload-overlay .btn-open-gmedia {
                                width: 40px;
                                height: 40px;
                                border-radius: 50%;
                                padding: 0;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0; /* Hides text */
                                background: rgba(255, 255, 255, 0.9);
                                border: 1px solid #ddd;
                                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                                color: #333;
                                transition: all 0.2s;
                                cursor: pointer;
                            }
                            .upload-overlay .btn-open-gmedia:hover {
                                background: #fff;
                                color: #000;
                            }
                            .upload-overlay .btn-open-gmedia i {
                                font-size: 16px;
                                margin: 0;
                            }
                            .upload-overlay .btn-open-gmedia i.fa-folder-open::before {
                                content: "\f040"; /* Pencil icon */
                            }
                            
                            .upload-overlay .btn-clear-gmedia {
                                width: 40px;
                                height: 40px;
                                border-radius: 50%;
                                padding: 0;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                background: rgba(255, 255, 255, 0.9);
                                border: 1px solid #ddd;
                                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                                margin-left: 8px !important;
                                transition: all 0.2s;
                                cursor: pointer;
                            }
                            .upload-overlay .btn-clear-gmedia:hover {
                                background: #fff;
                            }
                            
                            .upload-overlay .media-preview-area {
                                display: none !important; /* Hide duplicate preview */
                            }
                        </style>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                // Simpan URL asli ke dalam attribute data
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
                                    // Kembalikan ke URL asli dari attribute data
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
                            <div class="p-3 border-top">
                                <textarea maxlength="200" placeholder="Keterangan Gambar" type="text" class="form-control form-control-sm"
                                    name="media_description">{{ !empty(old('media_description')) ? old('media_description') : ($post->media_description ?? '') }}</textarea>
                            </div>
                        @endif
                    </div>

                @endif

                @if ($module->web->detail || $modname = $module->name == 'banner')
                    <div class="form-group mt-1">
                        <small>Pengalihan URL {!! help('Opsi Jika Ingin Mengalihkan Konten Ini ke suatu halaman web atau url') !!}</small>
                        <input type="url" id="redirect_to" class="form-control form-control-sm" name="redirect_to"
                            placeholder="URL dimulai https:// atau http://"
                            value="{{ !empty(old('redirect_to')) ? old('redirect_to') : ($post->redirect_to ?? '') }}"
                            oninput="validateRedirectUrl(this)" pattern="https?://.+">
                        <small class="text-danger" id="redirect_error" style="display:none; margin-top: 5px;">Format URL tidak valid (harus diawali http:// atau https://)</small>
                    </div>
                    
                    <script>
                        function validateRedirectUrl(input) {
                            let val = input.value.trim();
                            
                            if (val === '') {
                                $('#redirect_error').hide();
                                $(input).removeClass('is-invalid');
                                return;
                            }
                            
                            // Paksa awalan http:// jika user mengetik awalan selain 'h' atau 'http'
                            if (val.length > 0 && !val.toLowerCase().startsWith('h')) {
                                input.value = 'http://' + val;
                                val = input.value; // update nilai untuk divalidasi
                            }

                            // Regex untuk mengecek apakah dimulai dengan http:// atau https:// dan memiliki format domain/URL
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
                    <div class="card mt-3">
                        <p class="card-header"> <i class="fa fa-search" aria-hidden></i> SEO Meta (Opsional)</p>
                        <div style="padding:10px">
                            <small for="">Deskripsi
                                {!! help('Opsi deskripsi singkat tentang konten yang dapat ditelusuri oleh mesin pencarian') !!}
                            </small>
                            <textarea maxlength="200" placeholder="Tulis Deskripsi" type="text" class="form-control form-control-sm mb-2"
                                name="description">{{ !empty(old('description')) ? old('description') : ($post->description ?? '') }}</textarea>
                            <small for="">Kata Kunci
                                {!! help('Kata kunci tentang konten yang dapat ditelusuri oleh mesin pencarian') !!}</small>
                            <input placeholder="Keyword1,Keyword2,Keyword3" type="text" class="form-control form-control-sm"
                                name="keyword" value="{{ !empty(old('keyword')) ? old('keyword') : ($post->keyword ?? '') }}">
                        </div>
                    </div>
                    @endif
                    @if ($module->form->tag)

                        <small for="">Tags {!! help('Penanda untuk memudahkan pencarian topik') !!}</small>
                        <select name="tags[]" id="select2" class="form-control form-control-sm form-control-select" multiple id="">
                            @foreach($tags as $row)
                                <option {{ in_array($row->id, $post->tags->pluck('id')->toArray()) ? 'selected' : '' }}
                                    value="{{  $row->id}}">{{ $row->name }}</option>
                            @endforeach
                        </select>

                    
                    @endif

                @endif
                @if ($module->form->category)
                        <small for="">Kategori {{ $module->title }} </small><br>
                        @php
                            $dbCategories = config('modules.multisite_enabled') ? (is_main_domain() ? $category->load('tenant') : $category->where('tenant_id', tenant()->id)) : $category;
                            $defaultCategories = config('modules.default_category.' . $post->type, []);
                            $dbCategoryNames = $dbCategories->pluck('name')->map(fn($n) => strtolower($n))->toArray();
                            $unregisteredDefaults = array_filter($defaultCategories, fn($name) => !in_array(strtolower($name), $dbCategoryNames));
                        @endphp

                        <div class="input-group input-group-sm flex-nowrap">
                            <select class="form-control form-control-select select2-category" name="category_id" id="category_select2">
                                <option value="">-- Pilih / Tanpa Kategori --</option>
                                
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
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCategoryModal" title="Tambah Baru"><i class="fa fa-plus"></i></button>
                                <a href="{{ route($post->type.'.category') }}" class="btn btn-info" title="Kelola Kategori"><i class="fa fa-cog"></i></a>
                            </div>
                        </div>


                @endif
                @if ($module->web->sortable)
                    <small for="">Urutan {!! help('Urutan konten yang akan ditampilkan') !!}</small>
                    <select class="form-control form-control-sm" name="sort">
                        @php $count = query()->onType(get_post_type())->count();@endphp
                        @for ($i = 1; $i <= $count; $i++)
                            <option value="{{ $i }}" {{ $post->sort == $i ? 'selected=selected' : '' }}>{{ $i }}
                            </option>
                        @endfor
                    </select>
                    <div class="mb-2"></div>

                @else
                    <div class="mb-2"></div>

                @endif

                @if ($module->web->detail)
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="switch-password" name="password" value="Y" {{ $post && !empty($post->password) ? 'checked=checked' : '' }}>
                        <label class="custom-control-label" for="switch-password"><small>Batasi Akses {{ $module->title }} ini
                                {!! help('Jika dicentang, Pengunjung wajib memasukkan kode PIN utk melihat. Klik icon merah disamping untuk menyalin kode rahasia') !!}
                            </small></label>
                        @if(!empty($post->password))<i class="fa fa-copy copy text-danger pointer ml-1"
                        title="Klik untuk menyalin kode" data-copy="{{ dec64($post->password) }}"></i>@endif
                    </div>
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" id="switch-comment" name="allow_comment" value="Y"
                            {{ $post && $post->allow_comment == 'Y' ? 'checked=checked' : '' }}>
                        <label class="custom-control-label" for="switch-comment"><small>Izinkan Komentar
                                {!! help('Jika dicentang, maka pengunjung bisa mengirim komentar pada postingan ini') !!}
                            </small></label>
                    </div>
                @endif
                <div class="custom-control custom-switch mb-4">
                    <input type="checkbox" class="custom-control-input" id="switch-pinned" name="pinned" value="Y" {{ $post && $post->pinned == 'Y' ? 'checked=checked' : '' }}>
                    <label class="custom-control-label" for="switch-pinned"><small>Sematkan
                            {!! help('Jika dicentang, maka postingan ini akan menjadi prioritas dihalaman jika dikondisikan pada template ') !!}
                        </small></label>
                </div>
            </div>
    </form>
    @push('styles')
        <style>
            .sticky-sidebar-content::-webkit-scrollbar {
                width: 4px;
            }
            .sticky-sidebar-content::-webkit-scrollbar-track {
                background: transparent;
            }
            .sticky-sidebar-content::-webkit-scrollbar-thumb {
                background: rgba(0,0,0,0.15);
                border-radius: 4px;
            }
            .sticky-sidebar-content::-webkit-scrollbar-thumb:hover {
                background: rgba(0,0,0,0.3);
            }
        </style>
    @endpush
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
                        helperEl.innerHTML = '<i class="fa fa-info-circle"></i> Minimal 5 karakter (diluar spasi) & maksimal 200 karakter';
                    } else if (nonSpaceLen < 5) {
                        helperEl.className = 'text-danger title-info-text';
                        helperEl.innerHTML = '<i class="fa fa-exclamation-circle"></i> Minimal 5 karakter diluar spasi (saat ini: ' + nonSpaceLen + ' karakter)';
                    } else if (totalLen >= 200) {
                        helperEl.className = 'text-warning title-info-text';
                        helperEl.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Mencapai batas maksimal 200 karakter';
                    } else {
                        helperEl.className = 'text-muted title-info-text';
                        helperEl.innerHTML = '<i class="fa fa-info-circle"></i> Minimal 5 karakter (diluar spasi) & maksimal 200 karakter';
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
                        placeholder: "-- Pilih / Tanpa Kategori --",
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
                        $('.btn-group-toggle label').each(function () {
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
                                // Menggunakan jQuery .html() agar tag <script> dieksekusi (native innerHTML tidak mengeksekusi script)
                                $('#custom-fields-container').html(newCustomFields.innerHTML);
                            }

                            // Update Looping Data Container
                            let newLoopingData = newDoc.getElementById('looping-data-container');
                            if (newLoopingData && document.getElementById('looping-data-container')) {
                                $('#looping-data-container').html(newLoopingData.innerHTML);
                            }

                            // Clear any lingering Gmedia preview wrappers (temporary previews)
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
                                // Update href/data attributes
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
                                // Show or hide based on display style from server
                                if (newUrlBar.style.display === 'none') {
                                    $(currentUrlBar).slideUp(300);
                                } else {
                                    $(currentUrlBar).slideDown(300);
                                }
                            }
                        }

                        // Reset status buttons state instead of reloading
                        $('.btn-group-toggle label').each(function () {
                            let $label = $(this);
                            let $input = $label.find('input');
                            let val = $input.val();

                            // Enable all labels
                            $label.css('pointer-events', 'auto').fadeTo(200, 1);

                            // Reset icon and text based on value
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

                        // Reset status buttons state instead of reloading
                        $('.btn-group-toggle label').each(function () {
                            let $label = $(this);
                            let $input = $label.find('input');
                            let val = $input.val();

                            // Enable all labels
                            $label.css('pointer-events', 'auto').fadeTo(200, 1);

                            // Reset icon and text based on value
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
                // Set radio button to checked
                $btn.find('input').prop('checked', true);
                let val = $btn.find('input').val();

                // Change icon to spinner
                let $icon = $btn.find('i');
                $icon.removeClass('fa-globe fa-archive').addClass('fa-spinner fa-spin');

                // Change text safely without removing the input
                // Get all text nodes and replace their content
                $btn.contents().filter(function () {
                    return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                }).each(function () {
                    this.nodeValue = val === 'publish' ? ' Diproses...' : ' Menyimpan...';
                });

                // Disable other buttons
                $btn.siblings('label').css('pointer-events', 'none').fadeTo(200, 0.5);

                // Submit form
                $('.editorForm').submit();
            }
        </script>
    @endpush

@if ($module->form->editor && isset($module->form->ai_generator) && $module->form->ai_generator)
    <!-- AI Prompt Generator Modal -->
    <div class="modal fade" id="promptGeneratorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-magic"></i> AI Content Prompt Generator</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        Gunakan asisten ini untuk menyusun instruksi (prompt) ke AI. Anda bisa menggunakannya untuk berbagai jenis tulisan: artikel blog, berita, opini, cerita, hingga materi promosi.
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label>Topik / Judul Tulisan</label>
                            <input type="text" id="prompt_topic" class="form-control" placeholder="Contoh: Manfaat AI untuk Produktivitas...">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Konteks / Latar Belakang (Opsional)</label>
                            <input type="text" id="prompt_context" class="form-control" placeholder="Contoh: Tahun 2024 di Indonesia, atau situasi spesifik...">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Target Pembaca / Audiens (Opsional)</label>
                            <input type="text" id="prompt_audience" class="form-control" placeholder="Contoh: Masyarakat umum, Anak muda, Profesional...">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Poin Utama / Pesan yang Ingin Disampaikan</label>
                            <textarea id="prompt_what" class="form-control" rows="3" placeholder="Contoh: Menjelaskan apa itu AI, contoh penggunaannya sehari-hari, dan dampaknya bagi efisiensi kerja..."></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Gaya Bahasa (Tone of Voice)</label>
                            <select id="prompt_style" class="form-control">
                                <option value="Informatif dan edukatif">Edukasi / Informatif</option>
                                <option value="Santai, ramah, dan komunikatif layaknya blogger">Santai & Komunikatif</option>
                                <option value="Jurnalistik formal layaknya reporter berita">Berita Formal</option>
                                <option value="Kreatif, imajinatif, dan bercerita">Kreatif / Storytelling</option>
                                <option value="Persuasif dan menarik untuk promosi (Copywriting)">Promosi / Persuasif</option>
                                <option value="Resmi dan kaku layaknya dokumen formal/akademik">Resmi / Akademik</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Call to Action / Penutup (Opsional)</label>
                            <input type="text" id="prompt_cta" class="form-control" placeholder="Contoh: Ajak pembaca untuk berkomentar atau mencoba produk...">
                        </div>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-primary btn-block mb-3" onclick="generatePromptText()">
                        <i class="fa fa-cogs"></i> Generate Prompt
                    </button>
                    <div class="form-group">
                        <label>Hasil Prompt (Salin teks ini ke ChatGPT / AI Anda)</label>
                        <textarea id="prompt_result" class="form-control" rows="10" style="background:#f8f9fa; border:1px solid #ced4da;" readonly></textarea>
                        <button type="button" class="btn btn-success btn-sm mt-2" onclick="copyPrompt()">
                            <i class="fa fa-copy"></i> Salin ke Clipboard
                        </button>
                        <button type="button" class="btn btn-info btn-sm mt-2 ml-2" onclick="generateDirectToEditor()">
                            <i class="fa fa-magic"></i> Generate Langsung ke Editor
                        </button>
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
            
            // Set value ke modal AI Summernote bawaan
            $('#aiPrompt').val(promptText);
            
            // Tutup modal generator ini
            $('#promptGeneratorModal').modal('hide');
            
            // Memicu klik pada tombol generate bawaan Summernote AI
            setTimeout(function() {
                $('#btnGenerateAI').click();
            }, 300);
        }
    </script>
@endif

<!-- Modal Tambah Kategori -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="formAddCategory">
      <div class="modal-header">
        <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori Baru</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="form-group">
            <label for="categoryName">Nama Kategori</label>
            <input type="text" class="form-control" id="categoryName" name="name" required placeholder="Masukkan nama kategori">
            <input type="hidden" name="status" value="publish">
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSaveCategory">Simpan</button>
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
                    
                    btn.attr('disabled', false).html('Simpan');
                } else {
                    alert('Gagal menambahkan kategori');
                    btn.attr('disabled', false).html('Simpan');
                }
            },
            error: function(err) {
                let msg = 'Terjadi kesalahan.';
                if(err.responseJSON && err.responseJSON.message) {
                    msg = err.responseJSON.message;
                }
                alert(msg);
                btn.attr('disabled', false).html('Simpan');
            }
        });
    });
</script>
@endpush
@endif

@endsection