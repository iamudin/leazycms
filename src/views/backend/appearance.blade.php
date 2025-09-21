@extends('cms::backend.layout.app', ['title' => 'Tampilan'])
@section('content')

      <div class="row">
      <div class="col-lg-12 mb-3">
        <h3 style="font-weight:normal;float: left;"> <i class="fa fa-paint-brush"></i> Tampilan </h3>



              <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm pull-right"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
          </div>






      <div class="col-lg-2">
          <h6>Modul</h6>
          <div class="accordion mb-3" id="accordionExample" >
              @foreach(collect(get_module())->where('public', true)->where('web.detail', true) as $row)
              <div class="card">
                <div class="card-header" id="heading{{ $row->name }}" style="padding:0">
                    <span class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#{{ $row->name }}" aria-expanded="true" aria-controls="{{ $row->name }}">
                     <i class="fa {{ $row->icon }}"></i> {{ $row->title }}
                    </span>
                </div>

                <div id="{{ $row->name }}" class="collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading{{ $row->name }}" data-parent="#accordionExample">
                  <div class="card-body py-2 pl-3" >
                      <ul style="margin:0;padding:0;list-style:none">
                    @if($row->web->index)
                    <li>
                      <a href="javascript::void(0)" onclick="$('.preview').attr('src','{{ url($row->name) }}')"> <i class="fa fa-arrow-right"></i> View INDEX</a>
                    </li>
                    @endif
                    @if($row->web->detail)
                    <li>
                      @php $detail = query()->detail($row->name) @endphp
                      <a href="javascript::void(0)" onclick="$('.preview').attr('src','{{ url($detail->url ?? '/') }}')"> <i class="fa fa-arrow-right"></i> View DETAIL</a>
                  </li>
                    @endif
                    @if($row->form->category)
                    @php $category = query()->index_category($row->name)->first() @endphp
                    <li>
                      <a href="javascript::void(0)"  onclick="$('.preview').attr('src','{{ url($category->url ?? '/') }}')"> <i class="fa fa-arrow-right"></i>  View CATEGORY</a>

                    </li>
                    @endif
                    @if($row->web->archive)
                    @php $archive = query()->detail($row->name) @endphp
                    <li>
                      <a href="javascript::void(0)"  onclick="$('.preview').attr('src','{{ url($row->name . '/archive/' . ($archive ? $archive->created_at->format('Y') : date('Y'))) }}')"> <i class="fa fa-arrow-right"></i>    Archive</a>

                    </li>
                    @endif
                  </ul>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            <h6>Info Template</h6>

            <ul class="list-group mb-3">
              {{template_info()}}

        </ul>

      </div>

      <div class="col-lg-10">
          @php
  $template_asset = config('modules.config.template_asset') ?? null;
          @endphp
          @if($template_asset && is_array($template_asset))
              <h6> <i class="fa fa-image"></i> Template Assets <small class="text-muted">(optional)</small> </h6>
              <form action="{{ URL::current() }}" method="post" enctype="multipart/form-data">
                  @csrf
              <div class="row">

              @foreach($template_asset as $row)
              @php
      $keyasset = _us($row[0]);
              @endphp
              <div class="col-lg-6">
              <div class="form-group">
                  <small for="">{{ $row[0] }}</small><br>
                  @if($row[1] == 'file')
                  @if($asset = get_option($keyasset) && media_exists(get_option($keyasset)))
                  <a href="{{ get_option($keyasset)}}" target="_blank" class="btn btn-outline-primary">{{ basename(get_option($keyasset)) }}</a>
                  <i class="fa fa-trash text-danger pointer" onclick="media_destroy('{{ get_option($keyasset)}}')"></i>
                  @else
                  <input accept="{{ $row[2] }}" type="file" name="template_asset[{{$keyasset}}]" onchange="this.form.submit()">
                  @endif
                  @else

                  @endif
              </div>
              </div>
              @endforeach

          </div>
      </form>
          @endif
      <iframe  src="{{ url('/') }}" frameborder="0" class="w-100 preview" style="height: 80vh;border-radius:5px;border:4px solid rgb(48, 48, 48)"></iframe>
      <div class="mb-5 text-right">
      <form action="{{ url()->full() }}" method="post" enctype="multipart/form-data">
        @csrf
        <input onchange="if(confirm('Yakin utk mengganti template ?')) this.form.submit()" type="file"
          accept="application/zip,x-zip-compressed" class="template" name="template" style="display: none">
        <div class="btn-group">
          <button type="button" onclick="$('.template').click()" class="btn btn-warning btn-sm"> <i class="fa fa-upload"></i>
            Upload Template</button>
          <a type="button" onclick="location.href='{{ asset('sample/sample.zip')}}'" class="btn btn-info btn-sm"> <i
              class="fa fa-brush"></i> Sample Template</a>
      </form>
        @if(Cache::has('enablededitortemplate') || is_local())
            <a href="{{ route('appearance.editor') }}" class="btn btn-outline-primary btn-sm btn-md"> <i class="fa fa-code"></i>
              Edit Template</a>

        @endif
      </div></div>

      </div>

      </div>
      @include('cms::backend.layout.js')
@endsection
