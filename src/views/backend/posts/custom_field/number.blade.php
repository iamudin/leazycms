<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<input {{ isset($r[1]->required) ? 'required':'' }} type="text" value="{{$field[_us($r[0])] ?? ''}}" class="form-control"   oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" name="{{_us($r[0])}}" placeholder="Entri {{$r[0]}}">
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif
