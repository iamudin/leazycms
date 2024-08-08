<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<textarea {{ isset($r[2]) ? 'required':'' }} type="text"  class="form-control form-control-sm"  name="{{_us($r[0])}}" placeholder="Entri {{$r[0]}}"> {{$field[_us($r[0])] ?? ''}}</textarea>
