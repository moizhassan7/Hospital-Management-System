@extends('layouts.app')

@section('page_title', 'Doctors')

@section('content')
    @include('partials.page-toolbar', [
        'title' => 'Doctors Management',
        'subtitle' => 'Medical staff registration and directory',
        'backUrl' => route('dashboard'),
        'backLabel' => 'Back to Dashboard',
    ])

    <div class="hms-hub-list">
        <x-hub-action title="Add New Doctor" description="Register a new medical professional." :href="route('doctors.create')" button-text="Add doctor" icon-color="text-purple-600" button-class="bg-purple-500 hover:bg-purple-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="View All Doctors" description="Browse and manage all registered doctors." :href="route('doctors.all')" button-text="View doctors" icon-color="text-yellow-600" button-class="bg-yellow-500 hover:bg-yellow-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg></x-slot:icon>
        </x-hub-action>
    </div>
@endsection
