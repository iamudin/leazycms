<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<input {{ isset($r[2]) ? 'required':'' }} type="datetime-local" value="{{$field[_us($r[0])] ?? ''}}" class="form-control form-control-sm"  name="{{_us($r[0])}}" placeholder="Entri {{$r[0]}}">
