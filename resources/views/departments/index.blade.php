@extends('layouts.app')

@section('page_title', 'Departments')

@section('content')
    @include('partials.page-toolbar', [
        'title' => 'Departments Management',
        'subtitle' => 'Hospital structure and configuration',
    ])

    <div class="hms-hub-list">
        <x-hub-action title="Add Department" description="Create new hospital departments (e.g., Cardiology, Pediatrics)." :href="route('departments.add')" button-text="Add department" icon-color="text-blue-600" button-class="bg-blue-500 hover:bg-blue-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Add Speciality" description="Define medical specialities (e.g., General Surgery, Orthopedics)." :href="route('specialities.add')" button-text="Add speciality" icon-color="text-green-600" button-class="bg-green-500 hover:bg-green-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Add Floors" description="Manage hospital floor levels (e.g., Ground, First, Second Floor)." :href="route('floors.add')" button-text="Add floor" icon-color="text-purple-600" button-class="bg-purple-500 hover:bg-purple-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 10h.01M15 10h.01M9 14h.01M15 14h.01M9 18h.01M15 18h.01"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Add Rooms" description="Assign and categorize hospital rooms (e.g., Ward, Private, ICU)." :href="route('rooms.add')" button-text="Add room" icon-color="text-yellow-600" button-class="bg-yellow-500 hover:bg-yellow-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zM4 21a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Add Doctor Type" description="Define doctor categories (e.g., Consultant, Resident, Intern)." :href="route('doctor_types.add')" button-text="Add doctor type" icon-color="text-red-600" button-class="bg-red-500 hover:bg-red-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 10a6 6 0 11-12 0 6 6 0 0112 0zm-6 9v-3m0 0V9m0 3h-3m3 0h3"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Emergency Charges" description="Set and manage charges for emergency services." :href="route('emergency_charges.add')" button-text="Manage charges" icon-color="text-orange-600" button-class="bg-orange-500 hover:bg-orange-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot:icon>
        </x-hub-action>
    </div>
@endsection
