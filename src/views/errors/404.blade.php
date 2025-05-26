<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Halaman Tidak ditemukan</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            padding: 80px 20px;
        }
        .error-code {
            font-size: 100px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .error-subtext {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }
        ul.reason-list {
            width:300px;
            text-align: left;
            margin:0 auto;
            list-style: none;
            padding: 0;
            margin-bottom: 30px;
            color: #555;
            font-size: 15px;
        }
        ul.reason-list li::before {
            content: "• ";
            color: #dc3545;
            font-weight: bold;
        }
        .btnback {
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .btnback:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <div class="error-code">404</div>
    <div class="error-message">Halaman tidak ditemukan</div>
    <div class="error-subtext"><b>{{ url()->full() }} </b></div>

    <ul class="reason-list">
        <li>Halaman sudah dihapus</li>
        <li>Halaman sedang di perbaiki</li>
        <li>Halaman tidak pernah tersedia</li>
    </ul>

    <a href="/" class="btnback">Kembali ke Beranda</a>

</body>
</html>
