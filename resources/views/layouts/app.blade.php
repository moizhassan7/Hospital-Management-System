<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('hospital.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(config('hospital.logo')) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="hms-shell font-sans">
<script>
(function () {
    var collapsed = false;
    try { collapsed = localStorage.getItem('hms-sidebar-collapsed') === '1'; } catch (e) {}
    document.body.dataset.sidebarCollapsed = collapsed ? '1' : '0';
})();
</script>

    <aside class="hms-sidebar" id="hms-sidebar" aria-label="Sidebar">
        <div class="hms-sidebar-brand">
            <a href="{{ route('pathology.index') }}" title="{{ config('hospital.name') }}" class="hms-sidebar-brand-link">
                <span class="hms-sidebar-brand-wide">
                    @include('partials.hospital-brand', ['variant' => 'sidebar-wide'])
                </span>
                <span class="hms-sidebar-brand-rail">
                    @include('partials.hospital-brand', ['variant' => 'rail'])
                </span>
            </a>
            <button type="button" class="hms-sidebar-toggle" id="hms-sidebar-toggle" aria-label="Collapse sidebar" aria-expanded="true" aria-controls="hms-sidebar">
                <svg class="hms-sidebar-toggle-icon hms-sidebar-toggle-icon-collapse" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <svg class="hms-sidebar-toggle-icon hms-sidebar-toggle-icon-expand" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        @include('partials.navigation-rail')

        <div class="hms-sidebar-footer">
            <div class="hms-sidebar-user">
                <div class="hms-sidebar-user-avatar" aria-hidden="true">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div class="hms-sidebar-user-text min-w-0">
                    <p class="hms-sidebar-user-name">{{ Auth::user()->name }}</p>
                    <p class="hms-sidebar-user-role">{{ Auth::user()->isSuperAdmin() ? 'Administrator' : 'Lab staff' }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="hms-sidebar-logout" title="Sign out">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hms-sidebar-logout-text">Sign out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="hms-main">
        <header class="hms-topbar">
            <div class="min-w-0">
                <h1 class="hms-topbar-title">
                    @yield('page_title', 'Pathology Lab')
                </h1>
                <p class="hms-topbar-meta">{{ config('hospital.tagline') }}</p>
            </div>
            <div class="hms-user-chip">
                <span class="text-sm text-gray-600 hidden sm:inline">{{ config('hospital.city') }}</span>
                <div class="hms-avatar" aria-hidden="true">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </header>

        <main class="hms-content">
            <div class="hms-content-inner">
                @if(session('module_denied'))
                    <div class="hms-alert hms-alert-warning mb-4" role="alert">{{ session('module_denied') }}</div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>

</html>
