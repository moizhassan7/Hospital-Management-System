@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Book Future Appointment</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

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

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="booking_form" action="{{ route('patients.store_appointment') }}" method="POST">
            @csrf

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="mr_number" class="block text-gray-700 text-sm font-bold mb-2">MR Number:</label>
                    <input type="text" id="mr_number" name="mr_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001" required>
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="text" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-bold mb-2">Gender:</label>
                    <input type="text" id="gender" name="gender" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Appointment Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                 <div>
                    <label for="appointment_number" class="block text-gray-700 text-sm font-bold mb-2">Appointment Number:</label>
                    <input type="text" id="appointment_number" name="appointment_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-generated on save" readonly>
                </div>
                <div>
                    <label for="appointment_date" class="block text-gray-700 text-sm font-bold mb-2">Appointment Date:</label>
                    <input type="date" id="appointment_date" name="appointment_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label for="appointment_time" class="block text-gray-700 text-sm font-bold mb-2">Appointment Time:</label>
                    <input type="time" id="appointment_time" name="appointment_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label for="doctor_code" class="block text-gray-700 text-sm font-bold mb-2">Doctor Code:</label>
                    <select id="doctor_code" name="doctor_code" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Doctor Code</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->code }}" data-name="{{ $doctor->name }}">{{ $doctor->code }} - {{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="referred_by" class="block text-gray-700 text-sm font-bold mb-2">Referred By:</label>
                    <input type="text" id="referred_by" name="referred_by" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Dr. Ali">
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="submit" id="book_btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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