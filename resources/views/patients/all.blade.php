@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">All Registered Patients</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Patient List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Comprehensive Patient List</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reg. Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Combined Patient Data --}}
                    @php
                        $allPatients = [
                            // Outdoor Patients (example data from outdoor_register)
                            ['id' => 'OPD001', 'name' => 'Alice Smith', 'type' => 'Outdoor', 'reg_date' => '2025-07-18', 'gender' => 'Female', 'age' => 35, 'mobile' => '0300-1234567', 'status' => 'Active'],
                            ['id' => 'OPD002', 'name' => 'Bob Johnson', 'type' => 'Outdoor', 'reg_date' => '2025-07-18', 'gender' => 'Male', 'age' => 52, 'mobile' => '0301-2345678', 'status' => 'Active'],
                            ['id' => 'OPD003', 'name' => 'Charlie Brown', 'type' => 'Outdoor', 'reg_date' => '2025-07-17', 'gender' => 'Male', 'age' => 28, 'mobile' => '0302-3456789', 'status' => 'Active'],
                            ['id' => 'OPD004', 'name' => 'Diana Prince', 'type' => 'Outdoor', 'reg_date' => '2025-07-17', 'gender' => 'Female', 'age' => 41, 'mobile' => '0303-4567890', 'status' => 'Active'],
                            ['id' => 'OPD005', 'name' => 'Eve Adams', 'type' => 'Outdoor', 'reg_date' => '2025-07-16', 'gender' => 'Female', 'age' => 60, 'mobile' => '0304-5678901', 'status' => 'Active'],

                            // Indoor Patients (example static data)
                            ['id' => 'IPD001', 'name' => 'Frank Green', 'type' => 'Indoor', 'reg_date' => '2025-07-15', 'gender' => 'Male', 'age' => 48, 'mobile' => '0305-6789012', 'status' => 'Admitted'],
                            ['id' => 'IPD002', 'name' => 'Grace Hall', 'type' => 'Indoor', 'reg_date' => '2025-07-14', 'gender' => 'Female', 'age' => 22, 'mobile' => '0306-7890123', 'status' => 'Admitted'],
                            ['id' => 'IPD003', 'name' => 'Harry King', 'type' => 'Indoor', 'reg_date' => '2025-07-13', 'gender' => 'Male', 'age' => 67, 'mobile' => '0307-8901234', 'status' => 'Discharged'],
                            ['id' => 'IPD004', 'name' => 'Ivy Lee', 'type' => 'Indoor', 'reg_date' => '2025-07-12', 'gender' => 'Female', 'age' => 30, 'mobile' => '0308-9012345', 'status' => 'Admitted'],
                            ['id' => 'IPD005', 'name' => 'Jack Miller', 'type' => 'Indoor', 'reg_date' => '2025-07-11', 'gender' => 'Male', 'age' => 55, 'mobile' => '0309-0123456', 'status' => 'Admitted'],
                        ];
                    @endphp
                    @foreach($allPatients as $index => $patient)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $patient['type'] == 'Indoor' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $patient['type'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient['reg_date'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient['gender'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient['age'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $patient['mobile'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($patient['status'] == 'Admitted')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Admitted</span>
                                @elseif($patient['status'] == 'Discharged')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Discharged</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">View Profile</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
