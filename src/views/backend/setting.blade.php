@extends('cms::backend.layout.app', ['title' => 'Pengaturan'])
@section('content')
    <form class="" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal;margin-bottom:20px"> <i class="fa fa-gears"></i> Pengaturan <div class="btn-group pull-right"><button
                        name="save_setting" value="true" class="btn btn-primary btn-sm"> <i
                            class="fa fa-save" aria-hidden></i> Simpan</button>
                            <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
                        </div></h3>
                @include('cms::backend.layout.error')
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
                        @foreach ($option as $r)
                            <small for="" class="text-muted">{{ $r[0] }}</small>
                            @if ($r[1] == 'file')
                                @if (get_option(_us($r[0])) && media_exists(get_option(_us($r[0])), get_option(_us($r[0]))))
                                    <br><a href="{{ url(get_option(_us($r[0]))) }}"
                                        class="btn btn-outline-info btn-sm">Lihat {{ $r[0] }}
                                        (.{{ str(get_ext(get_option(_us($r[0]))))->upper() }})</a> <a
                                        href="javascript:void(0)" onclick="media_destroy('{{ get_option(_us($r[0])) }}')"
                                        class="text-danger btn-sm"> <i class="fa fa-trash"></i> </a>
                                    <br>
                                @else
                                    <input accept="{{ allow_mime() }}" {{ isset($r[2]) ? 'required' : '' }}
                                        name="{{ _us($r[0]) }}" type="file" class="form-control-sm form-control-file">
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
                                    {{ $r == get_option('home_page') ? 'selected' : '' }}>{{ str(str_replace('.blade.php','',$r))->upper(); }}</option>
                            @endforeach
                        </select>
                        @foreach ($site_attribute as $r)
                            @if ($r[2] == 'file')
                                <small for="" class="text-muted">{{ $r[0] }}</small>
                                @if($r[1]=='favicon')
                                <br><img height="60" src="/favicon.ico"
                                onerror="{{ noimage() }}">
                            <br>
                                <input accept="image/png,imgage/jpeg" type="file"
                                class="form-control-sm form-control-file compress-image" name="{{ $r[1] }}">
                                @else
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

                        @foreach ($shortcut as $r)
                        <small for="" class="text-muted">{{ $r[0] }}</small><br>

                        <input type="radio" name="{{ $r[1] }}" value="N"
                        {{ get_option($r[1]) == 'N' ? 'checked' : '' }}> <small>Activate</small>
                    <input type="radio" name="{{ $r[1]}}" value="Y"
                        {{ get_option($r[1]) == 'Y' ? 'checked' : '' }}> <small>Deactivate</small><br>
                    @endforeach
                    <small for="" class="text-muted">Maintenance Status</small><br>
                        <input type="radio" name="site_maintenance" value="Y"
                            {{ get_option('site_maintenance') == 'Y' ? 'checked' : '' }}> <small>Activate</small>
                        <input type="radio" name="site_maintenance" value="N"
                            {{ get_option('site_maintenance') == 'N' ? 'checked' : '' }}> <small>Deactivate</small><br>
                            <small for="" class="text-muted">App Environment ( <span class="text-warning">Local status allows the datatable not to work</span> )</small><br>
                            <input type="radio" name="app_env" value="production"
                                {{ get_option('app_env') == 'production' ? 'checked' : '' }}> <small>Production</small>
                            <input type="radio" name="app_env" value="local"
                                {{ get_option('app_env') == 'local' ? 'checked' : '' }}> <small>Local</small><br>
                    <br>
                    <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-key"></i> Login Path</h6>
                    <input type="text" class="form-control form-control-sm" name="admin_path" value="{{admin_path()}}">
                    <small class="text-danger"> <i class="fa fa-warning"></i> Menggunakan kata kunci yang unik / rahasia untuk URL login dapat membantu mengamankan website anda dari serangan melalui form login. Hindari menggunakan kata kunci seperti <b>login , admin , masuk , adminpanel </b> dan lainnya yang familiar.<br>!!! Mengganti login Path membutuhkan waktu proses +- 10 detik.</small>

                    <br>
                    @if(basename( str_replace('/login','',route('login'))) != admin_path())
                    <div class="alert alert-warning">Terdeteksi perubahan admin path, diperlukan sinkron konfigurasi. <a href="{{ route('admin_path_changer') }}" class="btn btn-md btn-warning" onclick="return confirm('Anda yakin ?')">Sinkron Sekarang</a> </div>
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
            </div>
        </div>
    </form>


    @push('scripts')
        @include('cms::backend.layout.js')
    @endpush
@endsection
