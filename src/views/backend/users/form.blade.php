@extends('cms::backend.layout.app', ['title' => $user ? 'Edit User' : 'Tambah User'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-users" aria-hidden="true"></i>
                {{ $user ? 'Edit User' : 'Tambah User' }}
            </h3>
            <div class="pull-right">
                @if(Route::has('user'))
                    <a href="{{route('user')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Batal</a>
                @endif
            </div>
        </div>
        <div class="col-lg-12">
            @if ($user)
                <div style="border-left:3px solid green" class="alert alert-success"><b>URL : </b><a title="Kunjungi URL"
                        data-toggle="tooltip" href="{{ url('/') }}"
                        target="_blank"><i><u>{{ url('/' . $user->url) }}</u></i></a>
                    <span title="Klik Untuk Menyalin alamat URL Kategori" data-toggle="tooltip"
                        class="pointer copy pull-right badge badge-primary" data-copy="{{ url('/' . $user->url) }}"><i
                            class="fa fa-copy" aria-hidden></i> <b>Salin </b></span>
                </div>
            @endif
            @include('cms::backend.layout.error')
            <form autocomplete="off" action="{{ $user ? route('user.update', $user->id) : route('user.store')}}"
                method="post" enctype="multipart/form-data">
                @csrf
                @if($user)
                    @method('PUT')
                @endif
                <div class="form-group mt-2  mb-2">
                    <label class="mb-0">Foto</label>
                    @if($user && $user->photo && media_exists($user->photo))
                        <div class="media-preview-wrapper">
                            <br><img src="{{ $user->photo_user}}" style="height: 70px" class="img-thumbnail"> <a
                                href="javascript:void(0)" class="btn-danger btn-sm btn-remove-media" data-field="photo"> <i
                                    class="fa fa-trash text-white"></i> </a>
                        </div>
                    @endif
                    <div class="media-input-wrapper"
                        style="{{ ($user && $user->photo && media_exists($user->photo)) ? 'display:none;' : '' }}">
                        <input accept="image/png,image/jpeg,image/webp"
                            class="compress-image form-control-sm form-control-file " name="photo" type="file">
                    </div>
                </div>

                <div class="form-group mt-2 mb-2">
                    <label class="mb-0">Nama</label>
                    <input class="form-control form-control-sm " name="name" type="text" placeholder="Masukkan Nama user"
                        value="{{$user ? $user->name : old('name')}}">
                </div>
                <div class="form-group mt-2  mb-2">
                    <label class="mb-0">Email [ <i class="text-danger">Email Aktif</i> ]</label>
                    <input class="form-control form-control-sm " name="email" type="email" placeholder="Masukkan Email"
                        value="{{$user ? $user->email : old('email')}}">
                </div>
                <div class="form-group mt-2 mb-2">
                    <label class="mb-0">Level</label>

                    <select name="level" class="form-control-sm form-control" required>
                        <option value="">--pilih--</option>
                        @foreach($roles as $role)

                            <option {{ (($user && $user->level == $role) || old('level') == $role) ? 'selected' : '' }}
                                value="{{ $role }}">{{ str($role)->headline() }}</option>
                            </optgroup>
                        @endforeach
                    </select>


                </div>
                <div class="form-group mt-2  mb-2">
                    <label class="mb-0">Username [ <i class="text-danger">Tanpa spasi</i> ]</label>
                    <div class="input-group">
                        <input onkeyup="this.value = this.value.replace(/\s+/g, '').toLowerCase();"
                            class="form-control form-control-sm " name="username" id="username" type="text" placeholder="Masukkan username"
                            value="{{$user ? $user->username : old('username')}}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="generateUsername()">Generate</button>
                        </div>
                    </div>
                </div>
                @if($user)<br>
                    <div class="alert alert-warning" style="font-size:small;border-left:4px solid brown;min-width:100%"><b
                            class="fa fa-warning"></b> Kosongkan kolom password jika tidak mengganti</div>
                @endif
                <div class="form-group">
                    <label>Password</label>

                    <div class="input-group">
                        <input type="text" id="password" name="password" class="form-control"
                            placeholder="Masukkan password" autocomplete="false">

                        <div class="input-group-append">
                            <button type="button" class="btn btn-secondary" onclick="generatePassword()">
                                Generate
                            </button>
                            <button type="button" class="btn btn-info" onclick="togglePassword()">
                                Show
                            </button>
                            <button type="button" class="btn btn-success" onclick="copyPassword()">
                                Copy
                            </button>
                        </div>
                    </div>
                    <small class="text-danger">Minimal 8 karakter dan di butuhkan Min 1 Kapital, 1 huruf kecil, 1 angka dan
                        symbol
                        khusus</small>
                </div>

                <div class="form-group mt-2  mb-2">
                    <label class="mb-0">Konfirmasi Password</label>
                    <input autocomplete="false" class="form-control form-control-sm confirm_password" id="confirm_password"
                        name="password_confirmation" type="password" placeholder="Masukkan ulang password">
                    <small class="text-danger">Ketik Ulang Password</small>
                </div>

                <div class="form-group mt-2  mb-2">
                    <label class="mb-0">Status</label><br>
                    @foreach(['active', 'blocked'] as $row)
                        <input name="status" type="radio" value="{{$row}}" {{ (($user && $user->status == $row) || old('status') == $row) ? 'checked' : '' }}> {{ str($row)->headline() }} &nbsp; &nbsp;
                    @endforeach
                </div>
                <div class="form-group mt-2  mb-2 text-right">
                    <button type="submit" class="btn btn-primary btn-sm"> <i class="fa fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            function generateUsername() {
                const length = 6;
                const charset = "abcdefghijklmnopqrstuvwxyz";
                let retVal = "";
                for (let i = 0; i < length; ++i) {
                    retVal += charset.charAt(Math.floor(Math.random() * charset.length));
                }
                document.getElementById("username").value = retVal;
            }

            function generatePassword() {
                const length = 12;
                const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$@!%*?&";
                let retVal = "";

                retVal += "ABCDEFGHIJKLMNOPQRSTUVWXYZ".charAt(Math.floor(Math.random() * 26));
                retVal += "abcdefghijklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 26));
                retVal += "0123456789".charAt(Math.floor(Math.random() * 10));
                retVal += "$@!%*?&".charAt(Math.floor(Math.random() * 7));

                for (let i = 4; i < length; ++i) {
                    retVal += charset.charAt(Math.floor(Math.random() * charset.length));
                }

                retVal = retVal.split('').sort(function () { return 0.5 - Math.random() }).join('');

                document.getElementById('password').value = retVal;
                document.getElementById('confirm_password').value = retVal;
            }

            function togglePassword() {
                let input = document.getElementById('password');
                input.type = input.type === "password" ? "text" : "password";
            }

            function copyPassword() {
                let input = document.getElementById('password');
                input.select();
                document.execCommand("copy");

                alert("Password copied!");
            }
        </script>
        @include('cms::backend.layout.js')
    @endpush
@endsection