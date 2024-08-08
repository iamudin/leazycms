<small for="{{_us($r[0])}}">{{$r[0]}}</small><br>
@php $key = _us($r[0]) @endphp
@if(isset($field[_us($r[0])]) && media_exists($post->field->$key))
<input  type="hidden" name="{{_us($r[0])}}" value="{{ $field[_us($r[0])]}}">
<a href={{asset($field[_us($r[0])]) }} class="btn btn-outline-info btn-sm" style="margin-top:4px">Lihat {{$r[0]}} (.{{ str(get_ext($field[_us($r[0])]))->upper() }})</a> <a  title="Hapus dokumen untuk mengganti" data-toggle="tooltip"  class="fa fa-trash text-danger" style="cursor:pointer" onclick="media_destroy('{{ $field[_us($r[0])]}}')"></a>
@else
<input {{ isset($r[2]) ? 'required':'' }} accept="{{ allow_mime() }}" type="file" class="form-control-sm" value="{{ $field[_us($r[0])]??null }}" name="{{_us($r[0])}}" placeholder="Entri {{$r[0]}}">
@endif
<br>

