@extends('cms::backend.layout.app', ['title' => 'Pengaturan'])
@section('content')
        <form class="" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <h3 style="font-weight:normal;margin-bottom:20px"> <i class="fa fa-gears"></i> Pengaturan <div class="btn-group pull-right">
                         @if(!app()->configurationIsCached())
                         <button
                            name="save_setting" value="true" class="btn btn-primary btn-sm"> <i
                                class="fa fa-save" aria-hidden></i> Simpan</button>
                            @endif
                                <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
                            </div></h3>
                    @include('cms::backend.layout.error')
                    @if(!app()->configurationIsCached())
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#home"> <i
                                        class="fa fa-home"></i> {{ $web_type ?? 'Organisasi' }}</a>
                            </li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#profile"> <i class="fa fa-globe"></i>
                                    Situs Web</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#keamanan"> <i class="fa fa-gears"></i>
                                    Lainnya</a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pwa"> <i class="fa fa-mobile-alt"></i>
                                        PWA</a></li>
                        </ul>
                        <div class="tab-content pt-2" id="myTabContent">
                            <div class="tab-pane fade active show" id="home">

                                @foreach ($option ?? [] as $r)
                                    <small for="" class="text-muted">{{ $r[0] }}</small>
                                    @if ($r[1] == 'file')
                                        @if (get_option(_us($r[0])) && media_exists(get_option(_us($r[0]))))
                                            <br><a href="{{ url(get_option(_us($r[0]))) }}"
                                                class="btn btn-outline-info btn-sm">Lihat {{ $r[0] }}
                                                (.{{ str(get_ext(get_option(_us($r[0]))))->upper() }})</a> <a
                                                href="javascript:void(0)" onclick="media_destroy('{{ get_option(_us($r[0])) }}')"
                                                class="text-danger btn-sm"> <i class="fa fa-trash"></i> </a>
                                            <br>
                                        @else
                                            <input accept="{{ allow_mime() }}" {{ isset($r[2]) ? 'required' : '' }}
                                                name="{{ _us($r[0]) }}" type="file" class="compress-image form-control-sm form-control-file">
                                        @endif
                                    @else
                                        <input {{ isset($r[2]) ? 'required' : '' }} type="text"
                                            class="form-control form-control-sm" placeholder="Masukkan {{ $r[0] }}"
                                            name="{{ _us($r[0]) }}" value="{{ get_option(_us($r[0])) }}">
                                    @endif
                                @endforeach

                            </div>
                            <div class="tab-pane fade" id="profile">

                                <small>Konten Halaman Utama</small>
                                <select class="form-control form-control-sm" name="home_page">
                                    <option value="default">Default</option>
                                    @foreach ($home as $r)
                                        <option value="{{ $r }}"
                                            {{ $r == get_option('home_page') ? 'selected' : '' }}>{{ str(str_replace('.blade.php', '', $r))->upper() }}</option>
                                    @endforeach
                                </select>
                                @foreach ($site_attribute as $r)
                                    @if ($r[2] == 'file')
                                        @if($r[1] == 'favicon')
                                        <small for="" class="text-muted">Favicon (didukung hanya file  gambar format .ico ukuran 64px x 64px)</small>

                                        <br><img height="60" src="/favicon.ico"
                                        onerror="{{ noimage() }}">
                                    <br>
                                        <input accept="image/x-icon,image/vnd.microsoft.icon" type="file"
                                        class="form-control-sm form-control-file" name="{{ $r[1] }}">
                                        @else
                                        <small for="" class="text-muted">{{ $r[0] }}</small>

                                        @if (get_option($r[1]) && media_exists(get_option($r[1])))
                                            <br><img height="60" src="{{ url(get_option($r[1])) }}"
                                                onerror="{{ url('backend/images/noimage.png') }}"> &nbsp;<a
                                                href="javascript:void(0)" onclick="media_destroy('{{ get_option($r[1]) }}')"
                                                class=" btn-sm text-danger"> <i class="fa fa-trash"></i> </a>
                                            <br>
                                        @else
                                            <input accept="image/png,imgage/jpeg" type="file"
                                                class="form-control-sm form-control-file compress-image" name="{{ $r[1] }}">
                                        @endif
                                        @endif
                                    @else

                                        <small for="" class="text-muted">{{ $r[0] }}</small>
                                        <input type="text"
                                            @if ($r[2] == 'number') oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" @endif
                                            class="form-control form-control-sm" placeholder="Masukkan {{ $r[0] }}"
                                            name="{{ $r[1] }}" value="{{ $r[1] == 'site_url' && empty(get_option($r[1])) ? request()->getHttpHost() : get_option($r[1]) }}">
                                    @endif
                                @endforeach

                            </div>
                            <div class="tab-pane fade" id="keamanan">
                                <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-lock"></i> Keamanan</h6>
                                @foreach ($security as $r)
                                    <small for="" class="text-muted">{{ $r[0] }}</small><br>
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="Enter {{ $r[1] }}" name="{{ _us($r[0]) }}"
                                        value="{{ get_option(_us($r[0])) }}">
                                @endforeach
                                <br>
                                <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-keyboard-o"></i> Web Control</h6>
                                <div class="list-group mb-4">
                                @foreach ($shortcut as $r)
                                    <div class="list-group-item py-2"><strong for="" class="text-muted">{{ $r[0] }}</strong> <div class="pull-right"><input name="{{ $r[1] }}" data-width="100" {{ get_option($r[1]) == 'Y' ? 'checked' : '' }} title="Ubah status data publik atau draft"
                                        type="checkbox" class="toggle-status" data-on="Active" data-off="Inactive" data-toggle="toggle"
                                        data-onstyle="outline-success" data-offstyle="outline-danger" data-size="sm"></div></div>
                                @endforeach
                                <div class="list-group-item py-2">
                            <strong for="" class="text-muted">Maintenance Status</strong>
                            <div class="pull-right"><input name="site_maintenance" data-width="100" {{ get_option('site_maintenance') == 'Y' ? 'checked' : '' }}
                                    title="Ubah status data publik atau draft" type="checkbox" class="toggle-status" data-on="Active"
                                    data-off="Inactive" data-toggle="toggle" data-onstyle="outline-success" data-offstyle="outline-danger"
                                    data-size="sm"></div>
                                    </div>
                                      <div class="list-group-item py-2">
                            <strong for="" class="text-muted">App Environment</strong>
                            <div class="pull-right"><input name="app_env" data-width="100" {{ get_option('app_env') == 'production' ? 'checked' : '' }}
                                    title="Ubah status data publik atau draft" type="checkbox" class="toggle-status" data-on="Production"
                                    data-off="Local" data-toggle="toggle" data-onstyle="outline-success" data-offstyle="outline-danger"
                                    data-size="sm"></div>
                                    </div>
                            </div>
                            @if(!app()->routesAreCached())
                            <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-key"></i> Login Path</h6>
                            <input type="text" class="form-control form-control-sm" name="admin_path" value="{{get_option('admin_path')}}">
                            <small class="text-danger"> <i class="fa fa-warning"></i> Menggunakan kata kunci yang unik / rahasia untuk URL login dapat membantu mengamankan website anda dari serangan melalui form login. Hindari menggunakan kata kunci seperti <b>login , admin , masuk , adminpanel </b> dan lainnya yang familiar.</small>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="pwa">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Untuk semua icon, usahakan sesuai keterangan resolusi atau cukup gambar dengan rasio 1:1 dan minimal resolusi 512px * 512px
                            </div>
                            @foreach ($pwa as $r)
                                @if ($r[2] == 'file')
                                    <small for="" class="text-muted">{{ $r[0] }}</small>
                                    @if (get_option($r[1]) && media_exists(get_option($r[1])))
                                        <br><img height="60" src="{{ url(get_option($r[1])) }}"
                                            onerror="{{ url('backend/images/noimage.png') }}"> &nbsp;<a
                                            href="javascript:void(0)" onclick="media_destroy('{{ get_option($r[1]) }}')"
                                            class=" btn-sm text-danger"> <i class="fa fa-trash"></i> </a>
                                        <br>
                                    @else
                                        <input accept="image/png,imgage/jpeg" type="file"
                                            class="form-control-sm form-control-file compress-image" name="{{ $r[1] }}">
                                    @endif
                                @else

                                    <small for="" class="text-muted">{{ $r[0] }}</small>
                                    <input type="text"
                                        @if ($r[2] == 'number') oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" @endif
                                        class="form-control form-control-sm" placeholder="Masukkan {{ $r[0] }}"
                                        name="{{ $r[1] }}" value="{{ $r[1] == 'site_url' && empty(get_option($r[1])) ? request()->getHttpHost() : get_option($r[1]) }}">
                                @endif
                            @endforeach

                        </div>

                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="fa fa-info"></i> Pengaturan tidak dapat diubah karena cache config aktif, silahkan nonaktifkan <a href="{{route('cache-manager')}}" class="">disini.</a>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        @push('scripts')
        <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
        @endpush
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    @include('cms::backend.layout.js')
        @endpush
@endsection
