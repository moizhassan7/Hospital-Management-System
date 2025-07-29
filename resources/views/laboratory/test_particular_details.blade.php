@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Test Particular Details</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Laboratory Management
        </a>
    </div>

    <!-- Test Particulars List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Comprehensive List of Test Particulars</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particular ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particular Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Normal Range</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference Notes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Test Particular Data --}}
                    @php
                        $particulars = [
                            ['id' => 'P001', 'name' => 'Hemoglobin', 'test' => 'Complete Blood Count (CBC)', 'head' => 'Hematology', 'unit' => 'g/dL', 'range' => '12.0 - 16.0', 'notes' => 'Varies by age and gender'],
                            ['id' => 'P002', 'name' => 'White Blood Cell Count', 'test' => 'Complete Blood Count (CBC)', 'head' => 'Hematology', 'unit' => 'x10^9/L', 'range' => '4.0 - 11.0', 'notes' => ''],
                            ['id' => 'P003', 'name' => 'Fasting Glucose', 'test' => 'Blood Glucose Fasting', 'head' => 'Biochemistry', 'unit' => 'mg/dL', 'range' => '70 - 99', 'notes' => 'Requires 8-12 hours fasting'],
                            ['id' => 'P004', 'name' => 'Creatinine', 'test' => 'Kidney Function Test', 'head' => 'Biochemistry', 'unit' => 'mg/dL', 'range' => '0.6 - 1.2', 'notes' => 'Higher levels indicate kidney issues'],
                            ['id' => 'P005', 'name' => 'Urinalysis pH', 'test' => 'Urine Routine Examination', 'head' => 'Microbiology', 'unit' => '', 'range' => '4.5 - 8.0', 'notes' => 'Affected by diet'],
                            ['id' => 'P006', 'name' => 'Blood Culture Result', 'test' => 'Blood Culture', 'head' => 'Microbiology', 'unit' => '', 'range' => 'Negative', 'notes' => 'Positive indicates infection'],
                            ['id' => 'P007', 'name' => 'Tissue Biopsy Findings', 'test' => 'Biopsy', 'head' => 'Pathology', 'unit' => '', 'range' => 'Normal / Abnormal', 'notes' => 'Detailed microscopic examination'],
                        ];
                    @endphp
                    @foreach($particulars as $index => $particular)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['test'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['head'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['unit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $particular['range'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" title="{{ $particular['notes'] }}">
                                {{ $particular['notes'] ?: 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
