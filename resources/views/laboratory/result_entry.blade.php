@extends('layouts.app')

@section('page_title', 'Result Entry')

@section('content')
    <div class="hms-re-page">

        {{-- Toolbar: title + inline search --}}
        <div class="hms-re-toolbar">
            <div class="hms-re-toolbar-left">
                <a href="{{ route('pathology.index') }}" class="hms-back-btn !py-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h2 class="hms-re-hero-title">Enter lab registration number</h2>
                <p class="hms-re-hero-desc">Type the registration number from Desktop booking or the pathology slip, then search
                    to enter or view results.</p>
            </div>

        {{-- Ambiguous patients (multiple matches) --}}
        @elseif(isset($ambiguousPatients) && $ambiguousPatients->isNotEmpty())
            <div class="hms-re-hero" style="max-width: 600px; margin: 2rem auto; text-align: left; background: #fff; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div class="flex items-center gap-3 mb-4">
                    <div class="hms-re-hero-icon !mx-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h2 class="hms-re-hero-title !mt-0 !text-xl">Multiple visits found</h2>
                </div>
                <p class="text-sm text-gray-500 mb-4">Multiple patients or visits were found for Lab Registration No. <strong>{{ $labRegNo }}</strong>. Please select the correct visit from the list below:</p>
                
                <div class="space-y-3">
                    @foreach($ambiguousPatients as $ambig)
                        <div class="flex items-center justify-between p-3 border rounded-lg hover:border-teal-500 hover:bg-teal-50 transition-colors">
                            <div>
                                <p class="font-medium text-gray-900">{{ $ambig->patient_name }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $ambig->gender }} / {{ $ambig->age }} &bull; MR: {{ $ambig->mr_no ?? '—' }} &bull; 
                                    Registered: {{ $ambig->created_at->format('d M Y h:i A') }}
                                </p>
                            </div>
                            <a href="{{ request()->fullUrlWithQuery(['patient_id' => $ambig->id]) }}" class="hms-btn hms-btn-teal hms-btn-sm shrink-0">
                                Select
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

        {{-- Searched but not found --}}
        @elseif(empty($patientRecord))
            <div class="hms-alert hms-alert-warning">
                No patient found for Lab Reg No: <strong>{{ $labRegNo }}</strong>
                @if(!empty($desktopError))
                    <p class="mt-2 text-sm"><strong>Desktop DB:</strong> {{ $desktopError }}</p>
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
                        <span class="hms-re-meta-label">Consultant</span>
                        <span class="hms-re-meta-value">{{ $patientRecord->getConsultantLabel() }}</span>
                    </div>
                    <div class="hms-re-meta-item">
                        <span class="hms-re-meta-label">Registered</span>
                        <span class="hms-re-meta-value">{{ $patientRecord->created_at->format('d M Y') }}</span>
                    </div>
                </div>
                @if($historyCount > 0)
                    <div
                        class="bg-white px-5 py-3 border-t sm:border-t-0 sm:border-l border-teal-200 flex items-center gap-2 shrink-0">
                        <button type="button"
                            data-hms-print="{{ route('pathology.print_all_reports', ['lab_patient_id' => $patientRecord->id]) }}"
                            data-collect-due
                            data-due-patient-id="{{ $patientRecord->id }}"
                            data-due-patient-name="{{ $patientRecord->patient_name }}"
                            data-due-lab-reg="{{ $patientRecord->lab_registration_no ?? 'N/A' }}"
                            data-due-mr-no="{{ $patientRecord->mr_no ?? 'N/A' }}"
                            data-due-grand-total="{{ $patientRecord->grand_total }}"
                            data-due-paid-amount="{{ $patientRecord->paid_amount }}"
                            data-due-amount="{{ $patientRecord->due_amount }}"
                            class="hms-btn hms-btn-primary whitespace-nowrap">
                            <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4" />
                            </svg>
                            Print All ({{ $historyCount }})
                        </button>
                        <button type="button"
                            data-hms-print="{{ route('pathology.print_all_reports', ['lab_patient_id' => $patientRecord->id, 'layout' => 'combined']) }}"
                            data-collect-due
                            data-due-patient-id="{{ $patientRecord->id }}"
                            data-due-patient-name="{{ $patientRecord->patient_name }}"
                            data-due-lab-reg="{{ $patientRecord->lab_registration_no ?? 'N/A' }}"
                            data-due-mr-no="{{ $patientRecord->mr_no ?? 'N/A' }}"
                            data-due-grand-total="{{ $patientRecord->grand_total }}"
                            data-due-paid-amount="{{ $patientRecord->paid_amount }}"
                            data-due-amount="{{ $patientRecord->due_amount }}"
                            class="hms-btn hms-btn-ghost whitespace-nowrap" title="Print all tests together on one page">
                            1-Page
                        </button>
                        <button type="button"
                            onclick="sendToWhatsApp('{{ route('pathology.front_desk_print.pdf_all', ['lab_patient_id' => $patientRecord->id]) }}', '{{ $patientRecord->contact_no }}', '{{ addslashes($patientRecord->patient_name) }}', '{{ $patientRecord->lab_registration_no ?? 'N/A' }}')"
                            class="hms-btn bg-green-500 hover:bg-green-600 text-white shrink-0 whitespace-nowrap hms-btn-sm" style="padding: 0.25rem 0.75rem;">
                            <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            WhatsApp
                        </button>
                    </div>
                @endif
            </div>

            @include('partials.pending-payment-card', ['patient' => $patientRecord])

            <div class="hms-re-grid">
                {{-- Pending tests (primary action column) --}}
                <div class="hms-re-main">
                    <div class="hms-re-card">
                        <div class="hms-re-card-header">
                            <h2 class="hms-re-card-title">
                                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Pending tests
                                @if($pendingCount > 0)<span class="hms-re-card-count">{{ $pendingCount }}</span>@endif
                            </h2>
                        </div>
                        <div class="hms-re-card-body">
                            @if($pendingTests->isEmpty())
                                <div class="hms-re-empty">
                                    <div class="hms-re-empty-icon">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
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
                                                <span
                                                    class="hms-re-status {{ \App\Models\LabSampleVial::statusReBadgeClass($sampleStatus) }}">
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
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Completed reports
                                @if($historyCount > 0)<span
                                class="hms-re-card-count !bg-blue-100 !text-blue-800">{{ $historyCount }}</span>@endif
                            </h2>
                            @if($historyCount > 0)
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button"
                                        data-hms-print="{{ route('pathology.print_all_reports', ['lab_patient_id' => $patientRecord->id]) }}"
                                        class="hms-btn hms-btn-primary hms-btn-sm">
                                        Print All
                                    </button>
                                    <button type="button"
                                        data-hms-print="{{ route('pathology.print_all_reports', ['lab_patient_id' => $patientRecord->id, 'layout' => 'combined']) }}"
                                        class="hms-btn hms-btn-ghost hms-btn-sm" title="Print all tests together on one page">
                                        1-Page
                                    </button>
                                    <button type="button"
                                        onclick="sendToWhatsApp('{{ route('pathology.front_desk_print.pdf_all', ['lab_patient_id' => $patientRecord->id]) }}', '{{ $patientRecord->contact_no }}', '{{ addslashes($patientRecord->patient_name) }}', '{{ $patientRecord->lab_registration_no ?? 'N/A' }}')"
                                        class="hms-btn bg-green-500 hover:bg-green-600 text-white shrink-0 hms-btn-sm" style="padding: 0.25rem 0.5rem;" title="Send all reports to WhatsApp">
                                        <svg class="w-3.5 h-3.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="hms-re-card-body">
                            @if($testHistory->isEmpty())
                                <div class="hms-re-empty">
                                    <div class="hms-re-empty-icon">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
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
                                                <p class="hms-re-history-date">{{ $firstResult->created_at->format('d M Y, h:i A') }}
                                                </p>
                                                <div class="hms-re-test-meta mt-2">
                                                    <span class="hms-re-status hms-re-status--result_ready">
                                                        <span class="hms-re-status-dot"></span>
                                                        Result: Entered
                                                    </span>
                                                    <span
                                                        class="hms-re-status {{ \App\Models\LabSampleVial::statusReBadgeClass($sampleStatus) }}">
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
                                                target="_blank"
                                                data-collect-due
                                                data-due-patient-id="{{ $patientRecord->id }}"
                                                data-due-patient-name="{{ $patientRecord->patient_name }}"
                                                data-due-lab-reg="{{ $patientRecord->lab_registration_no ?? 'N/A' }}"
                                                data-due-mr-no="{{ $patientRecord->mr_no ?? 'N/A' }}"
                                                data-due-grand-total="{{ $patientRecord->grand_total }}"
                                                data-due-paid-amount="{{ $patientRecord->paid_amount }}"
                                                data-due-amount="{{ $patientRecord->due_amount }}"
                                                class="hms-btn hms-btn-primary hms-btn-sm">Print</a>
                                            <a href="{{ route('pathology.print_report.pdf', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}"
                                                onclick="event.preventDefault(); window.promptWithHeaderFooter(withHeader => window.location.href = window.appendWithHeaderParam(this.href, withHeader));"
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
            @include('partials.collect-due-modal')

            <script>
                function sendToWhatsApp(downloadUrl, phone, patientName, labRegNo) {
                    if (!phone) {
                        alert('No phone number found for this patient.');
                        return;
                    }
                    
                    window.promptWithHeaderFooter(function(withHeader) {
                        const finalUrl = window.appendWithHeaderParam(downloadUrl, withHeader);
                        var cleanPhone = phone.replace(/\D/g,'');
                        if (cleanPhone.startsWith('0')) {
                            cleanPhone = '92' + cleanPhone.substring(1);
                        }

                        var labName = '{{ config('hospital.name', 'Our Lab') }}';
                        var message = 'Dear *' + patientName + '*,\n\n' +
                                      'Thank you for choosing *' + labName + '*.\n' +
                                      'Your laboratory test results for Registration No. *' + labRegNo + '* are ready.\n\n' +
                                      'Please find your detailed report attached to this message.\n\n' +
                                      'If you have any questions, please do not hesitate to contact us.\n' +
                                      'Wishing you the best of health!\n\n' +
                                      'Warm regards,\n' +
                                      '*' + labName + '*';

                        var text = encodeURIComponent(message);
                        var waUrl = 'https://wa.me/' + cleanPhone + '?text=' + text;

                        window.location.href = finalUrl;
                        
                        setTimeout(function() {
                            window.open(waUrl, '_blank');
                        }, 500);
                    });
                }
            </script>
        @endif
    </div>
@endsection