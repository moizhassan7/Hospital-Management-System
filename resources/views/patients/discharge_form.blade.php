@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Patient Discharge Form</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Discharge Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <!-- Patient Identification Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Identification</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No (Medical Record No):</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001" required>
                    {{-- In a real system, this would trigger patient data lookup --}}
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="admission_date" class="block text-gray-700 text-sm font-bold mb-2">Admission Date:</label>
                    <input type="date" id="admission_date" name="admission_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>
            </div>

            <!-- Discharge Details Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Discharge Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="discharge_date" class="block text-gray-700 text-sm font-bold mb-2">Discharge Date:</label>
                    <input type="date" id="discharge_date" name="discharge_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="discharge_time" class="block text-gray-700 text-sm font-bold mb-2">Discharge Time:</label>
                    <input type="time" id="discharge_time" name="discharge_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="discharge_status" class="block text-gray-700 text-sm font-bold mb-2">Discharge Status:</label>
                    <select id="discharge_status" name="discharge_status" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Status</option>
                        <option value="Recovered">Recovered</option>
                        <option value="Referred">Referred to another hospital</option>
                        <option value="Against Medical Advice">Against Medical Advice (AMA)</option>
                        <option value="Expired">Expired</option>
                        <option value="Transferred">Transferred</option>
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="discharge_summary" class="block text-gray-700 text-sm font-bold mb-2">Discharge Summary:</label>
                    <textarea id="discharge_summary" name="discharge_summary" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Summary of patient's hospital stay and treatment"></textarea>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="medication_at_discharge" class="block text-gray-700 text-sm font-bold mb-2">Medication at Discharge:</label>
                    <textarea id="medication_at_discharge" name="medication_at_discharge" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Medications prescribed at discharge"></textarea>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="follow_up_instructions" class="block text-gray-700 text-sm font-bold mb-2">Follow-up Instructions:</label>
                    <textarea id="follow_up_instructions" name="follow_up_instructions" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Instructions for post-discharge care"></textarea>
                </div>
                <div>
                    <label for="certifying_doctor" class="block text-gray-700 text-sm font-bold mb-2">Certifying Doctor:</label>
                    <select id="certifying_doctor" name="certifying_doctor" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Doctor</option>
                        {{-- Static Doctors for dropdown --}}
                        <option value="DOC001">Dr. Alice Smith</option>
                        <option value="DOC002">Dr. Bob Johnson</option>
                    </select>
                </div>
                <div>
                    <label for="payment_clearance_status" class="block text-gray-700 text-sm font-bold mb-2">Payment Clearance Status:</label>
                    <select id="payment_clearance_status" name="payment_clearance_status" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Status</option>
                        <option value="Cleared">Cleared</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Discharge Patient
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Placeholder for dynamic patient lookup based on MR No.
            // In a real application, you would use AJAX to fetch patient_name and admission_date
            // when mr_no input changes.
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const admissionDateInput = document.getElementById('admission_date');

            mrNoInput.addEventListener('input', function() {
                // Simulate fetching data
                if (mrNoInput.value === 'MRN001') {
                    patientNameInput.value = 'Jane Doe';
                    admissionDateInput.value = '2025-07-10';
                } else if (mrNoInput.value === 'MRN002') {
                    patientNameInput.value = 'Mark Twain';
                    admissionDateInput.value = '2025-07-12';
                } else {
                    patientNameInput.value = '';
                    admissionDateInput.value = '';
                }
            });
        });
    </script>
@endsection
