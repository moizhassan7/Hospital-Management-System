@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Sample Collection Portal</h2>
        <a href="{{ route('pathology.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pathology
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(!empty($desktopSynced))
        <div class="bg-blue-100 border border-blue-400 text-blue-800 px-4 py-3 rounded-xl relative mb-4" role="alert">
            Patient and booked tests imported from Desktop booking system.
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Search Patient</h3>
        <p class="text-sm text-gray-600 mb-4">Enter <strong>Lab Registration Number</strong> from Desktop booking (invoice / inv number).</p>
        <form action="{{ route('pathology.sample_portal') }}" method="GET" class="flex items-center space-x-4">
            <input type="text" name="lab_reg_no" placeholder="Enter Lab Registration No."
                class="shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent flex-grow"
                value="{{ $labRegNo ?? request('lab_reg_no') }}" required>
            <button type="submit"
                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out">
                Search Patient
            </button>
        </form>
    </div>

    @if(isset($patientRecord))
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <p><strong>Name:</strong> {{ $patientRecord->patient_name }}</p>
                <p><strong>Lab Reg No:</strong> {{ $patientRecord->lab_registration_no ?? 'N/A' }}</p>
                <p><strong>MR No:</strong> {{ $patientRecord->mr_no ?? 'N/A' }}</p>
                <p><strong>Age/Sex:</strong> {{ $patientRecord->age }} / {{ $patientRecord->gender }}</p>
                <p><strong>Priority:</strong> {{ $patientRecord->priority }}</p>
                <p><strong>Contact:</strong> {{ $patientRecord->contact_no ?? 'N/A' }}</p>
                <p><strong>Registered:</strong> {{ $patientRecord->created_at->format('d-M-Y h:i A') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Booked Pathology Tests</h3>
            @if($bookedTests->isEmpty())
                <p class="text-gray-600">No pathology tests booked for this patient.</p>
            @else
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vial Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sample Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collected</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received in Lab</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Print Barcode</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($bookedTests as $test)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $test['name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $test['sample_vial'] }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ \App\Models\LabSampleVial::statusBadgeClass($test['sample_status']) }}">
                                            {{ \App\Models\LabSampleVial::statusLabel($test['sample_status']) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $test['sample_collected_at'] ? \Carbon\Carbon::parse($test['sample_collected_at'])->format('d-M-Y h:i A') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $test['sample_received_in_lab_at'] ? \Carbon\Carbon::parse($test['sample_received_in_lab_at'])->format('d-M-Y h:i A') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $test['result_reported_at'] ? \Carbon\Carbon::parse($test['result_reported_at'])->format('d-M-Y h:i A') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @php
                                            $needsCollection = in_array($test['sample_status'], [null, '', \App\Models\LabSampleVial::STATUS_NOT_COLLECTED], true);
                                        @endphp
                                        <form action="{{ route('pathology.sample_portal.collect_test') }}" method="POST"
                                            onsubmit="return confirm('{{ $needsCollection ? 'Collect sample and print barcode label(s) for' : 'Reprint barcode label(s) for' }} {{ addslashes($test['name']) }}?');">
                                            @csrf
                                            <input type="hidden" name="laboratory_patient_id" value="{{ $patientRecord->id }}">
                                            <input type="hidden" name="test_id" value="{{ $test['id'] }}">
                                            <button type="submit"
                                                class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white font-medium py-1.5 px-3 rounded-lg text-xs whitespace-nowrap">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                                    </path>
                                                </svg>
                                                {{ $needsCollection ? 'Print Barcode' : 'Reprint Barcode' }}
                                            </button>
                                        </form>
                                        @if(($test['vials_required'] ?? 1) > 1)
                                            <span class="block text-xs text-gray-500 mt-1">{{ $test['vials_required'] }} vial(s)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <form action="{{ route('pathology.sample_portal.test_status', $patientRecord->id) }}" method="POST" class="flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="test_id" value="{{ $test['id'] }}">
                                            <input type="hidden" name="lab_reg_no" value="{{ $patientRecord->lab_registration_no }}">
                                            <select name="sample_status" class="border rounded-lg text-sm py-1 px-2 focus:ring-purple-500 focus:border-purple-500">
                                                @foreach($sampleStatuses as $value => $label)
                                                    <option value="{{ $value }}" {{ $test['sample_status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="text-purple-600 hover:text-purple-900 font-medium text-sm whitespace-nowrap">Update</button>
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
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Sample Vials</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vial Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vial #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collected</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Received in Lab</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($existingVials as $vial)
                                <tr class="{{ $vial->expires_at && $vial->expires_at->isPast() ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $vial->barcode }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vial->vial_type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vial->vial_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vial->collected_at?->format('d-M-Y h:i A') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vial->received_in_lab_at?->format('d-M-Y h:i A') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vial->reported_at?->format('d-M-Y h:i A') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $vial->expires_at?->format('d-M-Y h:i A') ?? '—' }}
                                        @if($vial->expires_at && $vial->expires_at->isPast())
                                            <span class="text-red-600 font-semibold">(Expired)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ \App\Models\LabSampleVial::statusBadgeClass($vial->status) }}">
                                            {{ \App\Models\LabSampleVial::statusLabel($vial->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ route('pathology.sample_portal.print', ['laboratory_patient_id' => $patientRecord->id, 'vials' => $vial->id]) }}"
                                                class="text-purple-600 hover:text-purple-900">Reprint</a>
                                            <form action="{{ route('pathology.sample_portal.vial_status', $vial->id) }}" method="POST" class="flex items-center gap-1">
                                                @csrf
                                                <input type="hidden" name="lab_reg_no" value="{{ $patientRecord->lab_registration_no }}">
                                                <select name="status" class="border rounded text-xs py-1 px-1">
                                                    @foreach($sampleStatuses as $value => $label)
                                                        <option value="{{ $value }}" {{ $vial->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="text-xs text-purple-600 font-medium">Save</button>
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
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-xl mb-4">
            No patient registration found for Lab Reg No: <strong>{{ $labRegNo }}</strong>
            @if(!empty($desktopError))
                <p class="mt-2 text-sm"><strong>Desktop DB:</strong> {{ $desktopError }}</p>
            @elseif(empty($desktopSynced))
                <p class="mt-2 text-sm">Desktop booking was also checked — no record was found for this Lab Reg No, or the booked tests could not be matched to the web pathology catalog.</p>
            @endif
        </div>
    @endif
@endsection
