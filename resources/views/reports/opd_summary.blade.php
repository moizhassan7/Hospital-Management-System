@extends('layouts.app')

@section('content')

    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">OPD Patient Summary</h2></div>
        <a href="" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Reports Dashboard
        </a>
    </div>

    <div class="hms-panel hms-panel-padded mb-5">
        <form action="{{ route('reports.opd_summary') }}" method="GET" class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-6">
            <div class="flex items-center space-x-2">
                <label for="start_date" class="text-gray-700 text-sm font-bold">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="shadow border rounded-lg py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ $startDate }}">
            </div>
            <div class="flex items-center space-x-2">
                <label for="end_date" class="text-gray-700 text-sm font-bold">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="shadow border rounded-lg py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ $endDate }}">
            </div>
            <button type="submit" class="hms-btn hms-btn-primary">
                Generate Report
            </button>
        </form>

        <div class="text-center mb-8">
            <img src="{{ asset(config('hospital.logo')) }}" alt="{{ config('hospital.name') }}" class="h-16 w-16 object-contain mx-auto mb-2">
            <h1 class="text-2xl font-bold text-gray-800">{{ config('hospital.name') }}</h1>
            <h2 class="text-xl font-semibold text-gray-700">OPD Patient Summary</h2>
            <p class="text-gray-500 mt-2">Date from {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
        </div>

        <div class="hms-table-wrap">
            <table class="hms-table">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sr. #</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php $counter = 1; @endphp
                    @foreach($opdAppointments as $appointment)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $counter++ }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $appointment->department_name }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $appointment->total }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-200 font-bold">
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" colspan="2">Total:</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $totalAppointments }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection