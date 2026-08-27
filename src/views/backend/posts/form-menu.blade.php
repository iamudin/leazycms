@extends('cms::backend.layout.app', ['title' => get_post_type('title_crud')])
@section('content')
    <form class="editorForm" action="{{URL::full()}}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal">
                    <i class="fa {{ $module->icon }}" aria-hidden="true"></i> {{ get_post_type('title_crud') }}
                    <div class="btn-group pull-right">
                        <a href="{{ route(get_post_type()) }}" class="btn btn-danger btn-sm" data-toggle="tooltip"
                            title="Kembali Ke Index Data"> <i class="fa fa-undo" aria-hidden></i>
                            Kembali</a>

                    </div>
                </h3>
                <br>
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
                    @if($looping_data)
                        <input type="hidden" name="title" value="{{ $post->title ?? '' }}">
                        <div class="alert alert-primary py-2" style="border-left:4px solid #000;font-size:20px">
                            {{ $post->title ?? '' }}
                        </div>
                    @else
                        <input data-toggle="tooltip" title="Masukkan {{ $module->datatable->data_title }}" required name="title"
                            type="text" value="{{ $post->title ?? '' }}"
                            placeholder="Masukkan {{ $module->datatable->data_title }}" class="form-control form-control-lg">
                    @endif

                </div>
                @include('cms::backend.posts.list-menu')

            </div>
            <div class="col-lg-3">
                <div class="sticky-sidebar-content" style="position: -webkit-sticky; position: sticky; top: 65px; z-index: 10; max-height: calc(100vh - 80px); overflow-y: auto; overflow-x: hidden; padding-bottom: 20px;">
                <!-- Desktop Status Toggle (Visible only on large screens) -->
                <div class="d-none d-lg-block mb-3">
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
                
       

                </div>
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
            .autocomplete-box {
                position: relative;
            }

            .autocomplete-results {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                border: 1px solid #ccc;
                background: #fff;
                z-index: 999;
                max-height: 200px;
                overflow-y: auto;
                display: none;
            }

            .autocomplete-results div {
                padding: 8px;
                cursor: pointer;
            }

            .autocomplete-results div:hover {
                background: #f0f0f0;
            }
        </style>
        <style type="text/css">
            body {
                overflow-x: hidden;
            }

            .dd {
                position: relative;
                display: block;
                margin: 0;
                padding: 0;
                max-width: 100vw;
                list-style: none;
                font-size: 13px;
                line-height: 20px;
            }

            .dd-list {
                display: block;
                position: relative;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .dd-list .dd-list {
                padding-left: 30px;
            }

            .dd-collapsed .dd-list {
                display: none;
            }

            .dd-item,
            .dd-empty,
            .dd-placeholder {
                display: block;
                position: relative;
                margin: 6px 0;
                padding: 0;
                min-height: 40px;
                font-size: 13px;
                line-height: 20px;
            }

            .dd-handle {
                display: block;
                height: 40px;
                line-height: 28px;
                margin: 0;
                padding: 5px 10px;
                color: #333;
                text-decoration: none;
                font-weight: bold;
                border: 1px solid #ccc;
                background: #fafafa;
                background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
                background: -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
                background: linear-gradient(top, #fafafa 0%, #eee 100%);
                -webkit-border-radius: 3px;
                border-radius: 3px;
                box-sizing: border-box;
                -moz-box-sizing: border-box;
            }

            .dd-handle:hover {
                color: #2ea8e5;
                background: #fff;
            }

            .dd-item>button {
                display: block;
                position: absolute !important;
                left: 42px !important;
                top: 10px !important;
                width: 20px !important;
                height: 20px !important;
                line-height: 18px !important;
                margin: 0 !important;
                padding: 0 !important;
                text-indent: 100% !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                border: 1px solid #cbd5e1 !important;
                background: #f8fafc !important;
                border-radius: 4px !important;
                color: #475569 !important;
                font-size: 0 !important;
                text-align: center !important;
                cursor: pointer !important;
                z-index: 6 !important;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
                float: none !important;
                box-sizing: border-box !important;
            }

            .dd-item>button[style*="display: none"],
            .dd-item>button[style*="display:none"] {
                display: none !important;
            }

            .dd-item>button:hover {
                background: #e2e8f0 !important;
                color: #0f172a !important;
                border-color: #94a3b8 !important;
            }

            .dd-item>button:before {
                content: '+' !important;
                display: block !important;
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: 100% !important;
                line-height: 18px !important;
                text-align: center !important;
                text-indent: 0 !important;
                font-size: 13px !important;
                font-weight: bold !important;
                color: #475569 !important;
                font-family: inherit !important;
            }

            .dd-item>button[data-action="collapse"]:before {
                content: '−' !important;
                font-size: 15px !important;
                line-height: 16px !important;
            }

            .dd3-item > button ~ .dd3-content {
                padding-left: 70px !important;
            }

            .dd-placeholder,
            .dd-empty {
                margin: 6px 0;
                padding: 0;
                min-height: 40px;
                background: #f2fbff;
                border: 1px dashed #b6bcbf;
                border-radius: 3px;
                box-sizing: border-box;
            }

            .dd-empty {
                border: 1px dashed #bbb;
                min-height: 100px;
                background-color: #e5e5e5;
                background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                    -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
                background-image: -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                    -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
                background-image: linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                    linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
                background-size: 60px 60px;
                background-position: 0 0, 30px 30px;
            }

            .dd-dragel {
                position: absolute;
                pointer-events: none;
                z-index: 9999;
            }

            .dd-dragel>.dd-item .dd-handle {
                margin-top: 0;
            }

            .dd-dragel .dd-handle {
                -webkit-box-shadow: 2px 4px 6px 0 rgba(0, 0, 0, .1);
                box-shadow: 2px 4px 6px 0 rgba(0, 0, 0, .1);
            }



            .nestable-lists {
                display: block;
                clear: both;
                padding: 30px 0;
                width: 100%;
                border: 0;
                border-top: 2px solid #ddd;
                border-bottom: 2px solid #ddd;
            }

            #nestable-menu {
                padding: 0;
                margin: 20px 0;
            }

            #nestable-output,
            #nestable2-output {
                width: 100%;
                height: 7em;
                font-size: 0.75em;
                line-height: 1.333333em;
                font-family: Consolas, monospace;
                padding: 5px;
                box-sizing: border-box;
                -moz-box-sizing: border-box;
            }

            #nestable2 .dd-handle {
                color: #fff;
                border: 1px solid #999;
                background: #bbb;
                background: -webkit-linear-gradient(top, #bbb 0%, #999 100%);
                background: -moz-linear-gradient(top, #bbb 0%, #999 100%);
                background: linear-gradient(top, #bbb 0%, #999 100%);
            }

            #nestable2 .dd-handle:hover {
                background: #bbb;
            }

            #nestable2 .dd-item>button:before {
                color: #fff;
            }

            @media only screen and (min-width: 700px) {

                .dd {
                    float: left;
                    width: 100%;
                }

                .dd+.dd {
                    margin-left: 2%;
                }

            }

            .dd-hover>.dd-handle {
                background: #2ea8e5 !important;
            }


            .dd3-content {
                display: block;
                height: 40px;
                line-height: 28px;
                margin: 0 !important;
                padding: 5px 12px 5px 46px;
                color: #333;
                text-decoration: none;
                font-weight: 600;
                border: 1px solid #ccc;
                background: #fafafa;
                background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
                background: -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
                background: linear-gradient(top, #fafafa 0%, #eee 100%);
                border-radius: 3px;
                box-sizing: border-box;
                font-size: 13.5px;
            }

            .dd3-content:hover {
                color: #2ea8e5;
                background: #fff;
            }

            .dd-dragel>.dd3-item>.dd3-content {
                margin: 0;
            }

            .dd3-item>button {
                margin-left: 36px;
            }

            .dd3-item > .dd-handle,
            .dd3-handle {
                position: absolute;
                margin: 0 !important;
                left: 0;
                top: 0;
                cursor: move;
                width: 36px;
                height: 40px;
                text-indent: 100%;
                white-space: nowrap;
                overflow: hidden;
                border: 1px solid #aaa;
                background: #ddd;
                background: -webkit-linear-gradient(top, #ddd 0%, #bbb 100%);
                background: -moz-linear-gradient(top, #ddd 0%, #bbb 100%);
                background: linear-gradient(top, #ddd 0%, #bbb 100%);
                border-top-left-radius: 3px;
                border-bottom-left-radius: 3px;
                border-top-right-radius: 0;
                border-bottom-right-radius: 0;
                box-sizing: border-box;
                padding: 0 !important;
            }

            .dd3-handle:before {
                content: '≡';
                display: block;
                position: absolute;
                left: 0;
                top: 0;
                height: 38px;
                line-height: 36px;
                width: 100%;
                text-align: center;
                text-indent: 0;
                color: #fff;
                font-size: 20px;
                font-weight: normal;
            }

            .dd3-handle:hover {
                background: #ddd;
            }

            .menu-action-buttons {
                float: right;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                height: 28px;
                line-height: 28px;
            }

            .menu-action-buttons a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                text-decoration: none;
                line-height: 1;
            }



            .socialite {
                display: block;
                float: left;
                height: 35px;
            }
        </style>
    @endpush

    @push('scripts')
        <script>

            const inputs = document.getElementById('link_target');
            const listBox = document.getElementById('autocomplete-list');

            inputs.addEventListener('input', function () {
                let query = this.value.trim();
                if (query.length < 3) {
                    listBox.style.display = 'none';
                    return;
                }

                fetch('{{route("menu-target")}}?q=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        listBox.innerHTML = '';
                        if (data.length === 0) {
                            listBox.style.display = 'none';
                            return;
                        }
                        data.forEach(item => {
                            let div = document.createElement('div');
                            div.innerHTML = `${item.title}<br><span style="color:blue;font-size:small">{{url('')}}/${item.url}</span>`;
                            div.onclick = function () {
                                inputs.value = item.url;
                                listBox.style.display = 'none';
                            };
                            listBox.appendChild(div);
                        });
                        listBox.style.display = 'block';
                    });
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('.autocomplete-box')) {
                    listBox.style.display = 'none';
                }
            });
        </script>
        <script src="{{asset('backend/js/jquery.nestable.js')}}"></script>
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

            $(document).ready(function() {
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
                let form = this;
                let actionUrl = $(form).attr('action');
                let formData = new FormData(form);
                formData.append('_method', 'PUT');

                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        notif('Berhasil menyimpan menu!', 'success');
                        
                        // Reset status buttons
                        let val = $('input[name="status"]:checked').val();
                        if (val) {
                            let $btn = $('input[name="status"][value="' + val + '"]').parent();
                            let $icon = $btn.find('i');
                            $icon.removeClass('fa-spinner fa-spin').addClass(val === 'publish' ? 'fa-globe' : 'fa-archive');
                            $btn.contents().filter(function () {
                                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                            }).each(function () {
                                this.nodeValue = val === 'publish' ? ' Publikasikan' : ' Draft';
                            });
                            $btn.siblings('label').css('pointer-events', 'auto').fadeTo(200, 1);
                        }
                    },
                    error: function (xhr) {
                        let errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                        if (errors) {
                            let firstError = Object.values(errors)[0][0];
                            notif(firstError, 'danger');
                        } else {
                            notif('Terjadi kesalahan!', 'danger');
                        }

                        // Reset status buttons
                        let val = $('input[name="status"]:checked').val();
                        if (val) {
                            let $btn = $('input[name="status"][value="' + val + '"]').parent();
                            let $icon = $btn.find('i');
                            $icon.removeClass('fa-spinner fa-spin').addClass(val === 'publish' ? 'fa-globe' : 'fa-archive');
                            $btn.contents().filter(function () {
                                return this.nodeType === 3 && $.trim(this.nodeValue) !== '';
                            }).each(function () {
                                this.nodeValue = val === 'publish' ? ' Publikasikan' : ' Draft';
                            });
                            $btn.siblings('label').css('pointer-events', 'auto').fadeTo(200, 1);
                        }
                    }
                });
            });
        </script>
    @endpush
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