@extends('cms::backend.layout.app',['title'=>'Tanggapan'])
@section('content')
<div class="row">
<div class="col-lg-12"><h3 style="font-weight:normal "><i class="fa fa-comments" aria-hidden="true"></i> Tanggapan</h3>

  <br>
    
    <table class="display table table-hover table-bordered datatable mt-5" style="background:#f7f7f7;width:100%">
  <thead style="text-transform:uppercase;color:#444">
<tr>
    <th style="max-width:10px">No</th>
    <th style="width:20px">Pengirim</th>
    <th style="width:400px">Isi</th>
    <th style="width:20px">Halaman</th>
    <th style="width:20px">Waktu</th>
    <th style="max-width:20px">Aksi</th>

  </tr></thead>
  <tbody class="bg-white">

  </tbody>
  </table>
  </div>

</div>
<script type="text/javascript">
          window.addEventListener('DOMContentLoaded', function() {
        var table = $('.datatable').DataTable({
        processing: true,
        serverSide: true,

        ajax: {
                method: "POST",
                url: "{{ route('comments') }}",
                data: function (d){
                 d._token = "{{csrf_token()}}";
            }
          },
        columns: [
            {
                    className: 'text-center',
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
            {data: 'sender', name: 'sender',orderable: false,},
            {data: 'content', name: 'content',orderable: false,},
            {data: 'reference', name: 'reference',orderable: false,},
            {data: 'created_at', name: 'created_at', orderable: true},
            {data: 'action', name: 'action'},
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
@include('cms::backend.layout.js')
<script type="text/javascript" src="{{url('backend/js/plugins/jquery.dataTables.min.js')}}"></script>
     <script type="text/javascript" src="{{url('backend/js/plugins/dataTables.bootstrap.min.js')}}"></script>
     <script type="text/javascript" src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
     <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
@endpush
</div>
</div>
@endsection
