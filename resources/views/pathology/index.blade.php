@extends('layouts.app')

@section('title', 'Pathology lab')

@section('content')
    <x-ui.page-header title="Pathology lab" subtitle="Register patients for tests, enter results and maintain the pathology test catalogue.">
        <x-slot:actions>
            <a href="{{ route('pathology.result_entry.search') }}" class="btn btn-secondary">
                <x-ui.icon name="test-tube" />Enter results
            </a>
            <a href="{{ route('pathology.patient_registration') }}" class="btn btn-primary">
                <x-ui.icon name="user-plus" />Register patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="stack">
        <section>
            <h2 class="section-heading">Daily work</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('pathology.patient_registration')" icon="user-plus" title="Patient registration" description="Register patients for pathology tests." primary />
                <x-ui.module-link :href="route('pathology.result_entry.search')" icon="test-tube" title="Result entry" description="Search a patient and enter pathology test results." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Test setup</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('pathology.test_catalog')" icon="book-open" title="Test catalog" description="View all pathology tests and their parameters." />
                <x-ui.module-link :href="route('pathology.manage_test')" icon="flask" title="Manage tests" description="Add, edit or remove individual pathology tests." />
                <x-ui.module-link :href="route('pathology.test_head')" icon="layers" title="Manage test heads" description="Define and organise pathology test categories." />
                <x-ui.module-link :href="route('pathology.add_test_particulars')" icon="list" title="Test particulars" description="Parameters, units and normal ranges for each test." />
            </div>
        </section>
    </div>
@endsection
