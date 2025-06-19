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

<script type="text/javascript">
    $(document).ready(function() {

        $("#editor").summernote({
            placeholder: 'Tulis isi..',
            height: 350,
            codeviewFilter: true,
            codeviewIframeFilter: true,
            callbacks: {
      onChange: function(contents, $editable) {
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
                onImageUpload: function(files) {
                    uploadImage(files[0]);
                },
                onMediaDelete: function(target) {
                var img = $(target).is('img') ? $(target) : $(target).find('img');

                if (img.length > 0) {
                    var src = img.attr('src');
                    if (src.startsWith('https')) {
                        return;
                    }else{
                    deleteImage(src);

                    }
                    removeFigure(target);
                } else {
                }
            },


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
        async function compressToWebP(file, quality = 0.3) {
            const imageBitmap = await createImageBitmap(file);
            const canvas = document.createElement('canvas');
            canvas.width = imageBitmap.width;
            canvas.height = imageBitmap.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(imageBitmap, 0, 0);

            const blob = await new Promise(resolve =>
                canvas.toBlob(resolve, 'image/webp', quality)
            );

            const newFileName = file.name.replace(/\.[^/.]+$/, '') + '.webp';
            return new File([blob], newFileName, { type: 'image/webp' });
        }
   /* function uploadImage(file) {
        if (file) {
            var allowedTypes = ['image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Pilih hanya format gambar: jpg atau png.');
            }else{
        var data = new FormData();
        data.append("file", file);
        data.append("post","{{ $post?->id }}");
        data.append("_token", "{{ csrf_token() }}");
        $.ajax({
            url: "{{ route('upload_image_summernote') }}",
            type: 'POST',
            data: data,
            contentType: false,
            processData: false,
            success: function(response) {
                var actualImageUrl = response.url;
                var figureHTML = `
                        <figure style="text-align: center; margin: 10px 0;">
                            <img src="${actualImageUrl}" style="max-width: 100%; height: auto;">
                            <figcaption style="font-style: italic; color: #666;"><small>Ilustrasi Gambar Disini</small></figcaption>
                        </figure>
                    `;
                $('#editor').summernote("pasteHTML", figureHTML);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error uploading image: ', textStatus, errorThrown);
            }
        });
            }
        }
    }
        */
    

        async function uploadImage(file) {
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Pilih hanya format gambar: JPG atau PNG.');
                return;
            }

            try {
                const compressedFile = await compressToWebP(file);

                const data = new FormData();
                data.append("file", compressedFile);
                data.append("post", "{{ $post?->id }}");
                data.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('upload_image_summernote') }}",
                    type: 'POST',
                    data: data,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        const actualImageUrl = response.url;
                        const figureHTML = `
                        <figure style="text-align: center; margin: 10px 0;">
                            <img src="${actualImageUrl}" style="max-width: 100%; height: auto;">
                            <figcaption style="font-style: italic; color: #666;">
                                <small>Ilustrasi Gambar Disini</small>
                            </figcaption>
                        </figure>`;
                        $('#editor').summernote("pasteHTML", figureHTML);
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error('Error uploading image: ', textStatus, errorThrown);
                    }
                });
            } catch (err) {
                console.error('Compress error:', err);
                alert('Gagal mengompres gambar.');
            }
        }

    function removeFigure(target) {
    var figure = $(target).closest('figure');
    if (figure.length > 0) {
        figure.remove();
    } else {
    }
}
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

