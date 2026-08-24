<!-- Sidebar menu-->
<style>
.app-sidebar {
    overflow-y: auto !important;
    overflow-x: hidden !important;
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
.app-sidebar::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
    width: 0;
}
.sidebar-mini.sidenav-toggled .app-sidebar {
    overflow-y: auto !important;
    overflow-x: hidden !important;
}
.app-menu {
    padding-right: 0 !important;
    margin-right: 0 !important;
    width: 100%;
}
.app-menu__item {
    width: 100% !important;
}
.sidebar-list-header {
    display: flex;
    align-items: center;
    padding: 12px 14px;
    font-size: 11px;
    letter-spacing: 0.5px;
    white-space: nowrap;
    overflow: hidden;
    transition: all 0.2s ease-in-out;
}
.sidebar-list-header i {
    width: 20px;
    text-align: center;
    font-size: 13px;
    flex-shrink: 0;
}
.sidebar-header-label {
    display: inline-block;
}

.treeview-header-title {
    display: none;
}

/* =========================================================================
 * DESKTOP ONLY: MINI SIDEBAR (when screen width >= 768px)
 * ========================================================================= */
@media (min-width: 768px) {
    .sidebar-mini.sidenav-toggled .app-sidebar {
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    .sidebar-mini.sidenav-toggled .sidebar-list-header {
        padding: 12px 0 !important;
        justify-content: center !important;
        text-align: center !important;
        width: 50px !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .sidebar-mini.sidenav-toggled .sidebar-list-header i {
        width: 50px !important;
        margin: 0 !important;
        font-size: 14px !important;
        display: block !important;
        text-align: center !important;
        opacity: 0.85;
    }

    .sidebar-mini.sidenav-toggled .sidebar-list-header {
        position: relative;
        cursor: pointer;
    }

    .sidebar-mini.sidenav-toggled .sidebar-list-header:hover {
        background: var(--sidebar-hover-bg, rgba(255, 255, 255, 0.08)) !important;
    }

    .sidebar-mini.sidenav-toggled .sidebar-header-label {
        display: none;
    }

    /* Floating Pill for Sidebar List Header on Hover in Mini Mode */
    .sidebar-mini.sidenav-toggled .sidebar-list-header:hover .sidebar-header-label {
        position: fixed !important;
        left: 50px !important;
        min-width: 160px !important;
        max-width: 240px !important;
        height: 38px !important;
        line-height: 38px !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: flex !important;
        align-items: center !important;
        background: var(--sidebar-bg, #1D2327) !important;
        color: var(--sidebar-font, #ffffff) !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        letter-spacing: 0.8px !important;
        text-transform: uppercase !important;
        padding: 0 16px !important;
        margin: 0 !important;
        border-radius: 0 6px 6px 0 !important;
        box-shadow: 4px 4px 16px rgba(0, 0, 0, 0.4) !important;
        border-left: 3px solid var(--theme-primary, #0d6efd) !important;
        z-index: 9999 !important;
        pointer-events: none !important;
        white-space: nowrap !important;
        box-sizing: border-box !important;
    }

    .sidebar-mini.sidenav-toggled .sidebar-footer-info {
        display: none !important;
    }
    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu > li {
        position: relative;
    }

    /* Mini Sidebar Menu Item Base, Hover, and Active State */
    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu__item {
        background: transparent !important;
        border-left: 3px solid transparent !important;
        color: var(--sidebar-font) !important;
        transition: background-color 0.2s ease, border-left-color 0.2s ease !important;
    }

    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu > li:hover > .app-menu__item,
    .sidebar-mini.sidenav-toggled .app-sidebar .treeview:hover > .app-menu__item,
    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu__item:hover,
    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu__item:focus {
        background: var(--sidebar-list-bg, var(--sidebar-bg)) !important;
        border-left-color: var(--theme-primary, #0d6efd) !important;
        color: var(--sidebar-font, #ffffff) !important;
    }

    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu > li:hover > .app-menu__item .app-menu__icon,
    .sidebar-mini.sidenav-toggled .app-sidebar .treeview:hover > .app-menu__item .app-menu__icon,
    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu__item:hover .app-menu__icon {
        color: var(--sidebar-font, #ffffff) !important;
    }

    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu__item.active,
    .sidebar-mini.sidenav-toggled .app-sidebar .treeview.is-expanded > .app-menu__item {
        background: var(--sidebar-hover-bg, rgba(255, 255, 255, 0.1)) !important;
        border-left-color: var(--theme-primary, #0d6efd) !important;
        color: var(--sidebar-font, #ffffff) !important;
    }

    /* Standalone Menu Label (Items without treeview submenu) */
    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu > li:not(.treeview):hover > .app-menu__item > .app-menu__label {
        position: fixed !important;
        left: 50px !important;
        min-width: 190px !important;
        max-width: 280px !important;
        height: 44px !important;
        line-height: 44px !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: flex !important;
        align-items: center !important;
        background: var(--sidebar-list-bg, #111518) !important;
        color: var(--sidebar-font, #ffffff) !important;
        font-weight: 600 !important;
        font-size: 13px !important;
        padding: 0 18px !important;
        margin: 0 !important;
        margin-left: 0 !important;
        border-radius: 0 8px 8px 0 !important;
        box-shadow: 4px 4px 18px rgba(0, 0, 0, 0.4) !important;
        z-index: 9999 !important;
        pointer-events: auto !important;
        white-space: nowrap !important;
        box-sizing: border-box !important;
    }

    /* In mini mode, hide the separate floating label for treeview because it is inside the treeview-menu */
    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu > li.treeview > .app-menu__item > .app-menu__label {
        display: none !important;
    }

    /* Treeview Unified Popup Container (Single Box) */
    .sidebar-mini.sidenav-toggled .app-sidebar .app-menu > li.treeview:hover > .treeview-menu {
        position: fixed !important;
        left: 50px !important;
        min-width: 220px !important;
        max-width: 320px !important;
        opacity: 1 !important;
        visibility: visible !important;
        display: block !important;
        background: var(--sidebar-list-bg, #111518) !important;
        padding: 0 0 6px 0 !important;
        margin: 0 !important;
        margin-left: 0 !important;
        border-top: none !important;
        border-radius: 0 8px 8px 0 !important;
        box-shadow: 4px 8px 25px rgba(0, 0, 0, 0.45) !important;
        z-index: 9999 !important;
        pointer-events: auto !important;
        box-sizing: border-box !important;
        overflow: hidden !important;
    }

    /* Treeview Header Title inside the unified menu */
    .sidebar-mini.sidenav-toggled .app-sidebar .treeview-menu .treeview-header-title {
        display: block !important;
        padding: 12px 18px !important;
        margin: 0 !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        letter-spacing: 0.5px !important;
        text-transform: uppercase !important;
        background: var(--sidebar-bg, #1D2327) !important;
        color: var(--sidebar-font, #ffffff) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        white-space: nowrap !important;
    }

    /* Submenu Item Links */
    .sidebar-mini.sidenav-toggled .app-sidebar .treeview-menu .treeview-item {
        padding: 9px 18px !important;
        display: flex !important;
        align-items: center !important;
        gap: 10px;
        color: var(--sidebar-muted, #a0aabf) !important;
        font-size: 12.5px !important;
        font-weight: 500 !important;
        white-space: nowrap !important;
        transition: all 0.15s ease !important;
    }

    .sidebar-mini.sidenav-toggled .app-sidebar .treeview-menu .treeview-item i {
        font-size: 11px !important;
        width: 14px !important;
        text-align: center;
        opacity: 0.8;
    }

    .sidebar-mini.sidenav-toggled .app-sidebar .treeview-menu .treeview-item:hover {
        background: var(--sidebar-hover-bg, rgba(255, 255, 255, 0.1)) !important;
        color: var(--sidebar-hover-color, #ffffff) !important;
        padding-left: 22px !important;
    }
}
</style>
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
@auth
    @php $userprofile = Auth::user() @endphp
    <aside class="app-sidebar" style="font-size: 12px">
        <div class="app-sidebar__user" style="cursor: pointer; margin-bottom: 0;padding-top:0;margin-top:-7px">
            <img alt="User Photo" class="app-sidebar__user-avatar" style="width: 30px; height: 30px"
                src="{{ $userprofile->photo_user }}">
            <div>
                <p class="app-sidebar__user-name">{{ str($userprofile->name)->limit(10) }}</p>
                <p class="app-sidebar__user-designation">
                    {{ ucfirst($userprofile->level) }}
                </p>
            </div>
        </div>

        <ul class="app-menu">
                <li>
                    <a class="app-menu__item {{ Request::is(admin_path() . '/dashboard') ? 'active' : '' }}"
                        href="{{ route('panel.dashboard') }}"><i class="app-menu__icon fa fa-dashboard "></i>
                        <span class="app-menu__label">Dahsboard</span></a>
                </li>
            <li class="sidebar-list-header" title="Publikasi">
                <i class="fa fa-globe" aria-hidden="true"></i> <span class="sidebar-header-label">&nbsp; PUBLIKASI</span>
            </li>
            @php
                $modulesForSidebar = collect(get_module())->sortBy('position');
                if (config('modules.multisite_enabled')) {
                    $disallowedModules = app()->bound('tenant') ? app('tenant')->modules ?? [] : [];
                    if (is_string($disallowedModules)) {
                        $disallowedModules = json_decode($disallowedModules, true) ?? [];
                    }
                    if (is_array($disallowedModules) && count($disallowedModules) > 0) {
                        $modulesForSidebar = $modulesForSidebar->whereNotIn('name', $disallowedModules);
                    }
                }

                if (!$userprofile->isAdmin()) {
                    $modulesForSidebar = $modulesForSidebar->whereIn(
                        'name',
                        $userprofile->get_modules->pluck('module')->toArray(),
                    );
                }
            @endphp
            @foreach ($modulesForSidebar as $row)
                @if ($row->name == 'menu')
                    <li class="sidebar-list-header" title="Kelola">
                        <i class="fa fa-archive" aria-hidden="true"></i> <span class="sidebar-header-label">&nbsp; KELOLA</span>
                    </li>
                @endif
                <li class="treeview {{ active_item($row->name) ? 'is-expanded' : '' }}">
                    <a title="{{ $row->description }}" class="app-menu__item" href="#" data-toggle="treeview"><i
                            class="app-menu__icon fa {{ $row->icon }}"></i><span
                            class="app-menu__label">{{ $row->title }}</span><i
                            class="treeview-indicator fa fa-chevron-right"></i></a>
                    <ul class="treeview-menu">
                        <li class="treeview-header-title">{{ $row->title }}</li>
                        @if (in_array('create', $row->route))
                            @if (auth()->user()->isAdmin() || !auth()->user()->hasRole($row->name, 'create', true))
                                <li>
                                    <a class="treeview-item @if (request()->segment(4) == 'edit') active @endif"
                                        href="{{ Route::has($row->name . '.create') ? route($row->name . '.create') : '' }}"><i
                                            class="icon fa fa-plus "></i> Tambah {{ $row->title }}</a>
                                </li>
                            @endif
                        @endif
                        <li>
                            <a class="treeview-item @if (active_item($row->name) && !request()->segment(3)) active @endif"
                                href="{{ Route::has($row->name) ? route($row->name) : '' }}"><i
                                    class="icon fa fa-table "></i> Daftar {{ $row->title }}</a>
                        </li>
                        @if ($row->form->category)
                            @if (auth()->user()->isAdmin() ||
                                    !auth()->user()->hasRole('category' . $row->name, 'index', true))
                                <li>
                                    <a class="treeview-item @if (request()->segment(3) == 'category') active @endif"
                                        href="{{ Route::has($row->name . '.category') ? route($row->name . '.category') : '' }}"><i
                                            class="icon fa fa-tags "></i> Kategori</a>
                                </li>
                            @endif
                        @endif

                    </ul>
                </li>
            @endforeach
            @if (Auth::user()->isAdmin())
                <li>
                    <a class="app-menu__item {{ Request::is(admin_path() . '/tags') ? 'active' : '' }}"
                        href="{{ admin_url('tags') }}"><i class="app-menu__icon fa fa-hashtag "></i>
                        <span class="app-menu__label ">Tags</span></a>
                </li>
                @if (is_main_domain())
                    <li title="Komentar Pengunjung">
                        <a class="app-menu__item {{ Request::is(admin_path() . '/comments') ? 'active' : '' }}"
                            href="{{ admin_url('comments') }}"><i class="app-menu__icon fa fa-comments "></i>
                            <span class="app-menu__label">Komentar</span></a>
                    </li>
                    <li>
                        <a class="app-menu__item {{ Request::is(admin_path() . '/files') ? 'active' : '' }}"
                            href="{{ admin_url('files') }}"><i class="app-menu__icon fa fa-folder  "></i>
                            <span class="app-menu__label">File Manager</span></a>
                    </li>
                @endif
                <li>
                    <a class="app-menu__item {{ Request::is(admin_path() . '/polling') ? 'active' : '' }}"
                        href="{{ admin_url('polling') }}"><i class="app-menu__icon fa fa-poll  "></i>
                        <span class="app-menu__label">Polling</span></a>
                </li>
            @endif
                @foreach (array_filter(config('modules.config.option', []), fn($value, $key) => $key !== 'template', ARRAY_FILTER_USE_BOTH) as $k => $row)
                    <li>
                        <a class="app-menu__item {{ Request::is(admin_path() . '/option/' . str($k)->slug()) ? 'active' : '' }}"
                            href="{{ route('option', str($k)->slug()) }}"><i
                                class="app-menu__icon fa fa-list-alt "></i>
                            <span class="app-menu__label">{{ str($k)->headline() }}</span></a>
                    </li>
                @endforeach
            @if (Auth::user()->isAdmin())
                @if ($custom = config('modules.custom_menu'))
                    @php
                        $customMenus = collect($custom)->where('show_in_sidebar', true);
                        $groupedPlugins = [];
                        $standaloneMenus = [];

                        $tenantPlugins = [];
                        if (config('modules.multisite_enabled')) {
                            $tenantPlugins = app('tenant')->plugins ?? [];
                            $tenantPlugins = is_string($tenantPlugins) ? json_decode($tenantPlugins, true) : $tenantPlugins;
                        }

                        foreach ($customMenus as $cs) {
                            $segments = explode('/', $cs['path']);
                            $potentialPlugin = $segments[0] ?? null;

                            if ($potentialPlugin && is_dir(resource_path('plugins/' . $potentialPlugin))) {
                                $disabledPlugins = get_disabled_plugins();
                                if (in_array($potentialPlugin, $disabledPlugins)) {
                                    continue;
                                }

                                if (config('modules.multisite_enabled') && !is_main_domain()) {
                                    if (!is_array($tenantPlugins) || !in_array($potentialPlugin, $tenantPlugins)) {
                                        continue;
                                    }
                                }
                                $groupedPlugins[$potentialPlugin][] = $cs;
                            } else {
                                $standaloneMenus[] = $cs;
                            }
                        }
                    @endphp

                    @if (count($standaloneMenus) > 0 || count($groupedPlugins) > 0)
                        <li class="sidebar-list-header" title="Plugin">
                            <i class="fa fa-puzzle-piece" aria-hidden="true"></i> <span class="sidebar-header-label">&nbsp; Plugin</span>
                        </li>

                        @foreach ($standaloneMenus as $cs)
                            <li title="{{ $cs['title'] }}">
                                <a class="app-menu__item {{ active_item($cs['path']) }}"
                                    href="{{ admin_url($cs['path']) }}"><i
                                        class="app-menu__icon fa {{ $cs['icon'] }} "></i>
                                    <span class="app-menu__label">{{ $cs['title'] }}</span></a>
                            </li>
                        @endforeach

                        @foreach ($groupedPlugins as $pluginName => $menus)
                            @php
                                $isActive = false;
                                foreach ($menus as $m) {
                                    if (request()->is(admin_path() . '/' . $m['path'] . '*')) {
                                        $isActive = true;
                                        break;
                                    }
                                }
                            @endphp
                            <li class="treeview {{ $isActive ? 'is-expanded' : '' }}">
                                <a class="app-menu__item" href="#" data-toggle="treeview">
                                    <i class="app-menu__icon fa fa-plug "></i>
                                    <span class="app-menu__label">{{ Str::title(str_replace('-', ' ', $pluginName)) }}</span>
                                    <i class="treeview-indicator fa fa-angle-right"></i>
                                </a>
                                <ul class="treeview-menu">
                                    <li class="treeview-header-title">{{ Str::title(str_replace('-', ' ', $pluginName)) }}</li>
                                    @foreach ($menus as $cs)
                                        <li>
                                            <a class="treeview-item {{ active_item($cs['path']) }}"
                                                href="{{ admin_url($cs['path']) }}"><i
                                                class="icon fa {{ $cs['icon'] }}"></i> {{ $cs['title'] }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    @endif
                @endif
            @endif
            @if (Auth::user()->isAdmin())
                <li class="sidebar-list-header" title="Administrator">
                    <i class="fa fa-lock" aria-hidden="true"></i> <span class="sidebar-header-label">&nbsp; Administrator</span>
                </li>
                @if (config('modules.app_master') && is_main_domain())
                    <li title="Monitor Situs">
                        <a class="app-menu__item {{ active_item(['site-monitor']) }}"
                            href="{{ route('app.master.index') }}"><i
                                class="app-menu__icon fa fa-desktop "></i>
                            <span class="app-menu__label">Monitor Web</span></a>
                    </li>
                @endif
        
                {{-- <li title="Webmail">
                <a
                    class="app-menu__item {{ active_item(['email']) }}"
        href="{{ route('email.index') }}"
        ><i class="app-menu__icon fa fa-at"></i>
        <span class="app-menu__label">Webmail</span></a>
        </li> --}}
                <li
                    class="treeview {{ active_item(['setting', 'appearance', 'cache', 'profile','plugins']) ? 'is-expanded' : '' }}">
                    <a class="app-menu__item" href="#" data-toggle="treeview"><i
                            class="app-menu__icon fa fa-gear "></i><span
                            class="app-menu__label">Pengaturan</span><i class="treeview-indicator fa fa-chevron-right"></i>
                    </a>

                    <ul class="treeview-menu">
                        <li class="treeview-header-title">Pengaturan</li>
                        <li>
                            <a class="treeview-item {{ active_item('profile') }}"
                                href="{{ Route::has('profile') ? route('profile') : '#' }}"><i
                                    class="icon fa fa-building "></i> Profile </a>
                        </li>

                        <li>
                            <a class="treeview-item {{ active_item('appearance') }}" href="{{ route('appearance') }}"><i
                                    class="icon fa fa-brush "></i> Template</a>
                        </li>
                        <li>
                            <a class="treeview-item {{ active_item('setting') }}" href="{{ route('setting') }}"><i
                                    class="icon fa fa-globe "></i> Website</a>
                        </li>
                        @if (is_main_domain())
                           <li>
                                <a class="treeview-item {{ active_item(val: 'plugins') }}"
                                    href="{{ route('admin.plugins') }}"><i class="icon fa fa-plug "></i>
                                    Plugins</a>
                            </li>
                            <li>
                                <a class="treeview-item {{ active_item(val: 'cache') }}"
                                    href="{{ route('cache-manager') }}"><i class="icon fa fa-flash "></i>
                                    Cache</a>
                            </li>
                        @endif
                    </ul>
                </li>

                @if (is_main_domain() || in_array(get_option('allow_manage_user'), ['1', 1, 'true', true, 'Y', 'y'], true))
                    <li title="Pengguna">
                        <a class="app-menu__item {{ active_item(['user', 'role']) }}" href="{{ route('user') }}"><i
                                class="app-menu__icon fa fa-users "></i>
                            <span class="app-menu__label">User</span></a>
                    </li>
                @endif
                @if (is_main_domain() && config('modules.multisite_enabled'))
                    <li title="Tenants">
                        <a class="app-menu__item {{ active_item(['tenant']) }}" href="{{ route('tenant.index') }}"><i
                                class="app-menu__icon fa fa-globe "></i>
                            <span class="app-menu__label">Tenants</span></a>
                    </li>
                    <li title="Themes">
                        <a class="app-menu__item {{ active_item(['theme']) }}" href="{{ route('theme.index') }}"><i
                                class="app-menu__icon fa fa-paint-brush "></i>
                            <span class="app-menu__label">Themes</span></a>
                    </li>
                @endif
                @if (!config('modules.multisite_enabled') || is_main_domain())
                <li title="Backup & Restore">
                    <a class="app-menu__item {{ active_item(['backup']) }}" href="{{ route('backup') }}"><i
                            class="app-menu__icon fa fa-database "></i>
                        <span class="app-menu__label">Backup</span></a>
                </li>
                @endif
                @if (is_main_domain())
                    <li title="Logs">
                        <a class="app-menu__item {{ active_item(['logs']) }}" href="{{ route('panel.logs') }}"><i
                                class="app-menu__icon fa fa-history "></i>
                            <span class="app-menu__label">Logs</span></a>
                    </li>
                    <li>
                        <a class="app-menu__item {{ Request::is(admin_path() . '/security/blocked-ip') ? 'active' : '' }}"
                            href="{{ route('blocked-ip') }}"><i class="app-menu__icon fa fa-shield-alt "></i>
                            <span class="app-menu__label">Blocked IP</span></a>
                    </li>
                    <li title="API Key">
                        <a class="app-menu__item {{ active_item(['apikey']) }}" href="{{ route('apikey') }}"><i
                                class="app-menu__icon fa fa-key "></i>
                            <span class="app-menu__label">API Key</span></a>
                    </li>
                @endif
            @endif
            <li>
                <a class="app-menu__item" href="javascript:void(0)"
                    onclick="confirmLogout(event)"><i
                        class="app-menu__icon fa fa-sign-out "></i>
                    <span class="app-menu__label">Keluar</span></a>
            </li>
            @if (is_main_domain())
                <li class="sidebar-list-header sidebar-footer-info">
                    <span class="sidebar-header-label">
                        <small>Build by: </small><b class="text-white">Leazycms</b><sup
                            class="">{{ current_cms_version() }}</sup>
                        <a target="_blank" href="https://leazycms.web.id/docs" class="pull-right">
                            <i class="fa fa-book"></i> Docs</a>
                    </span>
                </li>
            @endif
        </ul>
    </aside>
@endauth
