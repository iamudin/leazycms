<li class="list-group-item py-1 px-3">
<small>Nama </small>
<h6>{{$info['name'] ?? null}}</h6>
</li>
<li class="list-group-item py-1 px-3">
    <small>Pembuat </small>
    <h6><a target="_blank" href="{{$info['url'] ?? '#'}}">{{$info['author'] ?? null}}</a></h6>
</li>
<li class="list-group-item py-1 px-3">
    <small>Versi </small>
    <h6>{{$info['version'] ?? null}}</h6>
</li>
@php $rem = latest_theme_version(); @endphp
@if(version_compare(ltrim($info['version'] ?? null, 'v'),ltrim($rem, 'v'),'<') && !Cache::has('enablededitortemplate'))
<li class="list-group-item p-0 m-0">
    <button onclick="location.href='{{url()->current()}}?act=updatetemplate'" class="btn btn-sm btn-outline-primary w-100"> <i class="fa fa-sync"></i> Update to {{$rem}} </button>
</li>
@endif