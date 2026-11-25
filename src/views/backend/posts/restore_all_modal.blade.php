@if(in_array('delete', $module->route) && (auth()->user()->isAdmin() || !auth()->user()->hasRole(get_post_type(), 'delete', true)))
<div class="modal fade" id="modal-restore-all" tabindex="-1" role="dialog" aria-labelledby="modalRestoreAllLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow" style="border-radius: 14px; overflow: hidden;">
      <div class="modal-header bg-info text-white py-2 px-3">
        <h5 class="modal-title font-weight-bold" id="modalRestoreAllLabel" style="font-size: 16px;">
          <i class="fas fa-trash-restore mr-1"></i> Pulihkan Semua Data {{ $module->title }}
        </h5>
        <button type="button" id="restore-all-header-close" class="close text-white" data-dismiss="modal" aria-label="Close" style="outline: none;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-left p-3">
        <div id="restore-all-form-view">
          <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 13px;">
            <i class="fas fa-info-circle mr-1"></i> Data modul <strong>{{ $module->title }}</strong> yang berada di kotak sampah akan dipulihkan kembali.
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 13px;">Status Setelah Dipulihkan:</label>
            <div class="custom-control custom-radio mb-2">
              <input type="radio" id="restoreStatusDraft" name="restore_all_status" class="custom-control-input" value="draft" checked>
              <label class="custom-control-label font-weight-normal text-dark" for="restoreStatusDraft" style="cursor: pointer;">
                <strong>Jadikan Draft (Disarankan)</strong>
                <div class="text-muted" style="font-size: 12px;">Data dipulihkan dengan status draft agar dapat ditinjau sebelum dipublikasikan.</div>
              </label>
            </div>
            <div class="custom-control custom-radio mb-2">
              <input type="radio" id="restoreStatusPublish" name="restore_all_status" class="custom-control-input" value="publish">
              <label class="custom-control-label font-weight-normal text-dark" for="restoreStatusPublish" style="cursor: pointer;">
                <strong>Publikasikan Langsung</strong>
                <div class="text-muted" style="font-size: 12px;">Data dipulihkan dan langsung berstatus publish.</div>
              </label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="restoreStatusKeep" name="restore_all_status" class="custom-control-input" value="keep">
              <label class="custom-control-label font-weight-normal text-dark" for="restoreStatusKeep" style="cursor: pointer;">
                <strong>Pertahankan Status Sebelumnya</strong>
                <div class="text-muted" style="font-size: 12px;">Hanya menghapus penanda sampah tanpa mengubah status publish/draft sebelumnya.</div>
              </label>
            </div>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark mb-1 d-block" style="font-size: 13px;">Cakupan Data yang Dipulihkan:</label>
            <div class="custom-control custom-radio mb-2">
              <input type="radio" id="restoreScopeAll" name="restore_all_scope" class="custom-control-input" value="all" checked>
              <label class="custom-control-label font-weight-normal text-dark" for="restoreScopeAll" style="cursor: pointer;">
                <strong>Seluruh Data di Kotak Sampah</strong>
                <div class="text-muted" style="font-size: 12px;">Memulihkan semua data trashed pada modul {{ $module->title }}.</div>
              </label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" id="restoreScopeFiltered" name="restore_all_scope" class="custom-control-input" value="filtered">
              <label class="custom-control-label font-weight-normal text-dark" for="restoreScopeFiltered" style="cursor: pointer;">
                <strong>Hanya Sesuai Filter Aktif</strong>
                <div class="text-muted" style="font-size: 12px;">Memulihkan hanya data trashed sesuai kategori, penulis, tanggal, atau pencarian aktif.</div>
              </label>
            </div>
          </div>
        </div>

        <div id="restore-all-progress-view" style="display: none;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-weight-bold text-dark" style="font-size: 13.5px;" id="restore-all-stage-text">Menyiapkan pemulihan...</span>
            <span class="badge badge-pill badge-info font-weight-bold px-2 py-1" id="restore-all-pct-badge" style="font-size: 12px;">0%</span>
          </div>

          <div class="progress mb-2" style="height: 16px; border-radius: 10px; background: #e9ecef; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
            <div id="restore-all-progress-bar"
                 class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                 role="progressbar"
                 style="width: 0%; transition: width 0.25s ease;"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
            <div class="text-truncate mr-2" style="max-width: 75%;">
              <i class="fas fa-circle-notch fa-spin text-info mr-1" id="restore-all-status-spinner"></i>
              <span id="restore-all-status-detail">Menghitung data di kotak sampah...</span>
            </div>
            <span class="font-weight-bold text-nowrap" id="restore-all-counter">0 / 0</span>
          </div>

          <div class="p-2 rounded" id="restore-all-activity-log" style="max-height: 120px; overflow-y: auto; background: #f8fafc; border: 1px solid #eef2f6; font-size: 12px;">
            <div class="text-muted text-center py-2">Menunggu pemrosesan batch...</div>
          </div>
        </div>

        <div id="restore-all-done-view" style="display: none;" class="text-center py-3">
          <div class="mb-2">
            <i id="restore-all-done-icon" class="fas fa-check-circle text-success" style="font-size: 50px;"></i>
          </div>
          <h5 class="font-weight-bold text-dark mb-1" id="restore-all-done-title">Pemulihan Selesai</h5>
          <p class="text-muted small mb-0" id="restore-all-done-summary"></p>
        </div>
      </div>
      <div class="modal-footer bg-light py-2 px-3">
        <div id="restore-all-footer-form" class="w-100 d-flex justify-content-end" style="gap: 6px;">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
          <button type="button" id="btn-execute-restore-all" class="btn btn-info btn-sm font-weight-bold">
            <i class="fas fa-trash-restore mr-1"></i> Pulihkan Semua Data
          </button>
        </div>
        <div id="restore-all-footer-progress" class="w-100 d-none justify-content-between align-items-center">
          <small class="text-muted"><i class="fas fa-lock mr-1"></i> Jangan tutup halaman saat proses berjalan.</small>
          <button type="button" id="btn-abort-restore-all" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-stop mr-1"></i> Hentikan
          </button>
        </div>
        <div id="restore-all-footer-done" class="w-100 d-none justify-content-end">
          <button type="button" class="btn btn-success btn-sm px-3" data-dismiss="modal">
            <i class="fas fa-check mr-1"></i> Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
