@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Patient Management</h2>
        <a href="" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

        <!-- Indoor Registration Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-blue-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Indoor Registration</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Register patients for inpatient care and admission.</p>
            <a href="" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Register Indoor Patient</a>
        </div>

        <!-- Outdoor Registration Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-green-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Outdoor Registration</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Register patients for outpatient services and consultations.</p>
            <a href="" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Register Outdoor Patient</a>
        </div>

        <!-- View All Patients Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-purple-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">View All Patients</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Browse and manage all registered patient records.</p>
            <a href="" class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">View All Patients</a>
        </div>

        <!-- New Card: Admission Form -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-orange-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Admission Form</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Process patient admissions to the hospital.</p>
            <a href="{{ route('patients.admission_form') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Fill Admission Form</a>
        </div>

        <!-- New Card: Discharge Form -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-red-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Discharge Form</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Complete patient discharge procedures.</p>
            <a href="{{ route('patients.discharge_form') }}" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Fill Discharge Form</a>
        </div>

        <!-- New Card: Birth Certificates -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-indigo-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Birth Certificates</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Manage and issue birth certificates.</p>
            <a href="{{ route('patients.birth_certificates') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Manage Birth Certificates</a>
        </div>

        <!-- New Card: Death Certificates -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-gray-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Death Certificates</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Manage and issue death certificates.</p>
            <a href="{{ route('patients.death_certificates') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Manage Death Certificates</a>
        </div>

    </div>
@endsection
