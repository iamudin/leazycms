<select class="form-control form-control-sm soft-control" name="{{ _us($r[0]) }}[]">
    <option value="">-- Pilih --</option>
    @foreach($r[1] as $opt)
        <option {{ isset($l?->$k) && $l?->$k == $opt ? 'selected' : '' }} value="{{ $opt }}">{{ $opt }}</option>
    @endforeach
</select>
