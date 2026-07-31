<small for="{{_us($r[0])}}">{{$r[0]}}</small>
<textarea id="editor_{{_us($r[0])}}" class="form-control" name="{{_us($r[0])}}">{{ !empty(old(_us($r[0]))) ? old(_us($r[0])) :  (isset($field[_us($r[0])]) && !empty($field[_us($r[0])])  ? $field[_us($r[0])] : '')}}</textarea>

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
                    ]
                });
            }
        } else {
            setTimeout(initSummernote_{{_us($r[0])}}, 50);
        }
    }
    
    // Jalankan inisialisasi
    initSummernote_{{_us($r[0])}}();
</script>
