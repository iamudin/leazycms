<li class="list-group-item py-1 px-3">
<small>Nama </small>
<h6>{{$info['name'] ?? 'tidak diketahui'}}</h6>
</li>
<li class="list-group-item py-1 px-3">
    <small>Pembuat </small>
    <h6>
        @if(($info['url'] ?? '#') !== '#')
            <a target="_blank" href="{{$info['url']}}">{{$info['author'] ?? 'tidak diketahui'}}</a>
        @else
            {{$info['author'] ?? 'tidak diketahui'}}
        @endif
    </h6>
</li>
<li class="list-group-item py-1 px-3">
    <small>Versi </small>
    <h6>{{$info['version'] ?? 'tidak diketahui'}}</h6>
</li>
@php $rem = latest_theme_version(); @endphp
@if(($info['version'] ?? null) && ($info['version'] ?? null) !== 'tidak diketahui' && version_compare(ltrim((string) $info['version'], 'v'),ltrim((string) $rem, 'v'),'<'))
<li class="list-group-item p-0 m-0">
    <button onclick="location.href='{{url()->current()}}?act=updatetemplate'" class="btn btn-sm btn-outline-primary w-100"> <i class="fa fa-sync"></i> Update to {{$rem}} </button>
</li>
@endif
