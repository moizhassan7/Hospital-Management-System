@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('content')
    <div class="hms-bento mb-8">
        <div class="hms-stat-card is-featured">
            <div class="hms-stat-icon bg-blue-50 text-blue-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M12 20.052v-8.3M15 7.052h2.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5H12m-3-10V4.5a1.5 1.5 0 011.5-1.5h3.5a1.5 1.5 0 011.5 1.5V7"/></svg>
            </div>
            <div>
                <p class="hms-stat-label">Patients registered today</p>
                <p class="hms-stat-value text-blue-800">{{ $patientsRegisteredToday }}</p>
                <p class="hms-stat-hint">New registrations today</p>
            </div>
        </div>

        <div class="hms-stat-card">
            <div class="hms-stat-icon bg-red-50 text-red-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="hms-stat-label">Emergency patients</p>
                <p class="hms-stat-value text-red-800">{{ $emergencyPatientsCount }}</p>
                <p class="hms-stat-hint">Patients in emergency ward</p>
            </div>
        </div>

        <div class="hms-stat-card">
            <div class="hms-stat-icon bg-purple-50 text-purple-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <p class="hms-stat-label">Bed occupancy</p>
                <p class="hms-stat-value text-purple-800">{{ $bedOccupancyPercentage }}%</p>
                <p class="hms-stat-hint">Current hospital bed utilization</p>
            </div>
        </div>

        <div class="hms-stat-card">
            <div class="hms-stat-icon bg-green-50 text-green-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="hms-stat-label">Indoor patients</p>
                <p class="hms-stat-value text-green-800">{{ $indoorPatientsCount }}</p>
                <p class="hms-stat-hint">Currently admitted patients</p>
            </div>
        </div>

        <div class="hms-stat-card">
            <div class="hms-stat-icon bg-yellow-50 text-yellow-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="hms-stat-label">Revenue today</p>
                <p class="hms-stat-value text-yellow-800">Rs {{ number_format($revenueToday, 2) }}</p>
                <p class="hms-stat-hint">Total revenue generated today</p>
            </div>
        </div>

        <div class="hms-stat-card">
            <div class="hms-stat-icon bg-indigo-50 text-indigo-600">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="hms-stat-label">Active doctors</p>
                <p class="hms-stat-value text-indigo-800">{{ $activeDoctorsCount }}</p>
                <p class="hms-stat-hint">Doctors currently on duty</p>
            </div>
        </div>
    </div>
@endsection
