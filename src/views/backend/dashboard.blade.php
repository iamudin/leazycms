@extends('cms::backend.layout.app', ['title' => 'Dashboard'])
@section('content')
  <div class="row">
  <div class="col-lg-12 mb-1">

      <h3 style="font-weight:normal;"> <i class="fa fa-dashboard"></i> Dashboard </h3>

  </div>
      <div class="col-lg-12">

    <div class="row">
      @foreach($type as $row)
            <div title="Klik untuk selengkapnya" class="pointer col-md-6 col-lg-3" onclick="location.href='{{Route::has($row->name) ? route($row->name) : ''}}'">
              <div class="widget-small danger coloured-icon"><i class="icon fa {{$row->icon}} fa-3x"></i>
                <div class="info pl-3">
                  <p class="mt-2 text-muted">{{$row->title}}</p>
                  <h2><b>{{$posts[$row->name] ?? '0'}}</b></h2>
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
 
  </div>
  <div class="col-lg-12 mt-3">
  @include('cms::backend.stats')
  </div>

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
