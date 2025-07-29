@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Indoor Patient Registration</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Indoor Patient Registration Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <!-- Patient Referral Information Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="patient_refer" class="block text-gray-700 text-sm font-bold mb-2">Patient Refer:</label>
                    <input type="text" id="patient_refer" name="patient_refer" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., OPD">
                </div>
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No (Medical Record No):</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001" required>
                </div>
                <div>
                    <label for="registration_date" class="block text-gray-700 text-sm font-bold mb-2">Registration Date:</label>
                    <input type="date" id="registration_date" name="registration_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Patient Name" required>
                </div>
                <div>
                    <label for="martial_status" class="block text-gray-700 text-sm font-bold mb-2">Martial Status:</label>
                    <select id="martial_status" name="martial_status" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>
                <div>
                    <label for="date_of_birth" class="block text-gray-700 text-sm font-bold mb-2">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
            </div>

            <!-- Guardian/Relation Information Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Guardian/Relation Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="relation_type" class="block text-gray-700 text-sm font-bold mb-2">Relation Type:</label>
                    <select id="relation_type" name="relation_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Relation</option>
                        <option value="Father">Father</option>
                        <option value="Mother">Mother</option>
                        <option value="Spouse">Spouse</option>
                        <option value="Son">Son</option>
                        <option value="Daughter">Daughter</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="guardian_name" class="block text-gray-700 text-sm font-bold mb-2">Name (Guardian/Relative):</label>
                    <input type="text" id="guardian_name" name="guardian_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Full Name">
                </div>
                <div>
                    <label for="guardian_cnic" class="block text-gray-700 text-sm font-bold mb-2">Guardian CNIC:</label>
                    <input type="text" id="guardian_cnic" name="guardian_cnic" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 12345-6789012-3">
                </div>
                <div>
                    <label for="mobile_number" class="block text-gray-700 text-sm font-bold mb-2">Mobile Number:</label>
                    <input type="tel" id="mobile_number" name="mobile_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., +923xx-xxxxxxx">
                </div>
            </div>

            <!-- Patient Personal Details Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Patient Personal Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="number" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 30" min="0" required>
                </div>
                <div>
                    <label for="weight" class="block text-gray-700 text-sm font-bold mb-2">Weight (kg):</label>
                    <input type="number" id="weight" name="weight" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 70" min="0" step="0.1">
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
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                    <input type="email" id="email" name="email" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., patient@example.com">
                </div>
                <div>
                    <label for="cnic" class="block text-gray-700 text-sm font-bold mb-2">CNIC:</label>
                    <input type="text" id="cnic" name="cnic" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 12345-6789012-3">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address:</label>
                    <textarea id="address" name="address" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Patient's full address"></textarea>
                </div>
            </div>

            <!-- Registration Fee Information Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Registration & Fee Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="file_no" class="block text-gray-700 text-sm font-bold mb-2">File No:</label>
                    <input type="text" id="file_no" name="file_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., F-001">
                </div>
                <div>
                    <label for="slip_no" class="block text-gray-700 text-sm font-bold mb-2">Slip No:</label>
                    <input type="text" id="slip_no" name="slip_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., S-001">
                </div>
                <div>
                    <label for="ward_number" class="block text-gray-700 text-sm font-bold mb-2">Ward Number:</label>
                    <select id="ward_number" name="ward_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Ward</option>
                        {{-- Static Ward Data (example, should be dynamic from DB later) --}}
                        <option value="Ward A">Ward A</option>
                        <option value="Ward B">Ward B</option>
                        <option value="Ward C">Ward C</option>
                        <option value="ICU">ICU</option>
                    </select>
                </div>
                <div>
                    <label for="bed_no" class="block text-gray-700 text-sm font-bold mb-2">Bed No:</label>
                    <input type="text" id="bed_no" name="bed_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., A-101">
                    {{-- For a real system, this would be a dropdown populated based on selected Ward --}}
                </div>
                <div>
                    <label for="admission_fee" class="block text-gray-700 text-sm font-bold mb-2">Admission Fee ($):</label>
                    <input type="number" id="admission_fee" name="admission_fee" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 500" min="0" step="0.01" value="0">
                </div>
                <div>
                    <label for="advance_fee" class="block text-gray-700 text-sm font-bold mb-2">Advance Fee ($):</label>
                    <input type="number" id="advance_fee" name="advance_fee" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 200" min="0" step="0.01" value="0">
                </div>
                <div>
                    <label for="consultant" class="block text-gray-700 text-sm font-bold mb-2">Consultant:</label>
                    <select id="consultant" name="consultant" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Consultant</option>
                        {{-- Static Doctors for dropdown --}}
                        @php
                            $consultants = [
                                ['id' => 1, 'name' => 'Dr. Moiz Hassan'],
                                ['id' => 2, 'name' => 'Dr. Ahmad Chaudhary'],
                                ['id' => 3, 'name' => 'Dr. Abdullah Shahid'],
                                ['id' => 4, 'name' => 'Dr. Fatima Noor'],
                                ['id' => 5, 'name' => 'Dr. Sara Khan'],
                                ['id' => 6, 'name' => 'Dr. Ali Raza'],
                            ];
                        @endphp
                        @foreach($consultants as $consultant)
                            <option value="{{ $consultant['id'] }}">{{ $consultant['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_operation" id="is_operation" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 text-sm font-bold">Is Operation?</span>
                    </label>
                </div>
                <div id="operation_amount_field" class="hidden">
                    <label for="operation_amount" class="block text-gray-700 text-sm font-bold mb-2">Operation Amount ($):</label>
                    <input type="number" id="operation_amount" name="operation_amount" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 1500" min="0" step="0.01" value="0">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="total_amount" class="block text-gray-700 text-sm font-bold mb-2">Total Amount ($):</label>
                    <input type="text" id="total_amount" name="total_amount" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Calculated Total" readonly>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Register Indoor Patient
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isOperationCheckbox = document.getElementById('is_operation');
            const operationAmountField = document.getElementById('operation_amount_field');
            const operationAmountInput = document.getElementById('operation_amount');

            const admissionFeeInput = document.getElementById('admission_fee');
            const advanceFeeInput = document.getElementById('advance_fee');
            const totalAmountInput = document.getElementById('total_amount');

            function calculateTotal() {
                const admission = parseFloat(admissionFeeInput.value) || 0;
                const advance = parseFloat(advanceFeeInput.value) || 0;
                const operation = isOperationCheckbox.checked ? (parseFloat(operationAmountInput.value) || 0) : 0;
                const total = (admission + operation) - advance; // Assuming advance reduces total payable
                totalAmountInput.value = total.toFixed(2); // Format to 2 decimal places
            }

            function toggleOperationAmountField() {
                if (isOperationCheckbox.checked) {
                    operationAmountField.classList.remove('hidden');
                    operationAmountInput.setAttribute('required', 'required');
                } else {
                    operationAmountField.classList.add('hidden');
                    operationAmountInput.removeAttribute('required');
                    operationAmountInput.value = '0'; // Clear or reset value when hidden
                }
                calculateTotal(); // Recalculate total when operation status changes
            }

            // Initial setup
            toggleOperationAmountField();
            calculateTotal();

            // Event listeners
            isOperationCheckbox.addEventListener('change', toggleOperationAmountField);
            admissionFeeInput.addEventListener('input', calculateTotal);
            advanceFeeInput.addEventListener('input', calculateTotal);
            operationAmountInput.addEventListener('input', calculateTotal);
        });
    </script>
@endsection
