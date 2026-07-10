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
                        <button type="button" class="btn btn-success btn-lg" id="btn-trigger-upload"><i
                                class="fa fa-folder-open"></i> Buka File Explorer</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function selectMediaGridItem(elem, event) {
        if (event && $(event.target).closest('.btn-view-media').length > 0) return;
        $('.media-grid-item').removeClass('border-primary shadow').css('border-width', '1px');
        $(elem).addClass('border-primary shadow').css('border-width', '2px');
        $('#global-library-select').val($(elem).data('val'));
        $('#btn-use-selected-media').removeAttr('disabled');
    }

    $(function () {
        let currentFileInput = null;
        let currentFileWrapper = null;
        let currentTextInput = null;

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
                        let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext);

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
                            else if (ext === 'application/zip') allowedExts.push('zip');
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
                        let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext);
                        let baseUrl = '{{ url("media") }}/' + fileName;
                        let previewUrl = baseUrl + (isImage ? '?size=small' : '');
                        let scheme = '{{ request()->getScheme() }}';

                        let icon = 'fa-file';
                        if (['pdf'].includes(ext)) icon = 'fa-file-pdf text-danger';
                        else if (['doc', 'docx'].includes(ext)) icon = 'fa-file-word text-primary';
                        else if (['xls', 'xlsx'].includes(ext)) icon = 'fa-file-excel text-success';
                        else if (['zip', 'rar'].includes(ext)) icon = 'fa-file-archive text-warning';
                        else if (['mp4', 'mkv', 'avi'].includes(ext)) icon = 'fa-file-video text-info';

                        let viewBtn = '';
                        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'mp4', 'mkv', 'avi', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(ext)) {
                            viewBtn = '<button type="button" class="btn btn-sm btn-light position-absolute btn-view-media" data-media="' + scheme + '://' + media.host + '/media/' + fileName + '" data-ext="' + ext + '" style="top: 2px; right: 2px; z-index: 10; padding: 2px 6px; font-size: 11px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);" title="Preview"><i class="fa fa-eye"></i></button>';
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

                        let fileSize = Math.round(media.size / 1024); /* KB approx if available, or just omit if not. Actually LeazyCMS uses media_size() php helper. We can just skip size or format it roughly. */
                        let sizeStr = media.size ? (fileSize > 1024 ? (fileSize / 1024).toFixed(2) + ' MB' : fileSize + ' KB') : '';

                        let col = $('<div class="col-4 col-md-3 col-lg-2 mb-3 px-1 media-grid-col" data-ext="' + ext + '"></div>');
                        let card = $('<div class="card h-100 border media-grid-item pointer position-relative" data-val="' + fileName + '" onclick="selectMediaGridItem(this, event)"></div>');
                        if (viewBtn) card.append(viewBtn);
                        card.append(imgWrapper);
                        card.append('<div class="card-body p-1 text-center" style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; font-size: 11px;"><span title="' + fileName + '">' + fileName + '</span><div style="font-size: 10px; color: #888;">' + sizeStr + '</div></div>');

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

        /* Run on initial load */
        initGlobalFilePickers();

        /* Run when new nodes are added */
        let observer = new MutationObserver(function (mutations) {
            let shouldInit = false;
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length > 0) shouldInit = true;
            });
            if (shouldInit) initGlobalFilePickers();
        });
        observer.observe(document.body, { childList: true, subtree: true });

        $('#btn-trigger-upload').on('click', function () {
            if (currentFileInput) {
                $('#globalMediaModal').modal('hide');
                currentFileInput.click();
            }
        });

        $('#btn-use-selected-media').on('click', function () {
            let selectedVal = $('#global-library-select').val();
            if (selectedVal && currentFileInput && currentFileWrapper) {
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
                let isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext);
                let previewArea = currentFileWrapper.find('.media-preview-area');

                previewArea.empty().show();
                if (isImage) {
                    let url = '/media/' + selectedVal + '?size=small';
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
            }
        });

        $('#globalMediaModal').on('hidden.bs.modal', function () {
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
    });
</script>