@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Book Future Appointment</h2></div>
        <a href="{{ route('patients.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    @include('partials.flash-alerts')

@if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Please fix the following errors:</span>
            <ul class="mt-3 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="hms-panel hms-panel-padded mb-5">
        <form id="booking_form" action="{{ route('patients.store_appointment') }}" method="POST">
            @csrf

            <h3 class="hms-section-title">Patient Details</h3>
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="mr_number" class="hms-label">MR Number:</label>
                    <input type="text" id="mr_number" name="mr_number" class="hms-input" placeholder="e.g., MRN001" required>
                </div>
                <div>
                    <label for="patient_name" class="hms-label">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="hms-input hms-input-readonly" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="age" class="hms-label">Age:</label>
                    <input type="text" id="age" name="age" class="hms-input hms-input-readonly" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="gender" class="hms-label">Gender:</label>
                    <input type="text" id="gender" name="gender" class="hms-input hms-input-readonly" placeholder="Auto-populated" readonly>
                </div>
            </div>

            <h3 class="hms-section-title mt-8">Appointment Details</h3>
            <div class="hms-form-grid mb-6">
                 <div>
                    <label for="appointment_number" class="hms-label">Appointment Number:</label>
                    <input type="text" id="appointment_number" name="appointment_number" class="hms-input hms-input-readonly" placeholder="Auto-generated on save" readonly>
                </div>
                <div>
                    <label for="appointment_date" class="hms-label">Appointment Date:</label>
                    <input type="date" id="appointment_date" name="appointment_date" class="hms-input" required>
                </div>
                <div>
                    <label for="appointment_time" class="hms-label">Appointment Time:</label>
                    <input type="time" id="appointment_time" name="appointment_time" class="hms-input" required>
                </div>
                <div>
                    <label for="doctor_code" class="hms-label">Doctor Code:</label>
                    <select id="doctor_code" name="doctor_code" class="hms-select" required>
                        <option value="">Select Doctor Code</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->code }}" data-name="{{ $doctor->name }}">{{ $doctor->code }} - {{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="referred_by" class="hms-label">Referred By:</label>
                    <input type="text" id="referred_by" name="referred_by" class="hms-input" placeholder="e.g., Dr. Ali">
                </div>
            </div>

            <div class="hms-form-actions">
                <button type="submit" id="book_btn" class="hms-btn hms-btn-primary">
                    Book Appointment
                </button>
            </div>
        </form>
    </div>

    <script>
        const mrNumberInput = document.getElementById('mr_number');
        const patientNameInput = document.getElementById('patient_name');
        const ageInput = document.getElementById('age');
        const genderInput = document.getElementById('gender');

        async function fetchPatientDetails(mrNo) {
            try {
                const response = await fetch(`/patients/api/get-by-mr-no/${mrNo}`);
                const patient = await response.json();
                if (patient) {
                    patientNameInput.value = patient.name;
                    ageInput.value = patient.age;
                    genderInput.value = patient.gender;
                } else {
                    patientNameInput.value = '';
                    ageInput.value = '';
                    genderInput.value = '';
                }
            } catch (error) {
                console.error('Error fetching patient data:', error);
                alert('Failed to fetch patient data. Please try again.');
            }
        }

        mrNumberInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                fetchPatientDetails(this.value);
            }
        });
    </script>
@endsection