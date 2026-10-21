@extends('layouts.app')

@section('title', 'All patients')

@section('content')
    <x-ui.page-header title="All patients" subtitle="Every registered patient record, newest registrations included.">
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
            <a href="{{ route('patients.register') }}" class="btn btn-primary">
                <x-ui.icon name="user-plus" />Register patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel flush>
        <div class="table-toolbar">
            <x-ui.search-input name="" class="toolbar-search" placeholder="Filter by name, MR no. or mobile" data-table-filter="#patients-table" />
            <span class="toolbar-spacer"></span>
            <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#patients-table">{{ $patients->count() }}</span> patients</span>
        </div>

        <div class="table-wrap">
            <table id="patients-table" class="table">
                <thead>
                    <tr>
                        <th class="num w-12">#</th>
                        <th>MR no.</th>
                        <th>Patient name</th>
                        <th>Type</th>
                        <th>Registered</th>
                        <th>Gender</th>
                        <th class="num">Age</th>
                        <th>Mobile no.</th>
                        <th class="actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $index => $patient)
                        <tr>
                            <td class="num muted">{{ $index + 1 }}</td>
                            <td><span class="id">{{ $patient->mr_number }}</span></td>
                            <td class="cell-strong"><a href="{{ route('patients.show', $patient) }}" class="hover:text-brand-700 hover:underline">{{ $patient->name }}</a></td>
                            <td>
                                <x-ui.badge :tone="$patient->is_welfare ? 'info' : 'neutral'">{{ $patient->is_welfare ? 'Welfare' : 'Normal' }}</x-ui.badge>
                            </td>
                            <td class="nowrap">{{ $patient->registration_date ? $patient->registration_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $patient->gender }}</td>
                            <td class="num">{{ $patient->age }}</td>
                            <td class="nowrap">{{ $patient->mobile_number ?? 'N/A' }}</td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="eye" />View</a>
                                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                    <form action="{{ route('patients.destroy', $patient->id) }}" method="POST" class="inline"
                                        data-confirm="This permanently removes {{ $patient->name }} ({{ $patient->mr_number }}) from the patient register."
                                        data-confirm-title="Delete this patient?" data-confirm-label="Delete patient">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-ghost btn-sm">
                                            <x-ui.icon name="trash" />Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="9" class="p-0">
                                <x-ui.empty-state icon="users" title="No registered patients yet" description="Patients you register will appear here.">
                                    <x-slot:actions>
                                        <a href="{{ route('patients.register') }}" class="btn btn-primary btn-sm"><x-ui.icon name="user-plus" />Register patient</a>
                                    </x-slot:actions>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>
@endsection
