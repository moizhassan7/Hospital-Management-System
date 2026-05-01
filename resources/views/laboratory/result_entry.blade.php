@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Lab Result Entry</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Lab Management
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Search Patient</h3>
        <form action="{{ route('laboratory.result_entry.search') }}" method="GET" class="flex items-center space-x-4">
            <input type="text" name="mr_no" placeholder="Enter MR Number" class="shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent flex-grow" value="{{ request('mr_no') }}">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out">
                Search
            </button>
        </form>
    </div>

    @if(isset($patientRecord))
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <p><strong>Name:</strong> {{ $patientRecord->patient_name }}</p>
            <p><strong>MR No:</strong> {{ $patientRecord->mr_no }}</p>
            <p><strong>Age/Sex:</strong> {{ $patientRecord->age }} / {{ $patientRecord->gender }}</p>
        </div>

        {{-- Pending Tests Section --}}
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Pending Tests</h3>
            @if($pendingTests->isEmpty())
                <p class="text-gray-600">No pending tests for this patient.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach($pendingTests as $pendingTest)
    <li class="py-4 flex items-center justify-between">
        <span class="font-medium text-gray-900">{{ $pendingTest['name'] }}</span>
      <a href="{{ route('laboratory.result_entry.show_form', ['lab_patient_id' => $patientRecord->id, 'test_id' => $pendingTest['id']]) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-4 rounded-full transition-colors duration-200">
    Enter Results
</a>
    </li>
@endforeach
                </ul>
            @endif
        </div>

        {{-- Test History Section --}}
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Test History</h3>
            @if($testHistory->isEmpty())
                <p class="text-gray-600">No previous test results found.</p>
            @else
                <div class="space-y-3">
                    @foreach($testHistory as $key => $results)
                        @php
                            $firstResult = $results->first();
                            $test = $firstResult->test;
                            $date = $firstResult->created_at->format('d-M-Y');
                            $lab_patient_id = $firstResult->laboratory_patient_id;
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex items-center space-x-4">
                                <div class="bg-blue-100 p-2 rounded-lg text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">{{ $test->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $date }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('laboratory.result_entry.view', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}" class="bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-bold py-2 px-6 rounded-full shadow-md transition-all">
                                    View Report
                                </a>
                                <a href="{{ route('laboratory.print_report', ['lab_patient_id' => $lab_patient_id, 'test_id' => $test->id]) }}" target="_blank" class="bg-gray-800 hover:bg-black text-white text-sm font-bold py-2 px-6 rounded-full shadow-md transition-all flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4"></path></svg>
                                    Print
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
@endsection