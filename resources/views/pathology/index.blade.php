@extends('layouts.app')

@section('page_title', 'Pathology Lab')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Pathology Lab</h2>
        @unless(config('hospital.pathology_only'))
        <a href="{{ route('dashboard') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>
        @endunless
    </div>

    <h3 class="text-lg font-semibold text-gray-600 uppercase tracking-wide mb-4">Lab Operations</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">

        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-purple-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Sample Portal</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Collect samples and print barcode vial labels.</p>
            <a href="{{ route('pathology.sample_portal') }}"
                class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors">Open Portal</a>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-blue-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Lab Attendant</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Scan barcode to receive sample in lab.</p>
            <a href="{{ route('pathology.lab_attendant') }}"
                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors">Scan Barcode</a>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-teal-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Result Entry</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Search patient and enter test results.</p>
            <a href="{{ route('pathology.result_entry.search') }}"
                class="bg-teal-500 hover:bg-teal-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors">Enter Results</a>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-indigo-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Front Desk Print</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Search by phone or MR and print completed reports.</p>
            <a href="{{ route('pathology.front_desk_print') }}"
                class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors">Open Portal</a>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-red-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Critical Report</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">View patients with critical or abnormal test results for the selected date range.</p>
            <a href="{{ route('pathology.critical_report') }}"
                class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors">Open Report</a>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-cyan-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Samples Report</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">All samples — collection, receive in lab &amp; results status.</p>
            <a href="{{ route('pathology.lab_samples_report') }}"
                class="bg-cyan-500 hover:bg-cyan-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors">Open Report</a>
        </div>
    </div>
@endsection
