@extends('cms::backend.layout.app', ['title' => 'Setting › Template › Edit'])
@section('content')
                                    <div class="row">
                                        <div class="col-lg-12 mb-3">
                                            <h3 style="font-weight:normal;float: left;"> <i class="fa fa-paint-brush"></i> Setting › Template › Edit  </h3>
                                            <div class="pull-right">

                                                <div class="d-flex" style="gap: 5px;">
                                                @if(is_main_domain())
                                                    <form method="post" action="{{ route('appearance.editor') }}" style="display:inline; margin: 0;">
                                                        @csrf
                                                        <input type="hidden" name="type" value="export_template">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="fa fa-file-archive-o"></i> Export ZIP
                                                        </button>
                                                    </form>
                                                    @endif
                                                    
                                                    <button type="button" onclick="$('.editorForm').trigger('submit')" class="btn btn-primary btn-sm">
                                                        <i class="fa fa-save"></i> <span class="save-text">Simpan</span>
                                                    </button>
                                                    
                                                    <a href="{{route('appearance')}}" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-undo" aria-hidden="true"></i> Kembali
                                                    </a>
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
                                        @if(isset($templateHasAssets) && $templateHasAssets)
                                            <div class="col-lg-12">
                                                <div class="alert alert-info d-flex align-items-center justify-content-between" style="gap:12px">
                                                    <div>
                                                        <i class="fa fa-folder"></i> Template ini memiliki folder <b>assets</b>. Jalankan perintah
                                                        <code>php artisan cms:link-asset {{ $templateSlug ?? template() }}</code> agar assets ter-link ke folder public.
                                                    </div>
                                                    <div class="text-right">
                                                        <span class="badge {{ !empty($templateAssetsLinked) ? 'badge-success' : 'badge-danger' }}">
                                                            {{ !empty($templateAssetsLinked) ? 'ASSET LINKED' : 'BELUM LINKED' }}
                                                        </span>
                                                        <form method="post" action="{{ route('appearance.editor') }}" style="display:inline">
                                                            @csrf
                                                            <input type="hidden" name="type" value="link_asset">
                                                            <button type="submit" class="btn btn-sm {{ !empty($templateAssetsLinked) ? 'btn-secondary' : 'btn-info' }}" {{ !empty($templateAssetsLinked) ? 'disabled' : '' }}>
                                                                <i class="fa fa-link"></i> Link Asset
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-lg-2">
                                            <h6 class="clearfix"> <span class="pull-right text-danger"><i
                                                        class="fa fa-folder-plus pointer" onclick="folderPrompt('')" title="Create Folder"></i> &nbsp; <i
                                                        class="fa fa-file-circle-plus  pointer" onclick="filePrompt('')" title="Create File"></i> </span></h6>
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

                                                <textarea id="editor" name="file_src" class="custom_html">{{ $view }}</textarea>
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

                                                $(document).keydown(function(e) {
                                                    if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
                                                        e.preventDefault();
                                                        $('.editorForm').trigger('submit');
                                                    }
                                                });
                                            </script>
                                            <script>

                                                function folderPrompt(current = '') {
                                                    var userInput = prompt("Folder name :", "");
                                                    if (userInput != null) {

                                                        $.post('{{ route('appearance.editor') }}', { type: 'create_dir', dirname: userInput, current_path: current, _token: '{{ csrf_token() }}' }, function (response) {
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
                                                function deleteDir(dir) {
                                                    if (confirm('Sure delete this folder ? Cannot Undo Action')) {
                                                        $.post('{{ route('appearance.editor') }}', { type: 'delete_dir', dirname: dir, _token: '{{ csrf_token() }}' }, function (response) {
                                                            location.href='{{ url()->current() }}';
                                                        }).fail(function (xhr, status, error) {
                                                            var msg = 'Error: ' + error;
                                                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                                                msg = xhr.responseJSON.error;
                                                            }
                                                            notif(msg, 'danger');
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


@endsection
