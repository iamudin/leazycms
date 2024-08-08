@extends('views::backend.layout.app',['title'=>'Tanggapan'])
@section('content')
<div class="row">
<div class="col-lg-12"><h3 style="font-weight:normal"><i class="fa fa-comments" aria-hidden="true"></i> Tanggapan</h3>
  <br>
<table class="table table-hover table-bordered" style="font-size:small" id="sampleTable">
<thead>
  <tr style="background:#f7f7f7">
    <th width="2%">No</th>
    <th width="10%">Waktu</th>
    <th>Pengirim</th>
    <th>Isi Tanggapan</th>
    <th width="40px">Status</th>
  </tr>
</thead>
<tbody style="background:#fff">

@foreach($comments as $k=>$row)
<tr>
  <td>{{$k+1}}</td>
  <td><small class="badge">{{$row->created_at}}</small></td>
  <td width="20%"> <i class="fa fa-user" aria-hidden></i> {{$row->name}}<br>
    <i class="fa fa-link" aria-hidden></i> {{$row->link ?? '-'}}<br>
      <i class="fa fa-at" aria-hidden></i> {{$row->email ?? '-'}}
  </td>
  <td>{!!$row->content!!}<br>

<b class="text-muted">Pada : </b><br> <h6><a target="_blank" href="{{url($row->post->url)}}">{{$row->post->title}}</a></h6>
  </td>
  <td title="Klik Untuk Mengganti status Diterima atau Draft" class="pointer" onclick="$('.status').val('{{$row->id}}');$('.form').submit()">
 @if($row->status==1) <span class="badge badge-success"> Publish </span> @else <span class="badge badge-warning">Draft</span> @endif

              </td>
</tr>
@endforeach
</tbody>

</table>
</div>
</div>
<form action="{{URL::full()}}" class="form" method="post">
@csrf
<input type="hidden" class="status" name="status" value="">
</form>
@if(request()->post)
<script type="text/javascript">
$(function () {

$('input[type=search]').val('{{request()->post}}').keyup();
});
</script>
@endif
@push('styles')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

@endpush
@push('scripts')
<script type="text/javascript" src="{{secure_asset('backend/js/plugins/jquery.dataTables.min.js')}}"></script>
     <script type="text/javascript" src="{{secure_asset('backend/js/plugins/dataTables.bootstrap.min.js')}}"></script>
     <script type="text/javascript" src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
     <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
     <script type="text/javascript">$('#sampleTable').DataTable();</script>
@endpush
@endsection
