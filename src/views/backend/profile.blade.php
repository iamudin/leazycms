@extends('cms::backend.layout.app', ['title' => 'Setting › Profile'])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 style="font-weight:normal">
                <i class="fa fa-building" aria-hidden="true"></i> Setting › Profile
                <div class="btn-group pull-right">
                    @if(!app()->configurationIsCached())

                        <button type="button" onclick="$('.btn-submit').click()" class="btn btn-primary btn-sm">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    @endif
                    <a href="{{ route('panel.dashboard') }}" class="btn btn-danger btn-sm">
                        <i class="fa fa-undo" aria-hidden="true"></i> Kembali
                    </a>
                </div>
            </h3>
        </div>
    </div>

    <div class="row mt-4">

        <div class="col-lg-12">
            @if(app()->configurationIsCached())
                <div class="alert alert-danger">
                    <i class="fa fa-info"></i> Pengaturan Profile tidak dapat diubah karena cache config aktif, silahkan
                    nonaktifkan <a href="{{route('cache-manager')}}" class="">disini.</a>
                </div>
            @else

                <form method="POST" action="{{ route('profile') }}" class="form-profile" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card mb-3">
                        <div class="card-header bg-light p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="profileTab" role="tablist"
                                style="border-bottom: none; padding-top: 10px; padding-left: 10px;">
                                <li class="nav-item">
                                    <a class="nav-link active" id="identitas-tab" data-toggle="tab" href="#identitas" role="tab"
                                        aria-controls="identitas" aria-selected="true"
                                        style="border-top-left-radius: .25rem; border-top-right-radius: .25rem;">
                                        <i class="fa fa-id-card"></i> Identitas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="alamat-tab" data-toggle="tab" href="#alamat" role="tab"
                                        aria-controls="alamat" aria-selected="false">
                                        <i class="fa fa-map-marker"></i> Alamat & Lokasi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="kontak-tab" data-toggle="tab" href="#kontak" role="tab"
                                        aria-controls="kontak" aria-selected="false">
                                        <i class="fa fa-phone"></i> Kontak
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="medsos-tab" data-toggle="tab" href="#medsos" role="tab"
                                        aria-controls="medsos" aria-selected="false">
                                        <i class="fa fa-share-alt"></i> Media Sosial
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="profileTabContent">

                                <!-- ========== TAB: IDENTITAS ORGANISASI ========== -->
                                <div class="tab-pane fade show active" id="identitas" role="tabpanel"
                                    aria-labelledby="identitas-tab">
                                    <div class="row">
                                        <div class="col-lg-2">
                                            <div class="form-group text-center">
                                                <small>Logo Organisasi</small>

                                                @if (get_option('logo_organisasi') && media_exists(get_option('logo_organisasi')))
                                                    <div class="media-preview-wrapper">
                                                        <br>
                                                        <img src="{{ get_option('logo_organisasi') }}" alt="Logo Organisasi"
                                                            class=" rounded" style="width:100%"> <br>
                                                        <a title="Hapus" href="javascript:void(0)"
                                                            class="btn-sm text-danger btn-remove-media"
                                                            data-field="logo_organisasi"> <i class="fa fa-trash"></i> </a>
                                                    </div>
                                                @endif
                                                <div class="media-input-wrapper"
                                                    style="{{ (get_option('logo_organisasi') && media_exists(get_option('logo_organisasi'))) ? 'display:none;' : '' }}">
                                                    <input type="file" class="form-control form-control-sm compress-image"
                                                        name="logo_organisasi" accept="image/webp,image/png,image/webp">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-10">
                                            <div class="form-group">
                                                <small>Nama Organisasi</small>
                                                <input type="text" class="form-control form-control-sm" name="nama_organisasi"
                                                    value="{{ old('nama_organisasi', get_option('nama_organisasi')) }}" required
                                                    placeholder="Masukkan nama organisasi">
                                            </div>

                                            <div class="form-group">
                                                <small>Singkatan</small>
                                                <input type="text" class="form-control form-control-sm"
                                                    name="singkatan_organisasi"
                                                    value="{{ old('singkatan', get_option('singkatan_organisasi')) }}"
                                                    placeholder="Masukkan singkatan organisasi">
                                            </div>
                                            <div class="form-group">
                                                <small>Keterangan</small>
                                                <textarea class="form-control form-control-sm" name="keterangan_organisasi"
                                                    rows="3"
                                                    placeholder="Masukkan keterangan organisasi">{{ old('keterangan_organisasi', get_option('keterangan_organisasi')) }}</textarea>
                                            </div>
                                            <div class="form-group mt-3">
                                                <small>Jam Kerja</small>

                                                @php
                                                    $jamKerja = get_option('jam_kerja');
                                                    $jamKerja = preg_replace('/<br\s*\/?>/i', "&#10;", $jamKerja);
                                                @endphp

                                                <textarea class="form-control form-control-sm" name="jam_kerja" rows="4"
                                                    placeholder="Misal:&#10;Senin - Kamis: 08.00 - 16.00&#10;Jumat: 08.00 - 11.30&#10;Sabtu - Minggu: Tutup">{!! old('jam_kerja', $jamKerja) !!}</textarea>

                                                <small class="text-muted">
                                                    Gunakan tombol Enter untuk baris baru.
                                                </small>
                                            </div>
                                            <div class="form-group">
                                                <small class="mb-5">Informasi Statis<br></small>
                                                @foreach (array_merge(['Sejarah', 'Visi dan Misi', 'Struktur Organisasi'], config('modules.static_menu_profile')) as $key => $page)
                                                    <div class="btn-group mr-1 mb-2" role="group">
                                                        <a class="btn btn-outline-success btn-sm"
                                                            href="{{ route('page.create') }}?slug={{ str($page)->slug() }}"
                                                            target="_blank">
                                                            <i class="fa fa-edit"></i> {{ str($page)->headline() }}
                                                        </a>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm js-copy-url"
                                                            data-copy-url="{{ url(str($page)->slug()) }}">
                                                            <i class="fa fa-copy"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ========== TAB: INFORMASI ALAMAT ========== -->
                                <div class="tab-pane fade" id="alamat" role="tabpanel" aria-labelledby="alamat-tab">
                                    <div class="form-group">
                                        <small>Alamat Kantor</small>
                                        <textarea class="form-control form-control-sm" name="alamat" rows="3"
                                            placeholder="Masukkan alamat lengkap">{{ old('alamat', get_option('alamat')) }}</textarea>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <div class="col-lg-12">
                                            <b>Wilayah</b>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <small>Kelurahan/Desa</small>
                                                <input type="text" class="form-control form-control-sm" name="kelurahan"
                                                    value="{{ old('kelurahan', get_option('kelurahan')) }}"
                                                    placeholder="Masukkan Kelurahan / Desa">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <small>Kecamatan</small>
                                                <input type="text" class="form-control form-control-sm" name="kecamatan"
                                                    value="{{ old('kecamatan', get_option('kecamatan')) }}"
                                                    placeholder="Masukkan Kecamatan">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <small>Kabupaten</small>
                                                <input type="text" class="form-control form-control-sm" name="kabupaten"
                                                    value="{{ old('kabupaten', get_option('kabupaten')) }}"
                                                    placeholder="Masukkan Kabupaten">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <small>Provinsi</small>
                                                <input type="text" class="form-control form-control-sm" name="provinsi"
                                                    value="{{ old('provinsi', get_option('provinsi')) }}"
                                                    placeholder="Masukkan Provinsi">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <div class="col-lg-12">
                                            <b>Titik Koordinat</b>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <small>Latitude</small>
                                                <input type="text" class="form-control form-control-sm" name="latitude"
                                                    value="{{ old('latitude', get_option('latitude')) }}"
                                                    placeholder="Masukkan misal : -6.123456">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <small>Longitude</small>
                                                <input type="text" class="form-control form-control-sm" name="longitude"
                                                    value="{{ old('longitude', get_option('longitude')) }}"
                                                    placeholder="Masukkan misal : 102.234567">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ========== TAB: KONTAK ORGANISASI ========== -->
                                <div class="tab-pane fade" id="kontak" role="tabpanel" aria-labelledby="kontak-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <small>Email</small>
                                                <input type="email" class="form-control form-control-sm" name="email"
                                                    value="{{ old('email', get_option('email')) }}"
                                                    placeholder="Masukkan email resmi">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <small>Telepon</small>
                                                <input type="text" class="form-control form-control-sm" name="telepon"
                                                    value="{{ old('telepon', get_option('telepon')) }}"
                                                    placeholder="Masukkan nomor telepon">
                                            </div>
                                        </div>
                                    </div>


                                </div>

                                <!-- ========== TAB: MEDIA SOSIAL ========== -->
                                <div class="tab-pane fade" id="medsos" role="tabpanel" aria-labelledby="medsos-tab">
                                    <div class="form-group">
                                        <small>Facebook</small>
                                        <input type="url" class="form-control form-control-sm" name="facebook"
                                            value="{{ old('facebook', get_option('facebook')) }}"
                                            placeholder="Link Facebook organisasi">
                                    </div>

                                    <div class="form-group">
                                        <small>Instagram</small>
                                        <input type="url" class="form-control form-control-sm" name="instagram"
                                            value="{{ old('instagram', get_option('instagram')) }}"
                                            placeholder="Link Instagram organisasi">
                                    </div>

                                    <div class="form-group">
                                        <small>Twitter / X</small>
                                        <input type="url" class="form-control form-control-sm" name="twitter"
                                            value="{{ old('twitter', get_option('twitter')) }}"
                                            placeholder="Link Twitter organisasi">
                                    </div>

                                    <div class="form-group">
                                        <small>YouTube</small>
                                        <input type="url" class="form-control form-control-sm" name="youtube"
                                            value="{{ old('youtube', get_option('youtube')) }}"
                                            placeholder="Link channel YouTube">
                                    </div>

                                    <div class="form-group">
                                        <small>WhatsApp</small>
                                        <input type="number" class="form-control form-control-sm" name="whatsapp"
                                            value="{{ old('whatsapp', get_option('whatsapp')) }}" placeholder="Nomor WhatsApp">
                                    </div>
                                    <button type="submit" class="btn-submit" style="display: none"></button>
                                </div>

                            </div>
                        </div>
                    </div>



                </form>
            @endif
        </div>

    </div>
    @push('scripts')
        @include('cms::backend.layout.js')
        <script>
            (() => {
                const copyText = async (text) => {
                    if (!text) return false;
                    if (navigator.clipboard && window.isSecureContext) {
                        try {
                            await navigator.clipboard.writeText(text);
                            return true;
                        } catch (e) {
                        }
                    }
                    try {
                        const ta = document.createElement('textarea');
                        ta.value = text;
                        ta.setAttribute('readonly', '');
                        ta.style.position = 'fixed';
                        ta.style.top = '-9999px';
                        document.body.appendChild(ta);
                        ta.select();
                        const ok = document.execCommand('copy');
                        document.body.removeChild(ta);
                        return ok;
                    } catch (e) {
                        return false;
                    }
                };

                document.addEventListener('click', async (e) => {
                    const btn = e.target.closest('.js-copy-url');
                    if (!btn) return;
                    e.preventDefault();
                    const url = btn.getAttribute('data-copy-url');
                    const ok = await copyText(url);
                    const original = btn.innerHTML;
                    btn.innerHTML = ok ? '<i class="fa fa-check"></i> Copied' : '<i class="fa fa-triangle-exclamation"></i> Failed';
                    setTimeout(() => {
                        btn.innerHTML = original;
                    }, 1200);
                });
            })();


        </script>
    @endpush
@endsection