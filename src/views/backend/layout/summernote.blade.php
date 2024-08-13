<div class="modal fade" id="embedModal" tabindex="-1" role="dialog" aria-labelledby="embedModalLabel" aria-hidden="true">
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
            <input type="text" class="form-control" id="embed-width" placeholder="Sample : 100%, 100px or other">
          </div>
          <div class="form-group">
            <label for="embed-height">Height</label>
            <input type="text" class="form-control" id="embed-height" placeholder="Sample : 100%, 100px or other">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="embedModalSave">Save changes</button>
        </div>
      </div>
    </div>
  </div>

<script src="{{ asset('backend/js/summernote-image-attributes.js') }}"></script>
<script src="{{ asset('backend/js/en-us.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $("#editor").summernote({
            placeholder: 'Tulis isi..',
            height: 350,
            codeviewFilter: false,
            codeviewIframeFilter: true,
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
            lang: 'en-EN',
            popover: {
                image: [
                    ['image', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
                    ['float', ['floatLeft', 'floatRight', 'floatNone']],
                    ['remove', ['removeMedia']],
                    ['custom', ['imageAttributes']],
                ],
                link: [
    ['link', ['linkDialogShow', 'unlink']]
  ],
  table: [
    ['add', ['addRowDown', 'addRowUp', 'addColLeft', 'addColRight']],
    ['delete', ['deleteRow', 'deleteCol', 'deleteTable']],
  ]
            },
            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['fontsize', ['fontsize']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['fontname', ['fontname']],
                ['height', ['height']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['picture', 'link', 'video', 'hr','embedUrl']],
                ['table', ['table']],
                ['view', ['fullscreen', 'help','codeview']],
        ],

        buttons: {
            embedUrl: function() {
                var ui = $.summernote.ui;
                var button = ui.button({
                    contents: '<i class="fa fa-globe"/></i> Embed URL',
                    tooltip: 'Embed URL',
                    click: function() {
                        $('#embedModal').modal('show');
                    }
                });
                return button.render();
            }
        },
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
    $('#embedModalSave').click(function() {
        var url = $('#embed-url').val();
        var width = $('#embed-width').val();
        var height = $('#embed-height').val();

        if (url && width && height) {
            var iframeHTML = `
                <iframe src="${url}" style="width:${width};height:${height}" frameborder="0" allowfullscreen></iframe>
            `;
            $('#editor').summernote('pasteHTML', iframeHTML);
            $('#embedModal').modal('hide');
        } else {
            alert("Please fill out all fields.");
        }
    });
</script>
<!-- Modal -->

