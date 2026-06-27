@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Birth Certificates Management</h2></div>
        <a href="{{ route('patients.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Birth Certificate Form -->
    <div class="hms-panel hms-panel-padded mb-5">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Issue New Birth Certificate</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <div class="hms-form-grid mb-6">
                <div>
                    <label for="baby_name" class="hms-label">Baby's Name:</label>
                    <input type="text" id="baby_name" name="baby_name" class="hms-input" placeholder="e.g., Baby Smith" required>
                </div>
                <div>
                    <label for="dob" class="hms-label">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" class="hms-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="time_of_birth" class="hms-label">Time of Birth:</label>
                    <input type="time" id="time_of_birth" name="time_of_birth" class="hms-input" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="place_of_birth" class="hms-label">Place of Birth:</label>
                    <input type="text" id="place_of_birth" name="place_of_birth" class="hms-input" value="{{ config('hospital.name') }}" required>
                </div>
                <div>
                    <label for="gender" class="hms-label">Gender:</label>
                    <select id="gender" name="gender" class="hms-select" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="mother_name" class="hms-label">Mother's Name:</label>
                    <input type="text" id="mother_name" name="mother_name" class="hms-input" placeholder="e.g., Jane Doe" required>
                </div>
                <div>
                    <label for="mother_cnic" class="hms-label">Mother's CNIC:</label>
                    <input type="text" id="mother_cnic" name="mother_cnic" class="hms-input" placeholder="e.g., 12345-6789012-3">
                </div>
                <div>
                    <label for="father_name" class="hms-label">Father's Name:</label>
                    <input type="text" id="father_name" name="father_name" class="hms-input" placeholder="e.g., John Doe">
                </div>
                <div>
                    <label for="father_cnic" class="hms-label">Father's CNIC:</label>
                    <input type="text" id="father_cnic" name="father_cnic" class="hms-input" placeholder="e.g., 12345-6789012-3">
                </div>
                <div>
                    <label for="doctor_delivered" class="hms-label">Doctor Who Delivered:</label>
                    <select id="doctor_delivered" name="doctor_delivered" class="hms-select" required>
                        <option value="">Select Doctor</option>
                        {{-- Static Doctors for dropdown --}}
                        <option value="DOC001">Dr. Alice Smith</option>
                        <option value="DOC002">Dr. Bob Johnson</option>
                    </select>
                </div>
                <div>
                    <label for="registration_number" class="hms-label">Registration Number:</label>
                    <input type="text" id="registration_number" name="registration_number" class="hms-input" placeholder="e.g., BC-2025-001" required>
                </div>
                <div>
                    <label for="date_of_issue" class="hms-label">Date of Issue:</label>
                    <input type="date" id="date_of_issue" name="date_of_issue" class="hms-input" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="hms-btn hms-btn-primary">
                    Issue Certificate
                </button>
            </div>
        </form>
    </div>

    <!-- Issued Birth Certificates List Table -->
    <div class="hms-panel hms-panel-padded">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Issued Birth Certificates</h3>
        <div class="hms-table-wrap">
            <table class="hms-table">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reg. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Baby's Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DOB</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mother's Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Father's Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Birth Certificate Data --}}
                    @php
                        $birthCertificates = [
                            ['reg_no' => 'BC-2025-001', 'baby_name' => 'Baby A', 'dob' => '2025-07-10', 'gender' => 'Female', 'mother_name' => 'Jane Doe', 'father_name' => 'John Doe'],
                            ['reg_no' => 'BC-2025-002', 'baby_name' => 'Baby B', 'dob' => '2025-07-12', 'gender' => 'Male', 'mother_name' => 'Alice Smith', 'father_name' => 'Bob Smith'],
                            ['reg_no' => 'BC-2025-003', 'baby_name' => 'Baby C', 'dob' => '2025-07-15', 'gender' => 'Female', 'mother_name' => 'Sarah Johnson', 'father_name' => 'David Johnson'],
                        ];
                    @endphp
                    @foreach($birthCertificates as $index => $cert)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['reg_no'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['baby_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['dob'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['gender'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['mother_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['father_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
