@extends('cms::backend.layout.app', ['title' => 'Setting › Template › Edit'])
@section('content')
                                    <div class="row">
                                        <div class="col-lg-12 mb-3">
                                            <h3 style="font-weight:normal;float: left;"> <i class="fa fa-paint-brush"></i> Setting › Template › Edit  </h3>
                                            <div class="pull-right">

                                                <div class="btn-group">
                 <button type="button" class="btn btn-primary"  onclick="$('#generateModuleModal').modal('show')">
    <i class="fa fa-plus"></i> Tambah Post Type Baru
</button>
                                                    <button type="button" onclick="$('.editorForm').trigger('submit')" class="btn btn-primary btn-sm"> <i
                                                            class="fa fa-save"></i> <span class="save-text">Simpan</span></button>
                                                    <a href="{{route('appearance')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i>
                                                        Kembali</a>
                                                </div>
                                            </div>

                                        </div>
                                        @if(get_option('site_maintenance') == 'N')
                                            <div class="col-lg-12">
                                                <div class="alert alert-warning">
                                                    <i class="fa fa-warning"></i> Status Maintenance tidak aktif. Aktifkan pada menu <b>Pengaturan</b> <i
                                                        class="fa fa-arrow-right"></i> <b>Situs Web</b>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-lg-2">
                                            <h6> <i class="fa fa-folder"></i> /{{ template() }}/ <span class="pull-right text-danger"><i
                                                        class="fa fa-folder-plus pointer" onclick="folderPrompt()" title="Create Folder"></i> &nbsp; <i
                                                        class="fa fa-file-circle-plus  pointer" onclick="filePrompt()" title="Create File"></i> </span></h6>
                                            <div style="max-height: 74vh;overflow:auto;padding-right:10px">

                                                @php
    $treeData = [];
    $data = getDirectoryContents(null, $treeData);
    renderTemplateFile($treeData);
                                                @endphp
                                                <ul style="padding:0;list-style: none;margin:0">
                                                    <li> <i class="fa fa-file-code"></i> <a href="{{ url()->current() . '?edit=' . enc64("/styles.css") }}">
                                                            styles.css</a></li>
                                                    <li> <i class="fa fa-file-code"></i> <a href="{{ url()->current() . '?edit=' . enc64("/scripts.js") }}">
                                                            scripts.js</a></li>
                                                </ul>
                                                @if($controllers = config('modules.custom_controllers'))
                                                    <ul style="padding:0;list-style: none;margin:10px 0 0 0">
                                                        <li><i class="fa fa-gears"></i> Controllers</li>
                                                        @foreach($controllers as $row)
                                                            <li style="padding-left:20px"><a href="{{ url()->current() . '?edit=' . enc64($row) }}">
                                                                @if(file_exists(app_path('Http/Controllers/' . $row)))
                                                                    {{ $row }}
                                                                @else 
                                                                    <span class="text-danger">{{ $row }}</span>
                                                                @endif
                                                                </a></li>
                                                        @endforeach

                                                    </ul>
                                                @endif

                                            </div>
                                        </div>


                                        <div class="col-lg-10">
                                            <form action="{{ URL::full() }}" class="editorForm" method="post" enctype="multipart/form-data">
                                                @csrf
                                                @if($e = dec64(request()->edit))
                                                    <h6> <i class="fa fa-edit"></i> {{  'Edit : ' . $e  }}
                                                        @if(!str(basename($e))->contains(['modules.blade.php', 'home.blade.php', 'header.blade.php', 'footer.blade.php', 'styles.css', 'scripts.css']))<i
                                                            onclick="deleteFile('{{ $e }}')" class="fa fa-trash-o text-danger pointer"
                                                        title="Delete this file "></i>@endif
                                                        @if(str(request()->edit)->contains('modules'))
                                                            <span class="pointer badge badge-primary pull-right"><i class="fa fa-question-circle"></i> Petunjuk
                                                                Custom Modul</span>
                                                        @endif
                                                    </h6>

                                                @else
                                                    <h6> <i class="fa fa-edit"></i> {{  'Edit : /home.blade.php'  }}</h6>

                                                @endif
                                                <input type="hidden" name="type" value="change_file">

                                                <textarea id="editor" name="file_src" class="custom_html">
                                                                                        {{ $view }}
                                                                                        </textarea>
                                            </form>
                                        </div>
                                    </div>

                                        @include('cms::backend.layout.codemirrorjs')
                                        @push('scripts')
                                            <script>
                                                $('.editorForm').on('submit', function (e) {
                                                    e.preventDefault();

                                                    if (window.editor) {
                                                        editor.save();
                                                    }
                                                    $('.save-text').html('Menyimpan...');
                                                    $('.btn-primary').attr('disabled', 'disabled');
                                                    let form = this;
                                                    let actionUrl = $(form).attr('action');
                                                    let formData = new FormData(form);

                                                    $.ajax({
                                                        url: actionUrl,
                                                        method: 'POST',
                                                        data: formData,
                                                        processData: false,
                                                        contentType: false,
                                                        success: function (response) {
                                                            notif('Berhasil menyimpan perubahan!', 'success');
                                                            $('.save-text').html('Simpan');
                                                            $('.btn-primary').removeAttr('disabled');
                                                        },
                                                        error: function (xhr, status, error) {
                                                            console.error(xhr.responseText);
                                                             $('.save-text').html('Simpan');
                                                            $('.btn-primary').removeAttr('disabled');
                                                            notif(xhr.responseText, 'danger');
                                                        }
                                                    });
                                                });
                                            </script>
                                            <script>

                                                function folderPrompt() {
                                                    var userInput = prompt("Folder name :", "");
                                                    if (userInput != null) {

                                                        $.post('{{ route('appearance.editor') }}', { type: 'create_dir', dirname: userInput, _token: '{{ csrf_token() }}' }, function (response) {
                                                            location.reload();
                                                        }).fail(function (xhr, status, error) {
                                                            console.error('Error:', error);
                                                        });
                                                    }
                                                }
                                                function deleteFile(file) {
                                                    if (confirm('Sure delete this file ? Cannot Undo Action')) {

                                                        $.post('{{ route('appearance.editor') }}', { type: 'delete_file', filename: file, _token: '{{ csrf_token() }}' }, function (response) {
                                                            location.href='{{ url()->current() }}';

                                                        }).fail(function (xhr, status, error) {
                                                            console.error('Error:', error);
                                                        });
                                                    }
                                                }
                                                function filePrompt(current) {
                                                    var userInput = prompt("File name (without any ekstension) :", "");
                                                    if (userInput != null) {
                                                        $.post('{{ route('appearance.editor') }}', { type: 'create_file', filepath: current, filename: userInput, _token: '{{ csrf_token() }}' }, function (response) {
                                                            location.reload();
                                                        }).fail(function (xhr, status, error) {
                                                            console.error('Error:', error);
                                                        });
                                                    }
                                                }

                                            </script>
                                            @include('cms::backend.layout.js')

                                        @endpush
{{-- ==================== MODAL GENERATE MODULE - BOOTSTRAP 4 ==================== --}}
<div class="modal fade" id="generateModuleModal" tabindex="-1" role="dialog" aria-labelledby="generateModuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="generateModuleModalLabel">
                    <i class="fa fa-cube"></i> Generate Post Type Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form id="moduleForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Position</label>
                                <input type="number" id="position" class="form-control" value="8" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Name (Slug)</label>
                                <input type="text" id="name" class="form-control text-lowercase" placeholder="countdown" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Title</label>
                                <input type="text" id="title" class="form-control" placeholder="Hitung Mundur" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Icon</label>
                                <input type="text" id="icon" class="form-control" value="fa-hourglass">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Description</label>
                        <input type="text" id="description" class="form-control" placeholder="Menu Untuk Mengelola Pengumuman">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Parent (false atau slug parent)</label>
                                <input type="text" id="parent" class="form-control" value="false">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-4">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="public" checked>
                                    <label class="custom-control-label" for="public">Public (tampil di frontend)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Datatable -->
                    <h6 class="mt-4 text-primary">Datatable Settings</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Data Title</label>
                                <input type="text" id="data_title" class="form-control" placeholder="Nama Kolom Utama">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Custom Column (pisah dengan koma)</label>
                                <input type="text" id="custom_column" class="form-control" placeholder="Tanggal Berangkat">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Child Count (pisah dengan koma)</label>
                                <input type="text" id="child_count" class="form-control" placeholder="jadwal,cinta">
                            </div>
                        </div>
                    </div>

                    <!-- Form Settings -->
                    <h6 class="mt-4 text-primary">Form Settings</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="thumbnail" checked>
                                <label class="custom-control-label" for="thumbnail">Thumbnail</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="editorform">
                                <label class="custom-control-label" for="editorform">Editor</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="category">
                                <label class="custom-control-label" for="category">Category</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="tag">
                                <label class="custom-control-label" for="tag">Tag</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Looping Name</label>
                                <input type="text" id="looping_name" class="form-control" value="Arsip">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Looping Data (satu baris per field)</label>
                                <textarea id="looping_data" class="form-control" rows="3" 
                                    placeholder="Nama Waktu, text, required&#10;Jenis Waktu, text, required"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Custom Field (satu baris per field)</label>
                        <textarea id="custom_field" class="form-control" rows="4"
                            placeholder="Tanggal Berangkat, date, required"></textarea>
                        <small class="text-muted">Format: Label, tipe, required (opsional)</small>
                    </div>

                    <!-- Web Settings -->
                    <h6 class="mt-4 text-primary">Web (Frontend)</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="api">
                                <label class="custom-control-label" for="api">API</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="archive">
                                <label class="custom-control-label" for="archive">Archive</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="index">
                                <label class="custom-control-label" for="index">Index</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="detail">
                                <label class="custom-control-label" for="detail">Detail</label>
                            </div>
                        </div>
                    </div>
                </form>

                <hr>
                <h6>Hasil Script:</h6>
                <pre id="output" class="bg-dark text-light p-3 rounded overflow-auto" 
                     style="max-height: 320px; font-size: 13px; white-space: pre-wrap;"></pre>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" onclick="generateModule()" class="btn btn-primary">
                    <i class="fa fa-magic"></i> Generate Script
                </button>
                <button type="button" onclick="copyToClipboard()" class="btn btn-success">
                    <i class="fa fa-copy"></i> Copy
                </button>
                <button type="button" onclick="appendToModulesFile()" class="btn btn-warning">
                    <i class="fa fa-file-import"></i> Sisipkan ke modules.blade.php
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==================== JAVASCRIPT (Bootstrap 4 Compatible) ==================== --}}
@push('scripts')
<script>
    window.generateModule = function() {
        const position     = parseInt(document.getElementById('position').value) || 10;
        const name         = document.getElementById('name').value.trim().toLowerCase();
        const title        = document.getElementById('title').value.trim();
        const description  = document.getElementById('description').value.trim() || title;
        const icon         = document.getElementById('icon').value.trim() || 'fa-circle';
        const parent       = document.getElementById('parent').value.trim() || 'false';
        const isPublic     = document.getElementById('public').checked;

        const data_title   = document.getElementById('data_title').value.trim() || `Nama ${title}`;

        let customColumn = [];
        const ccText = document.getElementById('custom_column').value.trim();
        if (ccText) customColumn = ccText.split(',').map(item => `'${item.trim()}'`);

        let childCount = [];
        const childText = document.getElementById('child_count').value.trim();
        if (childText) childCount = childText.split(',').map(item => `'${item.trim()}'`);

        let loopingData = [];
        const ldText = document.getElementById('looping_data').value.trim();
        if (ldText) {
            ldText.split('\n').forEach(line => {
                if (line.trim() === '') return;
                const parts = line.split(',').map(p => p.trim());
                if (parts.length >= 2) {
                    const req = parts[2] ? `, '${parts[2]}'` : '';
                    loopingData.push(`                    ['${parts[0]}', '${parts[1]}'${req}],`);
                }
            });
        }

        let customFields = [];
        const cfText = document.getElementById('custom_field').value.trim();
        if (cfText) {
            cfText.split('\n').forEach(line => {
                if (line.trim() === '') return;
                const parts = line.split(',').map(p => p.trim());
                if (parts.length >= 2) {
                    const req = parts[2] ? `, '${parts[2]}'` : '';
                    customFields.push(`                    ['${parts[0]}', '${parts[1]}'${req}],`);
                }
            });
        }

        const script = `add_module([
            'position' => ${position},
            'name' => '${name}',
            'title' => '${title}',
            'description' => '${description}',
            'parent' => ${parent},
            'icon' => '${icon}',
            'route' => ['index', 'create', 'show', 'update', 'delete'],
            'datatable' => [
                'custom_column' => [${customColumn.join(', ')}],
                'data_title' => '${data_title}',
                'child_count'=> [${childCount.join(', ')}],
            ],
            'form' => [
                'unique_title' => false,
                'post_parent' => false,
                'thumbnail' => ${document.getElementById('thumbnail').checked},
                'editor' => ${document.getElementById('editorform').checked},
                'category' => ${document.getElementById('category').checked},
                'tag' => ${document.getElementById('tag').checked},
                'looping_name' => '${document.getElementById('looping_name').value.trim() || 'Arsip'}',
                'looping_data' => [
${loopingData.join('\n')}
                ],
                'custom_field' => [
${customFields.join('\n')}
                ]
            ],
            'web' => [
                'api' => ${document.getElementById('api').checked},
                'archive' => ${document.getElementById('archive').checked},
                'index' => ${document.getElementById('index').checked},
                'detail' => ${document.getElementById('detail').checked},
                'history' => false,
                'auto_query' => false,
                'sortable' => false,
            ],
            'public' => ${isPublic},
            'active' => true,
        ]);`;

        document.getElementById('output').textContent = script;
    };

    window.copyToClipboard = function() {
        const text = document.getElementById('output').textContent.trim();
        if (!text) {
            alert('Klik "Generate Script" terlebih dahulu!');
            return;
        }
        navigator.clipboard.writeText(text).then(() => {
            alert('✅ Script berhasil disalin ke clipboard!');
        });
    };

    window.appendToModulesFile = function() {
        const script = document.getElementById('output').textContent.trim();
        if (!script) {
            alert('Generate script dulu sebelum menyisipkan!');
            return;
        }
        if (!confirm('Yakin ingin menambahkan module ini ke baris terakhir modules.blade.php?')) return;

        fetch('/admin/modules/append', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ script: script + "\n\n" })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✅ Berhasil ditambahkan ke modules.blade.php!');
                $('#generateModuleModal').modal('hide');
            } else {
                alert('❌ Gagal: ' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(() => alert('Terjadi kesalahan saat menghubungi server'));
    };
</script>
@endpush
@endsection