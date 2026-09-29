@extends('cms::backend.layout.app', ['title' => 'Manajemen Tenant'])
@section('content')
    <div class="row">
        <div class="col-lg-12 mb-3">
            <h3 style="font-weight:normal;float:left"><i class="fa fa-globe" aria-hidden="true"></i> Tenant</h3>
            <div class="pull-right">
                <button type="button" onclick="event.preventDefault(); openTenantNoticeModal();" class="btn btn-secondary btn-sm text-white" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border: none;"> <i class="fa fa-bell" aria-hidden="true"></i> Popup Pengumuman</button>
                <button type="button" onclick="event.preventDefault(); openAdsModal();" class="btn btn-warning btn-sm text-dark font-weight-bold"> <i class="fa fa-bullhorn" aria-hidden="true"></i> Kelola Iklan</button>
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

    <!-- Modal Ads Master Config -->
    <div class="modal fade" id="adsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-bullhorn"></i> Pengaturan Iklan Induk (Global Ads)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row mb-3 align-items-center">
                        <label class="col-sm-4 col-form-label font-weight-bold">Sisipkan Setelah Paragraf ke:</label>
                        <div class="col-sm-3">
                            <select id="ad_paragraph_select" class="form-control form-control-sm">
                                <option value="1">Paragraf 1</option>
                                <option value="2" selected>Paragraf 2</option>
                                <option value="3">Paragraf 3</option>
                                <option value="4">Paragraf 4</option>
                            </select>
                        </div>
                        <div class="col-sm-5 text-right">
                            <button type="button" class="btn btn-success btn-sm" onclick="addNewAdRow()"><i class="fa fa-plus"></i> Tambah Iklan</button>
                        </div>
                    </div>

                        <table class="table table-bordered table-sm" id="adsTable" style="font-size: 13px;">
                            <thead class="thead-light">
                                <tr>
                                    <th width="18%">Judul / Label</th>
                                    <th width="20%">Kategori / Posisi</th>
                                    <th width="28%">URL Gambar (Image Src)</th>
                                    <th width="20%">Link Tujuan (URL)</th>
                                    <th width="8%" class="text-center">Status</th>
                                    <th width="6%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="adsTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnSaveAds" onclick="saveAdsConfig()"><i class="fa fa-save"></i> Simpan Pengaturan Iklan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tenant Notice Config (Popup Pengumuman Dinamis) -->
    <div class="modal fade" id="tenantNoticeConfigModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #ffffff;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa fa-bell fa-lg mr-2"></i>
                        <div>
                            <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 16px;">Pengaturan Popup Pengumuman Tenant</h5>
                            <small class="text-white-50" style="font-size: 11px;">Tampilkan popup imbauan/pemberitahuan saat admin tenant membuka /dashboard</small>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.8; text-shadow: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                    <div id="noticeConfigLoading" class="text-center py-4 text-muted">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2 mb-0">Memuat konfigurasi pengumuman...</p>
                    </div>

                    <form id="tenantNoticeForm" style="display: none;" onsubmit="event.preventDefault(); saveTenantNotice();">
                        <!-- Switch Status Aktif -->
                        <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="font-weight-bold mb-0 text-dark" style="font-size: 14px; cursor: pointer;" for="notice_enabled">
                                        <i class="fa fa-power-off text-primary mr-1"></i> Status Popup Pengumuman
                                    </label>
                                    <small class="text-muted d-block mt-1">Aktifkan untuk memunculkan modal popup imbauan ini di halaman <code>/dashboard</code> admin tenant.</small>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="notice_enabled" name="enabled" value="1">
                                    <label class="custom-control-label font-weight-bold" for="notice_enabled" id="notice_enabled_label" style="cursor: pointer;">Nonaktif</label>
                                </div>
                            </div>
                        </div>

                        <!-- Baris: Tipe / Varian Modal & Ikon -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold mb-1" style="font-size: 13px;">
                                    Tipe / Tema Visual <span class="text-danger">*</span>
                                </label>
                                <select id="notice_type" name="type" class="form-control form-control-sm" onchange="onNoticeTypeChange(this.value)">
                                    <option value="warning">Warning / Imbauan (Amber/Kuning)</option>
                                    <option value="danger">Peringatan Kritis (Merah)</option>
                                    <option value="info">Informasi / Panduan (Biru)</option>
                                    <option value="success">Pembaruan Sukses (Hijau)</option>
                                    <option value="primary">Pengumuman Resmi (Indigo)</option>
                                </select>
                                <small class="text-muted">Menentukan skema warna header, badge, dan tombol aksi.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold mb-1" style="font-size: 13px;">
                                    Ikon FontAwesome <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white" id="notice_icon_preview"><i class="fa fa-database"></i></span>
                                    </div>
                                    <input type="text" id="notice_icon" name="icon" class="form-control form-control-sm" placeholder="fa-database" value="fa-database" oninput="updateNoticeIconPreview(this.value)">
                                </div>
                                <div class="mt-1" style="font-size: 11px;">
                                    <span class="text-muted">Pilihan cepat:</span>
                                    <a href="javascript:void(0)" onclick="setNoticeIcon('fa-database')" class="badge badge-light border ml-1"><i class="fa fa-database"></i> Backup</a>
                                    <a href="javascript:void(0)" onclick="setNoticeIcon('fa-exclamation-triangle')" class="badge badge-light border ml-1"><i class="fa fa-exclamation-triangle"></i> Warning</a>
                                    <a href="javascript:void(0)" onclick="setNoticeIcon('fa-shield')" class="badge badge-light border ml-1"><i class="fa fa-shield"></i> Security</a>
                                    <a href="javascript:void(0)" onclick="setNoticeIcon('fa-bullhorn')" class="badge badge-light border ml-1"><i class="fa fa-bullhorn"></i> Pengumuman</a>
                                    <a href="javascript:void(0)" onclick="setNoticeIcon('fa-info-circle')" class="badge badge-light border ml-1"><i class="fa fa-info-circle"></i> Info</a>
                                </div>
                            </div>
                        </div>

                        <!-- Judul Pengumuman -->
                        <div class="form-group mb-3">
                            <label class="font-weight-bold mb-1" style="font-size: 13px;">
                                Judul Pengumuman / Imbauan <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="notice_title" name="title" class="form-control form-control-sm" placeholder="Contoh: Imbauan Penting: Lakukan Backup Data Secara Rutin" required>
                        </div>

                        <!-- Isi Pengumuman -->
                        <div class="form-group mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="font-weight-bold mb-0" style="font-size: 13px;">
                                    Isi Pesan / Pemberitahuan (Mendukung HTML) <span class="text-danger">*</span>
                                </label>
                                <div>
                                    <button type="button" class="btn btn-xs btn-outline-primary" onclick="insertBackupTemplate()" title="Gunakan template standar imbauan backup rutin">
                                        <i class="fa fa-magic mr-1"></i> Sisipkan Contoh Template Backup Rutin
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Simple format bar -->
                            <div class="btn-toolbar mb-1" role="toolbar" style="gap: 4px;">
                                <div class="btn-group btn-group-sm mr-1">
                                    <button type="button" class="btn btn-light btn-xs border" onclick="wrapNoticeTag('strong')" title="Bold"><i class="fa fa-bold"></i></button>
                                    <button type="button" class="btn btn-light btn-xs border" onclick="wrapNoticeTag('em')" title="Italic"><i class="fa fa-italic"></i></button>
                                    <button type="button" class="btn btn-light btn-xs border" onclick="wrapNoticeTag('u')" title="Underline"><i class="fa fa-underline"></i></button>
                                </div>
                                <div class="btn-group btn-group-sm mr-1">
                                    <button type="button" class="btn btn-light btn-xs border" onclick="insertNoticeList()" title="Bullet List"><i class="fa fa-list-ul"></i> List</button>
                                    <button type="button" class="btn btn-light btn-xs border" onclick="insertNoticeAlert()" title="Alert Box"><i class="fa fa-exclamation-circle"></i> Kotak Peringatan</button>
                                    <button type="button" class="btn btn-light btn-xs border" onclick="insertNoticeLink()" title="Link"><i class="fa fa-link"></i> Link</button>
                                </div>
                            </div>

                            <textarea id="notice_content" name="content" class="form-control form-control-sm" rows="7" style="font-size: 13px; line-height: 1.5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;" placeholder="Tuliskan isi pengumuman atau imbauan di sini..."></textarea>
                            <small class="text-muted">Gunakan paragraf <code>&lt;p&gt;</code>, daftar poin <code>&lt;ul&gt;&lt;li&gt;</code>, tautan <code>&lt;a&gt;</code>, atau teks tebal <code>&lt;strong&gt;</code>.</small>
                        </div>

                        <!-- Tombol Aksi Tambahan (Opsional) -->
                        <div class="card mb-3 border" style="background: #fafafa; border-radius: 8px;">
                            <div class="card-header py-2 px-3 bg-light font-weight-bold" style="font-size: 13px;">
                                <i class="fa fa-external-link mr-1"></i> Tombol Tindakan / Aksi Tambahan (Opsional)
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-5 mb-2">
                                        <label class="font-weight-normal mb-1 small">Label Tombol</label>
                                        <input type="text" id="notice_action_btn_text" name="action_btn_text" class="form-control form-control-sm" placeholder="Misal: Buka Menu Backup">
                                    </div>
                                    <div class="col-md-5 mb-2">
                                        <label class="font-weight-normal mb-1 small">URL Tujuan</label>
                                        <input type="text" id="notice_action_btn_url" name="action_btn_url" class="form-control form-control-sm" placeholder="Misal: /admin/backup atau https://...">
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="font-weight-normal mb-1 small">Target</label>
                                        <select id="notice_action_btn_target" name="action_btn_target" class="form-control form-control-sm">
                                            <option value="_blank">Tab Baru</option>
                                            <option value="_self">Tab Saat Ini</option>
                                        </select>
                                    </div>
                                </div>
                                <small class="text-muted">Biarkan label tombol kosong jika hanya ingin menampilkan tombol "Tutup / Saya Mengerti".</small>
                            </div>
                        </div>

                        <!-- Baris: Teks Tombol Tutup & Frekuensi Muncul -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold mb-1" style="font-size: 13px;">
                                    Teks Tombol Tutup
                                </label>
                                <input type="text" id="notice_close_btn_text" name="close_btn_text" class="form-control form-control-sm" value="Saya Mengerti" placeholder="Saya Mengerti">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold mb-1" style="font-size: 13px;">
                                    Frekuensi Tampil di Tenant
                                </label>
                                <select id="notice_frequency" name="frequency" class="form-control form-control-sm">
                                    <option value="session">Sekali Per Sesi Login (Rekomendasi)</option>
                                    <option value="daily">Sekali Per Hari (Daily)</option>
                                    <option value="always">Setiap Kali Membuka Dashboard</option>
                                </select>
                                <small class="text-muted">Admin tenant juga dapat mencentang "Jangan tampilkan lagi hari ini".</small>
                            </div>
                        </div>

                        <!-- Target Penerima Tenant -->
                        <div class="form-group mb-2">
                            <label class="font-weight-bold mb-1" style="font-size: 13px;">
                                Target Tenant Penerima
                            </label>
                            <div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="notice_target_all" name="target" value="all" class="custom-control-input" checked onchange="toggleNoticeTarget(this.value)">
                                    <label class="custom-control-label" for="notice_target_all">Semua Tenant (Global Broadcast)</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="notice_target_selected" name="target" value="selected" class="custom-control-input" onchange="toggleNoticeTarget(this.value)">
                                    <label class="custom-control-label" for="notice_target_selected">Pilih Tenant Tertentu</label>
                                </div>
                            </div>
                            
                            <div id="notice_tenant_selector_wrapper" class="mt-2 p-2 border rounded bg-white" style="display: none; max-height: 150px; overflow-y: auto;">
                                <div id="notice_tenant_checkboxes">
                                    <!-- Rendered dynamically -->
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                    <div class="d-flex align-items-center gap-2" style="gap: 8px;">
                        <button type="button" class="btn btn-info btn-sm text-white" onclick="previewTenantNotice()">
                            <i class="fa fa-eye mr-1"></i> Pratinjau (Live Preview)
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" id="btnSaveTenantNotice" onclick="saveTenantNotice()">
                            <i class="fa fa-save mr-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal Template -->
    @include('cms::backend.tenants.notice-popup', ['isPreview' => true])

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

        // ==========================================
        // ADS MASTER CONFIGURATION (JSON CRUD)
        // ==========================================
        let adsData = [];

        function openAdsModal() {
            $('#adsTableBody').html('<tr><td colspan="5" class="text-center py-3"><i class="fa fa-spinner fa-spin"></i> Memuat data iklan...</td></tr>');
            $('#adsModal').modal('show');

            $.ajax({
                url: '{{ route('tenant.ads.form') }}',
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        adsData = res.ads || [];
                        $('#ad_paragraph_select').val(res.paragraph || 2);
                        renderAdsTable();
                    } else {
                        $('#adsTableBody').html('<tr><td colspan="5" class="text-center text-danger py-3">Gagal memuat data iklan.</td></tr>');
                    }
                },
                error: function() {
                    $('#adsTableBody').html('<tr><td colspan="5" class="text-center text-danger py-3">Gagal menghubungi server.</td></tr>');
                }
            });
        }

        function renderAdsTable() {
            let html = '';
            if (adsData.length === 0) {
                html = '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada iklan. Klik tombol <strong>+ Tambah Iklan</strong> di atas.</td></tr>';
            } else {
                adsData.forEach((ad, index) => {
                    let isChecked = (ad.status === 1 || ad.status === '1' || ad.status === true || ad.status === 'active' || ad.status === 'on') ? 'checked' : '';
                    let cat = ad.category || 'in_article';
                    html += `
                    <tr data-index="${index}">
                        <td>
                            <input type="text" class="form-control form-control-sm ad-title" value="${escapeHtml(ad.title || '')}" placeholder="Contoh: Sponsor Promo">
                        </td>
                        <td>
                            <select class="form-control form-control-sm ad-category">
                                <option value="in_article" ${cat === 'in_article' ? 'selected' : ''}>Artikel Konten</option>
                                <option value="in_feed" ${cat === 'in_feed' ? 'selected' : ''}>Di Antara List / Index</option>
                                <option value="widget_calendar" ${cat === 'widget_calendar' ? 'selected' : ''}>Footer Widget Kalender Arsip</option>
                                <option value="all" ${cat === 'all' ? 'selected' : ''}>Semua Posisi</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm ad-image" value="${escapeHtml(ad.image || '')}" placeholder="https://.../banner.jpg atau /media/...">
                        </td>
                        <td>
                            <input type="url" class="form-control form-control-sm ad-link" value="${escapeHtml(ad.link || '')}" placeholder="https://domain.com/promo">
                        </td>
                        <td class="text-center align-middle">
                            <label class="mb-0 font-weight-normal" style="cursor:pointer;">
                                <input type="checkbox" class="ad-status" ${isChecked}>
                            </label>
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-danger btn-sm" title="Hapus Iklan" onclick="removeAdRow(${index})"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>`;
                });
            }
            $('#adsTableBody').html(html);
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#039;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function addNewAdRow() {
            // Simpan perubahan form yang sedang diedit
            syncCurrentAdsFromDOM();
            adsData.push({ title: '', category: 'in_article', image: '', link: '', status: 1 });
            renderAdsTable();
        }

        function removeAdRow(index) {
            syncCurrentAdsFromDOM();
            adsData.splice(index, 1);
            renderAdsTable();
        }

        function syncCurrentAdsFromDOM() {
            let currentList = [];
            $('#adsTableBody tr').each(function() {
                let row = $(this);
                let titleInput = row.find('.ad-title');
                if (titleInput.length) {
                    let title = titleInput.val();
                    let category = row.find('.ad-category').val() || 'in_article';
                    let image = row.find('.ad-image').val();
                    let link  = row.find('.ad-link').val();
                    let status = row.find('.ad-status').is(':checked') ? 1 : 0;
                    currentList.push({ title, category, image, link, status });
                }
            });
            if (currentList.length > 0) {
                adsData = currentList;
            }
        }

        function saveAdsConfig() {
            syncCurrentAdsFromDOM();

            let btn = $('#btnSaveAds');
            let originalText = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            let paragraph = $('#ad_paragraph_select').val();

            $.ajax({
                url: '{{ route('tenant.ads.save') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ads: adsData,
                    paragraph: paragraph
                },
                success: function(res) {
                    btn.html(originalText).prop('disabled', false);
                    if (res.status === 'success') {
                        $('#adsModal').modal('hide');
                        swal('Berhasil!', res.message, 'success');
                    } else {
                        swal('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
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

        // ==========================================
        // TENANT NOTICE CONFIGURATION (POPUP MODAL)
        // ==========================================
        let tenantNoticeData = null;
        let tenantListCache = [];

        function openTenantNoticeModal() {
            $('#tenantNoticeConfigModal').modal('show');
            $('#noticeConfigLoading').show();
            $('#tenantNoticeForm').hide();

            $.ajax({
                url: '{{ route('tenant.notice.form') }}',
                type: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        tenantNoticeData = res.notice || {};
                        tenantListCache = res.tenants || [];
                        populateNoticeForm(tenantNoticeData, tenantListCache);
                        $('#noticeConfigLoading').hide();
                        $('#tenantNoticeForm').show();
                    } else {
                        $('#noticeConfigLoading').html('<div class="alert alert-danger">Gagal memuat data pengumuman.</div>');
                    }
                },
                error: function() {
                    $('#noticeConfigLoading').html('<div class="alert alert-danger">Gagal menghubungi server.</div>');
                }
            });
        }

        function populateNoticeForm(notice, tenants) {
            let isEnabled = (notice.enabled == 1 || notice.enabled === true || notice.enabled === '1');
            $('#notice_enabled').prop('checked', isEnabled);
            $('#notice_enabled_label').text(isEnabled ? 'Aktif' : 'Nonaktif');

            $('#notice_type').val(notice.type || 'warning');
            $('#notice_icon').val(notice.icon || 'fa-database');
            updateNoticeIconPreview(notice.icon || 'fa-database');

            $('#notice_title').val(notice.title || '');
            $('#notice_content').val(notice.content || '');

            $('#notice_action_btn_text').val(notice.action_btn_text || '');
            $('#notice_action_btn_url').val(notice.action_btn_url || '');
            $('#notice_action_btn_target').val(notice.action_btn_target || '_blank');
            $('#notice_close_btn_text').val(notice.close_btn_text || 'Saya Mengerti');
            $('#notice_frequency').val(notice.frequency || 'session');

            let target = notice.target || 'all';
            if (target === 'selected') {
                $('#notice_target_selected').prop('checked', true);
            } else {
                $('#notice_target_all').prop('checked', true);
            }
            toggleNoticeTarget(target);

            // Render tenant checkboxes
            let selectedTenants = Array.isArray(notice.target_tenants) ? notice.target_tenants.map(Number) : [];
            let chkHtml = '';
            if (tenants && tenants.length > 0) {
                tenants.forEach(t => {
                    let checked = selectedTenants.includes(t.id) ? 'checked' : '';
                    chkHtml += `
                    <div class="custom-control custom-checkbox custom-control-inline mr-3 mb-1">
                        <input type="checkbox" class="custom-control-input notice-tenant-item" id="chk_tenant_${t.id}" value="${t.id}" ${checked}>
                        <label class="custom-control-label small" for="chk_tenant_${t.id}">
                            <strong>${escapeHtml(t.name)}</strong> <span class="text-muted">(${escapeHtml(t.domain)})</span>
                        </label>
                    </div>`;
                });
            } else {
                chkHtml = '<div class="text-muted small">Tidak ada data tenant.</div>';
            }
            $('#notice_tenant_checkboxes').html(chkHtml);
        }

        $('#notice_enabled').on('change', function() {
            $('#notice_enabled_label').text(this.checked ? 'Aktif' : 'Nonaktif');
        });

        function onNoticeTypeChange(type) {
            let defaultIcons = {
                'warning': 'fa-database',
                'danger': 'fa-shield',
                'info': 'fa-info-circle',
                'success': 'fa-check-circle',
                'primary': 'fa-bullhorn'
            };
            if (!$('#notice_icon').val() || $('#notice_icon').val().startsWith('fa-')) {
                setNoticeIcon(defaultIcons[type] || 'fa-bell');
            }
        }

        function setNoticeIcon(icon) {
            $('#notice_icon').val(icon);
            updateNoticeIconPreview(icon);
        }

        function updateNoticeIconPreview(icon) {
            let cleanIcon = (icon || '').trim();
            if (!cleanIcon.startsWith('fa-') && !cleanIcon.startsWith('fa ')) {
                cleanIcon = 'fa-' + cleanIcon;
            }
            if (!cleanIcon.includes('fa ')) {
                cleanIcon = 'fa ' + cleanIcon;
            }
            $('#notice_icon_preview').html(`<i class="${cleanIcon}"></i>`);
        }

        function toggleNoticeTarget(val) {
            if (val === 'selected') {
                $('#notice_tenant_selector_wrapper').slideDown(200);
            } else {
                $('#notice_tenant_selector_wrapper').slideUp(200);
            }
        }

        function insertBackupTemplate() {
            const template = `<p>Yth. Administrator Website Tenant,</p>
<p>Demi menjaga keamanan, integritas, dan kelangsungan data website Anda, kami mengimbau seluruh pengelola untuk <strong>melakukan backup data secara rutin</strong> (database dan berkas media penting) secara berkala.</p>
<ul>
    <li>Simpan salinan berkas cadangan (backup) di penyimpanan yang aman (Google Drive / Komputer lokal).</li>
    <li>Lakukan pemeriksaan rutin terhadap konten dan akun pengguna pada website Anda.</li>
    <li>Hubungi Administrator Pusat jika mengalami kendala teknis atau memiliki pertanyaan terkait sistem.</li>
</ul>
<p class="mb-0 text-muted small"><i class="fa fa-info-circle"></i> Terima kasih atas perhatian dan kerjasamanya.</p>`;
            $('#notice_content').val(template);
        }

        function wrapNoticeTag(tag) {
            let el = document.getElementById('notice_content');
            let start = el.selectionStart;
            let end = el.selectionEnd;
            let text = el.value;
            let sel = text.substring(start, end) || 'teks';
            let replacement = `<${tag}>${sel}</${tag}>`;
            el.value = text.substring(0, start) + replacement + text.substring(end);
            el.focus();
            el.setSelectionRange(start + tag.length + 2, start + tag.length + 2 + sel.length);
        }

        function insertNoticeList() {
            let el = document.getElementById('notice_content');
            let start = el.selectionStart;
            let text = el.value;
            let snippet = "\n<ul>\n    <li>Poin imbauan 1</li>\n    <li>Poin imbauan 2</li>\n</ul>\n";
            el.value = text.substring(0, start) + snippet + text.substring(start);
            el.focus();
        }

        function insertNoticeAlert() {
            let el = document.getElementById('notice_content');
            let start = el.selectionStart;
            let text = el.value;
            let snippet = '\n<div class="alert alert-warning p-2 small mb-2"><i class="fa fa-exclamation-triangle mr-1"></i> <strong>Catatan:</strong> Tulis catatan penting di sini.</div>\n';
            el.value = text.substring(0, start) + snippet + text.substring(start);
            el.focus();
        }

        function insertNoticeLink() {
            let url = prompt("Masukkan URL Link tujuan:", "https://");
            if (url) {
                let el = document.getElementById('notice_content');
                let start = el.selectionStart;
                let end = el.selectionEnd;
                let text = el.value;
                let sel = text.substring(start, end) || "Klik tautan ini";
                let replacement = `<a href="${url}" target="_blank">${sel}</a>`;
                el.value = text.substring(0, start) + replacement + text.substring(end);
                el.focus();
            }
        }

        function previewTenantNotice() {
            let title = $('#notice_title').val() || 'Judul Pengumuman Sistem';
            let content = $('#notice_content').val() || '<p>Isi pengumuman belum dimasukkan.</p>';
            let type = $('#notice_type').val() || 'warning';
            let icon = $('#notice_icon').val() || 'fa-database';
            let closeBtnText = $('#notice_close_btn_text').val() || 'Saya Mengerti';
            let actionBtnText = $('#notice_action_btn_text').val();
            let actionBtnUrl = $('#notice_action_btn_url').val();
            let actionBtnTarget = $('#notice_action_btn_target').val() || '_blank';

            const themes = {
                'warning': {
                    gradient: 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                    badge: 'Pemberitahuan Penting',
                    btn: 'background: #d97706; border-color: #b45309; color: #fff;'
                },
                'danger': {
                    gradient: 'linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)',
                    badge: 'Peringatan Sistem',
                    btn: 'background: #dc2626; border-color: #b91c1c; color: #fff;'
                },
                'info': {
                    gradient: 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                    badge: 'Informasi Tenant',
                    btn: 'background: #0284c7; border-color: #0369a1; color: #fff;'
                },
                'success': {
                    gradient: 'linear-gradient(135deg, #10b981 0%, #047857 100%)',
                    badge: 'Pengumuman Resmi',
                    btn: 'background: #059669; border-color: #047857; color: #fff;'
                },
                'primary': {
                    gradient: 'linear-gradient(135deg, #4f46e5 0%, #3730a3 100%)',
                    badge: 'Pesan Administrator Pusat',
                    btn: 'background: #4f46e5; border-color: #3730a3; color: #fff;'
                }
            };

            let curTheme = themes[type] || themes['warning'];

            let cleanIcon = (icon || '').trim();
            if (!cleanIcon.startsWith('fa-') && !cleanIcon.startsWith('fa ')) {
                cleanIcon = 'fa-' + cleanIcon;
            }
            if (!cleanIcon.includes('fa ')) {
                cleanIcon = 'fa ' + cleanIcon;
            }

            $('#tenantNoticeHeader').css('background', curTheme.gradient);
            $('#tenantNoticeBadgeText').text(curTheme.badge);
            $('#tenantNoticeIcon').attr('class', cleanIcon);
            $('#tenantNoticeTitle').text(title);
            $('#tenantNoticeBody').html(content);
            $('#tenantNoticeCloseBtnText').text(closeBtnText);

            if (actionBtnText && actionBtnUrl) {
                $('#tenantNoticeActionBtn').removeClass('d-none').attr('href', actionBtnUrl).attr('target', actionBtnTarget).attr('style', 'border-radius: 8px; font-size: 13px; font-weight: 600; padding: 7px 18px; ' + curTheme.btn);
                $('#tenantNoticeActionBtnText').text(actionBtnText);
            } else {
                $('#tenantNoticeActionBtn').addClass('d-none');
            }

            $('#tenantNoticeDashboardModal').modal({
                backdrop: 'static',
                keyboard: true
            });
        }

        function saveTenantNotice() {
            let title = $('#notice_title').val().trim();
            let content = $('#notice_content').val().trim();

            if (!title) {
                swal('Perhatian', 'Judul pengumuman tidak boleh kosong!', 'warning');
                return;
            }

            let btn = $('#btnSaveTenantNotice');
            let originalText = btn.html();
            btn.html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true);

            let selectedTenants = [];
            $('.notice-tenant-item:checked').each(function() {
                selectedTenants.push($(this).val());
            });

            let formData = {
                _token: '{{ csrf_token() }}',
                enabled: $('#notice_enabled').is(':checked') ? 1 : 0,
                type: $('#notice_type').val(),
                icon: $('#notice_icon').val(),
                title: title,
                content: content,
                action_btn_text: $('#notice_action_btn_text').val(),
                action_btn_url: $('#notice_action_btn_url').val(),
                action_btn_target: $('#notice_action_btn_target').val(),
                close_btn_text: $('#notice_close_btn_text').val(),
                frequency: $('#notice_frequency').val(),
                target: $('input[name="target"]:checked').val() || 'all',
                target_tenants: selectedTenants
            };

            $.ajax({
                url: '{{ route('tenant.notice.save') }}',
                type: 'POST',
                data: formData,
                success: function(res) {
                    btn.html(originalText).prop('disabled', false);
                    if (res.status === 'success') {
                        $('#tenantNoticeConfigModal').modal('hide');
                        swal('Berhasil!', res.message, 'success');
                    } else {
                        swal('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
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
