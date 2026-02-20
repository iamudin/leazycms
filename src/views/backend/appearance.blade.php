@extends('cms::backend.layout.app', ['title' => 'Template'])
@section('content')

        <div class="row">
        <div class="col-lg-12 mb-3">
          <h3 style="font-weight:normal;float: left;"> <i class="fa fa-brush"></i> Template </h3>


                <div class="btn-group  pull-right">
                         @if(Cache::has('enablededitortemplate') || is_local())
              <a href="{{ route('appearance.editor') }}" class="btn btn-warning btn-sm btn-md "> <i class="fa fa-code"></i>
                Edit Template</a>

          @endif
                    <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
                </div>

            </div>






        <div class="col-lg-2" style="max-height: 85vh;overflow:auto">
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
                        @php
  $template_asset = config('modules.config.option.template') ?? null;
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
                                      <a href="{{get_option(_us($field[0])) }}" target="_blank"
                                          class="btn btn-sm btn-outline-primary mb-2">{{ basename(get_option(_us($field[0]))) }}</a> <i
                                          title="Hapus File" class="fa fa-trash text-danger pointer"
                                          onclick="media_destroy('{{ get_option(_us($field[0])) }}')"></i><br>
                                  @else
                                      <input @if (isset($field[3])) required @endif type="file" accept="{{ $field[2] ?? null }}"
                                          class="compress-image form-control-sm form-control-file mb-2" name="{{ _us($field[0]) }}">
                                  @endif
                          @elseif($field[1]=='color')
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
                   
               
              <h6>Info Template</h6>

              <ul class="list-group mb-3">
                {{template_info()}}
                    <div class="mb-5 text-right">
        <form  class="list-group-item py-0 px-1 m-0" action="{{ url()->full() }}" method="post" enctype="multipart/form-data">
          @csrf
          <input onchange="if(confirm('Yakin utk mengganti template ?')) this.form.submit()" type="file"
            accept="application/zip,x-zip-compressed" class="template" name="template" style="display: none">
            <button type="button" onclick="$('.template').click()" class="btn btn-warning btn-sm w-100 my-1"> <i class="fa fa-upload"></i>
              Upload Template</button>
            <a type="button" onclick="location.href='{{ asset('sample/sample.zip')}}'" class="btn btn-info btn-sm w-100 my-1"> <i
                class="fa fa-brush"></i> Sample Template</a>
               
        </form>

      </div>
          </ul>

        </div>

        <div class="col-lg-10">
  
        <iframe  src="{{ url('/') }}?reload={{ time() }}" frameborder="0" class="w-100 preview" style="height: 85vh;border-radius:5px;border:4px solid rgb(48, 48, 48)"></iframe>


        </div>

        </div>
        @include('cms::backend.layout.js')
    
@endsection
