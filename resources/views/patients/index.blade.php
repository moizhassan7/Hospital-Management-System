@extends('layouts.app')

@section('title', 'Patients')

@section('content')
    <x-ui.page-header title="Patients" subtitle="Register patients and manage their visits, admissions and discharges.">
        <x-slot:actions>
            <a href="{{ route('patients.all') }}" class="btn btn-secondary">
                <x-ui.icon name="list" />All patients
            </a>
            <a href="{{ route('patients.register') }}" class="btn btn-primary">
                <x-ui.icon name="user-plus" />Register patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="stack">
        <section>
            <h2 class="section-heading">Registration</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('patients.register')" icon="user-plus" title="Register new patient" description="A single form to register every new patient." primary />
                <x-ui.module-link :href="route('patients.outdoor_register')" icon="clipboard" title="Today's OPD registration" description="Register walk-in patients for today's clinic." />
                <x-ui.module-link :href="route('patients.indoor_register')" icon="bed" title="Indoor patient registration" description="Admit patients to a ward or private room." />
                <x-ui.module-link :href="route('patients.book_appointment')" icon="calendar-plus" title="Book appointment" description="Schedule a future appointment. Pay now to reserve a token, or save unpaid." />
                <x-ui.module-link :href="route('patients.appointments.index')" icon="clipboard" title="Appointment desk" description="Find booked visits, collect fees and confirm tokens." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Records &amp; discharge</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('patients.all')" icon="users" title="All patients" description="Browse and manage every registered patient record." />
                <x-ui.module-link :href="route('patients.discharge')" icon="log-out" title="Discharge form" description="Complete discharge and settle the final bill." />
                <x-ui.module-link :href="route('patients.admission_form')" icon="file-text" title="Admission form" description="Process patient admissions to the hospital." hidden />
                <x-ui.module-link :href="route('patients.birth_certificates')" icon="award" title="Birth certificates" description="Manage and issue birth certificates." hidden />
                <x-ui.module-link :href="route('patients.death_certificates')" icon="file-check" title="Death certificates" description="Manage and issue death certificates." hidden />
            </div>
        </section>
    </div>
@endsection
