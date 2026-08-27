@php
    $currencyVal = !empty(old(_us($r[0]))) ? old(_us($r[0])) : (isset($field[_us($r[0])]) && !empty($field[_us($r[0])]) ? $field[_us($r[0])] : null);
    if (!empty($currencyVal) && is_numeric(str_replace(['.', ','], '', $currencyVal))) {
        $currencyVal = number_format((float)str_replace(['.', ','], '', $currencyVal), 0, ',', '.');
    }
@endphp
<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text font-weight-bold">Rp</span>
    </div>
    <input {{ isset($r[1]->required) ? 'required':'' }} type="text"
        value="{{ $currencyVal }}"
        class="form-control"
        id="{{_us($r[0])}}"
        name="{{_us($r[0])}}"
        placeholder="0"
        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
</div>
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif
