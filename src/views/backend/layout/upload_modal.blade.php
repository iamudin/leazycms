<style>
    .media-grid-item .btn-view-media,
    .media-grid-item .btn-download-media,
    .media-grid-item .btn-delete-media {
        opacity: 0;
        transition: opacity 0.2s ease-in-out;
    }

    .media-grid-item:hover .btn-view-media,
    .media-grid-item:hover .btn-download-media,
    .media-grid-item:hover .btn-delete-media {
        opacity: 1;
    }
</style>
<div class="modal fade" id="globalMediaModal" tabindex="-1" role="dialog" aria-labelledby="globalMediaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="globalMediaModalLabel">Pilih / Upload File</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="globalMediaTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="g-library-tab" data-toggle="tab" href="#g-library" role="tab"
                            aria-controls="g-library" aria-selected="true"><i class="fa fa-folder-open"></i> Media
                            Library</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="g-upload-tab" data-toggle="tab" href="#g-upload" role="tab"
                            aria-controls="g-upload" aria-selected="false"><i class="fa fa-upload"></i> Upload
                            Baru</a>
                    </li>
                </ul>
                <div class="tab-content" id="globalMediaTabContent">
                    <div class="tab-pane fade show active" id="g-library" role="tabpanel"
                        aria-labelledby="g-library-tab">
                        <div class="form-group">
                            <label>Pilih File yang Sudah Ada</label>
                            <input type="hidden" id="global-library-select" value="">
                            <div id="global-media-grid" class="row mx-0"
                                style="max-height: 400px; overflow-y: auto; overflow-x: hidden; border: 1px solid #eee; padding: 10px; background: #fafafa;">
                                <div class="col-12 text-center py-4" id="global-media-loading" style="display:none;">
                                    <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
                                    <p class="mt-2 text-muted">Memuat media...</p>
                                </div>
                                <div class="col-12 text-center text-muted py-4" id="global-media-empty"
                                    style="display:none;">Belum ada media di library.</div>
                            </div>
                            <div class="text-center mt-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-load-more-media"
                                    style="display:none;">Muat Lebih Banyak</button>
                            </div>
                        </div>
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-primary" id="btn-use-selected-media" disabled>Gunakan
                                File Ini</button>
                        </div>
                    </div>
                    <div class="tab-pane fade text-center py-5" id="g-upload" role="tabpanel"
                        aria-labelledby="g-upload-tab">
                        <p>Pilih file dari perangkat Anda untuk langsung digunakan pada form ini.</p>

                        <input type="file" id="g-ajax-upload-input" class="initialized-gmedia" style="display:none;"
                            multiple>

                        <div id="g-upload-alert" class="alert alert-danger"
                            style="display:none; max-width: 500px; margin: 0 auto 15px; text-align: left;"></div>

                        <div id="g-upload-ui">
                            <button type="button" class="btn btn-success btn-lg" id="btn-trigger-upload">
                                <i class="fa fa-folder-open"></i> Buka File Explorer
                            </button>
                        </div>

                        <div id="g-pre-upload-preview" class="mt-4"
                            style="display:none; max-width: 500px; margin: 0 auto; text-align: left;">
                            <h6 class="mb-2 text-muted">File Terpilih:</h6>
                            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 10px; margin-bottom: 15px; background: #fafafa;"
                                id="g-pre-upload-list">
                            </div>
                            <div class="text-center">
                                <button type="button" class="btn btn-outline-secondary mr-2"
                                    id="btn-cancel-upload">Batal</button>
                                <button type="button" class="btn btn-primary" id="btn-start-upload"><i
                                        class="fa fa-upload"></i> Mulai Upload</button>
                            </div>
                        </div>

                        <div id="g-upload-progress-container" class="mt-4"
                            style="display:none; max-width: 500px; margin: 0 auto;">
                            <div class="mb-2 text-left" id="g-upload-filename"
                                style="font-weight: 500; font-size: 14px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                Mengunggah...</div>
                            <div class="progress" style="height: 20px; border-radius: 10px;">
                                <div id="g-upload-progress-bar"
                                    class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                    role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
                                    aria-valuemax="100">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light d-flex justify-content-between align-items-center"
                style="font-size: 13px; border-top: 1px solid #e9ecef;">
                @php
                    $tenantFilesQuery = \Leazycms\FLC\Models\File::query();
                    if (config('modules.multisite_enabled') && !is_main_domain()) {
                        $tenantFilesQuery->where('host', request()->getHttpHost());
                    }

                    $totalFiles = $tenantFilesQuery->count();
                    $totalSizeBytes = $tenantFilesQuery->sum('file_size');

                    $formattedSize = '0 B';
                    if ($totalSizeBytes > 0) {
                        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                        $power = $totalSizeBytes > 0 ? floor(log($totalSizeBytes, 1024)) : 0;
                        $formattedSize = number_format($totalSizeBytes / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
                    }
                @endphp
                <div class="text-muted">
                    <i class="fa fa-hdd-o text-info mr-1"></i> Total Media: <b class="text-dark"
                        id="g-total-media-count">{{ number_format($totalFiles, 0, ',', '.') }}</b> file (<b
                        class="text-dark" id="g-total-media-size">{{ $formattedSize }}</b>)
                </div>

                @if(config('modules.multisite_enabled') && !is_main_domain())
                    @php
                        $tenantData = tenant();
                        $diskSpaceMB = $tenantData ? ($tenantData->disk_space ?? 0) : 0;
                        $diskSpaceBytes = $diskSpaceMB * 1024 * 1024;
                        $percentUsed = 0;
                        if ($diskSpaceBytes > 0) {
                            $percentUsed = min(100, round(($totalSizeBytes / $diskSpaceBytes) * 100, 1));
                        }
                    @endphp
                    @if($diskSpaceMB > 0)
                        @php
                            $sisaBytes = max(0, $diskSpaceBytes - $totalSizeBytes);
                            $sisaMB = round($sisaBytes / 1024 / 1024, 2);
                        @endphp
                        <div class="d-flex align-items-center ml-auto" style="width: 320px;" id="g-disk-space-container">
                            <div class="mr-2 text-right" style="line-height: 1.2;">
                                <div class="text-muted" style="font-size: 10px;">{{ $diskSpaceMB }} MB</div>
                                <div class="font-weight-bold g-disk-sisa-text {{ $sisaMB <= ($diskSpaceMB * 0.1) ? 'text-danger' : 'text-success' }}"
                                    style="font-size: 11px;">Sisa: {{ $sisaMB }} MB</div>
                            </div>
                            <div class="progress flex-grow-1" style="height: 12px; border-radius: 6px;"
                                title="{{ $formattedSize }} terpakai dari {{ $diskSpaceMB }} MB (Sisa: {{ $sisaMB }} MB)">
                                <div class="progress-bar g-disk-progress-bar {{ $percentUsed >= 90 ? 'bg-danger' : ($percentUsed >= 70 ? 'bg-warning' : 'bg-success') }}"
                                    role="progressbar" style="width: {{ $percentUsed }}%;" aria-valuenow="{{ $percentUsed }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span
                                class="ml-2 font-weight-bold g-disk-percent-text {{ $percentUsed >= 90 ? 'text-danger' : 'text-muted' }}"
                                style="font-size: 11px;">{{ $percentUsed }}%</span>
                        </div>
                    @else
                        <div class="text-muted ml-auto"><i class="fa fa-infinity text-primary"></i> Kapasitas Unmetered</div>
                    @endif
                @endif
                <script>
                    window.tenantDiskSpaceBytes = {{ (config('modules.multisite_enabled') && !is_main_domain() && isset($diskSpaceBytes) && $diskSpaceBytes > 0) ? $diskSpaceBytes : 'null' }};
                    window.tenantUsedSpaceBytes = {{ isset($totalSizeBytes) ? $totalSizeBytes : 0 }};
                </script>
            </div>
        </div>
    </div>
</div>

<script>
    function selectMediaGridItem(elem, event) {
        if (event && $(event.target).closest('.btn-view-media').length > 0) return;
        if (event && $(event.target).closest('.btn-delete-media').length > 0) return;
        if (event && $(event.target).closest('.btn-download-media').length > 0) {
            event.stopPropagation();
            return;
        }
        $('.media-grid-item').removeClass('border-primary shadow').css('border-width', '1px');
        $(elem).addClass('border-primary shadow').css('border-width', '2px');
        $('#global-library-select').val($(elem).data('val'));
        $('#btn-use-selected-media').removeAttr('disabled');
    }

    window.updateDiskSpaceUI = function (addedBytes = 0) {
        if (addedBytes !== 0) {
            window.tenantUsedSpaceBytes += addedBytes;
            if (window.tenantUsedSpaceBytes < 0) window.tenantUsedSpaceBytes = 0;
        }

        let totalMediaEl = $('#g-total-media-count');
        let totalMediaSizeEl = $('#g-total-media-size');
        if (addedBytes !== 0 && totalMediaEl.length) {
            let count = parseInt(totalMediaEl.text().replace(/\./g, '')) || 0;
            let increment = addedBytes > 0 ? 1 : -1;
            count += increment;
            if (count < 0) count = 0;
            totalMediaEl.text(count.toLocaleString('id-ID'));
        }

        let formatSize = function (size) {
            if (size === 0) return '0 B';
            let pwr = Math.floor(Math.log(size) / Math.log(1024));
            let units = ['B', 'KB', 'MB', 'GB', 'TB'];
            return (size / Math.pow(1024, pwr)).toFixed(2).replace('.00', '') + ' ' + units[pwr];
        };

        if (totalMediaSizeEl.length) {
            totalMediaSizeEl.text(formatSize(window.tenantUsedSpaceBytes));
        }

        if (window.tenantDiskSpaceBytes !== null && window.tenantDiskSpaceBytes > 0) {
            let diskMB = (window.tenantDiskSpaceBytes / 1024 / 1024).toFixed(0);
            let usedBytes = window.tenantUsedSpaceBytes;
            let sisaBytes = Math.max(0, window.tenantDiskSpaceBytes - usedBytes);
            let sisaMB = (sisaBytes / 1024 / 1024).toFixed(2);
            let percent = Math.min(100, Math.round((usedBytes / window.tenantDiskSpaceBytes) * 100 * 10) / 10);

            let barClass = percent >= 90 ? 'bg-danger' : (percent >= 70 ? 'bg-warning' : 'bg-success');
            let textClass = percent >= 90 ? 'text-danger' : 'text-muted';
            let sisaClass = sisaBytes <= (window.tenantDiskSpaceBytes * 0.1) ? 'text-danger' : 'text-success';
            let uStr = formatSize(usedBytes);

            let diskContainer = $('#g-disk-space-container');
            if (diskContainer.length) {
                diskContainer.find('.g-disk-sisa-text').removeClass('text-danger text-success').addClass(sisaClass).text('Sisa: ' + sisaMB + ' MB');

                let progressBar = diskContainer.find('.g-disk-progress-bar');
                progressBar.removeClass('bg-danger bg-warning bg-success').addClass(barClass)
                    .css('width', percent + '%')
                    .attr('aria-valuenow', percent);

                diskContainer.find('.g-disk-percent-text').removeClass('text-danger text-muted').addClass(textClass).text(percent + '%');
                diskContainer.find('.progress').attr('title', uStr + ' terpakai dari ' + diskMB + ' MB (Sisa: ' + sisaMB + ' MB)');
            }
        }
    };

    $(function () {
        let currentFileInput = null;
        let currentFileWrapper = null;
        let currentTextInput = null;
        window.currentSummernoteObj = null;

        function initGlobalFilePickers() {
            let uninitialized = $('input[type="file"]').not('.initialized-gmedia');

            uninitialized.each(function () {
                let $fileInput = $(this);
                if ($fileInput.hasClass('upload') || $fileInput.closest('.mediaupload').length > 0) return;

                /* Ignore Summernote hidden file inputs */
                if ($fileInput.attr('id') === 'replaceImageInput' || $fileInput.attr('id') === 'fileUploadInput' || $fileInput.hasClass('note-image-input') || $fileInput.hasClass('note-video-clip') || $fileInput.hasClass('note-audio-input') || $fileInput.closest('.note-modal').length > 0 || $fileInput.closest('.note-editor').length > 0) return;

                $fileInput.addClass('initialized-gmedia').hide();

                let wrapper = $('<div class="global-file-wrapper mb-2"></div>');
                let btn = $('<button type="button" class="btn btn-outline-primary btn-sm btn-open-gmedia"><i class="fa fa-folder-open"></i> Pilih / Upload Media</button>');
                let clearBtn = $('<button type="button" class="btn btn-outline-danger btn-sm btn-clear-gmedia ml-2" style="display:none;" title="Hapus File"><i class="fa fa-times"></i></button>');
                let previewArea = $('<div class="media-preview-area mt-2" style="display:none; max-width: 200px; border: 1px solid #ddd; padding: 10px; border-radius: 5px; text-align: center; background: #fff;"></div>');

                wrapper.append(btn).append(clearBtn).append(previewArea);
                $fileInput.after(wrapper);

                $fileInput.on('change', function () {
                    if (this.files && this.files.length > 0) {
                        let file = this.files[0];
                        let fileName = file.name;
                        let ext = fileName.split('.').pop().toLowerCase();
                        let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'].includes(ext);

                        previewArea.empty().show();
                        if (isImage) {
                            let objectUrl = URL.createObjectURL(file);
                            let $img = $('<img>').attr('src', objectUrl).css({
                                'max-width': '100%',
                                'height': 'auto',
                                'max-height': '150px',
                                'object-fit': 'contain',
                                'margin-bottom': '5px'
                            });
                            previewArea.append($img);
                        } else {
                            let icon = 'fa-file';
                            if (['pdf'].includes(ext)) icon = 'fa-file-pdf text-danger';
                            else if (['doc', 'docx'].includes(ext)) icon = 'fa-file-word text-primary';
                            else if (['xls', 'xlsx'].includes(ext)) icon = 'fa-file-excel text-success';
                            else if (['zip', 'rar'].includes(ext)) icon = 'fa-file-archive text-warning';
                            else if (['mp4', 'mkv', 'avi'].includes(ext)) icon = 'fa-file-video text-info';
                            previewArea.append('<i class="fa ' + icon + ' fa-3x mb-2"></i>');
                        }
                        previewArea.append('<div style="font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + fileName + '">' + fileName + '</div>');

                        clearBtn.show();
                        wrapper.siblings('input[type="hidden"].gmedia-hidden').remove();
                        $fileInput.removeAttr('disabled');
                    }
                });

                btn.on('click', function () {
                    currentFileInput = $fileInput;
                    currentFileWrapper = wrapper;

                    let acceptAttr = $fileInput.attr('accept');
                    if (acceptAttr) {
                        let acceptedExts = acceptAttr.split(',').map(s => s.trim().toLowerCase());
                        let allowedExts = [];
                        acceptedExts.forEach(ext => {
                            if (ext.startsWith('.')) allowedExts.push(ext.substring(1));
                            else if (ext === 'image/jpeg' || ext === 'image/jpg') allowedExts.push('jpg', 'jpeg');
                            else if (ext === 'image/png') allowedExts.push('png');
                            else if (ext === 'image/gif') allowedExts.push('gif');
                            else if (ext === 'image/webp') allowedExts.push('webp');
                            else if (ext === 'image/svg+xml' || ext === 'image/svg') allowedExts.push('svg');
                            else if (ext === 'application/pdf') allowedExts.push('pdf');
                            else if (ext === 'video/mp4') allowedExts.push('mp4');
                            else if (ext === 'video/x-matroska') allowedExts.push('mkv');
                            else if (ext === 'video/x-msvideo') allowedExts.push('avi');
                            else if (ext === 'image/x-icon' || ext === 'image/vnd.microsoft.icon') allowedExts.push('ico');
                            else if (ext === 'application/zip') allowedExts.push('zip');
                            else if (ext === 'application/x-rar-compressed' || ext === 'application/vnd.rar') allowedExts.push('rar');
                            else if (ext === 'application/msword') allowedExts.push('doc');
                            else if (ext === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') allowedExts.push('docx');
                            else if (ext === 'application/vnd.ms-excel') allowedExts.push('xls');
                            else if (ext === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') allowedExts.push('xlsx');
                            else if (ext === 'application/vnd.ms-powerpoint') allowedExts.push('ppt');
                            else if (ext === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') allowedExts.push('pptx');
                            else if (ext === 'text/csv') allowedExts.push('csv');
                        });

                        $('#globalMediaModal').data('allowedExts', allowedExts);
                    } else {
                        $('#globalMediaModal').removeData('allowedExts');
                    }

                    if (!window.gmediaLoaded) {
                        loadGlobalMedia(1);
                    } else {
                        filterMediaGrid();
                    }

                    $('#globalMediaModal').modal('show');
                });

                clearBtn.on('click', function () {
                    $fileInput.val('');
                    previewArea.empty().hide();
                    wrapper.siblings('input[type="hidden"].gmedia-hidden').remove();
                    $fileInput.removeAttr('disabled');
                    $(this).hide();
                });
            });
        }

        $(document).on('click', '.btn-text-gmedia', function (e) {
            e.preventDefault();
            let target = $($(this).data('target'));
            if (!target.length) return;

            currentTextInput = target;
            currentFileInput = null;
            currentFileWrapper = null;

            let acceptAttr = target.attr('accept');
            if (acceptAttr) {
                let acceptedExts = acceptAttr.split(',').map(s => s.trim().toLowerCase());
                let allowedExts = [];
                acceptedExts.forEach(ext => {
                    if (ext.startsWith('.')) allowedExts.push(ext.substring(1));
                    else if (ext === 'image/jpeg' || ext === 'image/jpg') allowedExts.push('jpg', 'jpeg');
                    else if (ext === 'image/png') allowedExts.push('png');
                    else if (ext === 'image/gif') allowedExts.push('gif');
                    else if (ext === 'image/webp') allowedExts.push('webp');
                    else if (ext === 'image/svg+xml' || ext === 'image/svg') allowedExts.push('svg');
                    else if (ext === 'application/pdf') allowedExts.push('pdf');
                    else if (ext === 'video/mp4') allowedExts.push('mp4');
                    else if (ext === 'video/x-matroska') allowedExts.push('mkv');
                    else if (ext === 'video/x-msvideo') allowedExts.push('avi');
                    else if (ext === 'application/zip') allowedExts.push('zip');
                    else if (ext === 'application/x-rar-compressed' || ext === 'application/vnd.rar') allowedExts.push('rar');
                    else if (ext === 'application/msword') allowedExts.push('doc');
                    else if (ext === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') allowedExts.push('docx');
                    else if (ext === 'application/vnd.ms-excel') allowedExts.push('xls');
                    else if (ext === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') allowedExts.push('xlsx');
                    else if (ext === 'application/vnd.ms-powerpoint') allowedExts.push('ppt');
                    else if (ext === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') allowedExts.push('pptx');
                    else if (ext === 'text/csv') allowedExts.push('csv');
                });
                $('#globalMediaModal').data('allowedExts', allowedExts);
            } else {
                $('#globalMediaModal').removeData('allowedExts');
            }

            if (!window.gmediaLoaded) {
                loadGlobalMedia(1);
            } else {
                filterMediaGrid();
            }

            $('#globalMediaModal').modal('show');
        });

        window.gmediaLoaded = false;
        let currentMediaPage = 1;

        function filterMediaGrid() {
            let allowedExts = $('#globalMediaModal').data('allowedExts');
            $('.media-grid-col').each(function () {
                let itemExt = String($(this).data('ext')).toLowerCase();
                if (allowedExts && allowedExts.length > 0 && !allowedExts.includes(itemExt)) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        }

        function loadGlobalMedia(page) {
            if (page === 1) {
                $('#global-media-grid').find('.media-grid-col').remove();
                $('#global-media-loading').show();
                $('#global-media-empty').hide();
                $('#btn-load-more-media').hide();
            } else {
                $('#btn-load-more-media').prop('disabled', true).text('Memuat...');
            }

            $.get('{{ route("global.media.list") }}?page=' + page, function (response) {
                $('#global-media-loading').hide();

                if (response.data && response.data.length > 0) {
                    response.data.forEach(function (media) {
                        let fileName = media.file_name;
                        let ext = fileName.split('.').pop().toLowerCase();
                        let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'].includes(ext);
                        let baseUrl = '{{ url("media") }}/' + fileName;
                        let previewUrl = baseUrl + (isImage && !['svg', 'ico'].includes(ext) ? '?size=small' : '');
                        let scheme = '{{ request()->getScheme() }}';

                        let icon = 'fa-file';
                        if (['pdf'].includes(ext)) icon = 'fa-file-pdf text-danger';
                        else if (['doc', 'docx'].includes(ext)) icon = 'fa-file-word text-primary';
                        else if (['xls', 'xlsx'].includes(ext)) icon = 'fa-file-excel text-success';
                        else if (['zip', 'rar'].includes(ext)) icon = 'fa-file-archive text-warning';
                        else if (['mp4', 'mkv', 'avi'].includes(ext)) icon = 'fa-file-video text-info';

                        let viewBtn = '';
                        let deleteBtn = '<button type="button" class="btn btn-sm btn-danger position-absolute btn-delete-media" data-media="' + fileName + '" style="top: 2px; left: 2px; z-index: 10; padding: 2px 6px; font-size: 11px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Hapus File"><i class="fa fa-trash"></i></button>';

                        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'mp4', 'mkv', 'avi', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'ico'].includes(ext)) {
                            viewBtn = '<button type="button" class="btn btn-sm btn-light position-absolute btn-view-media" data-media="' + scheme + '://' + media.host + '/media/' + fileName + '" data-ext="' + ext + '" style="top: 2px; right: 2px; z-index: 10; padding: 2px 6px; font-size: 11px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Preview"><i class="fa fa-eye"></i></button>';
                        } else {
                            viewBtn = '<a href="' + scheme + '://' + media.host + '/media/' + fileName + '" download class="btn btn-sm btn-light position-absolute btn-download-media" style="top: 2px; right: 2px; z-index: 10; padding: 2px 6px; font-size: 11px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Download File"><i class="fa fa-download"></i></a>';
                        }

                        let imgWrapper = $('<div class="card-img-top text-center bg-light d-flex align-items-center justify-content-center" style="height: 90px; overflow: hidden;"></div>');
                        if (isImage) {
                            let $img = $('<img>').attr('src', previewUrl).addClass('img-fluid').css({
                                'object-fit': 'cover',
                                'width': '100%',
                                'height': '100%'
                            }).attr('alt', fileName).attr('loading', 'lazy');
                            imgWrapper.append($img);
                        } else {
                            imgWrapper.append('<i class="fa ' + icon + ' fa-3x"></i>');
                        }

                        let rawSize = media.file_size || media.size;
                        let fileSize = Math.round(rawSize / 1024);
                        let sizeStr = rawSize ? (fileSize > 1024 ? (fileSize / 1024).toFixed(2) + ' MB' : fileSize + ' KB') : '';

                        let dateStr = '';
                        if (media.created_at) {
                            let d = new Date(media.created_at);
                            if (!isNaN(d.getTime())) {
                                let pad = (n) => n.toString().padStart(2, '0');
                                dateStr = '<div style="font-size: 9px; color: #2b2b2bff; margin-top: 3px;" title="Di-upload pada: ' + media.created_at + '"><i class="fa fa-clock-o"></i> ' + pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear() + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + '</div>';
                            } else {
                                dateStr = '<div style="font-size: 9px; color: #2b2b2bff; margin-top: 3px;" title="Di-upload pada: ' + media.created_at + '"><i class="fa fa-clock-o"></i> ' + media.created_at.substring(0, 16) + '</div>';
                            }
                        }

                        let col = $('<div class="col-4 col-md-3 col-lg-2 mb-3 px-1 media-grid-col" data-ext="' + ext + '"></div>');
                        let card = $('<div class="card h-100 border media-grid-item pointer position-relative" data-val="' + fileName + '" onclick="selectMediaGridItem(this, event)"></div>');
                        card.append(deleteBtn);
                        if (viewBtn) card.append(viewBtn);
                        card.append(imgWrapper);
                        card.append('<div class="card-body p-1 text-center" style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 11px;"><span title="' + fileName + '">' + fileName + '</span><div style="font-size: 10px; color: #888; margin-top: 2px;"><span class="badge badge-info mr-1" style="font-size: 9px; padding: 2px 4px;">' + ext.toUpperCase() + '</span>' + sizeStr + '</div>' + dateStr + '</div>');

                        col.append(card);
                        $('#global-media-loading').before(col); /* Append before loading indicator */
                    });

                    window.gmediaLoaded = true;
                    filterMediaGrid();

                    if (response.current_page < response.last_page) {
                        $('#btn-load-more-media').prop('disabled', false).text('Muat Lebih Banyak').show();
                        currentMediaPage = response.current_page + 1;
                    } else {
                        $('#btn-load-more-media').hide();
                    }
                } else if (page === 1) {
                    $('#global-media-empty').show();
                    window.gmediaLoaded = true;
                }
            }).fail(function () {
                $('#global-media-loading').hide();
                $('#btn-load-more-media').prop('disabled', false).text('Gagal memuat. Coba lagi?').show();
            });
        }

        $('#btn-load-more-media').on('click', function () {
            loadGlobalMedia(currentMediaPage);
        });

        function initSummernotePickers() {
            setTimeout(function () {
                $('.note-toolbar').each(function () {
                    let $toolbar = $(this);

                    /* Hide default summernote insert buttons to avoid confusion */
                    $toolbar.find('button[data-original-title="Picture"], .note-icon-picture').closest('button').hide();
                    $toolbar.find('button[data-original-title="Video"], .note-icon-video').closest('button').hide();
                    $toolbar.find('button[data-original-title="Link"], .note-icon-link').closest('button').hide();

                    if ($toolbar.find('.btn-summernote-gmedia').length > 0) return;

                    let $btnGroup = $('<div class="note-btn-group btn-group note-insert"></div>');
                    let $btn = $('<button type="button" class="note-btn btn btn-light btn-sm btn-summernote-gmedia" tabindex="-1" title="Media Library"><i class="fa fa-file"></i> Sisipkan Media</button>');

                    $btn.on('click', function (e) {
                        e.preventDefault();
                        currentTextInput = null;
                        currentFileInput = null;
                        currentFileWrapper = null;

                        let context = $toolbar.closest('.note-editor').prev('.custom_html');
                        if (!context.length) {
                            context = $toolbar.closest('.note-editor').prev('textarea');
                        }
                        window.currentSummernoteObj = { context: context };

                        $('#globalMediaModal').removeData('allowedExts');

                        if (!window.gmediaLoaded) {
                            loadGlobalMedia(1);
                        } else {
                            filterMediaGrid();
                        }
                        $('#globalMediaModal').modal('show');
                    });

                    $btnGroup.append($btn);
                    $toolbar.append($btnGroup);
                });
            }, 500);
        }

        /* Run on initial load */
        initGlobalFilePickers();
        initSummernotePickers();

        /* Run when new nodes are added */
        let observer = new MutationObserver(function (mutations) {
            let shouldInit = false;
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) shouldInit = true;
            });
            if (shouldInit) { initGlobalFilePickers(); initSummernotePickers(); }
        });
        observer.observe(document.body, { childList: true, subtree: true });

        $('#btn-trigger-upload').on('click', function () {
            let activeInput = currentFileInput || currentTextInput;
            let allowedExts = $('#globalMediaModal').data('allowedExts');

            if (activeInput && activeInput.attr('accept')) {
                $('#g-ajax-upload-input').attr('accept', activeInput.attr('accept'));
            } else if (allowedExts && allowedExts.length > 0) {
                let acceptStr = allowedExts.map(ext => '.' + ext).join(',');
                $('#g-ajax-upload-input').attr('accept', acceptStr);
            } else {
                $('#g-ajax-upload-input').removeAttr('accept');
            }
            $('#g-ajax-upload-input').click();
        });

        let selectedFilesForUpload = [];

        async function compressImageClientSide(file) {
            let ext = file.name.split('.').pop().toLowerCase();
            if (!['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                return file;
            }

            return new Promise((resolve) => {
                let reader = new FileReader();
                reader.onload = function (event) {
                    let img = new Image();
                    img.onload = function () {
                        let width = img.width;
                        let height = img.height;
                        let maxWidth = 1700;

                        if (width > maxWidth) {
                            height = Math.round((height * maxWidth) / width);
                            width = maxWidth;
                        } else if (ext === 'webp') {
                            resolve(file);
                            return;
                        }

                        let canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        let ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(function (blob) {
                            let newFileName = file.name.substring(0, file.name.lastIndexOf('.')) + '.webp';
                            let newFile = new File([blob], newFileName, { type: 'image/webp', lastModified: Date.now() });
                            resolve(newFile);
                        }, 'image/webp', 0.95);
                    };
                    img.onerror = function () {
                        resolve(file); // If image loading fails, just resolve with original file
                    };
                    img.src = event.target.result;
                };
                reader.onerror = function () {
                    resolve(file);
                };
                reader.readAsDataURL(file);
            });
        }

        $('#g-ajax-upload-input').on('change', async function () {
            let files = this.files;
            if (!files || files.length === 0) return;

            // Show a simple loading indicator while compressing
            $('#g-upload-ui').hide();
            $('#g-pre-upload-preview').hide();
            $('#g-upload-alert').html('<i class="fa fa-spinner fa-spin"></i> Memproses gambar...').removeClass('alert-danger').addClass('alert-info').show();

            let allowedExts = $('#globalMediaModal').data('allowedExts');
            let maxSizeBytes = {{ \Illuminate\Http\UploadedFile::getMaxFilesize() }};
            let maxSizeMB = (maxSizeBytes / 1024 / 1024).toFixed(2);
            let invalidFiles = [];
            selectedFilesForUpload = [];
            $('#g-pre-upload-list').empty();

            let validIndex = 0;
            let totalSelectedSize = 0;
            for (let i = 0; i < files.length; i++) {
                let file = files[i];

                try {
                    file = await compressImageClientSide(file);
                } catch (e) {
                    console.error("Compression failed", e);
                }

                let ext = file.name.split('.').pop().toLowerCase();

                if (allowedExts && allowedExts.length > 0 && !allowedExts.includes(ext)) {
                    invalidFiles.push(file.name + ' (Ekstensi tidak diizinkan)');
                } else if (file.size > maxSizeBytes) {
                    invalidFiles.push(file.name + ' (Ukuran ' + (file.size / 1024 / 1024).toFixed(2) + ' MB melebihi batas ' + maxSizeMB + ' MB)');
                } else {
                    totalSelectedSize += file.size;
                    selectedFilesForUpload.push(file);

                    let size = (file.size / 1024).toFixed(2);
                    let sizeStr = size + ' KB';
                    if (size > 1024) sizeStr = (size / 1024).toFixed(2) + ' MB';

                    let previewHtml = '';
                    let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'].includes(ext);
                    if (isImage) {
                        let objectUrl = URL.createObjectURL(file);
                        previewHtml = '<img src="' + objectUrl + '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;">';
                    } else {
                        previewHtml = '<div style="width: 40px; height: 40px; background: #eee; border-radius: 4px; margin-right: 10px; display: flex; align-items: center; justify-content: center;"><i class="fa fa-file text-secondary"></i></div>';
                    }

                    $('#g-pre-upload-list').append(
                        '<div class="d-flex flex-column border-bottom py-2" id="upload-item-' + validIndex + '">' +
                        '<div class="d-flex align-items-center justify-content-between">' +
                        '<div class="d-flex align-items-center text-truncate" style="max-width: 60%;">' +
                        previewHtml +
                        '<div class="text-truncate" title="' + file.name + '"><strong>' + file.name + '</strong></div>' +
                        '</div>' +
                        '<div><span class="badge badge-info mr-2">' + ext.toUpperCase() + '</span><span class="text-muted" style="font-size: 12px;">' + sizeStr + '</span></div>' +
                        '</div>' +
                        '<div class="progress mt-2" style="height: 10px; display: none;" id="progress-container-' + validIndex + '">' +
                        '<div class="progress-bar progress-bar-striped progress-bar-animated" id="progress-bar-' + validIndex + '" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>' +
                        '</div>' +
                        '<div class="text-danger mt-1" style="font-size: 11px; display: none;" id="error-msg-' + validIndex + '"></div>' +
                        '<div class="text-success mt-1" style="font-size: 11px; display: none;" id="success-msg-' + validIndex + '"><i class="fa fa-check-circle"></i> Terupload</div>' +
                        '</div>'
                    );
                    validIndex++;
                }
            }

            if (window.tenantDiskSpaceBytes !== null && window.tenantDiskSpaceBytes > 0) {
                let remainingSpace = Math.max(0, window.tenantDiskSpaceBytes - window.tenantUsedSpaceBytes);
                if (totalSelectedSize > remainingSpace) {
                    let totalSelMB = (totalSelectedSize / 1024 / 1024).toFixed(2);
                    let remMB = (remainingSpace / 1024 / 1024).toFixed(2);
                    invalidFiles.push('<strong>Disk Penuh / Tidak Cukup!</strong><br>Total file yang dipilih (' + totalSelMB + ' MB) melebihi sisa kapasitas disk (' + remMB + ' MB). Silakan hapus file lama.');

                    // Reset selected files to prevent upload
                    selectedFilesForUpload = [];
                    $('#g-pre-upload-list').empty();
                }
            }

            if (invalidFiles.length > 0) {
                let errMsg = 'File berikut tidak dapat diproses:<br><ul class="mb-0 mt-1 pl-3">';
                invalidFiles.forEach(f => errMsg += '<li>' + f + '</li>');
                errMsg += '</ul>';
                $('#g-upload-alert').html(errMsg).removeClass('alert-info').addClass('alert-danger').show();
            } else {
                $('#g-upload-alert').hide().removeClass('alert-info alert-danger');
            }

            if (selectedFilesForUpload.length > 0) {
                $('#g-upload-ui').hide();
                $('#g-pre-upload-preview').show();
            } else {
                $(this).val(''); // Reset
                $('#g-upload-ui').show();
            }
        });

        $('#btn-cancel-upload').on('click', function () {
            selectedFilesForUpload = [];
            $('#g-ajax-upload-input').val('');
            $('#g-pre-upload-preview').hide();
            $('#g-upload-alert').hide();
            $('#g-upload-ui').show();
        });

        $('#btn-start-upload').on('click', async function () {
            if (selectedFilesForUpload.length === 0) return;

            $('#btn-start-upload').prop('disabled', true);
            $('#btn-cancel-upload').prop('disabled', true);
            $('#g-upload-alert').hide().html('');

            let successCount = 0;
            let lastUploadedCard = null;
            let hasError = false;

            for (let i = 0; i < selectedFilesForUpload.length; i++) {
                let file = selectedFilesForUpload[i];
                let fileName = file.name;

                $('#progress-container-' + i).show();
                let $progressBar = $('#progress-bar-' + i);

                let formData = new FormData();
                formData.append('media', file);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                try {
                    let response = await $.ajax({
                        url: '{{ route("media.upload") }}',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        xhr: function () {
                            var xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function (evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                    $progressBar.css('width', percentComplete + '%')
                                        .attr('aria-valuenow', percentComplete);
                                }
                            }, false);
                            return xhr;
                        }
                    });

                    if (response.status === 'success' && response.file_name) {
                        successCount++;
                        if (typeof updateDiskSpaceUI === 'function') {
                            updateDiskSpaceUI(response.file_size || file.size);
                        }
                        $progressBar.removeClass('progress-bar-striped progress-bar-animated').addClass('bg-success');
                        $('#success-msg-' + i).show();
                        let savedFileName = response.file_name.replace('/media/', '');
                        let savedExt = savedFileName.split('.').pop().toLowerCase();
                        let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'].includes(savedExt);
                        let baseUrl = '{{ url("media") }}/' + savedFileName;
                        let previewUrl = baseUrl + (isImage && !['svg', 'ico'].includes(savedExt) ? '?size=small' : '');
                        let scheme = '{{ request()->getScheme() }}';
                        let host = window.location.host;

                        let icon = 'fa-file';
                        if (['pdf'].includes(savedExt)) icon = 'fa-file-pdf text-danger';
                        else if (['doc', 'docx'].includes(savedExt)) icon = 'fa-file-word text-primary';
                        else if (['xls', 'xlsx'].includes(savedExt)) icon = 'fa-file-excel text-success';
                        else if (['zip', 'rar'].includes(savedExt)) icon = 'fa-file-archive text-warning';
                        else if (['mp4', 'mkv', 'avi'].includes(savedExt)) icon = 'fa-file-video text-info';

                        let viewBtn = '';
                        let deleteBtn = '<button type="button" class="btn btn-sm btn-danger position-absolute btn-delete-media" data-media="' + savedFileName + '" style="top: 2px; left: 2px; z-index: 10; padding: 2px 6px; font-size: 11px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Hapus File"><i class="fa fa-trash"></i></button>';

                        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'mp4', 'mkv', 'avi', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'ico'].includes(savedExt)) {
                            viewBtn = '<button type="button" class="btn btn-sm btn-light position-absolute btn-view-media" data-media="' + scheme + '://' + host + '/media/' + savedFileName + '" data-ext="' + savedExt + '" style="top: 2px; right: 2px; z-index: 10; padding: 2px 6px; font-size: 11px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Preview"><i class="fa fa-eye"></i></button>';
                        } else {
                            viewBtn = '<a href="' + scheme + '://' + host + '/media/' + savedFileName + '" download class="btn btn-sm btn-light position-absolute btn-download-media" style="top: 2px; right: 2px; z-index: 10; padding: 2px 6px; font-size: 11px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Download File"><i class="fa fa-download"></i></a>';
                        }

                        let imgWrapper = $('<div class="card-img-top text-center bg-light d-flex align-items-center justify-content-center" style="height: 90px; overflow: hidden;"></div>');
                        if (isImage) {
                            let $img = $('<img>').attr('src', previewUrl).addClass('img-fluid').css({
                                'object-fit': 'cover',
                                'width': '100%',
                                'height': '100%'
                            }).attr('alt', savedFileName).attr('loading', 'lazy');
                            imgWrapper.append($img);
                        } else {
                            imgWrapper.append('<i class="fa ' + icon + ' fa-3x"></i>');
                        }

                        let col = $('<div class="col-4 col-md-3 col-lg-2 mb-3 px-1 media-grid-col" data-ext="' + savedExt + '"></div>');
                        let card = $('<div class="card h-100 border media-grid-item pointer position-relative" data-val="' + savedFileName + '" onclick="selectMediaGridItem(this, event)"></div>');

                        card.append(deleteBtn);
                        if (viewBtn) card.append(viewBtn);
                        card.append(imgWrapper);
                        card.append('<div class="card-body p-1 text-center" style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 11px;"><span title="' + savedFileName + '">' + savedFileName + '</span><div style="font-size: 10px; color: #888; margin-top: 2px;"><span class="badge badge-info mr-1" style="font-size: 9px; padding: 2px 4px;">' + savedExt.toUpperCase() + '</span>Baru</div></div>');

                        col.append(card);
                        $('#global-media-empty').hide();
                        $('#global-media-grid').prepend(col);

                        lastUploadedCard = card;
                    } else {
                        hasError = true;
                        $progressBar.removeClass('progress-bar-striped progress-bar-animated').addClass('bg-danger');
                        $('#error-msg-' + i).text('Gagal: ' + (response.message || 'Error')).show();
                    }
                } catch (err) {
                    hasError = true;
                    let msg = 'Gagal';
                    if (err.responseJSON && err.responseJSON.message) {
                        msg += ': ' + err.responseJSON.message;
                    } else if (err.status === 413) {
                        msg += ': Ukuran file melebihi batas server.';
                    }
                    $progressBar.removeClass('progress-bar-striped progress-bar-animated').addClass('bg-danger');
                    $('#error-msg-' + i).text(msg).show();
                }
            }

            $('#btn-start-upload').prop('disabled', false);
            $('#btn-cancel-upload').prop('disabled', false);

            if (!hasError) {
                // Bersihkan tampilan list jika semua berhasil
                selectedFilesForUpload = [];
                $('#g-ajax-upload-input').val('');
                $('#g-pre-upload-preview').hide();
                $('#g-upload-alert').hide();
                $('#g-upload-ui').show();
            }

            if (successCount > 0) {
                notif(successCount + ' file berhasil diupload', 'success');
                if (lastUploadedCard) {
                    lastUploadedCard.click();
                }
                if (!hasError) {
                    $('#g-library-tab').tab('show');
                }
            }
        });

        $('#btn-use-selected-media').on('click', function () {
            let selectedVal = $('#global-library-select').val();
            if (!selectedVal) return;

            if (currentFileInput && currentFileWrapper) {
                let fieldName = currentFileInput.attr('name') || currentFileInput.data('name');
                if (!fieldName) return;

                /* Instead of modifying the original file input (which can't be set programmatically due to security),
                   we disable it and create a hidden input with the same name that contains the selected file path. */
                currentFileInput.attr('disabled', 'disabled');

                /* Remove existing hidden input if any */
                currentFileWrapper.siblings('input[type="hidden"].gmedia-hidden').remove();

                /* Add new hidden input */
                $('<input>').attr({
                    type: 'hidden',
                    name: fieldName,
                    class: 'gmedia-hidden',
                    value: '/media/' + selectedVal
                }).insertAfter(currentFileWrapper);

                let ext = selectedVal.split('.').pop().toLowerCase();
                let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'].includes(ext);
                let previewArea = currentFileWrapper.find('.media-preview-area');

                previewArea.empty().show();
                if (isImage) {
                    let url = '/media/' + selectedVal + (!['svg', 'ico'].includes(ext) ? '?size=small' : '');
                    let $img = $('<img>').attr('src', url).css({
                        'max-width': '100%',
                        'height': 'auto',
                        'max-height': '150px',
                        'object-fit': 'contain',
                        'margin-bottom': '5px'
                    });
                    previewArea.append($img);
                } else {
                    let icon = 'fa-file';
                    if (['pdf'].includes(ext)) icon = 'fa-file-pdf text-danger';
                    else if (['doc', 'docx'].includes(ext)) icon = 'fa-file-word text-primary';
                    else if (['xls', 'xlsx'].includes(ext)) icon = 'fa-file-excel text-success';
                    else if (['zip', 'rar'].includes(ext)) icon = 'fa-file-archive text-warning';
                    else if (['mp4', 'mkv', 'avi'].includes(ext)) icon = 'fa-file-video text-info';
                    previewArea.append('<i class="fa ' + icon + ' fa-3x mb-2"></i>');
                }
                previewArea.append('<div style="font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + selectedVal + '">' + selectedVal + '</div>');

                currentFileWrapper.find('.btn-clear-gmedia').show();
                $('#globalMediaModal').modal('hide');
            } else if (currentTextInput) {
                let fileUrl = '/media/' + selectedVal;
                currentTextInput.val(fileUrl);
                $('#globalMediaModal').modal('hide');
            } else if (window.currentSummernoteObj && window.currentSummernoteObj.context) {
                let fileUrl = '/media/' + selectedVal;
                let ext = selectedVal.split('.').pop().toLowerCase();
                let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'].includes(ext);

                if (isImage) {
                    let figureHTML = '<figure style="text-align: center; margin: 10px 0;">' +
                        '<img src="' + fileUrl + '" style="max-width: 100%; height: auto;" alt="">' +
                        '<figcaption style="font-style: italic; color: #666;"><small>Keterangan gambar</small></figcaption>' +
                        '</figure><p><br></p>';
                    window.currentSummernoteObj.context.summernote('pasteHTML', figureHTML);
                } else {
                    window.currentSummernoteObj.context.summernote('createLink', {
                        text: selectedVal,
                        url: fileUrl,
                        isNewWindow: true
                    });
                }
                $('#globalMediaModal').modal('hide');
            }
        });

        $('#globalMediaModal').on('hidden.bs.modal', function () {
            window.currentSummernoteObj = null;
            $('#global-library-select').val('');
            $('.media-grid-item').removeClass('border-primary shadow').css('border-width', '1px');
            $('#btn-use-selected-media').attr('disabled', 'disabled');
        });

        $(document).on('click', '.btn-remove-media', function (e) {
            e.preventDefault();
            if (confirm('Hapus gambar dari form ini?')) {
                let field = $(this).data('field');
                let wrapper = $(this).closest('.media-preview-wrapper');
                wrapper.hide();
                let inputWrapper = wrapper.nextAll('.media-input-wrapper').first();
                inputWrapper.show();
                /* Add hidden input to clear the field in database when saved */
                /* We use prepend so it's first in the DOM, allowing any newly selected media hidden input to override it. */
                let existingHidden = inputWrapper.find('input.removed-media-hidden[name="' + field + '"]');
                if (existingHidden.length === 0) {
                    inputWrapper.prepend('<input type="hidden" class="removed-media-hidden" name="' + field + '" value="">');
                }
            }
        });

        $(document).on('click', '.btn-delete-media', function (e) {
            e.stopPropagation();
            if (confirm('Yakin ingin menghapus file ini secara permanen dari server?')) {
                let btn = $(this);
                let mediaName = btn.data('media');
                let cardCol = btn.closest('.media-grid-col');

                btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                $.post('{{ route("media.destroy") }}', {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    media: mediaName
                }, function (res) {
                    if (res.status === 'success') {
                        cardCol.fadeOut(300, function () { $(this).remove(); });
                        if (typeof updateDiskSpaceUI === 'function' && res.deleted_size) {
                            updateDiskSpaceUI(-res.deleted_size);
                        }
                    } else {
                        alert('Gagal menghapus file.');
                        btn.html('<i class="fa fa-trash"></i>').prop('disabled', false);
                    }
                }).fail(function (xhr) {
                    let errorMsg = 'Terjadi kesalahan saat menghapus file.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                    btn.html('<i class="fa fa-trash"></i>').prop('disabled', false);
                });
            }
        });
    });
</script>