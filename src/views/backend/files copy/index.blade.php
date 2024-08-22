@extends('cms::backend.layout.app', ['title' => 'File Manager'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-folder"></i> File Manager
            </h3>
            <div class="pull-right btn-group">
                <button class="btn btn-primary btn-sm" onclick="$('.upload').click()"> <i class="fa fa-upload"></i> Upload Media</button>
                <form action="{{ route('media.upload') }}" method="POST" class="mediaupload" enctype="multipart/form-data">
                @csrf
                <input accept="{{ allow_mime() }}" type="file" onchange="if(confirm('Upload Media ?')){$('.mediaupload').submit()}" name="media" class="upload d-none">
            </form>
                <a href="{{route('panel.dashboard')}}" class="btn btn-danger btn-sm"> <i class="fa fa-undo" aria-hidden></i> Kembali</a>
            </div>
        </div>
        <div class="col-lg-12">

            <table class="display table table-hover table-bordered datatable" style="background:#f7f7f7;width:100%">
                <thead style="text-transform:uppercase;color:#444">
                    <tr>

                        <th style="width:5px;vertical-align: middle">No</th>
                        <th style="vertical-align: middle;" width="150px">Preview</th>
                        <th style="vertical-align: middle;">Name</th>
                        <th style="vertical-align: middle;">Ref</th>
                        <th style="vertical-align: middle;">Size</th>
                        <th style="vertical-align: middle;">Created</th>
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
                    url: "{{ route('files.data') }}",
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
                        data: 'preview',
                        name: 'preview',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'name',
                        name: 'name',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'ref',
                        name: 'ref',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'file_size',
                        name: 'file_size',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: false,
                        searchable: true
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
    <script>
        function preview(url){
            $('.preview').attr('src',url);
            $('#embedModal').modal('show');
        }
    </script>
    @include('cms::backend.layout.js')
    <div class="modal fade" id="embedModal" tabindex="-1" role="dialog" aria-labelledby="embedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="embedModalLabel">Preview</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body p-0">
                <embed class="preview" src=""  type="application/pdf" style="width:100%;height: 80vh;"></embed>
            </div>

          </div>
        </div>
      </div>
@endsection
