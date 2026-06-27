@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">OPD Consultant Department</h2></div>
        <a href="" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <!-- OPD Consultation Form -->
    <div class="hms-panel hms-panel-padded mb-5">
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <!-- Consultation Details Section -->
            <h3 class="hms-section-title">Consultation Details</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="consultation_time" class="hms-label">Time:</label>
                    <input type="time" aria-current="time" id="consultation_time" name="consultation_time" class="hms-input" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="shift" class="hms-label">Shift:</label>
                    <select id="shift" name="shift" class="hms-select" required>
                        <option value="">Select Shift</option>
                        {{-- Static Shifts (example) --}}
                        <option value="Morning Shift">Morning Shift</option>
                        <option value="Evening Shift">Evening Shift</option>
                        <option value="Night Shift">Night Shift</option>
                    </select>
                </div>
                <div>
                    <label for="consultation_date" class="hms-label">Date:</label>
                    <input type="date" id="consultation_date" name="consultation_date" class="hms-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="doctor_code" class="hms-label">Doctor Code:</label>
                    <select id="doctor_code" name="doctor_code" class="hms-select" required>
                        <option value="">Select Doctor Code</option>
                        {{-- Static Doctors for dropdown --}}
                        @php
                            $doctors = [
                                ['code' => 'DOC001', 'name' => 'Dr. Alice Smith', 'department' => 'Cardiology'],
                                ['code' => 'DOC002', 'name' => 'Dr. Bob Johnson', 'department' => 'Pediatrics'],
                                ['code' => 'DOC003', 'name' => 'Dr. Carol White', 'department' => 'Neurology'],
                                ['code' => 'DOC004', 'name' => 'Dr. David Brown', 'department' => 'Orthopedics'],
                            ];
                        @endphp
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor['code'] }}" data-doctor-name="{{ $doctor['name'] }}">{{ $doctor['code'] }} - {{ $doctor['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="doctor_name" class="hms-label">Doctor Name:</label>
                    <input type="text" id="doctor_name" name="doctor_name" class="hms-input hms-input-readonly" placeholder="Auto-populated" readonly>
                </div>
                <div class="flex items-center mt-6">
                    <label class="hms-checkbox-row mr-4">
                        <input type="radio" name="booking_status" value="Yes" id="booking_yes" class="form-radio h-4 w-4 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 text-sm font-bold">Booking Yes</span>
                    </label>
                    <label class="hms-checkbox-row">
                        <input type="radio" name="booking_status" value="No" id="booking_no" class="form-radio h-4 w-4 text-blue-600 focus:ring-blue-500" checked>
                        <span class="ml-2 text-gray-700 text-sm font-bold">Booking No</span>
                    </label>
                </div>
                <div id="booking_no_field" class="hidden">
                    <label for="booking_number" class="hms-label">Booking No:</label>
                    <input type="text" id="booking_number" name="booking_number" class="hms-input" placeholder="e.g., BKN001">
                </div>
                <div>
                    <label for="slip_number" class="hms-label">Slip Number:</label>
                    <input type="text" id="slip_number" name="slip_number" class="hms-input" placeholder="e.g., SLP001" required>
                </div>
                <div>
                    <label for="inv_no" class="hms-label">INV No (Invoice No):</label>
                    <input type="text" id="inv_no" name="inv_no" class="hms-input" placeholder="e.g., INV001" required>
                </div>
                <div>
                    <label for="mr_no" class="hms-label">MR No (Medical Record No):</label>
                    <input type="text" id="mr_no" name="mr_no" class="hms-input" placeholder="e.g., MRN001" required>
                </div>
                <div>
                    <label for="patient_name" class="hms-label">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="hms-input" placeholder="e.g., Patient Name" required>
                </div>
                <div>
                    <label for="age" class="hms-label">Age:</label>
                    <input type="number" id="age" name="age" class="hms-input" placeholder="e.g., 30" min="0" required>
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
                    <label for="refer_by" class="hms-label">Refer By:</label>
                    <input type="text" id="refer_by" name="refer_by" class="hms-input" placeholder="e.g., Dr. XYZ, Self">
                </div>
            </div>

            <!-- Fee Information Section -->
            <h3 class="hms-section-title mt-8">Fee Information</h3>
            <div class="hms-form-grid mb-6">
                <div class="col-span-full flex items-center mb-4">
                    <label class="hms-checkbox-row mr-6">
                        <input type="radio" name="fee_type" value="General OPD Fee" id="general_opd_fee" class="form-radio h-4 w-4 text-blue-600 focus:ring-blue-500" checked>
                        <span class="ml-2 text-gray-700 text-sm font-bold">General OPD Fee</span>
                    </label>
                    <label class="hms-checkbox-row">
                        <input type="radio" name="fee_type" value="Emergency Fee" id="emergency_fee" class="form-radio h-4 w-4 text-red-600 focus:ring-red-500">
                        <span class="ml-2 text-gray-700 text-sm font-bold">Emergency Fee</span>
                    </label>
                </div>

                <div>
                    <label for="total_amount" class="hms-label">Total Amount ($):</label>
                    <input type="text" id="total_amount" name="total_amount" class="hms-input hms-input-readonly" placeholder="Calculated Total" readonly>
                </div>
                <div>
                    <label for="doctor_amount" class="hms-label">Doctor Amount ($):</label>
                    <input type="text" id="doctor_amount" name="doctor_amount" class="hms-input hms-input-readonly" placeholder="Calculated Doctor Share" readonly>
                </div>
                <div>
                    <label for="hospital_amount" class="hms-label">Hospital Amount ($):</label>
                    <input type="text" id="hospital_amount" name="hospital_amount" class="hms-input hms-input-readonly" placeholder="Calculated Hospital Share" readonly>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="hms-form-actions">
                <button type="button" id="print_slip_btn" class="hms-btn hms-btn-secondary">
                    Print Slip
                </button>
                <button type="reset" class="hms-btn hms-btn-danger">
                    Reset
                </button>
                <button type="submit" class="hms-btn hms-btn-primary">
                    Save
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const doctorCodeSelect = document.getElementById('doctor_code');
            const doctorNameInput = document.getElementById('doctor_name');
            const bookingStatusRadios = document.querySelectorAll('input[name="booking_status"]');
            const bookingNoField = document.getElementById('booking_no_field');
            const bookingNumberInput = document.getElementById('booking_number');

            const generalOpdFeeRadio = document.getElementById('general_opd_fee');
            const emergencyFeeRadio = document.getElementById('emergency_fee');
            const totalAmountInput = document.getElementById('total_amount');
            const doctorAmountInput = document.getElementById('doctor_amount');
            const hospitalAmountInput = document.getElementById('hospital_amount');

            // --- Static Fee Configuration (These would come from a database in a real app) ---
            const FEES = {
                'General OPD Fee': {
                    total: 500.00, // Example total
                    doctor_share_percentage: 0.70, // 70% for doctor
                    hospital_share_percentage: 0.30 // 30% for hospital
                },
                'Emergency Fee': {
                    total: 1500.00, // Example total
                    doctor_share_percentage: 0.60, // 60% for doctor
                    hospital_share_percentage: 0.40 // 40% for hospital
                }
            };
            // --- End Static Fee Configuration ---

            // Function to update Doctor Name based on selected Doctor Code
            function updateDoctorDetails() {
                const selectedOption = doctorCodeSelect.options[doctorCodeSelect.selectedIndex];
                if (selectedOption && selectedOption.value !== "") {
                    doctorNameInput.value = selectedOption.getAttribute('data-doctor-name');
                } else {
                    doctorNameInput.value = '';
                }
            }

            // Function to toggle Booking No field visibility
            function toggleBookingNoField() {
                if (document.getElementById('booking_yes').checked) {
                    bookingNoField.classList.remove('hidden');
                    bookingNumberInput.setAttribute('required', 'required');
                } else {
                    bookingNoField.classList.add('hidden');
                    bookingNumberInput.removeAttribute('required');
                    bookingNumberInput.value = ''; // Clear value when hidden
                }
            }

            // Function to calculate and display fee amounts
            function calculateFees() {
                let selectedFeeType = '';
                if (generalOpdFeeRadio.checked) {
                    selectedFeeType = generalOpdFeeRadio.value;
                } else if (emergencyFeeRadio.checked) {
                    selectedFeeType = emergencyFeeRadio.value;
                }

                const feeConfig = FEES[selectedFeeType];

                if (feeConfig) {
                    const total = feeConfig.total;
                    const doctorShare = total * feeConfig.doctor_share_percentage;
                    const hospitalShare = total * feeConfig.hospital_share_percentage;

                    totalAmountInput.value = total.toFixed(2);
                    doctorAmountInput.value = doctorShare.toFixed(2);
                    hospitalAmountInput.value = hospitalShare.toFixed(2);
                } else {
                    totalAmountInput.value = '0.00';
                    doctorAmountInput.value = '0.00';
                    hospitalAmountInput.value = '0.00';
                }
            }

            // --- Event Listeners ---
            doctorCodeSelect.addEventListener('change', updateDoctorDetails);

            bookingStatusRadios.forEach(radio => {
                radio.addEventListener('change', toggleBookingNoField);
            });

            generalOpdFeeRadio.addEventListener('change', calculateFees);
            emergencyFeeRadio.addEventListener('change', calculateFees);

            // --- Initial calls on page load ---
            updateDoctorDetails();
            toggleBookingNoField();
            calculateFees(); // Calculate fees based on default checked radio
        });
    </script>
@endsection
