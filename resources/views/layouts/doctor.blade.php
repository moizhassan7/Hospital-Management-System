<!DOCTYPE html>
<html lang="en">

@php
    $doctorUser = Auth::guard('doctor')->user();
    $hospitalName = get_setting('hospital_name', config('app.name', 'Hospital'));
    $hospitalLogo = get_setting('logo');
    $currentRoute = request()->route()?->getName();
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Doctor portal') · {{ $hospitalName }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;600&family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    <a href="#main" class="skip-link">Skip to main content</a>

    <header class="border-b border-line bg-surface">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-3 px-4 py-3">
            <a href="{{ route('doctors.dashboard') }}" class="flex items-center gap-2 min-w-0">
                <span class="sidebar-logo size-9 rounded-lg">
                    @if ($hospitalLogo)
                        <img src="{{ asset('storage/' . $hospitalLogo) }}" alt="">
                    @else
                        <x-ui.icon name="cross" class="size-4" />
                    @endif
                </span>
                <span class="min-w-0">
                    <span class="block truncate font-display text-sm font-extrabold">{{ $hospitalName }}</span>
                    <span class="block text-[11px] text-fg-muted">Doctor portal</span>
                </span>
            </a>

            <nav class="flex flex-1 flex-wrap items-center gap-1" aria-label="Doctor portal">
                <a href="{{ route('doctors.dashboard') }}" @class(['btn btn-sm', 'btn-primary' => $currentRoute === 'doctors.dashboard', 'btn-ghost' => $currentRoute !== 'doctors.dashboard'])>
                    <x-ui.icon name="layout-dashboard" />Dashboard
                </a>
                <a href="{{ route('doctors.write_prescription') }}" @class(['btn btn-sm', 'btn-primary' => $currentRoute === 'doctors.write_prescription', 'btn-ghost' => $currentRoute !== 'doctors.write_prescription'])>
                    <x-ui.icon name="pill" />Write prescription
                </a>
            </nav>

            <div class="flex items-center gap-2">
                <span class="hidden text-right sm:block">
                    <span class="block text-[13px] font-semibold">{{ $doctorUser->name }}</span>
                    <span class="block text-[11px] text-fg-muted">{{ $doctorUser->code }}</span>
                </span>
                <form action="{{ route('doctors.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <x-ui.icon name="log-out" />Sign out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main id="main" class="mx-auto max-w-6xl px-4 py-6" tabindex="-1">
        @include('layouts.partials.flash')
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
