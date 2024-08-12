@extends('cms::backend.layout.app',['title'=>'Akun'])
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

         <center><img class="img-thumbnail w-100" id="thumb" src="{{url($user->photo)}}" /></center><br>
    <input onchange="readURL(this);"  accept="image/jpeg,image/png"  type="file" class="form-control-file photo" name="photo" >
  </div>
</div>
<div class="col-lg-8">
      <small for="">Nama</small>
      <input required type="text" class="form-control form-control-sm name" name="name" placeholder="Masukkan Nama" value="{{$user->name}}">
      <small for="">Email</small>
      <input required type="email" class="form-control form-control-sm email" name="email" placeholder="Masukkan Email" value="{{$user->email}}">

      <small  for="">Username</small>
      <input required type="text" class="form-control form-control-sm username" name="username" placeholder="Masukkan Username" value="{{$user->username}}">
      <small for="">Password</small>
      <input type="password" class="form-control form-control-sm password" name="password" placeholder="Masukkan Password" value="">
        <small for="">Konfimasi Password</small>
        <input type="password" class="form-control form-control-sm password" name="password_confirmation" placeholder="Masukkan Password" value="">
        <small class="text-danger">*) Kosongkan jika tidak mengubah password</small>
      </div>
    </div>
</div>
</div>
</form>
<script>
    function readURL(input) {
      const allow = ['gif','png','jpeg','jpg'];
      var ext = input.value.replace(/^.*\./, '');
      if(!allow.includes(ext)){
        notif('Pilih hanya gambar','danger');
        input.value='';
      }else {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#thumb')
                    .attr('src', e.target.result)
                    .width('100px')
            };

            reader.readAsDataURL(input.files[0]);
        }
    }
  }
  </script>
@endsection
