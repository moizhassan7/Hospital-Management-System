@extends('layouts.app')

@section('page_title', 'Pathology Lab')

@section('content')
    @php
        use App\Support\LabPermissions;
        $can = fn (string $permission) => Auth::user()->isSuperAdmin() || Auth::user()->hasPermission($permission);
        $canTransit = LabPermissions::canAccessSampleTransit(Auth::user());
        $canCcAdmin = Auth::user()->isSuperAdmin() || Auth::user()->isMainLabScope() || $can(LabPermissions::COLLECTION_CENTERS);
        $canCommission = Auth::user()->isSuperAdmin() || Auth::user()->isMainLabScope() || $can(LabPermissions::COMMISSION_ADMIN);
    @endphp

    @include('partials.page-toolbar', [
        'title' => 'Pathology Lab',
        'subtitle' => 'Booking → collect → transit → results',
    ])

    <p class="hms-hub-section-title">Booking &amp; collection</p>
    <div class="hms-hub-list">
        @if($can(LabPermissions::BOOKING))
            <x-hub-action title="Lab Booking" description="Choose a collection center, register the patient, and book pathology tests." :href="route('pathology.bookings.create')" button-text="New Booking" icon-color="text-emerald-600" button-class="bg-emerald-500 hover:bg-emerald-600">
                <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon>
            </x-hub-action>
        @endif

        @if($can(LabPermissions::SAMPLE_COLLECTION))
            <x-hub-action title="Sample Portal" description="Collect samples and print barcode vial labels. Next: add to a transit batch." :href="route('pathology.sample_portal')" button-text="Open portal" icon-color="text-purple-600" button-class="bg-purple-500 hover:bg-purple-600">
                <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg></x-slot:icon>
            </x-hub-action>
        @endif
    </div>



    @if($can(LabPermissions::RESULT_ENTRY) || $can(LabPermissions::FRONT_DESK_PRINT))
        <p class="hms-hub-section-title mt-8">Results &amp; print</p>
        <div class="hms-hub-list">
            @if($can(LabPermissions::RESULT_ENTRY))
                <x-hub-action title="Result Entry" description="Search patient and enter test results." :href="route('pathology.result_entry.search')" button-text="Enter results" icon-color="text-teal-600" button-class="bg-teal-500 hover:bg-teal-600">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot:icon>
                </x-hub-action>
            @endif
            @if($can(LabPermissions::FRONT_DESK_PRINT))
                <x-hub-action title="Front Desk Print" description="Search by phone or MR and print completed reports." :href="route('pathology.front_desk_print')" button-text="Open portal" icon-color="text-indigo-600" button-class="bg-indigo-500 hover:bg-indigo-600">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></x-slot:icon>
                </x-hub-action>
            @endif
        </div>
    @endif

    @if($can(LabPermissions::CRITICAL_REPORT) || $can(LabPermissions::SAMPLES_REPORT) || $can(LabPermissions::FINANCIAL_SUMMARY))
        <p class="hms-hub-section-title mt-8">Reports</p>
        <div class="hms-hub-list">
            @if($can(LabPermissions::CRITICAL_REPORT))
                <x-hub-action title="Critical Report" description="View patients with critical or abnormal test results for the selected date range." :href="route('pathology.critical_report')" button-text="Open report" icon-color="text-red-600" button-class="bg-red-500 hover:bg-red-600">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></x-slot:icon>
                </x-hub-action>
            @endif
            @if($can(LabPermissions::SAMPLES_REPORT))
                <x-hub-action title="Samples Report" description="All samples — collection, receive in lab and results status." :href="route('pathology.lab_samples_report')" button-text="Open report" icon-color="text-cyan-600" button-class="bg-cyan-500 hover:bg-cyan-600">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg></x-slot:icon>
                </x-hub-action>
            @endif
            @if($can(LabPermissions::FINANCIAL_SUMMARY))
                <x-hub-action title="Financial Summary" description="Patient billing, payments due, and revenue breakdown by test." :href="route('pathology.lab_financial_summary')" button-text="Open report" icon-color="text-emerald-600" button-class="bg-emerald-500 hover:bg-emerald-600">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot:icon>
                </x-hub-action>
            @endif
        </div>
    @endif

    @if($canCcAdmin || $canCommission)
        <p class="hms-hub-section-title mt-8">LIMS network</p>
        <div class="hms-hub-list">
            @if($canCcAdmin)
                <x-hub-action title="Collection Centers" description="Manage Main Lab and spoke sites, codes, and lab-number prefixes." :href="route('pathology.collection_centers.index')" button-text="Manage centers" icon-color="text-amber-600" button-class="bg-amber-500 hover:bg-amber-600">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></x-slot:icon>
                </x-hub-action>
            @endif
            @if($canCommission)
                <x-hub-action title="Referring Doctors" description="LIMS referring doctors and ledger balances for commission." :href="route('pathology.lims_doctors.index')" button-text="Manage doctors" icon-color="text-sky-600" button-class="bg-sky-500 hover:bg-sky-600">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></x-slot:icon>
                </x-hub-action>
                <x-hub-action title="Commission Rules" description="Fixed or percent rules by category and optional collection center." :href="route('pathology.commission_rules.index')" button-text="Manage rules" icon-color="text-rose-600" button-class="bg-rose-500 hover:bg-rose-600">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></x-slot:icon>
                </x-hub-action>
            @endif
        </div>
    @endif

    @if(Auth::user()->isSuperAdmin())
        <p class="hms-hub-section-title mt-8">Administration</p>
        <div class="hms-hub-list">
            <x-hub-action title="User Management" description="Create logins and assign lab feature access per user." :href="route('admin.user_manager')" button-text="Manage users" icon-color="text-violet-600" button-class="bg-violet-500 hover:bg-violet-600">
                <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></x-slot:icon>
            </x-hub-action>
            <x-hub-action title="Lab Settings" description="Change lab logo, name, address and manage doctors shown on reports." :href="route('pathology.settings.index')" button-text="Open settings" icon-color="text-slate-600" button-class="bg-slate-600 hover:bg-slate-700">
                <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></x-slot:icon>
            </x-hub-action>
        </div>
    @endif
@endsection
