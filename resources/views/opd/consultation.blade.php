@extends('layouts.app')

@section('title', 'OPD consultation')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="OPD consultation" subtitle="Record a consultation visit, its doctor and the fee split.">
        <x-slot:meta>
            <x-ui.badge tone="warning" dot>Preview</x-ui.badge>
        </x-slot:meta>
        <x-slot:actions>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Dashboard
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="warning" title="This form is not connected yet" class="mb-5">
        Doctors and fees here are examples and saving does nothing. Use <a href="{{ route('patients.outdoor_register') }}" class="link">OPD registration</a> to register visits.
    </x-ui.alert>

    <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
        @csrf

        <div class="workspace">
            <x-ui.panel flush>
                <x-ui.form-section title="Visit" icon="calendar">
                    <div class="form-grid">
                        <x-ui.field label="Date" for="consultation_date" required class="md:col-span-4 xl:col-span-3">
                            <input type="date" id="consultation_date" name="consultation_date" class="input" value="{{ date('Y-m-d') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Time" for="consultation_time" required class="md:col-span-4 xl:col-span-3">
                            <input type="time" aria-current="time" id="consultation_time" name="consultation_time" class="input" value="{{ date('H:i') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Shift" for="shift" required class="md:col-span-4 xl:col-span-3">
                            <select id="shift" name="shift" class="select" required>
                                <option value="">Select shift</option>
                                {{-- Static Shifts (example) --}}
                                <option value="Morning Shift">Morning Shift</option>
                                <option value="Evening Shift">Evening Shift</option>
                                <option value="Night Shift">Night Shift</option>
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Slip no." for="slip_number" required class="md:col-span-4 xl:col-span-3 xl:col-start-1">
                            <input type="text" id="slip_number" name="slip_number" class="input input-mono" placeholder="SLP001" required>
                        </x-ui.field>
                        <x-ui.field label="Invoice no." for="inv_no" required class="md:col-span-4 xl:col-span-3">
                            <input type="text" id="inv_no" name="inv_no" class="input input-mono" placeholder="INV001" required>
                        </x-ui.field>
                        <x-ui.field label="Booked visit?" class="md:col-span-4 xl:col-span-3">
                            <div class="segmented" role="radiogroup" aria-label="Booked visit">
                                <label class="segmented-option">
                                    <input type="radio" name="booking_status" value="Yes" id="booking_yes">
                                    <span>Yes</span>
                                </label>
                                <label class="segmented-option">
                                    <input type="radio" name="booking_status" value="No" id="booking_no" checked>
                                    <span>No</span>
                                </label>
                            </div>
                        </x-ui.field>
                        <div id="booking_no_field" class="hidden md:col-span-4 xl:col-span-3">
                            <x-ui.field label="Booking no." for="booking_number">
                                <input type="text" id="booking_number" name="booking_number" class="input input-mono" placeholder="BKN001">
                            </x-ui.field>
                        </div>
                    </div>
                </x-ui.form-section>

                <x-ui.form-section title="Patient" icon="user">
                    <div class="form-grid">
                        <x-ui.field label="MR number" for="mr_no" required class="md:col-span-4 xl:col-span-3">
                            <input type="text" id="mr_no" name="mr_no" class="input input-mono" placeholder="e.g. MRN001" required>
                        </x-ui.field>
                        <x-ui.field label="Patient name" for="patient_name" required class="md:col-span-8 xl:col-span-5">
                            <input type="text" id="patient_name" name="patient_name" class="input" placeholder="Full name" required>
                        </x-ui.field>
                        <x-ui.field label="Age" for="age" required class="md:col-span-3 xl:col-span-2">
                            <input type="number" id="age" name="age" class="input input-num" placeholder="30" min="0" required>
                        </x-ui.field>
                        <x-ui.field label="Gender" for="gender" required class="md:col-span-4 xl:col-span-2">
                            <select id="gender" name="gender" class="select" required>
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Referred by" for="refer_by" class="md:col-span-5 xl:col-span-4">
                            <input type="text" id="refer_by" name="refer_by" class="input" placeholder="e.g. Dr. XYZ or Self">
                        </x-ui.field>
                    </div>
                </x-ui.form-section>

                <x-ui.form-section title="Doctor" icon="stethoscope">
                    <div class="form-grid">
                        <x-ui.field label="Doctor code" for="doctor_code" required class="md:col-span-6">
                            <select id="doctor_code" name="doctor_code" class="select" required>
                                <option value="">Select doctor</option>
                                {{-- Static Doctors for dropdown --}}
                                @php
                                    $doctors = [
                                        ['code' => 'DOC001', 'name' => 'Dr. Alice Smith', 'department' => 'Cardiology'],
                                        ['code' => 'DOC002', 'name' => 'Dr. Bob Johnson', 'department' => 'Pediatrics'],
                                        ['code' => 'DOC003', 'name' => 'Dr. Carol White', 'department' => 'Neurology'],
                                        ['code' => 'DOC004', 'name' => 'Dr. David Brown', 'department' => 'Orthopedics'],
                                    ];
                                @endphp
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor['code'] }}" data-doctor-name="{{ $doctor['name'] }}">{{ $doctor['code'] }} — {{ $doctor['name'] }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Doctor name" for="doctor_name" tag="Auto" class="md:col-span-6">
                            <input type="text" id="doctor_name" name="doctor_name" class="input" placeholder="Filled from the selected doctor" readonly>
                        </x-ui.field>
                    </div>
                </x-ui.form-section>
            </x-ui.panel>

            <aside class="workspace-rail">
                <x-ui.panel title="Fees" icon="receipt" flush>
                    <div class="panel-body space-y-4">
                        <div class="segmented w-full" role="radiogroup" aria-label="Fee type">
                            <label class="segmented-option flex-1">
                                <input type="radio" name="fee_type" value="General OPD Fee" id="general_opd_fee" checked>
                                <span class="w-full justify-center">General OPD</span>
                            </label>
                            <label class="segmented-option flex-1">
                                <input type="radio" name="fee_type" value="Emergency Fee" id="emergency_fee">
                                <span class="w-full justify-center">Emergency</span>
                            </label>
                        </div>
                        <x-ui.field label="Total amount" for="total_amount" tag="Calculated">
                            <div class="input-group has-start">
                                <span class="input-affix is-start">{{ $currency }}</span>
                                <input type="text" id="total_amount" name="total_amount" class="input input-money input-total" placeholder="0.00" readonly>
                            </div>
                        </x-ui.field>
                        <div class="grid grid-cols-2 gap-3">
                            <x-ui.field label="Doctor share" for="doctor_amount">
                                <input type="text" id="doctor_amount" name="doctor_amount" class="input input-money" placeholder="0.00" readonly>
                            </x-ui.field>
                            <x-ui.field label="Hospital share" for="hospital_amount">
                                <input type="text" id="hospital_amount" name="hospital_amount" class="input input-money" placeholder="0.00" readonly>
                            </x-ui.field>
                        </div>
                    </div>
                    <x-slot:footer class="flex-col items-stretch">
                        <button type="submit" class="btn btn-primary btn-block"><x-ui.icon name="check" />Save</button>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" id="print_slip_btn" class="btn btn-secondary"><x-ui.icon name="printer" />Print slip</button>
                            <button type="reset" class="btn btn-ghost">Reset</button>
                        </div>
                    </x-slot:footer>
                </x-ui.panel>
            </aside>
        </div>
    </form>

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
