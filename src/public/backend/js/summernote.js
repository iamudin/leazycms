function uploadImage(image) {
    var data = new FormData();
    data.append("image", image);
    $.ajaxSetup({
     headers: {
         'X-CSRF-TOKEN': '{{csrf_token()}}'
     }
 });
    $('#editor').summernote('insertImage','{{url('loading.gif')}}');
    $.ajax({
        url: "{{admin_url('upload_image')}}",
        cache: false,
        contentType: false,
        processData: false,
        data: data,
        type: "POST",
        success: function(url) {
          var uuu = "{{url('loading.gif')}}";
          var alls = $('#editor').summernote("code");
        $('#editor').summernote("code",alls.replace(uuu,url));
        },
        error: function(data) {
            alert(data);
        }
    });
}
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
