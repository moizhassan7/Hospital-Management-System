@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Death Certificates Management</h2></div>
        <a href="{{ route('patients.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Death Certificate Form -->
    <div class="hms-panel hms-panel-padded mb-5">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Issue New Death Certificate</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <div class="hms-form-grid mb-6">
                <div>
                    <label for="deceased_name" class="hms-label">Deceased's Name:</label>
                    <input type="text" id="deceased_name" name="deceased_name" class="hms-input" placeholder="e.g., John Doe" required>
                </div>
                <div>
                    <label for="mr_no" class="hms-label">Patient MR No:</label>
                    <input type="text" id="mr_no" name="mr_no" class="hms-input" placeholder="e.g., MRN001">
                </div>
                <div>
                    <label for="date_of_death" class="hms-label">Date of Death:</label>
                    <input type="date" id="date_of_death" name="date_of_death" class="hms-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="time_of_death" class="hms-label">Time of Death:</label>
                    <input type="time" id="time_of_death" name="time_of_death" class="hms-input" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="place_of_death" class="hms-label">Place of Death:</label>
                    <input type="text" id="place_of_death" name="place_of_death" class="hms-input" value="{{ config('hospital.name') }}" required>
                </div>
                <div>
                    <label for="cause_of_death" class="hms-label">Cause of Death:</label>
                    <textarea id="cause_of_death" name="cause_of_death" rows="1" class="hms-input" placeholder="e.g., Cardiac Arrest" required></textarea>
                </div>
                <div>
                    <label for="certifying_doctor" class="hms-label">Doctor Who Certified Death:</label>
                    <select id="certifying_doctor" name="certifying_doctor" class="hms-select" required>
                        <option value="">Select Doctor</option>
                        {{-- Static Doctors for dropdown --}}
                        <option value="DOC001">Dr. Alice Smith</option>
                        <option value="DOC002">Dr. Bob Johnson</option>
                    </select>
                </div>
                <div>
                    <label for="next_of_kin_name" class="hms-label">Next of Kin Name:</label>
                    <input type="text" id="next_of_kin_name" name="next_of_kin_name" class="hms-input" placeholder="e.g., Mary Doe">
                </div>
                <div>
                    <label for="next_of_kin_cnic" class="hms-label">Next of Kin CNIC:</label>
                    <input type="text" id="next_of_kin_cnic" name="next_of_kin_cnic" class="hms-input" placeholder="e.g., 12345-6789012-3">
                </div>
                <div>
                    <label for="registration_number" class="hms-label">Registration Number:</label>
                    <input type="text" id="registration_number" name="registration_number" class="hms-input" placeholder="e.g., DC-2025-001" required>
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

    <!-- Issued Death Certificates List Table -->
    <div class="hms-panel hms-panel-padded">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Issued Death Certificates</h3>
        <div class="hms-table-wrap">
            <table class="hms-table">
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
