@extends('layouts.app')

@section('page_title', 'Emergency')

@section('content')
    @include('partials.page-toolbar', [
        'title' => 'Emergency Management',
        'subtitle' => 'Emergency ward patient intake',
        'backUrl' => route('dashboard'),
        'backLabel' => 'Back to Dashboard',
    ])

    <div class="hms-hub-list">
        <x-hub-action title="Emergency Patients" description="Register and manage emergency ward patients." :href="route('emergency.patients.create')" button-text="Add patients" icon-color="text-red-600" button-class="bg-red-500 hover:bg-red-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></x-slot:icon>
        </x-hub-action>
    </div>
@endsection
