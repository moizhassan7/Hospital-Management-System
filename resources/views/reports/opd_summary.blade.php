@extends('layouts.app')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">OPD Patient Summary</h2>
        <a href="" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Reports Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form action="{{ route('reports.opd_summary') }}" method="GET" class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-6">
            <div class="flex items-center space-x-2">
                <label for="start_date" class="text-gray-700 text-sm font-bold">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="shadow border rounded-lg py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ $startDate }}">
            </div>
            <div class="flex items-center space-x-2">
                <label for="end_date" class="text-gray-700 text-sm font-bold">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="shadow border rounded-lg py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ $endDate }}">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Generate Report
            </button>
        </form>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Rai Foundation Teaching Hospital, Sargodha</h1>
            <h2 class="text-xl font-semibold text-gray-700">OPD Patient Summary</h2>
            <p class="text-gray-500 mt-2">Date from {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
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