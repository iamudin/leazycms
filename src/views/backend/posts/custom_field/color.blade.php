@php
    $colorVal = !empty(old(_us($r[0]))) ? old(_us($r[0])) : (isset($field[_us($r[0])]) && !empty($field[_us($r[0])]) ? $field[_us($r[0])] : '#3b82f6');
    if (empty($colorVal)) {
        $colorVal = '#3b82f6';
    } elseif (!str_starts_with($colorVal, '#') && preg_match('/^[a-fA-F0-9]{6}$/', $colorVal)) {
        $colorVal = '#' . $colorVal;
    }
@endphp
<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<div class="input-group">
    <div class="input-group-prepend">
        <input type="color"
            class="form-control p-1"
            style="width: 44px; height: 38px; cursor: pointer; border-top-right-radius: 0; border-bottom-right-radius: 0;"
            id="picker_{{_us($r[0])}}"
            value="{{ $colorVal }}"
            oninput="document.getElementById('input_{{_us($r[0])}}').value = this.value">
    </div>
    <input {{ isset($r[1]->required) ? 'required':'' }} type="text"
        value="{{ $colorVal }}"
        class="form-control"
        id="input_{{_us($r[0])}}"
        name="{{_us($r[0])}}"
        placeholder="#3b82f6"
        maxlength="7"
        oninput="if(this.value.length === 7 && this.value.startsWith('#')) { document.getElementById('picker_{{_us($r[0])}}').value = this.value; }">
</div>
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif
