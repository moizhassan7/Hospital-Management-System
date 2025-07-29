@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Laboratory Management</h2>
        <a href="" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

        <!-- Patient Registration Card (New) -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-indigo-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M12 20.052v-8.3M15 7.052h2.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5H12m-3-10V4.5a1.5 1.5 0 011.5-1.5h3.5a1.5 1.5 0 011.5 1.5V7"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Patient Registration</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Register patients for laboratory tests.</p>
            <a href="{{ route('laboratory.patient_registration') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Register Patient</a>
        </div>

        <!-- Manage Test Head Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-blue-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Manage Test Head</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Define and organize categories for lab tests.</p>
            <a href="{{ route('laboratory.manage_test_head') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Manage Test Head</a>
        </div>

        <!-- Manage Test Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-green-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Manage Test</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Add, edit, or remove individual lab tests.</p>
            <a href="{{ route('laboratory.manage_test') }}" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Manage Test</a>
        </div>

        <!-- Add Test Particulars Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-purple-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Add Test Particulars</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Define specific parameters and normal ranges for tests.</p>
            <a href="{{ route('laboratory.add_test_particulars') }}" class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Add Test Particulars</a>
        </div>

        <!-- Test Particular Details Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-yellow-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-5m3 5v-8m3 5V9M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Test Particular Details</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">View details and results of specific test parameters.</p>
            <a href="{{ route('laboratory.test_particular_details') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">View Details</a>
        </div>

    </div>
@endsection
