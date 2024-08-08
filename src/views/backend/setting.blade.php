@extends('cms::backend.layout.app', ['title' => 'Pengaturan'])
@section('content')
    <form class="" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal;margin-bottom:20px"> <i class="fa fa-gears"></i> Pengaturan <button
                        name="save_setting" value="true" class="btn btn-outline-primary btn-sm pull-right"> <i
                            class="fa fa-save" aria-hidden></i> Simpan</button></h3>
                @include('cms::backend.layout.error')
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#home"> <i
                                class="fa fa-home"></i> {{ $web_type ?? 'Organisasi' }}</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#profile"> <i class="fa fa-globe"></i>
                            Situs Web</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#keamanan"> <i class="fa fa-gears"></i>
                            Lainnya</a></li>

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
                        <small for="" class="text-muted">Status Maintenance</small><br>
                        <input type="radio" name="site_maintenance" value="Y"
                            {{ get_option('site_maintenance') == 'Y' ? 'checked' : '' }}> <small>Aktif</small>
                        <input type="radio" name="site_maintenance" value="N"
                            {{ get_option('site_maintenance') == 'N' ? 'checked' : '' }}> <small>Tidak Aktif</small><br>
                        <small>Konten Halaman Utama</small>
                        <select class="form-control form-control-sm" name="home_page">
                            <option value="default">Default</option>
                            @foreach ($home_page as $r)
                                <option value="{{ $r->id }}"
                                    {{ $r->id == get_option('home_page') ? 'selected' : '' }}>{{ $r->title }}</option>
                            @endforeach
                        </select>
                        @foreach ($site_attribute as $r)
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
                                        class="form-control-sm form-control-file" name="{{ $r[1] }}">
                                @endif
                            @else
                                <small for="" class="text-muted">{{ $r[0] }}</small>
                                <input type="text"
                                    @if ($r[2] == 'number') oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" @endif
                                    class="form-control form-control-sm" placeholder="Masukkan {{ $r[0] }}"
                                    name="{{ $r[1] }}" value="{{ get_option($r[1]) }}">
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
                    <br>
                    <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-key"></i> Login Path</h6>
                    <input type="text" class="form-control form-control-sm" name="admin_path" value="{{get_option('admin_path',true)?->value}}">
                    <small class="text-danger"> <i class="fa fa-warning"></i> Menggunakan kata kunci yang unik / rahasia untuk URL login dapat membantu mengamankan website anda dari serangan melalui form login. Hindari menggunakan kata kunci seperti <b>login , admin , masuk , adminpanel </b> dan lainnya yang familiar.</small>

                </div>


                </div>
            </div>
        </div>
    </form>
    @push('scripts')
        @include('cms::backend.layout.js')
    @endpush
@endsection
