@extends('cms::backend.layout.app', ['title' => 'Setting › Template'])
@section('content')

    <div class="row">
    <div class="col-lg-12 mb-3">
      <h3 style="font-weight:normal;float: left;"> <i class="fa fa-brush"></i>  Setting › Template </h3>


            <div class="btn-group  pull-right">
              @if(get_option('can_edit_template')=='Y' || is_main_domain())
          <a href="{{ route('appearance.editor') }}" class="btn btn-warning btn-sm btn-md "> <i class="fa fa-code"></i>
            Edit Template</a>
            @endif
                <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
            </div>

        </div>






    <div class="col-lg-3" style="max-height: 85vh;overflow:auto">
        <h6>Modul</h6>
        <div class="accordion mb-3" id="accordionExample" >
          @php 
            $module = collect(get_module())->where('public', true)->where('web.detail', true);
            if (config('modules.multisite_enabled')) {
                $disallowedModules = app()->bound('tenant') ? app('tenant')->modules ?? [] : [];
                if (is_string($disallowedModules)) {
                    $disallowedModules = json_decode($disallowedModules, true) ?? [];
                }
                if (is_array($disallowedModules) && count($disallowedModules) > 0) {
                    $module = $module->whereNotIn('name', $disallowedModules);
                }
            }
            $data = query()->selectedColumn()->whereIn('type', $module->pluck('name')->toArray())->with('category')->get();
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
                    @php
                        $fieldName = $field[0] ?? '';
                        $fieldType = $field[1] ?? 'text';
                        $fieldExtra = $field[2] ?? null;
                        $fieldSlug = _us($fieldName);
                        $savedVal = get_option($fieldSlug);
                        $fallbackDefault = (isset($fieldExtra) && is_string($fieldExtra) && $fieldType !== 'file' && $fieldType !== 'break' && !is_array($fieldType)) ? $fieldExtra : null;
                        $displayVal = ($savedVal !== null && $savedVal !== '') ? $savedVal : $fallbackDefault;
                    @endphp

                    @if(is_array($fieldType))
                      <small class="font-weight-bold text-dark">{{ str($fieldName)->headline() }}</small><br>
                      <select name="{{ $fieldSlug }}" class="form-control form-control-sm"  @if (isset($fieldExtra)) required @endif >
                          <option value="">Tanpa Preload</option>
                          @foreach($fieldType as $row)
                          <option value="{{ $row }}" {{ $displayVal == $row ? 'selected' : '' }}>{{ $row }}</option>
                          @endforeach
                      </select>
                      @if($fieldSlug === 'preload_effect')
                        <button type="button" class="btn btn-sm btn-info mt-2 mb-2" onclick="previewPreload()"><i class="fa fa-play"></i> Preview Preload</button>
                        <script>
                            function previewPreload() {
                                var effect = document.querySelector('select[name="preload_effect"]').value;
                                var color = document.querySelector('input[name="preload_color"]') ? document.querySelector('input[name="preload_color"]').value : '#2563eb';
                                var iframe = document.querySelector('.preview');
                                if (iframe) {
                                    var baseUrl = iframe.src.split('?')[0];
                                    iframe.src = baseUrl + '?preview_preload=' + encodeURIComponent(effect) + '&preview_color=' + encodeURIComponent(color);
                                }
                            }
                        </script>
                      @endif
                    @elseif ($fieldType === 'file')
                          <small class="font-weight-bold text-dark">{{ str($fieldName)->headline() }}</small><br>
                              @if (media_exists($savedVal))
                                  <div class="media-preview-wrapper">
                                  <a href="{{ $savedVal }}" target="_blank"
                                      class="btn btn-sm btn-outline-primary mb-2">{{ basename($savedVal) }}</a> <i
                                      title="Hapus File" class="fa fa-trash text-danger pointer btn-remove-media"
                                      data-field="{{ $fieldSlug }}"></i><br>
                                  </div>
                              @endif
                                  <div class="media-input-wrapper" style="{{ (media_exists($savedVal)) ? 'display:none;' : '' }}">
                                  <input @if (isset($field[3])) required @endif type="file" accept="{{ $fieldExtra ?? 'image/*' }}"
                                      class="compress-image form-control-sm form-control-file mb-2" name="{{ $fieldSlug }}">
                                  </div>
                    @elseif($fieldType === 'break')
                        <div class="sub-section-header mt-4 mb-3 pt-3 border-top w-100">
                            <div class="d-flex align-items-center">
                                <div class="badge badge-primary px-3 py-1.5 mr-2 font-weight-bold text-uppercase" style="font-size: 12px; letter-spacing: 0.5px; border-radius: 6px;">
                                    <i class="fa fa-layer-group mr-1.5"></i> {{ str($fieldName)->headline() }}
                                </div>
                                <div class="flex-grow-1" style="height: 2px; background: linear-gradient(to right, rgba(0,123,255,0.4), rgba(0,123,255,0.05));"></div>
                            </div>
                            @if(!empty($fieldExtra))
                                <small class="text-muted d-block mt-1 pl-1">{{ $fieldExtra }}</small>
                            @endif
                        </div>
                    @elseif($fieldType === 'color')
                        <small class="font-weight-bold text-dark">{{ str($fieldName)->headline() }}</small><br>
                        <input type="color" name="{{ $fieldSlug }}" class="form-control form-control-sm" style="width: 80px; height: 35px; padding: 2px;" value="{{ $displayVal ?: '#000000' }}">
                    @elseif($fieldType === 'textarea')
                          <small class="font-weight-bold text-dark">{{ str($fieldName)->headline() }}</small><br>
                          <textarea @if (isset($field[3])) required @endif class="form-control form-control-sm" name="{{ $fieldSlug }}" rows="3" placeholder="Masukkan {{ $fieldName }}">{{ $displayVal }}</textarea>
                    @else
                          <small class="font-weight-bold text-dark">{{ str($fieldName)->headline() }}</small><br>
                          <input @if (isset($field[3])) required @endif type="{{ $fieldType }}"
                              class="form-control form-control-sm mb-2" name="{{ $fieldSlug }}"
                              placeholder="Masukkan {{ $fieldName }}" value="{{ $displayVal }}">
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
                <div class="">
    
    
  
  </div>
      </ul>
      @endif
      @if(is_main_domain() || get_option('can_upload_template', 'N') === 'Y')
      <form action="{{ URL::full() }}" method="post" enctype="multipart/form-data" id="formUploadTemplate" class="mb-2">
        @csrf
        <label class="font-weight-bold mb-1">Upload Template :</label>
        <input type="file" accept="application/zip,x-zip-compressed,.zip" class="template mb-2" name="template" id="inputTemplateFile">
        <button type="submit" id="btnUploadTemplate" class="btn btn-sm btn-warning w-100 mt-2" style="display: none;">
          <i class="fa fa-upload"></i> Upload
        </button>
      </form>
      @endif

      <script>
        document.addEventListener('DOMContentLoaded', function () {
          const form = document.getElementById('formUploadTemplate');
          const fileInput = document.getElementById('inputTemplateFile');
          const btnSubmit = document.getElementById('btnUploadTemplate');
          
          if (!form || !btnSubmit) return;

          function checkTemplateSelected() {
            const hasHiddenInput = form.querySelector('input.gmedia-hidden[value]') !== null;
            const hasFileSelected = fileInput && fileInput.files && fileInput.files.length > 0;

            if (hasHiddenInput || hasFileSelected) {
              btnSubmit.style.display = 'block';
            } else {
              btnSubmit.style.display = 'none';
            }
          }

          if (fileInput) {
            fileInput.addEventListener('change', checkTemplateSelected);
          }

          const observer = new MutationObserver(function () {
            checkTemplateSelected();
          });
          observer.observe(form, { childList: true, subtree: true });

          checkTemplateSelected();
        });
      </script>
  <a href="{{ route('appearance.template_store') }}" class="btn btn-info btn-sm w-100">
        <i class="fa fa-cloud-download-alt"></i> Pilih dari Cloud
    </a>
    </div>

    <div class="col-lg-9">

    <iframe  src="{{ url('/') }}?reload={{ time() }}" frameborder="0" class="w-100 preview" style="height: 85vh;border-radius:5px;border:4px solid rgb(48, 48, 48)"></iframe>


    </div>

    </div>


    <!-- Hidden form for installing cloud template -->
    <form id="install-cloud-form" action="{{ route('appearance.install_cloud') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="url" id="install-cloud-url">
    </form>

  @push('scripts')

    @include('cms::backend.layout.js')
  @endpush
@endsection
