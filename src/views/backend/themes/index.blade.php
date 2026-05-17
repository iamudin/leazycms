@extends('cms::backend.layout.app', ['title' => 'Manajemen Tema'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-paint-brush" aria-hidden="true"></i> Manajemen Tema</h3>
            <div class="pull-right">
                <a href="{{ route('theme.create') }}" class="btn btn-primary btn-sm"> <i class="fa fa-plus" aria-hidden></i> Tambah Tema</a>
            </div>
        </div>
        <div class="col-lg-12">
            <table class="display table table-hover table-bordered datatable" style="background:#f7f7f7;width:100%;font-size:small">
                <thead style="text-transform:uppercase;color:#444">
                    <tr>
                        <th style="width:5px;vertical-align: middle">No</th>
                        <th style="vertical-align: middle">Nama Tema</th>
                        <th style="vertical-align: middle">Path</th>
                        <th style="vertical-align: middle">Git URL</th>
                        <th style="vertical-align: middle" width="10px">Tenants</th>
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
                    url: "{{ route('theme.datatable') }}",
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
                        data: 'path',
                        name: 'path',
                        searchable: true
                    },
                    {
                        data: 'git',
                        name: 'git',
                        searchable: true
                    },
                    {
                        data: 'tenants',
                        name: 'tenants',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
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

        function updateTheme(id) {
            swal({
                title: "Perbarui Tema?",
                text: "Sistem akan mendownload ulang file dari Git URL",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Iya, Perbarui!",
                cancelButtonText: "Batal",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: "{{ url(admin_path().'/theme') }}/" + id + "/update-git",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            swal("Berhasil!", response.success, "success");
                            $('.datatable').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            swal("Gagal!", "Terjadi kesalahan saat memperbarui tema", "error");
                        }
                    });
                }
            });
        }
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
    @endpush
    @include('cms::backend.layout.js')
@endsection
