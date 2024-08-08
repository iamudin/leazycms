
<span @if(!empty($l->$k) && media_exists($post,$l->$k)) style="display:none"@endif class="input-{{_us($r[0])}}-{{$y}}">

  <input title="Format: {{allowed_ext()}}" data-toggle="tooltip" onchange="readFile(this);"  placeholder="Masukkan {{$r[0]}}" type="file" style="width:74px;" accept={{allow_mime() }} class="form-control-sm" name="{{_us($r[0])}}[]"/>
</span>
   <input type="hidden" class="oldfile-{{_us($r[0])}}-{{$y}}"  name="{{_us($r[0])}}[]" value="{{$l->$k ?? 'nofile'}}">
@if(!empty($l->$k) &&  media_exists($post,$l->$k))
<a target="_blank" href="{{asset($l->$k)}}" class="file-{{_us($r[0])}}-{{$y}} btn btn-sm btn-outline-info"> {{strtoupper(get_ext($l->$k))}} </a>
<a class="fa fa-trash pointer text-danger edit-{{_us($r[0])}}-{{$y}}" style="display: none" onclick="media_destroy('{{$l->$k}}')" aria-hidden></a>

@endif
