<!-- Navbar-->
<header class="app-header" style="background:#222d32"><a href="" class="app-header__logo"
        style="color:#fff;background:transparent">@if(get_option('logo')) <img src="/{{ get_option('logo') }}" height="30" alt="">  @else Admin<b>Panel</b> @endif</a>
    <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar"
        aria-label="Hide Sidebar"></a>
    <!-- Navbar Right Menu-->
    <ul class="app-nav">
        <!--Notification Menu-->
        <li class="item" title="Kunjungi Website"><a class="app-nav__item text-info" href="{{ url('/') }}" target="_blank"><i class="fa fa-globe fa-lg"></i></a></li>
        @if(request()->user()->level=='admin')
<li class="item" title="Pengaturan"><a class="app-nav__item text-warning" href="{{ route('setting')}}" ><i class="fa fa-gears fa-lg"></i></a></li>
@endif

        <li class="dropdown"><a class="app-nav__item text-danger" title="Profile" href="#" data-toggle="dropdown"
                aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right">

                <li><a class="dropdown-item" href="{{ route('user.account') }}"><i class="fa fa-user fa-lg"></i> Profile</a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('logout') }}"><i class="fa fa-sign-out fa-lg"></i> Logout</a>

                </li>
            </ul>
        </li>
    </ul>
</header>
