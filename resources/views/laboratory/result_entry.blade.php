@extends('layouts.app')

@section('page_title', 'Result Entry')

@section('content')
<div class="hms-re-page">

    {{-- Toolbar: title + inline search --}}
    <div class="hms-re-toolbar">
        <div class="hms-re-toolbar-left">
            <a href="{{ route('pathology.index') }}" class="hms-back-btn !py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="hms-re-toolbar-title">Result entry</h1>
                <p class="hms-re-toolbar-sub">Search by lab registration number</p>
            </div>
        </div>

        <form action="{{ route('pathology.result_entry.search') }}" method="GET" class="hms-re-search">
            <input type="text" name="lab_reg_no" placeholder="Lab registration no." class="hms-input"
                value="{{ $labRegNo ?? request('lab_reg_no') }}" required autofocus>
            <button type="submit" class="hms-btn hms-btn-teal shrink-0">Search</button>
        </form>
    </div>

    @include('partials.flash-alerts')

    @if(!empty($desktopSynced))
        <div class="hms-alert hms-alert-info">Patient and tests imported from Desktop booking.</div>
    @endif

    {{-- No search yet: hero state --}}
    @if(empty($labRegNo))
        <div class="hms-re-hero">
            <div class="hms-re-hero-icon">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="hms-re-hero-title">Enter lab registration number</h2>
            <p class="hms-re-hero-desc">Type the registration number from Desktop booking or the pathology slip, then search to enter or view results.</p>
        </div>

    {{-- Searched but not found --}}
    @elseif(empty($patientRecord))
        <div class="hms-alert hms-alert-warning">
            No patient found for Lab Reg No: <strong>{{ $labRegNo }}</strong>
            @if(!empty($desktopError))
                <p class="mt-2 text-sm"><strong>Desktop DB:</strong> {{ $desktopError }}</p>
            @elseif(empty($desktopSynced))
                <p class="mt-2 text-sm">Desktop booking was also checked — no matching record or catalog tests found.</p>
            @endif
        </div>

    {{-- Patient loaded --}}
    @else
        @php
            $initials = collect(explode(' ', $patientRecord->patient_name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
            $pendingCount = $pendingTests->count();
            $historyCount = $testHistory->count();
            $canEditResults = Auth::user()->isSuperAdmin() || Auth::user()->hasPermission(\App\Support\LabPermissions::RESULT_EDIT);
        @endphp

        {{-- Patient strip --}}
        <div class="hms-re-patient-strip">
            <div class="hms-re-patient-accent">
                <div class="hms-re-patient-avatar">{{ $initials }}</div>
                <div>
                    <p class="hms-re-patient-name">{{ $patientRecord->patient_name }}</p>
                    <p class="hms-re-patient-reg">Reg #{{ $patientRecord->lab_registration_no ?? $labRegNo }}</p>
                </div>
            </div>
            <div class="hms-re-patient-meta">
                <div class="hms-re-meta-item">
                    <span class="hms-re-meta-label">MR number</span>
                    <span class="hms-re-meta-value">{{ $patientRecord->mr_no ?? '—' }}</span>
                </div>
                <div class="hms-re-meta-item">
                    <span class="hms-re-meta-label">Age / sex</span>
                    <span class="hms-re-meta-value">{{ $patientRecord->age }} / {{ $patientRecord->gender }}</span>
                </div>
                <div class="hms-re-meta-item">
                    <span class="hms-re-meta-label">Priority</span>
                    <span class="hms-re-meta-value">{{ $patientRecord->priority ?? 'Routine' }}</span>
                </div>
                <div class="hms-re-meta-item">
                    <span class="hms-re-meta-label">Registered</span>
                    <span class="hms-re-meta-value">{{ $patientRecord->created_at->format('d M Y') }}</span>
                </div>
            </div>
            @if($historyCount > 0)
                <div class="bg-white px-5 py-3 border-t sm:border-t-0 sm:border-l border-teal-200 flex items-center shrink-0">
                    <button type="button"
                        data-hms-print="{{ route('pathology.print_all_reports', ['lab_patient_id' => $patientRecord->id]) }}"
                        class="hms-btn hms-btn-primary whitespace-nowrap">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4"/></svg>
                        Print All ({{ $historyCount }})
                    </button>
                </div>
            @endif
        </div>

        <div class="hms-re-grid">
            {{-- Pending tests (primary action column) --}}
            <div class="hms-re-main">
                <div class="hms-re-card">
                    <div class="hms-re-card-header">
                        <h2 class="hms-re-card-title">
                            <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pending tests
                            @if($pendingCount > 0)<span class="hms-re-card-count">{{ $pendingCount }}</span>@endif
                        </h2>
                    </div>
                    <div class="hms-re-card-body">
                        @if($pendingTests->isEmpty())
                            <div class="hms-re-empty">
                                <div class="hms-re-empty-icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">All tests completed</p>
                                <p class="text-xs text-gray-400 mt-1">No pending results for this registration.</p>
                            </div>
                        @else
                            @foreach($pendingTests as $pendingTest)
                                @php $sampleStatus = $pendingTest['sample_status'] ?? \App\Models\LabSampleVial::STATUS_NOT_COLLECTED; @endphp
                                <div class="hms-re-test-row">
                                    <div>
                                        <p class="hms-re-test-name">{{ $pendingTest['name'] }}</p>
                                        <div class="hms-re-test-meta">
                                            <span class="hms-re-status {{ \App\Models\LabSampleVial::statusReBadgeClass($sampleStatus) }}">
                                                <span class="hms-re-status-dot"></span>
                                                Sample: {{ \App\Models\LabSampleVial::statusLabel($sampleStatus) }}
                                            </span>
                                            <span class="hms-re-status hms-re-status--result_pending">
                                                <span class="hms-re-status-dot"></span>
                                                Result: Pending
                                            </span>
                                        </div>
                                    </div>
                                    <a href="{{ route('pathology.result_entry.show_form', ['lab_patient_id' => $patientRecord->id, 'test_id' => $pendingTest['id']]) }}"
                                        class="hms-btn hms-btn-success hms-btn-sm shrink-0">
                                        Enter results
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            {{-- Test history (sidebar) --}}
            <div class="hms-re-side">
                <div class="hms-re-card">
                    <div class="hms-re-card-header">
                        <h2 class="hms-re-card-title">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Completed reports
                            @if($historyCount > 0)<span class="hms-re-card-count !bg-blue-100 !text-blue-800">{{ $historyCount }}</span>@endif
                        </h2>
                        @if($historyCount > 0)
                            <button type="button"
                                data-hms-print="{{ route('pathology.print_all_reports', ['lab_patient_id' => $patientRecord->id]) }}"
                                class="hms-btn hms-btn-primary hms-btn-sm shrink-0">
                                Print All
                            </button>
                        @endif
                    </div>
                    <div class="hms-re-card-body">
                        @if($testHistory->isEmpty())
                            <div class="hms-re-empty">
                                <div class="hms-re-empty-icon">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">No reports yet</p>
                                <p class="text-xs text-gray-400 mt-1">Completed results will appear here.</p>
                            </div>
                        @else
                            @foreach($testHistory as $results)
                                @php
                                    $firstResult = $results->first();
                                    $test = $firstResult->test;
                                    $lab_patient_id = $firstResult->laboratory_patient_id;
                                    $testMeta = collect($patientRecord->getSelectedTestsArray())->firstWhere('id', $test->id);
                                    $sampleStatus = $testMeta['sample_status'] ?? \App\Models\LabSampleVial::STATUS_COMPLETED;
                                @endphp
                                <div class="hms-re-history-item">
                                    <div class="hms-re-history-top">
                                        <div>
                                            <p class="hms-re-history-name">{{ $test->name }}</p>
                                            <p class="hms-re-history-date">{{ $firstResult->created_at->format('d M Y, h:i A') }}</p>
                                            <div class="hms-re-test-meta mt-2">
                                                <span class="hms-re-status hms-re-status--result_ready">
                                                    <span class="hms-re-status-dot"></span>
                                                    Result: Entered
                                                </span>
                                                <span class="hms-re-status {{ \App\Models\LabSampleVial::statusReBadgeClass($sampleStatus) }}">
                                                    <span class="hms-re-status-dot"></span>
                                                    Sample: {{ \App\Models\LabSampleVial::statusLabel($sampleStatus) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="hms-re-history-actions">
                                        <a href="{{ route('pathology.result_entry.view', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}"
                                            class="hms-btn hms-btn-indigo hms-btn-sm">View</a>
                                        @if($canEditResults)
                                            <a href="{{ route('pathology.result_entry.edit', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}"
                                                class="hms-btn hms-btn-warning hms-btn-sm">Edit</a>
                                        @endif
                                        <a href="{{ route('pathology.print_report', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}"
                                            target="_blank" class="hms-btn hms-btn-primary hms-btn-sm">Print</a>
                                        <a href="{{ route('pathology.print_report.pdf', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}"
                                            class="hms-btn hms-btn-ghost hms-btn-sm">PDF</a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @include('partials.hms-inline-print')
    @endif
</div>
@endsection
