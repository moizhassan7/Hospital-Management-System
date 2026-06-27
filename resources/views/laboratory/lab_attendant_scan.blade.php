@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Lab Attendant — Barcode Scan</h2>
        <a href="{{ route('pathology.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pathology
        </a>
    </div>

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
        <h3 class="text-2xl font-semibold text-gray-800 mb-2 border-b pb-2">Scan Sample Barcode</h3>
        <p class="text-sm text-gray-600 mb-6">
            Scan or type the vial barcode. Status will update to <strong>Received in Lab</strong> and the time will be recorded automatically.
        </p>

        <form action="{{ route('pathology.lab_attendant.scan') }}" method="POST" id="scan-form" class="max-w-2xl">
            @csrf
            <label for="barcode" class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
            <div class="flex gap-3">
                <input type="text" name="barcode" id="barcode" autofocus autocomplete="off"
                    placeholder="Scan barcode here..."
                    value="{{ old('barcode') }}"
                    class="flex-grow shadow appearance-none border-2 border-blue-300 rounded-xl py-4 px-4 text-lg font-mono text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg transition-colors whitespace-nowrap">
                    Receive Sample
                </button>
            </div>
        </form>
    </div>

    @if($lastScan)
        @php
            $isReceived = ($lastScan['result'] ?? '') === 'received';
            $isAlready = ($lastScan['result'] ?? '') === 'already_received';
        @endphp
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border-l-4 {{ $isReceived ? 'border-green-500' : 'border-yellow-500' }}">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-2xl font-semibold text-gray-800">
                        @if($isReceived)
                            Sample Received in Lab
                        @else
                            Already Received in Lab
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500">Scanned at {{ $lastScan['scanned_at'] ?? now()->format('d-M-Y h:i A') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ \App\Models\LabSampleVial::statusBadgeClass($lastScan['status']) }}">
                    {{ $lastScan['status_label'] }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs uppercase text-gray-500 font-semibold">Patient</p>
                    <p class="font-bold text-gray-900">{{ $lastScan['patient_name'] }}</p>
                    <p class="text-sm text-gray-600">MR: {{ $lastScan['mr_no'] }} · {{ $lastScan['age'] }}/{{ $lastScan['gender'] }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs uppercase text-gray-500 font-semibold">Barcode</p>
                    <p class="font-mono font-bold text-gray-900">{{ $lastScan['barcode'] }}</p>
                    <p class="text-sm text-gray-600">{{ $lastScan['vial_type'] }} · Vial #{{ $lastScan['vial_number'] }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs uppercase text-gray-500 font-semibold">Collected At</p>
                    <p class="font-semibold text-gray-900">{{ $lastScan['collected_at'] ?? '—' }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <p class="text-xs uppercase text-blue-600 font-semibold">Received in Lab At</p>
                    <p class="font-bold text-blue-900">{{ $lastScan['received_in_lab_at'] ?? '—' }}</p>
                </div>
            </div>

            <h4 class="font-semibold text-gray-800 mb-3">Tests on this vial</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Received in Lab</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reported At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($lastScan['tests'] as $test)
                            <tr>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $test['name'] }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ \App\Models\LabSampleVial::statusBadgeClass($test['sample_status']) }}">
                                        {{ \App\Models\LabSampleVial::statusLabel($test['sample_status']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    {{ $test['sample_received_in_lab_at'] ? \Carbon\Carbon::parse($test['sample_received_in_lab_at'])->format('d-M-Y h:i A') : '—' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                    {{ $test['result_reported_at'] ? \Carbon\Carbon::parse($test['result_reported_at'])->format('d-M-Y h:i A') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(!empty($lastScan['reported_at']))
                <p class="mt-4 text-sm text-teal-700 bg-teal-50 border border-teal-200 rounded-lg px-4 py-2">
                    <strong>Reporting time:</strong> {{ $lastScan['reported_at'] }}
                </p>
            @endif
        </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('barcode');
            const form = document.getElementById('scan-form');

            if (input) {
                input.focus();
                input.select();
            }

            form?.addEventListener('submit', function () {
                if (input) {
                    input.value = input.value.trim().toUpperCase();
                }
            });
        });
    </script>
    @endpush
@endsection
