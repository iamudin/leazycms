@php
    $urlVal = !empty(old(_us($r[0]))) ? old(_us($r[0])) : (isset($field[_us($r[0])]) && !empty($field[_us($r[0])]) ? $field[_us($r[0])] : null);
@endphp
<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text"><i class="fa fa-globe"></i></span>
    </div>
    <input {{ isset($r[1]->required) ? 'required':'' }}
        type="url"
        value="{{ $urlVal }}"
        class="form-control"
        pattern="^https?://[^\s]+$"
        title="URL wajib diawali dengan http:// atau https:// dan tanpa spasi"
        oninput="this.value = this.value.replace(/\s+/g, '');"
        onblur="if(this.value.trim() !== '' && !this.value.startsWith('http://') && !this.value.startsWith('https://')) { this.value = 'https://' + this.value; }"
        name="{{_us($r[0])}}"
        placeholder="https://">
</div>
@if(!empty($r[1]->helper))
<small class="form-text text-muted">{{ $r[1]->helper }}</small>
@endif
