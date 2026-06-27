@php
    use App\Support\LabPermissions;

    $currentRoute = Route::currentRouteName();
    $isActive = fn (...$routes) => in_array($currentRoute, $routes, true) ? 'is-active' : '';
    $can = fn (string $permission) => Auth::user()->isSuperAdmin() || Auth::user()->hasPermission($permission);
    $hasAnyLab = Auth::user()->isSuperAdmin() || Auth::user()->hasAnyPermission(LabPermissions::all());
@endphp

<nav class="hms-rail-nav" aria-label="Main navigation">
    @if($hasAnyLab)
        <a href="{{ route('pathology.index') }}" class="hms-rail-link {{ $isActive('pathology.index') }}" title="Pathology Lab">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.022.547l-2.387 2.387a2 2 0 001.414 3.414h15.828a2 2 0 001.414-3.414l-2.387-2.387zM15 11V5a2 2 0 00-2-2H11a2 2 0 00-2 2v6m6 0a2 2 0 012 2v2M9 11a2 2 0 00-2 2v2m4 6h2"/></svg>
            <span class="hms-rail-label">Lab</span>
        </a>
    @endif

    @if($can(LabPermissions::SAMPLE_COLLECTION))
        <a href="{{ route('pathology.sample_portal') }}" class="hms-rail-link {{ $isActive('pathology.sample_portal') }}" title="Sample Collection">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
            <span class="hms-rail-label">Sample</span>
        </a>
    @endif

    @if($can(LabPermissions::LAB_ATTENDANT))
        <a href="{{ route('pathology.lab_attendant') }}" class="hms-rail-link {{ $isActive('pathology.lab_attendant') }}" title="Lab Attendant">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            <span class="hms-rail-label">Attend</span>
        </a>
    @endif

    @if($can(LabPermissions::RESULT_ENTRY))
        <a href="{{ route('pathology.result_entry.search') }}" class="hms-rail-link {{ $isActive('pathology.result_entry.search', 'pathology.result_entry.show_form', 'pathology.result_entry.view') }}" title="Result Entry">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="hms-rail-label">Results</span>
        </a>
    @endif

    @if($can(LabPermissions::FRONT_DESK_PRINT))
        <a href="{{ route('pathology.front_desk_print') }}" class="hms-rail-link {{ $isActive('pathology.front_desk_print') }}" title="Front Desk Print">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span class="hms-rail-label">Print</span>
        </a>
    @endif

    @if($can(LabPermissions::CRITICAL_REPORT))
        <a href="{{ route('pathology.critical_report') }}" class="hms-rail-link {{ $isActive('pathology.critical_report') }}" title="Critical Report">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span class="hms-rail-label">Critical</span>
        </a>
    @endif

    @if($can(LabPermissions::SAMPLES_REPORT))
        <a href="{{ route('pathology.lab_samples_report') }}" class="hms-rail-link {{ $isActive('pathology.lab_samples_report') }}" title="Samples Report">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <span class="hms-rail-label">Samples</span>
        </a>
    @endif

    @if(Auth::user()->isSuperAdmin())
        <a href="{{ route('admin.user_manager') }}" class="hms-rail-link {{ $isActive('admin.user_manager', 'admin.user_manager.edit') }}" title="User Management">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span class="hms-rail-label">Users</span>
        </a>
        <a href="{{ route('pathology.settings.index') }}" class="hms-rail-link {{ $isActive('pathology.settings.index') }}" title="Lab Settings">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="hms-rail-label">Setup</span>
        </a>
    @endif
</nav>
