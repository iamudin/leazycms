<!-- Sidebar menu-->
<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
@auth
    @php $userprofile = Auth::user() @endphp
    <aside class="app-sidebar" style="background: #1D2327; font-size: 12px">
        <div class="app-sidebar__user" style="cursor: pointer; margin-bottom: 0">
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
            @if (!in_array(Auth::user()->level, collect(config('modules.extension_module'))->pluck('path')->toArray()))
                <li>
                    <a class="app-menu__item {{ Request::is(admin_path() . '/dashboard') ? 'active' : '' }}"
                        href="{{ route('panel.dashboard') }}"><i class="app-menu__icon fa fa-dashboard text-danger"></i>
                        <span class="app-menu__label">Dahsboard</span></a>
                </li>
            @endif
            <li class="text-muted" style="padding: 12px 10px; font-size: small; background: #000">
                <i class="fa fa-globe" aria-hidden="true"></i> &nbsp; PUBLIKASI
            </li>
            @php
                $modulesForSidebar = collect(get_module())->sortBy('position');
                if (config('modules.multisite_enabled')) {
                    $tenantModules = app()->bound('tenant') ? app('tenant')->modules ?? [] : [];
                    $modulesForSidebar = $modulesForSidebar->whereIn(
                        'name',
                        array_merge($tenantModules, default_menu()),
                    );
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
                    <li class="text-muted" style="padding: 12px 10px; font-size: small; background: #000">
                        <i class="fa fa-archive" aria-hidden="true"></i> &nbsp; KELOLA
                    </li>
                @endif
                <li class="treeview {{ active_item($row->name) ? 'is-expanded' : '' }}">
                    <a title="{{ $row->description }}" class="app-menu__item" href="#" data-toggle="treeview"><i
                            class="app-menu__icon text-danger fa {{ $row->icon }}"></i><span
                            class="app-menu__label">{{ $row->title }}</span><i
                            class="treeview-indicator fa fa-chevron-right"></i></a>
                    <ul class="treeview-menu">
                        @if (in_array('create', $row->route))
                            @if (auth()->user()->isAdmin() || !auth()->user()->hasRole($row->name, 'create', true))
                                <li>
                                    <a class="treeview-item @if (request()->segment(4) == 'edit') active @endif"
                                        href="{{ Route::has($row->name . '.create') ? route($row->name . '.create') : '' }}"><i
                                            class="icon fa fa-plus text-warning"></i> Tambah {{ $row->title }}</a>
                                </li>
                            @endif
                        @endif
                        <li>
                            <a class="treeview-item @if (active_item($row->name) && !request()->segment(3)) active @endif"
                                href="{{ Route::has($row->name) ? route($row->name) : '' }}"><i
                                    class="icon fa fa-table text-info"></i> Daftar {{ $row->title }}</a>
                        </li>
                        @if ($row->form->category)
                            @if (auth()->user()->isAdmin() ||
                                    !auth()->user()->hasRole('category' . $row->name, 'index', true))
                                <li>
                                    <a class="treeview-item @if (request()->segment(3) == 'category') active @endif"
                                        href="{{ Route::has($row->name . '.category') ? route($row->name . '.category') : '' }}"><i
                                            class="icon fa fa-tags text-success"></i> Kategori</a>
                                </li>
                            @endif
                        @endif

                    </ul>
                </li>
            @endforeach
            @if (Auth::user()->level == 'admin')
                <li>
                    <a class="app-menu__item {{ Request::is(admin_path() . '/tags') ? 'active' : '' }}"
                        href="{{ admin_url('tags') }}"><i class="app-menu__icon fa fa-hashtag text-danger"></i>
                        <span class="app-menu__label ">Tags</span></a>
                </li>
                @if (is_main_domain())
                    <li title="Komentar Pengunjung">
                        <a class="app-menu__item {{ Request::is(admin_path() . '/comments') ? 'active' : '' }}"
                            href="{{ admin_url('comments') }}"><i class="app-menu__icon fa fa-comments text-danger"></i>
                            <span class="app-menu__label">Komentar</span></a>
                    </li>
                    <li>
                        <a class="app-menu__item {{ Request::is(admin_path() . '/files') ? 'active' : '' }}"
                            href="{{ admin_url('files') }}"><i class="app-menu__icon fa fa-folder  text-danger"></i>
                            <span class="app-menu__label">File Manager</span></a>
                    </li>
                @endif
                <li>
                    <a class="app-menu__item {{ Request::is(admin_path() . '/polling') ? 'active' : '' }}"
                        href="{{ admin_url('polling') }}"><i class="app-menu__icon fa fa-poll  text-danger"></i>
                        <span class="app-menu__label">Polling</span></a>
                </li>
            @endif
            @if (is_main_domain())
                @foreach (array_filter(config('modules.config.option', []), fn($value, $key) => $key !== 'template', ARRAY_FILTER_USE_BOTH) as $k => $row)
                    <li>
                        <a class="app-menu__item {{ Request::is(admin_path() . '/option/' . str($k)->slug()) ? 'active' : '' }}"
                            href="{{ route('option', str($k)->slug()) }}"><i
                                class="app-menu__icon fa fa-list-alt text-warning"></i>
                            <span class="app-menu__label">{{ str($k)->headline() }}</span></a>
                    </li>
                @endforeach
            @endif
            @if (Auth::user()->level == 'admin')
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

                            if ($potentialPlugin && (is_dir(resource_path('plugins/' . $potentialPlugin)) || is_dir(resource_path('plugins/' . $potentialPlugin)))) {                                $disabledPlugins = get_disabled_plugins();
                                if (in_array($potentialPlugin, $disabledPlugins)) {
                                    continue;
                                }

                                if (config('modules.multisite_enabled') ) {
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

                    @foreach ($standaloneMenus as $cs)
                        <li title="{{ $cs['title'] }}">
                            <a class="app-menu__item {{ active_item($cs['path']) }}"
                                href="{{ admin_url($cs['path']) }}"><i
                                    class="app-menu__icon fa {{ $cs['icon'] }} text-primary"></i>
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
                                <i class="app-menu__icon fa fa-plug text-primary"></i>
                                <span class="app-menu__label">{{ Str::title(str_replace('-', ' ', $pluginName)) }}</span>
                                <i class="treeview-indicator fa fa-angle-right"></i>
                            </a>
                            <ul class="treeview-menu">
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

            @if (get_option('sub_app_enabled') == 'Y' && !config('modules.multisite_enabled'))

                @if ($ext = config('modules.extension_module'))
                    @if (Auth::user()->level == 'admin')
                        <li class="text-muted" style="padding: 12px 10px; font-size: small; background: #000">
                            <i class="fa fa-puzzle-piece" aria-hidden="true"></i> &nbsp; APLIKASI
                        </li>
                    @endif
                    @foreach (json_decode(json_encode($ext)) as $row)
                        @if ((Auth::user()->level != 'admin' && Auth::user()->level == $row->path) || Auth::user()->level == 'admin')
                            @if (auth()->user()->isAdmin())
                                <li
                                    class="treeview {{ Str::contains($row->path . '/' . request()->segment(3), collect($row->module)->pluck('path')->toArray()) ? 'is-expanded' : null }}">
                                    <a title="{{ $row->description }}" class="app-menu__item" href="#"
                                        data-toggle="treeview"><i
                                            class="app-menu__icon fa {{ $row->icon }} text-success"></i><span
                                            class="app-menu__label">{{ $row->name }}</span><i
                                            class="treeview-indicator fa fa-chevron-right"></i></a>
                                    <ul class="treeview-menu">

                                        @foreach (collect($row->module)->where('only_admin', true) as $module)
                                            <li>
                                                <a class="treeview-item {{ str_contains(url()->full(), $module->path) ? 'active' : '' }}"
                                                    href="{{ Route::has(config($row->path . '.route') . $module->route) ? route(config($row->path . '.route') . $module->route) : '#' }}"><i
                                                        class="icon fa {{ $module->icon }}"></i> {{ $module->name }}</a>
                                            </li>
                                        @endforeach
                                        <li>
                                            <a class="treeview-item " title="{{ $row->url }}"
                                                onclick="return confirm('Buka alamat aplikasi {{ $row->url }}')"
                                                href="{{ $row->url }}" target="_blank"><i class="icon fa fa-globe"></i>
                                                Buka Aplikasi</a>
                                        </li>
                                    </ul>
                                </li>
                            @else
                                @foreach (collect($row->module)->where('only_admin', false) as $module)
                                    <li title="{{ $module->name }}">
                                        <a class="app-menu__item {{ str_contains(url()->full(), $module->path) ? 'active' : '' }}"
                                            href="{{ route($module->route) }}"><i
                                                class="app-menu__icon fa {{ $module->icon }} text-warning"></i>
                                            <span class="app-menu__label">{{ $module->name }}</span></a>
                                    </li>
                                @endforeach
                            @endif
                        @endif
                    @endforeach
                @endif

            @endif

            @if (Auth::user()->isAdmin())
                <li class="text-muted" style="padding: 12px 10px; font-size: small; background: #000">
                    <i class="fa fa-lock" aria-hidden="true"></i> &nbsp; Administrator
                </li>
                @if (config('modules.app_master') && is_main_domain())
                    <li title="Monitor Situs">
                        <a class="app-menu__item {{ active_item(['site-monitor']) }}"
                            href="{{ route('app.master.index') }}"><i
                                class="app-menu__icon fa fa-desktop text-danger"></i>
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
                            class="app-menu__icon fa fa-gear text-danger"></i><span
                            class="app-menu__label">Setting</span><i class="treeview-indicator fa fa-chevron-right"></i>
                    </a>

                    <ul class="treeview-menu">
                        <li>
                            <a class="treeview-item {{ active_item('profile') }}"
                                href="{{ \Illuminate\Support\Facades\Route::has('profile') ? route('profile') : '#' }}"><i
                                    class="icon fa fa-building text-primary"></i> Profile </a>
                        </li>

                        <li>
                            <a class="treeview-item {{ active_item('appearance') }}" href="{{ route('appearance') }}"><i
                                    class="icon fa fa-brush text-primary"></i> Template</a>
                        </li>
                        <li>
                            <a class="treeview-item {{ active_item('setting') }}" href="{{ route('setting') }}"><i
                                    class="icon fa fa-globe text-success"></i> Website</a>
                        </li>
                        @if (is_main_domain())
                           <li>
                                <a class="treeview-item {{ active_item(val: 'plugins') }}"
                                    href="{{ route('admin.plugins') }}"><i class="icon fa fa-plug text-info"></i>
                                    Plugins</a>
                            </li>
                            <li>
                                <a class="treeview-item {{ active_item(val: 'cache') }}"
                                    href="{{ route('cache-manager') }}"><i class="icon fa fa-flash text-warning"></i>
                                    Cache</a>
                            </li>
                        @endif
                    </ul>
                </li>

                <li title="Pengguna">
                    <a class="app-menu__item {{ active_item(['user', 'role']) }}" href="{{ route('user') }}"><i
                            class="app-menu__icon fa fa-users text-danger"></i>
                        <span class="app-menu__label">User</span></a>
                </li>
                @if (is_main_domain() && config('modules.multisite_enabled'))
                    <li title="Tenants">
                        <a class="app-menu__item {{ active_item(['tenant']) }}" href="{{ route('tenant.index') }}"><i
                                class="app-menu__icon fa fa-globe text-primary"></i>
                            <span class="app-menu__label">Tenants</span></a>
                    </li>
                    <li title="Themes">
                        <a class="app-menu__item {{ active_item(['theme']) }}" href="{{ route('theme.index') }}"><i
                                class="app-menu__icon fa fa-paint-brush text-warning"></i>
                            <span class="app-menu__label">Themes</span></a>
                    </li>
                @endif
                <li title="Backup & Restore">
                    <a class="app-menu__item {{ active_item(['backup']) }}" href="{{ route('backup') }}"><i
                            class="app-menu__icon fa fa-database text-warning"></i>
                        <span class="app-menu__label">Backup</span></a>
                </li>
                @if (is_main_domain())
                    <li title="Logs">
                        <a class="app-menu__item {{ active_item(['logs']) }}" href="{{ route('panel.logs') }}"><i
                                class="app-menu__icon fa fa-history text-danger"></i>
                            <span class="app-menu__label">Logs</span></a>
                    </li>
                    <li>
                        <a class="app-menu__item {{ Request::is(admin_path() . '/security/blocked-ip') ? 'active' : '' }}"
                            href="{{ route('blocked-ip') }}"><i class="app-menu__icon fa fa-shield-alt text-danger"></i>
                            <span class="app-menu__label">Blocked IP</span></a>
                    </li>
                    <li title="API Key">
                        <a class="app-menu__item {{ active_item(['apikey']) }}" href="{{ route('apikey') }}"><i
                                class="app-menu__icon fa fa-key text-danger"></i>
                            <span class="app-menu__label">API Key</span></a>
                    </li>
                @endif
            @endif
            <li>
                <a class="app-menu__item" href="javascript:void(0)"
                    onclick="event.preventDefault(); var f=document.createElement('form'); f.method='POST'; f.action='{{ route('logout') }}'; f.style.display='none'; var t=document.createElement('input'); t.type='hidden'; t.name='_token'; t.value='{{ csrf_token() }}'; f.appendChild(t); document.body.appendChild(f); f.submit();"><i
                        class="app-menu__icon fa fa-sign-out text-danger"></i>
                    <span class="app-menu__label">Keluar</span></a>
            </li>
            @if (is_main_domain())
                <li class="text-muted" style="padding: 12px 10px; font-size: small; background: #000">
                    <small>Build by: </small><b class="text-white">Leazycms</b><sup
                        class="text-danger">{{ current_cms_version() }}</sup>
                    <a target="_blank" href="https://leazycms.web.id/docs" class="pull-right">
                        <i class="fa fa-book"></i> Docs</a>
                </li>
            @endif
        </ul>
    </aside>
@endauth
