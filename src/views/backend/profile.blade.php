@extends('cms::backend.layout.app', ['title' => 'Setting › Profile'])

@section('content')
    <style>
        .form-profile .form-group {
            margin-bottom: 1.35rem;
        }

        .form-profile label.form-label {
            display: inline-flex;
            align-items: center;
            font-size: 0.95rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }

        .form-profile label.form-label i {
            font-size: 1rem;
            width: 22px;
            text-align: center;
            margin-right: 8px;
        }

        .form-profile .form-control-lg {
            height: calc(1.5em + 1.15rem + 2px);
            font-size: 0.975rem;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            border: 1.5px solid #cbd5e1;
            background-color: #fff;
            color: #1e293b;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-profile .form-control-lg:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
            outline: none;
        }

        .form-profile textarea.form-control-lg {
            height: auto;
            min-height: 105px;
            line-height: 1.55;
        }

        .form-profile .section-divider-title {
            display: flex;
            align-items: center;
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            margin-top: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .form-profile .section-divider-title i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        .form-profile .ql-toolbar.ql-snow {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .form-profile .ql-container.ql-snow {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            border-color: #cbd5e1;
            font-size: 0.975rem;
        }

        .form-profile .nav-tabs .nav-link {
            font-size: 0.95rem;
            font-weight: 600;
            padding: 11px 20px;
            color: #64748b;
        }

        .form-profile .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 700;
        }
    </style>

    <div class="row">
        <div class="col-lg-12">
            <h3 style="font-weight:normal">
                <i class="fa-solid fa-building text-primary" aria-hidden="true"></i> Setting › Profile
                <div class="btn-group pull-right">
                    @if(!app()->configurationIsCached())
                        <button type="button" onclick="$('.btn-submit').click()" class="btn btn-primary btn-md">
                            <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
                        </button>
                    @endif
                    <a href="{{ route('panel.dashboard') }}" class="btn btn-danger btn-md">
                        <i class="fa-solid fa-arrow-left mr-1" aria-hidden="true"></i> Kembali
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

                    <div class="card mb-3 shadow-sm" style="border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden;">
                        <div class="card-header bg-light p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="profileTab" role="tablist"
                                style="border-bottom: none; padding-top: 10px; padding-left: 10px;">
                                <li class="nav-item">
                                    <a class="nav-link active" id="identitas-tab" data-toggle="tab" href="#identitas" role="tab"
                                        aria-controls="identitas" aria-selected="true"
                                        style="border-top-left-radius: .35rem; border-top-right-radius: .35rem;">
                                        <i class="fa-solid fa-id-card mr-1"></i> Identitas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="alamat-tab" data-toggle="tab" href="#alamat" role="tab"
                                        aria-controls="alamat" aria-selected="false">
                                        <i class="fa-solid fa-map-location-dot mr-1"></i> Alamat & Lokasi
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="kontak-tab" data-toggle="tab" href="#kontak" role="tab"
                                        aria-controls="kontak" aria-selected="false">
                                        <i class="fa-solid fa-phone-volume mr-1"></i> Kontak
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="medsos-tab" data-toggle="tab" href="#medsos" role="tab"
                                        aria-controls="medsos" aria-selected="false">
                                        <i class="fa-solid fa-share-nodes mr-1"></i> Media Sosial
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content" id="profileTabContent">

                                <!-- ========== TAB: IDENTITAS ORGANISASI ========== -->
                                <div class="tab-pane fade show active" id="identitas" role="tabpanel"
                                    aria-labelledby="identitas-tab">
                                    <div class="row">
                                        <div class="col-lg-3 col-xl-2">
                                            <div class="form-group text-center">
                                                <label class="form-label font-weight-bold d-block text-center mb-2" for="logo_organisasi">
                                                    <i class="fa-solid fa-image text-primary"></i> Logo Organisasi
                                                </label>

                                                @if (get_option('logo_organisasi') && media_exists(get_option('logo_organisasi')))
                                                    <div class="media-preview-wrapper mb-2">
                                                        <div class="p-2 border rounded bg-light d-inline-block position-relative shadow-sm" style="max-width: 100%;">
                                                            <img src="{{ get_option('logo_organisasi') }}" alt="Logo Organisasi"
                                                                class="rounded" style="max-width: 100%; height: auto; max-height: 140px; object-fit: contain;">
                                                            <div class="mt-2 text-center">
                                                                <a title="Hapus" href="javascript:void(0)"
                                                                    class="btn btn-sm btn-outline-danger btn-remove-media"
                                                                    data-field="logo_organisasi">
                                                                    <i class="fa-solid fa-trash-can mr-1"></i> Hapus Logo
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="media-input-wrapper"
                                                    style="{{ (get_option('logo_organisasi') && media_exists(get_option('logo_organisasi'))) ? 'display:none;' : '' }}">
                                                    <input type="file" id="logo_organisasi" class="form-control form-control-lg compress-image"
                                                        name="logo_organisasi" accept="image/webp,image/png,image/jpeg,image/jpg">
                                                    <small class="text-muted d-block mt-1">Format: PNG, JPG, WEBP</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-9 col-xl-10">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="nama_organisasi">
                                                    <i class="fa-solid fa-building text-primary"></i> Nama Organisasi <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="nama_organisasi" name="nama_organisasi"
                                                    value="{{ old('nama_organisasi', get_option('nama_organisasi')) }}" required
                                                    placeholder="Masukkan nama organisasi">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="singkatan_organisasi">
                                                    <i class="fa-solid fa-tag text-primary"></i> Singkatan
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="singkatan_organisasi"
                                                    name="singkatan_organisasi"
                                                    value="{{ old('singkatan_organisasi', old('singkatan', get_option('singkatan_organisasi'))) }}"
                                                    placeholder="Masukkan singkatan organisasi">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="keterangan_organisasi">
                                                    <i class="fa-solid fa-align-left text-primary"></i> Keterangan
                                                </label>
                                                <textarea class="form-control form-control-lg" id="keterangan_organisasi" name="keterangan_organisasi"
                                                    rows="3"
                                                    placeholder="Masukkan keterangan organisasi">{{ old('keterangan_organisasi', get_option('keterangan_organisasi')) }}</textarea>
                                            </div>

                                            <div class="form-group mt-3 pb-3" style="border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; background: #f8fafc;">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <label class="form-label font-weight-bold mb-0 text-dark" for="welcome_speech">
                                                        <i class="fa-solid fa-volume-high text-primary"></i> Ucapan Selamat Datang (Text-to-Speech Suara)
                                                    </label>
                                                    <label class="mb-0 d-flex align-items-center" style="cursor: pointer; gap: 6px;">
                                                        <input type="checkbox" name="welcome_speech_active" value="Y" {{ get_option('welcome_speech_active', 'N') == 'Y' ? 'checked' : '' }}>
                                                        <span class="badge badge-success font-weight-bold" style="font-size: 11px;">Aktifkan di Beranda</span>
                                                    </label>
                                                </div>
                                                <textarea class="form-control form-control-lg bg-white" id="welcome_speech" name="welcome_speech" rows="2"
                                                    placeholder="Contoh: Selamat datang di website resmi {{ get_option('nama_organisasi', 'kami') }}">{{ old('welcome_speech', get_option('welcome_speech')) }}</textarea>
                                                <small class="text-muted d-block mt-2" style="font-size: 12px;">
                                                    <i class="fa-solid fa-circle-info text-info mr-1"></i> Teks ini akan otomatis dibacakan dengan suara Bahasa Indonesia alami saat pengunjung pertama kali membuka halaman utama website.
                                                </small>
                                            </div>

                                            <div class="form-group mt-3">
                                                <label class="form-label font-weight-bold" for="jam_kerja_organisasi">
                                                    <i class="fa-solid fa-clock text-primary"></i> Jam Kerja
                                                </label>

                                                @php
                                                    $jamKerja = get_option('jam_kerja_organisasi');
                                                    $jamKerja = preg_replace('/<br\s*\/?>/i', "&#10;", $jamKerja);
                                                @endphp

                                                <textarea class="form-control form-control-lg" id="jam_kerja_organisasi" name="jam_kerja_organisasi" rows="4"
                                                    placeholder="Misal:&#10;Senin - Kamis: 08.00 - 16.00&#10;Jumat: 08.00 - 11.30&#10;Sabtu - Minggu: Tutup">{!! old('jam_kerja_organisasi', old('jam_kerja', $jamKerja)) !!}</textarea>

                                                <small class="text-muted d-block mt-1">
                                                    <i class="fa-solid fa-keyboard mr-1"></i> Gunakan tombol Enter untuk baris baru.
                                                </small>
                                            </div>

                                            <div class="form-group mt-3">
                                                <label class="form-label font-weight-bold" for="visi">
                                                    <i class="fa-solid fa-bullseye text-primary"></i> Visi
                                                </label>
                                                @php
                                                    $visi = get_option('visi');
                                                    $visi = preg_replace('/<br\s*\/?>/i', "&#10;", $visi);
                                                @endphp
                                                <textarea class="form-control form-control-lg" id="visi" name="visi" rows="3" placeholder="Masukkan Visi Organisasi">{!! old('visi', $visi) !!}</textarea>
                                            </div>

                                            <div class="form-group mt-3">
                                                <label class="form-label font-weight-bold" for="misi-editor">
                                                    <i class="fa-solid fa-list-check text-primary"></i> Misi
                                                </label>
                                                <!-- Quill CSS -->
                                                <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
                                                
                                                <div id="misi-editor" style="height: 160px; background: #fff; border-radius: 0 0 8px 8px; font-size: 15px;">{!! old('misi', get_option('misi')) !!}</div>
                                                <input type="hidden" name="misi" id="misi-input" value="{{ old('misi', get_option('misi')) }}">
                                                
                                                <!-- Quill JS -->
                                                <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
                                                <script>
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        var quill = new Quill('#misi-editor', {
                                                            theme: 'snow',
                                                            modules: {
                                                                 toolbar: [
                                                                    ['bold', 'italic', 'underline'],
                                                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                                    ['clean']
                                                                ]
                                                            }
                                                        });
                                                        quill.on('text-change', function() {
                                                            var html = quill.root.innerHTML;
                                                            if (html === '<p><br></p>') html = '';
                                                            document.getElementById('misi-input').value = html;
                                                        });
                                                    });
                                                </script>
                                            </div>

                                            <div class="form-group mt-4">
                                                <label class="form-label font-weight-bold mb-2">
                                                    <i class="fa-solid fa-file-lines text-primary"></i> Informasi Statis
                                                </label>
                                                <div>
                                                    @foreach (array_merge(['Sejarah', 'Visi dan Misi', 'Struktur Organisasi'], config('modules.static_menu_profile')) as $key => $page)
                                                        <div class="btn-group mr-2 mb-2" role="group">
                                                            <a class="btn btn-outline-success btn-md"
                                                                href="{{ route('page.create') }}?slug={{ str($page)->slug() }}"
                                                                target="_blank">
                                                                <i class="fa-solid fa-pen-to-square mr-1"></i> {{ str($page)->headline() }}
                                                            </a>
                                                            <button type="button" class="btn btn-outline-secondary btn-md js-copy-url"
                                                                data-copy-url="{{ url(str($page)->slug()) }}" title="Salin URL">
                                                                <i class="fa-solid fa-copy"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ========== TAB: INFORMASI ALAMAT ========== -->
                                <div class="tab-pane fade" id="alamat" role="tabpanel" aria-labelledby="alamat-tab">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold" for="alamat">
                                            <i class="fa-solid fa-map-location-dot text-primary"></i> Alamat Kantor
                                        </label>
                                        <textarea class="form-control form-control-lg" id="alamat" name="alamat" rows="3"
                                            placeholder="Masukkan alamat lengkap">{{ old('alamat', get_option('alamat')) }}</textarea>
                                    </div>

                                    <div class="form-group row mb-0">
                                        <div class="col-lg-12">
                                            <div class="section-divider-title">
                                                <i class="fa-solid fa-earth-asia text-primary"></i> Wilayah Administrasi
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="kelurahan">
                                                    <i class="fa-solid fa-house-chimney text-primary"></i> Kelurahan / Desa
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="kelurahan" name="kelurahan"
                                                    value="{{ old('kelurahan', get_option('kelurahan')) }}"
                                                    placeholder="Masukkan Kelurahan / Desa">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="kecamatan">
                                                    <i class="fa-solid fa-landmark text-primary"></i> Kecamatan
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="kecamatan" name="kecamatan"
                                                    value="{{ old('kecamatan', get_option('kecamatan')) }}"
                                                    placeholder="Masukkan Kecamatan">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="kabupaten">
                                                    <i class="fa-solid fa-city text-primary"></i> Kabupaten / Kota
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="kabupaten" name="kabupaten"
                                                    value="{{ old('kabupaten', get_option('kabupaten')) }}"
                                                    placeholder="Masukkan Kabupaten">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="provinsi">
                                                    <i class="fa-solid fa-map text-primary"></i> Provinsi
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="provinsi" name="provinsi"
                                                    value="{{ old('provinsi', get_option('provinsi')) }}"
                                                    placeholder="Masukkan Provinsi">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-0">
                                        <div class="col-lg-12">
                                            <div class="section-divider-title">
                                                <i class="fa-solid fa-location-crosshairs text-primary"></i> Titik Koordinat GPS
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="latitude">
                                                    <i class="fa-solid fa-arrows-up-down text-primary"></i> Latitude
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="latitude" name="latitude"
                                                    value="{{ old('latitude', get_option('latitude')) }}"
                                                    placeholder="Contoh: -6.123456">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="longitude">
                                                    <i class="fa-solid fa-arrows-left-right text-primary"></i> Longitude
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="longitude" name="longitude"
                                                    value="{{ old('longitude', get_option('longitude')) }}"
                                                    placeholder="Contoh: 102.234567">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ========== TAB: KONTAK ORGANISASI ========== -->
                                <div class="tab-pane fade" id="kontak" role="tabpanel" aria-labelledby="kontak-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="email">
                                                    <i class="fa-solid fa-envelope text-primary"></i> Email Resmi
                                                </label>
                                                <input type="email" class="form-control form-control-lg" id="email" name="email"
                                                    value="{{ old('email', get_option('email')) }}"
                                                    placeholder="nama@organisasi.go.id">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="telepon">
                                                    <i class="fa-solid fa-phone text-primary"></i> Nomor Telepon
                                                </label>
                                                <input type="text" class="form-control form-control-lg" id="telepon" name="telepon"
                                                    value="{{ old('telepon', get_option('telepon')) }}"
                                                    placeholder="Masukkan nomor telepon">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="whatsapp">
                                                    <i class="fa-brands fa-whatsapp text-success"></i> WhatsApp
                                                </label>
                                                <input type="number" class="form-control form-control-lg" id="whatsapp" name="whatsapp"
                                                    value="{{ old('whatsapp', get_option('whatsapp')) }}" placeholder="Contoh: 081234567890">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ========== TAB: MEDIA SOSIAL ========== -->
                                <div class="tab-pane fade" id="medsos" role="tabpanel" aria-labelledby="medsos-tab">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="facebook">
                                                    <i class="fa-brands fa-facebook" style="color: #1877F2;"></i> Facebook
                                                </label>
                                                <input type="url" class="form-control form-control-lg" id="facebook" name="facebook"
                                                    value="{{ old('facebook', get_option('facebook')) }}"
                                                    placeholder="https://facebook.com/namaorganisasi">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="instagram">
                                                    <i class="fa-brands fa-instagram" style="color: #E4405F;"></i> Instagram
                                                </label>
                                                <input type="url" class="form-control form-control-lg" id="instagram" name="instagram"
                                                    value="{{ old('instagram', get_option('instagram')) }}"
                                                    placeholder="https://instagram.com/namaorganisasi">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="twitter">
                                                    <i class="fa-brands fa-x-twitter text-dark"></i> Twitter / X
                                                </label>
                                                <input type="url" class="form-control form-control-lg" id="twitter" name="twitter"
                                                    value="{{ old('twitter', get_option('twitter')) }}"
                                                    placeholder="https://x.com/namaorganisasi">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label font-weight-bold" for="youtube">
                                                    <i class="fa-brands fa-youtube" style="color: #FF0000;"></i> YouTube
                                                </label>
                                                <input type="url" class="form-control form-control-lg" id="youtube" name="youtube"
                                                    value="{{ old('youtube', get_option('youtube')) }}"
                                                    placeholder="https://youtube.com/@namaorganisasi">
                                            </div>
                                        </div>
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
                    btn.innerHTML = ok ? '<i class="fa-solid fa-check mr-1"></i> Tersalin' : '<i class="fa-solid fa-triangle-exclamation mr-1"></i> Gagal';
                    setTimeout(() => {
                        btn.innerHTML = original;
                    }, 1200);
                });
            })();
        </script>
    @endpush
@endsection