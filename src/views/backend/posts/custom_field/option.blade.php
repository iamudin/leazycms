@php
    $isRestricted = isset($r[1]->only_user_id) && auth()->id() != $r[1]->only_user_id;
    $currentVal = $field && isset($field[_us($r[0])]) ? $field[_us($r[0])] : null;
@endphp
<small for="{{_us($r[0])}}">{{$r[0]}}</small>
@if($isRestricted)
    <br><span class="badge badge-primary px-3 py-2" style="font-size:14px;">{{ $currentVal ?: 'Baru' }}</span>
    <input type="hidden" name="{{_us($r[0])}}" value="{{ $currentVal ?? 'Baru' }}">
@else
    <select {{ isset($r[1]->required) ? 'required':'' }} class="form-control" name="{{_us($r[0])}}">
       <option value="">--pilih--</option>
       @foreach($r[1]->type as $i)
       <option  {{($currentVal == $i)? 'selected':'' }} value="{{$i}}">{{$i}}</option>
       @endforeach
    </select>
@endif
