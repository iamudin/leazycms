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
    <th style="width:20px">Status</th>
    <th style="max-width:20px">Aksi</th>

  </tr></thead>
  <tbody class="bg-white">

  </tbody>
  </table>
  </div>

</div>

<!-- Modal Reply -->
<div class="modal fade" id="replyModal" tabindex="-1" role="dialog" aria-labelledby="replyModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="replyModalLabel"><i class="fa fa-reply"></i> Balas Komentar</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="replyForm">
        <div class="modal-body">
          @csrf
          <input type="hidden" name="action" value="reply">
          <input type="hidden" id="replyParentId" name="parent_id" value="">
          <div class="form-group">
            <label for="replyContent">Balasan:</label>
            <textarea id="replyContent" name="content" rows="4" class="form-control" maxlength="500" placeholder="Tulis balasan..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" id="submitReply" class="btn btn-primary">Kirim</button>
        </div>
      </form>
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
            {data: 'sender', name: 'sender',orderable: false,},
            {data: 'content', name: 'content',orderable: false,},
            {data: 'reference', name: 'reference',orderable: false,},
            {data: 'created_at', name: 'created_at', orderable: true},
            {data: 'status', name: 'status',orderable: false,},
            {data: 'action', name: 'action'},
        ],
        responsive: true,
        /*    order: [
                [sort_col, 'desc']
            ]*/
    });

          });

          // Reply functions
          function openReplyModal(parentId) {
            document.getElementById('replyParentId').value = parentId;
            document.getElementById('replyContent').value = '';
            $('#replyModal').modal('show');
          }

          function toggleCommentStatus(commentId) {
            $.ajax({
              url: "{{ route('comments') }}",
              method: "POST",
              data: {
                _token: "{{ csrf_token() }}",
                action: 'toggle-status',
                comment_id: commentId
              },
              success: function() {
                $('.datatable').DataTable().ajax.reload();
              },
              error: function() {
                alert('Gagal mengubah status!');
              }
            });
          }

          document.getElementById('replyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var submitBtn = document.getElementById('submitReply');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            $.ajax({
              url: "{{ route('comments') }}",
              method: "POST",
              data: $(this).serialize(),
              success: function() {
                $('#replyModal').modal('hide');
                $('.datatable').DataTable().ajax.reload();
                alert('Balasan berhasil dikirim!');
              },
              error: function() {
                alert('Gagal mengirim balasan!');
              },
              complete: function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Kirim';
              }
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
