@extends('layouts.app')

@section('page_title', 'Reports')

@section('content')
    @include('partials.page-toolbar', [
        'title' => 'Reports Dashboard',
        'subtitle' => 'Hospital summaries and discharge records',
    ])

    <div class="hms-hub-list">
        <x-hub-action title="Indoor Patient Summary" description="View summary of admitted patients." :href="route('reports.indoor_patient_summary')" :linkable="true" icon-color="text-blue-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M12 20.052v-8.3M15 7.052h2.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5H12m-3-10V4.5a1.5 1.5 0 011.5-1.5h3.5a1.5 1.5 0 011.5 1.5V7"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="OPD Patient Summary" description="View outpatient department statistics." :href="route('reports.opd_summary')" :linkable="true" icon-color="text-green-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Discharge History" description="View history of discharged patients." :href="route('reports.indoor_discharge_history')" :linkable="true" icon-color="text-purple-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.75-5.25 4.5-5.25 4.5S6 15.25 6 11s2.25-4 5.25-4c3 0 5.25 1.5 5.25 4s-2.25 4-5.25 4M12 21a9 9 0 100-18 9 9 0 000 18z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="IPD Discharge Payment Report" description="View indoor discharge patient payment records." :href="route('reports.indoor_discharge_payment')" :linkable="true" icon-color="text-yellow-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot:icon>
        </x-hub-action>
    </div>
@endsection
