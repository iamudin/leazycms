<script src="{{ asset('backend/js/summernote-image-attributes.js') }}"></script>
<script src="{{ asset('backend/js/en-us.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $("#editor").summernote({
            placeholder: 'Tulis isi..',
            height: 350,
            callbacks: {
                onFileUpload: function(file) {
                    fileupload(files[0]);
                },
                onImageUpload: function(files) {
                    uploadImage(files[0]);
                },
                onMediaDelete: function(target) {
                    deleteImage(target[0].src);
                },


            },

            imageAttributes: {
                icon: '<i class="note-icon-pencil"></i>',
                figureClass: 'figureClass',
                figcaptionClass: 'captionClass',
                captionText: 'Caption Goes Here.',
                manageAspectRatio: false
            },
            lang: 'en-US',
            popover: {
                image: [
                    ['image', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']],
                    ['custom', ['imageAttributes']],
                ],
            },
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontname', ['fontname']],
                ['height', ['height']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['picture', 'link', 'video', 'hr']],
                ['table', ['table']],
                ['view', ['fullscreen', 'help', 'codeview']],
            ],
            tableClassName: function() {
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
    });

    function uploadImage(file) {
        if (file) {
            var allowedTypes = ['image/jpeg', 'image/png','image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Pilih hanya format gambar: jpg,png atau gif.');
            }else{
                var placeholderImageUrl = '/backend/images/load.gif';
        $('#editor').summernote('editor.insertImage', placeholderImageUrl);
        var data = new FormData();
        data.append("file", file);
        data.append("_token", "{{ csrf_token() }}");
        $.ajax({
            url: "{{ route('media.imagesummernoteupload') }}",
            type: 'POST',
            data: data,
            contentType: false,
            processData: false,
            success: function(response) {
                var actualImageUrl = response.url;
                var alls = $('#editor').summernote("code");
                $('#editor').summernote("code", alls.replace(placeholderImageUrl, actualImageUrl));

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error uploading image: ', textStatus, errorThrown);
            }
        });
            }
        }



    }

    function deleteImage(src) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        $.ajax({
            data: {
                media: src
            },
            type: "POST",
            url: "{{ route('media.destroy') }}",
            cache: false,
            success: function(response) {
                console.log(response);
            }
        });
    }
</script>
