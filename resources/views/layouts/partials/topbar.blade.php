<header class="app-topbar">
    <button type="button" class="btn btn-ghost btn-icon btn-sm" data-sidebar-toggle aria-controls="app-sidebar" aria-label="Toggle navigation">
        <x-ui.icon name="panel-left" class="size-[18px]" />
    </button>
    <span class="topbar-divider" aria-hidden="true"></span>

    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <ol role="list">
            <li><a href="{{ $homeUrl }}" aria-label="Home"><x-ui.icon name="home" class="size-4" /></a></li>
            @hasSection('breadcrumbs')
                @yield('breadcrumbs')
            @else
                @foreach ($breadcrumbs as $crumb)
                    <li>
                        <x-ui.icon name="chevron-right" class="breadcrumb-sep" />
                        @if ($loop->last)
                            <span aria-current="page">{{ $crumb['label'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}" class="hidden sm:inline">{{ $crumb['label'] }}</a>
                        @endif
                    </li>
                @endforeach
            @endif
        </ol>
    </nav>

    <div class="topbar-actions">
        <button type="button" class="topbar-search hidden md:flex" data-palette-open aria-haspopup="dialog">
            <x-ui.icon name="search" />
            <span class="flex-1 text-left">Jump to page…</span>
            <span class="kbd">Ctrl K</span>
        </button>
        <button type="button" class="btn btn-ghost btn-icon btn-sm md:hidden" data-palette-open aria-haspopup="dialog" aria-label="Jump to page">
            <x-ui.icon name="search" />
        </button>

        @if (count($quickActions))
            <div class="menu-anchor">
                <button type="button" class="btn btn-primary btn-sm" data-menu-button aria-haspopup="menu" aria-expanded="false" aria-controls="quick-create-menu">
                    <x-ui.icon name="plus" />
                    <span class="hidden sm:inline">New</span>
                </button>
                <div id="quick-create-menu" class="menu" role="menu" aria-label="Start a new record" hidden>
                    <p class="menu-label">Start a new record</p>
                    @foreach ($quickActions as $action)
                        <a href="{{ route($action['route']) }}" class="menu-item" role="menuitem">
                            <x-ui.icon :name="$action['icon']" />{{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <span class="topbar-divider hidden sm:block" aria-hidden="true"></span>

        <div class="menu-anchor">
            <button type="button" class="user-button" data-menu-button aria-haspopup="menu" aria-expanded="false" aria-controls="user-menu" aria-label="Account menu for {{ $authUser->name }}">
                <span class="avatar" aria-hidden="true">{{ $initials ?: '?' }}</span>
                <span class="hidden lg:block">
                    <span class="user-name">{{ $authUser->name }}</span>
                    <span class="user-role">{{ $roleName ?? 'Staff' }}</span>
                </span>
                <x-ui.icon name="chevron-down" class="hidden size-3.5 text-fg-muted lg:block" />
            </button>
            <div id="user-menu" class="menu" role="menu" aria-label="Account" hidden>
                <div class="menu-header">
                    <p class="text-[13px] font-semibold text-fg">{{ $authUser->name }}</p>
                    @if ($authUser->email)
                        <p class="text-xs text-fg-muted">{{ $authUser->email }}</p>
                    @endif
                </div>
                @if ($authUser->hasRole('Admin'))
                    <a href="{{ route('admin.settings.index') }}" class="menu-item" role="menuitem">
                        <x-ui.icon name="settings" />Hospital settings
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="menu-item menu-item-danger" role="menuitem">
                        <x-ui.icon name="log-out" />Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
