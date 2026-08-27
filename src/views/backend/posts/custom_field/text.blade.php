<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<input {{ isset($r[1]->required) ? 'required':'' }} type="text" value="{{ !empty(old(_us($r[0]))) ? old(_us($r[0])) :  (isset($field[_us($r[0])]) && !empty($field[_us($r[0])])  ? $field[_us($r[0])] : null)}}" class="form-control"  name="{{_us($r[0])}}" placeholder="Entri {{$r[0]}}">
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif
