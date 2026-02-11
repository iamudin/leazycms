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
                <button type="submit" data-toggle="tooltip" title="Simpan Perubahan"
                    class="btn btn-sm btn-primary  add"> <i class="fa fa-save"></i> SIMPAN</button>
              </div>
            </h3>
            <br>
            @include('cms::backend.layout.error')
          </div>
          <div class="col-lg-9">
            <div class="form-group">
                @if($looping_data)
                <input type="hidden" name="title" value="{{ $post->title ?? '' }}">
                <div class="alert alert-primary py-2" style="border-left:4px solid #000;font-size:20px">{{ $post->title ?? '' }}</div>
                @else
                <input data-toggle="tooltip" title="Masukkan {{ $module->datatable->data_title }}" required
                    name="title" type="text" value="{{ $post->title ?? '' }}"
                    placeholder="Masukkan {{ $module->datatable->data_title }}" class="form-control form-control-lg">
                @endif

            </div>
            @include('cms::backend.posts.list-menu')

          </div>
          <div class="col-lg-3">

             <div class="form-group form-inline">
                <div class="animated-radio-button">
                   <label>
                   <input {{($post && $post->status == 'publish') ? 'checked=checked' : ''}} required type="radio" name="status" value="publish"><small class="label-text">Publikasikan</small>
                   </label>
                </div>
                &nbsp;&nbsp;&nbsp;
                <div class="animated-radio-button">
                   <label>
                   <input {{($post && $post->status == 'draft') ? 'checked=checked' : ''}} required type="radio" name="status" value="draft"><small class="label-text">Draft</small>
                   </label>
                </div>
             </div>

          </div>
       </div>
    </form>
    @push('styles')
    <style>
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
        body{overflow-x:hidden;}

        .dd { position: relative; display: block; margin: 0; padding: 0; max-width: 100vw; list-style: none; font-size: 13px; line-height: 20px; }

        .dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
        .dd-list .dd-list { padding-left: 30px; }
        .dd-collapsed .dd-list { display: none; }

        .dd-item,
        .dd-empty,
        .dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px; }

        .dd-handle { display: block; height: 30px; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
        background: #fafafa;
        background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
        background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
        background:         linear-gradient(top, #fafafa 0%, #eee 100%);
        -webkit-border-radius: 3px;
              border-radius: 3px;
        box-sizing: border-box; -moz-box-sizing: border-box;
        }
        .dd-handle:hover { color: #2ea8e5; background: #fff; }

        .dd-item > button { display: block; position: relative; cursor: pointer; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
        .dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
        .dd-item > button[data-action="collapse"]:before { content: '-'; }

        .dd-placeholder,
        .dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; }
        .dd-empty { border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;
        background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                        -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
        background-image:    -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                           -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
        background-image:         linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                                linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
        background-size: 60px 60px;
        background-position: 0 0, 30px 30px;
        }

        .dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
        .dd-dragel > .dd-item .dd-handle { margin-top: 0; }
        .dd-dragel .dd-handle {
        -webkit-box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
              box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
        }



        .nestable-lists { display: block; clear: both; padding: 30px 0; width: 100%; border: 0; border-top: 2px solid #ddd; border-bottom: 2px solid #ddd; }

        #nestable-menu { padding: 0; margin: 20px 0; }

        #nestable-output,
        #nestable2-output { width: 100%; height: 7em; font-size: 0.75em; line-height: 1.333333em; font-family: Consolas, monospace; padding: 5px; box-sizing: border-box; -moz-box-sizing: border-box; }

        #nestable2 .dd-handle {
        color: #fff;
        border: 1px solid #999;
        background: #bbb;
        background: -webkit-linear-gradient(top, #bbb 0%, #999 100%);
        background:    -moz-linear-gradient(top, #bbb 0%, #999 100%);
        background:         linear-gradient(top, #bbb 0%, #999 100%);
        }
        #nestable2 .dd-handle:hover { background: #bbb; }
        #nestable2 .dd-item > button:before { color: #fff; }

        @media only screen and (min-width: 700px) {

        .dd { float: left; width: 100%; }
        .dd + .dd { margin-left: 2%; }

        }

        .dd-hover > .dd-handle { background: #2ea8e5 !important; }


        .dd3-content { display: block; height: 30px; margin: 5px 0; padding: 5px 10px 5px 40px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
        background: #fafafa;
        background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
        background:    -moz-linear-gradient(top, #fafafa 0%, #eee 100%);
        background:         linear-gradient(top, #fafafa 0%, #eee 100%);
        -webkit-border-radius: 3px;
              border-radius: 3px;
        box-sizing: border-box; -moz-box-sizing: border-box;
        }
        .dd3-content:hover { color: #2ea8e5; background: #fff; }

        .dd-dragel > .dd3-item > .dd3-content { margin: 0; }

        .dd3-item > button { margin-left: 30px; }

        .dd3-handle { position: absolute; margin: 0; left: 0; top: 0; cursor: pointer; width: 30px; text-indent: 100%; white-space: nowrap; overflow: hidden;
        border: 1px solid #aaa;
        background: #ddd;
        background: -webkit-linear-gradient(top, #ddd 0%, #bbb 100%);
        background:    -moz-linear-gradient(top, #ddd 0%, #bbb 100%);
        background:         linear-gradient(top, #ddd 0%, #bbb 100%);
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        }
        .dd3-handle:before { content: '≡'; display: block; position: absolute; left: 0; top: 3px; width: 100%; text-align: center; text-indent: 0; color: #fff; font-size: 20px; font-weight: normal; }
        .dd3-handle:hover { background: #ddd; }



        .socialite { display: block; float: left; height: 35px; }

        </style>
    @endpush

    @push('scripts')
    <script>

    const inputs = document.getElementById('link_target');
    const listBox = document.getElementById('autocomplete-list');

    inputs.addEventListener('input', function() {
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
    div.onclick = function() {
        inputs.value = item.url;
        listBox.style.display = 'none';
    };
    listBox.appendChild(div);
                });
                listBox.style.display = 'block';
            });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.autocomplete-box')) {
            listBox.style.display = 'none';
        }
    });
    </script>
       <script src="{{asset('backend/js/jquery.nestable.js')}}"></script>
        <script>

        $(document).ready(function()
        {

          var updateOutput = function(e)
          {
              var list   = e.length ? e : $(e.target),
                  output = list.data('output');
              if (window.JSON) {
                  output.val(window.JSON.stringify(list.nestable('serialize')));
              } else {
                  output.val('JSON browser support required for this demo.');
              }
          };


        $('#nestable3').nestable({
         group: 1}
         )
        .on('change', updateOutput);
        updateOutput($('#nestable3').data('output', $('#nestable3-output')));
        });
        function del_menu(id){
          if(confirm('Hapus Menu ini?'))
           {
             $('.menu-id-'+id).remove();
             $('#nestable3').nestable({
              group: 1}
            ).change();
         }
        }
        function setmenu(){
          var name = $('.name').val();
          var desc = $('.description').val();
          var link = $('.link').val();
          var icon = $('.iconx').val();

          $('#name').val(name);
          $('#description').val(desc);
          $('#link').val(link);
          $('#iconx').val(icon);
          $('#labelname').html(name);
          var type = $('#type').val();
          if(type != 'add'){
            $('.name-'+type).val(name);
            $('.desc-'+type).val(desc);
            $('.link-'+type).val(link);
            $('.icon-'+type).val(icon);
          }else {
            $('.main-list').append($('.newmenu').html());
            $('#nestable3').nestable({
             group: 1}
           ).change();

          }

         $('.add').click();
        }
        </script>
    @include('cms::backend.layout.js')

    @endpush
@endsection
