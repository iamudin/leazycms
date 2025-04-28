@if(View::exists('template.'.template().'.403'))
@include('template.'.template().'.403')
@else
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Dilarang Mengakses</title>
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
        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        a:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <div class="error-code">403</div>
    <div class="error-message">Akses Dilarang</div>
    <div class="error-subtext">Anda tidak memiliki izin untuk mengakses halaman ini.</div>

    <ul class="reason-list">
        <li>Akses dengan cara ilegal atau tidak sah</li>
        <li>Halaman atau file bersifat privasi</li>
        <li>Hak akses Anda tidak mencukupi</li>
        <li>Halaman berisi konten terlarang</li>
        <li>Upaya membuka halaman terproteksi</li>
    </ul>

    <a href="/">Kembali ke Beranda</a>

</body>
</html>

@endif
