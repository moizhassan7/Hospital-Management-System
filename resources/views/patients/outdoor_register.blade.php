@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Outdoor Patient Registration</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Outdoor Patient Registration Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Registration Details</h3>
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="opd_number" class="block text-gray-700 text-sm font-bold mb-2">OPD Number:</label>
                    <input type="text" id="opd_number" name="opd_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Auto-generated or Manual" required>
                </div>
                <div>
                    <label for="registration_date" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
                    <input type="date" id="registration_date" name="registration_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No (Medical Record No):</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001" required>
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Full Name" required>
                </div>
                <div>
                    <label for="doctor_code" class="block text-gray-700 text-sm font-bold mb-2">Doctor Code:</label>
                    <select id="doctor_code" name="doctor_code" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Doctor Code</option>
                        {{-- Static Doctors for dropdown --}}
                        @php
                            $doctors = [
                                ['code' => 'DOC001', 'name' => 'Dr. Moiz Hassan', 'shift' => 'Morning Shift'],
                                ['code' => 'DOC002', 'name' => 'Dr. Ahmad Chaudhary', 'shift' => 'Evening Shift'],
                                ['code' => 'DOC003', 'name' => 'Dr. Abdullah Shahid', 'shift' => 'Night Shift'],
                                ['code' => 'DOC004', 'name' => 'Dr. Fatima Noor', 'shift' => 'Morning Shift'],
                                ['code' => 'DOC005', 'name' => 'Dr. Sara Khan', 'shift' => 'Evening Shift'],
                                ['code' => 'DOC006', 'name' => 'Dr. Ali Raza', 'shift' => 'Night Shift'],
                            ];
                        @endphp
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor['code'] }}" data-doctor-name="{{ $doctor['name'] }}" data-doctor-shift="{{ $doctor['shift'] }}">{{ $doctor['code'] }} - {{ $doctor['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="doctor_name" class="block text-gray-700 text-sm font-bold mb-2">Doctor Name:</label>
                    <input type="text" id="doctor_name" name="doctor_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="shift" class="block text-gray-700 text-sm font-bold mb-2">Shift:</label>
                    <input type="text" id="shift" name="shift" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="number" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 30" min="0" required>
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
                    <label for="appointment_date" class="block text-gray-700 text-sm font-bold mb-2">Appointment Date:</label>
                    <input type="date" id="appointment_date" name="appointment_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Register Patient
                </button>
            </div>
        </form>
    </div>

    

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const doctorCodeSelect = document.getElementById('doctor_code');
            const doctorNameInput = document.getElementById('doctor_name');
            const shiftInput = document.getElementById('shift');

            // Function to update Doctor Name and Shift based on selected Doctor Code
            function updateDoctorDetails() {
                const selectedOption = doctorCodeSelect.options[doctorCodeSelect.selectedIndex];
                if (selectedOption && selectedOption.value !== "") {
                    doctorNameInput.value = selectedOption.getAttribute('data-doctor-name');
                    shiftInput.value = selectedOption.getAttribute('data-doctor-shift');
                } else {
                    doctorNameInput.value = '';
                    shiftInput.value = '';
                }
            }

            // Initial call to set values if a default option is selected
            updateDoctorDetails();

            // Add event listener for when the doctor code changes
            doctorCodeSelect.addEventListener('change', updateDoctorDetails);
        });
    </script>
@endsection
