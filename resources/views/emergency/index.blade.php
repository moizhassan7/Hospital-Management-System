@extends('layouts.app')

@section('title', 'Emergency')

@section('content')
    <x-ui.page-header title="Emergency" subtitle="Register patients arriving in emergency and bill hourly charges and consultant visits.">
        <x-slot:actions>
            <a href="{{ route('emergency.patients.create') }}" class="btn btn-primary">
                <x-ui.icon name="plus" />Register emergency patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="module-grid">
        <x-ui.module-link :href="route('emergency.patients.create')" icon="siren" title="Register patient" description="Register a patient, add consultants and collect payment." primary />
        <x-ui.module-link :href="route('emergency.patients.all')" icon="list" title="Emergency patients" description="Everyone registered in emergency, with bills and receipts." />

        {{-- Add Services is intentionally not linked here.
        <x-ui.module-link :href="route('emergency.add_service')" icon="plus" title="Add services" description="Add and manage emergency services." />
        --}}
    </div>
@endsection
