@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Patient Admission Form</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Admission Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <!-- Patient Information Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No (Medical Record No):</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001" required>
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Jane Doe" required>
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
                    <label for="mobile_number" class="block text-gray-700 text-sm font-bold mb-2">Mobile Number:</label>
                    <input type="tel" id="mobile_number" name="mobile_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., +923xx-xxxxxxx">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                    <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
                    <textarea id="address" name="address" rows="1" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Patient's full address"></textarea>
                </div>
            </div>

            <!-- Admission Details Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Admission Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="admission_date" class="block text-gray-700 text-sm font-bold mb-2">Admission Date:</label>
                    <input type="date" id="admission_date" name="admission_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="admission_time" class="block text-gray-700 text-sm font-bold mb-2">Admission Time:</label>
                    <input type="time" id="admission_time" name="admission_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="ward_number" class="block text-gray-700 text-sm font-bold mb-2">Ward Number:</label>
                    <select id="ward_number" name="ward_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Ward</option>
                        {{-- Static Ward Data (example) --}}
                        <option value="Ward A">Ward A</option>
                        <option value="Ward B">Ward B</option>
                        <option value="ICU">ICU</option>
                        <option value="Private Room 1">Private Room 1</option>
                    </select>
                </div>
                <div>
                    <label for="bed_no" class="block text-gray-700 text-sm font-bold mb-2">Bed No:</label>
                    <input type="text" id="bed_no" name="bed_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., A-101" required>
                    {{-- In a real system, this would be dynamically populated based on selected Ward --}}
                </div>
                <div>
                    <label for="referring_doctor" class="block text-gray-700 text-sm font-bold mb-2">Referring Doctor:</label>
                    <select id="referring_doctor" name="referring_doctor" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Doctor</option>
                        {{-- Static Doctors for dropdown --}}
                        <option value="DOC001">Dr. Alice Smith</option>
                        <option value="DOC002">Dr. Bob Johnson</option>
                        <option value="DOC003">Dr. Carol White</option>
                    </select>
                </div>
                <div>
                    <label for="estimated_discharge_date" class="block text-gray-700 text-sm font-bold mb-2">Estimated Discharge Date:</label>
                    <input type="date" id="estimated_discharge_date" name="estimated_discharge_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="admission_reason" class="block text-gray-700 text-sm font-bold mb-2">Admission Reason:</label>
                    <textarea id="admission_reason" name="admission_reason" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Reason for admission" required></textarea>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="initial_diagnosis" class="block text-gray-700 text-sm font-bold mb-2">Initial Diagnosis:</label>
                    <textarea id="initial_diagnosis" name="initial_diagnosis" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Initial medical diagnosis"></textarea>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="admission_notes" class="block text-gray-700 text-sm font-bold mb-2">Admission Notes:</label>
                    <textarea id="admission_notes" name="admission_notes" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Any additional notes for admission"></textarea>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Admit Patient
                </button>
            </div>
        </form>
    </div>
@endsection
