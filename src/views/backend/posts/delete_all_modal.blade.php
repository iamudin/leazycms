@if(in_array('delete', $module->route) && (auth()->user()->isAdmin() || !auth()->user()->hasRole(get_post_type(), 'delete', true)))
<div class="modal fade" id="modal-delete-all" tabindex="-1" role="dialog" aria-labelledby="modalDeleteAllLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
      <div class="modal-header bg-danger text-white py-2 px-3">
        <h5 class="modal-title font-weight-bold" id="modalDeleteAllLabel" style="font-size: 16px;">
          <i class="fas fa-exclamation-triangle mr-1"></i> Hapus Semua Data {{ $module->title }}
        </h5>
        <button type="button" id="delete-all-header-close" class="close text-white" data-dismiss="modal" aria-label="Close" style="outline: none;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-left p-3">
        <div id="delete-all-form-view">
          <div class="alert alert-warning py-2 px-3 mb-3" style="font-size: 13px;">
            <i class="fas fa-info-circle mr-1"></i> Perhatian: Tindakan ini akan menghapus data pada modul <strong>{{ $module->title }}</strong> secara massal.
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 13px;">Pilih Jenis Penghapusan:</label>
            <div class="custom-control custom-radio mb-2">
              <input type="radio" id="deleteTypeTrash" name="delete_all_type" class="custom-control-input" value="trash" checked>
              <label class="custom-control-label font-weight-normal text-dark" for="deleteTypeTrash" style="cursor: pointer;">
                <strong>Pindahkan ke Sampah (Soft Delete)</strong>
                <div class="text-muted" style="font-size: 12px;">Data dipindahkan ke kotak sampah dan masih dapat dipulihkan kapan saja.</div>
              </label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="deleteTypePermanent" name="delete_all_type" class="custom-control-input" value="permanent">
              <label class="custom-control-label font-weight-normal text-danger" for="deleteTypePermanent" style="cursor: pointer;">
                <strong>Hapus Permanen (Force Delete)</strong>
                <div class="text-muted" style="font-size: 12px;">Data dihapus selamanya dari database dan tidak dapat dikembalikan!</div>
              </label>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 13px;">Cakupan Data yang Dihapus:</label>
            <div class="custom-control custom-radio mb-2">
              <input type="radio" id="scopeAll" name="delete_all_scope" class="custom-control-input" value="all" checked>
              <label class="custom-control-label font-weight-normal text-dark" for="scopeAll" style="cursor: pointer;">
                <strong>Seluruh Data Modul Ini</strong>
                <div class="text-muted" style="font-size: 12px;">Menghapus semua record data {{ $module->title }}.</div>
              </label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="scopeFiltered" name="delete_all_scope" class="custom-control-input" value="filtered">
              <label class="custom-control-label font-weight-normal text-dark" for="scopeFiltered" style="cursor: pointer;">
                <strong>Hanya Sesuai Filter / Tab Aktif</strong>
                <div class="text-muted" style="font-size: 12px;">Menghapus hanya data yang saat ini aktif difilter (misal tab Sampah, kategori, atau pencarian).</div>
              </label>
            </div>
          </div>

          <div class="form-group mb-0 p-2 bg-light rounded border">
            <label class="font-weight-bold text-danger mb-1" for="confirmDeleteAllText" style="font-size: 13px;">
              Konfirmasi Keamanan:
            </label>
            <p class="text-muted mb-2" style="font-size: 12px;">Ketik kata <code>HAPUS</code> di bawah ini untuk mengaktifkan tombol:</p>
            <input type="text" id="confirmDeleteAllText" class="form-control form-control-sm text-uppercase font-weight-bold" placeholder="Ketik HAPUS di sini..." autocomplete="off">
          </div>
        </div>

        <div id="delete-all-progress-view" style="display: none;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-weight-bold text-dark" style="font-size: 13.5px;" id="delete-all-stage-text">Menyiapkan penghapusan...</span>
            <span class="badge badge-pill badge-danger font-weight-bold px-2 py-1" id="delete-all-pct-badge" style="font-size: 12px;">0%</span>
          </div>

          <div class="progress mb-2" style="height: 16px; border-radius: 10px; background: #e9ecef; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
            <div id="delete-all-progress-bar"
                 class="progress-bar progress-bar-striped progress-bar-animated bg-danger"
                 role="progressbar"
                 style="width: 0%; transition: width 0.25s ease;"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
            <div class="text-truncate mr-2" style="max-width: 75%;">
              <i class="fas fa-circle-notch fa-spin text-danger mr-1" id="delete-all-status-spinner"></i>
              <span id="delete-all-status-detail">Menghitung jumlah data...</span>
            </div>
            <span class="font-weight-bold text-nowrap" id="delete-all-counter">0 / 0</span>
          </div>

          <div class="p-2 rounded" id="delete-all-activity-log" style="max-height: 120px; overflow-y: auto; background: #f8fafc; border: 1px solid #eef2f6; font-size: 12px;">
            <div class="text-muted text-center py-2">Menunggu pemrosesan batch...</div>
          </div>
        </div>

        <div id="delete-all-done-view" style="display: none;" class="text-center py-3">
          <div class="mb-2">
            <i id="delete-all-done-icon" class="fas fa-check-circle text-success" style="font-size: 50px;"></i>
          </div>
          <h5 class="font-weight-bold text-dark mb-1" id="delete-all-done-title">Penghapusan Selesai</h5>
          <p class="text-muted small mb-0" id="delete-all-done-summary"></p>
        </div>
      </div>
      <div class="modal-footer bg-light py-2 px-3">
        <div id="delete-all-footer-form" class="w-100 d-flex justify-content-end" style="gap: 6px;">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
          <button type="button" id="btn-execute-delete-all" class="btn btn-danger btn-sm font-weight-bold" disabled>
            <i class="fas fa-trash-alt mr-1"></i> <span id="btn-delete-all-label">Hapus Semua Data</span>
          </button>
        </div>
        <div id="delete-all-footer-progress" class="w-100 d-none justify-content-between align-items-center">
          <small class="text-muted"><i class="fas fa-lock mr-1"></i> Jangan tutup halaman saat proses berjalan.</small>
          <button type="button" id="btn-abort-delete-all" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-stop mr-1"></i> Hentikan
          </button>
        </div>
        <div id="delete-all-footer-done" class="w-100 d-none justify-content-end">
          <button type="button" class="btn btn-success btn-sm px-3" data-dismiss="modal">
            <i class="fas fa-check mr-1"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
