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
    var _tblStyleTarget = { table: null, tr: null, td: null };
    let currentImage = null;

    $(document).ready(function () {
        $(document).on('mousedown', '.note-editable img', function () {
            $('.note-editable img').removeClass('selected-img');
            $(this).addClass('selected-img');
            currentImage = $(this);
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
            }
            $('#aiModal').hide();
            $('.modal-backdrop').hide();
        });

        $("#editor").summernote({
            placeholder: 'Tulis isi..',
            height: 600,
            codeviewFilter: true,
            codeviewIframeFilter: false,
            disableDragAndDrop: true,
            callbacks: {
                onChange: function (contents, $editable) {
                    let sanitized = contents
                        .replace(/<script[^>]*>.*?<\/script>/gi, '')
                        .replace(/<style[^>]*>.*?<\/style>/gi, '')
                        .replace(/javascript:/gi, '')
                        .replace(/on\w+="[^"]*"/gi, '')
                        .replace(/on\w+='[^']*'/gi, '');

                    if (sanitized !== contents) {
                        $('#editor').summernote('code', sanitized);
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