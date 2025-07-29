@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Add New Shift</h2>
        <a href="{{ route('departments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Departments
        </a>
    </div>

    <!-- Add Shift Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Shift Details</h3>
        <form action="#" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="shift_name" class="block text-gray-700 text-sm font-bold mb-2">Shift Name:</label>
                    <input type="text" id="shift_name" name="shift_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Morning, Night" required>
                </div>
                <div>
                    <label for="start_time" class="block text-gray-700 text-sm font-bold mb-2">Start Time:</label>
                    <input type="time" id="start_time" name="start_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="end_time" class="block text-gray-700 text-sm font-bold mb-2">End Time:</label>
                    <input type="time" id="end_time" name="end_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add Shift
                </button>
            </div>
        </form>
    </div>

    <!-- Shift List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Shifts</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $shifts = [
                            ['id' => 'SH001', 'name' => 'Morning Shift', 'start' => '08:00', 'end' => '16:00'],
                            ['id' => 'SH002', 'name' => 'Evening Shift', 'start' => '16:00', 'end' => '00:00'],
                            ['id' => 'SH003', 'name' => 'Night Shift', 'start' => '00:00', 'end' => '08:00'],
                            ['id' => 'SH004', 'name' => 'Day Shift (Long)', 'start' => '08:00', 'end' => '20:00'],
                            ['id' => 'SH005', 'name' => 'Weekend Shift', 'start' => '10:00', 'end' => '16:00'],
                        ];
                    @endphp
                    @foreach($shifts as $index => $shift)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $shift['id'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $shift['name'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::createFromFormat('H:i', $shift['start'])->format('g:i A') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::createFromFormat('H:i', $shift['end'])->format('g:i A') }}</td>
                            <td class="px-6 py-4 text-sm font-medium">
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
