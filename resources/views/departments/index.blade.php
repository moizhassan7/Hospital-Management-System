@extends('layouts.app')

@section('title', 'Departments')

@section('content')
    <x-ui.page-header title="Departments" subtitle="Set up the hospital's departments, specialities, diagnoses, floors, rooms, staff categories and emergency charges." />

    <div class="stack">
        <section>
            <h2 class="section-heading">Clinical organisation</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('departments.add')" icon="building" title="Departments" description="Create hospital departments such as Cardiology or Pediatrics." />
                <x-ui.module-link :href="route('specialities.add')" icon="award" title="Specialities" description="Define medical specialities such as General Surgery or Orthopedics." />
                <x-ui.module-link :href="route('diagnoses.index')" icon="clipboard" title="Diagnoses" description="Diagnosis names offered on the discharge form." />
                <x-ui.module-link :href="route('doctor_types.add')" icon="contact" title="Doctor types" description="Doctor categories such as Consultant, Resident or Intern." />
                <x-ui.module-link :href="route('shifts.add')" icon="clock" title="Shifts" description="Working shifts for staff such as Morning, Evening or Night." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Facilities</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('floors.add')" icon="layers" title="Floors" description="Hospital floor levels such as Ground, First or Second floor." />
                <x-ui.module-link :href="route('rooms.add')" icon="door" title="Rooms & wards" description="Rooms and wards with their bed counts and daily rent." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Charges</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('emergency_charges.add')" icon="banknote" title="Emergency charges" description="Set and manage charges for emergency services." />
            </div>
        </section>
    </div>
@endsection
