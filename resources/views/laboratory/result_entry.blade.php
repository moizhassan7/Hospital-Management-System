@extends('layouts.app')

@section('title', ($category ?? 'Laboratory') . ' result entry')

@section('content')
    @php
        $hubRoute = $category == 'Pathology' ? route('pathology.index') : ($category == 'Radiology' ? route('radiology.index') : route('laboratory.index'));
        $searchRoute = $category == 'Pathology' ? route('pathology.result_entry.search') : ($category == 'Radiology' ? route('radiology.result_entry.search') : route('laboratory.result_entry.search'));
    @endphp

    <x-ui.page-header :title="($category ?? 'Laboratory') . ' result entry'" subtitle="Find a patient by MR number to enter pending results or reopen earlier reports.">
        <x-slot:actions>
            <a href="{{ $hubRoute }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />{{ $category ?? 'Laboratory' }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel class="mb-5" flush>
        <form action="{{ $searchRoute }}" method="GET" class="flex flex-wrap items-end gap-3 p-4">
            <x-ui.field label="MR number" for="result_mr_no" class="w-full sm:w-80">
                <div class="input-group has-start">
                    <span class="input-affix is-start"><x-ui.icon name="hash" /></span>
                    <input type="text" id="result_mr_no" name="mr_no" placeholder="e.g. MRN001" class="input input-mono" value="{{ request('mr_no') }}" autofocus autocomplete="off">
                </div>
            </x-ui.field>
            <button type="submit" class="btn btn-primary">
                <x-ui.icon name="search" />Find patient
            </button>
        </form>
    </x-ui.panel>

    @if (isset($patientRecord))
        <x-ui.patient-band class="mb-5" :mr="$patientRecord->mr_no" :name="$patientRecord->patient_name" :age="$patientRecord->age" :gender="$patientRecord->gender">
            <div class="band-cell">
                <span class="band-label">Pending</span>
                <span class="band-value">{{ $pendingTests->count() }} {{ \Illuminate\Support\Str::plural('test', $pendingTests->count()) }}</span>
            </div>
        </x-ui.patient-band>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
            <x-ui.panel title="Pending tests" description="Tests marked to carry out that have no results yet." icon="clock" flush>
                @if ($pendingTests->isEmpty())
                    <x-ui.empty-state icon="check-circle" title="Nothing pending" description="All tests for this patient have results." />
                @else
                    <ul role="list" class="divide-y divide-line-soft">
                        @foreach ($pendingTests as $pendingTest)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="flex min-w-0 items-center gap-3">
                                    <x-ui.badge tone="warning" dot>Pending</x-ui.badge>
                                    <span class="truncate font-semibold">{{ $pendingTest['name'] }}</span>
                                </div>
                                <a href="{{ route('laboratory.result_entry.show_form', ['lab_patient_id' => $patientRecord->id, 'test_id' => $pendingTest['id']]) }}" class="btn btn-primary btn-sm">
                                    <x-ui.icon name="pencil" />Enter results
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.panel>

            <x-ui.panel title="Report history" description="Completed tests across all registrations for this MR number." icon="file-text" flush>
                @if ($testHistory->isEmpty())
                    <x-ui.empty-state icon="file-text" title="No previous results" description="Saved reports for this patient will be listed here." />
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Test</th>
                                    <th>Result date</th>
                                    <th>Status</th>
                                    <th class="actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($testHistory as $key => $results)
                                    @php
                                        $firstResult = $results->first();
                                        $test = $firstResult->test;
                                        $date = $firstResult->created_at->format('d-M-Y');
                                        $lab_patient_id = $firstResult->laboratory_patient_id;
                                    @endphp
                                    <tr>
                                        <td class="cell-strong">{{ $test->name }}</td>
                                        <td class="nowrap">{{ $date }}</td>
                                        <td><x-ui.badge tone="success" dot>Completed</x-ui.badge></td>
                                        <td class="actions">
                                            <div class="row-actions">
                                                <a href="{{ route('laboratory.result_entry.view', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}" class="btn btn-ghost btn-sm">
                                                    <x-ui.icon name="eye" />View report
                                                </a>
                                                <a href="{{ route('laboratory.print_report', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}" target="_blank" class="btn btn-secondary btn-sm">
                                                    <x-ui.icon name="printer" />Print
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-ui.panel>
        </div>
    @elseif (request()->filled('mr_no'))
        <x-ui.panel>
            <x-ui.empty-state icon="search" title="No lab registration for MR {{ request('mr_no') }}" description="Check the number, or register the patient for tests first." />
        </x-ui.panel>
    @else
        <x-ui.panel>
            <x-ui.empty-state icon="test-tube" title="Look up a patient to begin" description="Enter the MR number printed on the lab registration slip." />
        </x-ui.panel>
    @endif
@endsection
