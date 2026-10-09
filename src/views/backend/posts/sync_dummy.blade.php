<!-- Modal Sinkronisasi Data Dummy -->
<div class="modal fade" id="syncDummyModal" tabindex="-1" role="dialog" aria-labelledby="syncDummyModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.18); overflow: hidden;">
      
      <!-- Header -->
      <div class="modal-header border-0 pb-0 pt-4 px-4 align-items-center">
        <div class="d-flex align-items-center">
          <div class="mr-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(13, 110, 253, 0.1); color: #0d6efd;">
            <i class="fas fa-sync-alt fa-lg" id="sync-modal-header-icon"></i>
          </div>
          <div>
            <h5 class="modal-title font-weight-bold mb-0 text-dark" id="syncDummyModalLabel">Sinkronisasi Data Dummy</h5>
            <small class="text-muted">Modul: <span class="badge badge-primary font-weight-normal px-2 py-1">{{ $module->title ?? ucfirst(get_post_type()) }}</span></small>
          </div>
        </div>
        <button type="button" class="close text-muted" id="btn-sync-header-close" aria-label="Close" style="outline: none;">
          <span aria-hidden="true" style="font-size: 24px;">&times;</span>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body p-4">
        
        <!-- View 1: Konfirmasi Mulai -->
        <div id="sync-view-confirm">
          <div class="p-3 mb-3" style="background: #f8fafc; border-radius: 12px; border: 1px solid #eef2f6;">
            <p class="text-secondary mb-2" style="font-size: 13.5px; line-height: 1.6;">
              Data contoh (dummy) untuk modul <strong>{{ $module->title ?? ucfirst(get_post_type()) }}</strong> akan disiapkan dan ditambahkan ke database.
            </p>
            <div class="d-flex align-items-center text-muted small mt-2">
              <i class="fas fa-shield-alt text-success mr-2"></i>
              <span>Data yang sudah ada tidak akan diduplikasi atau dihapus.</span>
            </div>
            <div class="d-flex align-items-center text-muted small mt-1">
              <i class="fas fa-layer-group text-primary mr-2"></i>
              <span>Proses dapat dijalankan di latar belakang tanpa mengganggu pekerjaan Anda.</span>
            </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-light btn-sm px-3 mr-2 text-secondary" data-dismiss="modal" style="border: 1px solid #dee2e6;">Batal</button>
            <button type="button" class="btn btn-primary btn-sm px-4 shadow-sm" id="btn-start-sync">
              <i class="fas fa-play mr-1"></i> Mulai Sinkronisasi
            </button>
          </div>
        </div>

        <!-- View 2: Progress Bar & Status (Syncing) -->
        <div id="sync-view-progress" style="display: none;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="font-weight-bold text-dark" style="font-size: 13.5px;" id="sync-stage-text">Memproses Sinkronisasi...</span>
            <span class="badge badge-pill badge-primary font-weight-bold px-2 py-1" id="sync-pct-badge" style="font-size: 12px;">0%</span>
          </div>

          <!-- Progress Bar -->
          <div class="progress mb-2" style="height: 16px; border-radius: 10px; background: #e9ecef; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
            <div id="sync-progress-bar"
                 class="progress-bar progress-bar-striped progress-bar-animated"
                 role="progressbar"
                 style="width: 0%; background: linear-gradient(90deg, #0d6efd 0%, #00b4d8 50%, #20c997 100%); transition: width 0.3s ease;"
                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            </div>
          </div>

          <!-- Sub info -->
          <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
            <div class="text-truncate mr-2" style="max-width: 75%;">
              <i class="fas fa-circle-notch fa-spin text-primary mr-1" id="sync-status-spinner"></i>
              <span id="sync-status-detail">Menyiapkan data...</span>
            </div>
            <span class="font-weight-bold text-nowrap" id="sync-items-counter">0 / 0</span>
          </div>

          <!-- Mini Activity Log -->
          <div class="activity-log p-2 rounded" id="sync-activity-log" style="max-height: 110px; overflow-y: auto; background: #f8fafc; border: 1px solid #eef2f6; font-size: 12px; font-family: inherit;">
            <div class="text-muted text-center py-2" id="sync-log-empty">Menunggu pemrosesan batch...</div>
          </div>

          <!-- Actions -->
          <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-sync-minimize" title="Biarkan berjalan di pojok layar">
              <i class="fas fa-window-minimize mr-1"></i> Jalankan di Latar Belakang
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="btn-sync-abort">
              <i class="fas fa-stop mr-1"></i> Batalkan
            </button>
          </div>
        </div>

        <!-- View 3: Selesai (Completed) -->
        <div id="sync-view-completed" style="display: none;" class="text-center py-3">
          <div class="mb-3">
            <i class="fas fa-check-circle text-success" style="font-size: 56px;"></i>
          </div>
          <h5 class="font-weight-bold text-dark mb-1">Sinkronisasi Selesai!</h5>
          <p class="text-muted small mb-4" id="sync-completed-summary">Semua data dummy berhasil disinkronisasi ke dalam sistem.</p>
          <button type="button" class="btn btn-success btn-sm px-4 shadow-sm" data-dismiss="modal">
            <i class="fas fa-check mr-1"></i> Tutup & Tampilkan Data
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Floating Dock Widget (Ketika diminimalkan ke latar belakang) -->
<div id="sync-floating-dock" style="display: none; position: fixed; bottom: 25px; right: 25px; z-index: 9999; width: 330px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px); border-radius: 14px; box-shadow: 0 10px 35px rgba(0,0,0,0.18); border: 1px solid rgba(0,0,0,0.08); padding: 14px 16px; transition: all 0.3s ease;">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="font-weight-bold text-dark d-flex align-items-center" style="font-size: 13px;">
      <i class="fas fa-sync fa-spin text-primary mr-2" id="dock-status-icon"></i>
      <span class="text-truncate" style="max-width: 170px;" id="dock-title">Sinkronisasi {{ $module->title ?? ucfirst(get_post_type()) }}</span>
    </div>
    <div class="d-flex align-items-center">
      <span class="badge badge-primary font-weight-bold mr-2" id="dock-pct-badge" style="font-size: 11px;">0%</span>
      <button type="button" class="btn btn-link text-muted p-0 mr-2" id="btn-dock-expand" title="Perbesar ke Dialog">
        <i class="fas fa-expand-alt" style="font-size: 13px;"></i>
      </button>
      <button type="button" class="btn btn-link text-danger p-0" id="btn-dock-cancel" title="Batalkan">
        <i class="fas fa-times" style="font-size: 13px;"></i>
      </button>
    </div>
  </div>

  <!-- Mini Progress Bar -->
  <div class="progress" style="height: 6px; border-radius: 3px; background: #e9ecef;">
    <div id="dock-progress-bar"
         class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
         role="progressbar"
         style="width: 0%; transition: width 0.3s ease;">
    </div>
  </div>

  <div class="d-flex justify-content-between align-items-center text-muted mt-2" style="font-size: 11px;">
    <span class="text-truncate mr-2" id="dock-detail-text" style="max-width: 210px;">Menyiapkan...</span>
    <span class="font-weight-bold text-nowrap" id="dock-counter-text">0 / 0</span>
  </div>
</div>

<script>
$(function () {
  let syncState = {
    isSyncing: false,
    isCancelled: false,
    isMinimized: false,
    mode: 'direct',
    syncId: null,
    batches: [],
    currentBatch: 0,
    totalBatches: 0,
    totalItems: 0,
    processedCount: 0,
    retryCount: 0
  };

  const syncUrl = "{{ route(get_post_type() . '.sync_dummy') }}";
  const csrfToken = $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}";

  // Klik tombol utama "Sinkron Data Dummy"
  $(document).on('click', '.btn-sync-dummy', function () {
    if (syncState.isSyncing) {
      // Jika sedang berjalan di latar belakang, buka kembali modalnya
      if (syncState.isMinimized) {
        maximizeSyncModal();
      } else {
        $('#syncDummyModal').modal('show');
      }
      return;
    }

    // Reset view modal
    $('#sync-view-confirm').show();
    $('#sync-view-progress').hide();
    $('#sync-view-completed').hide();
    $('#btn-sync-header-close').show();
    $('#sync-activity-log').html('<div class="text-muted text-center py-2" id="sync-log-empty">Menunggu pemrosesan...</div>');
    $('#syncDummyModal').modal('show');
  });

  // Tombol Tutup Header
  $('#btn-sync-header-close').on('click', function () {
    if (syncState.isSyncing) {
      minimizeSyncToDock();
    } else {
      $('#syncDummyModal').modal('hide');
    }
  });

  // Mulai Sinkronisasi
  $('#btn-start-sync').on('click', function () {
    startSyncProcess();
  });

  // Minimalkan ke Latar Belakang (Dock Widget)
  $('#btn-sync-minimize').on('click', function () {
    minimizeSyncToDock();
  });

  // Perbesar dari Dock ke Modal
  $('#btn-dock-expand').on('click', function () {
    maximizeSyncModal();
  });

  // Batalkan Sinkronisasi
  $('#btn-sync-abort, #btn-dock-cancel').on('click', function () {
    swal({
      title: "Batalkan Sinkronisasi?",
      text: "Proses sinkronisasi akan dihentikan. Data yang sudah tersimpan tetap dipertahankan.",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Batalkan",
      cancelButtonText: "Lanjutkan",
      closeOnConfirm: true
    }, function (isConfirm) {
      if (isConfirm) {
        syncState.isCancelled = true;
        finishCancelledSync();
      }
    });
  });

  function minimizeSyncToDock() {
    syncState.isMinimized = true;
    $('#syncDummyModal').modal('hide');
    $('#sync-floating-dock').fadeIn(200);
  }

  function maximizeSyncModal() {
    syncState.isMinimized = false;
    $('#sync-floating-dock').fadeOut(150, function () {
      $('#syncDummyModal').modal('show');
    });
  }

  function startSyncProcess() {
    syncState.isSyncing = true;
    syncState.isCancelled = false;
    syncState.currentBatch = 0;
    syncState.processedCount = 0;
    syncState.retryCount = 0;

    $('#sync-view-confirm').hide();
    $('#sync-view-progress').show();
    $('#sync-view-completed').hide();
    $('#btn-sync-header-close').show();

    updateProgressUI(0, 'Menyiapkan daftar data dummy...', 0, 0);

    // Langkah 1: INIT - Ambil batch data yang perlu disinkronisasi
    $.ajax({
      url: syncUrl,
      type: 'POST',
      timeout: 60000,
      data: {
        _token: csrfToken,
        action: 'init'
      },
      success: function (res) {
        if (!res.success) {
          handleSyncError(res.message || 'Gagal memulai sinkronisasi.');
          return;
        }

        if (res.total_batches === 0 || !res.batches || res.batches.length === 0) {
          // Data sudah lengkap
          completeSync(0, 'Semua data dummy sudah tersinkronisasi.');
          return;
        }

        syncState.mode = res.mode || 'direct';
        syncState.syncId = res.sync_id || null;
        syncState.batches = res.batches;
        syncState.totalBatches = res.total_batches;
        syncState.totalItems = res.total_items;

        $('#sync-log-empty').remove();
        processNextBatch();
      },
      error: function (xhr) {
        handleSyncError('Gagal menghubungi server untuk inisialisasi.');
      }
    });
  }

  function processNextBatch() {
    if (syncState.isCancelled) {
      return;
    }

    if (syncState.currentBatch >= syncState.totalBatches) {
      // Semua batch selesai -> FINISH
      finalizeSync();
      return;
    }

    const batchIndex = syncState.currentBatch;
    const batchData = syncState.batches[batchIndex];
    let firstTitle = 'Data';
    let postData = {
      _token: csrfToken,
      action: 'batch'
    };

    if (syncState.mode === 'server') {
      firstTitle = (batchData && batchData.title) ? batchData.title : ('Batch #' + (batchIndex + 1));
      postData.mode = 'server';
      postData.sync_id = syncState.syncId;
      postData.batch_index = batchIndex;
    } else {
      firstTitle = (batchData && batchData[0]) ? batchData[0].title : 'Data';
      postData.items = batchData;
    }

    const currentPct = Math.round((batchIndex / syncState.totalBatches) * 100);
    updateProgressUI(currentPct, 'Menyimpan: ' + firstTitle + '...', syncState.processedCount, syncState.totalItems);

    $.ajax({
      url: syncUrl,
      type: 'POST',
      timeout: 60000,
      data: postData,
      success: function (res) {
        if (syncState.isCancelled) return;

        if (res.success) {
          syncState.retryCount = 0;
          const itemsProcessed = res.processed || (syncState.mode === 'server' ? (batchData.count || 1) : batchData.length);
          syncState.processedCount += itemsProcessed;
          syncState.currentBatch++;

          // Tampilkan log item (titles dari response atau batch)
          const titles = (res.titles && res.titles.length) ? res.titles : (syncState.mode === 'direct' ? batchData.map(i => i.title) : [firstTitle]);
          titles.forEach(t => {
            const logItem = $('<div class="text-truncate py-1 text-dark" style="border-bottom: 1px dashed #edf2f7;">' +
              '<i class="fas fa-check text-success mr-1"></i> ' + $('<div>').text(t).html() +
            '</div>');
            $('#sync-activity-log').prepend(logItem);
          });

          // Batasi elemen log di DOM maksimal 50 agar browser tidak lambat saat ribuan data
          const $logChildren = $('#sync-activity-log').children();
          if ($logChildren.length > 50) {
            $logChildren.slice(50).remove();
          }

          const nextPct = Math.round((syncState.currentBatch / syncState.totalBatches) * 100);
          updateProgressUI(nextPct, 'Tersimpan: ' + firstTitle, syncState.processedCount, syncState.totalItems);

          // Lanjutkan ke batch berikutnya dengan jeda 80ms
          setTimeout(processNextBatch, 80);
        } else {
          if (syncState.retryCount < 3) {
            syncState.retryCount++;
            updateProgressUI(currentPct, 'Mencoba ulang batch (' + syncState.retryCount + '/3)...', syncState.processedCount, syncState.totalItems);
            setTimeout(processNextBatch, 1500);
          } else {
            handleSyncError(res.message || 'Terjadi kesalahan saat menyimpan batch.');
          }
        }
      },
      error: function (xhr, textStatus) {
        if (syncState.isCancelled) return;

        // Auto-retry hingga 3 kali jika ada gangguan koneksi/timeout sesaat
        if (syncState.retryCount < 3) {
          syncState.retryCount++;
          const retryMsg = 'Koneksi terganggu, mencoba ulang batch (' + syncState.retryCount + '/3)...';
          updateProgressUI(currentPct, retryMsg, syncState.processedCount, syncState.totalItems);
          setTimeout(processNextBatch, 2000);
          return;
        }

        let errDetail = 'Terjadi gangguan jaringan atau timeout saat memproses data.';
        if (xhr.status === 504 || xhr.status === 502) {
          errDetail = 'Server mengalami timeout (504/502). Data yang tersimpan tetap aman.';
        } else if (xhr.status === 419) {
          errDetail = 'Sesi telah kedaluwarsa (CSRF token expired). Silakan refresh halaman dan klik Sinkronisasi kembali.';
        } else if (xhr.status === 500) {
          errDetail = 'Server mengalami kendala internal (500). ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '');
        }

        handleSyncError(errDetail);
      }
    });
  }

  function finalizeSync() {
    updateProgressUI(98, 'Memperbarui cache modul...', syncState.processedCount, syncState.totalItems);

    $.ajax({
      url: syncUrl,
      type: 'POST',
      timeout: 60000,
      data: {
        _token: csrfToken,
        action: 'finish',
        sync_id: syncState.syncId
      },
      success: function (res) {
        completeSync(syncState.processedCount, 'Berhasil menambahkan ' + syncState.processedCount + ' data dummy.');
      },
      error: function () {
        // Tetap tandai complete karena data sudah disimpan
        completeSync(syncState.processedCount, 'Data berhasil disimpan.');
      }
    });
  }

  function completeSync(count, message) {
    syncState.isSyncing = false;
    updateProgressUI(100, 'Sinkronisasi selesai!', count, count);

    // Ubah progress bar jadi warna sukses
    $('#sync-progress-bar').removeClass('progress-bar-striped progress-bar-animated')
      .css('background', '#28a745');
    $('#dock-progress-bar').removeClass('progress-bar-striped progress-bar-animated')
      .removeClass('bg-primary').addClass('bg-success');

    $('#sync-completed-summary').text(message);
    $('#sync-view-progress').hide();
    $('#sync-view-completed').fadeIn(200);

    // Update Dock jika sedang di latar belakang
    if (syncState.isMinimized) {
      $('#dock-status-icon').removeClass('fa-sync fa-spin text-primary')
        .addClass('fa-check-circle text-success');
      $('#dock-title').text('Sinkronisasi Selesai');
      $('#dock-pct-badge').removeClass('badge-primary').addClass('badge-success').text('100%');
      $('#dock-detail-text').text(count + ' data berhasil ditambahkan');

      setTimeout(function () {
        $('#sync-floating-dock').fadeOut(400);
      }, 4000);
    }

    // Hilangkan tombol sinkronisasi dummy di halaman
    $('.btn-sync-dummy').fadeOut(300);

    // Refresh datatable tanpa full reload
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('.datatable')) {
      $('.datatable').DataTable().ajax.reload(null, false);
    }

    if (typeof notify === 'function') {
      notify('Sinkronisasi data dummy berhasil diselesaikan!', 'success');
    }
  }

  function finishCancelledSync() {
    if (syncState.syncId) {
      $.ajax({
        url: syncUrl,
        type: 'POST',
        data: {
          _token: csrfToken,
          action: 'cancel',
          sync_id: syncState.syncId
        }
      });
    }
    syncState.isSyncing = false;
    $('#sync-floating-dock').fadeOut();
    $('#syncDummyModal').modal('hide');

    if (typeof notify === 'function') {
      notify('Sinkronisasi data dummy dihentikan.', 'warning');
    }

    if ($.fn.DataTable && $.fn.DataTable.isDataTable('.datatable')) {
      $('.datatable').DataTable().ajax.reload(null, false);
    }
  }

  function handleSyncError(errorMsg) {
    syncState.isSyncing = false;
    if (syncState.isMinimized) {
      $('#sync-floating-dock').fadeOut();
    }
    $('#syncDummyModal').modal('hide');
    swal({
      title: "Sinkronisasi Terhenti",
      text: errorMsg + "\n\nCatatan: Data yang sudah tersimpan (" + syncState.processedCount + " data) tidak hilang. Anda dapat menekan tombol Sinkronkan Data Dummy lagi untuk melanjutkan sisa data yang belum tersinkron.",
      type: "warning",
      confirmButtonText: "Mengerti"
    });

    if ($.fn.DataTable && $.fn.DataTable.isDataTable('.datatable')) {
      $('.datatable').DataTable().ajax.reload(null, false);
    }
  }

  function updateProgressUI(percent, statusText, currentCount, totalCount) {
    const pct = Math.min(Math.max(percent, 0), 100);
    
    // Update Modal
    $('#sync-pct-badge').text(pct + '%');
    $('#sync-progress-bar').css('width', pct + '%').attr('aria-valuenow', pct);
    $('#sync-status-detail').text(statusText);
    $('#sync-items-counter').text(currentCount + ' / ' + totalCount);

    // Update Floating Dock
    $('#dock-pct-badge').text(pct + '%');
    $('#dock-progress-bar').css('width', pct + '%');
    $('#dock-detail-text').text(statusText);
    $('#dock-counter-text').text(currentCount + ' / ' + totalCount);
  }
});
</script>
