@extends('cms::backend.layout.app',['title'=> $polling ? 'Edit Polling':'Tambah Polling'])
@section('content')
<div class="row">
<div class="col-lg-12">
  <h3 style="font-weight:normal;float:left"><i class="fa fa-poll" aria-hidden="true"></i> {{ $polling? 'Edit Polling':'Tambah Polling' }}
</h3>
<div class="pull-right">
    @if(Route::has('polling'))
    <a href="{{route('polling')}}" class="btn btn-outline-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Batal</a>
    @endif
</div>
</div>
<div class="col-lg-12">

@include('cms::backend.layout.error')
        <form autocomplete="off" action="{{ $polling ?  route('polling.update',$polling->id): route('polling.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            @if($polling)
            @method('PUT')
            @endif


            <div class="form-group mt-2 mb-2">
                <label class="mb-0">Judul Polling</label>
                  <input class="form-control form-control-sm " name="title" type="text" placeholder="Masukkan Judul Polling" value="{{$polling ? $polling->title : old('title')}}">
            </div>

            <div class="form-group mt-2 mb-2">
                <label class="mb-0">Kata Kunci</label>
                  <input onkeyup="this.value = this.value.replace(/\s+/g, '').toLowerCase();" @if($polling) readonly @endif  class="form-control form-control-sm " name="keyword" type="text" placeholder="Masukkan Judul Polling" value="{{$polling ? $polling->keyword : old('keyword')}}">
            </div>
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Keterangan </label>
                <textarea class="form-control form-control-sm " name="description" placeholder="Masukkan Keterangan">{{ $polling ? $polling->description : old('description') }}</textarea>
            </div>
            @if($polling)
            <div class="form-group mt-2  mb-2">
            <label class="mb-0">Opsi Polling <a href="{{ route('polling.option.index',$polling->id) }}" class=""> <i class="fa fa-edit"></i> </a></label>
            <ul class="pl-3 m-0">
            @foreach ($polling->options as $item)
            <li>{{ $item->name }}</li>
            @endforeach
        </ul>
            </div>
            @endif
            <div class="form-group mt-2  mb-2">
                <label class="mb-0">Status</label><br>
                @foreach(['draft','publish'] as $row)
                  <input name="status"  type="radio" value="{{$row}}" {{ (($polling && $polling->status==$row) || old('status') == $row) ? 'checked':'' }}> {{ str($row)->headline() }} &nbsp; &nbsp;
                  @endforeach
            </div>
            <div class="form-group mt-2  mb-2 text-right">
                <button type="submit" class="btn btn-outline-primary btn-sm"> <i class="fa fa-save"></i> Simpan</button>
            </div>
</form>
</div>
</div>
@push('scripts')
@include('cms::backend.layout.js')
@endpush
@endsection
