<style>
    .note-editor .note-editable iframe {
        pointer-events: none;
    }

    .note-editable img {
        cursor: pointer;
    }

    .note-editable img.selected-img {
        outline: 2px solid #007bff;
    }
</style>

@if (function_exists('current_module') && isset(current_module()?->form?->editor_mode) && current_module()?->form?->editor_mode == 'simple')
    <style>
        .note-editor .note-editable iframe {
            pointer-events: none;
        }

        .btn-summernote-gmedia {
            display: none !important;
        }
    </style>

@endif
<input type="file" id="replaceImageInput" accept="image/*" style="display:none;">
<input type="file" id="fileUploadInput" style="display:none;" accept="{{ allow_mime() }}">
<div class="modal fade" id="editImageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Edit Gambar</h5>
            </div>

            <div class="modal-body">
                <input type="text" id="edit-image-url" class="form-control mb-2" placeholder="URL">
                <input type="text" id="edit-image-alt" class="form-control mb-2" placeholder="ALT">
                <input type="text" id="edit-image-caption" class="form-control" placeholder="Caption">
            </div>

            <div class="modal-footer">

                <button class="btn btn-primary" type="button" id="btnSaveImageEdit">Simpan</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="embedModal" tabindex="-1" role="dialog" aria-labelledby="embedModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="embedModalLabel">Embed URL</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="embed-url">URL</label>
                    <input type="text" class="form-control" id="embed-url" placeholder="Enter URL">
                </div>
                <div class="form-group">
                    <label for="embed-width">Width</label>
                    <input type="text" class="form-control" id="embed-width"
                        placeholder="Sample : 100%, 100px or other">
                </div>
                <div class="form-group">
                    <label for="embed-height">Height</label>
                    <input type="text" class="form-control" id="embed-height"
                        placeholder="Sample : 100%, 100px or other">
                </div>
                <div class="form-group">
                    <label for="embed-style-attr">Style Attribute</label>
                    <input type="text" class="form-control" id="embed-style-attr"
                        placeholder="Sample : border: 1px solid #ccc; border-radius: 10px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="embedModalSave">Save changes</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade aiModal" id="aiModal" tabindex="-1" role="dialog" aria-labelledby="aiModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="aiModalLabel">Generate Artikel dengan AI</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <textarea id="aiPrompt" class="form-control" rows="4"
                    placeholder="Masukkan perintah artikel..."></textarea>
            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" id="btnGenerateAI" data-dismiss="modal" class="btn btn-primary">Generate</button>
            </div>

        </div>
    </div>
</div>
<!-- Table Style Modal -->
<div class="modal fade" id="tableStyleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Properti Tabel</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="tableStyleTabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabStyleTable">Table</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabStyleTr">Baris (TR)</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabStyleTd">Sel (TD)</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabStyleTable">
                        <div class="form-group">
                            <label>Width</label>
                            <input type="text" class="form-control form-control-sm" id="tblStyleWidth"
                                placeholder="100%, 500px, auto">
                        </div>
                        <div class="form-group">
                            <label>Border</label>
                            <input type="text" class="form-control form-control-sm" id="tblStyleBorder"
                                placeholder="1px solid #ccc">
                        </div>
                        <div class="form-group">
                            <label>Style Lainnya</label>
                            <input type="text" class="form-control form-control-sm" id="tblStyleExtra"
                                placeholder="background:#fff; padding:5px;">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabStyleTr">
                        <p class="text-muted small">Mengubah style pada baris (TR) yang sedang aktif/diklik.</p>
                        <div class="form-group">
                            <label>Background</label>
                            <input type="text" class="form-control form-control-sm" id="trStyleBg"
                                placeholder="#f5f5f5, transparent">
                        </div>
                        <div class="form-group">
                            <label>Style Lainnya</label>
                            <input type="text" class="form-control form-control-sm" id="trStyleExtra"
                                placeholder="border-bottom:1px solid #ccc;">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tabStyleTd">
                        <p class="text-muted small">Mengubah style pada sel (TD/TH) yang sedang aktif/diklik.</p>
                        <div class="form-group">
                            <label>Width</label>
                            <input type="text" class="form-control form-control-sm" id="tdStyleWidth"
                                placeholder="200px, 30%">
                        </div>
                        <div class="form-group">
                            <label>Background</label>
                            <input type="text" class="form-control form-control-sm" id="tdStyleBg"
                                placeholder="#fff, transparent">
                        </div>
                        <div class="form-group">
                            <label>Text Align</label>
                            <select class="form-control form-control-sm" id="tdStyleAlign">
                                <option value="">-- Tidak diubah --</option>
                                <option value="left">Kiri</option>
                                <option value="center">Tengah</option>
                                <option value="right">Kanan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Vertical Align</label>
                            <select class="form-control form-control-sm" id="tdStyleVAlign">
                                <option value="">-- Tidak diubah --</option>
                                <option value="top">Atas</option>
                                <option value="middle">Tengah</option>
                                <option value="bottom">Bawah</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Border</label>
                            <input type="text" class="form-control form-control-sm" id="tdStyleBorder"
                                placeholder="1px solid #ccc">
                        </div>
                        <div class="form-group">
                            <label>Padding</label>
                            <input type="text" class="form-control form-control-sm" id="tdStylePadding"
                                placeholder="5px, 10px 15px">
                        </div>
                        <div class="form-group">
                            <label>Style Lainnya</label>
                            <input type="text" class="form-control form-control-sm" id="tdStyleExtra"
                                placeholder="font-weight:bold; color:red;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveTableStyle">Terapkan</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    <script src="https://js.puter.com/v2/"></script>
@endpush
<script type="text/javascript">
    function dataURLtoFile(dataurl, filename) {
        var arr = dataurl.split(','),
            mimeMatch = arr[0].match(/:(.*?);/),
            mime = mimeMatch ? mimeMatch[1] : 'image/png',
            bstr = atob(arr[1]),
            n = bstr.length,
            u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        var ext = (mime.split('/')[1] || 'png').replace('+xml', '');
        if (!filename.includes('.')) filename += '.' + ext;
        return new File([u8arr], filename, { type: mime });
    }

    function extractImagesFromRtf(rtfData) {
        if (!rtfData) return [];
        var images = [];
        var imageGroupRegex = /\{\\pict[\s\S]+?\}/g;
        var matches = rtfData.match(imageGroupRegex);
        if (!matches) return images;

        for (var i = 0; i < matches.length; i++) {
            var group = matches[i];
            var mimeType = 'image/png';
            if (group.indexOf('\\jpegblip') !== -1) mimeType = 'image/jpeg';
            else if (group.indexOf('\\pngblip') !== -1) mimeType = 'image/png';

            var hexMatch = group.match(/(?:\\pngblip|\\jpegblip|\\emfblip|\\wmetafile\d+)\s+([0-9a-fA-F\s\r\n]+)/);
            if (!hexMatch) {
                hexMatch = group.match(/\\pict[^{}]*?\s+([0-9a-fA-F\s\r\n]+)/);
            }
            if (hexMatch && hexMatch[1]) {
                var hex = hexMatch[1].replace(/\s+/g, '');
                if (hex.length > 0 && hex.length % 2 === 0) {
                    try {
                        var binary = '';
                        for (var j = 0; j < hex.length; j += 2) {
                            binary += String.fromCharCode(parseInt(hex.substr(j, 2), 16));
                        }
                        var base64 = btoa(binary);
                        images.push('data:' + mimeType + ';base64,' + base64);
                    } catch(e) {}
                }
            }
        }
        return images;
    }

    var SN_LOADING_SVG = "data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22120%22%20height%3D%2280%22%3E%3Crect%20width%3D%22100%25%22%20height%3D%22100%25%22%20fill%3D%22%23f3f4f6%22%20rx%3D%226%22%2F%3E%3Ctext%20x%3D%2250%25%22%20y%3D%2250%25%22%20dominant-baseline%3D%22middle%22%20text-anchor%3D%22middle%22%20font-size%3D%2211%22%20fill%3D%22%239ca3af%22%20font-family%3D%22sans-serif%22%3E%E2%8F%B3%20Mengunggah...%3C%2Ftext%3E%3C%2Fsvg%3E";

    function cleanWordHtml(html) {
        if (!html) return '';

        var clean = html
            .replace(/<!--[\s\S]*?-->/gi, '')
            .replace(/<xml[\s\S]*?<\/xml>/gi, '')
            .replace(/<style[\s\S]*?<\/style>/gi, '')
            .replace(/<script[\s\S]*?<\/script>/gi, '')
            .replace(/<meta[^>]*>/gi, '')
            .replace(/<link[^>]*>/gi, '')
            .replace(/<\/?\w+:[^>]*>/gi, '');

        var container = document.createElement('div');
        container.innerHTML = clean;

        function sanitizeNode(node) {
            if (!node) return;
            if (node.nodeType === 1) {
                var tagName = node.tagName.toLowerCase();

                if (tagName === 'font' || tagName === 'center' || tagName === 'o:p' || tagName === 'w:sdt') {
                    var parent = node.parentNode;
                    if (parent) {
                        while (node.firstChild) {
                            parent.insertBefore(node.firstChild, node);
                        }
                        parent.removeChild(node);
                    }
                    return;
                }

                var attrs = Array.from(node.attributes);
                for (var i = 0; i < attrs.length; i++) {
                    var attrName = attrs[i].name.toLowerCase();
                    if (tagName === 'img') {
                        if (attrName !== 'src' && attrName !== 'id' && attrName !== 'data-uploading' && attrName !== 'alt' && attrName !== 'title') {
                            node.removeAttribute(attrs[i].name);
                        }
                    } else if (tagName === 'a') {
                        if (attrName !== 'href' && attrName !== 'target') {
                            node.removeAttribute(attrs[i].name);
                        }
                    } else {
                        node.removeAttribute(attrs[i].name);
                    }
                }

                if (tagName === 'div') {
                    var p = document.createElement('p');
                    while (node.firstChild) {
                        p.appendChild(node.firstChild);
                    }
                    if (node.parentNode) {
                        node.parentNode.replaceChild(p, node);
                        node = p;
                    }
                }

                if (tagName === 'span' && node.attributes.length === 0) {
                    var parentSpan = node.parentNode;
                    if (parentSpan) {
                        while (node.firstChild) {
                            parentSpan.insertBefore(node.firstChild, node);
                        }
                        parentSpan.removeChild(node);
                        return;
                    }
                }

                var children = Array.from(node.childNodes);
                for (var j = 0; j < children.length; j++) {
                    sanitizeNode(children[j]);
                }
            }
        }

        var rootNodes = Array.from(container.childNodes);
        for (var k = 0; k < rootNodes.length; k++) {
            sanitizeNode(rootNodes[k]);
        }

        return container.innerHTML;
    }

    function getFileCacheKey(file, base64Src) {
        if (base64Src) {
            var len = base64Src.length;
            if (len < 500) return base64Src;
            var mid = Math.floor(len / 2);
            return 'b64_' + len + '_' + base64Src.substring(0, 80) + '_' + base64Src.substring(mid, mid + 80) + '_' + base64Src.substring(len - 80);
        }
        if (file) {
            return 'file_' + file.size + '_' + file.type + '_' + (file.name || '');
        }
        return null;
    }

    var base64UploadCache = {};

    function uploadSummernoteImage(file, successCallback, errorCallback, base64Src) {
        var cacheKey = getFileCacheKey(file, base64Src);

        if (cacheKey && base64UploadCache[cacheKey]) {
            var cached = base64UploadCache[cacheKey];
            if (typeof cached === 'string') {
                if (typeof successCallback === 'function') successCallback(cached);
                return Promise.resolve(cached);
            } else if (cached && typeof cached.then === 'function') {
                return cached.then(function(url) {
                    if (url && typeof successCallback === 'function') successCallback(url);
                    return url;
                });
            }
        }

        let formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');
        @if(isset($edit) && isset($edit->post_id))
            formData.append('post', '{{ $edit->post_id }}');
        @elseif(isset($post) && isset($post->id))
            formData.append('post', '{{ $post->id }}');
        @endif

        var promise = $.ajax({
            url: "{{ route('upload_image_summernote') }}",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false
        }).then(function(res) {
            if (res && res.status === 'success' && res.url) {
                if (cacheKey) base64UploadCache[cacheKey] = res.url;
                if (typeof successCallback === 'function') successCallback(res.url);
                return res.url;
            } else {
                if (cacheKey) delete base64UploadCache[cacheKey];
                if (typeof errorCallback === 'function') errorCallback(res);
            }
        }, function(err) {
            if (cacheKey) delete base64UploadCache[cacheKey];
            if (typeof errorCallback === 'function') errorCallback(err);
        });

        if (cacheKey) {
            base64UploadCache[cacheKey] = promise;
        }

        return promise;
    }

    var _tblStyleTarget = { table: null, tr: null, td: null };
    let currentImage = null;

    $(document).ready(function () {
        $(document).on('mousedown', '.note-editable img', function () {
            $('.note-editable img').removeClass('selected-img');
            $(this).addClass('selected-img');
            currentImage = $(this);
        });

        $('form').on('submit', function () {
            if ($('#editor').length) {
                var code = $('#editor').summernote('code');
                if (code && code.includes('data:image/')) {
                    var cleanCode = code
                        .replace(/<img[^>]*src=["']data:image\/svg\+xml[^"']*["'][^>]*\/?>/gi, '')
                        .replace(/<img[^>]*src=["']data:image\/[^"']+["'][^>]*\/?>/gi, '');
                    $('#editor').summernote('code', cleanCode);
                }
            }
        });

        let firstRequest = true;

        function aiButton(context) {
            var ui = $.summernote.ui;
            return ui.button({
                contents: '<i class="note-icon-magic"></i> AI Generate',
                tooltip: 'Generate Artikel dengan AI',
                click: function () {
                    $('#btnGenerateAI').removeAttr('disabled');
                    $('#btnGenerateAI').text('Generate');
                    var myModal = new bootstrap.Modal(document.getElementById('aiModal'));
                    myModal.show();
                }
            }).render();
        }
        $('#btnGenerateAI').on('click', async function () {
            let prompt = $('#aiPrompt').val().trim();
            if (!prompt) {
                alert("Masukkan prompt terlebih dahulu!");
                return;
            } else {
                $('#btnGenerateAI').attr('disabled', true);
                $('#btnGenerateAI').text('Generating...');
            }

            let current = $('#editor').summernote('code');

            if (!firstRequest) {
                current += "<br><br>";
                $('#editor').summernote('code', current);
            }
            firstRequest = false;

            const resp = await puter.ai.chat(prompt, {
                model: 'gpt-4o-mini',
                stream: true
            });

            let carry = "";
            for await (const part of resp) {
                if (part?.text) {
                    carry += part.text;
                    if ((carry.match(/\*\*/g) || []).length % 2 === 0) {
                        let processed = carry
                            .replaceAll('\n', '<br>')
                            .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
                        current += processed;
                        $('#editor').summernote('code', current);
                        carry = "";
                    }
                }
            }

            if (carry) {
                let processed = carry
                    .replaceAll('\n', '<br>')
                    .replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');
                current += processed;
                $('#editor').summernote('code', current);
                updateSummernoteCounter();
            }
            $('#aiModal').hide();
            $('.modal-backdrop').hide();
            
            updateSummernoteCounter();
        });

        let defaultPlaceholder = 'Tuliskan keterangan tentang {{ current_module()->datatable->data_title }} disini...';
        let initialTitle = $('[name="title"]').val();
        let initPlaceholder = initialTitle ? 'Tuliskan keterangan tentang ' + initialTitle + ' disini...' : defaultPlaceholder;

        function setSummernoteEditable(editable) {
            var $sn = (window.currentSummernoteObj && window.currentSummernoteObj.context) ? window.currentSummernoteObj.context : $('#editor');
            if ($sn && $sn.length && typeof $sn.summernote === 'function') {
                try {
                    $sn.summernote(editable ? 'enable' : 'disable');
                } catch(e) {}
            }
        }

        function updateSummernoteCounter() {
            var $editor = $('#editor');
            if (!$editor.length) return;

            var $noteEditor = $editor.next('.note-editor');
            var text = '';

            if ($noteEditor.length && $noteEditor.hasClass('codeview')) {
                var codeText = $noteEditor.find('.note-codable').val() || '';
                var temp = document.createElement('div');
                temp.innerHTML = codeText;
                text = temp.innerText || temp.textContent || '';
            } else if ($noteEditor.length && $noteEditor.find('.note-editable').length) {
                var $editable = $noteEditor.find('.note-editable');
                text = $editable[0].innerText || $editable[0].textContent || '';
            } else {
                var code = $editor.val() || '';
                var temp = document.createElement('div');
                temp.innerHTML = code;
                text = temp.innerText || temp.textContent || '';
            }

            // Clean up zero-width spaces and control chars
            var cleanText = text.replace(/[\u200B-\u200D\uFEFF]/g, '').trim();

            if (!cleanText || cleanText === '\n') {
                $('#sn-word-count').text('0');
                $('#sn-char-count').text('0');
                $('#sn-char-no-space-count').text('0');
                $('#sn-reading-time').text('0');
                return;
            }

            var totalChars = cleanText.length;
            var charsNoSpace = cleanText.replace(/\s+/g, '').length;
            var words = cleanText.split(/\s+/).filter(function (w) { return w.length > 0; });
            var wordCount = words.length;
            var readingTime = wordCount > 0 ? Math.ceil(wordCount / 200) : 0;

            $('#sn-word-count').text(wordCount.toLocaleString('id-ID'));
            $('#sn-char-count').text(totalChars.toLocaleString('id-ID'));
            $('#sn-char-no-space-count').text(charsNoSpace.toLocaleString('id-ID'));
            $('#sn-reading-time').text(readingTime);
        }

        $("#editor").summernote({
            placeholder: initPlaceholder,
            height: 600,
            codeviewFilter: true,
            codeviewIframeFilter: false,
            disableDragAndDrop: true,
            callbacks: {
                onInit: function () {
                    var $editor = $('#editor');
                    var $noteEditor = $editor.next('.note-editor');
                    if ($noteEditor.length && !$noteEditor.find('.note-counter-bar').length) {
                        var counterHtml = `
                            <div class="note-counter-bar d-flex justify-content-between align-items-center px-3 py-1 bg-light border-top" style="font-size: 12px; color: #6c757d; user-select: none;">
                                <div class="d-flex align-items-center flex-wrap">
                                    <span class="mr-3 sn-stat-words"><i class="fa fa-file-text-o mr-1"></i> <strong id="sn-word-count">0</strong> Kata</span>
                                    <span class="mr-3 sn-stat-chars"><i class="fa fa-font mr-1"></i> <strong id="sn-char-count">0</strong> Karakter</span>
                                    <span class="mr-3 sn-stat-chars-nospace text-muted">(<strong id="sn-char-no-space-count">0</strong> tanpa spasi)</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="sn-stat-reading-time"><i class="fa fa-clock-o mr-1"></i> Estimasi baca: ~<strong id="sn-reading-time">0</strong> menit</span>
                                </div>
                            </div>
                        `;
                        var $statusbar = $noteEditor.find('.note-statusbar');
                        if ($statusbar.length) {
                            $statusbar.before(counterHtml);
                        } else {
                            $noteEditor.append(counterHtml);
                        }
                    }
                    updateSummernoteCounter();
                },
                onKeyup: function () {
                    updateSummernoteCounter();
                },
                onChange: function (contents, $editable) {
                    updateSummernoteCounter();
                    if ($editable && $editable.length) {
                        $editable.find('img[src^="data:image/"], img[src^="file://"]').each(function() {
                            var $img = $(this);
                            var src = $img.attr('src');
                            if (src.includes('svg+xml') || $img.attr('data-uploading')) return;

                            if (src.startsWith('data:image/')) {
                                $img.attr('data-uploading', '1');
                                setSummernoteEditable(false);
                                try {
                                    var fileObj = dataURLtoFile(src, 'img_' + Math.random().toString(36).substr(2, 7));
                                    uploadSummernoteImage(fileObj, function(uploadedUrl) {
                                        $img.attr('src', uploadedUrl)
                                            .removeAttr('data-uploading')
                                            .removeAttr('height')
                                            .removeAttr('width')
                                            .css({ height: '', width: '' });
                                        setSummernoteEditable(true);
                                    }, function() {
                                        $img.remove();
                                        setSummernoteEditable(true);
                                    }, src);
                                } catch(e) {
                                    $img.remove();
                                    setSummernoteEditable(true);
                                }
                            } else {
                                $img.remove();
                            }
                        });
                    }
                },

                onPaste: function (e) {
                    var event = e.originalEvent || e;
                    var clipboardData = event.clipboardData || window.clipboardData;
                    if (!clipboardData) return;

                    var html = clipboardData.getData('text/html');
                    var rtf = clipboardData.getData('text/rtf');
                    var files = clipboardData.files;
                    var items = clipboardData.items;

                    // Case A: Pure image paste (Snipping tool, screenshot, image file)
                    if ((!html || html.trim().length === 0) && ((files && files.length > 0) || (items && items.length > 0))) {
                        var imageFiles = [];
                        if (files && files.length > 0) {
                            for (var f = 0; f < files.length; f++) {
                                if (files[f].type && files[f].type.startsWith('image/')) imageFiles.push(files[f]);
                            }
                        } else if (items && items.length > 0) {
                            for (var it = 0; it < items.length; it++) {
                                if (items[it].type && items[it].type.startsWith('image/')) {
                                    var blob = items[it].getAsFile();
                                    if (blob) imageFiles.push(blob);
                                }
                            }
                        }

                        if (imageFiles.length > 0) {
                            e.preventDefault();
                            setSummernoteEditable(false);
                            var pureUploadPromises = [];
                            imageFiles.forEach(function(imgFile) {
                                var p = new Promise(function(resolve) {
                                    uploadSummernoteImage(imgFile, function(uploadedUrl) {
                                        var $newImg = $('<img>').attr('src', uploadedUrl);
                                        setSummernoteEditable(true);
                                        if (window.currentSummernoteObj && window.currentSummernoteObj.context) {
                                            window.currentSummernoteObj.context.summernote('insertNode', $newImg[0]);
                                        } else {
                                            $('#editor').summernote('insertNode', $newImg[0]);
                                        }
                                        resolve();
                                    }, function() {
                                        resolve();
                                    });
                                });
                                pureUploadPromises.push(p);
                            });
                            Promise.all(pureUploadPromises).then(function() {
                                setSummernoteEditable(true);
                            }).catch(function() {
                                setSummernoteEditable(true);
                            });
                            return;
                        }
                    }

                    // Case B: HTML Paste (Word Desktop, Word Online, Webpage, Editor)
                    if (html && html.trim().length > 0) {
                        e.preventDefault();

                        var rtfImages = extractImagesFromRtf(rtf);
                        var itemBlobs = [];
                        if (items && items.length > 0) {
                            for (var k = 0; k < items.length; k++) {
                                if (items[k].type && items[k].type.startsWith('image/')) {
                                    var b = items[k].getAsFile();
                                    if (b) itemBlobs.push(b);
                                }
                            }
                        }

                        var cleanedHtml = cleanWordHtml(html);
                        var tempContainer = document.createElement('div');
                        tempContainer.innerHTML = cleanedHtml;
                        var imgElements = tempContainer.querySelectorAll('img');

                        function pasteFinalHtml() {
                            tempContainer.querySelectorAll('img').forEach(function(img) {
                                img.removeAttribute('height');
                                img.removeAttribute('width');
                                img.style.height = '';
                                img.style.width = '';
                            });
                            var finalCleanHtml = tempContainer.innerHTML;
                            setSummernoteEditable(true);
                            if (window.currentSummernoteObj && window.currentSummernoteObj.context) {
                                window.currentSummernoteObj.context.summernote('pasteHTML', finalCleanHtml);
                            } else {
                                $('#editor').summernote('pasteHTML', finalCleanHtml);
                            }
                        }

                        var pendingUploads = [];
                        var fileImgIndex = 0;

                        imgElements.forEach(function(imgEl) {
                            var src = imgEl.getAttribute('src') || '';

                            if (src.startsWith('data:image/')) {
                                try {
                                    var fileObj = dataURLtoFile(src, 'word_img_' + Math.random().toString(36).substr(2, 7));
                                    pendingUploads.push({ imgEl: imgEl, fileObj: fileObj, cacheSrc: src });
                                } catch(err) {
                                    imgEl.remove();
                                }
                            } else if (src.startsWith('file://') || src.includes('file:///')) {
                                var fileObj = null;
                                var cacheSrc = null;

                                if (rtfImages && rtfImages[fileImgIndex]) {
                                    cacheSrc = rtfImages[fileImgIndex];
                                    fileObj = dataURLtoFile(cacheSrc, 'word_img_' + Math.random().toString(36).substr(2, 7));
                                } else if (itemBlobs && itemBlobs[fileImgIndex]) {
                                    fileObj = itemBlobs[fileImgIndex];
                                }

                                fileImgIndex++;

                                if (fileObj) {
                                    pendingUploads.push({ imgEl: imgEl, fileObj: fileObj, cacheSrc: cacheSrc });
                                } else {
                                    imgEl.remove();
                                }
                            }
                        });

                        if (pendingUploads.length > 0) {
                            setSummernoteEditable(false);
                            var totalImgs = pendingUploads.length;
                            var completedImgs = 0;
                            var uploadPromises = [];

                            $('#word-paste-progress').remove();
                            var $progressAlert = $('<div id="word-paste-progress" class="alert alert-info d-flex align-items-center justify-content-between mb-2 py-2 px-3" style="font-size: 13px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">' +
                                '<div><i class="fa fa-spinner fa-spin mr-2 text-primary"></i> <span id="word-paste-progress-text">Mengonversi & mengunggah <b>0 / ' + totalImgs + '</b> gambar dari MS Word...</span></div>' +
                                '<div class="progress" style="width: 140px; height: 10px; margin: 0; border-radius: 5px;"><div id="word-paste-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%;"></div></div>' +
                                '</div>');

                            var $targetEditor = window.currentSummernoteObj && window.currentSummernoteObj.context ? window.currentSummernoteObj.context.next('.note-editor') : $('.note-editor').first();
                            if ($targetEditor.length) {
                                $targetEditor.before($progressAlert);
                            }

                            function updateProgress() {
                                completedImgs++;
                                var percent = Math.round((completedImgs / totalImgs) * 100);
                                $('#word-paste-progress-bar').css('width', percent + '%');
                                $('#word-paste-progress-text').html('Mengonversi & mengunggah <b>' + completedImgs + ' / ' + totalImgs + '</b> gambar dari MS Word...');
                            }

                            pendingUploads.forEach(function(item) {
                                var promise = new Promise(function(resolve) {
                                    uploadSummernoteImage(item.fileObj, function(uploadedUrl) {
                                        item.imgEl.setAttribute('src', uploadedUrl);
                                        item.imgEl.removeAttribute('height');
                                        item.imgEl.removeAttribute('width');
                                        item.imgEl.style.height = '';
                                        item.imgEl.style.width = '';
                                        updateProgress();
                                        resolve();
                                    }, function() {
                                        item.imgEl.remove();
                                        updateProgress();
                                        resolve();
                                    }, item.cacheSrc);
                                });
                                uploadPromises.push(promise);
                            });

                            Promise.all(uploadPromises).then(function() {
                                $('#word-paste-progress').removeClass('alert-info').addClass('alert-success').html('<i class="fa fa-check-circle mr-2 text-success"></i> <b>' + totalImgs + ' gambar dari MS Word berhasil dikonversi & diunggah!</b>');
                                setTimeout(function() {
                                    $('#word-paste-progress').fadeOut(400, function() { $(this).remove(); });
                                }, 3000);

                                if (typeof notif === 'function') {
                                    notif(totalImgs + ' gambar dari Word berhasil dikonversi & diunggah!', 'success');
                                }

                                pasteFinalHtml();
                            }).catch(function() {
                                $('#word-paste-progress').fadeOut(300, function() { $(this).remove(); });
                                pasteFinalHtml();
                            });
                        } else {
                            pasteFinalHtml();
                        }
                    }
                },

                onImageUpload: function(files) {
                    if (window.isPastingHtml) return;
                    if (files && files.length > 0) {
                        setSummernoteEditable(false);
                        var uploadPromises = [];
                        for (var i = 0; i < files.length; i++) {
                            (function(file) {
                                var p = new Promise(function(resolve) {
                                    uploadSummernoteImage(file, function(uploadedUrl) {
                                        var $newImg = $('<img>').attr('src', uploadedUrl);
                                        setSummernoteEditable(true);
                                        if (window.currentSummernoteObj && window.currentSummernoteObj.context) {
                                            window.currentSummernoteObj.context.summernote('insertNode', $newImg[0]);
                                        } else {
                                            $('#editor').summernote('insertNode', $newImg[0]);
                                        }
                                        resolve();
                                    }, function() {
                                        resolve();
                                    });
                                });
                                uploadPromises.push(p);
                            })(files[i]);
                        }
                        Promise.all(uploadPromises).then(function() {
                            setSummernoteEditable(true);
                        }).catch(function() {
                            setSummernoteEditable(true);
                        });
                    }
                },

                onMediaDelete: function (target) {
                    var img = $(target).is('img') ? $(target) : $(target).find('img');

                    if (img.length > 0) {
                        var src = img.attr('src');
                        if (src.startsWith('https')) {
                            return;
                        } else {
                            deleteImage(src);

                        }
                        removeFigure(target);
                    } else { }
                },


            },

            lang: 'en-EN',
            popover: {
                video: [
                    ['custom', ['editEmbed']],
                    ['remove', ['removeVideo']]
                ],
                image: [
                    ['image', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['custom', ['editImage']],
                    ['remove', ['removeMedia']],
                ],
                link: [
                    ['link', ['linkDialogShow', 'unlink']],
                    ['custom', ['removeFile']]
                ],
                table: [
                    ['add', ['addRowDown', 'addRowUp', 'addColLeft', 'addColRight']],
                    ['delete', ['deleteRow', 'deleteCol', 'deleteTable']],
                    ['custom', ['tableProps', 'addParagraphBelow']],
                ]
            },
            toolbar: [
                @if(function_exists('current_module') && isset(current_module()?->form?->editor_mode) && current_module()?->form?->editor_mode == 'simple')
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['para', ['paragraph']],
                    ['view', ['fullscreen', 'codeview']],
                @else
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontname', ['fontname']],
                    ['height', ['height']],
                    ['color', ['color']],
                    ['para', ['paragraph', 'ul', 'ol']],
                    ['insert', ['link', 'video', 'hr']],
                    ['embed', ['embedUrl']],
                    ['table', ['table']],
                    ['view', ['fullscreen', 'codeview']],
                    ['custom', ['aiGenerate']],
                @endif
            ],


            buttons: {
                editEmbed: function (context) {
                    var ui = $.summernote.ui;
                    var button = ui.button({
                        contents: '<i class="fa fa-pencil"></i> Edit Embed',
                        tooltip: 'Edit Embed',
                        click: function () {
                            var $node = $(context.invoke('restoreTarget') || window.getSelection().anchorNode);
                            var $iframe = $node.is('iframe') ? $node : $node.closest('iframe');
                            if (!$iframe.length) $iframe = $(context.invoke('restoreTarget'));
                            if ($iframe.length && $iframe.is('iframe')) {
                                $('#embed-url').val($iframe.attr('src'));
                                $('#embed-width').val($iframe[0].style.width || $iframe.attr('width'));
                                $('#embed-height').val($iframe[0].style.height || $iframe.attr('height'));
                                $('#embed-border').val($iframe.css('border') || '');

                                // Store the active iframe so we can update it instead of inserting a new one
                                window.activeSummernoteIframe = $iframe;

                                $('#embedModal').modal('show');
                            }
                        }
                    });
                    return button.render();
                },
                aiGenerate: aiButton,
                embedUrl: function () {
                    var ui = $.summernote.ui;
                    var button = ui.button({
                        contents: '<i class="fa fa-globe"></i>',
                        tooltip: 'Embed URL',
                        click: function () {
                            $('#editor').summernote('editor.saveRange');
                            $('#embedModal').modal('show');
                        }
                    });
                    return button.render();
                },
                tableProps: function (context) {
                    var ui = $.summernote.ui;
                    return ui.button({
                        contents: '<i class="fa fa-cogs"></i> Properti',
                        tooltip: 'Ubah Properti Tabel / Baris / Sel',
                        click: function () {
                            var anchor = window.getSelection().anchorNode;
                            var $td = $(anchor).closest('td, th');
                            var $tr = $(anchor).closest('tr');
                            var $table = $(anchor).closest('table');
                            if (!$table.length) return;

                            _tblStyleTarget = { table: $table, tr: $tr.length ? $tr : null, td: $td.length ? $td : null };

                            /* Populate Table tab */
                            $('#tblStyleWidth').val($table[0].style.width || '');
                            $('#tblStyleBorder').val($table[0].style.border || '');
                            var tableExtra = $table.attr('style') || '';
                            tableExtra = tableExtra.replace(/width\s*:[^;]+;?/gi, '').replace(/border\s*:[^;]+;?/gi, '').trim();
                            $('#tblStyleExtra').val(tableExtra);

                            /* Populate TR tab */
                            if ($tr.length) {
                                $('#trStyleBg').val($tr[0].style.background || $tr[0].style.backgroundColor || '');
                                var trExtra = $tr.attr('style') || '';
                                trExtra = trExtra.replace(/background[^;]*;?/gi, '').trim();
                                $('#trStyleExtra').val(trExtra);
                            } else {
                                $('#trStyleBg, #trStyleExtra').val('');
                            }

                            /* Populate TD tab */
                            if ($td.length) {
                                $('#tdStyleWidth').val($td[0].style.width || '');
                                $('#tdStyleBg').val($td[0].style.background || $td[0].style.backgroundColor || '');
                                $('#tdStyleAlign').val($td[0].style.textAlign || '');
                                $('#tdStyleVAlign').val($td[0].style.verticalAlign || '');
                                $('#tdStyleBorder').val($td[0].style.border || '');
                                $('#tdStylePadding').val($td[0].style.padding || '');
                                var tdExtra = $td.attr('style') || '';
                                tdExtra = tdExtra.replace(/width\s*:[^;]+;?/gi, '').replace(/background[^;]*;?/gi, '').replace(/text-align\s*:[^;]+;?/gi, '').replace(/vertical-align\s*:[^;]+;?/gi, '').replace(/border\s*:[^;]+;?/gi, '').replace(/padding\s*:[^;]+;?/gi, '').trim();
                                $('#tdStyleExtra').val(tdExtra);
                            } else {
                                $('#tdStyleWidth, #tdStyleBg, #tdStyleBorder, #tdStylePadding, #tdStyleExtra').val('');
                                $('#tdStyleAlign, #tdStyleVAlign').val('');
                            }

                            $('#tableStyleTabs a:first').tab('show');
                            $('#tableStyleModal').modal('show');
                        }
                    }).render();
                },
                addParagraphBelow: function (context) {
                    var ui = $.summernote.ui;
                    return ui.button({
                        contents: '<i class="fa fa-level-down"></i> Baris Baru',
                        tooltip: 'Tambah paragraf di bawah tabel',
                        click: function () {
                            var $table = $(window.getSelection().anchorNode).closest('table');
                            if ($table.length) {
                                var $p = $('<p><br></p>');
                                $table.after($p);

                                var range = document.createRange();
                                var sel = window.getSelection();
                                range.setStart($p[0], 0);
                                range.collapse(true);
                                sel.removeAllRanges();
                                sel.addRange(range);
                            }
                        }
                    }).render();
                },

                removeFile: function () {
                    var ui = $.summernote.ui;

                    return ui.button({
                        contents: '<i class="fa fa-trash"></i>',
                        tooltip: 'Hapus File',

                        click: function () {

                            let link = window.getSelection().anchorNode;

                            if (!link) return;

                            let $link = $(link).closest('a');

                            if (!$link.length) {
                                alert('Bukan link');
                                return;
                            }

                            let href = $link.attr('href');

                            let fileExt = href.split('.').pop().toLowerCase();
                            let allowed = @json(flc_ext());

                            if (!allowed.includes(fileExt)) {
                                alert('Hanya untuk hapus link file');
                                return;
                            }

                            $.post("{{ route('media.destroy') }}", {
                                media: href,
                                _token: "{{ csrf_token() }}"
                            });

                            $link.remove();
                        }
                    }).render();
                },

                editImage: function () {
                    var ui = $.summernote.ui;

                    return ui.button({
                        contents: '<i class="fa fa-edit"></i>',
                        tooltip: 'Edit Image',

                        click: function () {

                            if (!currentImage || !currentImage.length) {
                                alert('Klik gambar dulu');
                                return;
                            }

                            let src = currentImage.attr('src') || '';
                            let alt = currentImage.attr('alt') || '';
                            let caption = currentImage.closest('figure').find(
                                'figcaption').text().trim();

                            $('#edit-image-url').val(src);
                            $('#edit-image-alt').val(alt);
                            $('#edit-image-caption').val(caption);

                            new bootstrap.Modal(document.getElementById(
                                'editImageModal')).show();
                        }
                    }).render();
                }
            },
            tableClassName: function () {
                $(this).addClass('table table-bordered table-hover')

                    .attr('cellpadding', 12)
                    .attr('cellspacing', 0)
                    .attr('border', 1)
                    .css('borderCollapse', 'collapse');

                $(this).find('td')
                    .css('borderColor', '#ccc')
                    .css('padding', '5px');
            },
        });

        $('[name="title"]').on('input', function() {
            let val = $(this).val();
            let newPlaceholder = val ? 'Tuliskan keterangan tentang ' + val + ' disini...' : defaultPlaceholder;
            $('#editor').next('.note-editor').find('.note-placeholder').html(newPlaceholder);
        });

        $(document).on('input keyup change paste', '.note-editable, .note-codable', function () {
            updateSummernoteCounter();
        });

        $('#btnSaveImageEdit').on('click', function () {


            if (!currentImage || !currentImage.length) return;

            let url = $('#edit-image-url').val().trim();
            let alt = $('#edit-image-alt').val().trim();
            let caption = $('#edit-image-caption').val().trim();

            if (url) currentImage.attr('src', url);
            currentImage.attr('alt', alt);

            let figure = currentImage.closest('figure');

            if (figure.length) {

                let cap = figure.children('figcaption');

                if (cap.length) {
                    cap.html(`<small>${caption}</small>`);
                } else {
                    figure.append(`<figcaption><small>${caption}</small></figcaption>`);
                }
            }

            $('#editImageModal').hide();

            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });

        /* Save Table Style Modal */
        $('#btnSaveTableStyle').on('click', function () {
            var t = _tblStyleTarget;
            if (!t.table) return;

            /* Build style string helper */
            function buildStyle(parts) {
                return parts.filter(function (p) { return p; }).join(' ').replace(/;?\s*$/, '').trim();
            }

            /* === TABLE === */
            var tblParts = [];
            var w = $('#tblStyleWidth').val().trim();
            if (w) tblParts.push('width:' + w + ';');
            var b = $('#tblStyleBorder').val().trim();
            if (b) tblParts.push('border:' + b + ';');
            var ex = $('#tblStyleExtra').val().trim();
            if (ex) tblParts.push(ex);
            var tblStyle = buildStyle(tblParts);
            if (tblStyle) {
                t.table.attr('style', tblStyle);
            } else {
                t.table.removeAttr('style');
            }

            /* === TR === */
            if (t.tr) {
                var trParts = [];
                var trBg = $('#trStyleBg').val().trim();
                if (trBg) trParts.push('background:' + trBg + ';');
                var trEx = $('#trStyleExtra').val().trim();
                if (trEx) trParts.push(trEx);
                var trStyle = buildStyle(trParts);
                if (trStyle) {
                    t.tr.attr('style', trStyle);
                } else {
                    t.tr.removeAttr('style');
                }
            }

            /* === TD === */
            if (t.td) {
                var tdParts = [];
                var tdW = $('#tdStyleWidth').val().trim();
                if (tdW) tdParts.push('width:' + tdW + ';');
                var tdBg = $('#tdStyleBg').val().trim();
                if (tdBg) tdParts.push('background:' + tdBg + ';');
                var tdA = $('#tdStyleAlign').val();
                if (tdA) tdParts.push('text-align:' + tdA + ';');
                var tdVA = $('#tdStyleVAlign').val();
                if (tdVA) tdParts.push('vertical-align:' + tdVA + ';');
                var tdB = $('#tdStyleBorder').val().trim();
                if (tdB) tdParts.push('border:' + tdB + ';');
                var tdP = $('#tdStylePadding').val().trim();
                if (tdP) tdParts.push('padding:' + tdP + ';');
                var tdEx = $('#tdStyleExtra').val().trim();
                if (tdEx) tdParts.push(tdEx);
                var tdStyle = buildStyle(tdParts);
                if (tdStyle) {
                    t.td.attr('style', tdStyle);
                } else {
                    t.td.removeAttr('style');
                }
            }

            $('#tableStyleModal').modal('hide');
        });

    });

    function deleteImage(src) {
        var data = new FormData();
        data.append("media", src);
        data.append("_token", "{{ csrf_token() }}");
        $.ajax({
            data: data,
            type: "POST",
            url: "{{ route('media.destroy') }}",
            contentType: false,
            processData: false,
            success: function (response) {
                console.log(response);
            }
        });
    }
    $('#embedModalSave').click(function () {
        var url = $('#embed-url').val();
        var width = $('#embed-width').val() || '100%';
        var height = $('#embed-height').val() || '400px';
        var styleAttr = $('#embed-style-attr').val() || '';

        if (url) {
            if (window.activeSummernoteIframe && window.activeSummernoteIframe.length) {
                // Updating existing iframe
                var $iframe = window.activeSummernoteIframe;
                $iframe.attr('src', url);

                // Reset style and apply new styles
                $iframe.attr('style', '');
                $iframe.css({
                    'width': width,
                    'height': height
                });

                if (styleAttr) {
                    var currentStyle = $iframe.attr('style') || '';
                    $iframe.attr('style', currentStyle + (currentStyle.endsWith(';') ? ' ' : '; ') + styleAttr);
                }

                window.activeSummernoteIframe = null; // reset
                $('#embed-custom-popover').hide();
            } else {
                // Inserting new iframe
                $('#editor').summernote('editor.restoreRange');
                $('#editor').summernote('editor.focus');

                var iframeNode = document.createElement('iframe');
                iframeNode.src = url;
                $(iframeNode).css({
                    'width': width,
                    'height': height
                });

                if (styleAttr) {
                    var currentStyle = iframeNode.getAttribute('style') || '';
                    iframeNode.setAttribute('style', currentStyle + (currentStyle.endsWith(';') ? ' ' : '; ') + styleAttr);
                }

                iframeNode.setAttribute('frameborder', '0');
                iframeNode.setAttribute('allowfullscreen', 'true');

                $('#editor').summernote('insertNode', iframeNode);

                // Add a small paragraph after it so the user can continue typing
                var pNode = document.createElement('p');
                pNode.innerHTML = '<br>';
                $('#editor').summernote('insertNode', pNode);
            }

            $('#embedModal').modal('hide');
            $('#embed-url').val('');
            $('#embed-width').val('');
            $('#embed-height').val('');
            $('#embed-style-attr').val('');
        } else {
            alert("Please enter a URL.");
        }
    });

    // Custom Popover Logic for Iframes
    $(document).ready(function () {
        var $customPopover = $('<div id="embed-custom-popover" class="note-popover popover in note-video-popover bottom" style="display: none; position: absolute; z-index: 1060; background: #fff; border: 1px solid rgba(0,0,0,.2); padding: 5px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,.2);"><div class="arrow" style="left: 50%; margin-left: -5px; top: -11px; border-bottom-color: #fff; border-width: 0 5px 5px;"></div><div class="popover-content note-children-container"><div class="note-btn-group btn-group note-custom"><button type="button" class="note-btn btn btn-light btn-sm" id="btn-edit-embed" title="Edit Embed" style="margin-right:2px;"><i class="fa fa-pencil"></i> Edit</button><button type="button" class="note-btn btn btn-danger btn-sm" id="btn-remove-embed" title="Remove Embed"><i class="fa fa-trash"></i></button></div></div></div>');
        $('body').append($customPopover);

        $(document).on('mousedown', '.note-editable', function (e) {
            var $iframes = $(this).find('iframe');
            var clickedIframe = null;
            $iframes.each(function () {
                var rect = this.getBoundingClientRect();
                if (e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom) {
                    clickedIframe = this;
                    return false; // break loop
                }
            });

            if (clickedIframe) {
                var $iframe = $(clickedIframe);
                window.activeSummernoteIframe = $iframe;
                var rect = clickedIframe.getBoundingClientRect();

                $customPopover.css({
                    display: 'block',
                    left: rect.left + window.scrollX + (rect.width / 2) - ($customPopover.outerWidth() / 2),
                    top: rect.bottom + window.scrollY + 5
                });
            } else {
                $customPopover.hide();
            }
        });

        // Hide popover if clicking outside
        $(document).on('mousedown', function (e) {
            if (!$(e.target).closest('#embed-custom-popover').length && !$(e.target).closest('.note-editable').length) {
                $('#embed-custom-popover').hide();
            }
        });

        $('#btn-edit-embed').click(function () {
            var $iframe = window.activeSummernoteIframe;
            if ($iframe && $iframe.length) {
                $('#embed-url').val($iframe.attr('src'));
                $('#embed-width').val($iframe[0].style.width || $iframe.attr('width') || '');
                $('#embed-height').val($iframe[0].style.height || $iframe.attr('height') || '');

                // Extract custom styles (excluding width and height which are handled separately)
                var fullStyle = $iframe.attr('style') || '';
                var styles = fullStyle.split(';').map(s => s.trim()).filter(s => s.length > 0);
                var customStyles = [];
                for (var i = 0; i < styles.length; i++) {
                    var parts = styles[i].split(':').map(s => s.trim());
                    if (parts[0] !== 'width' && parts[0] !== 'height') {
                        customStyles.push(styles[i]);
                    }
                }

                $('#embed-style-attr').val(customStyles.length > 0 ? customStyles.join('; ') + ';' : '');
                $('#embedModal').modal('show');
            }
        });

        $('#btn-remove-embed').click(function () {
            if (window.activeSummernoteIframe && window.activeSummernoteIframe.length) {
                window.activeSummernoteIframe.remove();
                $('#embed-custom-popover').hide();
                window.activeSummernoteIframe = null;
            }
        });
    });
</script>

<!-- Modal -->