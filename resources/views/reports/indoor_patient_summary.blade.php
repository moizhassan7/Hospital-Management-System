@extends('layouts.app')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Indoor Patient Summary</h2>
        <div class="flex items-center space-x-4">
            <a href="" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Reports Dashboard
            </a>
            <a href="{{ route('reports.indoor_patient_summary.download', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download PDF
            </a>
            <a href="javascript:void(0);" 
   onclick="window.print()" 
   class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12"></path>
    </svg>
    Print Report
</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form action="{{ route('reports.indoor_patient_summary') }}" method="GET" class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4 mb-6">
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
            <h2 class="text-xl font-semibold text-gray-700">Indoor Patient Summary</h2>
            <p class="text-gray-500 mt-2">From {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sr. No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ward</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">General</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Welfare</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php $counter = 1; @endphp
                    @foreach($reportData as $wardName => $data)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $counter++ }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $wardName }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $data['general'] }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $data['welfare'] }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $data['total'] }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-200 font-bold">
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900" colspan="2">Grand Total:</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $grandTotalGeneral }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $grandTotalWelfare }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $grandTotal }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

@endsection
