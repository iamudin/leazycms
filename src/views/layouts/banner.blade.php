@if($data)
<div style="margin-bottom:5px;">
<small style="cursor:pointer" title="Tulisan ini hanya bisa dilihat oleh admin yang sedang Aktif login">Banner {{ $banner }} <b style="color:red">[?]</b> </small>
</div>
@else
<div style="width:100%;border:2px dashed #222;vertical-align:center;text-align:center;background:#f5f5f5;margin:10px 0">
<h6 style="padding:50px 0;color:#bbb">Pasang Banner {{ $banner }} Disini</h6>
</div>
@endif
