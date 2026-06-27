@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Add New Shift</h2></div>
        <a href="{{ route('departments.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Departments
        </a>
    </div>

    <!-- Add Shift Form -->
    <div class="hms-panel hms-panel-padded mb-5">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Shift Details</h3>
        <form action="#" method="POST">
            @csrf
            <div class="hms-form-grid-2 mb-6">
                <div>
                    <label for="shift_name" class="hms-label">Shift Name:</label>
                    <input type="text" id="shift_name" name="shift_name" class="hms-input focus:ring-2 focus:ring-blue-500" placeholder="e.g., Morning, Night" required>
                </div>
                <div>
                    <label for="start_time" class="hms-label">Start Time:</label>
                    <input type="time" id="start_time" name="start_time" class="hms-input focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="end_time" class="hms-label">End Time:</label>
                    <input type="time" id="end_time" name="end_time" class="hms-input focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="hms-btn hms-btn-primary">
                    Add Shift
                </button>
            </div>
        </form>
    </div>

    <!-- Shift List Table -->
    <div class="hms-panel hms-panel-padded">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Shifts</h3>
        <div class="hms-table-wrap">
            <table class="hms-table">
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
