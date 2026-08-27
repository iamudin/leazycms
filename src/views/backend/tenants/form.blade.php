@extends('cms::backend.layout.app', ['title' => $tenant ? 'Edit Tenant' : 'Tambah Tenant'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-globe" aria-hidden="true"></i>
                {{ $tenant ? 'Edit Tenant' : 'Tambah Tenant' }}</h3>
            <div class="pull-right">
                <a href="{{ route('tenant.index') }}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i>
                    Batal</a>
            </div>
        </div>
        <div class="col-lg-12">
            @include('cms::backend.layout.error')
            <form autocomplete="off" action="{{ $tenant ? route('tenant.update', $tenant->id) : route('tenant.store') }}"
                method="post">
                @csrf
                @if($tenant)
                    @method('PUT')
                @endif
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white">Informasi Tenant</div>
                    <div class="card-body">
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Nama Tenant</label>
                            <input class="form-control form-control-sm" name="name" type="text"
                                placeholder="Masukkan Nama Tenant" value="{{ $tenant ? $tenant->name : old('name') }}"
                                required>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Domain / URL</label>
                            <input class="form-control form-control-sm" name="domain" type="text"
                                placeholder="Masukkan Domain atau URL"
                                value="{{ $tenant ? $tenant->domain : old('domain') }}" required>
                            <small class="text-muted">Jika memasukkan URL (misal: http://sub.domain.com), sistem akan
                                otomatis mengambil hostname-nya.</small>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Kapasitas Penyimpanan (MB)</label>
                            <input class="form-control form-control-sm" name="disk_space" type="number"
                                placeholder="Misal: 500"
                                value="{{ $tenant ? $tenant->disk_space : old('disk_space') }}">
                            <small class="text-muted">Biarkan kosong atau isi 0 jika tidak ingin membatasi (unmetered).</small>
                        </div>
                
                        <div class="form-group mt-2 mb-2">
                            <label for="">Nonaktifkan Modul (Disallow Modules)</label>
                            <select name="modules[]" id="select2" class="form-control form-control-sm form-control-select"
                                multiple id="">
                                @foreach($modules as $k => $row)
                                    <option {{ in_array($k, $tenant->modules ?? []) ? 'selected' : '' }} value="{{  $k }}">
                                        {{ $row }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Pilih modul dari sistem (config modules.menu) yang <strong>TIDAK DIIZINKAN / Nonaktif</strong> untuk tenant ini.</small>
                        </div>

                        @if(isset($availablePlugins) && count($availablePlugins) > 0)
                            <div class="form-group mt-2 mb-2">
                                <label class="mb-2 d-block">Akses Plugin</label>
                                @foreach($availablePlugins as $pluginName)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="plugins[]"
                                            id="plugin_{{ $pluginName }}" value="{{ $pluginName }}" {{ in_array($pluginName, $tenant->plugins ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="plugin_{{ $pluginName }}">{{ Str::title(str_replace('-', ' ', $pluginName)) }}</label>
                                    </div>
                                @endforeach
                                <br><small class="text-muted">Pilih plugin mana saja yang aktif untuk tenant ini.</small>
                            </div>
                        @endif

                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Pilih Tema</label>
                            @php
                                $activeTheme = old('theme', $tenant ? ($options['template'] ?? $tenant->theme) : null);
                            @endphp
                            <select class="form-control form-control-sm" name="theme" id="theme-select" {{ (old('custom_theme') == '1' || ($tenant && $tenant->custom_theme)) ? '' : 'required' }}>
                                <option value="">-- Pilih Tema --</option>
                                <option value="default" {{ $activeTheme == 'default' ? 'selected' : '' }}>Default</option>

                                @if($activeTheme && $activeTheme != 'default' && !$themes->contains('path', $activeTheme))
                                    <option value="{{ $activeTheme }}" selected>
                                        {{ Str::title(str_replace('-', ' ', $activeTheme)) }} (Cloud / Custom Aktif)
                                    </option>
                                @endif

                                @foreach($themes as $row)
                                    <option value="{{ $row->path }}" {{ $activeTheme == $row->path ? 'selected' : '' }}>{{ $row->name }} ({{ $row->path }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Custom Theme ?</label><br>
                            <input name="custom_theme" id="custom-theme-check" type="checkbox" value="1" {{ (old('custom_theme') == '1' || ($tenant && $tenant->custom_theme)) ? 'checked' : '' }}> <small
                                class="text-muted">Ceklis jika ingin menduplikasi tema terpilih khusus untuk tenant ini agar
                                bisa diedit secara terpisah.</small>
                        </div>

                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Izinkan Parkir Domain ?</label><br>
                            <input type="hidden" name="options[allow_park_domain]" value="0">
                            <input name="options[allow_park_domain]" id="allow-park-domain-check" type="checkbox" value="1" {{ (old('options.allow_park_domain') == '1' || ($options['allow_park_domain'] ?? '0') == '1') ? 'checked' : '' }}> <small
                                class="text-muted">Ceklis jika ingin mengizinkan tenant ini melakukan parkir custom domain melalui menu Setting -> Domain di panel mereka.</small>
                        </div>

                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Izinkan Kelola Pengguna (Manage User) ?</label><br>
                            <input type="hidden" name="options[allow_manage_user]" value="0">
                            <input name="options[allow_manage_user]" id="allow-manage-user-check" type="checkbox" value="1" {{ (old('options.allow_manage_user') == '1' || in_array($options['allow_manage_user'] ?? '0', ['1', 1, 'true', true, 'Y', 'y'], true)) ? 'checked' : '' }}> <small
                                class="text-muted">Ceklis jika ingin mengizinkan tenant ini mengelola pengguna / menampilkan menu User di sidebar tenant.</small>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const themeSelect = document.getElementById('theme-select');
                                const customThemeCheck = document.getElementById('custom-theme-check');

                                function toggleThemeRequired() {
                                    if (customThemeCheck.checked) {
                                        themeSelect.removeAttribute('required');
                                    } else {
                                        themeSelect.setAttribute('required', 'required');
                                    }
                                }

                                customThemeCheck.addEventListener('change', toggleThemeRequired);
                                toggleThemeRequired();
                            });
                        </script>
                        @if($tenant && parse_url(config('app.url'), PHP_URL_HOST) != $tenant->domain || !$tenant)

                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">Status</label><br>
                                @foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Suspended', 'maintenance' => 'Maintenance'] as $key => $val)
                                    <input name="status" type="radio" value="{{ $key }}" {{ (($tenant && $tenant->status == $key) || old('status', 'active') == $key) ? 'checked' : '' }}> {{ $val }} &nbsp; &nbsp;
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @if($tenant && parse_url(config('app.url'), PHP_URL_HOST) != $tenant->domain || !$tenant)
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">Akun Administrator Tenant</div>
                        <div class="card-body">
                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">Nama Admin</label>
                                <input class="form-control form-control-sm" name="admin_name" type="text"
                                    placeholder="Masukkan Nama Administrator"
                                    value="{{ $admin ? $admin->name : old('admin_name') }}" required>
                            </div>
                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">Email Admin</label>
                                <input class="form-control form-control-sm" name="admin_email" type="email"
                                    placeholder="Masukkan Email Admin" value="{{ $admin ? $admin->email : old('admin_email') }}"
                                    required>
                            </div>
                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">Username Admin</label>
                                <div class="input-group">
                                    <input onkeyup="this.value = this.value.replace(/\s+/g, '').toLowerCase();"
                                        class="form-control form-control-sm" name="admin_username" id="admin_username" type="text"
                                        placeholder="Masukkan Username Admin"
                                        value="{{ $admin ? $admin->username : old('admin_username') }}" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="generateUsername()">Generate</button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">Password Admin {{ $admin ? '(Kosongkan jika tidak ganti)' : '' }}</label>
                                <div class="input-group">
                                    <input type="password" id="admin_password" name="admin_password"
                                        class="form-control form-control-sm" placeholder="Masukkan Password Admin" {{ $admin ? '' : 'required' }}>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="generatePassword()">Generate</button>
                                        <button type="button" class="btn btn-info btn-sm"
                                            onclick="togglePassword()">Show</button>
                                    </div>
                                </div>
                                <small class="text-danger">Minimal 8 karakter, mengandung Huruf Besar, Huruf Kecil, Angka, dan
                                    Simbol.</small>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">Konfigurasi & Opsi Tenant</div>
                        <div class="card-body">
                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0 font-weight-bold">Tampilkan Brand Master (Watermark Powered By) ?</label><br>
                                <input type="hidden" name="options[show_master_brand]" value="0">
                                <input name="options[show_master_brand]" id="show-master-brand-check" type="checkbox" value="1" {{ (old('options.show_master_brand', $options['show_master_brand'] ?? '1') == '1') ? 'checked' : '' }}>
                                <small class="text-muted">Ceklis jika ingin menampilkan watermark "Powered by : [Logo Master] [Brand Name]" di bagian footer website tenant ini.</small>
                            </div>

                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0 font-weight-bold">Bebas Iklan (Ad-Free) ?</label><br>
                                <input type="hidden" name="options[bebas_iklan]" value="0">
                                <input name="options[bebas_iklan]" id="bebas-iklan-check" type="checkbox" value="1" {{ (old('options.bebas_iklan', $options['bebas_iklan'] ?? '0') == '1') ? 'checked' : '' }}>
                                <small class="text-muted">Ceklis jika tenant ini berlangganan paket Bebas Iklan (iklan dari induk tidak akan tampil pada website tenant ini).</small>
                            </div>

                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">Dapat Edit Template ?</label><br>
                                <input name="options[can_edit_template]" type="radio" value="Y" {{ (old('options.can_edit_template', $options['can_edit_template'] ?? 'N') == 'Y') ? 'checked' : '' }}> Iya &nbsp; &nbsp;
                                <input name="options[can_edit_template]" type="radio" value="N" {{ (old('options.can_edit_template', $options['can_edit_template'] ?? 'N') == 'N') ? 'checked' : '' }}> Tidak
                            </div>

                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">Dapat Upload Template ?</label><br>
                                <input name="options[can_upload_template]" type="radio" value="Y" {{ (old('options.can_upload_template', $options['can_upload_template'] ?? 'N') == 'Y') ? 'checked' : '' }}> Iya &nbsp; &nbsp;
                                <input name="options[can_upload_template]" type="radio" value="N" {{ (old('options.can_upload_template', $options['can_upload_template'] ?? 'N') == 'N') ? 'checked' : '' }}> Tidak
                            </div>

                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">Kategori</label>
                                @php
                                    $categories = [
                                        'Pendidikan' => ['Sekolah', 'Perguruan Tinggi', 'Pesantren & Madrasah', 'Kursus & Bimbingan Belajar', 'Pendidikan Lainnya'],
                                        'Pemerintahan' => ['Desa & Kelurahan', 'Kecamatan', 'Dinas & Instansi', 'BUMDes', 'Layanan Publik'],
                                        'Bisnis' => ['UMKM', 'Perusahaan', 'Toko & E-Commerce', 'Jasa', 'Kuliner', 'Properti', 'Industri', 'Bisnis Lainnya'],
                                        'Organisasi' => ['Yayasan', 'Komunitas', 'Organisasi Profesi', 'Organisasi Sosial', 'Organisasi Pemuda', 'Organisasi Lainnya'],
                                        'Publik' => ['Kesehatan', 'Keagamaan', 'Sosial & Kemanusiaan', 'Pariwisata', 'Media & Informasi', 'Layanan Publik'],
                                        'Personal' => ['Portfolio', 'Personal Branding', 'Blog', 'Profesional', 'Kreator'],
                                    ];
                                @endphp
                                <select name="options[category]" class="form-control form-control-sm">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($categories as $group => $cats)
                                        <optgroup label="{{ $group }}">
                                            @foreach($cats as $cat)
                                                <option value="{{ $cat }}" {{ (old('options.category', $options['category'] ?? '') == $cat) ? 'selected' : '' }}>{{ $cat }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <hr>
                            <h6>Informasi Situs (SEO & General)</h6>
                            @php
                                $siteAttributes = [
                                    ['Alamat Situs Web', 'site_url', 'text'],
                                    ['Nama Situs Web', 'site_title', 'text'],
                                    ['Deskripsi Situs Web', 'site_description', 'text'],
                                    ['SEO Meta Keyword', 'site_meta_keyword', 'text'],
                                    ['SEO Meta Description', 'site_meta_description', 'text'],
                                    ['Google Analytics Code', 'google_analytics_code', 'text'],
                                    ['Google Verification Code', 'google_verification_code', 'text'],
                                    ['Postingan Perhalaman', 'post_perpage', 'number'],
                                ];
                            @endphp

                            @foreach($siteAttributes as $attr)
                                <div class="form-group mt-2 mb-2">
                                    <label class="mb-0">{{ $attr[0] }}</label>
                                    @if($attr[2] == 'textarea')
                                        <textarea class="form-control form-control-sm" name="options[{{ $attr[1] }}]"
                                            rows="2">{{ old("options.{$attr[1]}", $options[$attr[1]] ?? '') }}</textarea>
                                    @elseif($attr[1] == 'google_verification_code')
                                        <input class="form-control form-control-sm" name="options[{{ $attr[1] }}]" type="text"
                                            placeholder="Contoh: aR200GWxv78O3x4u2wYLKnVbtH03bwYdFzO7Fv2x0TI"
                                            oninput="var m = this.value.match(/content=['&quot;]([^'&quot;]+)['&quot;]/i); if(m && m[1]) this.value = m[1];"
                                            onpaste="var el = this; setTimeout(function(){ var m = el.value.match(/content=['&quot;]([^'&quot;]+)['&quot;]/i); if(m && m[1]) el.value = m[1]; }, 10);"
                                            value="{{ old("options.{$attr[1]}", $options[$attr[1]] ?? '') }}">
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                            Contoh: <code>aR200GWxv78O3x4u2wYLKnVbtH03bwYdFzO7Fv2x0TI</code> atau copy-paste full tag: <code>&lt;meta name="google-site-verification" content="aR200GWxv78O3x4u2wYLKnVbtH03bwYdFzO7Fv2x0TI" /&gt;</code> (sistem otomatis hanya mengambil isi kodenya saja).
                                        </small>
                                    @else
                                        <input class="form-control form-control-sm" name="options[{{ $attr[1] }}]" type="{{ $attr[2] }}"
                                            value="{{ old("options.{$attr[1]}", $options[$attr[1]] ?? '') }}">
                                    @endif
                                </div>
                            @endforeach

                        </div>
                    </div>
                @endif

                <div class="form-group mt-2 mb-2 text-right">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Tenant & Admin</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function generatePassword() {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$@!%*?&";
            let retVal = "";

            // Pastikan memenuhi syarat regex (Min 1 Kapital, 1 kecil, 1 angka, 1 simbol)
            retVal += "ABCDEFGHIJKLMNOPQRSTUVWXYZ".charAt(Math.floor(Math.random() * 26));
            retVal += "abcdefghijklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 26));
            retVal += "0123456789".charAt(Math.floor(Math.random() * 10));
            retVal += "$@!%*?&".charAt(Math.floor(Math.random() * 7));

            for (let i = 4; i < length; ++i) {
                retVal += charset.charAt(Math.floor(Math.random() * charset.length));
            }

            // Acak urutan
            retVal = retVal.split('').sort(function () { return 0.5 - Math.random() }).join('');

            document.getElementById("admin_password").value = retVal;
            document.getElementById("admin_password").type = "text";
        }

        function togglePassword() {
            const x = document.getElementById("admin_password");
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }

        function generateUsername() {
            const length = 6;
            const charset = "abcdefghijklmnopqrstuvwxyz";
            let retVal = "";
            for (let i = 0; i < length; ++i) {
                retVal += charset.charAt(Math.floor(Math.random() * charset.length));
            }
            document.getElementById("admin_username").value = retVal;
        }
    </script>
    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#select2').select2({
                    tags: true,
                    placeholder: 'Pilih Modul'
                });
            });
        </script>

        @include('cms::backend.layout.js')
    @endpush
@endsection