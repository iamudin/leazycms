@extends('cms::backend.layout.app', ['title' => 'Blocked IP'])
@section('content')
<div class="row">
    <div class="col-lg-12 mb-3">
        <h3 style="font-weight:normal;float:left"><i class="fa fa-shield-alt" aria-hidden="true"></i> Blocked IP</h3>
        <div class="pull-right">
            <a href="{{ route('panel.dashboard') }}" class="btn btn-outline-danger btn-sm"><i class="fa fa-undo" aria-hidden="true"></i> Kembali</a>
        </div>
    </div>
    <div class="col-lg-12">
        @include('cms::backend.layout.error')
        <table class="display table table-hover table-bordered datatable mt-2" style="background:#f7f7f7;width:100%">
            <thead style="text-transform:uppercase;color:#444">
            <tr>
                <th style="width:10px">No</th>
                <th>IP</th>
                <th>Lokasi</th>
                <th>Device</th>
                <th>Alasan</th>
                <th>Waktu Blokir</th>
                <th style="width:40px">Aksi</th>
            </tr>
            </thead>
            <tbody class="bg-white"></tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    window.addEventListener('DOMContentLoaded', function () {
        $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            aaSorting: [],
            ajax: {
                method: "POST",
                url: "{{ route('blocked-ip') }}",
                data: function (d) {
                    d._token = "{{ csrf_token() }}";
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'ip', name: 'ip', orderable: false, searchable: true},
                {data: 'location', name: 'location', orderable: false, searchable: true},
                {data: 'device_info', name: 'device_info', orderable: false, searchable: true},
                {data: 'reason_text', name: 'reason_text', orderable: false, searchable: true},
                {data: 'blocked_date', name: 'blocked_date', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
@endpush
@push('scripts')
@include('cms::backend.layout.js')
<script type="text/javascript" src="{{ url('backend/js/plugins/jquery.dataTables.min.js') }}"></script>
<script type="text/javascript" src="{{ url('backend/js/plugins/dataTables.bootstrap.min.js') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
@endpush
@endsection
