@extends('layouts.app')

@section('title', 'Radiology')

@section('content')
    <x-ui.page-header title="Radiology" subtitle="Register patients for scans, report findings with images and maintain radiology procedures.">
        <x-slot:actions>
            <a href="{{ route('radiology.result_entry.search') }}" class="btn btn-secondary">
                <x-ui.icon name="file-text" />Enter findings
            </a>
            <a href="{{ route('radiology.patient_registration') }}" class="btn btn-primary">
                <x-ui.icon name="user-plus" />Register patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="stack">
        <section>
            <h2 class="section-heading">Daily work</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('radiology.patient_registration')" icon="user-plus" title="Patient registration" description="Register patients for radiology scans (X-ray, MRI and others)." primary />
                <x-ui.module-link :href="route('radiology.result_entry.search')" icon="file-text" title="Reporting & results" description="Enter findings and impressions, and upload scan images." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Procedure setup</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('radiology.test_catalog')" icon="book-open" title="Radiology catalog" description="View all radiology procedures and templates." />
                <x-ui.module-link :href="route('radiology.manage_test')" icon="scan" title="Manage scans" description="Add, edit or remove radiology scans." />
                <x-ui.module-link :href="route('radiology.test_head')" icon="layers" title="Manage modalities" description="Define and organise radiology modalities." />
                <x-ui.module-link :href="route('radiology.add_test_particulars')" icon="list" title="Report particulars" description="Sections and parameters used on radiology reports." />
            </div>
        </section>
    </div>
@endsection
