<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="authord" content="">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:site" content="">
    <meta property="twitter:creator" content="">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="">
    <meta property="og:title" content="">
    <meta property="og:url" content="">
    <meta property="og:image" content="">
    <meta property="og:description" content="">
    <title>
        {{ isset($title) ? $title . ' › Admin Panel ' . (get_option('site_title') ? ' › ' . get_option('site_title') : '') : ('Admin Panel ' . (get_option('site_title') ? ' › ' . get_option('site_title') : '')) }}
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="yes" name="apple-touch-fullscreen">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ get_option('icon') ?? noimage() }}">
    <meta name="theme-color" content="#009688">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="{{ url('backend/css/main.css') }}">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css"
        href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js" async></script>

    <style>
        .pointer {
            cursor: pointer;
        }

        /* Modern Button Styles */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease, color 0.3s ease, border-color 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            border: 1px solid transparent;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Premium Gradients */
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
            border: none !important;
            color: #fff !important;
        }

        .btn-info {
            background: linear-gradient(135deg, #0dcaf0 0%, #087990 100%) !important;
            border: none !important;
            color: #fff !important;
        }

        .btn-success {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%) !important;
            border: none !important;
            color: #fff !important;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%) !important;
            border: none !important;
            color: #fff !important;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%) !important;
            border: none !important;
            color: #212529 !important;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
            border: none !important;
            color: #fff !important;
        }

        /* Outline Overrides */
        .btn-outline-primary {
            background: transparent !important;
            border: 2px solid #0d6efd !important;
            color: #0d6efd !important;
        }

        .btn-outline-warning {
            border: 2px solid #fd8d0d !important;
            color: #fd3d0d !important;
            background: transparent !important;
        }
        
        .btn-outline-warning:hover,
        .btn-outline-warning.active {
            background: #fd8d0d !important;
            color: #fff !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary.active {
            background: #0d6efd !important;
            color: #fff !important;
        }

        .btn-outline-info {
            background: transparent !important;
            border: 2px solid #0dcaf0 !important;
            color: #087990 !important;
        }

        .btn-outline-info:hover,
        .btn-outline-info.active {
            background: #0dcaf0 !important;
            color: #fff !important;
        }

        .btn-outline-danger {
            background: transparent !important;
            border: 2px solid #dc3545 !important;
            color: #dc3545 !important;
        }

        .btn-outline-danger:hover,
        .btn-outline-danger.active {
            background: #dc3545 !important;
            color: #fff !important;
        }

        .btn-outline-success {
            background: transparent !important;
            border: 2px solid #198754 !important;
            color: #198754 !important;
        }

        .btn-outline-success:hover,
        .btn-outline-success.active {
            background: #198754 !important;
            color: #fff !important;
        }

        /* Fix for btn-group border radius */
        .btn-group .btn {
            border-radius: 0 !important;
            margin-right: -1px;
            /* Overlap borders slightly to avoid double borders */
        }

        .btn-group .btn:first-child {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
        }

        .btn-group .btn:last-child {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            margin-right: 0;
        }

        .btn-group .btn:hover {
            z-index: 2;
            /* Ensure the hovered button overlaps properly */
        }

        /* Responsive DataTables */
        @media (max-width: 768px) {
            div.dataTables_wrapper>div.row:first-child {
                display: flex;
                flex-direction: row;
                flex-wrap: nowrap;
                justify-content: space-between;
                align-items: center;
            }

            div.dataTables_wrapper>div.row:first-child>div {
                width: 50% !important;
                flex: 0 0 50%;
                padding: 0 5px;
            }

            div.dataTables_wrapper div.dataTables_length {
                text-align: left !important;
                margin-top: 0;
            }

            div.dataTables_wrapper div.dataTables_filter {
                text-align: right !important;
                margin-top: 0;
            }

            div.dataTables_wrapper div.dataTables_filter label,
            div.dataTables_wrapper div.dataTables_length label {
                display: flex;
                align-items: center;
                margin-bottom: 0;
                white-space: nowrap;
                font-size: 12px;
            }

            div.dataTables_wrapper div.dataTables_filter label {
                justify-content: flex-end;
            }

            div.dataTables_wrapper div.dataTables_filter input {
                width: 100%;
                max-width: 120px;
                margin-left: 5px;
            }

            div.dataTables_wrapper div.dataTables_length select {
                width: auto;
                margin: 0 5px;
            }
        }
    </style>
    @if (\Session::has('success'))
        <script>
            window.onload = function () {
                notif("{{ Session::get('success') }}", "success");
            };
        </script>
    @endif

    @if (\Session::has('warning'))
        <script>
            window.onload = function () {
                notif("{{ Session::get('warning') }}", "warning");
            };
        </script>
    @endif
    @if (\Session::has('danger'))
        <script>
            window.onload = function () {
                notif("{{ Session::get('danger') }}", "danger");
            };
        </script>
    @endif

    @stack('styles')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        :root {
            --sidebar-bg: #1D2327;
            --header-bg: #111518;
            --sidebar-list-bg: #000000;
            --sidebar-font: #ffffff;
            --header-font: #ffffff;
            --sidebar-muted: #a0aabf;
            --body-bg: #F0F0F1;
            --theme-primary: #0d6efd;
            --theme-primary-dark: #0a58ca;
            --theme-primary-soft: #0d6efd80;
            --sidebar-hover-bg: rgba(255, 255, 255, 0.1);
            --sidebar-hover-color: #ffffff;
        }

        /* Dynamic Sidebar & Header Overrides */
        .app-sidebar {
            background: var(--sidebar-bg) !important;
        }

        .app-header {
            background: var(--header-bg) !important;
        }

        .app-sidebar__user-name,
        .app-sidebar__user-designation {
            color: var(--sidebar-font) !important;
        }

        .sidebar-list-header,
        .treeview-menu {
            background: var(--sidebar-list-bg) !important;
            color: var(--sidebar-muted) !important;
        }

        .app-menu__item,
        .app-menu__label,
        .treeview-indicator,
        .treeview-item {
            color: var(--sidebar-font) !important;
        }

        .treeview-item:hover,
        .app-menu__item:hover,
        .app-menu__item.active,
        .treeview-item.active,
        .treeview.is-expanded>.app-menu__item {
            background: var(--sidebar-hover-bg, rgba(255, 255, 255, 0.1)) !important;
            color: var(--sidebar-hover-color, #fff) !important;
        }

        .treeview-item:hover *,
        .app-menu__item:hover *,
        .app-menu__item.active *,
        .treeview-item.active *,
        .treeview.is-expanded>.app-menu__item * {
            color: var(--sidebar-hover-color, #fff) !important;
        }

        /* Dynamic Primary Color Override */
        .text-primary {
            color: var(--theme-primary) !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-primary-dark) 100%) !important;
            border-color: var(--theme-primary) !important;
        }

        .bg-primary {
            background-color: var(--theme-primary) !important;
        }

        /* Active Sidebar Menu & Submenu */
        .app-menu__item.active,
        .treeview-item.active,
        .treeview.is-expanded>.app-menu__item {
            border-left-color: var(--theme-primary) !important;
        }

        body {
            font-family: sans-serif;
            background-color: var(--body-bg);
        }

        .text-stroke {
            text-decoration: line-through;
        }

        a:hover {
            text-decoration: none;
        }

        .btop {
            margin-top: -80px;
            right: 0;
            position: absolute;
        }

        input[type=text] {
            background-color: rgb(255, 255, 255, .8);
        }

        #editor {
            background-color: rgb(255, 255, 255, .8);

        }

        label.myLabel input[type="file"] {
            position: absolute;
            top: -1000px;
        }


        /***** Example custom styling *****/

        .myLabel {
            border: 1px solid #000;
            padding: 2px 5px;
            margin: 2px;
            background: #fff;
            font-size: 9px;
            cursor: pointer;
            display: inline-block;
        }

        .myLabel:hover {
            background: red;
        }

        .myLabel:active {
            background: #CCF;
        }

        .myLabel:invalid+span {
            color: #fff;
        }

        .myLabel:valid+span {
            color: #fff;
        }

        .card-list {
            position: relative;
            background: #ffffff;
            border-radius: 3px;
            padding: 10px;
            -webkit-box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -2px rgba(0, 0, 0, 0.2);
            box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.14), 0 1px 5px 0 rgba(0, 0, 0, 0.12), 0 3px 1px -2px rgba(0, 0, 0, 0.2);
            margin-bottom: 10px;
            -webkit-transition: all 0.3s ease-in-out;
            -o-transition: all 0.3s ease-in-out;
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>

<body id="body" class="app sidebar-mini">
    @include('cms::backend.layout.header')
    @include('cms::backend.layout.sidebar')


    <main class="app-content">



        @yield('content')

    </main>

    <script>
        const panel_theme_config = {
            'dark': { bg: '#1D2327', headerBg: '#111518', listBg: '#000000', font: '#ffffff', headerFont: '#ffffff', muted: '#a0aabf', bodyBg: '#F0F0F1', primary: '#0d6efd', primaryDark: '#0a58ca', hoverBg: 'rgba(255, 255, 255, 0.1)', hoverColor: '#ffffff' },
            'navy': { bg: '#1e293b', headerBg: '#0f172a', listBg: '#0f172a', font: '#f8fafc', headerFont: '#ffffff', muted: '#94a3b8', bodyBg: '#f1f5f9', primary: '#3b82f6', primaryDark: '#2563eb', hoverBg: 'rgba(255, 255, 255, 0.1)', hoverColor: '#ffffff' },
            'purple': { bg: '#4c1d95', headerBg: '#3b0764', listBg: '#2e1065', font: '#f5f3ff', headerFont: '#ffffff', muted: '#c4b5fd', bodyBg: '#f5f3ff', primary: '#8b5cf6', primaryDark: '#7c3aed', hoverBg: 'rgba(255, 255, 255, 0.1)', hoverColor: '#ffffff' },
            'forest': { bg: '#064e3b', headerBg: '#022c22', listBg: '#022c22', font: '#ecfdf5', headerFont: '#ffffff', muted: '#6ee7b7', bodyBg: '#ecfdf5', primary: '#10b981', primaryDark: '#059669', hoverBg: 'rgba(255, 255, 255, 0.1)', hoverColor: '#ffffff' },
            'light': { bg: '#ffffff', headerBg: '#f8f9fa', listBg: '#f1f5f9', font: '#334155', headerFont: '#334155', muted: '#64748b', bodyBg: '#f8f9fa', primary: '#0d6efd', primaryDark: '#0a58ca', hoverBg: 'rgba(0, 0, 0, 0.05)', hoverColor: '#000000' }
        };

        function changePanelTheme(themeName) {
            if (!panel_theme_config[themeName]) return;
            // Gunakan prefix unik agar terikat dengan session login ini (optional tapi localStorage berbasis browser)
            localStorage.setItem('sidebar_theme_{{ request()->user()->id ?? 0 }}', themeName);
            applyPanelTheme(themeName);
        }

        function applyPanelTheme(themeName) {
            const theme = panel_theme_config[themeName];
            if (!theme) return;

            document.documentElement.style.setProperty('--sidebar-bg', theme.bg);
            document.documentElement.style.setProperty('--header-bg', theme.headerBg);
            document.documentElement.style.setProperty('--sidebar-list-bg', theme.listBg);
            document.documentElement.style.setProperty('--sidebar-font', theme.font);
            document.documentElement.style.setProperty('--header-font', theme.headerFont);
            document.documentElement.style.setProperty('--sidebar-muted', theme.muted);
            document.documentElement.style.setProperty('--body-bg', theme.bodyBg);
            document.documentElement.style.setProperty('--theme-primary', theme.primary);
            document.documentElement.style.setProperty('--theme-primary-dark', theme.primaryDark);
            document.documentElement.style.setProperty('--theme-primary-soft', theme.primary + '80');
            document.documentElement.style.setProperty('--sidebar-hover-bg', theme.hoverBg);
            document.documentElement.style.setProperty('--sidebar-hover-color', theme.hoverColor);
        }

        // Apply immediately on load based on user ID
        const savedTheme = localStorage.getItem('sidebar_theme_{{ request()->user()->id ?? 0 }}') || 'dark';
        applyPanelTheme(savedTheme);

        $(document).on('click', '.copy', function () {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(this).attr('data-copy')).select();
            document.execCommand("copy");
            notif('Copied', 'info');
            $temp.remove();
        });

        function copy($val) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($val).select();
            document.execCommand("copy");
            notif('Copied', 'info');
            $temp.remove();
        }
    </script>

    <!-- Essential javascripts for application to work-->
    <script src="{{ url('backend/js/popper.min.js') }}"></script>
    <script src="{{ url('backend/js/bootstrap.min.js') }}"></script>
    <script src="{{ url('backend/js/main.js') }}"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="{{ url('backend/js/plugins/pace.min.js') }}"></script>
    <!-- Page specific javascripts-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script src="{{ url('backend/js/plugins/bootstrap-notify.min.js') }}"></script>

    <script src="{{ url('backend/js/plugins/sweetalert.min.js') }}"></script>
    @stack('scripts')

    <!-- Global Media Modal -->

    <script>
    function confirmLogout(event) {
        event.preventDefault();
        swal(
            {
                title: "Anda yakin ingin keluar?",
                text: "Anda akan mengakhiri sesi ini.",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Keluar!",
                cancelButtonText: "Batal",
                closeOnConfirm: false
            },
            function (isConfirm) {
                if (isConfirm) {
                    swal({
                        title: "Terima kasih!",
                        text: "Sampai jumpa kembali.",
                        type: "success",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function() {
                        var f=document.createElement('form');
                        f.method='POST';
                        f.action='{{ route('logout') }}';
                        f.style.display='none';
                        var t=document.createElement('input');
                        t.type='hidden';
                        t.name='_token';
                        t.value='{{ csrf_token() }}';
                        f.appendChild(t);
                        document.body.appendChild(f);
                        f.submit();
                    }, 1000);
                }
            }
        );
    }
    </script>
</body>

</html>