<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Suspended</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(239, 68, 68, 0.15), transparent 30%),
                radial-gradient(circle at bottom left, rgba(251, 191, 36, 0.08), transparent 30%),
                linear-gradient(135deg, #0f172a, #111827);
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            width: 560px;
            max-width: 100%;
            background: rgba(17, 24, 39, 0.95);
            border: 1px solid rgba(239, 68, 68, 0.15);
            border-radius: 24px;
            padding: 50px 40px;
            text-align: center;
            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(239, 68, 68, 0.05);
            backdrop-filter: blur(8px);
        }

        .icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            border-radius: 24px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            box-shadow: 0 15px 35px rgba(239, 68, 68, 0.35);
        }

        h1 {
            margin: 0 0 18px;
            font-size: 34px;
            color: #f87171;
        }

        p {
            margin: 0 0 18px;
            line-height: 1.8;
            color: #cbd5e1;
            font-size: 15px;
        }

        .notice-box {
            margin-top: 30px;
            padding: 18px;
            border-radius: 16px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.12);
        }

        .notice-title {
            font-size: 15px;
            font-weight: 600;
            color: #f8fafc;
            margin-bottom: 10px;
        }

        .notice-text {
            font-size: 14px;
            color: #94a3b8;
            line-height: 1.7;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 25px;
            padding: 10px 18px;
            border-radius: 999px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            font-size: 14px;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 12px #ef4444;
            animation: pulse 1.5s infinite;
        }

        .actions {
            margin-top: 35px;
        }

        .btn {
            display: inline-block;
            padding: 13px 24px;
            background: linear-gradient(135deg, #38bdf8, #0ea5e9);
            color: #0f172a;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            transition: all .25s ease;
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.25);
        }

        .btn:hover {
            transform: translateY(-2px);
            opacity: .95;
        }

        .footer {
            margin-top: 35px;
            padding-top: 20px;
            border-top: 1px solid rgba(148, 163, 184, 0.08);
            font-size: 12px;
            color: #64748b;
        }

        @keyframes pulse {
            0% {
                opacity: .4;
                transform: scale(0.9);
            }

            50% {
                opacity: 1;
                transform: scale(1);
            }

            100% {
                opacity: .4;
                transform: scale(0.9);
            }
        }

        @media (max-width: 640px) {
            .card {
                padding: 35px 25px;
            }

            h1 {
                font-size: 28px;
            }

            .icon {
                width: 75px;
                height: 75px;
                font-size: 34px;
            }
        }
    </style>
</head>

<body>

    <div class="card">

        <div class="icon">
            ⛔
        </div>

        <h1>Website Suspended</h1>

        <p>
            This website has been temporarily suspended by the administrator.
        </p>

        <p>
            Access to this website is currently unavailable.
            Please contact the administrator for more information regarding this suspension.
        </p>

        <div class="status">
            <div class="status-dot"></div>
            Suspended Status Active
        </div>

        <div class="notice-box">

            <div class="notice-title">
                Need Assistance?
            </div>

            <div class="notice-text">
                If you believe this suspension was made in error or you need further clarification,
                please contact the system administrator or hosting provider immediately.
            </div>

        </div>

        <div class="actions">
            <a href="/" class="btn">
                Refresh Page
            </a>
        </div>

        <div class="footer">
            © {{ date('Y') }} {{request()->getHost()}} All rights reserved.
        </div>

    </div>

</body>

</html>