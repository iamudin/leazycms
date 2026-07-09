@extends('cms::backend.layout.app', ['title' => 'Setting › Template'])
@section('content')

    <div class="row">
    <div class="col-lg-12 mb-3">
      <h3 style="font-weight:normal;float: left;"> <i class="fa fa-brush"></i> Setting › Template </h3>


            <div class="btn-group  pull-right">
              @if(get_option('can_edit_template')=='Y' || is_main_domain())
          <a href="{{ route('appearance.editor') }}" class="btn btn-warning btn-sm btn-md "> <i class="fa fa-code"></i>
            Edit Template</a>
            @endif
                <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
            </div>

        </div>






    <div class="col-lg-2" style="max-height: 85vh;overflow:auto">
        <h6>Modul</h6>
        <div class="accordion mb-3" id="accordionExample" >
          @php 
          $module = collect(get_module())->where('public', true)->where('web.detail', true);
          $data = query()->selectedColumn()->whereIn('type',$module->pluck('name')->toArray())->with('category')->get();
          @endphp
            @foreach($module as $row)
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
                  @php
                  $detail= $data->where('type',$row->name)->first();
                  @endphp
                    <a href="javascript::void(0)" onclick="$('.preview').attr('src','{{ url($detail->url ?? '/') }}')"> <i class="fa fa-arrow-right"></i> View DETAIL</a>
                </li>
                  @endif
                  @if($row->form->category)
                  @php 
                  $category = Leazycms\Web\Models\Category::whereType($row->name)->whereHas('posts')->first();
                  
                  @endphp
                  <li>
                    <a href="javascript::void(0)"  onclick="$('.preview').attr('src','{{ url($category->url ?? '/') }}')"> <i class="fa fa-arrow-right"></i>  View CATEGORY</a>

                  </li>
                  @endif
               
                </ul>
                </div>
              </div>
            </div>
            @endforeach
          </div>
                    @php $template_asset = config('modules.config.option.template') ?? null;
        @endphp


                  <h6> <i class="fa fa-gear"></i> Template Setting</h6>
                  <form action="{{ URL::current() }}" class="template-setting mb-4" method="post" enctype="multipart/form-data">
                      @csrf
                      <input type="hidden" name="template_setting" value="true">
                      <div class="row">
                  <div class="col-lg-12">
                          <small>Konten Halaman Utama</small>
                            <select class="form-control form-control-sm" name="home_page">
                                <option value="default">Default</option>
                                @foreach ($home as $r)
                                    <option value="{{ $r }}"
                                        {{ $r == get_option('home_page') ? 'selected' : '' }}>{{ str(str_replace('.blade.php', '', $r))->upper() }}</option>
                                @endforeach
                            </select>
                  </div>
       
                         @if($template_asset && is_array($template_asset))
                 @foreach ($template_asset as $field)
                  <div class="col-lg-12">
                    @if(is_array($field[1]))
                      <small>{{ str($field[0])->headline() }}</small><br>
                      <select name="{{_us($field[0])}}" class="form-control form-control-sm"  @if (isset($field[2])) required @endif >
                          <option value="">--pilih--</option>
                          @foreach($field[1] as $row)
                          <option value="{{ $row }}" {{ get_option(_us($field[0])) && get_option(_us($field[0])) == $row ? 'selected' : null }}>{{$row }}</option>
                          @endforeach
                      </select>
                    @elseif ($field[1] == 'file')
                          <small>{{ str($field[0])->headline() }}</small><br>

                              @if (media_exists(get_option(_us($field[0]))))
                                  <div class="media-preview-wrapper">
                                  <a href="{{get_option(_us($field[0])) }}" target="_blank"
                                      class="btn btn-sm btn-outline-primary mb-2">{{ basename(get_option(_us($field[0]))) }}</a> <i
                                      title="Hapus File" class="fa fa-trash text-danger pointer btn-remove-media"
                                      data-field="{{ _us($field[0]) }}"></i><br>
                                  </div>
                              @endif
                                  <div class="media-input-wrapper" style="{{ (media_exists(get_option(_us($field[0])))) ? 'display:none;' : '' }}">
                                  <input @if (isset($field[3])) required @endif type="file" accept="{{ $field[2] ?? null }}"
                                      class="compress-image form-control-sm form-control-file mb-2" name="{{ _us($field[0]) }}">
                                  </div>
                      @elseif($field[1 ]=='color')
                 <small>{{ str($field[0])->headline() }}</small><br>
                  <input type="color" name="{{_us($field[0])}}" class="form-control form-control-sm" value="{{ get_option(_us($field[0])) }}">
                      @elseif($field[1] == 'textarea')

                          <small>{{ str($field[0])->headline() }}</small><br>

                          <textarea @if (isset($field[2])) required @endif class="form-control form-control-sm" name="{{_us($field[0])}}">
                              {{ get_option(_us($field[0])) }}
                          </textarea>


                      @else
                          <small>{{ str($field[0])->headline() }}</small><br>

                              <input @if (isset($field[2])) required @endif type="{{ $field[1]  }}"
                                  class="form-control form-control-sm mb-2" name="{{ _us($field[0]) }}"
                                  placeholder="Masukkan {{ $field[0] }}" value="{{ get_option(_us($field[0])) }}">
                      @endif
                        </div>
                @endforeach
        @endif

                              </div>
                              <button type="submit" class="submit_form btn w-100 mt-2 btn-sm btn-outline-primary"> <i class="fa fa-save" ></i> Simpan</button>
          </form>

@if(is_main_domain())
          <h6>Info Template</h6>

          <ul class="list-group mb-3">
            {{template_info()}}
                <div class="mb-5">
    <form  class="list-group-item" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
      @csrf
      <label>Upload Template :</label>
      <input onchange="if(confirm('Yakin utk mengganti template ?')) this.form.submit()" type="file"
        accept="application/zip,x-zip-compressed" class="template" name="template" >
       
 

    </form>
    
    <a href="{{ route('appearance.template_store') }}" class="btn btn-info btn-sm w-100 my-1">
        <i class="fa fa-cloud-download-alt"></i> Pilih dari Cloud
    </a>
  </div>
      </ul>
      @endif

    </div>

    <div class="col-lg-10">

    <iframe  src="{{ url('/') }}?reload={{ time() }}" frameborder="0" class="w-100 preview" style="height: 85vh;border-radius:5px;border:4px solid rgb(48, 48, 48)"></iframe>


    </div>

    </div>


    <!-- Hidden form for installing cloud template -->
    <form id="install-cloud-form" action="{{ route('appearance.install_cloud') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="url" id="install-cloud-url">
    </form>

  @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        /* Scripts removed, handled by template_store view */
    });
    </script>
    @include('cms::backend.layout.js')
  @endpush
@endsection
