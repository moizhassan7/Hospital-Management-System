@extends('layouts.app')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Reports Dashboard</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        <!-- Card: Indoor Patient Summary -->
        <a href="{{ route('reports.indoor_patient_summary') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
                <div class="text-blue-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M12 20.052v-8.3M15 7.052h2.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 01-1.5 1.5H12m-3-10V4.5a1.5 1.5 0 011.5-1.5h3.5a1.5 1.5 0 011.5 1.5V7"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Indoor Patient Summary</h3>
                <p class="text-gray-600 text-center text-sm mt-2">View summary of admitted patients</p>
            </div>
        </a>

        <!-- Card: OPD Patient Summary -->
        <a href="{{ route('reports.opd_summary') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
                <div class="text-green-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">OPD Patient Summary</h3>
                <p class="text-gray-600 text-center text-sm mt-2">View outpatient department statistics</p>
            </div>
        </a>
        
        <!-- Card: Indoor Discharge Patient History -->
        <a href="{{ route('reports.indoor_discharge_history') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
                <div class="text-purple-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c0 3.75-5.25 4.5-5.25 4.5S6 15.25 6 11s2.25-4 5.25-4c3 0 5.25 1.5 5.25 4s-2.25 4-5.25 4M12 21a9 9 0 100-18 9 9 0 000 18z"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Discharge History</h3>
                <p class="text-gray-600 text-center text-sm mt-2">View history of discharged patients</p>
            </div>
        </a>

        <!-- Card: IPD Discharge Payment Report -->
        <a href="{{ route('reports.indoor_discharge_payment') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300 h-full">
                <div class="text-yellow-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">IPD Discharge Payment Report</h3>
                <p class="text-gray-600 text-center text-sm mt-2">View Indoor Discharge Patient Payment</p>
            </div>
        </a>

        <!-- Card: Doctor Shares Config -->
        <a href="{{ route('reports.doctor_shares') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300 h-full">
                <div class="text-indigo-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2 text-center">Doctor Config Shares</h3>
                <p class="text-gray-600 text-center text-sm mt-2">View defined percentages for doctors on procedures.</p>
            </div>
        </a>

        <!-- Card: Hospital Shares Config -->
        <a href="{{ route('reports.hospital_shares') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300 h-full">
                <div class="text-pink-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2 text-center">Hospital Config Shares</h3>
                <p class="text-gray-600 text-center text-sm mt-2">View defined percentages for the hospital on procedures.</p>
            </div>
        </a>

        <!-- Card: Doctor Revenue Share -->
        <a href="{{ route('reports.revenue_doctors') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300 h-full">
                <div class="text-green-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2 text-center">Doctor Revenue Share</h3>
                <p class="text-gray-600 text-center text-sm mt-2">Financial report of actual earnings by doctors.</p>
            </div>
        </a>

        <!-- Card: Hospital Revenue Share -->
        <a href="{{ route('reports.revenue_hospital') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300 h-full">
                <div class="text-blue-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2 text-center">Hospital Revenue Share</h3>
                <p class="text-gray-600 text-center text-sm mt-2">Financial report of actual earnings by the hospital.</p>
            </div>
        </a>
    </div>

@endsection