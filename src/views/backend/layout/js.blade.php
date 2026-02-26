<script>
    $(document).on('click', '.btn-view-media', function () {

        const file = $(this).data('media');
        const ext = ($(this).data('ext') || '').toLowerCase();
        const baseUrl = "{{ url('/') }}";

        // Absolute URL safety
        const url = file.startsWith('http') ? file : baseUrl + file;

        // Extract filename
        const fileName = url.split('/').pop();

        const extensions = {
            image: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            pdf: ['pdf'],
            office: [
                'docx', 'docm', 'dotm', 'dotx',
                'xlsx', 'xlsb', 'xls', 'xlsm',
                'pptx', 'ppsx', 'ppt', 'pps', 'pptm', 'potm', 'ppam', 'potx', 'ppsm'
            ]
        };

        let content = '';
        let viewerUrl = '';
        let showDownloadBtn = false;

        // =========================
        // TYPE HANDLER
        // =========================

        if (extensions.image.includes(ext)) {

            content = `
            <img src="${url}" class="img-fluid rounded shadow-sm">
        `;

        } else if (extensions.pdf.includes(ext)) {

            viewerUrl = url;
            showDownloadBtn = true;

        } else if (extensions.office.includes(ext)) {

            viewerUrl = `https://view.officeapps.live.com/op/view.aspx?src=${encodeURIComponent(url)}`;
            showDownloadBtn = true;
        }

        // =========================
        // IFRAME VIEWER HANDLER
        // =========================

        if (viewerUrl) {
            content = `
            <div class="position-relative" style="min-height:600px;">
                <div id="mediaLoader" 
                     class="d-flex justify-content-center align-items-center position-absolute w-100 h-100 bg-white">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                <iframe 
                    src="${viewerUrl}" 
                    width="100%" 
                    height="600px" 
                    style="border:none; display:none;" 
                    onload="document.getElementById('mediaLoader').remove(); this.style.display='block';"
                    onerror="document.getElementById('mediaLoader').innerHTML='<p class=\\'text-danger\\'>Gagal memuat file.</p>';">
                </iframe>
            </div>
        `;
        }

        // =========================
        // FALLBACK
        // =========================

        if (!content) {
            content = `
            <div class="text-center p-4">
                <p>File tidak dapat dipreview.</p>
                <a href="${url}" class="btn btn-primary" download>
                    Download File
                </a>
            </div>
        `;
        }

        // =========================
        // DOWNLOAD BUTTON HEADER
        // =========================

        const downloadButton = showDownloadBtn ? `
        <a href="${url}" class="btn btn-sm btn-outline-primary mr-2" download>
            <i class="fa fa-download"></i> Download
        </a>
    ` : '';

        // =========================
        // BUILD MODAL
        // =========================

        $('#dynamicMediaModal').remove();

        const modalHtml = `
        <div class="modal fade" id="dynamicMediaModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    
                    <div class="modal-header bg-light d-flex justify-content-between align-items-center">
                        
                        <div>
                            <strong>${fileName}</strong>
                            <span class="badge badge-secondary text-uppercase ml-2">
                                ${ext || 'unknown'}
                            </span>
                        </div>

                        <div class="d-flex align-items-center">
                            ${downloadButton}
                            <button type="button" class="close ml-2" data-dismiss="modal">&times;</button>
                        </div>

                    </div>

                    <div class="modal-body text-center">
                        ${content}
                    </div>

                </div>
            </div>
        </div>
    `;

        $('body').append(modalHtml);

        const modal = $('#dynamicMediaModal');
        modal.modal('show');

        modal.on('hidden.bs.modal', function () {
            modal.remove();
        });

    });
</script>
<script  src="{{url('backend/js/plugins/select2.min.js')}}"></script>
<script>
    const compressImage = async (file, { quality = 1.0, maxWidth = 1700 } = {}) => {
        const imageBitmap = await createImageBitmap(file);

        let { width, height } = imageBitmap;
        if (width > maxWidth) {
            const scale = maxWidth / width;
            width = maxWidth;
            height = Math.round(height * scale);
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.drawImage(imageBitmap, 0, 0, width, height);

        const blob = await new Promise((resolve) =>
            canvas.toBlob(resolve, 'image/webp', quality)
        );

        const newFileName = file.name.replace(/\.[^/.]+$/, '') + '.webp';
        return new File([blob], newFileName, { type: 'image/webp' });
    };

const getIdentifier = (input) => input.name || 'input_' + Math.random().toString(36).substring(2, 8);

const compressAndPreview = async (file, preview, dataTransfer, quality) => {
    const originalSizeKB = (file.size / 1024).toFixed(2);
    const compressedFile = await compressImage(file, { quality });
    const compressedSizeKB = (compressedFile.size / 1024).toFixed(2);
    dataTransfer.items.add(compressedFile);

    const reader = new FileReader();
    reader.onload = (e) => {
        const wrapper = document.createElement('div');
        wrapper.style.width = '150px';
        wrapper.className = 'text-center position-relative';

        wrapper.innerHTML = `
            <img src="${e.target.result}" class="img-thumbnail mb-1 ml-2" style="height: 120px; object-fit: cover;" alt="Preview">
            <small class="text-muted d-block">⬇ ${originalSizeKB} KB → ${compressedSizeKB} KB</small>
            <select class="form-select form-select-sm mt-1 compression-select mb-2">
                <option value="0.3" ${quality === 0.3 ? 'selected' : ''}>Kecil (30%)</option>
                <option value="0.6" ${quality === 0.6 ? 'selected' : ''}>Sedang (60%)</option>
                <option value="0.85" ${quality === 0.85 ? 'selected' : ''}>Tinggi (85%)</option>
                <option value="1.0" ${quality === 1.0 ? 'selected' : ''}>Original (100%)</option>
            </select>
        `;

        wrapper.querySelector('.compression-select').addEventListener('change', async (e) => {
            const newQuality = parseFloat(e.target.value);
            dataTransfer.items.clear();
            preview.innerHTML = '';
            for (const f of originalFiles) {
                await compressAndPreview(f, preview, dataTransfer, newQuality);
            }
            input.files = dataTransfer.files;
        });

        preview.appendChild(wrapper);
    };
    reader.readAsDataURL(compressedFile);
};

let originalFiles = [];
let input;

const fileselect = async (targetInput) => {
    input = targetInput;
    const identifier = getIdentifier(input);
    const previewId = `preview_${identifier}`;

    let preview = document.getElementById(previewId);
    if (!preview) {
        preview = document.createElement('div');
        preview.id = previewId;
        preview.className = 'd-flex flex-wrap gap-3 mt-3';
        input.insertAdjacentElement('afterend', preview);
    }

    preview.innerHTML = '';
    const files = Array.from(input.files);
    originalFiles = files;

    const dataTransfer = new DataTransfer();

    for (const file of files) {
        const fileExt = file.name.toLowerCase();
        const allowed = /\.(jpe?g|png|webp|gif)$/;

        if (!allowed.test(fileExt)) {
            alert(`File "${file.name}" tidak didukung. Hanya JPG, JPEG,GIF dan PNG`);
            continue;
        }
     
            await compressAndPreview(file, preview, dataTransfer, 1.0); 
    }

    input.files = dataTransfer.files;
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.compress-image').forEach(inp => {
        inp.addEventListener('change', () => {
            const file = inp.files[0];
            if (file && file.type.startsWith('image/') && !file.type.includes('gif')) {
                fileselect(inp);
            }
        });
    });
});

</script>

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
@if(get_post_type() || in_array(request()->segment(2), ['polling', 'tags', 'user', 'files', 'comments']))
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

                swal("Berhasil", "Penghapusan berhasil", "success");

                if ($(".datatable").length) {
                    setTimeout(() => {
                    $(".datatable").show();
                        $(".datatable").DataTable().ajax.reload();
                    }, 500);
                }else{
                    setTimeout(() => {
                   location.reload();
                    }, 500);
                }

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
