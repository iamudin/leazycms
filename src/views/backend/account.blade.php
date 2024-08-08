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
    <input onchange="readURL(this);"  type="file" class="form-control photo" name="photo" value="{{$data->photo}}">
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
      <input required type="text" class="form-control username" name="username" placeholder="Masukkan Username" value="{{$data->username}}">
    </div>
    <div class="form-group">
      <label for="">Password</label>
      <input type="password" class="form-control password" name="password" placeholder="Masukkan Password" value="">
    </div>
    <div class="form-group">
        <label for="">Konfimasi Password</label>
        <input type="password" class="form-control password" name="password2" placeholder="Masukkan Password" value="">
        <small class="text-danger">*) Kosongkan jika tidak mengubah password</small>
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
