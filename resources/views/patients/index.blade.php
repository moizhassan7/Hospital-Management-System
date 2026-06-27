@extends('layouts.app')

@section('page_title', 'Patients')

@section('content')
    @include('partials.page-toolbar', [
        'title' => 'Patient Management',
        'subtitle' => 'Registration, admission, and discharge',
        'backUrl' => route('dashboard'),
        'backLabel' => 'Back to Dashboard',
    ])

    <div class="hms-hub-list">
        <x-hub-action title="Register New Patient" description="A single form to register all new patients." :href="route('patients.register')" button-text="Register patient" icon-color="text-blue-600" button-class="bg-blue-500 hover:bg-blue-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M12 20.052v-8.3M15 7.052h2.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5H12m-3-10V4.5a1.5 1.5 0 011.5-1.5h3.5a1.5 1.5 0 011.5 1.5V7"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Indoor Patient Registration" description="Register patients for inpatient admission." :href="route('patients.indoor_register')" button-text="Register indoor patient" icon-color="text-pink-600" button-class="bg-pink-500 hover:bg-pink-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2.5a1.5 1.5 0 001.5-1.5v-5a1.5 1.5 0 00-1.5-1.5H19m-14 0H2.5A1.5 1.5 0 001 14.5v5A1.5 1.5 0 002.5 21H5m14 0v-5a2 2 0 00-2-2h-2a2 2 0 00-2 2v5m-4 0v-5a2 2 0 00-2-2H7a2 2 0 00-2 2v5M6 10h.01M18 10h.01"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Today's OPD Registration" description="Register walk-in patients for today's clinic." :href="route('patients.outdoor_register')" button-text="Register OPD patient" icon-color="text-green-600" button-class="bg-green-500 hover:bg-green-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Book Appointment" description="Schedule a future appointment for a patient." :href="route('patients.book_appointment')" button-text="Book appointment" icon-color="text-yellow-600" button-class="bg-yellow-500 hover:bg-yellow-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h.01M12 7h.01M16 7h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3 13h18M12 3v18"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="View All Patients" description="Browse and manage all registered patient records." :href="route('patients.all')" button-text="View all patients" icon-color="text-purple-600" button-class="bg-purple-500 hover:bg-purple-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Discharge Form" description="Complete patient discharge procedures." :href="route('patients.discharge')" button-text="Fill discharge form" icon-color="text-red-600" button-class="bg-red-500 hover:bg-red-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></x-slot:icon>
        </x-hub-action>
    </div>
@endsection
