<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>LeazyCMS Multisite Installation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top right, #1e3a8a 0%, transparent 30%),
                radial-gradient(circle at bottom left, #0f766e 0%, transparent 30%),
                #0f172a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            width: 650px;
            max-width: 100%;
            background: rgba(30, 41, 59, 0.95);
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 24px;
            padding: 45px;
            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 30px;
            font-weight: 700;
            color: #38bdf8;
            margin-bottom: 8px;
        }

        .logo-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #38bdf8, #0ea5e9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
            font-weight: bold;
            font-size: 20px;
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.35);
        }

        .domain {
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 35px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #4ade80;
            padding: 10px 16px;
            border-radius: 999px;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 10px #22c55e;
        }

        h1 {
            font-size: 32px;
            margin: 0 0 18px;
            line-height: 1.3;
        }

        p {
            color: #cbd5e1;
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .terminal-box {
            margin: 30px 0;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(148, 163, 184, 0.15);
            background: #020617;
        }

        .terminal-header {
            background: #111827;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .dot.red {
            background: #ef4444;
        }

        .dot.yellow {
            background: #facc15;
        }

        .dot.green {
            background: #22c55e;
        }

        .terminal-title {
            margin-left: 10px;
            color: #94a3b8;
            font-size: 13px;
        }

        .terminal-content {
            padding: 25px;
            font-family: Consolas, monospace;
            font-size: 16px;
            color: #4ade80;
            overflow-x: auto;
        }

        .command::before {
            content: "$ ";
            color: #64748b;
        }

        .info-box {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.12);
            border-radius: 16px;
            padding: 20px;
            margin-top: 25px;
        }

        .info-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #f8fafc;
        }

        .info-list {
            margin: 0;
            padding-left: 18px;
        }

        .info-list li {
            margin-bottom: 10px;
            color: #cbd5e1;
            line-height: 1.6;
        }

        .actions {
            margin-top: 35px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 13px 22px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.25s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #38bdf8, #0ea5e9);
            color: #0f172a;
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(56, 189, 248, 0.45);
        }

        .btn-secondary {
            border: 1px solid rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
            background: rgba(15, 23, 42, 0.7);
        }

        .btn-secondary:hover {
            background: rgba(30, 41, 59, 0.9);
        }

        .footer {
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
            font-size: 13px;
            color: #64748b;
            text-align: center;
        }

        @media (max-width: 640px) {
            .card {
                padding: 30px 22px;
                border-radius: 18px;
            }

            h1 {
                font-size: 26px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="card">

        <div class="logo">
            <div class="logo-badge">L</div>
            LeazyCMS
        </div>

        <div class="domain">
            leazycms.web.id
        </div>

        <div class="status">
            <div class="status-dot"></div>
            Multisite Mode Detected
        </div>

        <h1>
            LeazyCMS Multisite Setup 🚀
        </h1>

        <p>
            Sistem mendeteksi bahwa fitur <b>Multisite Mode</b> sedang aktif.
            Untuk menyelesaikan proses instalasi dan menyiapkan seluruh tabel database tenant,
            silakan jalankan proses migrasi Laravel berikut melalui terminal atau command line.
        </p>

        <div class="terminal-box">

            <div class="terminal-header">
                <div class="dot red"></div>
                <div class="dot yellow"></div>
                <div class="dot green"></div>

                <div class="terminal-title">
                    Terminal
                </div>
            </div>

            <div class="terminal-content">
                <div class="command">php artisan migrate</div>
            </div>

        </div>

        <div class="info-box">

            <div class="info-title">
                Installation Information
            </div>

            <ul class="info-list">
                <li>Migration akan membuat seluruh tabel utama multisite.</li>
                <li>Pastikan konfigurasi database pada file <b>.env</b> sudah benar.</li>
                <li>Setelah migrasi selesai, refresh halaman untuk melanjutkan.</li>
            </ul>

        </div>

        <div class="actions">

            <a href="https://leazycms.web.id" target="_blank" class="btn btn-primary">
                Visit Official Website
            </a>

            <a href="javascript:location.reload();" class="btn btn-secondary">
                Refresh Page
            </a>

        </div>

        <div class="footer">
            © {{ date('Y') }} LeazyCMS. All rights reserved.
        </div>

    </div>

</body>

</html>