<div class="modal fade" id="modal-bulk-action" tabindex="-1" role="dialog" aria-labelledby="modalBulkActionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
      <div id="bulk-action-modal-header" class="modal-header bg-primary text-white py-2 px-3">
        <h5 class="modal-title font-weight-bold" id="modalBulkActionLabel" style="font-size: 16px;">
          <i id="bulk-action-header-icon" class="fas fa-tasks mr-1"></i>
          <span id="bulk-action-modal-title">Memproses Tindakan Massal</span>
        </h5>
        <button type="button" id="bulk-action-header-close" class="close text-white" data-dismiss="modal" aria-label="Close" style="outline: none;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-left p-3">
        <div id="bulk-action-progress-view">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-weight-bold text-dark" style="font-size: 13.5px;" id="bulk-action-stage-text">Menyiapkan pemrosesan...</span>
            <span class="badge badge-pill badge-primary font-weight-bold px-2 py-1" id="bulk-action-pct-badge" style="font-size: 12px;">0%</span>
          </div>

          <div class="progress mb-2" style="height: 16px; border-radius: 10px; background: #e9ecef; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
            <div id="bulk-action-progress-bar"
                 class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                 role="progressbar"
                 style="width: 0%; transition: width 0.25s ease;"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
            <div class="text-truncate mr-2" style="max-width: 75%;">
              <i class="fas fa-circle-notch fa-spin text-primary mr-1" id="bulk-action-status-spinner"></i>
              <span id="bulk-action-status-detail">Memulai proses...</span>
            </div>
            <span class="font-weight-bold text-nowrap" id="bulk-action-counter">0 / 0</span>
          </div>

          <div class="p-2 rounded" id="bulk-action-activity-log" style="max-height: 120px; overflow-y: auto; background: #f8fafc; border: 1px solid #eef2f6; font-size: 12px;">
            <div class="text-muted text-center py-2">Menunggu pemrosesan batch...</div>
          </div>
        </div>

        <div id="bulk-action-done-view" style="display: none;" class="text-center py-3">
          <div class="mb-2">
            <i id="bulk-action-done-icon" class="fas fa-check-circle text-success" style="font-size: 50px;"></i>
          </div>
          <h5 class="font-weight-bold text-dark mb-1" id="bulk-action-done-title">Proses Selesai</h5>
          <p class="text-muted small mb-0" id="bulk-action-done-summary"></p>
        </div>
      </div>
      <div class="modal-footer bg-light py-2 px-3">
        <div id="bulk-action-footer-progress" class="w-100 d-flex justify-content-between align-items-center">
          <small class="text-muted"><i class="fas fa-lock mr-1"></i> Jangan tutup halaman saat proses berjalan.</small>
          <button type="button" id="btn-abort-bulk-action" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-stop mr-1"></i> Hentikan
          </button>
        </div>
        <div id="bulk-action-footer-done" class="w-100 d-none justify-content-end">
          <button type="button" class="btn btn-success btn-sm px-3" data-dismiss="modal">
            <i class="fas fa-check mr-1"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
