@extends('views::backend.layout.app',['title'=>'Akun'])
@section('content')
<form class="" action="{{URL::full()}}" method="post" enctype="multipart/form-data">
  @csrf
<div class="row">
<div class="col-lg-12"><h3 style="font-weight:normal"> <i class="fa fa-user" aria-hidden="true"></i> Akun <button name="save" value="true" class="btn btn-outline-primary btn-sm pull-right"> <i class="fa fa-save" aria-hidden></i> Simpan</button></h3>
  <br>
  <div class="form-group">
         <center><img class="img-responsive"  onclick="window.open(this.src)" style="border:none;width:100px" id="thumb" src="{{thumb($data->photo)}}" /></center><br>
    <label for="">Foto Pengguna</label>
    <input type="file" class="form-control photo compress-image" name="photo" value="{{$data->photo}}">
  </div>

    <div class="form-group">
      <label for="">Nama</label>
      <input required type="text" class="form-control name" name="name" placeholder="Masukkan Nama" value="{{$data->name}}">
    </div>
    <div class="form-group">
      <label for="">Email</label>
      <input required type="email" class="form-control email" name="email" placeholder="Masukkan Email" value="{{$data->email}}">
    </div>

    <div class="form-group">
      <label  for="">Username</label>
      <div class="input-group">
        <input onkeyup="this.value = this.value.replace(/\s+/g, '').toLowerCase();" required type="text" class="form-control username" id="admin_username" name="username" placeholder="Masukkan Username" value="{{$data->username}}">
        <div class="input-group-append">
          <button type="button" class="btn btn-secondary btn-sm" onclick="generateUsername()">Generate</button>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for="">Password</label>
      <div class="input-group">
        <input type="password" class="form-control password" id="admin_password" name="password" placeholder="Masukkan Password" value="">
        <div class="input-group-append">
          <button type="button" class="btn btn-secondary btn-sm" onclick="generatePassword()">Generate</button>
          <button type="button" class="btn btn-info btn-sm" onclick="togglePassword()">Show</button>
        </div>
      </div>
      <small class="text-danger">Minimal 8 karakter, mengandung Huruf Besar, Huruf Kecil, Angka, dan Simbol ($@!%*?&).</small>
    </div>
    <div class="form-group">
        <label for="">Konfimasi Password</label>
        <input type="password" class="form-control password" name="password2" placeholder="Masukkan Password" value="">
        <small class="text-danger">*) Kosongkan jika tidak mengubah password</small>
      </div>
</div>
</div>
</form>
@push('scripts')
<script>
    function generateUsername() {
        const length = 6;
        const charset = "abcdefghijklmnopqrstuvwxyz";
        let retVal = "";
        for (let i = 0; i < length; ++i) {
            retVal += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        document.getElementById("admin_username").value = retVal;
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
@include('views::backend.layout.js')
@endpush
@endsection
