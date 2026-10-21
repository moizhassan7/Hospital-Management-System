@extends('layouts.app')

@section('title', 'Laboratory')

@section('content')
    <x-ui.page-header title="Laboratory" subtitle="Register patients for tests, enter results and maintain tests and their parameters.">
        <x-slot:actions>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Dashboard
            </a>
            <a href="{{ route('laboratory.patient_registration') }}" class="btn btn-primary">
                <x-ui.icon name="user-plus" />Register patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="stack">
        <section>
            <h2 class="section-heading">Daily work</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('laboratory.patient_registration')" icon="user-plus" title="Patient registration" description="Register patients for laboratory tests." primary />
                <x-ui.module-link :href="route('laboratory.result_entry.search')" icon="test-tube" title="Result entry" description="Search a patient and enter test findings and results." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Test setup</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('test_head')" icon="layers" title="Manage test heads" description="Define and organise categories for lab tests." />
                <x-ui.module-link :href="route('laboratory.manage_test')" icon="flask" title="Manage tests" description="Add, edit or remove individual lab tests." />
                <x-ui.module-link :href="route('laboratory.add_test_particulars')" icon="list" title="Add test particulars" description="Define the parameters and normal ranges for each test." />
                <x-ui.module-link :href="route('laboratory.test_particular_details')" icon="clipboard" title="Particular details" description="Full list of every parameter defined on the catalogue." />
                <x-ui.module-link :href="route('laboratory.test_catalog')" icon="book-open" title="Test catalog" description="Every test with its parameters in one list." />
            </div>
        </section>
    </div>
@endsection
