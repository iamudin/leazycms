<small for="{{_us($r[0])}}">{{$r[0]}}</small><br>
@php $key = _us($r[0]) @endphp
@if(isset($field[_us($r[0])]) && media_exists($post->field->$key))
    <div class="media-preview-wrapper">
    <input type="hidden" name="{{_us($r[0])}}" value="{{ $field[_us($r[0])]}}">
    <span class="btn btn-outline-info btn-sm btn-view-media"
        data-ext="{{ str(media_extension($post->field->$key))->lower() }}" data-media="{{$post->field->$key }} "
        style="margin-top:4px">Lihat {{$r[0]}} (.{{ str(get_ext($field[_us($r[0])]))->upper() }})</span>
    @if(!Route::is($post->type . '.show')) <a title="Hapus dokumen untuk mengganti" data-toggle="tooltip"
        class="fa fa-trash text-danger pointer btn-remove-media" data-field="{{ _us($r[0]) }}"></a>
    @endif
    </div>
@else
    @if(Route::is($post->type . '.show'))
        <small class="text-danger">Tidak teresedia</small>
    @endif
@endif
    <div class="media-input-wrapper" style="{{ (isset($field[_us($r[0])]) && media_exists($post->field->$key)) ? 'display:none;' : '' }}">
    <input {{ (isset($r[1]->required) && !isset($field[_us($r[0])])) ? 'required' : '' }}
        accept="{{ isset($r[3]->mime_type) ? $r[3]->mime_type : allow_mime() }}" type="file"
        class="compress-image form-control form-control-file" value="{{ $field[_us($r[0])] ?? null }}" name="{{_us($r[0])}}"
        placeholder="Entri {{$r[0]}}">
    </div>
<br>