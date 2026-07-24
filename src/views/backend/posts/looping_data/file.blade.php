<div class="media-input-wrapper" style="{{ (!empty($l?->$k) && media_exists($l?->$k)) ? 'display:none;' : '' }}">
<span class="input-{{_us($r[0])}}-{{$y}}">
<input title="Format: {{allowed_ext()}}" data-toggle="tooltip"   type="file" style="width:74px;" accept={{allow_mime() }} class="form-control-sm compress-image" name="{{_us($r[0])}}[]"/>
</span>
<input type="hidden" class="oldfile-{{_us($r[0])}}-{{$y}}"  name="{{_us($r[0])}}[]" value="{{$l?->$k ?? 'nofile'}}">
</div>
@if(!empty($l?->$k) && media_exists($l?->$k))
    <div class="media-preview-wrapper">
    <a target="_blank"  class="file-{{_us($r[0])}}-{{$y}} btn btn-sm btn-outline-info btn-view-media" data-ext="{{ str(media_extension($l?->$k))->lower() }}" data-media="{{$l?->$k}} "> {{strtoupper(get_ext($l?->$k))}} </a>
    @if(!Route::is($post->type . '.show'))
    <a class="fa fa-trash pointer text-danger edit-{{_us($r[0])}}-{{$y}} btn-remove-media" style="display: none" onclick="$(this).closest('.media-preview-wrapper').hide().prev('.media-input-wrapper').show().find('input[type=hidden]').val('nofile')" aria-hidden></a>
    @endif
    </div>
@endif
