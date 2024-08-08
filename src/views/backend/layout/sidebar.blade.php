<!-- Sidebar menu-->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar" style="background:#1D2327;font-size:12px;">
    <div class="app-sidebar__user" style="cursor:pointer;margin-bottom:0">
        <img class="app-sidebar__user-avatar" style="width:30px;height:30px" src="{{ request()->user()->user_photo }}" alt="User Image">
        <div>
            <p class="app-sidebar__user-name">{{ Auth::user()->name }}</p>
            <p class="app-sidebar__user-designation">{{ ucfirst(Auth::user()->level) }}</p>
        </div>
    </div>

    <ul class="app-menu">
        <li class="text-muted" style="padding:12px 10px;font-size:small;background:#000"> <i class="fa fa-list"
                aria-hidden="true"></i> MENU</li>
        <li>
            <a class="app-menu__item {{ Request::is(admin_path() . '/dashboard') ? 'active' : '' }}" href="{{ route('panel.dashboard') }}"><i class="app-menu__icon fa fa-tachometer"></i> <span class="app-menu__label">Dahsboard</span></a></li>
            @foreach (request()->user()->isAdmin() ? collect(get_module())->sortBy('position') : collect(get_module())->sortBy('position')->whereIn('name', request()->user()->get_modules->pluck('module')->toArray()) as $row)
            <li title="">
                <a class="app-menu__item {{ active_item($row->name) }}" href="{{ route($row->name) }}">
                        <i class="app-menu__icon fa {{ $row->icon }}"></i>
                        <span  class="app-menu__label">{{ $row->title }}</span>
                </a>
            </li>
        @endforeach

        {{-- <li title="Komentar Pengunjung"><a
                class="app-menu__item {{ Request::is(admin_path() . '/comments') ? 'active' : '' }}"
                href="{{ admin_url('comments') }}"><i class="app-menu__icon fa fa-comments"></i> <span
                    class="app-menu__label">Tanggapan</span></a></li> --}}
        @if (Auth::user()->level == 'admin')
            <li class="text-muted" style="padding:12px 10px;font-size:small;background:#000"><i class="fa fa-lock"
                    aria-hidden="true"></i> &nbsp; ADMINISTRATOR</li>
                    <li ><a
                        class="app-menu__item {{ Request::is(admin_path() . '/tags') ? 'active' : '' }}"
                        href="{{ admin_url('tags') }}"><i class="app-menu__icon fa fa-hashtag"></i> <span
                            class="app-menu__label">Tags</span></a></li>

            <li title="Pengguna"><a class="app-menu__item {{ Request::is(admin_path() . '/appearance') ? 'active' : '' }}" href="{{ admin_url('appearance') }}"><i class="app-menu__icon fa fa-paint-brush"></i> <span class="app-menu__label">Tampilan</span></a></li>
            {{-- <li title="Pengguna"><a class="app-menu__item {{ Request::is(admin_path() . '/ekstension') ? 'active' : '' }}" href="{{ admin_url('ekstension') }}"><i class="app-menu__icon fa fa-puzzle-piece"></i> <span class="app-menu__label">Ekstensi</span></a></li> --}}

            <li title="Pengguna"><a class="app-menu__item {{ active_item(['user','role']) }}" href="{{ route('user') }}"><i class="app-menu__icon fa fa-users"></i> <span class="app-menu__label">Pengguna</span></a></li>
            <li title="Pengaturan"><a class="app-menu__item {{ Request::is(admin_path() . '/setting') ? 'active' : '' }}"  href="{{ route('setting') }}"><i class="app-menu__icon fa fa-gears"></i> <span class="app-menu__label">Pengaturan</span></a></li>
        @endif

        <li class="text-muted" style="padding:12px 10px;font-size:small;background:#000"><small>Build by: </small> <span class="text-success">Lara</pan><b class="text-white">mix</b><sup class="text-danger">.ID</sup></li>
    </ul>
</aside>
