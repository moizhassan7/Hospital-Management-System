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
        <form action="{{ route('pathology.sample_portal') }}" method="GET" class="flex items-center space-x-4">
            <input type="text" name="mr_no" placeholder="Enter MR Number"
                class="shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent flex-grow"
                value="{{ $mrNo ?? request('mr_no') }}" required>
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
                <p><strong>MR No:</strong> {{ $patientRecord->mr_no ?? 'N/A' }}</p>
                <p><strong>Age/Sex:</strong> {{ $patientRecord->age }} / {{ $patientRecord->gender }}</p>
                <p><strong>Priority:</strong> {{ $patientRecord->priority }}</p>
                <p><strong>Contact:</strong> {{ $patientRecord->contact_no ?? 'N/A' }}</p>
                <p><strong>Registered:</strong> {{ $patientRecord->created_at->format('d-M-Y h:i A') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Booked Pathology Tests</h3>
            @if($pendingTests->isEmpty())
                <p class="text-gray-600">No pending pathology tests found for this patient.</p>
            @else
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sample Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vial Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vials Needed</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry (Hrs)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($pendingTests as $test)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $test['name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $test['type'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $test['sample_vial'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $test['vials_required'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $test['sample_expiry_hours'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($vialSummary->isNotEmpty())
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-purple-800 mb-3">Vials to Print Summary</h4>
                        <ul class="space-y-2">
                            @foreach($vialSummary as $summary)
                                <li class="text-sm text-purple-900">
                                    <strong>{{ $summary['vial_type'] }}:</strong>
                                    {{ $summary['total_vials'] }} vial(s) —
                                    Tests: {{ implode(', ', $summary['test_names']) }}
                                    (Expires in {{ $summary['expiry_hours'] }} hrs)
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <form action="{{ route('pathology.sample_portal.collect') }}" method="POST"
                        onsubmit="return confirm('Collect samples and print barcode labels for all required vials?');">
                        @csrf
                        <input type="hidden" name="laboratory_patient_id" value="{{ $patientRecord->id }}">
                        <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-full shadow-lg transition-colors duration-200 ease-in-out flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            Collect Sample & Print Barcodes
                        </button>
                    </form>
                @endif
            @endif
        </div>

        @if($existingVials->isNotEmpty())
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4 border-b pb-2">
                    <h3 class="text-2xl font-semibold text-gray-800">Previously Collected Samples</h3>
                    <a href="{{ route('pathology.sample_portal.print', ['laboratory_patient_id' => $patientRecord->id]) }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-full text-sm">
                        Reprint All Barcodes
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vial Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vial #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collected</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($existingVials as $vial)
                                <tr class="{{ $vial->expires_at && $vial->expires_at->isPast() ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $vial->barcode }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vial->vial_type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vial->vial_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $vial->collected_at?->format('d-M-Y h:i A') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $vial->expires_at?->format('d-M-Y h:i A') ?? '—' }}
                                        @if($vial->expires_at && $vial->expires_at->isPast())
                                            <span class="text-red-600 font-semibold">(Expired)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst($vial->status) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('pathology.sample_portal.print', ['laboratory_patient_id' => $patientRecord->id, 'vials' => $vial->id]) }}"
                                            class="text-purple-600 hover:text-purple-900">Reprint</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @elseif($mrNo)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-xl">
            No patient registration found for MR Number: <strong>{{ $mrNo }}</strong>
        </div>
    @endif
@endsection
