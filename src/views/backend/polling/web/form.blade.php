<style>
    /* Wrapper untuk polling form */
.polling-form {
    width: 100%;
    max-width: 400px; /* Lebar maksimal form */
    margin: 20px auto; /* Menjaga agar form tetap di tengah */
    padding: 20px;
    background-color: #f9f9f9; /* Warna latar belakang form */
    border: 2px solid #3498db; /* Border berwarna biru */
    border-radius: 8px; /* Membuat sudut border menjadi bulat */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Efek bayangan halus */
    font-family: 'Arial', sans-serif; /* Menggunakan font Arial */
}

/* Header polling form */
.polling-header {
    text-align: center;
    margin-bottom: 20px;
}

/* Judul polling */
.polling-title {
    font-size: 20px;
    font-weight: bold;
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
.polling-footer .btn-submit-polling {
    background-color: #3498db; /* Warna biru */
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

/* Efek hover pada tombol kirim */
.polling-footer .btn-submit-polling:hover {
    background-color: #2980b9; /* Warna biru lebih gelap */
}

/* Menyembunyikan tombol kirim secara default */
.polling-footer .btn-submit-polling[style*="display:none"] {
    display: none;
}

</style>
<div class="polling-form polling-form-{{$data->id}}">
    <div class="polling-header">
        <h6 class="polling-title">{{ $data->title }}</h6>
    </div>

    <div class="polling-body">
        @foreach ($data->options as $item)
            <input type="radio"  onchange="$('.btn-submit-polling-{{$data->id}}').show()" name="answer_{{$data->id}}" value="{{ $item->id }}"> {{ $item->name }}<br>
        @endforeach
    </div>
    <div class="polling-footer">
        <button class="btn-submit-polling btn-submit-polling-{{$data->id}}" style="display:none">Kirim</button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
          $('.btn-submit-polling-{{$data->id}}').click(function () {
              const answer = $('input[name="answer_{{$data->id}}"]:checked').val();
              let formData = new FormData();
              formData.append('answer', answer);
              formData.append('topic','{{$data->id}}');
              formData.append('_token','{{csrf_token()}}');
              $.ajax({
                  url: '/pollingentry/submit',
                  type: 'POST',
                  data: formData,
                  processData: false,
                  contentType: false,
                  success: function (response) {
                  $('.polling-form-{{$data->id}}').html('<center>Terima Kasih Atas Voting Anda</center>');
                  },
                  error: function (xhr, status, error) {
                  }
              });
          });

  });
  </script>

</div>
