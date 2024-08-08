@extends('admin.layout.app',['title'=>'Pengguna'])
@section('content')
<div class="row">
<div class="col-lg-12"><h3 style="font-weight:normal">Scraper Web </h3>
  <br>
@php $page = request('page') ? 'page='.request('page') :''; 
$data = json_decode(file_get_contents('https://diskominfotik.bengkaliskab.go.id/gerbang/get_recent_posts?'.$page),true);

@endphp
<div style="max-height:90px;width:100%;overflow:auto">
@for($a=1; $a<=$data['count_total'] / 10; $a++)
<a href="{{url(admin_path().'/scraper?page='.$a)}}" @if($a==request('page') ?? 0 ) class="btn btn-primary" @endif style="display:inline" >{{$a}}</a>
@endfor
</div>
<br>
<br>
<table class="table table-hover table-bordered" >
  <thead  style="background:#f7f7f7">
    <tr>
    <th style="width:15px">No</th>
    <th>Foto</th>
    <th>Judul</th>
    <th>Isi</th>
  </tr>
</thead>
  <tbody style="background:#fff;">
@foreach($data['posts'] as $k=>$row)
<tr>
  <td align="center">{{$k+1}}</td>
  <td align="center"><a download="gambar.jpg" href="{{$row['gambar']}}" ><img src="{{$row['gambar']}}" height="60" class="img-fluid rounded"></a></td>
  <td ><b><code>{{$row['tanggal']}} {{$row['waktu']}}</code></b><br>{{$row['judul_berita']}} <br><br> <button class="copy btn btn-warning btn-sm" data-copy="{{$row['judul_berita']}}">COPY</button></td>
  <td style="max-width:400px"><div style="max-height:200px;overflow:auto">{!!str_replace('/gambar/userfiles',
    'https://diskominfotik.bengkaliskab.go.id/gambar/userfiles',$row['isi'])!!}</div> <br> <button class="copy btn btn-warning btn-sm" data-copy="{{str_replace('/gambar/userfiles',
    'https://diskominfotik.bengkaliskab.go.id/gambar/userfiles',$row['isi'])}}">COPY</button></td>
@endforeach
  </tbody>
</table>

</div>
</div>

@endsection
