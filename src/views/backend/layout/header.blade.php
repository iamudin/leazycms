<style>
/* Fix Summernote CSS conflict shifting nav icons */
.app-nav .app-nav__item {
    display: flex !important;
    align-items: center !important;
    height: 50px !important;
}
.app-nav .app-nav__item i {
    display: flex;
    align-items: center;
}
.app-nav .app-nav__item, .app-nav__mobile-toggle {
    transition: background-color 0.2s ease-in-out;
}
.app-nav .app-nav__item:hover, .app-nav__mobile-toggle:hover, .app-sidebar__toggle:hover {
    background-color: rgba(128, 128, 128, 0.2) !important;
}
.app-header__logo {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.app-header__logo img {
    vertical-align: middle !important;
    max-width: 100% !important;
    height: auto !important;
    max-height: 45px !important;
    object-fit: contain;
}

/* Mobile Right Menu Toggle */
@media (max-width: 767px) {
    .app-header {
        display: flex !important;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
    .app-header__logo {
        order: 2;
        flex: 1;
        justify-content: flex-start !important;
        width: auto !important;
        min-width: 0; /* prevent overflow */
        padding-left: 0 !important;
        margin-left: -10px !important;
    }
    .app-header__logo img {
        max-width: 100% !important;
    }
    .app-sidebar__toggle {
        order: 1;
        flex: 0 0 50px;
    }
    .app-nav__mobile-toggle {
        order: 3;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        color: var(--header-font, #fff) !important;
        cursor: pointer;
        flex: 0 0 50px;
        margin-left: 0;
    }
    .app-nav {
        position: absolute;
        top: 50px;
        left: 0;
        right: 0;
        background: var(--header-bg, #1D2327);
        width: 100%;
        display: none !important;
        flex-direction: row;
        justify-content: center;
        gap: 15px;
        padding: 10px 0;
        box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        z-index: 999;
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    .app-nav.show-mobile-nav {
        display: flex !important;
        animation: slideDownNav 0.3s ease forwards;
    }
    @keyframes slideDownNav {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
}
@media (min-width: 768px) {
    .app-nav__mobile-toggle {
        display: none !important;
    }
}
</style>
<!-- Navbar-->
<header class="app-header"><a href="{{ route('panel.dashboard') }}" class="app-header__logo"
        style="color: var(--header-font, #fff) !important; background:transparent">
            @if(get_option('logo_title') && get_option('logo_description') && get_option('logo_image')) <img src="{{ url('logo.webp')}}" style="max-width: 100%; height: auto; max-height: 45px; object-fit: contain;" alt="Logo">  @else Admin<b>Panel</b> @endif

    </a>
    <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar"
        aria-label="Hide Sidebar" style="color: var(--header-font, #fff) !important;"></a>
        
    <!-- Mobile Nav Toggle -->
    <a class="app-nav__mobile-toggle" href="#" onclick="document.querySelector('.app-nav').classList.toggle('show-mobile-nav'); event.preventDefault();" aria-label="Toggle Navigation">
        <i class="fa fa-ellipsis-v fa-lg"></i>
    </a>

    <!-- Navbar Right Menu-->
    <ul class="app-nav">
        @include('cms::backend.layout.notif')
        <!--Notification Menu-->
        <li class="item" title="Kunjungi Website"><a class="app-nav__item" href="{{ url('/') }}" target="_blank" style="color: var(--header-font, #fff) !important;"><i class="fa fa-globe fa-lg text-primary"></i></a></li>

        <!-- Sidebar Theme Menu -->
        <li class="dropdown">
            <a class="app-nav__item" title="Ubah Tema Sidebar" href="#" data-toggle="dropdown" aria-label="Open Theme Menu" style="color: var(--header-font, #fff) !important;">
                <i class="fa fa-paint-brush fa-lg text-info"></i>
            </a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right" id="theme-selector-menu">
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="changePanelTheme('dark')"><span style="width:15px;height:15px;border-radius:50%;background:#1D2327;margin-right:10px;border:1px solid #ccc;"></span> Dark (Default)</a></li>
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="changePanelTheme('navy')"><span style="width:15px;height:15px;border-radius:50%;background:#1e293b;margin-right:10px;border:1px solid #ccc;"></span> Navy Blue</a></li>
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="changePanelTheme('purple')"><span style="width:15px;height:15px;border-radius:50%;background:#4c1d95;margin-right:10px;border:1px solid #ccc;"></span> Elegant Purple</a></li>
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="changePanelTheme('forest')"><span style="width:15px;height:15px;border-radius:50%;background:#064e3b;margin-right:10px;border:1px solid #ccc;"></span> Deep Forest</a></li>
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="changePanelTheme('light')"><span style="width:15px;height:15px;border-radius:50%;background:#ffffff;margin-right:10px;border:1px solid #ccc;"></span> Clean Light</a></li>
            </ul>
        </li>

        <li class="dropdown"><a class="app-nav__item" title="Profile" href="#" data-toggle="dropdown"
                aria-label="Open Profile Menu" style="color: var(--header-font, #fff) !important;"><i class="fa fa-user fa-lg text-danger"></i></a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right">
                @if(request()->user()->level=='admin')

                <li><a class="dropdown-item" href="{{ route('setting') }}"><i class="fa fa-gear fa-lg"></i> Setting</a>
                </li>
@endif
                <li><a class="dropdown-item" href="{{ route('user.account') }}"><i class="fa fa-user fa-lg"></i> Profile</a>
                </li>



                <li>
               <a class="dropdown-item" href="javascript:void(0)" onclick="confirmLogout(event)"><i class="fa fa-sign-out fa-lg"></i> Logout</a>
                </li>

                </li>
            </ul>
        </li>

    </ul>
</header>
