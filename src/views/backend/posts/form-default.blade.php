@extends('cms::backend.layout.app', ['title' => get_post_type('title_crud')])
@section('content')
        <form class="editorForm" action="{{ route(get_post_type() . '.update', $post->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('put')
            <div class="row">
                <div class="col-lg-12">
                    <h3 style="font-weight:normal">
                        <i class="fa {{ $module->icon }}" aria-hidden="true"></i> {{ get_post_type('title_crud') }}
                        <div class="btn-group pull-right">
                            @if(View::exists('template.' . template() . '.' . $post->type . '.' . $post->slug))
                            <a href="{{ route('appearance.editor') . '?edit=/' . $post->type . '/' . $post->slug . '.blade.php' }}" class="btn btn-warning btn-sm"> <i class="fa fa-edit"></i> Edit Halaman {!! help('Tombol ini akan muncul ketika ' . $module->title . ' ini memiliki custom page pada tampilan. Klik untuk mulai mengedit') !!}</a>
                            @endif
                            <a href="{{ route(get_post_type()) }}" class="btn btn-danger btn-sm "
                            data-toggle="tooltip" title="Kembali Ke Index Data"> <i class="fa fa-undo" aria-hidden></i>
                            Kembali</a>

                        </div>
                    </h3>
                    <br>
                    @if (!empty($post && $module->web->detail && $post->title && $post->status == 'publish') && $module->public)
                        <div style="border-left:3px solid green" class="alert alert-success"><b>URL : </b><a
                                title="Kunjungi URL" data-toggle="tooltip" href="{{ url($post->url) }}"
                                target="_blank"><i class="url"><u>{{ url($post->url) }}</u></i>  </a><span class="custom-url"></span> <i class="fa fa-edit ml-2 pointer"  data-post-url="{{ url($post->url) }}"
                                    data-slug="{{ $post->slug }}"></i><span
                                title="Klik Untuk Menyalin alamat URL {{ $module->title }}" data-toggle="tooltip"
                                class="pointer copy pull-right badge badge-primary" data-copy="{{ url($post->url) }}"><i
                                    class="fa fa-copy" aria-hidden></i> <b>Salin</b></span></div>
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
                </div>
                <div class="col-lg-9">
                    <div class="form-group">
                        <input data-toggle="tooltip" title="Masukkan {{ $module->datatable->data_title }}" required
                            name="title" type="text" value="{{ !empty(old('title')) ? old('title') : ($post->title ?? null) }}"
                            placeholder="Masukkan {{ $module->datatable->data_title }}" class="form-control form-control-lg" >

                    </div>

                    @if ($module->form->editor)
                        <div class="form-group">

                              @if($post->type == 'docs')
                              @php $type = "application/x-httpd-php"; @endphp
                              <textarea name="content" placeholder="Dokumentasi" id="editor" class="custom_html">{{ $post->content ?? '' }}</textarea>
                              @include('cms::backend.layout.codemirrorjs')
                              @else
                                <textarea name="content" placeholder="Keterangan..." id="editor">{{ !empty(old('content')) ? old('content') : ($post->content ?? '') }}</textarea>
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
                        <select @if (isset($pp[3]) && $pp[3] == 'required') required @endif  data-live-search="true"  class="selectpicker form-control form-control-sm"
                            name="parent_id">
                            <option value="">--pilih--</option>

                            @foreach ($par as $row)
                                <option @if ($post && $post->parent_id == $row->id) selected @endif value="{{ $row->id }}">
                                    {{ $row->title }} {{ $row->parent ? ' - ' . $row->parent->title . ($row->parent->parent ? ' - ' . $row->parent->parent->title : '') : ''}}</option>
                            @endforeach

                        </select>
                        @push('styles')
                        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
                        @endpush
                        @push('scripts')
                        <!-- Latest compiled and minified JavaScript -->
                        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>

                        <!-- (Optional) Latest compiled and minified JavaScript translation files -->
                        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-*.min.js"></script>
                        @endpush

                    @endif

                    @if ($module->form->custom_field)
                        @include('cms::backend.posts.custom_field.form')

                    @endif
                    @if ($module->form->looping_data)
                    @include('cms::backend.posts.looping_data.form')
                    @endif
                </div>
                <div class="col-lg-3">
                    @if ($module->form->thumbnail)
                        <div class="card">
                            <p class="card-header"> <i class="fa fa-image" aria-hidden></i> Gambar</p>

                            <img class="img-responsive" style="border:none" id="thumb" src="{{ $post->thumbnail }}"/>
                            <input accept="image/png,image/jpeg,image/webp,image/gif" type="file" class="compress-image form-control-file form-control-sm"
                                name="media" value="">
                            @if ($module->web->index && $module->web->detail)
                                <span style="padding:10px">
                                    <textarea placeholder="Keterangan Gambar" type="text" class="form-control form-control-sm"
                                        name="media_description">{{ !empty(old('media_description')) ? old('media_description') : ($post->media_description ?? '') }}</textarea>
                                </span>
                            @endif

                        </div>

                    @endif

                    @if ($module->web->detail || $modname = $module->name == 'banner')
                        <small>Pengalihan URL {!! help('Opsi Jika Ingin Mengalihkan Konten Ini ke suatu halaman web atau url') !!} </small>
                        <input type="text" class="form-control form-control-sm" name="redirect_to"
                            placeholder="URL dimulai https:// atau http://" value="{{ !empty(old('redirect_to')) ? old('redirect_to') : ($post->redirect_to ?? '') }}">
                        @if(!isset($modname))
                        <small for="">Deskripsi {!! help('Opsi deskripsi singkat tentang konten yang dapat ditelusuri oleh mesin pencarian') !!} </small>
                        <textarea placeholder="Tulis Deskripsi" type="text" class="form-control form-control-sm" name="description">{{ !empty(old('description')) ? old('description') : ($post->description ?? '') }}</textarea>
                        <small for="">Kata Kunci {!! help('Kata kunci tentang konten yang dapat ditelusuri oleh mesin pencarian') !!}</small>
                        <input placeholder="Keyword1,Keyword2,Keyword3" type="text" class="form-control form-control-sm"
                            name="keyword" value="{{ !empty(old('keyword')) ? old('keyword') : ($post->keyword ?? '') }}">
                            @endif
                    @if ($module->form->tag)

                            <small for="">Tags {!! help('Penanda untuk memudahkan pencarian topik') !!}</small>
                            <select name="tags[]" id="select2" class="form-control form-control-sm form-control-select" multiple id="">
                                @foreach($tags as $row)
                                <option  {{ in_array($row->id, $post->tags->pluck('id')->toArray()) ? 'selected' : '' }} value="{{  $row->id}}">{{ $row->name }}</option>
                                @endforeach
                            </select>

                    @else
                    @endif

                    @endif
                    @if ($module->form->category)
                        <small for="">Kategori {{ $module->title }} </small><br>
                        <select class="form-control form-control-sm" name="category_id">
                            <option value=""> --pilih-- </option>
                            @foreach ($category as $row)
                                <option value="{{ $row->id }}"
                                    {{ $row->id == $post->category_id ? 'selected=selected' : '' }}>{{ $row->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-right"><small class="text-primary"><a href="{{ route($post->type . '.category') }}"> <i
                                        class="fa fa-plus" aria-hidden></i> Tambah Baru</a></small></div>
                    @else
                    @endif
                    @if ($module->web->sortable)
                    <small for="">Urutan {!! help('Urutan konten yang akan ditampilkan') !!}</small>
                    <select class="form-control form-control-sm" name="sort">
                        @php $count = query()->onType(get_post_type())->count();@endphp
                        @for ($i = 1; $i <= $count; $i++)
                            <option value="{{ $i }}"  {{ $post->sort == $i ? 'selected=selected' : '' }}>{{ $i }}
                            </option>
                        @endfor
                    </select>
                    <div class="mb-2"></div>

                    @else
                    <div class="mb-2"></div>

                    @endif

                    @if ($module->web->detail)

                        <div @if (!$module->web->detail) ) style="margin-top:10px" @endif class="animated-checkbox">
                            <label>
                                <input type="checkbox" {{ $post && $post->allow_comment == 'Y' ? 'checked=checked' : '' }}
                                    name="allow_comment" value="Y"><span class="label-text"><small>Izinkan Komentar
                                        {!! help('Jika dicentang, maka pengunjung bisa mengirim komentar pada postingan ini') !!}</small></span>
                            </label>
                        </div>
                    @endif
                        <div class="animated-checkbox">
                            <label>
                                <input {{ $post && $post->pinned == 'Y' ? 'checked=checked' : '' }} type="checkbox"
                                    name="pinned" value="Y"><span class="label-text"><small>Sematkan
                                        {!! help('Jika dicentang, maka postingan ini akan menjadi prioritas dihalaman jika dikondisikan pada template ') !!}</small></span>
                            </label>
                        </div>
                    <div class="form-group form-inline">
                        <div class="animated-radio-button">
                            <label>
                                <input {{ $post && $post->status == 'publish' ? 'checked=checked' : '' }} required
                                    type="radio" name="status" value="publish"><small
                                    class="label-text">Publikasikan</small>
                            </label>
                        </div>
                        &nbsp;&nbsp;&nbsp;
                        <div class="animated-radio-button">
                            <label>
                                <input {{ $post && $post->status == 'draft' ? 'checked=checked' : '' }} required type="radio"
                                    name="status" value="draft"><small class="label-text">Draft</small>
                            </label>
                        </div>
                    </div>
                    <button type="submit" data-toggle="tooltip" title="Simpan Perubahan"  class="btn btn-md btn-primary w-100 add"> <i class="fa fa-save"></i> <span class="text-save">Simpan</span> </button><br><br>
                </>
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
                      $('.editorFormd').on('submit', function (e) {
                            e.preventDefault();
                            $('.text-save').html('Menyimpan...');
                            $('.btn-primary').attr('disabled', 'disabled');
                            let form = this;
                            let actionUrl = $(form).attr('action');
                            let formData = new FormData(form);
                            formData.append('_method', 'PUT');
                          $.ajaxSetup({
                              headers: {
                                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
                                    location.reload();
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
                                }

                            });
                        });
                    </script>
            @include('cms::backend.layout.js')

        @endpush

@endsection
