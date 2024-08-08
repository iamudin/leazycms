@extends('cms::backend.layout.app', ['title' => 'Tags'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-hashtag aria-hidden="true"></i> Tags
            </h3>
            <div class="pull-right">

                <a href="{{route('panel.dashboard')}}" class="btn btn-outline-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
            </div>
        </div>
        <div class="col-lg-4">
            @include('cms::backend.layout.error')
            @include('cms::backend.tags.form')
        </div>
        <div class="col-lg-8">

            <table class="display table table-hover table-bordered datatable" style="background:#f7f7f7;width:100%">
                <thead style="text-transform:uppercase;color:#444">
                    <tr>

                        <th style="width:5px;vertical-align: middle">No</th>
                        <th style="vertical-align: middle;">Nama</th>
                        <th style="vertical-align: middle">Keterangan</th>
                        <th style="vertical-align: middle" width="10px">Post</th>
                        <th style="vertical-align: middle" width="10px">Hits</th>
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
                    url: "{{ route('tag') }}",
                    data: {
                        _token: "{{ csrf_token() }}"
                    }
                },
                lengthMenu: [10, 20],
                deferRender: true,
                columns: [{
                        className: 'text-center',
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'description',
                        name: 'description',
                        orderable: false,
                        searchable: true
                    },

                    {
                        data: 'posts_count',
                        name: 'posts_count',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'visited',
                        name: 'visited',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                responsive: true,

            });


        });
    </script>
    @push('styles')
        <link rel="stylesheet" type="text/css"
            href="https://cdn.datatables.net/rowreorder/1.4.1/css/rowReorder.dataTables.min.css">
        <link rel="stylesheet" type="text/css"
            href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    @endpush
    @push('scripts')
        <script type="text/javascript" src="{{ secure_asset('backend/js/plugins/jquery.dataTables.min.js') }}"></script>
        <script type="text/javascript" src="{{ secure_asset('backend/js/plugins/dataTables.bootstrap.min.js') }}"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js">
        </script>
        <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js">
        </script>
        <script type="text/javascript">
            $('#sampleTable').DataTable();
        </script>
    @endpush
    @include('cms::backend.layout.js')
@endsection
