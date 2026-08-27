@php
    $phoneVal = !empty(old(_us($r[0]))) ? old(_us($r[0])) : (isset($field[_us($r[0])]) && !empty($field[_us($r[0])]) ? $field[_us($r[0])] : null);
@endphp
<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text"><i class="fa fa-phone"></i></span>
    </div>
    <input {{ isset($r[1]->required) ? 'required':'' }}
        type="tel"
        value="{{ $phoneVal }}"
        class="form-control"
        maxlength="16"
        minlength="9"
        pattern="^(0|62)[0-9]{8,14}$"
        title="Nomor wajib diawali dengan 0 atau 62 (contoh: 081234567890 atau 6281234567890)"
        oninput="let v = this.value.replace(/[^0-9]/g, ''); if ((v.length === 1 && v !== '0' && v !== '6') || (v.length >= 2 && !v.startsWith('0') && !v.startsWith('62'))) { v = ''; } this.value = v;"
        onblur="if (this.value === '6') this.value = '';"
        name="{{_us($r[0])}}"
        placeholder="Contoh: 081234567890 atau 6281234567890">
</div>
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif
