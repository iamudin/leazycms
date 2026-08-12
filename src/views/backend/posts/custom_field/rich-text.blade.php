<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<textarea id="editor_{{_us($r[0])}}" data-no-media="true" class="form-control" name="{{_us($r[0])}}">{{ !empty(old(_us($r[0]))) ? old(_us($r[0])) :  (isset($field[_us($r[0])]) && !empty($field[_us($r[0])])  ? $field[_us($r[0])] : '')}}</textarea>

@push('styles')
    @if(!defined('SUMMERNOTE_CSS_CUSTOM_FIELD'))
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
        @php define('SUMMERNOTE_CSS_CUSTOM_FIELD', true); @endphp
    @endif
@endpush

@push('scripts')
    @if(!defined('SUMMERNOTE_JS_CUSTOM_FIELD'))
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
        @php define('SUMMERNOTE_JS_CUSTOM_FIELD', true); @endphp
    @endif
@endpush

<script>
    function initSummernote_{{_us($r[0])}}() {
        // Tunggu sampai plugin summernote termuat sepenuhnya (penting untuk load pertama maupun AJAX)
        if (typeof $.fn.summernote !== 'undefined') {
            if ($('#editor_{{_us($r[0])}}').length) {
                // Destroy dulu jika sebelumnya sudah ada (menghindari duplikasi saat AJAX reload)
                $('#editor_{{_us($r[0])}}').summernote('destroy');
                
                $('#editor_{{_us($r[0])}}').summernote({
                    height: 300, // Lebih pendek agar ringkas
                    placeholder: 'Ketik {{$r[0]}} di sini...',
                    disableDragAndDrop: true, // Nonaktifkan fitur drop gambar/file
                    toolbar: [
                        // Tampilan sangat simple
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['paragraph','ul', 'ol' ]],
                        ['insert', ['link']],
                        ['view',['codeview']]
                    ],
                    callbacks: {
                        onPaste: function (e) {
                            var event = e.originalEvent || e;
                            var clipboardData = event.clipboardData || window.clipboardData;
                            if (!clipboardData) return;

                            var html = clipboardData.getData('text/html');
                            if (html && html.trim().length > 0) {
                                e.preventDefault();

                                var clean = html
                                    .replace(/<!--[\s\S]*?-->/gi, '')
                                    .replace(/<xml[\s\S]*?<\/xml>/gi, '')
                                    .replace(/<style[\s\S]*?<\/style>/gi, '')
                                    .replace(/<script[\s\S]*?<\/script>/gi, '')
                                    .replace(/<meta[^>]*>/gi, '')
                                    .replace(/<link[^>]*>/gi, '')
                                    .replace(/<\/?\w+:[^>]*>/gi, '')
                                    .replace(/<img[^>]*\/?>/gi, '');

                                var container = document.createElement('div');
                                container.innerHTML = clean;

                                function sanitizeNode(node) {
                                    if (!node) return;
                                    if (node.nodeType === 1) {
                                        var tagName = node.tagName.toLowerCase();

                                        if (tagName === 'img' || tagName === 'font' || tagName === 'center' || tagName === 'o:p' || tagName === 'w:sdt') {
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
                                            if (tagName === 'a') {
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

                                var finalCleanHtml = container.innerHTML;
                                $('#editor_{{_us($r[0])}}').summernote('pasteHTML', finalCleanHtml);
                            }
                        }
                    }
                });
            }
        } else {
            setTimeout(initSummernote_{{_us($r[0])}}, 50);
        }
    }
    
    // Jalankan inisialisasi
    initSummernote_{{_us($r[0])}}();
</script>
