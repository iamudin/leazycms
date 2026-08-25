@extends('cms::backend.layout.app', ['title' => 'Manajemen Tenant'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-globe" aria-hidden="true"></i> Tenant</h3>
            <div class="pull-right">
                <button type="button" onclick="event.preventDefault(); openBrandConfig();" class="btn btn-info btn-sm"> <i class="fa fa-tag" aria-hidden="true"></i> Brand Master</button>
                <button type="button" onclick="event.preventDefault(); openCpanelConfig();" class="btn btn-dark btn-sm"> <i class="fa fa-cogs" aria-hidden="true"></i> API cPanel</button>
                <a href="{{ route('tenant.create') }}" class="btn btn-primary btn-sm"> <i class="fa fa-plus" aria-hidden="true"></i> Tambah Tenant</a>
            </div>
        </div>

        @if(isset($stats) && $stats->count() > 0)
        <div class="col-lg-12 mb-2">
            <div class="row">
                @php
                    $gradients = [
                        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        'linear-gradient(135deg, #ff0844 0%, #ffb199 100%)',
                        'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
                        'linear-gradient(135deg, #2F80ED 0%, #56CCF2 100%)',
                        'linear-gradient(135deg, #f2994a 0%, #f2c94c 100%)',
                        'linear-gradient(135deg, #ee0979 0%, #ff6a00 100%)',
                        'linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%)',
                    ];
                @endphp
                
                <!-- Widget Total Keseluruhan -->
                <div class="col-md-3 col-6 col-sm-6 mb-2" >
                    <div class="card border-0" onclick="filterCategory('')" style="cursor:pointer; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 8px; transition: transform 0.2s ease, box-shadow 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';">
                        <div class="card-body text-white p-2 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.5px; opacity: 0.9;"><i class="fa fa-server mr-1"></i> Semua Tenant</div>
                                <div class="font-weight-bold" style="font-size: 1.4rem; line-height: 1.2;">{{ $stats->sum('total') }} <span style="font-size: 0.7rem; font-weight: normal; opacity: 0.8;">Websites</span></div>
                            </div>
                            <div style="opacity: 0.3;">
                                <i class="fa fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($stats as $index => $stat)
                    <div class="col-md-3 col-6 col-sm-6 mb-2">
                        <div class="card border-0" onclick="filterCategory('{{ $stat->category }}')" style="cursor:pointer; background: {{ $gradients[$index % count($gradients)] }}; border-radius: 8px; transition: transform 0.2s ease, box-shadow 0.2s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';">
                            <div class="card-body text-white p-2 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase font-weight-bold" style="font-size: 0.7rem; letter-spacing: 0.5px; opacity: 0.9;"><i class="fa fa-folder-open-o mr-1"></i> {{ $stat->category }}</div>
                                    <div class="font-weight-bold" style="font-size: 1.4rem; line-height: 1.2;">{{ $stat->total }} <span style="font-size: 0.7rem; font-weight: normal; opacity: 0.8;">Websites</span></div>
                                </div>
                                <div style="opacity: 0.3;">
                                    <i class="fa fa-globe fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

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
                        searchable: true,
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
    <!-- Modal cPanel API Config -->
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

    <!-- Modal Brand Master Config -->
    <div class="modal fade" id="brandModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-tag"></i> Konfigurasi Brand Master (Watermark)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="brandModalBody">
                    <div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat data...</div>
                </div>
                <div class="modal-footer" id="brandModalFooter" style="display: none;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="saveBrandConfig()"><i class="fa fa-save"></i> Simpan Brand</button>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
    @include('cms::backend.layout.js')
    
    <script>
        function openBrandConfig() {
            $('#brandModalBody').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Memuat formulir brand...</div>');
            $('#brandModalFooter').hide();
            $('#brandModal').modal('show');

            $.ajax({
                url: '{{ route('tenant.brand.form') }}',
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#brandModalBody').html(response.html);
                        $('#brandModalFooter').show();
                        if (typeof window.initGlobalFilePickers === 'function') {
                            window.initGlobalFilePickers();
                        }
                    } else {
                        $('#brandModalBody').html('<div class="alert alert-danger">' + (response.message || 'Gagal memuat form.') + '</div>');
                    }
                },
                error: function() {
                    $('#brandModalBody').html('<div class="alert alert-danger">Gagal menghubungi server.</div>');
                }
            });
        }

        function removeBrandLogo() {
            $('#removeBrandLogoInput').val('1');
            $('#brandLogoPreviewWrapper').slideUp();
        }

        function saveBrandConfig() {
            var form = document.getElementById('brandConfigForm');
            if (!form) return;

            var formData = new FormData(form);
            var btn = $('#brandModal .btn-primary');
            var originalText = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            $.ajax({
                url: '{{ route('tenant.brand.save') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    btn.html(originalText).prop('disabled', false);
                    if (response.status === 'success') {
                        $('#brandModal').modal('hide');
                        swal('Berhasil!', response.message, 'success');
                    } else {
                        swal('Gagal!', response.message || 'Gagal menyimpan konfigurasi.', 'error');
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
        function filterCategory(category) {
            var table = $('.datatable').DataTable();
            $('.dataTables_filter input').val(category);
            table.search(category).draw();
        }
    </script>
    @endpush
@endsection
