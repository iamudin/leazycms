@extends('cms::backend.layout.app', ['title' => 'Media Manager'])

@section('content')
    <link rel="stylesheet" href="{{ url('backend/css/cropper.min.css') }}">

    <style>
        /* Styling Media Manager */
        .mm-header-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .mm-card-stats {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 16px 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .mm-card-stats:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .mm-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .mm-filter-pill {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none !important;
            margin-right: 6px;
            margin-bottom: 6px;
        }

        .mm-filter-pill:hover {
            background: #f1f5f9;
            color: #1e293b;
            border-color: #94a3b8;
        }

        .mm-filter-pill.active {
            background: #0284c7;
            color: #fff;
            border-color: #0284c7;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);
        }

        .mm-filter-pill .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 20px;
            background: rgba(0, 0, 0, 0.08);
            color: inherit;
        }

        .mm-filter-pill.active .badge {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        /* Grid Items */
        .mm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
        }

        @media (max-width: 576px) {
            .mm-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
        }

        .mm-item-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: all 0.25s ease-in-out;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
        }

        .mm-item-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border-color: #93c5fd;
        }

        .mm-thumb-wrap {
            height: 140px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .mm-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .mm-item-card:hover .mm-thumb-img {
            transform: scale(1.06);
        }

        .mm-badge-ext {
            position: absolute;
            top: 8px;
            left: 8px;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 7px;
            border-radius: 6px;
            background: rgba(15, 23, 42, 0.75);
            color: #fff;
            backdrop-filter: blur(4px);
            z-index: 2;
            letter-spacing: 0.5px;
        }

        .mm-badge-size {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 7px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.9);
            color: #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }

        .mm-overlay-actions {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
            z-index: 3;
            backdrop-filter: blur(2px);
        }

        .mm-item-card:hover .mm-overlay-actions {
            opacity: 1;
        }

        .mm-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none !important;
        }

        .mm-action-btn:hover {
            background: #fff;
            color: #0f172a;
            transform: scale(1.1);
        }

        .mm-action-btn.btn-edit:hover {
            background: #f59e0b;
            color: #fff;
            border-color: #f59e0b;
        }

        .mm-action-btn.btn-delete:hover {
            background: #ef4444;
            color: #fff;
            border-color: #ef4444;
        }

        .mm-info-wrap {
            padding: 10px 12px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
        }

        .mm-file-name {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }

        .mm-file-meta {
            font-size: 11px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Highlight Animasi Saat Gambar Berhasil Di-edit */
        @keyframes mmCardHighlight {
            0% {
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.95);
                transform: scale(1.03);
            }
            50% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0.4);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
                transform: scale(1);
            }
        }

        .mm-card-updated {
            animation: mmCardHighlight 2s ease-out;
            z-index: 5;
        }

        /* Upload Zone */
        .mm-dropzone {
            border: 2px dashed #38bdf8;
            border-radius: 14px;
            background: #f0f9ff;
            padding: 35px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .mm-dropzone:hover,
        .mm-dropzone.dragover {
            border-color: #0284c7;
            background: #e0f2fe;
            transform: scale(1.005);
        }

        /* Cropper Editor */
        .cropper-view-container {
            max-height: 480px;
            background: #0f172a;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cropper-view-container img {
            max-width: 100%;
            max-height: 480px;
        }

        .editor-btn-group .btn {
            padding: 5px 10px;
            font-size: 12px;
        }

        /* Usage Alert Items */
        .usage-item-card {
            border-left: 4px solid #ef4444;
            background: #fef2f2;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 8px;
            font-size: 13px;
        }
    </style>

    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                <div>
                    <h3 class="mm-header-title mb-1">
                        <i class="fa fa-picture-o text-primary mr-2"></i> Media Manager
                    </h3>
                    <p class="text-muted mb-0" style="font-size: 13px;">
                        Kelola seluruh media gambar, dokumen, dan berkas dengan tampilan grid view, pengelompokan ekstensi,
                        pengurutan, serta editor gambar interaktif.
                    </p>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="reloadMediaGrid()">
                        <i class="fa fa-refresh mr-1"></i> Refresh
                    </button>
                    <label for="mm-file-input" class="btn btn-primary btn-sm mb-0 pointer" style="cursor: pointer;">
                        <i class="fa fa-cloud-upload mr-1"></i> Upload File
                    </label>
                    <input type="file" id="mm-file-input" multiple
                        accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.tar,.gz,.txt,.csv"
                        style="position: absolute; left: -9999px; opacity: 0; width: 1px; height: 1px;">
                </div>
            </div>
        </div>

        <!-- Upload Progress Bar (Muncul saat proses upload file dari perangkat) -->
        <div class="col-12 mb-3" id="mm-upload-progress" style="display: none;">
            <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-0"
                style="background: #e0f2fe; border-left: 4px solid #0284c7 !important; border-radius: 8px;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="font-weight-bold text-primary" style="font-size: 13px;">
                        <i class="fa fa-spinner fa-spin mr-1"></i> Sedang mengunggah file dari perangkat ke Media Library...
                    </span>
                    <span id="mm-upload-progress-percent" class="font-weight-bold text-primary"
                        style="font-size: 12px;">0%</span>
                </div>
                <div class="progress" style="height: 6px; border-radius: 4px;">
                    <div id="mm-upload-progress-bar"
                        class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar"
                        style="width: 0%;"></div>
                </div>
            </div>
        </div>

        <!-- Statistik Bar -->
        <div class="col-12 mb-3">
            <div class="row" style="margin-left: -6px; margin-right: -6px;">
                <div class="col-md-3 col-6 mb-2 px-1">
                    <div class="mm-card-stats">
                        <div class="mm-stat-icon" style="background: #e0f2fe; color: #0284c7;">
                            <i class="fa fa-folder-open"></i>
                        </div>
                        <div>
                            <div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">
                                Total Media</div>
                            <div style="font-size: 18px; font-weight: 700; color: #0f172a;" id="stat-total-files">
                                {{ number_format($totalFiles, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-2 px-1">
                    <div class="mm-card-stats">
                        <div class="mm-stat-icon" style="background: #fef3c7; color: #d97706;">
                            <i class="fa fa-database"></i>
                        </div>
                        <div>
                            <div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">
                                Ukuran Penyimpanan</div>
                            <div style="font-size: 18px; font-weight: 700; color: #0f172a;" id="stat-total-size">
                                {{ $totalSizeFormatted }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-2 px-1">
                    <div class="mm-card-stats">
                        <div class="mm-stat-icon" style="background: #dcfce7; color: #16a34a;">
                            <i class="fa fa-file-image-o"></i>
                        </div>
                        <div>
                            <div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">
                                Gambar Terunggah</div>
                            @php
                                $imgCount = 0;
                                foreach (['webp', 'png', 'jpg', 'jpeg', 'gif', 'svg'] as $ie) {
                                    $imgCount += ($extensionsList[$ie] ?? 0);
                                }
                            @endphp
                            <div style="font-size: 18px; font-weight: 700; color: #0f172a;">
                                {{ number_format($imgCount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-2 px-1">
                    <div class="mm-card-stats">
                        <div class="mm-stat-icon" style="background: #f1f5f9; color: #475569;">
                            <i class="fa fa-tags"></i>
                        </div>
                        <div>
                            <div class="text-muted" style="font-size: 11px; text-transform: uppercase; font-weight: 600;">
                                Format Ekstensi</div>
                            <div style="font-size: 18px; font-weight: 700; color: #0f172a;">{{ $extensionsList->count() }}
                                Jenis</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Toolbar Area -->
        <div class="col-12 mb-3">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <!-- Baris 1: Filter Kategori & Ekstensi -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap mb-2">
                            <span class="font-weight-bold text-dark" style="font-size: 13px;">
                                <i class="fa fa-filter text-info mr-1"></i> Kelompok Ekstensi File:
                            </span>
                        </div>

                        <!-- Kategori Pills -->
                        <div class="d-flex flex-wrap align-items-center">
                            <a href="javascript:void(0)" class="mm-filter-pill active" data-filter="all"
                                onclick="setCategoryFilter('all', this)">
                                <i class="fa fa-th-large"></i> Semua <span class="badge"
                                    id="badge-count-all">{{ $totalFiles }}</span>
                            </a>
                            <a href="javascript:void(0)" class="mm-filter-pill" data-filter="image"
                                onclick="setCategoryFilter('image', this)">
                                <i class="fa fa-file-image-o text-success"></i> Gambar
                            </a>
                            <a href="javascript:void(0)" class="mm-filter-pill" data-filter="document"
                                onclick="setCategoryFilter('document', this)">
                                <i class="fa fa-file-pdf-o text-danger"></i> Dokumen
                            </a>
                            <a href="javascript:void(0)" class="mm-filter-pill" data-filter="video"
                                onclick="setCategoryFilter('video', this)">
                                <i class="fa fa-file-video-o text-primary"></i> Video & Audio
                            </a>
                            <a href="javascript:void(0)" class="mm-filter-pill" data-filter="archive"
                                onclick="setCategoryFilter('archive', this)">
                                <i class="fa fa-file-archive-o text-warning"></i> Arsip
                            </a>
                        </div>

                        <!-- Ekstensi Spesifik Pills Container -->
                        <div id="dynamic-ext-pills" class="d-flex flex-wrap align-items-center mt-2 pt-2"
                            style="border-top: 1px dashed #e2e8f0;">
                            <span class="text-muted mr-2"
                                style="font-size: 11px; text-transform: uppercase; font-weight: 600;">Format:</span>
                            @foreach($extensionsList->take(12) as $ext => $cnt)
                                <a href="javascript:void(0)" class="mm-filter-pill ext-pill py-1 px-2" style="font-size: 11px;"
                                    data-ext="{{ $ext }}" onclick="setExtensionFilter('{{ $ext }}', this)">
                                    .{{ strtoupper($ext) }} <span class="badge">{{ $cnt }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Baris 2: Pencarian & Pengurutan (Waktu / Ukuran) -->
                    <div class="row align-items-center pt-2" style="border-top: 1px solid #f1f5f9;">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"
                                        style="border-radius: 8px 0 0 8px;">
                                        <i class="fa fa-search text-muted"></i>
                                    </span>
                                </div>
                                <input type="text" id="mm-search-input" class="form-control border-left-0"
                                    placeholder="Cari berdasarkan nama file..." style="border-radius: 0 8px 8px 0;">
                                <div class="input-group-append" id="mm-search-clear-wrap" style="display: none;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-md-end align-items-center flex-wrap" style="gap: 10px;">
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <label class="mb-0 text-muted" style="font-size: 12px; white-space: nowrap;">
                                    <i class="fa fa-sort mr-1"></i> Urutkan:
                                </label>
                                <select id="mm-sort-select" class="form-control form-control-sm"
                                    style="width: auto; border-radius: 8px;" onchange="onSortChange()">
                                    <option value="time_desc" selected>🕒 Waktu: Terbaru</option>
                                    <option value="time_asc">🕒 Waktu: Terlama</option>
                                    <option value="size_desc">⚖️ Ukuran: Terbesar</option>
                                    <option value="size_asc">⚖️ Ukuran: Terkecil</option>
                                    <option value="name_asc">🔤 Nama: A &rarr; Z</option>
                                    <option value="name_desc">🔤 Nama: Z &rarr; A</option>
                                </select>
                            </div>
                            <div class="text-muted" style="font-size: 12px;" id="mm-info-count">
                                Menampilkan <b id="mm-showing-count">0</b> file
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media Grid Container -->
        <div class="col-12">
            <div id="mm-loading-state" class="text-center py-5">
                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                <p class="mt-2 text-muted" style="font-size: 14px;">Memuat pustaka media...</p>
            </div>

            <div id="mm-empty-state" class="text-center py-5 bg-white rounded shadow-sm border" style="display: none;">
                <i class="fa fa-folder-open-o fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
                <h5 class="text-dark font-weight-bold">Tidak ada file yang ditemukan</h5>
                <p class="text-muted" style="font-size: 13px;">Coba ubah kata kunci pencarian atau bersihkan filter
                    ekstensi.</p>
                <div class="d-inline-flex align-items-center">
                    <button class="btn btn-sm btn-outline-primary mr-2" onclick="resetAllFilters()">Reset Filter</button>
                    <label for="mm-file-input" class="btn btn-sm btn-primary mb-0 pointer">
                        <i class="fa fa-cloud-upload mr-1"></i> Upload File dari Perangkat
                    </label>
                </div>
            </div>

            <div id="mm-grid-container" class="mm-grid" style="display: none;">
                <!-- Kartu Grid diinjeksi via JavaScript -->
            </div>

            <!-- Tombol Muat Lebih Banyak / Paginasi -->
            <div class="text-center mt-4 mb-5" id="mm-pagination-wrap" style="display: none;">
                <button type="button" class="btn btn-outline-primary px-4 py-2" id="btn-load-more"
                    onclick="loadMoreMedia()">
                    <i class="fa fa-arrow-down mr-1"></i> Muat Lebih Banyak
                </button>
            </div>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- MODAL: PREVIEW FILE LENGKAP -->
    <!-- ==================================================== -->
    <div class="modal fade" id="modalPreviewMedia" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
                <div class="modal-header bg-dark text-white py-3 px-4">
                    <h6 class="modal-title font-weight-bold" id="preview-modal-title">Preview File</h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div id="preview-content-area" class="text-center bg-light p-3"
                        style="min-height: 250px; display: flex; align-items: center; justify-content: center;">
                        <!-- Konten preview (gambar, video, audio, dokumen) -->
                    </div>
                    <div class="p-3 bg-white border-top">
                        <div class="row align-items-center">
                            <div class="col-md-7 mb-2 mb-md-0">
                                <div class="font-weight-bold text-dark text-truncate" id="preview-meta-name"
                                    style="font-size: 14px;">-</div>
                                <div class="text-muted" style="font-size: 12px;">
                                    Ukuran: <span id="preview-meta-size" class="font-weight-bold text-dark">-</span> &bull;
                                    Tipe: <span id="preview-meta-type" class="badge badge-secondary">-</span> &bull;
                                    Diupload: <span id="preview-meta-date">-</span>
                                    <span id="preview-meta-host-wrap" style="display: none;"> &bull; Host: <span id="preview-meta-host" class="badge badge-info text-white font-weight-normal">-</span></span>
                                </div>
                            </div>
                            <div class="col-md-5 text-md-right">
                                <button type="button" class="btn btn-sm btn-outline-secondary mr-1"
                                    id="btn-copy-preview-url" onclick="copyCurrentPreviewUrl()">
                                    <i class="fa fa-link mr-1"></i> Salin URL
                                </button>
                                <a href="#" download class="btn btn-sm btn-primary" id="btn-download-preview">
                                    <i class="fa fa-download mr-1"></i> Unduh
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- MODAL: IMAGE EDITOR (CROP, ROTATE, FLIP, RESIZE) -->
    <!-- ==================================================== -->
    <div class="modal fade" id="modalImageEditor" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
                <div class="modal-header bg-dark text-white py-3 px-4">
                    <h5 class="modal-title font-weight-bold" style="font-size: 16px;">
                        <i class="fa fa-magic text-warning mr-2"></i> Edit Gambar: <span id="editor-file-name"
                            class="text-info"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        onclick="closeImageEditor()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3 bg-light">
                    <!-- Toolbar Alat Editor -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-2 d-flex flex-wrap align-items-center justify-content-between"
                            style="gap: 10px;">
                            <!-- Aspect Ratio -->
                            <div class="d-flex align-items-center" style="gap: 5px;">
                                <small class="text-muted font-weight-bold mr-1">Aspek Rasio:</small>
                                <div class="btn-group btn-group-sm editor-btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary active"
                                        onclick="setAspectRatio(NaN, this)">Bebas</button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="setAspectRatio(1, this)">1:1</button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="setAspectRatio(16/9, this)">16:9</button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="setAspectRatio(4/3, this)">4:3</button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="setAspectRatio(2/3, this)">2:3</button>
                                </div>
                            </div>

                            <!-- Transformasi (Rotasi, Flip, Zoom) -->
                            <div class="d-flex align-items-center" style="gap: 6px;">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-dark" onclick="rotateEditor(-90)"
                                        title="Putar 90° Kiri"><i class="fa fa-undo"></i> -90°</button>
                                    <button type="button" class="btn btn-outline-dark" onclick="rotateEditor(90)"
                                        title="Putar 90° Kanan"><i class="fa fa-repeat"></i> +90°</button>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-dark" onclick="flipEditor('x')"
                                        title="Cermin Horizontal"><i class="fa fa-arrows-h"></i> Flip H</button>
                                    <button type="button" class="btn btn-outline-dark" onclick="flipEditor('y')"
                                        title="Cermin Vertikal"><i class="fa fa-arrows-v"></i> Flip V</button>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-dark" onclick="zoomEditor(0.1)"
                                        title="Perbesar"><i class="fa fa-search-plus"></i></button>
                                    <button type="button" class="btn btn-outline-dark" onclick="zoomEditor(-0.1)"
                                        title="Perkecil"><i class="fa fa-search-minus"></i></button>
                                    <button type="button" class="btn btn-outline-dark" onclick="resetEditor()"
                                        title="Reset Posisi"><i class="fa fa-refresh"></i> Reset</button>
                                </div>
                            </div>

                            <!-- Dimensi Saat Ini -->
                            <div class="text-muted" style="font-size: 12px;">
                                Resolusi Seleksi: <b id="editor-crop-dimensions" class="text-primary">- x -</b>
                            </div>
                        </div>
                    </div>

                    <!-- Canvas Workspace Gambar -->
                    <div class="cropper-view-container">
                        <img id="editor-image-preview" src="" alt="Edit Image" style="display: block; max-width: 100%;">
                    </div>
                </div>
                <div class="modal-footer bg-white d-flex justify-content-between align-items-center px-4 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal"
                        onclick="closeImageEditor()">
                        Batal
                    </button>
                    <div class="d-flex" style="gap: 8px;">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-save-as-new"
                            onclick="saveEditedImage('new')">
                            <i class="fa fa-copy mr-1"></i> Simpan sebagai File Baru
                        </button>
                        <button type="button" class="btn btn-success btn-sm font-weight-bold" id="btn-save-overwrite"
                            onclick="saveEditedImage('overwrite')">
                            <i class="fa fa-check mr-1"></i> Timpa File Asli
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- MODAL: PERINGATAN VALIDASI HAPUS (JIKA TERPAKAI) -->
    <!-- ==================================================== -->
    <div class="modal fade" id="modalUsageWarning" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="border-radius: 14px; overflow: hidden; border: none; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.25);">
                <div class="modal-header bg-danger text-white py-3 px-4">
                    <h5 class="modal-title font-weight-bold" style="font-size: 16px;">
                        <i class="fa fa-exclamation-triangle mr-2"></i> File Tidak Dapat Dihapus!
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark mb-3" style="font-size: 14px;">
                        File <b id="usage-file-name" class="text-danger"></b> tidak boleh dihapus karena saat ini masih
                        aktif digunakan pada:
                    </p>

                    <!-- Daftar Referensi Posts -->
                    <div id="usage-posts-section" style="display: none;" class="mb-3">
                        <h6 class="font-weight-bold text-dark mb-2" style="font-size: 13px;">
                            <i class="fa fa-newspaper-o text-primary mr-1"></i> Tabel Posts (<span
                                id="usage-posts-count">0</span>):
                        </h6>
                        <div id="usage-posts-list" style="max-height: 160px; overflow-y: auto;">
                            <!-- List Item Posts -->
                        </div>
                    </div>

                    <!-- Daftar Referensi Options -->
                    <div id="usage-options-section" style="display: none;">
                        <h6 class="font-weight-bold text-dark mb-2" style="font-size: 13px;">
                            <i class="fa fa-cogs text-warning mr-1"></i> Tabel Options / Pengaturan (<span
                                id="usage-options-count">0</span>):
                        </h6>
                        <div id="usage-options-list" style="max-height: 120px; overflow-y: auto;">
                            <!-- List Item Options -->
                        </div>
                    </div>

                    <div class="alert alert-warning py-2 px-3 mt-3 mb-0" style="font-size: 12px; border-radius: 8px;">
                        <i class="fa fa-info-circle mr-1"></i> Silakan hapus atau ganti file tersebut dari post atau
                        pengaturan terkait terlebih dahulu jika tetap ingin menghapusnya.
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- OVERLAY DRAG & DROP FILE LANGSUNG KE HALAMAN -->
    <!-- ==================================================== -->
    <div id="mm-drag-drop-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.8); z-index: 99999; backdrop-filter: blur(4px);">
        <div class="d-flex align-items-center justify-content-center h-100">
            <div
                style="border: 3px dashed #38bdf8; border-radius: 20px; padding: 50px 70px; text-align: center; color: #fff; background: rgba(15, 23, 42, 0.7); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
                <i class="fa fa-cloud-upload text-info fa-5x mb-3"></i>
                <h3 class="font-weight-bold mb-2">Lepaskan File di Sini</h3>
                <p style="font-size: 14px; color: #cbd5e1; margin: 0;">File akan langsung diunggah dari perangkat Anda ke
                    Media Library</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ url('backend/js/plugins/cropper.min.js') }}"></script>
        <script>
            // State Media Manager
            let currentCategory = 'all';
            let currentExtension = 'all';
            let currentSearch = '';
            let currentSort = 'time_desc';
            let currentPage = 1;
            let lastPage = 1;
            let isLoading = false;
            let cropper = null;
            let activeEditingFile = null;
            let currentPreviewUrl = '';

            const ROUTES = {
                list: "{{ route('media-manager.list') }}",
                upload: "{{ route('media-manager.upload') }}",
                checkUsage: "{{ url(admin_path() . '/media-manager/check-usage') }}",
                destroy: "{{ url(admin_path() . '/media-manager/destroy') }}",
                editImage: "{{ url(admin_path() . '/media-manager/edit-image') }}",
            };

            $(document).ready(function () {
                loadMedia(1, false);

                // Search Input Debounce
                let searchTimer = null;
                $('#mm-search-input').on('input', function () {
                    let val = $(this).val();
                    if (val.length > 0) {
                        $('#mm-search-clear-wrap').show();
                    } else {
                        $('#mm-search-clear-wrap').hide();
                    }
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function () {
                        currentSearch = val;
                        loadMedia(1, false);
                    }, 350);
                });

                // File input change handler (Langsung upload saat file dipilih dari explorer device)
                $('#mm-file-input').on('change', function () {
                    if (this.files && this.files.length > 0) {
                        handleFileUpload(this.files);
                    }
                });

                // Drag & Drop File langsung ke seluruh layar
                let dragCounter = 0;
                $(window).on('dragenter', function (e) {
                    e.preventDefault();
                    dragCounter++;
                    $('#mm-drag-drop-overlay').fadeIn(150);
                });
                $(window).on('dragleave', function (e) {
                    e.preventDefault();
                    dragCounter--;
                    if (dragCounter <= 0) {
                        dragCounter = 0;
                        $('#mm-drag-drop-overlay').fadeOut(150);
                    }
                });
                $(window).on('dragover', function (e) {
                    e.preventDefault();
                });
                $(window).on('drop', function (e) {
                    e.preventDefault();
                    dragCounter = 0;
                    $('#mm-drag-drop-overlay').fadeOut(150);
                    let files = e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
                    if (files && files.length > 0) {
                        handleFileUpload(files);
                    }
                });
            });

            // ==========================================
            // FUNGSI LOAD DATA GRID
            // ==========================================
            function loadMedia(page, append) {
                if (isLoading) return;
                isLoading = true;

                if (!append) {
                    $('#mm-grid-container').hide().empty();
                    $('#mm-empty-state').hide();
                    $('#mm-loading-state').show();
                    $('#mm-pagination-wrap').hide();
                } else {
                    $('#btn-load-more').prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Memuat...');
                }

                $.ajax({
                    url: ROUTES.list,
                    method: 'GET',
                    data: {
                        page: page,
                        category: currentCategory,
                        extension: currentExtension,
                        search: currentSearch,
                        sort: currentSort,
                        per_page: 24
                    },
                    success: function (res) {
                        $('#mm-loading-state').hide();
                        isLoading = false;

                        if (res.status === 'success') {
                            currentPage = res.current_page;
                            lastPage = res.last_page;

                            $('#mm-showing-count').text(res.total);

                            if (res.data.length === 0 && !append) {
                                $('#mm-empty-state').show();
                                $('#mm-grid-container').hide();
                            } else {
                                $('#mm-empty-state').hide();
                                $('#mm-grid-container').show();

                                res.data.forEach(function (item) {
                                    renderGridCard(item);
                                });

                                if (currentPage < lastPage) {
                                    $('#mm-pagination-wrap').show();
                                    $('#btn-load-more').prop('disabled', false).html('<i class="fa fa-arrow-down mr-1"></i> Muat Lebih Banyak (' + (res.total - (currentPage * res.per_page) > 0 ? (res.total - (currentPage * res.per_page)) : '') + ' tersisa)');
                                } else {
                                    $('#mm-pagination-wrap').hide();
                                }
                            }
                        }
                    },
                    error: function () {
                        isLoading = false;
                        $('#mm-loading-state').hide();
                        notifyAlert('danger', 'Gagal memuat data media. Silakan coba kembali.');
                    }
                });
            }

            function reloadMediaGrid() {
                loadMedia(1, false);
            }

            function loadMoreMedia() {
                if (currentPage < lastPage) {
                    loadMedia(currentPage + 1, true);
                }
            }

            // ==========================================
            // RENDER & UPDATE KARTU GRID ITEM
            // ==========================================
            function escapeHtml(str) {
                if (!str) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function buildCardHtml(item) {
                let isImage = item.is_image;
                let isVideo = item.is_video;
                let isAudio = item.is_audio;
                let isPdf = item.is_pdf;

                let icon = 'fa-file-o text-muted';
                if (isPdf) icon = 'fa-file-pdf-o text-danger';
                else if (item.is_doc) icon = 'fa-file-word-o text-primary';
                else if (item.is_archive) icon = 'fa-file-archive-o text-warning';
                else if (isVideo) icon = 'fa-file-video-o text-info';
                else if (isAudio) icon = 'fa-file-audio-o text-success';

                // Sanitasi XSS
                let safeName = escapeHtml(item.file_name);
                let safeExt = escapeHtml(item.extension);
                let safeSize = escapeHtml(item.file_size_formatted);
                let safeCreated = escapeHtml(item.created_at);
                let jsonItem = JSON.stringify(item).replace(/"/g, '&quot;');
                let jsonName = JSON.stringify(item.file_name).replace(/"/g, '&quot;');
                let jsonUrl = JSON.stringify(item.url).replace(/"/g, '&quot;');

                // Thumb inner
                let thumbHtml = '';
                if (isImage) {
                    thumbHtml = `<img src="${item.thumb_url}" alt="${safeName}" class="mm-thumb-img" loading="lazy" onerror="this.src='${item.url}'">`;
                } else {
                    thumbHtml = `<div class="d-flex flex-column align-items-center justify-content-center h-100 text-center"><i class="fa ${icon}" style="font-size: 42px;"></i></div>`;
                }

                // Action Buttons Overlay
                let editBtn = '';
                if (isImage && !['SVG', 'ICO'].includes(item.extension)) {
                    editBtn = `<button type="button" class="mm-action-btn btn-edit" title="Edit Gambar (Crop/Rotate/Resize)" onclick="openImageEditor(${item.id}, ${jsonUrl}, ${jsonName}, event)"><i class="fa fa-pencil"></i></button>`;
                }

                return `
                    <div class="mm-item-card" id="media-card-${item.id}">
                        <div class="mm-thumb-wrap">
                            <span class="mm-badge-ext">${safeExt}</span>
                            <span class="mm-badge-size">${safeSize}</span>
                            ${thumbHtml}
                            <div class="mm-overlay-actions">
                                <button type="button" class="mm-action-btn" title="Preview File" onclick="openPreviewModal(${jsonItem}, event)">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button type="button" class="mm-action-btn" title="Salin URL" onclick="copyMediaUrl(${jsonUrl}, event)">
                                    <i class="fa fa-link"></i>
                                </button>
                                ${editBtn}
                                <button type="button" class="mm-action-btn btn-delete" title="Hapus File" onclick="confirmDeleteMedia(${item.id}, ${jsonName}, event)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mm-info-wrap">
                            <div class="mm-file-name" title="${safeName}">${safeName}</div>
                            <div class="mm-file-meta">
                                <span><i class="fa fa-clock-o mr-1"></i>${safeCreated}</span>
                                ${item.host ? `<span class="text-truncate ml-1 text-muted" title="Host: ${escapeHtml(item.host)}" style="max-width: 110px;"><i class="fa fa-globe mr-1"></i>${escapeHtml(item.host)}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderGridCard(item, prepend = false) {
                let cardHtml = buildCardHtml(item);
                if (prepend) {
                    $('#mm-grid-container').prepend(cardHtml);
                } else {
                    $('#mm-grid-container').append(cardHtml);
                }
            }

            function updateEditedMediaCard(file) {
                let existingCard = $(`#media-card-${file.id}`);
                if (existingCard.length > 0) {
                    let newCard = $(buildCardHtml(file));
                    newCard.addClass('mm-card-updated');
                    existingCard.replaceWith(newCard);

                    setTimeout(function () {
                        newCard.removeClass('mm-card-updated');
                    }, 2200);
                }
            }

            // ==========================================
            // FILTER & SORT HANDLERS
            // ==========================================
            function setCategoryFilter(category, el) {
                $('.mm-filter-pill').removeClass('active');
                $(el).addClass('active');
                $('.ext-pill').removeClass('active');

                currentCategory = category;
                currentExtension = 'all';
                loadMedia(1, false);
            }

            function setExtensionFilter(ext, el) {
                $('.mm-filter-pill').removeClass('active');
                $(el).addClass('active');

                currentCategory = 'all';
                currentExtension = ext;
                loadMedia(1, false);
            }

            function onSortChange() {
                currentSort = $('#mm-sort-select').val();
                loadMedia(1, false);
            }

            function clearSearch() {
                $('#mm-search-input').val('');
                $('#mm-search-clear-wrap').hide();
                currentSearch = '';
                loadMedia(1, false);
            }

            function resetAllFilters() {
                currentCategory = 'all';
                currentExtension = 'all';
                currentSearch = '';
                $('#mm-search-input').val('');
                $('#mm-search-clear-wrap').hide();
                $('.mm-filter-pill').removeClass('active');
                $('.mm-filter-pill[data-filter="all"]').addClass('active');
                loadMedia(1, false);
            }

            // ==========================================
            // UPLOAD HANDLER (LANGSUNG DARI PERANGKAT)
            // ==========================================
            function handleFileUpload(fileList) {
                if (!fileList || fileList.length === 0) return;

                let formData = new FormData();
                for (let i = 0; i < fileList.length; i++) {
                    formData.append('media[]', fileList[i]);
                }
                formData.append('_token', "{{ csrf_token() }}");

                $('#mm-upload-progress').slideDown(200);
                $('#mm-upload-progress-bar').css('width', '15%');
                $('#mm-upload-progress-percent').text('15%');

                $.ajax({
                    url: ROUTES.upload,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function () {
                        let xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function (evt) {
                            if (evt.lengthComputable) {
                                let percent = Math.round((evt.loaded / evt.total) * 95);
                                $('#mm-upload-progress-bar').css('width', percent + '%');
                                $('#mm-upload-progress-percent').text(percent + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function (res) {
                        $('#mm-upload-progress-bar').css('width', '100%');
                        $('#mm-upload-progress-percent').text('100%');
                        setTimeout(function () {
                            $('#mm-upload-progress').slideUp(300);
                            $('#mm-file-input').val('');
                        }, 600);

                        if (res.status === 'success') {
                            notifyAlert('success', res.message);
                            loadMedia(1, false);
                        }
                    },
                    error: function (xhr) {
                        $('#mm-upload-progress').slideUp(300);
                        $('#mm-file-input').val('');
                        let msg = 'Gagal mengupload file.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        notifyAlert('danger', msg);
                    }
                });
            }

            // ==========================================
            // PREVIEW MODAL
            // ==========================================
            function openPreviewModal(item, e) {
                if (e) e.stopPropagation();
                let cleanUrl = item.url.split('?')[0];
                currentPreviewUrl = cleanUrl;

                $('#preview-modal-title').text(item.file_name);
                $('#preview-meta-name').text(item.file_name);
                $('#preview-meta-size').text(item.file_size_formatted);
                $('#preview-meta-type').text(item.extension);
                $('#preview-meta-date').text(item.created_at);
                if (item.host) {
                    $('#preview-meta-host').text(item.host);
                    $('#preview-meta-host-wrap').show();
                } else {
                    $('#preview-meta-host-wrap').hide();
                }
                $('#btn-download-preview').attr('href', cleanUrl);

                let area = $('#preview-content-area');
                area.empty();

                if (item.is_image) {
                    let sep = item.url.indexOf('?') >= 0 ? '&' : '?';
                    area.html(`<img src="${item.url}${sep}t=${new Date().getTime()}" class="img-fluid rounded" style="max-height: 480px; object-fit: contain;" alt="${item.file_name}">`);
                } else if (item.is_video) {
                    area.html(`<video controls style="max-width: 100%; max-height: 420px; border-radius: 8px;"><source src="${item.url}" type="${item.file_type}">Browser Anda tidak mendukung tag video.</video>`);
                } else if (item.is_audio) {
                    area.html(`<audio controls style="width: 100%; max-width: 400px;"><source src="${item.url}" type="${item.file_type}">Browser Anda tidak mendukung tag audio.</audio>`);
                } else if (item.is_pdf) {
                    area.html(`<iframe src="${item.url}" style="width: 100%; height: 500px; border: none; border-radius: 8px;"></iframe>`);
                } else {
                    area.html(`
                                <div class="py-5 text-center">
                                    <i class="fa fa-file-text-o fa-5x text-secondary mb-3"></i>
                                    <h5 class="font-weight-bold text-dark">${item.file_name}</h5>
                                    <p class="text-muted" style="font-size: 13px;">Format ini tidak mendukung preview langsung di browser.</p>
                                    <a href="${cleanUrl}" download class="btn btn-primary btn-sm"><i class="fa fa-download mr-1"></i> Unduh File</a>
                                </div>
                            `);
                }

                $('#modalPreviewMedia').modal('show');
            }

            function copyCurrentPreviewUrl() {
                if (currentPreviewUrl) {
                    copyTextToClipboard(currentPreviewUrl);
                }
            }

            function copyMediaUrl(url, e) {
                if (e) e.stopPropagation();
                let cleanUrl = url.split('?')[0];
                copyTextToClipboard(cleanUrl);
            }

            function copyTextToClipboard(text) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(function () {
                        notifyAlert('success', 'URL file berhasil disalin ke clipboard!');
                    });
                } else {
                    let tempInput = $('<input>');
                    $('body').append(tempInput);
                    tempInput.val(text).select();
                    document.execCommand('copy');
                    tempInput.remove();
                    notifyAlert('success', 'URL file berhasil disalin!');
                }
            }

            // ==========================================
            // VALIDASI SEBELUM MENGHAPUS (POSTS & OPTIONS)
            // ==========================================
            function confirmDeleteMedia(id, fileName, e) {
                if (e) e.stopPropagation();

                let card = $(`#media-card-${id}`);
                let btn = card.find('.btn-delete');
                let origHtml = btn.html();
                btn.html('<i class="fa fa-spinner fa-spin"></i>');

                // Panggil API check-usage untuk validasi
                $.ajax({
                    url: `${ROUTES.checkUsage}/${id}`,
                    method: 'GET',
                    success: function (res) {
                        btn.html(origHtml);

                        if (res.is_used) {
                            // FILE TERPAKAI: BLOKIR PENGHAPUSAN DAN TAMPILKAN DAFTAR PENGGUNAAN
                            $('#usage-file-name').text(fileName);

                            // Render Posts Usages
                            let postsList = $('#usage-posts-list');
                            postsList.empty();
                            if (res.post_usages && res.post_usages.length > 0) {
                                $('#usage-posts-count').text(res.post_usages.length);
                                res.post_usages.forEach(function (p) {
                                    postsList.append(`
                                                <div class="usage-item-card">
                                                    <div class="font-weight-bold text-dark">${p.title}</div>
                                                    <div class="text-muted" style="font-size: 11px;">
                                                        Tipe: <span class="badge badge-info">${p.type}</span> &bull; 
                                                        Posisi: <span class="text-danger font-weight-bold">${p.used_in}</span>
                                                    </div>
                                                </div>
                                            `);
                                });
                                $('#usage-posts-section').show();
                            } else {
                                $('#usage-posts-section').hide();
                            }

                            // Render Options Usages
                            let optionsList = $('#usage-options-list');
                            optionsList.empty();
                            if (res.option_usages && res.option_usages.length > 0) {
                                $('#usage-options-count').text(res.option_usages.length);
                                res.option_usages.forEach(function (o) {
                                    optionsList.append(`
                                                <div class="usage-item-card" style="border-left-color: #f59e0b; background: #fffbeb;">
                                                    <div class="font-weight-bold text-dark"><i class="fa fa-cog mr-1"></i> ${o.name}</div>
                                                    <div class="text-muted" style="font-size: 11px;">Digunakan sebagai pengaturan situs / template</div>
                                                </div>
                                            `);
                                });
                                $('#usage-options-section').show();
                            } else {
                                $('#usage-options-section').hide();
                            }

                            $('#modalUsageWarning').modal('show');
                        } else {
                            // FILE TIDAK TERPAKAI: BISA DIHAPUS DENGAN AMAN
                            if (typeof swal === 'function') {
                                swal({
                                    title: "Hapus File Ini?",
                                    text: `File "${fileName}" tidak terpakai di tabel posts maupun options. File akan dihapus permanen dari sistem.`,
                                    type: "warning",
                                    showCancelButton: true,
                                    confirmButtonColor: "#ef4444",
                                    confirmButtonText: "Ya, Hapus!",
                                    cancelButtonText: "Batal",
                                    closeOnConfirm: false
                                }, function (isConfirm) {
                                    if (isConfirm) {
                                        executeDeleteFile(id, fileName);
                                    }
                                });
                            } else {
                                if (confirm(`Yakin ingin menghapus file "${fileName}"?`)) {
                                    executeDeleteFile(id, fileName);
                                }
                            }
                        }
                    },
                    error: function () {
                        btn.html(origHtml);
                        notifyAlert('danger', 'Gagal memeriksa status penggunaan file.');
                    }
                });
            }

            function executeDeleteFile(id, fileName) {
                $.ajax({
                    url: `${ROUTES.destroy}/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        if (typeof swal === 'function') {
                            swal("Terhapus!", res.message, "success");
                        } else {
                            notifyAlert('success', res.message);
                        }

                        $(`#media-card-${id}`).fadeOut(300, function () {
                            $(this).remove();
                            let count = parseInt($('#stat-total-files').text().replace(/\./g, '')) || 0;
                            if (count > 0) {
                                $('#stat-total-files').text((count - 1).toLocaleString('id-ID'));
                            }
                        });
                    },
                    error: function (xhr) {
                        let msg = 'Gagal menghapus file.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (typeof swal === 'function') {
                            swal("Gagal!", msg, "error");
                        } else {
                            notifyAlert('danger', msg);
                        }
                    }
                });
            }

            // ==========================================
            // IMAGE EDITOR (CROPPER.JS)
            // ==========================================
            function openImageEditor(id, url, fileName, e) {
                if (e) e.stopPropagation();

                let cacheBuster = (url.indexOf('?') >= 0 ? '&' : '?') + 't=' + new Date().getTime();
                let freshUrl = url + cacheBuster;

                activeEditingFile = { id: id, url: url, fileName: fileName };
                $('#editor-file-name').text(fileName);

                let img = document.getElementById('editor-image-preview');
                img.src = freshUrl;

                $('#modalImageEditor').modal('show');

                // Inisialisasi Cropper setelah modal tampil
                setTimeout(function () {
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(img, {
                        viewMode: 2,
                        dragMode: 'move',
                        autoCropArea: 0.9,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        crop: function (event) {
                            let w = Math.round(event.detail.width);
                            let h = Math.round(event.detail.height);
                            $('#editor-crop-dimensions').text(`${w} x ${h} px`);
                        }
                    });
                }, 300);
            }

            function closeImageEditor() {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                $('#modalImageEditor').modal('hide');
            }

            function setAspectRatio(ratio, el) {
                if (!cropper) return;
                $('.editor-btn-group button').removeClass('active');
                $(el).addClass('active');
                cropper.setAspectRatio(ratio);
            }

            function rotateEditor(deg) {
                if (!cropper) return;
                cropper.rotate(deg);
            }

            let scaleX = 1;
            let scaleY = 1;
            function flipEditor(axis) {
                if (!cropper) return;
                if (axis === 'x') {
                    scaleX = -scaleX;
                    cropper.scaleX(scaleX);
                } else {
                    scaleY = -scaleY;
                    cropper.scaleY(scaleY);
                }
            }

            function zoomEditor(ratio) {
                if (!cropper) return;
                cropper.zoom(ratio);
            }

            function resetEditor() {
                if (!cropper) return;
                cropper.reset();
                scaleX = 1;
                scaleY = 1;
            }

            function saveEditedImage(action) {
                if (!cropper || !activeEditingFile) return;

                let btn = action === 'overwrite' ? $('#btn-save-overwrite') : $('#btn-save-as-new');
                let origHtml = btn.html();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Menyimpan...');

                // Ambil data canvas gambar hasil edit
                let canvas = cropper.getCroppedCanvas({
                    maxWidth: 4096,
                    maxHeight: 4096,
                    imageSmoothingQuality: 'high'
                });

                if (!canvas) {
                    btn.prop('disabled', false).html(origHtml);
                    notifyAlert('danger', 'Gagal mengambil hasil canvas gambar.');
                    return;
                }

                let base64Image = canvas.toDataURL('image/webp', 0.92);

                $.ajax({
                    url: `${ROUTES.editImage}/${activeEditingFile.id}`,
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        action: action,
                        image_data: base64Image
                    },
                    success: function (res) {
                        btn.prop('disabled', false).html(origHtml);
                        closeImageEditor();

                        if (res.status === 'success') {
                            notifyAlert('success', res.message);

                            if (res.mode === 'overwrite' && res.file) {
                                // Reload HANYA gambar yang di-edit di dalam list tanpa me-reload seluruh list
                                updateEditedMediaCard(res.file);
                            } else if (res.mode === 'new' && res.file) {
                                // Mode file baru: tambahkan kartu baru di awal grid jika sesuai filter
                                let matchesCategory = (currentCategory === 'all' || currentCategory === 'image');
                                let matchesExt = (currentExtension === 'all' || currentExtension.toLowerCase() === res.file.extension.toLowerCase());

                                if (matchesCategory && matchesExt) {
                                    renderGridCard(res.file, true);
                                    let newCard = $(`#media-card-${res.file.id}`);
                                    if (newCard.length > 0) {
                                        newCard.addClass('mm-card-updated');
                                        setTimeout(function () {
                                            newCard.removeClass('mm-card-updated');
                                        }, 2200);
                                    }
                                }

                                let count = parseInt($('#stat-total-files').text().replace(/\./g, '')) || 0;
                                $('#stat-total-files').text((count + 1).toLocaleString('id-ID'));
                            }
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html(origHtml);
                        let msg = 'Gagal menyimpan perubahan gambar.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        notifyAlert('danger', msg);
                    }
                });
            }

            // ==========================================
            // NOTIFIKASI
            // ==========================================
            function notifyAlert(type, message) {
                if ($.notify) {
                    $.notify({
                        icon: type === 'success' ? 'fa fa-check-circle' : 'fa fa-exclamation-circle',
                        message: message
                    }, {
                        type: type === 'success' ? 'success' : (type === 'danger' ? 'danger' : 'info'),
                        allow_dismiss: true,
                        placement: {
                            from: "top",
                            align: "right"
                        },
                        delay: 4000,
                        animate: {
                            enter: 'animated fadeInDown',
                            exit: 'animated fadeOutUp'
                        }
                    });
                } else {
                    alert(message);
                }
            }
        </script>
    @endpush
@endsection