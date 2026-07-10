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
</style>
<!-- Navbar-->
<header class="app-header"><a href="{{ route('panel.dashboard') }}" class="app-header__logo"
        style="color: var(--header-font, #fff) !important; background:transparent">
        @if(!config('modules.multisite_enabled'))
        @if(is_main_domain())
        @if(get_option('logo')  && media_exists(get_option('logo'))) <img src="{{ get_option('logo')}}" height="30" alt="">  @else Admin<b>Panel</b> @endif
        @else
        <img src="{{ $logo}}" height="30" alt="">
        @endif
        @else
        @if(get_option('logo')  && media_exists(get_option('logo'))) <img src="{{ get_option('logo')}}" height="30" alt="">  @else Admin<b>Panel</b> @endif
        @endif  
    </a>
    <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar"
        aria-label="Hide Sidebar" style="color: var(--header-font, #fff) !important;"></a>
    <!-- Navbar Right Menu-->
    <ul class="app-nav">
        @include('cms::backend.layout.notif')
        <!--Notification Menu-->
        <li class="item" title="Kunjungi Website"><a class="app-nav__item" href="{{ url('/') }}" target="_blank" style="color: var(--header-font, #fff) !important;"><i class="fa fa-globe fa-lg"></i></a></li>

        <!-- Sidebar Theme Menu -->
        <li class="dropdown">
            <a class="app-nav__item" title="Ubah Tema Sidebar" href="#" data-toggle="dropdown" aria-label="Open Theme Menu" style="color: var(--header-font, #fff) !important;">
                <i class="fa fa-paint-brush fa-lg"></i>
            </a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right" id="theme-selector-menu">
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="setSidebarTheme('dark')"><span style="width:15px;height:15px;border-radius:50%;background:#1D2327;margin-right:10px;border:1px solid #ccc;"></span> Dark (Default)</a></li>
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="setSidebarTheme('navy')"><span style="width:15px;height:15px;border-radius:50%;background:#1e293b;margin-right:10px;border:1px solid #ccc;"></span> Navy Blue</a></li>
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="setSidebarTheme('purple')"><span style="width:15px;height:15px;border-radius:50%;background:#4c1d95;margin-right:10px;border:1px solid #ccc;"></span> Elegant Purple</a></li>
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="setSidebarTheme('forest')"><span style="width:15px;height:15px;border-radius:50%;background:#064e3b;margin-right:10px;border:1px solid #ccc;"></span> Deep Forest</a></li>
                <li><a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="setSidebarTheme('light')"><span style="width:15px;height:15px;border-radius:50%;background:#ffffff;margin-right:10px;border:1px solid #ccc;"></span> Clean Light</a></li>
            </ul>
        </li>

        <li class="dropdown"><a class="app-nav__item" title="Profile" href="#" data-toggle="dropdown"
                aria-label="Open Profile Menu" style="color: var(--header-font, #fff) !important;"><i class="fa fa-user fa-lg"></i></a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right">
                @if(request()->user()->level=='admin')

                <li><a class="dropdown-item" href="{{ route('setting') }}"><i class="fa fa-gear fa-lg"></i> Setting</a>
                </li>
@endif
            @if(is_main_domain() || config('modules.multisite_enabled'))
                <li><a class="dropdown-item" href="{{ route('user.account') }}"><i class="fa fa-user fa-lg"></i> Profile</a>
                </li>
@endif



                <li>
               <a class="dropdown-item" href="javascript:void(0)" onclick="confirmLogout(event)"><i class="fa fa-sign-out fa-lg"></i> Logout</a>
                </li>

                </li>
            </ul>
        </li>

    </ul>
</header>
