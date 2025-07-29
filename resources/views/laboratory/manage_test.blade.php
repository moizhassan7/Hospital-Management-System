@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Manage Test</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Laboratory Management
        </a>
    </div>

    <!-- Add Test Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Add New Test</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="test_name" class="block text-gray-700 text-sm font-bold mb-2">Test Name:</label>
                    <input type="text" id="test_name" name="test_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Complete Blood Count" required>
                </div>
                <div>
                    <label for="test_price" class="block text-gray-700 text-sm font-bold mb-2">Price (PKR):</label>
                    <input type="number" id="test_price" name="test_price" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 50.00" min="0" step="0.01" required>
                </div>
                <div>
                    <label for="test_type" class="block text-gray-700 text-sm font-bold mb-2">Test Type:</label>
                    <select id="test_type" name="test_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Type</option>
                        <option value="Blood">Blood</option>
                        <option value="Urine">Urine</option>
                        <option value="Imaging">Imaging</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="test_head" class="block text-gray-700 text-sm font-bold mb-2">Test Head:</label>
                    <select id="test_head" name="test_head" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Test Head</option>
                        {{-- Static Test Heads (example from manage_test_head) --}}
                        <option value="TH001">Hematology</option>
                        <option value="TH002">Biochemistry</option>
                        <option value="TH003">Microbiology</option>
                        <option value="TH004">Pathology</option>
                        <option value="TH005">Serology</option>
                    </select>
                </div>
                <div>
                    <label for="priority" class="block text-gray-700 text-sm font-bold mb-2">Priority:</label>
                    <select id="priority" name="priority" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Priority</option>
                        <option value="Routine">Routine</option>
                        <option value="Urgent">Urgent</option>
                        <option value="STAT">STAT (Critical)</option>
                    </select>
                </div>
                <div>
                    <label for="report_time" class="block text-gray-700 text-sm font-bold mb-2">Report Time (Hours):</label>
                    <input type="number" id="report_time" name="report_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 24" min="0">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add Test
                </button>
            </div>
        </form>
    </div>

    <!-- Test List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Tests</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test Head</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report (Hours)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Test Data --}}
                    @php
                        $tests = [
                            ['id' => 'T001', 'name' => 'Complete Blood Count (CBC)', 'price' => 50.00, 'type' => 'Blood', 'head' => 'Hematology', 'priority' => 'Routine', 'report_time' => 24],
                            ['id' => 'T002', 'name' => 'Blood Glucose Fasting', 'price' => 30.00, 'type' => 'Blood', 'head' => 'Biochemistry', 'priority' => 'Urgent', 'report_time' => 6],
                            ['id' => 'T003', 'name' => 'Urine Routine Examination', 'price' => 25.00, 'type' => 'Urine', 'head' => 'Microbiology', 'priority' => 'Routine', 'report_time' => 12],
                            ['id' => 'T004', 'name' => 'X-Ray Chest PA View', 'price' => 120.00, 'type' => 'Imaging', 'head' => 'Radiology', 'priority' => 'STAT', 'report_time' => 1],
                            ['id' => 'T005', 'name' => 'Thyroid Function Test (TFT)', 'price' => 80.00, 'type' => 'Blood', 'head' => 'Endocrinology', 'priority' => 'Routine', 'report_time' => 48],
                        ];
                    @endphp
                    @foreach($tests as $index => $test)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($test['price'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test['type'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test['head'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test['priority'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $test['report_time'] }}</td>
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
