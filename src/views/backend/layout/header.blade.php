<!-- Navbar-->
<header class="app-header" style="background:#222d32"><a href="/" class="app-header__logo"
        style="color:#fff;background:transparent">
        @if(is_main_domain())
        @if(get_option('logo')  && media_exists(get_option('logo'))) <img src="{{ get_option('logo')}}" height="30" alt="">  @else Admin<b>Panel</b> @endif
        @else
        <img src="{{ $logo}}" height="30" alt="">
        @endif
    </a>
    <!-- Sidebar toggle button--><a class="app-sidebar__toggle" href="#" data-toggle="sidebar"
        aria-label="Hide Sidebar"></a>
    <!-- Navbar Right Menu-->
    <ul class="app-nav">
        @include('cms::backend.layout.notif')
        <!--Notification Menu-->
        <li class="item" title="Kunjungi Website"><a class="app-nav__item text-info" href="{{ url('/') }}" target="_blank"><i class="fa fa-globe fa-lg"></i></a></li>


        <li class="dropdown"><a class="app-nav__item text-danger" title="Profile" href="#" data-toggle="dropdown"
                aria-label="Open Profile Menu"><i class="fa fa-user fa-lg"></i></a>
            <ul class="dropdown-menu settings-menu dropdown-menu-right">
                @if(request()->user()->level=='admin')

                <li><a class="dropdown-item" href="{{ route('setting') }}"><i class="fa fa-gear fa-lg"></i> Setting</a>
                </li>
@endif
                <li><a class="dropdown-item" href="{{ route('user.account') }}"><i class="fa fa-user fa-lg"></i> Profile</a>
                </li>


                <li>
                    <form id="logout-form" action="{{ auth()->user()->isAdmin() ? route('logout') : url('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fa fa-sign-out fa-lg"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </li>

    </ul>
</header>
