@extends('layouts.app')

@section('page_title', 'Sample Portal')

@section('content')
    @include('partials.page-shell-start', [
        'title' => 'Sample Collection Portal',
        'subtitle' => 'Search patient and collect pathology samples',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Back to Pathology',
    ])



    <div class="hms-panel hms-panel-padded mb-5">
        <h3 class="hms-filter-title">Search patient</h3>
        <p class="text-sm text-gray-500 mb-4">Enter <strong>Lab Registration Number</strong> to find patient details and collect samples.</p>
        <form action="{{ route('pathology.sample_portal') }}" method="GET" class="hms-search-bar">
            <input type="text" name="lab_reg_no" placeholder="Enter lab registration no." class="hms-input flex-1" value="{{ $labRegNo ?? request('lab_reg_no') }}" required>
            <button type="submit" class="hms-btn hms-btn-purple">Search patient</button>
        </form>
    </div>

    @if(isset($patientRecord))
        <div class="hms-panel mb-5">
            <div class="hms-panel-header flex justify-between items-center">
                <h3 class="hms-panel-title">Patient details</h3>
                <a href="{{ route('pathology.bookings.receipt', $patientRecord->id) }}" 
                   onclick="window.open(this.href, '_blank', 'width=350,height=600'); return false;" 
                   class="hms-btn hms-btn-purple hms-btn-sm flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Receipt Slip
                </a>
            </div>
            <div class="hms-panel-body">
                <div class="hms-detail-grid">
                    <div class="hms-detail-item"><strong>Name</strong>{{ $patientRecord->patient_name }}</div>
                    <div class="hms-detail-item"><strong>Lab reg no</strong>{{ $patientRecord->lab_registration_no ?? 'N/A' }}</div>
                    <div class="hms-detail-item"><strong>MR no</strong>{{ $patientRecord->mr_no ?? 'N/A' }}</div>
                    <div class="hms-detail-item"><strong>Age / sex</strong>{{ $patientRecord->age }} / {{ $patientRecord->gender }}</div>
                    <div class="hms-detail-item"><strong>Priority</strong>{{ $patientRecord->priority }}</div>
                    <div class="hms-detail-item"><strong>Contact</strong>{{ $patientRecord->contact_no ?? 'N/A' }}</div>
                    <div class="hms-detail-item"><strong>Consultant</strong>{{ $patientRecord->getConsultantLabel() }}</div>
                    <div class="hms-detail-item"><strong>Registered</strong>{{ $patientRecord->created_at->format('d-M-Y h:i A') }}</div>
                </div>
            </div>
        </div>

        <div class="hms-panel hms-panel-flush mb-5">
            <div class="hms-panel-header"><h3 class="hms-panel-title">Booked pathology tests</h3></div>
            @if($bookedTests->isEmpty())
                <div class="hms-empty"><p class="hms-empty-title">No tests booked</p><p class="hms-empty-desc">No pathology tests booked for this patient.</p></div>
            @else
                <div class="hms-table-wrap">
                    <table class="hms-table">
                        <thead>
                            <tr>
                                <th>Test name</th>
                                <th>Vial type</th>
                                <th>Vials</th>
                                <th>Status</th>
                                <th>Collected</th>
                                <th>Received</th>
                                <th>Reported</th>
                                <th>Barcode</th>
                                <th>Update status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookedTests as $test)
                                <tr>
                                    <td>{{ $test['name'] }}</td>
                                    <td>{{ $test['sample_vial'] }}</td>
                                    <td>
                                        @if(str_contains($test['sample_vial'] ?? '', ','))
                                            {{ count(array_filter(array_map('trim', explode(',', $test['sample_vial'])))) }} types
                                        @else
                                            {{ $test['vials_required'] ?? 1 }}×
                                        @endif
                                    </td>
                                    <td>
                                        <span class="hms-badge hms-badge-purple {{ \App\Models\LabSampleVial::statusBadgeClass($test['sample_status']) }}">
                                            {{ \App\Models\LabSampleVial::statusLabel($test['sample_status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $test['sample_collected_at'] ? \Carbon\Carbon::parse($test['sample_collected_at'])->format('d-M-Y h:i A') : '—' }}</td>
                                    <td>{{ $test['sample_received_in_lab_at'] ? \Carbon\Carbon::parse($test['sample_received_in_lab_at'])->format('d-M-Y h:i A') : '—' }}</td>
                                    <td>{{ $test['result_reported_at'] ? \Carbon\Carbon::parse($test['result_reported_at'])->format('d-M-Y h:i A') : '—' }}</td>
                                    <td>
                                        @php $needsCollection = in_array($test['sample_status'], [null, '', \App\Models\LabSampleVial::STATUS_NOT_COLLECTED], true); @endphp
                                        <form action="{{ route('pathology.sample_portal.collect_test') }}" method="POST"
                                            onsubmit="return confirm('{{ $needsCollection ? 'Collect and print barcode for' : 'Reprint barcode for' }} {{ addslashes($test['name']) }}?');">
                                            @csrf
                                            <input type="hidden" name="laboratory_patient_id" value="{{ $patientRecord->id }}">
                                            <input type="hidden" name="test_id" value="{{ $test['id'] }}">
                                            <button type="submit" class="hms-btn hms-btn-purple hms-btn-sm">
                                                {{ $needsCollection ? 'Print barcode' : 'Reprint' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('pathology.sample_portal.test_status', $patientRecord->id) }}" method="POST" class="sample-status-form flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="test_id" value="{{ $test['id'] }}">
                                            <input type="hidden" name="lab_reg_no" value="{{ $patientRecord->lab_registration_no }}">
                                            <select name="sample_status" class="hms-select hms-btn-sm !py-1 !px-2 sample-status-select" data-original="{{ $test['sample_status'] }}">
                                                @foreach($sampleStatuses as $value => $label)
                                                    <option value="{{ $value }}" {{ $test['sample_status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($existingVials->isNotEmpty())
            <div class="hms-panel hms-panel-flush mb-5">
                <div class="hms-panel-header"><h3 class="hms-panel-title">Sample vials</h3></div>
                <div class="hms-table-wrap">
                    <table class="hms-table">
                        <thead>
                            <tr>
                                <th>Barcode</th><th>Vial type</th><th>Vial #</th><th>Collected</th>
                                <th>Received</th><th>Reported</th><th>Expires</th><th>Status</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($existingVials as $vial)
                                <tr class="{{ $vial->expires_at && $vial->expires_at->isPast() ? 'bg-red-50' : '' }}">
                                    <td class="font-mono">{{ $vial->barcode }}</td>
                                    <td>{{ $vial->vial_type }}</td>
                                    <td>{{ $vial->vial_number }}</td>
                                    <td>{{ $vial->collected_at?->format('d-M-Y h:i A') ?? '—' }}</td>
                                    <td>{{ $vial->received_in_lab_at?->format('d-M-Y h:i A') ?? '—' }}</td>
                                    <td>{{ $vial->reported_at?->format('d-M-Y h:i A') ?? '—' }}</td>
                                    <td>
                                        {{ $vial->expires_at?->format('d-M-Y h:i A') ?? '—' }}
                                        @if($vial->expires_at && $vial->expires_at->isPast())
                                            <span class="hms-badge hms-badge-red ml-1">Expired</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="hms-badge hms-badge-blue">{{ \App\Models\LabSampleVial::statusLabel($vial->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ route('pathology.sample_portal.print', ['laboratory_patient_id' => $patientRecord->id, 'vials' => $vial->id]) }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">Reprint</a>
                                            <form action="{{ route('pathology.sample_portal.vial_status', $vial->id) }}" method="POST" class="sample-status-form flex items-center gap-1">
                                                @csrf
                                                <input type="hidden" name="lab_reg_no" value="{{ $patientRecord->lab_registration_no }}">
                                                <select name="status" class="hms-select !py-1 !px-2 text-xs sample-status-select" data-original="{{ $vial->status }}">
                                                    @foreach($sampleStatuses as $value => $label)
                                                        <option value="{{ $value }}" {{ $vial->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @elseif($labRegNo)
        <div class="hms-alert hms-alert-warning">
            No patient found for Lab Registration Number: <strong>{{ $labRegNo }}</strong>.
        </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('print_receipt_id'))
                if (confirm('Do you want to print the booking receipt/slip?')) {
                    window.open("{{ route('pathology.bookings.receipt', session('print_receipt_id')) }}", '_blank', 'width=350,height=600');
                }
            @endif

            document.querySelectorAll('.sample-status-select').forEach(function (select) {
                select.addEventListener('change', function () {
                    if (select.value === select.dataset.original) {
                        return;
                    }

                    const form = select.closest('form');
                    if (!form) {
                        return;
                    }

                    // Submit before disabling — disabled fields are excluded from POST data.
                    form.requestSubmit();
                    select.disabled = true;
                });
            });
        });
    </script>
    @endpush
@endsection
