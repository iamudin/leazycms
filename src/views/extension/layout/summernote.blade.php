<script src="{{asset('backend/js/summernote-image-attributes.js')}}"></script>
<script src="{{asset('backend/js/summernote-file.js')}}"></script>
<script src="{{asset('backend/js/en-us.js')}}"></script>
<script type="text/javascript">
$(document).ready(function() {

    $("#editor").summernote({
      placeholder: 'Tulis isi..',
            height: 600,
            callbacks: {
              onFileUpload: function(file) {
          fileupload(file[0]);
      },
        onMediaDelete: function(target) {
            deleteImage(target[0].src);
        }


    },
    imageAttributes: {
           icon: '<i class="note-icon-pencil"></i>',
         figureClass: 'figureClass',
         figcaptionClass: 'captionClass',
         captionText: 'Caption Goes Here.',
         manageAspectRatio: false // true = Lock the Image Width/Height, Default to true
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
       ['style', ['style','bold', 'italic', 'underline', 'clear']],
        ['fontsize', ['fontsize']],
        ['font', ['strikethrough', 'superscript', 'subscript']],
        ['fontname', ['fontname']],
        ['height', ['height']],
        ['color', ['color']],
        ['para',['ul', 'ol','paragraph']],
        ['table', ['table']],
        ['insert', ['picture','link', 'video','hr','file']],
        ['view', ['fullscreen', 'help','codeview']],
      ],
        tableClassName: function()
{
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

function deleteImage(src) {
  $.ajaxSetup({
   headers: {
       'X-CSRF-TOKEN': '{{csrf_token()}}'
   }
});
          $.ajax({
              data: {src : src},
              type: "POST",
              url: "{{url('/unlink_image')}}",
              cache: false,
              success: function(response) {
                  console.log(response);
              }
          });
      }

      function fileupload(file) {
          let data = new FormData();
          data.append("file", file);
          $.ajaxSetup({
           headers: {
               'X-CSRF-TOKEN': '{{csrf_token()}}'
           }
       });
          $.ajax({
              data: data,
              type: "POST",
              url: "{{admin_url(get_post_type().'/upload_file/'.$edit->post_id)}}",
              cache: false,
              contentType: false,
              processData: false,
              success: function(reponse) {
                  if(reponse.status === true) {
                      let listMimeImg = ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg'];
                      let listMimeAudio = ['audio/mpeg', 'audio/ogg'];
                      let listMimeVideo = ['video/mpeg', 'video/mp4', 'video/webm'];
                      let listMimeOther = ['application/x-zip', 'application/x-zip-compressed', 'application/pdf','application/msword','text/plain','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                      let elem;

                      if (listMimeImg.indexOf(file.type) > -1) {
                          //Picture
                          $('#editor').summernote('editor.insertImage', reponse.filename);
                      } else if (listMimeAudio.indexOf(file.type) > -1) {
                          //Audio
                          elem = document.createElement("audio");
                          elem.setAttribute("src", reponse.filename);
                          elem.setAttribute("controls", "controls");
                          elem.setAttribute("preload", "metadata");
                          $('#editor').summernote('editor.insertNode', elem);
                      } else if (listMimeVideo.indexOf(file.type) > -1) {
                          //Video
                          elem = document.createElement("video");
                          elem.setAttribute("src", reponse.filename);
                          elem.setAttribute("controls", "controls");
                          elem.setAttribute("preload", "metadata");
                          elem.setAttribute("style", "width:100%");
                          $('#editor').summernote('editor.insertNode', elem);
                      } else{
                          //Other file type
                          elem = document.createElement("a");
                          let linkText = document.createTextNode(file.name);
                          elem.appendChild(linkText);
                          elem.title = file.name;
                          elem.href = reponse.filename;
                          $('#editor').summernote('editor.insertNode', elem);
                      }
                  }else{
                    alert(reponse.msg);

                  }
              }
          });
      }
      function progressHandlingFunction(e) {
    if (e.lengthComputable) {
        //Log current progress
        console.log((e.loaded / e.total * 100) + '%');

        //Reset progress on complete
        if (e.loaded === e.total) {
            console.log("Upload finished.");
        }
    }
  }
</script>
