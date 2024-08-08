@extends('cms::backend.layout.app',['title'=> $user? 'Edit User':'Tambah User'])
@section('content')
<div class="row">
<div class="col-lg-12 mb-3">
  <h3 style="font-weight:normal;float:left"><i class="fa fa-users" aria-hidden="true"></i> {{ $user? 'Edit User':'Tambah User' }}
</h3>
<div class="pull-right">
    @if(Route::has('user'))
    <a href="{{route('user')}}" class="btn btn-outline-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Batal</a>
    @endif
</div>
</div>
<div class="col-lg-12">
    @if ($user)
    <div style="border-left:3px solid green" class="alert alert-success"><b>URL : </b><a
            title="Kunjungi URL" data-toggle="tooltip" href="{{ url($user->url) }}"
            target="_blank"><i><u>{{ url($user->url) }}</u></i></a> <span
            title="Klik Untuk Menyalin alamat URL Kategori" data-toggle="tooltip"
            class="pointer copy pull-right badge badge-primary" data-copy="{{ url($user->url) }}"><i
                class="fa fa-copy" aria-hidden></i> <b>Salin</b></span></div>
@endif
@include('cms::backend.layout.error')
        <form autocomplete="off" action="{{ $user ?  route('user.update',$user->id): route('user.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            @if($user)
            @method('PUT')
            @endif
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Foto</label>
                @if($user && $user->photo && media_exists($user,$user->photo))
                <br><img src="{{ url($user->photo) }}" style="height: 70px" class="img-thumbnail"> <a href="javascript:void(0)" onclick="media_destroy('{{ $user->photo }}')" class="btn-danger btn-sm"> <i class="fa fa-trash text-white"></i> </a>
                @else
                  <input accept="image/png,image/jpeg"  class=" form-control-sm form-control-file " name="photo"  type="file">
                @endif
            </div>

            <div class="form-group mt-2 mb-2">
                <label class="mb-0">Nama</label>
                  <input class="form-control form-control-sm " name="name" type="text" placeholder="Masukkan Nama user" value="{{$user ? $user->name : old('name')}}">
            </div>
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Email  [ <i class="text-danger">Email Aktif</i> ]</label>
                <input class="form-control form-control-sm " name="email" type="email" placeholder="Masukkan Email" value="{{$user ? $user->email : old('email')}}">
            </div>
            <div class="form-group mt-2 mb-2">
                <label class="mb-0">Level</label>
                @if(get_option('roles'))
                <select name="level"class="form-control-sm form-control" required>
                    <option value="">--pilih--</option>
                    @foreach(explode(',',get_option('roles')) as $role)
                    <option {{ (($user && $user->level==$role) || old('level') == $role) ? 'selected':'' }} value="{{ $role }}">{{ str($role)->headline() }}</option>

                    @endforeach
                </select>
                @else
                <br>
                <p class="badge badge-danger">
                    Level User (Roles) Belum tersedia, silahkan tambah di menu <b>Pengaturan</b> -> <b>Keamanan</b> -> <b>Roles</b>
                </p>
                @endif

            </div>
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Username [ <i class="text-danger">Tanpa spasi</i> ]</label>
                  <input class="form-control form-control-sm " name="username" type="text" placeholder="Masukkan username" value="{{$user ? $user->username : old('username')}}">
            </div>
            @if($user)<br>
            <div class="alert alert-warning" style="font-size:small;border-left:4px solid brown;min-width:100%"><b class="fa fa-warning"></b> Kosongkan kolom password jika tidak mengganti</div>
            @endif
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Password</label>
                  <input autocomplete="false" class="form-control form-control-sm " name="password" type="password" placeholder="Masukkan password" >
                  <small class="text-danger">Minimal 8 karakter dan di butuhkan Min 1 Kapital, 1 huruf kecil, 1 angka dan symbol khusus</small>
            </div>

            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Konfirmasi Password</label>
                  <input autocomplete="false" class="form-control form-control-sm " name="password_confirmation" type="password" placeholder="Masukkan ulang password">
                  <small class="text-danger">Ketik Ulang Password</small>
            </div>

            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Status</label><br>
                @foreach(['active','blocked'] as $row)
                  <input name="status"  type="radio" value="{{$row}}" {{ (($user && $user->status==$row) || old('status') == $row) ? 'checked':'' }}> {{ str($row)->headline() }} &nbsp; &nbsp;
                  @endforeach
            </div>
            <div class="form-group mt-2  mb-2 text-right">
                <button type="submit" class="btn btn-primary btn-sm"> <i class="fa fa-save"></i> Simpan</button>
            </div>
</form>
</div>
</div>
@push('scripts')
@include('cms::backend.layout.js')
@endpush
@endsection
