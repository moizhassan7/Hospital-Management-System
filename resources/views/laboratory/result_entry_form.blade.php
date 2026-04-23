@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Enter Results for {{ $test->name }}</h2>
        <a href="{{ route('laboratory.result_entry.search') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Search
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient: {{ $labPatient->patient_name }} (MR: {{ $labPatient->mr_no }})</h3>

        <form action="{{ route('laboratory.result_entry.save', ['lab_patient_id' => $labPatient->id, 'test_id' => $test->id]) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                @foreach($test->testParticulars as $particular)
                    <div>
                        <label for="result_{{ $particular->id }}" class="block text-gray-700 text-sm font-bold mb-2">
                            {{ $particular->name }}
                            @if($particular->normal_range_min || $particular->normal_range_max)
                                <span class="text-xs text-gray-500">({{ $particular->normal_range_min }} - {{ $particular->normal_range_max }} {{ $particular->unit }})</span>
                            @else
                                <span class="text-xs text-gray-500">({{ $particular->reference_text }})</span>
                            @endif
                        </label>
                        <input type="text" id="result_{{ $particular->id }}" name="result_{{ $particular->id }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter result" required>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save Results
                </button>
            </div>
        </form>
    </div>
@endsection