@extends('cms::backend.layout.app', ['title' => 'Edit Template'])
@section('content')
                                                <div class="row">
                                                <div class="col-lg-12 mb-3"><h3 style="font-weight:normal;float: left;"> <i class="fa fa-paint-brush"></i> Edit Template </h3>
                                                    <div class="pull-right">

                                                        <div class="btn-group">
                                                      <button type="button" onclick="$('.editorForm').trigger('submit')" class="btn btn-primary btn-sm"> <i class="fa fa-save"></i> <span class="save-text">Simpan</span></button>
                                                        <a href="{{route('appearance')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
                                                    </div></div>

                                                </div>
                                                @if(get_option('site_maintenance') == 'N')
                                                    <div class="col-lg-12">
                                                        <div class="alert alert-warning">
                                                           <i class="fa fa-warning"></i> Status Maintenance tidak aktif. Aktifkan pada menu <b>Pengaturan</b> <i class="fa fa-arrow-right"></i> <b>Situs Web</b>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-lg-3">
                                                <h6 > <i class="fa fa-folder"></i> /{{ template() }}/ <span class="pull-right text-danger"><i class="fa fa-folder-plus pointer" onclick="folderPrompt()" title="Create Folder"></i> &nbsp;  <i class="fa fa-file-circle-plus  pointer" onclick="filePrompt()" title="Create File"></i> </span></h6>
                                                <div style="max-height: 74vh;overflow:auto;padding-right:10px">

                                                @php
    $treeData = [];
    $data = getDirectoryContents(null, $treeData);
    renderTemplateFile($treeData);
                                                    @endphp
                                                <ul style="padding:0;list-style: none;margin:0">
                                                    <li> <i class="fa fa-file-code"></i> <a href="{{ url()->current() . '?edit=' . enc64("/styles.css") }}">  styles.css</a></li>
                                                    <li> <i class="fa fa-file-code"></i> <a href="{{ url()->current() . '?edit=' . enc64("/scripts.js") }}"> scripts.js</a></li>
                                                </ul>
                                                </div>
                                                </div>


                                                <div class="col-lg-9">

                                                    <form action="{{ url()->full() }}" class="editorForm" method="post" enctype="multipart/form-data">
                                                        @csrf
                                                        @if($e = dec64(request()->edit))
                                                    <h6 > <i class="fa fa-edit"></i> {{  'Edit : ' . $e  }} @if(!str($e)->contains(['modules.blade.php', 'home.', 'header', 'footer', 'styles', 'scripts']))<i onclick="deleteFile('{{ $e }}')" class="fa fa-trash-o text-danger pointer" title="Delete this file "></i>@endif
                                                        @if(str(request()->edit)->contains('modules'))
                                                        <span class="pointer badge badge-primary pull-right"><i class="fa fa-question-circle"></i> Petunjuk Custom Modul</span>
                                                        @endif
                                                    </h6>

                                                    @else
                                                    <h6> <i class="fa fa-edit"></i> {{  'Edit : /home.blade.php'  }}</h6>

                                                    @endif
                                                    <input type="hidden" name="type" value="change_file">

                                                    <textarea id="editor" name="file_src" class="custom_html" >
                                                    {{ $view }}
                                                    </textarea>
                                                </form>
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
                                                                                                                    notif('Gagal menyimpan perubahan!', 'danger');
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
                                                                        location.reload();

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
                                                </div>

                                                </div>
@endsection
