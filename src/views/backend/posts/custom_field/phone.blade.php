<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<input {{ isset($r[2]) ? 'required':'' }} type="text" value="{{$field[_us($r[0])] ?? ''}}" class="form-control form-control-sm" maxlength="14" minlength="12" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" name="{{_us($r[0])}}" placeholder="Entri {{$r[0]}}">
