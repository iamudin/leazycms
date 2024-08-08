@extends('cms::backend.layout.app', ['title' => 'Lihat Media'])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 style="font-weight:normal"><i class="fa {{ get_module_info('icon') }}" aria-hidden="true"></i> Lihat Media<a
                    href="{{ admin_url(get_post_type()) }}" class="btn btn-outline-danger btn-sm pull-right"> <i
                        class="fa fa-undo" aria-hidden></i> Kembali</a></h3>
            <br>
        </div>
        <div class="col-lg-7">
            @if (isImage($post->mime))
                <img class="img-thumbnail w-100" src="{{ route('stream',$post->slug) }}" alt="">
            @elseif(get_ext($post->slug)== 'pdf')
                <iframe style="width:100%;height:80vh;border:0;" src="{{  route('stream',$post->slug) }}"
                    frameborder="0"></iframe>
            @elseif(get_ext($post->slug) == 'docx' || get_ext($post->slug) == 'doc')
                <iframe style="width:100%;height:80vh;border:0;"
                    src="https://docs.google.com/gview?url={{ secure_asset($post->url) }}&embedded=true"></iframe>
            @else
                <center><h1>.{{ get_ext($post->slug) }}</h1></center>
            @endif

        </div>
        <div class="col-lg-5">

            @foreach ($module->form->custom_field as $r)
                <small for="">{{ $r[0] }} :</small><br>
                <label>{{ $field[_us($r[0])] ?? '-' }}</label><br>
            @endforeach

            <small for="">Waktu Upload :</small><br>
            <label>{{ $post->created_at }}</label><br>
            @if($post->post_parent)
            <small for="">Diupload dari :</small><br>
            @php
                $src = route($post->post_parent->type.'.edit',$post->post_parent->id);
            @endphp
            <label><a href="{{ $src }}" style="word-break: break-all;">{{ $src }}</a> </label>

            <br>
            @endif
            <small>Link Media : </small><br>
            <label> <a href="{{  route('stream',$post->slug) }}" style="word-break: break-all;">{{  route('stream',$post->slug) }}</a></label><br>
            <small for="">Oleh :</small><br>
            <label>{{ $post->user->name }}</label><br>
        </div>

    </div>
@endsection
