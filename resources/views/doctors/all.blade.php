@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">All Registered Doctors</h2>
        <a href="{{ route('doctors.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Doctors Management
        </a>
    </div>

    <!-- Doctors List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Comprehensive Doctors List</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Picture</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dr. Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Speciality</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Doctor Data --}}
                    @php
                        $doctors = [
                            [
                                'id' => 'DR001', 'name' => 'Dr. Alice Smith', 'type' => 'Consultant', 'department' => 'Cardiology', 'speciality' => 'Cardiac Surgery',
                                'mobile' => '0300-1112233', 'status' => true, 'picture' => 'https://placehold.co/40x40/AED6F1/2E86C1?text=AS'
                            ],
                            [
                                'id' => 'DR002', 'name' => 'Dr. Bob Johnson', 'type' => 'Resident', 'department' => 'Pediatrics', 'speciality' => 'Pediatric Oncology',
                                'mobile' => '0301-2223344', 'status' => true, 'picture' => 'https://placehold.co/40x40/FADBD8/CB4335?text=BJ'
                            ],
                            [
                                'id' => 'DR003', 'name' => 'Dr. Carol White', 'type' => 'Consultant', 'department' => 'Neurology', 'speciality' => 'Neurophysiology',
                                'mobile' => '0302-3334455', 'status' => false, 'picture' => 'https://placehold.co/40x40/D5F5E3/28B463?text=CW'
                            ],
                            [
                                'id' => 'DR004', 'name' => 'Dr. David Brown', 'type' => 'Intern', 'department' => 'Orthopedics', 'speciality' => 'Joint Replacement',
                                'mobile' => '0303-4445566', 'status' => true, 'picture' => 'https://placehold.co/40x40/E8DAEF/884EA0?text=DB'
                            ],
                            [
                                'id' => 'DR005', 'name' => 'Dr. Eve Davis', 'type' => 'Consultant', 'department' => 'Oncology', 'speciality' => 'Radiation Therapy',
                                'mobile' => '0304-5556677', 'status' => true, 'picture' => 'https://placehold.co/40x40/FCF3CF/D4AC0D?text=ED'
                            ],
                            [
                                'id' => 'DR006', 'name' => 'Dr. Frank Green', 'type' => 'Resident', 'department' => 'Radiology', 'speciality' => 'Diagnostic Imaging',
                                'mobile' => '0305-6667788', 'status' => true, 'picture' => 'https://placehold.co/40x40/F2D7D5/A93226?text=FG'
                            ],
                            [
                                'id' => 'DR007', 'name' => 'Dr. Grace Hall', 'type' => 'Consultant', 'department' => 'Emergency', 'speciality' => 'Trauma Care',
                                'mobile' => '0306-7778899', 'status' => true, 'picture' => 'https://placehold.co/40x40/D6EAF8/21618C?text=GH'
                            ],
                            [
                                'id' => 'DR008', 'name' => 'Dr. Henry Lee', 'type' => 'Consultant', 'department' => 'Dermatology', 'speciality' => 'Cosmetic Dermatology',
                                'mobile' => '0307-8889900', 'status' => false, 'picture' => 'https://placehold.co/40x40/EAECEE/7F8C8D?text=HL'
                            ],
                            [
                                'id' => 'DR009', 'name' => 'Dr. Ivy Moore', 'type' => 'Resident', 'department' => 'Gastroenterology', 'speciality' => 'Endoscopy',
                                'mobile' => '0308-9990011', 'status' => true, 'picture' => 'https://placehold.co/40x40/D1F2EB/1ABC9C?text=IM'
                            ],
                            [
                                'id' => 'DR010', 'name' => 'Dr. Jack Nelson', 'type' => 'Consultant', 'department' => 'Urology', 'speciality' => 'Kidney Transplant',
                                'mobile' => '0309-0001122', 'status' => true, 'picture' => 'https://placehold.co/40x40/FDEBD0/E67E22?text=JN'
                            ],
                        ];
                    @endphp
                    @foreach($doctors as $index => $doctor)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $doctor['picture'] }}" alt="{{ $doctor['name'] }}" onerror="this.onerror=null;this.src='https://placehold.co/40x40/CCCCCC/333333?text=N/A';">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doctor['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doctor['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doctor['type'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doctor['department'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doctor['speciality'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doctor['mobile'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $doctor['status'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $doctor['status'] ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
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
