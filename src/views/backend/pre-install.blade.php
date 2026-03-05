<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LeazyCMS Installation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .card {
            background: #1e293b;
            padding: 40px;
            border-radius: 16px;
            width: 600px;
            max-width: 95%;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #38bdf8;
        }

        .domain {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        p {
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .terminal {
            background: #0f172a;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            color: #22c55e;
            font-size: 14px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #64748b;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            border-radius: 8px;
            background: #38bdf8;
            color: #0f172a;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn:hover {
            background: #0ea5e9;
        }
    </style>
</head>

<body>

    <div class="card">
        <div class="logo">LeazyCMS</div>
        <div class="domain">leazycms.web.id</div>

        <h1>Welcome to LeazyCMS 🚀</h1>

        <p>
            Terima kasih telah menginstall LeazyCMS.
            Untuk menyelesaikan proses instalasi, silakan jalankan perintah berikut di terminal:
        </p>

        <div class="terminal">
            php artisan cms:install
        </div>

        <p>
            Setelah proses selesai, silakan refresh halaman ini.
        </p>

        <a href="https://leazycms.web.id" class="btn" target="_blank">
            Visit Official Website
        </a>

        <div class="footer">
            © {{ date('Y') }} LeazyCMS. All rights reserved.
        </div>
    </div>

</body>

</html>