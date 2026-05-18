@extends('cms::backend.layout.app', ['title' => 'Manajemen Tenant'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-globe" aria-hidden="true"></i> Manajemen Tenant</h3>
            <div class="pull-right">
                <a href="{{ route('tenant.create') }}" class="btn btn-primary btn-sm"> <i class="fa fa-plus" aria-hidden></i> Tambah Tenant</a>
            </div>
        </div>
        <div class="col-lg-12">
            <table class="display table table-hover table-bordered datatable" style="background:#f7f7f7;width:100%;font-size:small">
                <thead style="text-transform:uppercase;color:#444">
                    <tr>
                        <th style="width:5px;vertical-align: middle">No</th>
                        <th style="vertical-align: middle">Nama Tenant</th>
                        <th style="vertical-align: middle">Domain</th>
                        <th style="vertical-align: middle">Theme</th>
                        <th style="vertical-align: middle" width="10px">Status</th>
                        <th style="vertical-align: middle" width="10px">Aksi</th>
                    </tr>
                </thead>
                <tbody style="background:#fff">
                </tbody>
            </table>
        </div>
    </div>
    <script type="text/javascript">
        window.addEventListener('DOMContentLoaded', function() {
            var table = $('.datatable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                aaSorting: [],
                ajax: {
                    method: "POST",
                    url: "{{ route('tenant.datatable') }}",
                    data: {_token:"{{csrf_token()}}"}
                },
                columns: [
                    {
                        className: 'text-center',
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        searchable: true
                    },
                    {
                        data: 'domain',
                        name: 'domain',
                        searchable: true
                    },
                    {
                        data: 'theme',
                        name: 'theme',
                        searchable: true
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });
    </script>
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
    @include('cms::backend.layout.js')
@endsection
