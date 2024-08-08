@extends('cms::backend.layout.app',['title'=>'Edit Template'])
@section('content')
<div class="row">
<div class="col-lg-12 mb-3"><h3 style="font-weight:normal;float: left;"> <i class="fa fa-paint-brush"></i> Edit Template </h3>
    <div class="pull-right">


        @if(get_option('site_maintenance') == 'Y') <button type="button" onclick="if(confirm('Anda Yakin ?')) $('.editorForm').submit()" class="btn btn-outline-primary btn-sm"> <i class="fa fa-save"></i> Simpan Perubahan</button> @endif
        <a href="{{route('appearance')}}" class="btn btn-outline-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
    </div>

</div>
@if(get_option('site_maintenance')=='N')
    <div class="col-lg-12">
        <div class="alert alert-warning">
           <i class="fa fa-warning"></i> Untuk mengakses fitur ini, silahkan kan Aktifkan <b>Status Maintenance </b> pada menu <b>Pengaturan</b> <i class="fa fa-arrow-right"></i> <b>Situs Web</b>
        </div>
    </div>
@else
<div class="col-lg-3">
<h6 > <i class="fa fa-folder"></i> /{{ template() }}/ <span class="pull-right text-danger"><i class="fa fa-folder-plus pointer" onclick="folderPrompt()" title="Create Folder"></i> &nbsp;  <i class="fa fa-file-circle-plus  pointer" onclick="filePrompt()" title="Create File"></i> </span></h6>
<div style="max-height: 74vh;overflow:auto;padding-right:10px">

@php
    $treeData = [];
    $data = getDirectoryContents(null, $treeData);
    renderTemplateFile($treeData);
    @endphp
<ul style="padding:0;list-style: none;margin:0">
    <li> <i class="fa fa-file-code"></i> <a href="{{ url()->current().'?edit=/styles.css' }}">  styles.css</a></li>
    <li> <i class="fa fa-file-code"></i> <a href="{{ url()->current().'?edit=/scripts.js' }}"> scripts.js</a></li>
</ul>
</div>
</div>


<div class="col-lg-9">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/htmlmixed/htmlmixed.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/css/css.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.1/mode/php/php.min.js"></script>

    <form action="{{ url()->full() }}" class="editorForm" method="post">
        @csrf
        @if($e = request()->edit )
    <h6> <i class="fa fa-edit"></i> {{  'Edit : '.$e  }} @if(!str($e)->contains(['modules','home','header','footer','styles','scripts']))<i onclick="deleteFile('{{ $e }}')" class="fa fa-trash-o text-danger pointer" title="Delete this file "></i>@endif</h6>
    @else
    <h6> <i class="fa fa-edit"></i> {{  'Edit : /home.blade.php'  }}</h6>

    @endif
    <input type="hidden" name="type" value="change_file">

    <textarea id="editor" name="file_src" >
    {{ $view }}
    </textarea>
</form>

    <script>
  function folderPrompt() {
        var userInput = prompt("Folder name :", "");
        if (userInput != null) {

            $.post('{{ route('appearance.editor') }}', {type:'create_dir',dirname:userInput,_token:'{{ csrf_token() }}'}, function(response){
                    location.reload();
                }).fail(function(xhr, status, error) {
                    console.error('Error:', error);
                });
        }
    }
    function deleteFile(file) {
            if(confirm('Sure delete this file ? Cannot Undo Action')){

            $.post('{{ route('appearance.editor') }}', {type:'delete_file',filename:file,_token:'{{ csrf_token() }}'}, function(response){
                location.reload();

                }).fail(function(xhr, status, error) {
                    console.error('Error:', error);
                });
            }
        }
    function filePrompt(current) {
        var userInput = prompt("File name (without any ekstension) :", "");
        if (userInput != null) {
            $.post('{{ route('appearance.editor') }}', {type:'create_file',filepath:current,filename:userInput,_token:'{{ csrf_token() }}'}, function(response){
                    location.reload();
                }).fail(function(xhr, status, error) {
                    console.error('Error:', error);
                });
        }
    }
        var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
            lineNumbers: true,
            mode: "{{ $type }}",
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            theme: "default"
        });
        $('.CodeMirror').attr('style','height:74vh');
    </script>


</div>
@endif
</div>

</div>
@include('cms::backend.layout.js')
@endsection
