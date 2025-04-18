<!-- Sidebar menu-->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
@php $userprofile = Auth::user() @endphp
<aside class="app-sidebar" style="background: #1D2327; font-size: 12px">
    <div class="app-sidebar__user" style="cursor: pointer; margin-bottom: 0">
        <img
            class="app-sidebar__user-avatar"
            style="width: 30px; height: 30px"
            src="{{ $userprofile->photo_user }}"
            alt="User Image"
        />
        <div>
            <p class="app-sidebar__user-name">{{ $userprofile->name }}</p>
            <p class="app-sidebar__user-designation">
                {{ ucfirst($userprofile->level) }}
            </p>
        </div>
    </div>

    <ul class="app-menu">
        <li
            class="text-muted"
            style="padding: 12px 10px; font-size: small; background: #000"
        >
            <i class="fa fa-list" aria-hidden="true"></i> MENU UTAMA
        </li>
        <li>
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/dashboard') ? 'active' : '' }}"
                href="{{ route('panel.dashboard') }}"
                ><i class="app-menu__icon fa fa-dashboard"></i>
                <span class="app-menu__label">Dahsboard</span></a
            >
        </li>
        @foreach ($userprofile->isAdmin() ?
        collect(get_module())->sortBy('position') :
        collect(get_module())->sortBy('position')->whereIn('name',
        $userprofile->get_modules->pluck('module')->toArray()) as $row)
        <li class="treeview {{ active_item($row->name) ? 'is-expanded' : '' }}">
            <a
                title="{{ $row->description }}"
                class="app-menu__item"
                href="#"
                data-toggle="treeview"
                ><i class="app-menu__icon fa {{ $row->icon }}"></i
                ><span class="app-menu__label">{{ $row->title }}</span
                ><i class="treeview-indicator fa fa-chevron-right"></i
            ></a>
            <ul class="treeview-menu">
                <li>
                    <a
                        class="treeview-item @if (request()->segment(4) == 'edit') active @endif"
                        href="{{ Route::has($row->name . '.create') ? route($row->name . '.create') : '' }}"
                        ><i class="icon fa fa-plus"></i> Tambah {{ $row->title
                        }}</a
                    >
                </li>
                <li>
                    <a
                        class="treeview-item @if (active_item($row->name) && !request()->segment(3)) active @endif"
                        href="{{ Route::has($row->name) ? route($row->name) : '' }}"
                        ><i class="icon fa fa-table"></i> Daftar {{ $row->title
                        }}</a
                    >
                    @if ($row->form->category)
                </li>

                <li>
                    <a
                        class="treeview-item @if (request()->segment(3) == 'category') active @endif"
                        href="{{ Route::has($row->name . '.category') ? route($row->name . '.category') : '' }}"
                        ><i class="icon fa fa-tags"></i> Category</a
                    >
                    @endif
                </li>
            </ul>
        </li>
        @endforeach
        <li>
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/tags') ? 'active' : '' }}"
                href="{{ admin_url('tags') }}"
                ><i class="app-menu__icon fa fa-hashtag"></i>
                <span class="app-menu__label">Tags</span></a
            >
        </li>
        <li title="Komentar Pengunjung">
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/comments') ? 'active' : '' }}"
                href="{{ admin_url('comments') }}"
                ><i class="app-menu__icon fa fa-comments"></i>
                <span class="app-menu__label">Tanggapan</span></a
            >
        </li>
        <li>
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/files') ? 'active' : '' }}"
                href="{{ admin_url('files') }}"
                ><i class="app-menu__icon fa fa-folder"></i>
                <span class="app-menu__label">File Manager</span></a
            >
        </li>
        <li>
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/polling') ? 'active' : '' }}"
                href="{{ admin_url('polling') }}"
                ><i class="app-menu__icon fa fa-poll"></i>
                <span class="app-menu__label">Polling</span></a
            >
        </li>
        @if ($option = config('modules.config.option'))
        <li>
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/option') ? 'active' : '' }}"
                href="{{ admin_url('option') }}"
                ><i class="app-menu__icon fa fa-table"></i>
                <span class="app-menu__label">Data Web</span></a
            >
        </li>
        @endif @if ($ext = config('modules.extension_module'))
        <li
            class="text-muted"
            style="padding: 12px 10px; font-size: small; background: #000"
        >
            <i class="fa fa-puzzle-piece" aria-hidden="true"></i> &nbsp; MENU
            EXTRA
        </li>

        @foreach (json_decode(json_encode($ext)) as $row)
        <li class="treeview">
            <a
                title="{{ $row->description }}"
                class="app-menu__item"
                href="#"
                data-toggle="treeview"
                ><i class="app-menu__icon fa {{ $row->icon }}"></i
                ><span class="app-menu__label">{{ $row->name }}</span
                ><i class="treeview-indicator fa fa-chevron-right"></i
            ></a>
            <ul class="treeview-menu">
                @foreach ($row->module as $module)
                <li>
                    <a class="treeview-item" href="{{ route($module->route) }}"
                        ><i class="icon fa fa-arrow-right"></i> {{ $module->name
                        }}</a
                    >
                </li>
                @endforeach
            </ul>
        </li>
        @endforeach @endif @if ($userprofile->level == 'admin')
        <li
            class="text-muted"
            style="padding: 12px 10px; font-size: small; background: #000"
        >
            <i class="fa fa-lock" aria-hidden="true"></i> &nbsp; ADMINISTRATOR
        </li>

        <li title="Pengguna">
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/appearance') ? 'active' : '' }}"
                href="{{ admin_url('appearance') }}"
                ><i class="app-menu__icon fa fa-paint-brush"></i>
                <span class="app-menu__label">Tampilan</span></a
            >
        </li>
        {{--
        <li title="Pengguna">
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/ekstension') ? 'active' : '' }}"
                href="{{ admin_url('ekstension') }}"
                ><i class="app-menu__icon fa fa-puzzle-piece"></i>
                <span class="app-menu__label">Ekstensi</span></a
            >
        </li>
        --}}

        <li title="Pengguna">
            <a
                class="app-menu__item {{ active_item(['user', 'role']) }}"
                href="{{ route('user') }}"
                ><i class="app-menu__icon fa fa-users"></i>
                <span class="app-menu__label">Pengguna</span></a
            >
        </li>
        <li title="Pengaturan">
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/setting') ? 'active' : '' }}"
                href="{{ route('setting') }}"
                ><i class="app-menu__icon fa fa-gears"></i>
                <span class="app-menu__label">Pengaturan</span></a
            >
        </li>
        {{--
        <li title="Backup & Restore">
            <a
                class="app-menu__item {{ Request::is(admin_path() . '/backup') ? 'active' : '' }}"
                href="{{ route('backup') }}"
                ><i class="app-menu__icon fa fa-database"></i>
                <span class="app-menu__label">Backup & Restore</span></a
            >
        </li>
        --}} @endif

        <li
            class="text-muted"
            style="padding: 12px 10px; font-size: small; background: #000"
        >
            <small>Build by: </small><b class="text-white">Leazycms</b
            ><sup class="text-danger">{{ get_leazycms_version() }}</sup>
            <a
                target="_blank"
                href="https://leazycms.com/docs"
                class="pull-right"
            >
                <i class="fa fa-book"></i> Docs</a
            >
        </li>
    </ul>
</aside>
