@extends('cms::backend.layout.app',['title'=>'Dashboard'])
@section('content')
<!-- <link href="https://coderthemes.com/adminox/layouts/vertical/assets/css/icons.min.css" rel="stylesheet" type="text/css" /> -->
<div class="row">
<div class="col-lg-12 mb-3"><h3 style="font-weight:normal;"> <i class="fa fa-tachometer"></i> Dashboard </h3>

</div>
    <div class="col-lg-12">
  <div class="row">
    @foreach($type as $row)
          <div title="Klik untuk selengkapnya" class="pointer col-md-6 col-lg-3 col-6 " onclick="location.href='{{route($row->name)}}'">
            <div class="widget-small danger coloured-icon"><i class="icon fa {{$row->icon}} fa-3x"></i>
              <div class="info pl-3">
                <p class="mt-2 text-muted">{{$row->title}}</p>
                <h2><b>{{$posts->where('type',$row->name)->count()}}</b></h2>
              </div>
            </div>
          </div>
          @endforeach
        </div>
</div>
<div class="col-lg-8 mb-3">
  <div class="card" style="padding:15px">
  <h4 for="" style="margin-bottom:20px"><i class="fa fa-pencil" aria-hidden="true"></i> 5 Terakhir Dibuat</h4>
  <div class="table-responsive">
    <table class="table" style="font-size:small">
  <thead><tr>
    <th width="150px">Waktu</th>
    <th width="100px">Modul</th>
    <th>Judul</th>
    <th  width="100px">Author</th>
    <th  width="50px">Status</th>
  </tr></thead>
  <tbody>
    @foreach($latest as $row)
    <tr>
        <td><code>{{ $row->created_at->diffForHumans() }}</code></td>
        <td>{{ str($row->type)->headline() }}</td>
        <td><span class="text-primary">{{$row->title }}</span></td>
        <td>{{ $row->user->name }}</td>
        <td>{!! $row->status == 'draft' ? '<badge class="badge badge-warning">Draft</badge>' : '<badge class="badge badge-success">Publish</badge>' !!}</td>
    </tr>
    @endforeach
  </tbody>
  </table>
  </div>
</div>
</div>
<div class="col-lg-4">
  <div class="card" style="padding:15px">
  <h4 for="" style="margin-bottom:20px"> <i class="fa fa-bar-chart" aria-hidden="true"></i> Grafik Pengunjung Mingguan</h4>
  @include('cms::backend.visitor-chart')
</div>
</div>
<div class="col-lg-12 mt-3">
  <div class="card" style="padding:15px">
  <h4 for=""  style="margin-bottom:20px"> <i class="fa fa-info" aria-hidden="true"></i> Rincian Trafik <span class="pull-right"><small>Pilih </small> <input max="{{date('Y-m-d')}}"  onchange="$('.datatable').DataTable().ajax.reload();" style="width:120px" type="date" class="form-control-sm " id="timevisit" ></span></h4>

  <div class="table-responsive"> <table class="table datatable" style="font-size:small;width:100%">
  <thead><tr>
    <th width="18%">Time</th>
    <th width="15%">Page</th>
    <th width="15%">Reference</th>
    <th width="20%">IP</th>
    <th width="10%">Browser</th>
    <th width="10%">Device</th>
    <th width="10%">OS</th>
  </tr></thead>
  <tbody>

  </tbody>
  </table>
  </div>

</div>
</div>
<script type="text/javascript">
          window.addEventListener('DOMContentLoaded', function() {
      /*  var sort_col = $('.datatable').find("th:contains('Time')")[0].cellIndex;*/
        var table = $('.datatable').DataTable({
        processing: true,
        serverSide: true,

        ajax: {
                method: "POST",
                url: "{{ route('visitor.data') }}",
                data: function (d){
                 d._token = "{{csrf_token()}}";
                 d.timevisit = $("#timevisit").val();
            }
          },
        columns: [

            {data: 'created_at', name: 'created_at', orderable: true},
            {data: 'page', name: 'page'},
            {data: 'reference', name: 'reference'},
            {data: 'ip_location', name: 'ip_location'},
            {data: 'browser', name: 'browser'},
            {data: 'device', name: 'device'},
            {data: 'os', name: 'os'},
        ],
        responsive: true,
        /*    order: [
                [sort_col, 'desc']
            ]*/
    });

          });
    </script>

</div>
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
