@extends('views::backend.layout.app',['title'=>'Tanggapan'])
@section('content')
<div class="row">
<div class="col-lg-12"><h3 style="font-weight:normal"><i class="fa fa-comments" aria-hidden="true"></i> Tanggapan</h3>

    <div class="table-responsive"> <table class="table datatable" style="font-size:small;width:100%">
  <thead><tr>
    <th width="2%">No</th>
    <th width="15%">Pengirim</th>
    <th width="15%">Isi</th>
    <th width="20%">Waktu</th>
    <th width="10%">Aksi</th>

  </tr></thead>
  <tbody>

  </tbody>
  </table>
  </div>

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
            {data: 'sender', name: 'sender'},
            {data: 'content', name: 'content'},
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
<script type="text/javascript" src="{{secure_asset('backend/js/plugins/jquery.dataTables.min.js')}}"></script>
     <script type="text/javascript" src="{{secure_asset('backend/js/plugins/dataTables.bootstrap.min.js')}}"></script>
     <script type="text/javascript" src="https://cdn.datatables.net/rowreorder/1.4.1/js/dataTables.rowReorder.min.js"></script>
     <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
     <script type="text/javascript">$('#sampleTable').DataTable();</script>
@endpush
</div>
</div>
@endsection
