@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Doctor Revenue Share Report</h2>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form method="GET" action="{{ route('reports.revenue_doctors') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div>
                <label for="doctor_id" class="block text-gray-700 text-sm font-bold mb-2">Select Doctor:</label>
                <select id="doctor_id" name="doctor_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Choose Doctor --</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ $selectedDoctorId == $doc->id ? 'selected' : '' }}>{{ $doc->name }} ({{ $doc->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="from_date" class="block text-gray-700 text-sm font-bold mb-2">From Date:</label>
                <input type="date" id="from_date" name="from_date" value="{{ $fromDate }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="to_date" class="block text-gray-700 text-sm font-bold mb-2">To Date:</label>
                <input type="date" id="to_date" name="to_date" value="{{ $toDate }}" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg w-full shadow transition-colors duration-200">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    @if($selectedDoctorId)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-green-100 border border-green-400 p-6 rounded-xl shadow">
                <h3 class="text-xl font-bold text-green-800">Total Doctor Revenue Share</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">Rs. {{ number_format($totalDoctorRevenue, 2) }}</p>
            </div>
            <div class="bg-blue-100 border border-blue-400 p-6 rounded-xl shadow">
                <h3 class="text-xl font-bold text-blue-800">Total Hospital Revenue Share</h3>
                <p class="text-3xl font-bold text-blue-600 mt-2">Rs. {{ number_format($totalHospitalRevenue, 2) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Procedure</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor Share</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hospital Share</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $row['date'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        {{ $row['module'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $row['patient_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $row['procedure_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold text-right">Rs. {{ number_format($row['total_amount'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold text-right">Rs. {{ number_format($row['doctor_share_amount'], 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold text-right">Rs. {{ number_format($row['hospital_share_amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 font-medium">No revenue records found for this doctor in the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
