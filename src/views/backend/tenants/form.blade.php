@extends('cms::backend.layout.app', ['title' => $tenant ? 'Edit Tenant' : 'Tambah Tenant'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-globe" aria-hidden="true"></i> {{ $tenant ? 'Edit Tenant' : 'Tambah Tenant' }}</h3>
            <div class="pull-right">
                <a href="{{ route('tenant.index') }}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Batal</a>
            </div>
        </div>
        <div class="col-lg-12">
            @include('cms::backend.layout.error')
            <form autocomplete="off" action="{{ $tenant ? route('tenant.update', $tenant->id) : route('tenant.store') }}" method="post">
                @csrf
                @if($tenant)
                    @method('PUT')
                @endif
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white">Informasi Tenant</div>
                    <div class="card-body">
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Nama Tenant</label>
                            <input class="form-control form-control-sm" name="name" type="text" placeholder="Masukkan Nama Tenant" value="{{ $tenant ? $tenant->name : old('name') }}" required>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Domain / URL</label>
                            <input class="form-control form-control-sm" name="domain" type="text" placeholder="Masukkan Domain atau URL" value="{{ $tenant ? $tenant->domain : old('domain') }}" required>
                            <small class="text-muted">Jika memasukkan URL (misal: http://sub.domain.com), sistem akan otomatis mengambil hostname-nya.</small>
                        </div>
                        <div class="form-group mt-2 mb-2">

                                    <label for="">Module</label>
                            <select name="modules[]" id="select2" class="form-control form-control-sm form-control-select" multiple id="">
                                @foreach($modules as $k=>$row)
                                <option  {{ in_array($k, $tenant->modules ?? []) ? 'selected' : '' }} value="{{  $k }}">{{ $row }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Pilih Tema</label>
                            <select class="form-control form-control-sm" name="theme" id="theme-select" {{ (old('custom_theme') == '1' || ($tenant && $tenant->custom_theme)) ? '' : 'required' }}>
                                <option value="">-- Pilih Tema --</option>
                                @if($tenant && $tenant->custom_theme && $tenant->theme && !$themes->contains('path', $tenant->theme))
                                    <option value="{{ $tenant->theme }}" selected>{{ Str::title(str_replace('-', ' ', $tenant->theme)) }} (Tema Custom Aktif)</option>
                                @endif
                                @foreach($themes as $row)
                                    <option value="{{ $row->path }}" {{ (($tenant && $tenant->theme == $row->path) || old('theme') == $row->path) ? 'selected' : '' }}>{{ $row->name }} ({{ $row->path }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Custom Theme ?</label><br>
                            <input name="custom_theme" id="custom-theme-check" type="checkbox" value="1" {{ (old('custom_theme') == '1' || ($tenant && $tenant->custom_theme)) ? 'checked' : '' }}> <small class="text-muted">Ceklis jika ingin menduplikasi tema terpilih khusus untuk tenant ini agar bisa diedit secara terpisah.</small>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
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
                            <input class="form-control form-control-sm" name="admin_name" type="text" placeholder="Masukkan Nama Administrator" value="{{ $admin ? $admin->name : old('admin_name') }}" required>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Email Admin</label>
                            <input class="form-control form-control-sm" name="admin_email" type="email" placeholder="Masukkan Email Admin" value="{{ $admin ? $admin->email : old('admin_email') }}" required>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Username Admin</label>
                            <input onkeyup="this.value = this.value.replace(/\s+/g, '').toLowerCase();" class="form-control form-control-sm" name="admin_username" type="text" placeholder="Masukkan Username Admin" value="{{ $admin ? $admin->username : old('admin_username') }}" required>
                        </div>
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Password Admin {{ $admin ? '(Kosongkan jika tidak ganti)' : '' }}</label>
                            <div class="input-group">
                                <input type="password" id="admin_password" name="admin_password" class="form-control form-control-sm" placeholder="Masukkan Password Admin" {{ $admin ? '' : 'required' }}>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="generatePassword()">Generate</button>
                                    <button type="button" class="btn btn-info btn-sm" onclick="togglePassword()">Show</button>
                                </div>
                            </div>
                            <small class="text-danger">Minimal 8 karakter, mengandung Huruf Besar, Huruf Kecil, Angka, dan Simbol.</small>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">Konfigurasi & Opsi Tenant</div>
                    <div class="card-body">
                        <div class="form-group mt-2 mb-2">
                            <label class="mb-0">Dapat Edit Template ?</label><br>
                            <input name="options[can_edit_template]" type="radio" value="Y" {{ (isset($options['can_edit_template']) && $options['can_edit_template'] == 'Y') ? 'checked' : '' }}> Iya &nbsp; &nbsp;
                            <input name="options[can_edit_template]" type="radio" value="N" {{ (!isset($options['can_edit_template']) || $options['can_edit_template'] == 'N') ? 'checked' : '' }}> Tidak
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
                                ['Postingan Perhalaman', 'post_perpage', 'number'],
                            ];
                        @endphp

                        @foreach($siteAttributes as $attr)
                            <div class="form-group mt-2 mb-2">
                                <label class="mb-0">{{ $attr[0] }}</label>
                                @if($attr[2] == 'textarea')
                                    <textarea class="form-control form-control-sm" name="options[{{ $attr[1] }}]" rows="2">{{ $options[$attr[1]] ?? '' }}</textarea>
                                @else
                                    <input class="form-control form-control-sm" name="options[{{ $attr[1] }}]" type="{{ $attr[2] }}" value="{{ $options[$attr[1]] ?? '' }}">
                                @endif
                            </div>
                        @endforeach

                        @php
                            $configOptions = array_filter(config('modules.config.option', []), fn($value, $key) => $key !== 'template', ARRAY_FILTER_USE_BOTH);
                        @endphp

                        @if(count($configOptions) > 0)
                            <hr>
                            <h6>Opsi Tambahan Modul</h6>
                            @foreach($configOptions as $groupName => $fields)
                                <div class="mb-3 border p-2 rounded">
                                    <strong>{{ str($groupName)->headline() }}</strong>
                                    @foreach($fields as $field)
                                        @php
                                            $fieldName = _us($field[0]);
                                            if(collect($siteAttributes)->pluck(1)->contains($fieldName)) continue;
                                            $fieldType = $field[1];
                                            $fieldLabel = $field[0];
                                            $currentValue = $options[$fieldName] ?? '';
                                        @endphp
                                        <div class="form-group mt-2 mb-2">
                                            <label class="mb-0">{{ $fieldLabel }}</label>
                                            @if($fieldType == 'file')
                                                <input class="form-control form-control-sm" name="options[{{ $fieldName }}]" type="text" placeholder="URL File" value="{{ $currentValue }}">
                                                <small class="text-muted">Masukkan URL atau path file</small>
                                            @elseif($fieldType == 'textarea')
                                                <textarea class="form-control form-control-sm" name="options[{{ $fieldName }}]" rows="2">{{ $currentValue }}</textarea>
                                            @else
                                                <input class="form-control form-control-sm" name="options[{{ $fieldName }}]" type="{{ $fieldType == 'number' ? 'number' : 'text' }}" value="{{ $currentValue }}">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        @endif
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
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
            let retVal = "";

            // Pastikan memenuhi syarat regex (Min 1 Kapital, 1 kecil, 1 angka, 1 simbol)
            retVal += "ABCDEFGHIJKLMNOPQRSTUVWXYZ".charAt(Math.floor(Math.random() * 26));
            retVal += "abcdefghijklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 26));
            retVal += "0123456789".charAt(Math.floor(Math.random() * 10));
            retVal += "!@#$%^&*()_+".charAt(Math.floor(Math.random() * 12));

            for (let i = 4; i < length; ++i) {
                retVal += charset.charAt(Math.floor(Math.random() * charset.length));
            }

            // Acak urutan
            retVal = retVal.split('').sort(function(){return 0.5-Math.random()}).join('');

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
    </script>
    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#select2').select2({
                tags: true,
                placeholder: 'Pilih Modul'
            });
        });
    </script>

    @include('cms::backend.layout.js')
    @endpush
@endsection
