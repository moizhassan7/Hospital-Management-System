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
                @foreach($testHistory as $testId => $results)
                    @php
                        // Get the test name from the first result in the collection
                        $testName = $results->first()->test->name;
                    @endphp
                    <div class="mb-6">
                        <h4 class="text-xl font-semibold text-gray-700 mb-2">{{ $testName }}</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
                                <thead class="bg-gray-100 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particular</th>
                                        @php
                                            $uniqueDates = $results->pluck('created_at')->unique()->sort();
                                        @endphp
                                        @foreach($uniqueDates as $date)
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $date->format('d-M-Y') }}</th>
                                        @endforeach
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference Value</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @php
                                        $particulars = $results->groupBy('test_particular.name');
                                    @endphp
                                    @foreach($particulars as $particularName => $particularResults)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $particularName }}</td>
                                            @foreach($uniqueDates as $date)
                                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $particularResults->where('created_at', $date)->first()->result_value ?? 'N/A' }}
                                                </td>
                                            @endforeach
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                                {{ $particularResults->first()->testParticular->normal_range_min ?? '' }} - {{ $particularResults->first()->testParticular->normal_range_max ?? '' }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                                {{ $particularResults->first()->testParticular->unit ?? '' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif
@endsection