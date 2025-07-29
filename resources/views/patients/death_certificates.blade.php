@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Death Certificates Management</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Death Certificate Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Issue New Death Certificate</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="deceased_name" class="block text-gray-700 text-sm font-bold mb-2">Deceased's Name:</label>
                    <input type="text" id="deceased_name" name="deceased_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., John Doe" required>
                </div>
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">Patient MR No:</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001">
                </div>
                <div>
                    <label for="date_of_death" class="block text-gray-700 text-sm font-bold mb-2">Date of Death:</label>
                    <input type="date" id="date_of_death" name="date_of_death" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="time_of_death" class="block text-gray-700 text-sm font-bold mb-2">Time of Death:</label>
                    <input type="time" id="time_of_death" name="time_of_death" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="place_of_death" class="block text-gray-700 text-sm font-bold mb-2">Place of Death:</label>
                    <input type="text" id="place_of_death" name="place_of_death" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="Hospital Name" required>
                </div>
                <div>
                    <label for="cause_of_death" class="block text-gray-700 text-sm font-bold mb-2">Cause of Death:</label>
                    <textarea id="cause_of_death" name="cause_of_death" rows="1" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Cardiac Arrest" required></textarea>
                </div>
                <div>
                    <label for="certifying_doctor" class="block text-gray-700 text-sm font-bold mb-2">Doctor Who Certified Death:</label>
                    <select id="certifying_doctor" name="certifying_doctor" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Doctor</option>
                        {{-- Static Doctors for dropdown --}}
                        <option value="DOC001">Dr. Alice Smith</option>
                        <option value="DOC002">Dr. Bob Johnson</option>
                    </select>
                </div>
                <div>
                    <label for="next_of_kin_name" class="block text-gray-700 text-sm font-bold mb-2">Next of Kin Name:</label>
                    <input type="text" id="next_of_kin_name" name="next_of_kin_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Mary Doe">
                </div>
                <div>
                    <label for="next_of_kin_cnic" class="block text-gray-700 text-sm font-bold mb-2">Next of Kin CNIC:</label>
                    <input type="text" id="next_of_kin_cnic" name="next_of_kin_cnic" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 12345-6789012-3">
                </div>
                <div>
                    <label for="registration_number" class="block text-gray-700 text-sm font-bold mb-2">Registration Number:</label>
                    <input type="text" id="registration_number" name="registration_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., DC-2025-001" required>
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

    <!-- Issued Death Certificates List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Issued Death Certificates</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reg. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deceased Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date of Death</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cause of Death</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Certifying Doctor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Static Death Certificate Data --}}
                    @php
                        $deathCertificates = [
                            ['reg_no' => 'DC-2025-001', 'deceased_name' => 'Patient X', 'dod' => '2025-07-05', 'cause' => 'Heart Failure', 'doctor' => 'Dr. Alice Smith'],
                            ['reg_no' => 'DC-2025-002', 'deceased_name' => 'Patient Y', 'dod' => '2025-07-10', 'cause' => 'Respiratory Failure', 'doctor' => 'Dr. Bob Johnson'],
                        ];
                    @endphp
                    @foreach($deathCertificates as $index => $cert)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['reg_no'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['deceased_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['dod'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['cause'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cert['doctor'] }}</td>
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
