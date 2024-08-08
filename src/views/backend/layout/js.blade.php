<script type="text/javascript" src="{{secure_asset('backend/js/plugins/select2.min.js')}}"></script>

<script>
    $('#select2').select2({

placeholder: 'Pilih Tags',
});

function media_destroy(source){
    if(confirm('Hapus ? ')){
    $.post( "{{ route('media.destroy') }}", { _token:"{{ csrf_token() }}",media:source  })
.done(function( data ) {
    location.reload();
});
}
}
                    function readURL(input) {
                        const allow = ['gif', 'png', 'jpeg', 'jpg', 'GIF', 'PNG', 'JPEG', 'JPG'];
                        var ext = input.value.replace(/^.*\./, '');
                        if (!allow.includes(ext)) {
                            alert('Pilih hanya gambar');
                            input.value = '';
                        } else {
                            if (input.files && input.files[0]) {
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    $('#thumb')
                                        .attr('src', e.target.result)
                                        .width('100%')
                                };

                                reader.readAsDataURL(input.files[0]);
                            }
                        }
                    }
function notif(a, type) {
    var ic;
    if (type == "success") {
        ic = "fa fa-check";
    } else if (type == "danger") {
        ic = "fa fa-warning";
    } else {
        ic = "fa fa-info";
    }
    $.notify(
        {
            title: a,
            message: "",
            icon: ic,
        },
        {
            type: type,
        }
    );
}

function showalert(val) {
    swal(val);
}
</script>
@if(get_post_type() || request()->is(admin_path().'/tags') || request()->is(admin_path().'/user'))
<script>
function deleteAlert(url) {
    swal(
        {
            title: "Hapus Data ?",
            text: "Semua berkas terkait data ini akan terhapus.",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Iya, Hapus!",
            cancelButtonText: "Tidak, Batalkan!",
            closeOnConfirm: false,
            closeOnCancel: false,
        },
        function (isConfirm) {
            if (isConfirm) {
                if(url.includes('http')){
                    $.post( url, { _token:"{{ csrf_token() }}",_method:"delete"}).done(function( data ) {
                           console.log(data);
                        });
                }else{
                    $.post( "{{ route('media.destroy') }}", { _token:"{{ csrf_token() }}",media:url  }).done(function( data ) {
                        console.log(data);
                        });
                }


                if ($(".datatable").length) {
                    setTimeout(() => {
                    $(".datatable").show();
                        $(".datatable").DataTable().ajax.reload();
                    }, 500);
                }
                swal.close();
            } else {
                swal("Dibatalkan", "Penghapusan dibatalkan", "error");
            }
        }
    );
}
</script>
@endif
<script>


$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});
var goUrl = function () {
    document.onclick = function (e) {
        if (e.target.getAttribute("modul")) {
            location.href = e.target.getAttribute("modul");
        }
    };
};
goUrl();
</script>
