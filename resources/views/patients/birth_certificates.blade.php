@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Birth Certificates Management</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Birth Certificate Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Issue New Birth Certificate</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="baby_name" class="block text-gray-700 text-sm font-bold mb-2">Baby's Name:</label>
                    <input type="text" id="baby_name" name="baby_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Baby Smith" required>
                </div>
                <div>
                    <label for="dob" class="block text-gray-700 text-sm font-bold mb-2">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="time_of_birth" class="block text-gray-700 text-sm font-bold mb-2">Time of Birth:</label>
                    <input type="time" id="time_of_birth" name="time_of_birth" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="place_of_birth" class="block text-gray-700 text-sm font-bold mb-2">Place of Birth:</label>
                    <input type="text" id="place_of_birth" name="place_of_birth" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="Hospital Name" required>
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-bold mb-2">Gender:</label>
                    <select id="gender" name="gender" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="mother_name" class="block text-gray-700 text-sm font-bold mb-2">Mother's Name:</label>
                    <input type="text" id="mother_name" name="mother_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Jane Doe" required>
                </div>
                <div>
                    <label for="mother_cnic" class="block text-gray-700 text-sm font-bold mb-2">Mother's CNIC:</label>
                    <input type="text" id="mother_cnic" name="mother_cnic" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 12345-6789012-3">
                </div>
                <div>
                    <label for="father_name" class="block text-gray-700 text-sm font-bold mb-2">Father's Name:</label>
                    <input type="text" id="father_name" name="father_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., John Doe">
                </div>
                <div>
                    <label for="father_cnic" class="block text-gray-700 text-sm font-bold mb-2">Father's CNIC:</label>
                    <input type="text" id="father_cnic" name="father_cnic" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 12345-6789012-3">
                </div>
                <div>
                    <label for="doctor_delivered" class="block text-gray-700 text-sm font-bold mb-2">Doctor Who Delivered:</label>
                    <select id="doctor_delivered" name="doctor_delivered" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Doctor</option>
                        {{-- Static Doctors for dropdown --}}
                        <option value="DOC001">Dr. Alice Smith</option>
                        <option value="DOC002">Dr. Bob Johnson</option>
                    </select>
                </div>
                <div>
                    <label for="registration_number" class="block text-gray-700 text-sm font-bold mb-2">Registration Number:</label>
                    <input type="text" id="registration_number" name="registration_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., BC-2025-001" required>
                </div>
                <div>
                    <label for="date_of_issue" class="block text-gray-700 text-sm font-bold mb-2">Date of Issue:</label>
                    <input type="date" id="date_of_issue" name="date_of_issue" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Issue Certificate
                </button>
            </div>
        </form>
    </div>

    <!-- Issued Birth Certificates List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Issued Birth Certificates</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
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
