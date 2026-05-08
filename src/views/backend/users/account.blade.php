@extends('cms::backend.layout.app', ['title' => 'Akun'])
@section('content')
  <form class="" action="{{URL::full()}}" method="post" enctype="multipart/form-data">
    @csrf
  <div class="row">
  <div class="col-lg-12"><h3 style="font-weight:normal"> <i class="fa fa-user" aria-hidden="true"></i> Akun <button name="save" value="true" class="btn btn-primary btn-sm pull-right"> <i class="fa fa-save" aria-hidden></i> Perbaharui</button></h3>
    <br>
    @include('cms::backend.layout.error')
    @if(session('success'))
    <div class="alert alert-success">
    Berhasil Perbarui Akun
    </div>
    @endif
    <div class="row">
    <div class="col-lg-4">


    <div class="form-group">

       <center><img class="img-thumbnail w-100" id="thumb" src="{{$user->photo_user}}" /></center><br>
    <input accept="image/jpeg,image/png,image/webp"  type="file" class="form-control-file photo compress-image" name="photo" >
    </div>
  </div>
  <div class="col-lg-8">
      <small for="">Nama</small>
      <input required type="text" class="form-control form-control-sm name" name="name" placeholder="Masukkan Nama" value="{{$user->name}}">
      <small for="">Email</small>
      <input required type="email" class="form-control form-control-sm email" name="email" placeholder="Masukkan Email" value="{{$user->email}}">

      <small  for="">Username</small>
      <input required type="text" class="form-control form-control-sm username" name="username" placeholder="Masukkan Username" value="{{$user->username}}">
            <div class="form-group">
              <small>Password</small>

              <div class="input-group">
                <input type="text" id="password" name="password" class="form-control form-control-sm" placeholder="Masukkan password"
                  autocomplete="false">

                <div class="input-group-append">
                  <button type="button" class="btn btn-secondary btn-sm" onclick="generatePassword()">
                    Generate
                  </button>
                  <button type="button" class="btn btn-info btn-sm" onclick="togglePassword()">
                    Show
                  </button>
                  <button type="button" class="btn btn-success btn-sm" onclick="copyPassword()">
                    Copy
                  </button>
                </div>
              </div>
              <small class="text-danger mb-0">Minimal 8 karakter dan di butuhkan Min 1 Kapital, 1 huruf kecil, 1 angka dan symbol
                khusus</small>
            </div>

            <div class="form-group mb-2 mt-0 pt-0">
              <small class="mb-0">Konfirmasi Password</small>
              <input autocomplete="false" class="form-control form-control-sm confirm_password" id="confirm_password"
                name="password_confirmation" type="password" placeholder="Masukkan ulang password">
              <small class="text-danger">Ketik Ulang Password</small>
            </div>
      </div>
    </div>
  </div>
  </div>
  </form>
  @push('scripts')
      <script>
        function generatePassword() {
          let length = 12;

          let chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
          let password = "";

          for (let i = 0; i < length; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
          }

          document.getElementById('password').value = password;
          document.getElementById('confirm_password').value = password;
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
