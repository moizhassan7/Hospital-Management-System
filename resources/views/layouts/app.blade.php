<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('hospital.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(config('hospital.logo')) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')
</head>

<body class="hms-shell font-sans">

    <aside class="hms-rail" aria-label="Sidebar">
        <div class="hms-rail-brand">
            <a href="{{ route('pathology.index') }}" title="{{ config('hospital.name') }}">
                @include('partials.hospital-brand', ['variant' => 'rail'])
            </a>
        </div>

        @include('partials.navigation-rail')

        <div class="hms-rail-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="hms-rail-link w-full" title="Logout">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hms-rail-label">Exit</span>
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
                <p class="hms-topbar-meta">{{ config('hospital.name') }}</p>
            </div>
            <div class="hms-user-chip">
                <span class="text-sm text-gray-600 hidden sm:inline">Welcome, {{ Auth::user()->name }}</span>
                <div class="hms-avatar" aria-hidden="true">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </header>

        <main class="hms-content">
            <div class="hms-content-inner">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>

</html>
