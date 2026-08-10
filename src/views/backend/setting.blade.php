@extends('cms::backend.layout.app', ['title' => 'Setting › Website'])
@section('content')
    <form class="settingForm" action="{{ URL::full() }}" method="post" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal;margin-bottom:20px"> <i class="fa fa-globe"></i> Setting › Website <div
                        class="btn-group pull-right">
                        @if (!app()->configurationIsCached() || config('modules.multisite_enabled'))
                            <button name="save_setting" value="true" class="btn btn-primary btn-sm"> <i class="fa fa-save"
                                    aria-hidden></i> Simpan</button>
                        @endif
                        <a href="{{ route('panel.dashboard') }}" class="btn btn-danger btn-sm"> <i class="fa fa-undo"
                                aria-hidden></i> Kembali</a>
                    </div>
                </h3>
                @include('cms::backend.layout.error')
                @if (!app()->configurationIsCached() || config('modules.multisite_enabled'))
                    <ul class="nav nav-tabs">

                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#profile"> <i
                                    class="fa fa-search"></i>
                                S E O</a></li>
                        @if (is_main_domain())
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#keamanan"> <i
                                        class="fa fa-gears"></i>
                                    Lainnya</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#pwa"> <i
                                    class="fa fa-mobile-alt"></i>
                                PWA</a></li>
                        @if (is_main_domain())
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#gdrive"> <i
                                        class="fab fa-google-drive"></i>
                                    Google Drive</a></li>
                        @endif
                    </ul>
                    <div class="tab-content pt-2" id="myTabContent">

                        <div class="tab-pane fade  active show" id="profile">
                            <div class="form-group mb-3 pb-2" style="border-bottom:1px dashed #ccc;">
                                <small class="text-muted d-block font-weight-bold mb-1">Indeks Mesin Pencari (Search Engine Indexing)</small>
                                <label class="d-flex align-items-center gap-2 mb-1" style="cursor: pointer;">
                                    <input type="checkbox" name="allow_search_engine" value="Y" {{ get_option('allow_search_engine', (config('modules.multisite_enabled') && function_exists('is_main_domain') && is_main_domain() ? 'N' : 'Y')) == 'Y' ? 'checked' : '' }}>
                                    <span>Izinkan mesin pencari mengindeks website ini (Google, Bing, Yahoo, dll.)</span>
                                </label>
                                <small class="text-muted d-block" style="font-size: 11px;">Jika diaktifkan, meta robots bernilai <code>index, follow</code>. Jika tidak diaktifkan, meta robots bernilai <code>noindex, nofollow</code> agar website tidak masuk ke pencarian mesin pencari.</small>
                            </div>

                            @foreach ($site_attribute as $r)
                                @if ($r[2] == 'file')
                                    @if ($r[1] == 'favicon')
                                        @if (is_main_domain() || (config('modules.multisite_enabled') && !get_option('favicon_for_all') && !is_main_domain()))
                                            <small for="" class="text-muted">Favicon (didukung hanya file gambar format .ico)</small>
                                            @if (is_main_domain() && config('modules.multisite_enabled'))
                                                <br> <input name="favicon_for_all" value="1" type="checkbox"
                                                    @if (get_option('favicon_for_all')) checked @endif> (Aktikan untuk Semua tenant)
                                            @endif
                                            
                                            @php
                                                $faviconUrl = get_option('favicon') ? (str_starts_with(get_option('favicon'), '/') ? get_option('favicon') : '/' . ltrim(get_option('favicon'), '/')) : '/favicon.ico';
                                            @endphp

                                            @if (get_option('favicon') && media_exists(get_option('favicon')))
                                                <div class="media-preview-wrapper">
                                                    <br><img height="60" src="{{ $faviconUrl }}" onerror="{{ noimage() }}"> &nbsp;<a
                                                        href="javascript:void(0)" class="btn-sm text-danger btn-remove-media" data-field="favicon"> <i class="fa fa-trash"></i> </a>
                                                    <br>
                                                </div>
                                            @elseif(file_exists(public_path('favicon.ico')))
                                                <div class="media-preview-wrapper">
                                                    <br><img height="60" src="/favicon.ico?v={{ time() }}" onerror="{{ noimage() }}"> &nbsp;<a
                                                        href="javascript:void(0)" class="btn-sm text-danger btn-remove-media" data-field="favicon"> <i class="fa fa-trash"></i> </a>
                                                    <br>
                                                </div>
                                            @endif
                                            
                                            <div class="media-input-wrapper" style="{{ (get_option('favicon') || file_exists(public_path('favicon.ico'))) ? 'display:none;' : '' }}">
                                                <input accept=".ico,image/x-icon,image/vnd.microsoft.icon" type="file"
                                                    class="form-control-sm form-control-file compress-image" name="favicon">
                                            </div>
                                        @endif
                                    @else
                                        <small for="" class="text-muted">{{ $r[0] }}</small>
                                        @if (get_option($r[1]) && media_exists(get_option($r[1])))
                                            <div class="media-preview-wrapper">
                                                <br><img height="60" src="{{ get_option($r[1]) }}"
                                                    onerror="{{ url('backend/images/noimage.png') }}"> &nbsp;<a
                                                    href="javascript:void(0)" class="btn-sm text-danger btn-remove-media" data-field="{{ $r[1] }}"> <i class="fa fa-trash"></i> </a>
                                                <br>
                                            </div>
                                        @endif
                                        <div class="media-input-wrapper" style="{{ (get_option($r[1]) && media_exists(get_option($r[1]))) ? 'display:none;' : '' }}">
                                            <input accept="image/png,image/jpeg,image/gif,image/webp" type="file"
                                                class="form-control-sm form-control-file compress-image"
                                                name="{{ $r[1] }}">
                                        </div>
                                    @endif
                                @else
                                    <small for="" class="text-muted">{{ $r[0] }}
                                        @if (is_main_domain())
                                            @if ($r[1] == 'site_title')
                                                <br><input type="checkbox" name="show_site_title_after_page_name"
                                                    value="true"
                                                    {{ get_option('show_site_title_after_page_name') ? 'checked' : '' }}>
                                                Tampilkan setelah Nama Halaman
                                            @endif
                                        @endif
                                    </small>
                                    <input type="text"
                                        @if ($r[2] == 'number') oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" @endif
                                        class="form-control form-control-sm" placeholder="Masukkan {{ $r[0] }}"
                                        name="{{ $r[1] }}"
                                        value="{{ $r[1] == 'site_url' && empty(get_option($r[1])) ? request()->getHttpHost() : get_option($r[1]) }}">
                                @endif
                            @endforeach

                        </div>
                        @if (is_main_domain())
                            <div class="tab-pane fade" id="keamanan">
                                <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-clock"></i> Zona
                                    Waktu
                                </h6>
                                <select name="timezone" class="form-control form-control-sm">
                                    <option value="Asia/Jakarta"
                                        {{ config('app.timezone') == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)
                                    </option>
                                    <option value="Asia/Makassar"
                                        {{ config('app.timezone') == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar
                                        (WITA)
                                    </option>
                                    <option value="Asia/Jayapura"
                                        {{ config('app.timezone') == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura
                                        (WIT)
                                    </option>
                                </select>
                                <br>
                                <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-lock"></i>
                                    Keamanan
                                </h6>
                                @foreach ($security as $r)
                                    @php
                                        $key = _us($r[0]);
                                    @endphp
                                    <small for="" class="text-muted">{{ $r[0] }} @if ($key == 'allow_ip')
                                            <i class="text-danger">(Khusus Akses API eg : url/berita/api/{id})</i>
                                        @endif
                                    </small>   @if (_us($r[0]) == 'forbidden_keyword')
                                    {!! help('Keyword default terfilter : '.implode(',', forbidden_keyword())) !!}
                                    @endif
                                    <br>

                                    @if ($key == 'filter_request_client')
                                        {!! help('Jika aktif, request client akan difilter menggunakan Forbidden Keyword.') !!}
                                        <div class="clearfix" style="margin-bottom:10px">
                                            <div class="pull-right">
                                                <input name="{{ $key }}" data-width="140" value="Y"
                                                    {{ get_option($key) == 'Y' ? 'checked' : '' }}
                                                    type="checkbox" class="toggle-status" data-on="Active"
                                                    data-off="Inactive" data-toggle="toggle"
                                                    data-onstyle="outline-success" data-offstyle="outline-danger"
                                                    data-size="sm">
                                            </div>
                                        </div>
                                    @else
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="Enter {{ $r[1] }}" name="{{ $key }}"
                                            value="{{ get_option($key) }}">
                                    @endif
                                @endforeach
                                <br>
                                <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-warning"></i>
                                    Notifikasi Serangan via Telegram</h6>
                                <small for="" class="text-muted">Bot Token</small><br>
                                <input type="text" class="form-control form-control-sm"
                                    placeholder="Enter Bot Token Telegram 3434:tokentelegram"
                                    value="{{ dec64(config('modules.teletoken')) }}" name="telegram_token">
                                <small for="" class="text-muted">Chat ID</small><br>
                                <input type="text" class="form-control form-control-sm"
                                    placeholder="Enter chat ID 12345678"
                                    value="{{ dec64(config('modules.telechatid')) }}" name="telegram_chat_id">
                                <br>
                                <h6 for="" style="border-bottom:1px dashed #000"> <i
                                        class="fa fa-keyboard-o"></i>
                                    Web Control</h6>
                                <div class="list-group mb-4">
                                    @foreach ($shortcut as $r)
                                    @if($r[1]=='sub_app_enabled' && config('modules.multisite_enabled'))
                                    @else
                                        <div class="list-group-item py-2"><strong for=""
                                                class="text-muted">{{ $r[0] }}</strong>
                                            <div class="pull-right"><input name="{{ $r[1] }}" data-width="100"
                                                    {{ get_option($r[1]) == 'Y' ? 'checked' : '' }}
                                                    title="Ubah status data publik atau draft" type="checkbox"
                                                    class="toggle-status" data-on="Active" data-off="Inactive"
                                                    data-toggle="toggle" data-onstyle="outline-success"
                                                    data-offstyle="outline-danger" data-size="sm"></div>
                                        </div>
                                    @endif
                                    @endforeach
                                    <div class="list-group-item py-2">
                                        <strong for="" class="text-muted">Maintenance Status</strong>
                                        <div class="pull-right"><input name="site_maintenance" data-width="100"
                                                {{ get_option('site_maintenance') == 'Y' ? 'checked' : '' }}
                                                title="Ubah status data publik atau draft" type="checkbox"
                                                class="toggle-status" data-on="Active" data-off="Inactive"
                                                data-toggle="toggle" data-onstyle="outline-success"
                                                data-offstyle="outline-danger" data-size="sm"></div>
                                    </div>
                                    <div class="list-group-item py-2">
                                        <strong for="" class="text-muted">App Environment</strong>
                                        <div class="pull-right"><input name="app_env" data-width="100"
                                                {{ get_option('app_env') == 'production' ? 'checked' : '' }}
                                                title="Ubah status data publik atau draft" type="checkbox"
                                                class="toggle-status" data-on="Production" data-off="Local"
                                                data-toggle="toggle" data-onstyle="outline-success"
                                                data-offstyle="outline-danger" data-size="sm"></div>
                                    </div>
                                </div>
                                @if (!app()->routesAreCached())
                                    <h6 for="" style="border-bottom:1px dashed #000"> <i class="fa fa-key"></i>
                                        Login
                                        Path</h6>
                                    <input type="text" class="form-control form-control-sm" name="admin_path"
                                        oninput="this.value = this.value.replace(/[^a-z]/g, '')"
                                        value="{{ admin_path() }}">
                                    <small class="text-danger"> <i class="fa fa-warning"></i> Menggunakan kata kunci yang
                                        unik
                                        / rahasia untuk URL login dapat membantu mengamankan website anda dari serangan
                                        melalui
                                        form login. Hindari menggunakan kata kunci seperti <b>login , admin , masuk ,
                                            adminpanel
                                        </b> dan lainnya yang familiar.</small>
                                @endif
                            </div>
                        @endif
                        <div class="tab-pane fade" id="pwa">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Untuk semua icon, usahakan sesuai keterangan resolusi
                                atau cukup gambar dengan rasio 1:1 dan minimal resolusi 512px * 512px
                            </div>
                            @foreach ($pwa as $r)
                                @if ($r[2] == 'file')
                                    <small for="" class="text-muted">{{ $r[0] }}</small>
                                    @if (get_option($r[1]) && media_exists(get_option($r[1])))
                                        <div class="media-preview-wrapper">
                                            <br><img height="60" src="{{ url(get_option($r[1])) }}"
                                                onerror="{{ url('backend/images/noimage.png') }}"> &nbsp;<a
                                                href="javascript:void(0)" class="btn-sm text-danger btn-remove-media" data-field="{{ $r[1] }}"> <i class="fa fa-trash"></i> </a>
                                            <br>
                                        </div>
                                    @endif
                                    <div class="media-input-wrapper" style="{{ (get_option($r[1]) && media_exists(get_option($r[1]))) ? 'display:none;' : '' }}">
                                        <input accept="image/png,image/jpeg,image/webp,image/gif" type="file"
                                            class="form-control-sm form-control-file compress-image"
                                            name="{{ $r[1] }}">
                                    </div>
                                @else
                                    <small for="" class="text-muted">{{ $r[0] }}</small>
                                    <input type="{{ $r[2] }}"
                                        @if ($r[2] == 'number') oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" @endif
                                        class="form-control form-control-sm" placeholder="Masukkan {{ $r[0] }}"
                                        name="{{ $r[1] }}"
                                        value="{{ $r[1] == 'site_url' && empty(get_option($r[1])) ? request()->getHttpHost() : get_option($r[1]) }}">
                                @endif
                            @endforeach

                        </div>

                        @if (is_main_domain())
                            <div class="tab-pane fade" id="gdrive">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> Konfigurasi kredensial Google Drive API. Kosongkan semua isian jika ingin menonaktifkan fitur upload otomatis ke Google Drive.
                                </div>
                                @foreach ($google_drive as $r)
                                    <div class="form-group mb-2">
                                        <small for="" class="text-muted">{{ $r[0] }}</small>
                                        <input type="{{ $r[2] }}" class="form-control form-control-sm" placeholder="Masukkan {{ $r[0] }}" name="{{ $r[1] }}" value="{{ get_option($r[1]) }}">
                                    </div>
                                @endforeach

                                <div class="form-group mt-4">
                                    <small for="" class="text-muted d-block mb-1">Status Koneksi Google Drive</small>
                                    @if(get_option('google_drive_refresh_token'))
                                        <div class="alert alert-success d-flex justify-content-between align-items-center p-2 mb-0">
                                            <span><i class="fa fa-check-circle"></i> Terhubung dengan Google Drive</span>
                                            <form action="{{ route('setting.gdrive.disconnect') }}" method="POST" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin memutus koneksi Google Drive?')"><i class="fa fa-unlink"></i> Disconnect</button>
                                            </form>
                                        </div>
                                    @else
                                        <div class="alert alert-warning d-flex justify-content-between align-items-center p-2 mb-0">
                                            <span><i class="fa fa-exclamation-triangle"></i> Belum Terhubung</span>
                                            @if(get_option('google_drive_client_id') && get_option('google_drive_client_secret'))
                                                <a href="{{ route('setting.gdrive.auth') }}" class="btn btn-primary btn-sm"><i class="fab fa-google-drive"></i> Connect Google Drive</a>
                                            @else
                                                <button type="button" class="btn btn-secondary btn-sm" disabled title="Isi dan simpan Client ID & Secret terlebih dahulu"><i class="fab fa-google-drive"></i> Connect Google Drive</button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                @else
                    <div class="alert alert-danger">
                        <i class="fa fa-info"></i> Pengaturan tidak dapat diubah karena cache config aktif, silahkan
                        nonaktifkan <a href="{{ route('cache-manager') }}" class="">disini.</a>
                    </div>
                @endif
            </div>
        </div>
    </form>
    @push('styles')
        <style>
            .list-group .list-group-item:hover {
                background-color: #ffe1e1;
            }
        </style>
        <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css"
            rel="stylesheet">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
        @include('cms::backend.layout.js')
        <script>
            $('.settingForm').on('submit', function (e) {
                e.preventDefault();
                let $btn = $(this).find('button[name="save_setting"]');
                let originalText = $btn.html();
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').attr('disabled', 'disabled');
                
                let form = this;
                let actionUrl = $(form).attr('action');
                let formData = new FormData(form);
                formData.append('save_setting', 'true');
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                });

                $.ajax({
                    url: actionUrl,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        let submittedAdminPath = formData.get('admin_path');
                        let originalAdminPath = '{{ admin_path() }}';
                        
                        if (submittedAdminPath && submittedAdminPath !== originalAdminPath) {
                            notif('Berhasil! Mengalihkan ke halaman admin baru...', 'success');
                            let newUrl = window.location.href.replace('/' + originalAdminPath + '/', '/' + submittedAdminPath + '/');
                            window.location.href = newUrl;
                            return;
                        }

                        notif('Berhasil menyimpan pengaturan!', 'success');
                        $btn.html(originalText).removeAttr('disabled');
                        
                        if (typeof response === 'string' && response.includes('<html')) {
                            let newDoc = new DOMParser().parseFromString(response, 'text/html');
                            
                            ['profile', 'keamanan', 'pwa', 'gdrive'].forEach(function(tabId) {
                                let newTab = newDoc.getElementById(tabId);
                                if (newTab && document.getElementById(tabId)) {
                                    document.getElementById(tabId).innerHTML = newTab.innerHTML;
                                }
                            });
                            
                            if ($.fn.bootstrapToggle) {
                                $('.toggle-status').bootstrapToggle();
                            }
                            
                            $('.btn-clear-gmedia').click();
                        }
                    },
                    error: function (xhr) {
                        try {
                            let res = JSON.parse(xhr.responseText);
                            let allMsg = [];
                            if (res.errors) {
                                Object.values(res.errors).forEach(arrMsg => { allMsg = allMsg.concat(arrMsg); });
                                notif(allMsg.join('<br>'), 'danger');
                            } else if (res.message) {
                                notif(res.message, 'danger');
                            } else {
                                notif('Gagal menyimpan perubahan!', 'danger');
                            }
                        } catch (e) {
                            notif('Gagal menyimpan perubahan!', 'danger');
                        }
                        $btn.html(originalText).removeAttr('disabled');
                    }
                });
            });
        </script>
    @endpush
@endsection
