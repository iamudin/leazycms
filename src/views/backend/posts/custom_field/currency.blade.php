@php
    $rawVal = !empty(old(_us($r[0]))) ? old(_us($r[0])) : (isset($field[_us($r[0])]) && !empty($field[_us($r[0])]) ? $field[_us($r[0])] : '');
    $rawVal = ($rawVal !== '' && $rawVal !== null) ? preg_replace('/[^0-9]/', '', (string)$rawVal) : '';
    $currencyVal = ($rawVal !== '' && $rawVal !== null) ? number_format((float)$rawVal, 0, ',', '.') : '';
@endphp
<small for="display_{{_us($r[0])}}">{{$r[0]}}</small>
<div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text font-weight-bold">Rp</span>
    </div>
    <input type="hidden"
        id="{{_us($r[0])}}"
        name="{{_us($r[0])}}"
        value="{{ $rawVal }}">
    <input {{ isset($r[1]->required) && $r[1]->required ? 'required':'' }} type="text"
        value="{{ $currencyVal }}"
        class="form-control"
        id="display_{{_us($r[0])}}"
        placeholder="0"
        autocomplete="off"
        oninput="
            let raw = this.value.replace(/[^0-9]/g, '');
            if (raw.length > 1 && raw.startsWith('0')) { raw = String(parseInt(raw, 10) || 0); }
            document.getElementById('{{_us($r[0])}}').value = raw;
            this.value = raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        ">
</div>
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif
