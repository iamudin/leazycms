@extends('cms::backend.layout.app',['title'=>get_post_type('title_crud')])
@section('content')
<form class="editor-form" action="{{URL::full()}}" method="post" enctype="multipart/form-data">
   @csrf
   @method('PUT')
   <div class="row">
      <div class="col-lg-12">
        <h3 style="font-weight:normal">
            <i class="fa {{ $module->icon }}" aria-hidden="true"></i> {{ get_post_type('title_crud') }}
            <a href="{{ route(get_post_type()) }}" class="btn btn-outline-danger btn-sm pull-right"
                data-toggle="tooltip" title="Kembali Ke Index Data"> <i class="fa fa-undo" aria-hidden></i>
                Kembali</a>
        </h3>
        <br>
        @include('cms::backend.layout.error')
      </div>
      <div class="col-lg-9">
        <div class="form-group">
            @if($looping_data)
            <input type="hidden" name="title" value="{{ $post->title ?? '' }}">
            <div class="alert alert-primary py-2" style="border-left:4px solid #000;font-size:20px">{{ $post->title ?? '' }}</div>
            @else
            <input data-toggle="tooltip" title="Masukkan {{ $module->datatable->data_title }}" required
                name="title" type="text" value="{{ $post->title ?? '' }}"
                placeholder="Masukkan {{ $module->datatable->data_title }}" class="form-control form-control-lg">
            @endif

        </div>
        @include('cms::backend.posts.list-menu')

      </div>
      <div class="col-lg-3">

         <div class="form-group form-inline">
            <div class="animated-radio-button">
               <label>
               <input {{($post && $post->status == 'publish')? 'checked=checked':''}} required type="radio" name="status" value="publish"><small class="label-text">Publikasikan</small>
               </label>
            </div>
            &nbsp;&nbsp;&nbsp;
            <div class="animated-radio-button">
               <label>
               <input {{($post && $post->status == 'draft')? 'checked=checked':''}} required type="radio" name="status" value="draft"><small class="label-text">Draft</small>
               </label>
            </div>
         </div>
         <button @if(Auth::user()->level=='admin' || Auth::user()==$post->author) name="save" value="@if(empty($post))add @else {{$post->id}} @endif" type="submit" data-toggle="tooltip" title="Simpan Perubahan" @else type="button"  onclick="alert('Anda bukan pemilik konten ini!')" @endif class="btn btn-md btn-outline-primary w-100 add">SIMPAN</button><br><br>
      </div>
   </div>
</form>
@include('cms::backend.layout.js')

@endsection
