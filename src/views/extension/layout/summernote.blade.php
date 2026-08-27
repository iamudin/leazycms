<script src="{{asset('backend/js/summernote-image-attributes.js')}}"></script>
<script src="{{asset('backend/js/summernote-file.js')}}"></script>
<script src="{{asset('backend/js/en-us.js')}}"></script>
<script type="text/javascript">
$(document).ready(function() {

    function sanitizeVideoIframe($node) {
        if (!$node) return $node;
        var $iframe = $node.is('iframe') ? $node : $node.find('iframe');
        if ($iframe.length) {
            $iframe.each(function() {
                var $this = $(this);
                $this.removeAttr('width')
                     .removeAttr('height')
                     .attr('style', 'width:100%;height:500px;')
                     .attr('allowfullscreen', 'true')
                     .attr('frameborder', '0');
            });
        }
        return $node;
    }

    function createCustomVideoNode(url) {
        if (!url) return null;
        url = url.trim();

        var ytRegExp = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})(?:\S+)?$/;
        var ytMatch = url.match(ytRegExp);
        if (ytMatch && ytMatch[1].length === 11) {
            var youtubeId = ytMatch[1];
            return $('<iframe>')
                .attr('frameborder', 0)
                .attr('allowfullscreen', 'true')
                .attr('src', '//www.youtube.com/embed/' + youtubeId)
                .attr('style', 'width:100%;height:500px;');
        }

        var igRegExp = /(?:instagram\.com|instagr\.am)\/p\/([\w-]+)\/?/i;
        var igMatch = url.match(igRegExp);
        if (igMatch && igMatch[0].length) {
            return $('<iframe>')
                .attr('frameborder', 0)
                .attr('allowfullscreen', 'true')
                .attr('scrolling', 'no')
                .attr('allowtransparency', 'true')
                .attr('src', 'https://instagram.com/p/' + igMatch[1] + '/embed/')
                .attr('style', 'width:100%;height:500px;');
        }

        var vimeoRegExp = /(?:https?:)?\/\/(?:www\.)?(?:player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
        var vimeoMatch = url.match(vimeoRegExp);
        if (vimeoMatch && vimeoMatch[3].length) {
            return $('<iframe webkitallowfullscreen mozallowfullscreen allowfullscreen>')
                .attr('frameborder', 0)
                .attr('allowfullscreen', 'true')
                .attr('src', '//player.vimeo.com/video/' + vimeoMatch[3])
                .attr('style', 'width:100%;height:500px;');
        }

        var dmRegExp = /^(?:https?:\/\/)?(?:www\.)?(?:dailymotion\.com\/(?:embed\/video\/|video\/)|dai\.ly\/)([\w-]+)/;
        var dmMatch = url.match(dmRegExp);
        if (dmMatch && dmMatch[1].length) {
            return $('<iframe>')
                .attr('frameborder', 0)
                .attr('allowfullscreen', 'true')
                .attr('src', '//www.dailymotion.com/embed/video/' + dmMatch[1])
                .attr('style', 'width:100%;height:500px;');
        }

        if (url.indexOf('<iframe') !== -1) {
            var $parsed = $(url);
            return sanitizeVideoIframe($parsed);
        } else if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('//')) {
            return $('<iframe>')
                .attr('frameborder', 0)
                .attr('allowfullscreen', 'true')
                .attr('src', url)
                .attr('style', 'width:100%;height:500px;');
        }

        return null;
    }

    $("#editor").summernote({
      placeholder: 'Tulis isi..',
            height: 600,
            callbacks: {
                onInit: function (context) {
                    var ctx = context || $('#editor').data('summernote');
                    if (ctx && ctx.modules && ctx.modules.video) {
                        var videoModule = ctx.modules.video;
                        videoModule.createVideoNode = function (url) {
                            return createCustomVideoNode(url);
                        };
                        videoModule.insertVideo = function (url) {
                            var $node = this.createVideoNode(url);
                            if ($node) {
                                sanitizeVideoIframe($node);
                                ctx.invoke('editor.insertNode', $node[0]);
                                var pNode = document.createElement('p');
                                pNode.innerHTML = '<br>';
                                ctx.invoke('editor.insertNode', pNode);
                            }
                        };
                    }
                },
                onChange: function (contents, $editable) {
                    if ($editable && $editable.length) {
                        $editable.find('iframe').each(function() {
                            var $ifr = $(this);
                            if ($ifr.is('[width]') || $ifr.is('[height]')) {
                                sanitizeVideoIframe($ifr);
                            }
                        });
                    }
                    let sanitized = contents
                        .replace(/<script[^>]*>.*?<\/script>/gi, '')
                        .replace(/<style[^>]*>.*?<\/style>/gi, '')
                        .replace(/javascript:/gi, '')
                        .replace(/on\w+="[^"]*"/gi, '')
                        .replace(/on\w+='[^']*'/gi, '')
                        .replace(/<img[^>]*src=["']?data:image\/[^"'>\s]+["']?[^>]*\/?>/gi, '')
                        .replace(/<img[^>]*src=["']?file:\/\/[^"'>\s]+["']?[^>]*\/?>/gi, '');

                    if (sanitized !== contents) {
                        $('#editor').summernote('code', sanitized);
                    }
                },
                onPaste: function (e) {
                    var event = e.originalEvent || e;
                    var clipboardData = event.clipboardData || window.clipboardData;
                    if (!clipboardData) return;

                    var html = clipboardData.getData('text/html');
                    if (html) {
                        var hasBase64 = /data:image\/[^;]+;base64,/i.test(html) || /file:\/\/\//i.test(html);
                        var hasMsWord = /schemas-microsoft-com/i.test(html) || /mso-/i.test(html) || /<!--\[if/i.test(html);

                        if (hasBase64 || hasMsWord) {
                            e.preventDefault();

                            var cleanedHtml = html
                                .replace(/<!--\[if[\s\S]*?<!\[endif\]-->/gi, '')
                                .replace(/<v:[^>]*>[\s\S]*?<\/v:[^>]*>/gi, '')
                                .replace(/<o:[^>]*>[\s\S]*?<\/o:[^>]*>/gi, '')
                                .replace(/<img[^>]*src=["']?data:image\/[^"'>\s]+["']?[^>]*\/?>/gi, '')
                                .replace(/<img[^>]*src=["']?file:\/\/[^"'>\s]+["']?[^>]*\/?>/gi, '');

                            document.execCommand('insertHTML', false, cleanedHtml);

                            if (hasBase64) {
                                alert("Perhatian: Gambar base64 / dari MS Word tidak diizinkan.");
                            }
                        }
                    }
                },
                onImageUpload: function(files) {
                    alert("Perhatian: Unggah/paste gambar langsung (base64) tidak diizinkan.");
                    return false;
                },
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
