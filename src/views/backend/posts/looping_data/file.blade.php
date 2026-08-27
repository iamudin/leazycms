<div class="media-input-wrapper" style="{{ (!empty($l?->$k) && media_exists($l?->$k)) ? 'display:none;' : '' }}">
    <span class="input-{{ _us($r[0]) }}-{{ $y }}">
        <input title="Format: {{ allowed_ext() }}" data-toggle="tooltip" type="file" style="max-width: 110px;" accept="{{ allow_mime() }}" class="form-control-sm compress-image" name="{{ _us($r[0]) }}[]"/>
    </span>
    <input type="hidden" class="oldfile-{{ _us($r[0]) }}-{{ $y }}" name="{{ _us($r[0]) }}[]" value="{{ $l?->$k ?? 'nofile' }}">
</div>
@if(!empty($l?->$k) && media_exists($l?->$k))
    <div class="media-preview-wrapper align-items-center" style="display: inline-flex;">
        <a target="_blank" class="file-{{ _us($r[0]) }}-{{ $y }} btn btn-xs btn-outline-info btn-view-media py-0 px-2 mr-1" data-ext="{{ str(media_extension($l?->$k))->lower() }}" data-media="{{ media($l?->$k)->url() }}" style="font-size: 11px; border-radius: 4px;">
            <i class="fa fa-file mr-1"></i> {{ strtoupper(get_ext($l?->$k)) }}
        </a>
        @if(!Route::is($post->type . '.show'))
            <i class="fa fa-trash pointer text-danger edit-{{ _us($r[0]) }}-{{ $y }} btn-remove-media" data-field="{{ _us($r[0]) }}[]" style="cursor: pointer; display: none; font-size: 12px;" title="Hapus Berkas"></i>
        @endif
    </div>
@endif
