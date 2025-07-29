@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Add Emergency Charges</h2>
        <a href="{{ route('departments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Departments
        </a>
    </div>

    <!-- Add Emergency Charges Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Charge Details</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="first_hour_charges" class="block text-gray-700 text-sm font-bold mb-2">First Hour Charges (PKR):</label>
                    <input type="number" id="first_hour_charges" name="first_hour_charges" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 100.00" min="0" step="0.01" required>
                </div>
                <div>
                    <label for="other_hours_charges" class="block text-gray-700 text-sm font-bold mb-2">Other Hours Charges (PKR):</label>
                    <input type="number" id="other_hours_charges" name="other_hours_charges" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 50.00" min="0" step="0.01" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save Charges
                </button>
            </div>
        </form>
    </div>

    <!-- Emergency Charges List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Emergency Charges</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Charge ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Hour Charges</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Other Hours Charges</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Emergency Charges Data --}}
                    @php
                        $emergencyCharges = [
                            ['id' => 'EC001', 'first_hour' => 150.00, 'other_hours' => 75.00],
                            ['id' => 'EC002', 'first_hour' => 200.00, 'other_hours' => 100.00],
                            ['id' => 'EC003', 'first_hour' => 120.00, 'other_hours' => 60.00],
                            ['id' => 'EC004', 'first_hour' => 180.00, 'other_hours' => 90.00],
                            ['id' => 'EC005', 'first_hour' => 130.00, 'other_hours' => 65.00],
                        ];
                    @endphp
                    @foreach($emergencyCharges as $index => $charge)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $charge['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs {{ number_format($charge['first_hour'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs {{ number_format($charge['other_hours'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
