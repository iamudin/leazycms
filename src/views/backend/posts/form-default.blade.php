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
                            data-slug="{{ $post->slug }}"></i><span title="Klik Untuk Menyalin alamat URL {{ $module->title }}"
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

                            document.querySelectorAll('.fa-edit').forEach(icon => {
                                icon.addEventListener('click', function () {
                                    const postUrl = this.dataset.postUrl;
                                    const slug = this.dataset.slug;
                                    enableCustomSlugEdit(postUrl, slug);
                                });
                            });

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
                        <input data-toggle="tooltip" title="Masukkan {{ $module->datatable->data_title }}" required name="title"
                            type="text" value="{{ !empty(old('title')) ? old('title') : ($post->title ?? null) }}"
                            placeholder="Masukkan {{ $module->datatable->data_title }}" class="form-control form-control-lg">
                    @else
                        <input type="hidden" name="title" value="{{ $post->title ?? null }}">
                        <label for="">{{ $module->datatable->data_title }}</label>
                        <h3>{{ $post->title ?? null }}</h3>
                    @endif

                </div>

                @if ($module->form->editor)
                    <div class="form-group">
                        @if (isset($module->form->ai_generator) && $module->form->ai_generator)
                        <button type="button" class="btn btn-sm btn-outline-primary mb-2" data-toggle="modal" data-target="#promptGeneratorModal" onclick="$('#prompt_topic').val($('input[name=title]').val())">
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
                        <p class="card-header"> <i class="fa fa-image" aria-hidden></i> Gambar Unggulan</p>
                        <img class="img-responsive" style="border:none" id="thumb" src="{{ $post->thumbnail }}" />
                        <input accept="image/png,image/jpeg,image/webp,image/gif" type="file"
                            class="compress-image form-control-file form-control-sm" name="media" value="">
                        @if ($module->web->index && $module->web->detail)
                            <span style="padding:10px">
                                <textarea maxlength="200" placeholder="Keterangan Gambar" type="text" class="form-control form-control-sm"
                                    name="media_description">{{ !empty(old('media_description')) ? old('media_description') : ($post->media_description ?? '') }}</textarea>
                            </span>
                        @endif

                    </div>

                @endif

                @if ($module->web->detail || $modname = $module->name == 'banner')
                    <small>Pengalihan URL {!! help('Opsi Jika Ingin Mengalihkan Konten Ini ke suatu halaman web atau url') !!}
                    </small>
                    <input type="text" class="form-control form-control-sm" name="redirect_to"
                        placeholder="URL dimulai https:// atau http://"
                        value="{{ !empty(old('redirect_to')) ? old('redirect_to') : ($post->redirect_to ?? '') }}">
                    @if(!isset($modname))
                        <small for="">Deskripsi
                            {!! help('Opsi deskripsi singkat tentang konten yang dapat ditelusuri oleh mesin pencarian') !!}
                        </small>
                        <textarea maxlength="200" placeholder="Tulis Deskripsi" type="text" class="form-control form-control-sm"
                            name="description">{{ !empty(old('description')) ? old('description') : ($post->description ?? '') }}</textarea>
                        <small for="">Kata Kunci
                            {!! help('Kata kunci tentang konten yang dapat ditelusuri oleh mesin pencarian') !!}</small>
                        <input placeholder="Keyword1,Keyword2,Keyword3" type="text" class="form-control form-control-sm"
                            name="keyword" value="{{ !empty(old('keyword')) ? old('keyword') : ($post->keyword ?? '') }}">
                    @endif
                    @if ($module->form->tag)

                        <small for="">Tags {!! help('Penanda untuk memudahkan pencarian topik') !!}</small>
                        <select name="tags[]" id="select2" class="form-control form-control-sm form-control-select" multiple id="">
                            @foreach($tags as $row)
                                <option {{ in_array($row->id, $post->tags->pluck('id')->toArray()) ? 'selected' : '' }}
                                    value="{{  $row->id}}">{{ $row->name }}</option>
                            @endforeach
                        </select>

                    @else
                    @endif

                @endif
                @if ($module->form->category)
                    @if((config('modules.multisite_enabled') && $post->tenant_id == tenant()->id || is_main_domain() && $post->tenant_id == null) || !config('modules.multisite_enabled') && $module->form->category)
                        <small for="">Kategori {{ $module->title }} </small><br>
                        <select class="form-control form-control-sm" name="category_id">
                            <option value=""> --pilih-- </option>
                            @foreach (config('modules.multisite_enabled') ? $category->where('tenant_id', tenant()->id) : $category as $row)
                                <option value="{{ $row->id }}" {{ $row->id == $post->category_id ? 'selected=selected' : '' }}>
                                    {{ $row->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-right"><small class="text-primary"><a href="{{ route($post->type . '.category') }}"> <i
                                        class="fa fa-plus" aria-hidden></i> Tambah Baru</a></small></div>
                    @endif

                @else

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
                    <div class="custom-control custom-switch mb-3">
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
            $('.editorForm').on('submit', function (e) {
                e.preventDefault();
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
                                $('input[name="media"]').val('');
                            }

                            // Update Custom Fields Container
                            let newCustomFields = newDoc.getElementById('custom-fields-container');
                            if (newCustomFields && document.getElementById('custom-fields-container')) {
                                document.getElementById('custom-fields-container').innerHTML = newCustomFields.innerHTML;
                            }

                            // Update Looping Data Container
                            let newLoopingData = newDoc.getElementById('looping-data-container');
                            if (newLoopingData && document.getElementById('looping-data-container')) {
                                document.getElementById('looping-data-container').innerHTML = newLoopingData.innerHTML;
                            }

                            // Clear any lingering Gmedia preview wrappers (temporary previews)
                            $('.btn-clear-gmedia').click();

                            // Update URL bar dynamically
                            let newUrlBar = newDoc.getElementById('post-url-bar');
                            let currentUrlBar = document.getElementById('post-url-bar');
                            if (currentUrlBar && newUrlBar) {
                                currentUrlBar.innerHTML = newUrlBar.innerHTML;
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

@endsection