<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<input {{ isset($r[2]) ? 'required':'' }} type="text" value="{{ !empty(old(_us($r[0]))) ? old(_us($r[0])) :  (isset($field[_us($r[0])]) && !empty($field[_us($r[0])])  ? $field[_us($r[0])] : null)}}" class="form-control form-control-sm"  name="{{_us($r[0])}}" placeholder="Entri {{$r[0]}}">
