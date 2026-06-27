@extends('layouts.app')

@section('page_title', 'Critical Test Report')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Critical Test Report</h2>
        <a href="{{ route('pathology.index') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pathology
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Select Date Range</h3>
        <form action="{{ route('pathology.critical_report') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" required
                    class="w-full shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                    value="{{ $dateFromInput }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" required
                    class="w-full shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                    value="{{ $dateToInput }}">
            </div>
            <div>
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg transition-colors">
                    Generate Report
                </button>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('pathology.critical_report.print', ['date_from' => $dateFromInput, 'date_to' => $dateToInput]) }}"
                    target="_blank"
                    class="flex-1 text-center bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors">
                    Print
                </a>
                <a href="{{ route('pathology.critical_report.pdf', ['date_from' => $dateFromInput, 'date_to' => $dateToInput]) }}"
                    class="flex-1 text-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors">
                    PDF
                </a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500">
            <p class="text-sm font-medium text-gray-500 uppercase">Patients with Critical Results</p>
            <p class="text-4xl font-bold text-red-600 mt-2">{{ $patient_count }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-orange-500">
            <p class="text-sm font-medium text-gray-500 uppercase">Critical Test Records</p>
            <p class="text-4xl font-bold text-orange-600 mt-2">{{ $critical_count }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <p class="text-sm font-medium text-gray-500 uppercase">Report Period</p>
            <p class="text-lg font-semibold text-gray-800 mt-2">
                {{ $date_from->format('d M Y') }} — {{ $date_to->format('d M Y') }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Critical Test Records</h3>
        <p class="text-sm text-gray-600 mb-4">Results outside normal reference range (High ▲ / Low ▼) for the selected period.</p>

        @if($records->isEmpty())
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                No critical test results found for this date range.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lab Reg</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Age/Sex</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parameter</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Flag</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($records as $index => $row)
                            <tr class="hover:bg-red-50">
                                <td class="px-3 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $row['result_date']->format('d-M-Y h:i A') }}</td>
                                <td class="px-3 py-3 text-sm font-medium text-gray-900">{{ $row['lab_registration_no'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-900">{{ $row['patient_name'] }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700">{{ $row['age'] }} / {{ $row['gender'] }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700">{{ $row['contact_no'] ?? '—' }}</td>
                                <td class="px-3 py-3 text-sm text-gray-800">{{ $row['test_name'] }}</td>
                                <td class="px-3 py-3 text-sm text-gray-800">{{ $row['parameter'] }}</td>
                                <td class="px-3 py-3 text-sm font-bold text-red-600">
                                    {{ $row['result_value'] }} {{ $row['unit'] }}
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-600">{{ $row['reference_range'] }}</td>
                                <td class="px-3 py-3 text-center">
                                    @if($row['flag'] === 'HIGH')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">▲ HIGH</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">▼ LOW</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm">
                                    <a href="{{ route('pathology.result_entry.view', ['lab_patient_id' => $row['lab_patient_id'], 'test_id' => $row['test_id']]) }}"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium">Report</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
