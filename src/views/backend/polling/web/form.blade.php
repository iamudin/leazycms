<style>
    /* Wrapper untuk polling form */


/* Header polling form */
.polling-header {
    text-align: center;
    margin-bottom: 20px;
}
.polling-footer {
    text-align: center;
}
/* Judul polling */
.polling-title {
    font-size: 20px;
    line-height: normal;
    font-weight: normal;
    color: #333;
}

/* Body polling (tempat opsi) */
.polling-body {
    margin-bottom: 20px;
}

/* Styling untuk input radio */
.polling-body input[type="radio"] {
    margin-right: 10px; /* Jarak antara radio button dan label */
    transform: scale(1.2); /* Membuat ukuran radio button lebih besar */
    vertical-align: middle;
}

/* Styling untuk teks pilihan */
.polling-body label {
    font-size: 16px;
    color: #555;
    cursor: pointer;
}

/* Styling untuk tombol kirim */


/* Efek hover pada tombol kirim */
.polling-footer .btn-submit-polling:hover {
    background-color: #2980b9; /* Warna biru lebih gelap */
}

/* Menyembunyikan tombol kirim secara default */
.polling-footer .btn-submit-polling[style*="display:none"] {
    display: none;
}
/* ===== POLLING RADIO v1 ===== */
.polling-v1 .polling-radio {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    margin-bottom: 8px;
    border-radius: 10px;
    border: 1px solid #e9ecef;
    cursor: pointer;
    transition: all .2s ease;
    background: #fff;
}

/* Hover */
.polling-v1 .polling-radio:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
}

/* Hide native radio */
.polling-v1 .polling-radio input[type="radio"] {
    display: none;
}

/* Custom radio */
.polling-v1 .radio-ui {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid #adb5bd;
    position: relative;
    flex-shrink: 0;
    transition: all .2s ease;
}

/* Checked dot */
.polling-v1 .radio-ui::after {
    content: "";
    width: 8px;
    height: 8px;
    background: #0d6efd;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0);
    transition: transform .15s ease;
}

/* Checked state */
.polling-v1 .polling-radio input:checked + .radio-ui {
    border-color: #0d6efd;
}

.polling-v1 .polling-radio input:checked + .radio-ui::after {
    transform: translate(-50%, -50%) scale(1);
}

/* Text */
.polling-v1 .radio-text {
    font-size: 14px;
    color: #212529;
}

/* Active background */
.polling-v1 .polling-radio input:checked ~ .radio-text {
    font-weight: 600;
}

</style>
<div class="polling-form polling-form-{{$data->id}}">
    <div class="polling-header">
        <h6 class="polling-title">{{ $data->title }}</h6>
    </div>

<div class="polling-body polling-v1">

    @foreach ($data->options as $item)
        <label class="polling-radio">
            <input type="radio"
                   name="answer_{{ $data->id }}"
                   value="{{ $item->id }}"
                   onchange="$('.btn-submit-polling-{{ $data->id }}').fadeIn(150)">

            <span class="radio-ui"></span>
            <span class="radio-text">{{ $item->name }}</span>
        </label>
    @endforeach

</div>

    <div class="polling-footer">
        <button class="btn btn-submit-polling btn-primary btn-md btn-submit-polling-{{$data->id}}" style="display:none">Kirim</button>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    $('.btn-submit-polling-{{ $data->id }}').click(function () {

        const answer = $('input[name="answer_{{ $data->id }}"]:checked').val();

        if (!answer) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: 'Silakan pilih salah satu jawaban terlebih dahulu',
                confirmButtonColor: '#0d6efd'
            });
            return;
        }

        let formData = new FormData();
        formData.append('answer', answer);
        formData.append('topic', '{{ $data->id }}');
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '/pollingentry/submit',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {

                Swal.fire({
                    icon: 'success',
                    title: 'Terima Kasih 🙏',
                    text: 'Voting Anda berhasil dikirim',
                    timer: 2000,
                    showConfirmButton: false
                });

                $('.polling-form-{{ $data->id }}').hide();
            },

            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan, silakan coba lagi',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    });

});
</script>


</div>
