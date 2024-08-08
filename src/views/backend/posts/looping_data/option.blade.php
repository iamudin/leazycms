<select class="form-control form-control-sm" name="{{_us($r[0])}}[]">
    <option value="">-pilih-</option>
    @foreach($r[1] as $r)
    <option {{isset($l->$k) && $l->$k==$r ? 'selected':''}} value="{{$r}}">{{$r}}</option>
    @endforeach
 </select>
