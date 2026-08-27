<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<textarea {{ isset($r[1]->required) ? 'required':'' }} type="text"  class="form-control form-control-sm"  name="{{_us($r[0])}}" placeholder="Entri {{$r[0]}}"> {{$field[_us($r[0])] ?? ''}}</textarea>
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif
