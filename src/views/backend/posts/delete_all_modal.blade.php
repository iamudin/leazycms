@if(in_array('delete', $module->route) && (auth()->user()->isAdmin() || !auth()->user()->hasRole(get_post_type(), 'delete', true)))
<div class="modal fade" id="modal-delete-all" tabindex="-1" role="dialog" aria-labelledby="modalDeleteAllLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white py-2 px-3">
        <h5 class="modal-title font-weight-bold" id="modalDeleteAllLabel" style="font-size: 16px;">
          <i class="fas fa-exclamation-triangle mr-1"></i> Hapus Semua Data {{ $module->title }}
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="outline: none;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-left p-3">
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
      <div class="modal-footer bg-light py-2 px-3">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
        <button type="button" id="btn-execute-delete-all" class="btn btn-danger btn-sm font-weight-bold" disabled>
          <i class="fas fa-trash-alt mr-1"></i> <span id="btn-delete-all-label">Hapus Semua Data</span>
        </button>
      </div>
    </div>
  </div>
</div>
@endif
