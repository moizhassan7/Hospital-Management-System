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

        <!-- You can add more report cards here, e.g., Revenue Report, Doctor Performance, etc. -->
        <a href="{{ route('reports.indoor_discharge_payment') }}" class="block">
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
                <div class="text-yellow-600 mb-4">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">IPD Discharge Payment Report</h3>
                <p class="text-gray-600 text-center text-sm mt-2">View Indoor Discharge Patient Payment</p>
            </div>
        </a>
    </div>

@endsection