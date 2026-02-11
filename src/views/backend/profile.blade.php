@extends('cms::backend.layout.app', ['title' => 'Profile'])
@section('content')

        <div class="row">
            <div class="col-lg-12">
                <h3 style="font-weight:normal">
                    <i class="fa fa-building" aria-hidden="true"></i> Profile
                    <div class="btn-group pull-right">
                           <button type="button" onclick="$('.btn-submit').click()" class="btn btn-primary btn-sm">
                    <i class="fa fa-save"></i> Simpan
                </button>
                        <a href="{{ route('panel.dashboard') }}" class="btn btn-danger btn-sm">
                            <i class="fa fa-undo" aria-hidden="true"></i> Kembali
                        </a>
                    </div>
                </h3>
            </div>
        </div>

        <div class="row mt-4">

         <div class="col-lg-12">
        <form method="POST" action="{{ route('profile') }}" class="form-profile" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- ========== SECTION: IDENTITAS ORGANISASI ========== -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <strong><i class="fa fa-id-card"></i> Identitas Organisasi</strong>
                </div>
                <div class="card-body row">
                    <div class="col-lg-2">
                        <div class="form-group text-center">
                            <small>Logo Organisasi</small>

                            @if (get_option('logo_organisasi') && media_exists(get_option('logo_organisasi')))
                            <br>
                                <img src="{{ get_option('logo_organisasi') }}" alt="Logo Organisasi"
                                    class=" rounded" style="max-height: 200px"> <br>
                            <a title="Hapus"
                                                href="javascript:void(0)" onclick="media_destroy('{{ get_option('logo_organisasi') }}')"
                                                class=" btn-sm text-danger"> <i class="fa fa-trash"></i> </a>
                            @else
                              <input type="file" class="form-control form-control-sm compress-image" name="logo_organisasi"
                                accept="image/*">
                            @endif
                    </div>
                    </div>
                    <div class="col-lg-10">
                    <div class="form-group">
                        <small>Nama Organisasi</small>
                        <input type="text" class="form-control form-control-sm" name="nama_organisasi"
                            value="{{ old('nama_organisasi', get_option('nama_organisasi')) }}"
                            required placeholder="Masukkan nama organisasi">
                    </div>

                    <div class="form-group">
                        <small>Singkatan</small>
                        <input type="text" class="form-control form-control-sm" name="singkatan_organisasi"
                            value="{{ old('singkatan', get_option('singkatan')) }}"
                            placeholder="Masukkan singkatan organisasi">
                    </div>
      <div class="form-group">
                        <small>Keterangan</small>
                        <textarea class="form-control form-control-sm" name="keterangan_organisasi" rows="3"
                            placeholder="Masukkan keterangan organisasi">{{ old('keterangan_organisasi', get_option('keterangan_organisasi')) }}</textarea>
                    </div>
                </div>
                </div>

            </div>


            <!-- ========== SECTION: INFORMASI ALAMAT ========== -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <strong><i class="fa fa-map-marker"></i> Alamat & Lokasi</strong>
                </div>
                <div class="card-body">

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
                        <input type="latitude" class="form-control form-control-sm" name="kelurahan"
                            value="{{ old('kelurahan', get_option('kelurahan')) }}" placeholder="Masukkan  Kelurahan / Desa">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <small>Kecamatan</small>
                        <input type="longitude" class="form-control form-control-sm" name="kecamatan"
                            value="{{ old('kecamatan', get_option('kecamatan')) }}" placeholder="Masukkan Kecamatan">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <small>Kabupaten</small>
                        <input type="longitude" class="form-control form-control-sm" name="kabupaten"
                            value="{{ old('kabupaten', get_option('kabupaten')) }}" placeholder="Masukkan Kabupaten">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <small>Provinsi</small>
                        <input type="longitude" class="form-control form-control-sm" name="provinsi"
                            value="{{ old('provinsi', get_option('provinsi')) }}" placeholder="Masukkan Provinsi">
                    </div>
                </div>
            </div>
                    <div class="form-group row mb-0">
                        <div class="col-lg-12">
                        <b>Titik Kordinat</b>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <small>Latitude</small>
                                <input type="latitude" class="form-control form-control-sm" name="latitude"
                                    value="{{ old('latitude', get_option('latitude')) }}"
                                    placeholder="Masukkan misal : -6.123456">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <small>Longitude</small>
                                <input type="longitude" class="form-control form-control-sm" name="longitude"
                                    value="{{ old('longitude', get_option('longitude')) }}"
                                    placeholder="Masukkan misal : 102.234567">
                            </div>
                        </div>
                    </div>

                </div>
            </div>


            <!-- ========== SECTION: KONTAK ORGANISASI ========== -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <strong><i class="fa fa-phone"></i> Informasi Kontak</strong>
                </div>
                <div class="card-body">

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
            </div>


            <!-- ========== SECTION: MEDIA SOSIAL ========== -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <strong><i class="fa fa-share-alt"></i> Media Sosial</strong>
                </div>
                <div class="card-body">

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
                            value="{{ old('whatsapp', get_option('whatsapp')) }}"
                            placeholder="Nomor WhatsApp">
                    </div>
                <button type="submit" class="btn-submit" style="display: none"></button>
                </div>
            </div>



        </form>
    </div>

        </div>
    @push('scripts')
    @include('cms::backend.layout.js')
    @endpush
@endsection
