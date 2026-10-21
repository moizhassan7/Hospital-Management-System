@extends('layouts.app')

@section('title', 'Doctors')

@section('content')
    <x-ui.page-header title="Doctors" subtitle="Register doctors and manage their schedules, fees and revenue shares.">
        <x-slot:actions>
            <a href="{{ route('doctors.all') }}" class="btn btn-secondary">
                <x-ui.icon name="list" />All doctors
            </a>
            <a href="{{ route('doctors.create') }}" class="btn btn-primary">
                <x-ui.icon name="user-plus" />Add doctor
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="module-grid">
        <x-ui.module-link :href="route('doctors.create')" icon="user-plus" title="Add new doctor" description="Register a doctor with their department, fees and working days." primary />
        <x-ui.module-link :href="route('doctors.all')" icon="stethoscope" title="All doctors" description="Browse, search and manage every registered doctor." />
    </div>
@endsection
