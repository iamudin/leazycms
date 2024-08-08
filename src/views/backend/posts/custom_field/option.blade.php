<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<select {{ isset($r[2]) ? 'required':'' }} class="form-control form-control-sm" name="{{_us($r[0])}}">
   <option value="">--pilih--</option>
   @foreach($r[1] as $i)
   <option  {{($field && isset($field[_us($r[0])]) && $field[_us($r[0])]==$i)? 'selected':'' }} value="{{$i}}">{{$i}}</option>
   @endforeach
</select>
