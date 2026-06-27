@extends('layouts.app')

@section('page_title', 'Critical Test Report')

@section('content')
    @php
        $highCount = $records->where('flag', 'HIGH')->count();
        $lowCount = $records->where('flag', 'LOW')->count();
        $exportParams = ['date_from' => $dateFromInput, 'date_to' => $dateToInput];
    @endphp

    <div class="hms-cr-page">

        {{-- Toolbar --}}
        <div class="hms-cr-toolbar">
            <div class="hms-cr-toolbar-left">
                <a href="{{ route('pathology.index') }}" class="hms-back-btn !py-2" title="Back to Pathology Lab">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="hms-cr-toolbar-title">Critical test report</h1>
                    <p class="hms-cr-toolbar-sub">Out-of-range pathology results for clinical review</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <x-btn variant="ghost" size="sm" :href="route('pathology.critical_report.print', $exportParams)" target="_blank">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </x-btn>
                <x-btn variant="indigo" size="sm" :href="route('pathology.critical_report.pdf', $exportParams)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </x-btn>
            </div>
        </div>

        {{-- Alert summary strip --}}
        <div class="hms-cr-alert-strip">
            <div class="hms-cr-alert-accent">
                <p class="hms-cr-alert-eyebrow">Abnormal results</p>
                <h2 class="hms-cr-alert-title">
                    @if($critical_count > 0)
                        {{ $critical_count }} {{ $critical_count === 1 ? 'flag' : 'flags' }} need attention
                    @else
                        All clear for this period
                    @endif
                </h2>
                <p class="hms-cr-alert-period">
                    {{ $date_from->format('d M Y') }} — {{ $date_to->format('d M Y') }}
                </p>
            </div>
            <div class="hms-cr-stats">
                <div class="hms-cr-stat">
                    <span class="hms-cr-stat-label">Patients affected</span>
                    <span class="hms-cr-stat-value is-patients">{{ $patient_count }}</span>
                </div>
                <div class="hms-cr-stat">
                    <span class="hms-cr-stat-label">Critical records</span>
                    <span class="hms-cr-stat-value is-records">{{ $critical_count }}</span>
                </div>
                <div class="hms-cr-stat">
                    <span class="hms-cr-stat-label">High ▲</span>
                    <span class="hms-cr-stat-value is-high">{{ $highCount }}</span>
                </div>
                <div class="hms-cr-stat">
                    <span class="hms-cr-stat-label">Low ▼</span>
                    <span class="hms-cr-stat-value is-low">{{ $lowCount }}</span>
                </div>
            </div>
        </div>

        {{-- Date range filter --}}
        <div class="hms-cr-filter">
            <div class="hms-cr-filter-header">
                <div>
                    <p class="hms-cr-filter-title">Report period</p>
                    <p class="hms-cr-filter-hint">Results outside normal reference range (High ▲ / Low ▼)</p>
                </div>
            </div>
            <div class="hms-cr-filter-body">
                <form action="{{ route('pathology.critical_report') }}" method="GET" class="hms-cr-filter-grid">
                    <x-form.field label="From date" for="date_from" required>
                        <input type="date" id="date_from" name="date_from" required
                            class="hms-input hms-cr-input-focus" value="{{ $dateFromInput }}">
                    </x-form.field>
                    <x-form.field label="To date" for="date_to" required>
                        <input type="date" id="date_to" name="date_to" required
                            class="hms-input hms-cr-input-focus" value="{{ $dateToInput }}">
                    </x-form.field>
                    <div class="hms-cr-filter-actions">
                        <x-btn type="submit" variant="danger">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Generate report
                        </x-btn>
                        <x-btn variant="ghost" :href="route('pathology.critical_report')">Reset to this month</x-btn>
                    </div>
                </form>
            </div>
        </div>

        {{-- Results table --}}
        <div class="hms-cr-card">
            <div class="hms-cr-card-header">
                <div>
                    <h3 class="hms-cr-card-title">Critical test records</h3>
                    <p class="hms-cr-card-sub">Sorted by most recent result first</p>
                </div>
                @if($records->isNotEmpty())
                    <span class="hms-cr-card-count">{{ $records->count() }}</span>
                @endif
            </div>

            @if($records->isEmpty())
                <div class="hms-cr-empty">
                    <div class="hms-cr-empty-icon">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="hms-cr-empty-title">No critical results in this date range</p>
                    <p class="hms-cr-empty-desc">Try widening the period above, or check back after more results are entered.</p>
                </div>
            @else
                <div class="hms-table-wrap">
                    <table class="hms-cr-table">
                        <thead>
                            <tr>
                                <th class="w-2 p-0" scope="col"><span class="sr-only">Severity</span></th>
                                <th scope="col">#</th>
                                <th scope="col">Date / time</th>
                                <th scope="col">Patient</th>
                                <th scope="col">Age / sex</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Test</th>
                                <th scope="col">Result</th>
                                <th scope="col">Reference</th>
                                <th scope="col" class="text-center">Flag</th>
                                <th scope="col">Report</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $index => $row)
                                @php $isHigh = $row['flag'] === 'HIGH'; @endphp
                                <tr class="{{ $isHigh ? 'is-high' : 'is-low' }}">
                                    <td class="!px-1 !py-2 w-2">
                                        <div class="hms-cr-severity {{ $isHigh ? 'is-high' : 'is-low' }}" aria-hidden="true"></div>
                                    </td>
                                    <td class="text-gray-400 text-xs tabular-nums">{{ $index + 1 }}</td>
                                    <td class="whitespace-nowrap text-xs">{{ $row['result_date']->format('d M Y') }}<br><span class="text-gray-400">{{ $row['result_date']->format('h:i A') }}</span></td>
                                    <td>
                                        <p class="hms-cr-patient">{{ $row['patient_name'] }}</p>
                                        <p class="hms-cr-lab-reg">Lab {{ $row['lab_registration_no'] ?? '—' }}</p>
                                    </td>
                                    <td class="text-sm whitespace-nowrap">{{ $row['age'] }} / {{ $row['gender'] }}</td>
                                    <td class="text-sm">
                                        @if($row['contact_no'])
                                            <a href="tel:{{ $row['contact_no'] }}" class="text-gray-700 hover:text-indigo-600">{{ $row['contact_no'] }}</a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <p class="hms-cr-test">{{ $row['test_name'] }}</p>
                                        <p class="hms-cr-param">{{ $row['parameter'] }}</p>
                                    </td>
                                    <td>
                                        <span class="hms-cr-result">
                                            {{ $row['result_value'] }}<span class="hms-cr-result-unit">{{ $row['unit'] }}</span>
                                        </span>
                                    </td>
                                    <td class="text-xs text-gray-500 whitespace-nowrap">{{ $row['reference_range'] }}</td>
                                    <td class="text-center">
                                        @if($isHigh)
                                            <span class="hms-cr-flag is-high" title="Above reference range">▲ High</span>
                                        @else
                                            <span class="hms-cr-flag is-low" title="Below reference range">▼ Low</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('pathology.result_entry.view', ['lab_patient_id' => $row['lab_patient_id'], 'test_id' => $row['test_id']]) }}"
                                            class="hms-cr-view-link">
                                            View
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
