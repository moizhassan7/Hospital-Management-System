@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Front Desk — Print Reports</h2>
        <a href="{{ route('pathology.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pathology
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Search Patient</h3>
        <p class="text-sm text-gray-600 mb-4">Enter <strong>Lab Registration Number</strong> or Phone Number to find completed test reports.</p>
        <form action="{{ route('pathology.front_desk_print') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lab Registration No.</label>
                <input type="text" name="lab_reg_no" placeholder="e.g. 1"
                    class="w-full shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    value="{{ $labRegNo ?? '' }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="text" name="phone" placeholder="e.g. 03001234567"
                    class="w-full shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    value="{{ $phone ?? '' }}">
            </div>
            <div>
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition-colors">
                    Search
                </button>
            </div>
        </form>
    </div>

    @if(($labRegNo || $phone) && !$patient)
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-xl mb-8">
            No patient found for the given search. Please check Lab Registration Number or phone number.
        </div>
    @endif

    @if($patient)
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <p><strong>Name:</strong> {{ $patient->patient_name }}</p>
                <p><strong>Lab Reg No:</strong> {{ $patient->lab_registration_no ?? 'N/A' }}</p>
                <p><strong>MR No:</strong> {{ $patient->mr_no ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $patient->contact_no ?? 'N/A' }}</p>
                <p><strong>Age/Sex:</strong> {{ $patient->age }} / {{ $patient->gender }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Completed Tests — Ready to Print</h3>

            @if($completedTests->isEmpty())
                <p class="text-gray-600">No completed pathology test results found for this patient yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registration</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($completedTests as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item['test_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item['completed_at']->format('d-M-Y h:i A') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item['registration_date']->format('d-M-Y') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('pathology.front_desk_print.print', ['lab_patient_id' => $item['lab_patient_id'], 'test_id' => $item['test_id']]) }}"
                                                target="_blank"
                                                class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 px-3 rounded-lg text-xs">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                                    </path>
                                                </svg>
                                                Print
                                            </a>
                                            <a href="{{ route('pathology.front_desk_print.pdf', ['lab_patient_id' => $item['lab_patient_id'], 'test_id' => $item['test_id']]) }}"
                                                class="inline-flex items-center bg-teal-600 hover:bg-teal-700 text-white font-medium py-1.5 px-3 rounded-lg text-xs">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                Download PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
@endsection
