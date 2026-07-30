@extends('cms::backend.layout.app', ['title' => 'Manajemen Tenant'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-globe" aria-hidden="true"></i> Manajemen Tenant</h3>
            <div class="pull-right">
                <button type="button" onclick="event.preventDefault(); openCpanelConfig();" class="btn btn-dark btn-sm"> <i class="fa fa-cogs" aria-hidden="true"></i> API cPanel</button>
                <a href="{{ route('tenant.create') }}" class="btn btn-primary btn-sm"> <i class="fa fa-plus" aria-hidden="true"></i> Tambah Tenant</a>
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
                        <th style="vertical-align: middle">Kategori</th>
                        <th style="vertical-align: middle">Resource</th>
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
                        data: 'category',
                        name: 'category',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'resource',
                        name: 'resource',
                        className: 'text-center',
                        searchable: false,
                        orderable: false
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
     <div class="modal fade" id="cpanelModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-cogs"></i> Konfigurasi API cPanel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="cpanelModalBody">
                    <!-- Form akan dimuat melalui AJAX -->
                </div>
                <div class="modal-footer" id="cpanelModalFooter" style="display: none;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveCpanelConfig()">Simpan Konfigurasi</button>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
    @include('cms::backend.layout.js')
    <!-- Modal cPanel API Config -->
   
    <script>
        function openCpanelConfig() {
            $('#cpanelModalBody').html(`
                <div id="cpanelAuthSection">
                    <p>Masukkan password admin Anda untuk mengonfigurasi API cPanel:</p>
                    <form onsubmit="event.preventDefault(); authCpanelConfig();" autocomplete="off">
                        <!-- Fake hidden input to trap aggressive browser autofill (preventing it from filling Datatables search) -->
                        <input type="text" name="fake_username" style="display:none;" aria-hidden="true" autocomplete="username">
                        
                        <input type="password" id="cpanelAdminPassword" class="form-control mb-3" placeholder="Password" autocomplete="new-password">
                        <button type="submit" class="btn btn-primary" id="btnCpanelAuth">Otentikasi</button>
                    </form>
                </div>
            `);
            $('#cpanelModalFooter').hide();
            $('#cpanelModal').modal('show');
        }

        function authCpanelConfig() {
            var pwd = $('#cpanelAdminPassword').val();
            if (!pwd) {
                swal("Peringatan", "Password tidak boleh kosong!", "warning");
                return;
            }
            $('#btnCpanelAuth').html('<i class="fa fa-spinner fa-spin"></i> Memeriksa...').prop('disabled', true);
            $.ajax({
                url: '{{ route('tenant.cpanel.form') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    password: pwd
                },
                success: function(response) {
                    if (response.status === 'error') {
                        $('#btnCpanelAuth').html('Otentikasi').prop('disabled', false);
                        swal("Gagal", response.message, "error");
                    } else {
                        $('#cpanelModalBody').html(response.html);
                        $('#cpanelModalFooter').show();
                    }
                },
                error: function(xhr) {
                    $('#btnCpanelAuth').html('Otentikasi').prop('disabled', false);
                    swal("Error", "Gagal menghubungi server.", "error");
                }
            });
        }

        function saveCpanelConfig() {
            var btn = $('#cpanelModal .btn-primary');
            var originalText = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);
            
            $.ajax({
                url: '{{ route('tenant.cpanel.save') }}',
                type: 'POST',
                data: $('#cpanelConfigForm').serialize() + '&_token={{ csrf_token() }}',
                success: function(response) {
                    btn.html(originalText).prop('disabled', false);
                    if (response.status === 'success') {
                        $('#cpanelModal').modal('hide');
                        swal('Berhasil!', response.message, 'success');
                    } else {
                        swal('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    btn.html(originalText).prop('disabled', false);
                    var msg = 'Terjadi kesalahan sistem.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    swal('Error!', msg, 'error');
                }
            });
        }
    </script>
    @endpush
@endsection
