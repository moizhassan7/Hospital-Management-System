@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Patient Admission Form</h2></div>
        <a href="{{ route('patients.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    <!-- Admission Form -->
    <div class="hms-panel hms-panel-padded mb-5">
        <form action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <!-- Patient Information Section -->
            <h3 class="hms-section-title">Patient Information</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="mr_no" class="hms-label">MR No (Medical Record No):</label>
                    <input type="text" id="mr_no" name="mr_no" class="hms-input" placeholder="e.g., MRN001" required>
                </div>
                <div>
                    <label for="patient_name" class="hms-label">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="hms-input" placeholder="e.g., Jane Doe" required>
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
                    <label for="mobile_number" class="hms-label">Mobile Number:</label>
                    <input type="tel" id="mobile_number" name="mobile_number" class="hms-input" placeholder="e.g., +923xx-xxxxxxx">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-1">
                    <label for="address" class="hms-label">Address:</label>
                    <textarea id="address" name="address" rows="1" class="hms-input" placeholder="Patient's full address"></textarea>
                </div>
            </div>

            <!-- Admission Details Section -->
            <h3 class="hms-section-title mt-8">Admission Details</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="admission_date" class="hms-label">Admission Date:</label>
                    <input type="date" id="admission_date" name="admission_date" class="hms-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="admission_time" class="hms-label">Admission Time:</label>
                    <input type="time" id="admission_time" name="admission_time" class="hms-input" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="ward_number" class="hms-label">Ward Number:</label>
                    <select id="ward_number" name="ward_number" class="hms-select" required>
                        <option value="">Select Ward</option>
                        {{-- Static Ward Data (example) --}}
                        <option value="Ward A">Ward A</option>
                        <option value="Ward B">Ward B</option>
                        <option value="ICU">ICU</option>
                        <option value="Private Room 1">Private Room 1</option>
                    </select>
                </div>
                <div>
                    <label for="bed_no" class="hms-label">Bed No:</label>
                    <input type="text" id="bed_no" name="bed_no" class="hms-input" placeholder="e.g., A-101" required>
                    {{-- In a real system, this would be dynamically populated based on selected Ward --}}
                </div>
                <div>
                    <label for="referring_doctor" class="hms-label">Referring Doctor:</label>
                    <select id="referring_doctor" name="referring_doctor" class="hms-select">
                        <option value="">Select Doctor</option>
                        {{-- Static Doctors for dropdown --}}
                        <option value="DOC001">Dr. Alice Smith</option>
                        <option value="DOC002">Dr. Bob Johnson</option>
                        <option value="DOC003">Dr. Carol White</option>
                    </select>
                </div>
                <div>
                    <label for="estimated_discharge_date" class="hms-label">Estimated Discharge Date:</label>
                    <input type="date" id="estimated_discharge_date" name="estimated_discharge_date" class="hms-input">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="admission_reason" class="hms-label">Admission Reason:</label>
                    <textarea id="admission_reason" name="admission_reason" rows="2" class="hms-input" placeholder="Reason for admission" required></textarea>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="initial_diagnosis" class="hms-label">Initial Diagnosis:</label>
                    <textarea id="initial_diagnosis" name="initial_diagnosis" rows="2" class="hms-input" placeholder="Initial medical diagnosis"></textarea>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="admission_notes" class="hms-label">Admission Notes:</label>
                    <textarea id="admission_notes" name="admission_notes" rows="3" class="hms-input" placeholder="Any additional notes for admission"></textarea>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="hms-btn hms-btn-primary">
                    Admit Patient
                </button>
            </div>
        </form>
    </div>
@endsection
