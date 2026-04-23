@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Outdoor Patient Registration</h2>
    <div class="flex space-x-4">
        <a href="{{ route('patients.register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Patient
        </a>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Patient Management
        </a>
    </div>
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
    <form id="outdoor_patient_form" action="{{ route('patients.store_outdoor') }}" method="POST">
        @csrf

        <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div>
                <label for="appointment_number" class="block text-gray-700 text-sm font-bold mb-2">Appointment Number:</label>
                <input type="text" id="appointment_number" name="appointment_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="AP-{{ date('Ymd') }}-{{ $dailySerial }}" readonly>
            </div>
            <div>
                <label for="registration_date" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
                <input type="date" id="registration_date" name="registration_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ date('Y-m-d') }}" readonly>
            </div>
            <div>
                <label for="registration_time" class="block text-gray-700 text-sm font-bold mb-2">Time:</label>
                <input type="time" id="registration_time" name="registration_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ date('H:i') }}" readonly>
            </div>
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

        <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Doctor & Fee Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div>
                <label for="doctor_code" class="block text-gray-700 text-sm font-bold mb-2">Doctor Code:</label>
                <select id="doctor_code" name="doctor_code" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    <option value="">Select Doctor Code</option>
                    @foreach($doctors as $doctor)
                    {{-- Store all fee data on each option --}}
                    <option
                        value="{{ $doctor->code }}"
                        data-name="{{ $doctor->name }}"
                        data-general-normal-fee="{{ $doctor->general_normal_fee }}"
                        data-welfare-normal-fee="{{ $doctor->welfare_normal_fee }}">
                        {{ $doctor->code }} - {{ $doctor->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="doctor_name" class="block text-gray-700 text-sm font-bold mb-2">Doctor Name:</label>
                <input type="text" id="doctor_name" name="doctor_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
            </div>
            <div>
                <label for="doctor_fee" class="block text-gray-700 text-sm font-bold mb-2">Doctor Fee ($):</label>
                <input type="number" id="doctor_fee" name="doctor_fee" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" min="0" step="0.01" readonly>
            </div>
            <div>
                <label for="referred_by" class="block text-gray-700 text-sm font-bold mb-2">Referred By:</label>
                <input type="text" id="referred_by" name="referred_by" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Dr. Ali">
            </div>
            <div>
                <label for="total_amount" class="block text-gray-700 text-sm font-bold mb-2">Total Amount ($):</label>
                <input type="text" id="total_amount" name="total_amount" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Calculated Total" readonly>
            </div>
            <div>
                <label for="token_number" class="block text-gray-700 text-sm font-bold mb-2">Token Number:</label>
                <input type="text" id="token_number" name="token_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Generated on doctor select" readonly>
            </div>
        </div>
        <div class="flex justify-end space-x-4 mt-6">
            <button type="submit" id="save_btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                Save
            </button>
            <button type="button" id="print_slip_btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Print Slip
            </button>
        </div>
    </form>
</div>

<div id="patient_search_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <h3 class="text-xl font-semibold mb-4">Search Patient</h3>
        <input type="text" id="patient_search_query" class="w-full px-3 py-2 border rounded-md" placeholder="Enter MR No, Name, CNIC, or Mobile No.">
        <div id="patient_search_results" class="mt-4 max-h-60 overflow-y-auto"></div>
        <div class="text-right mt-4">
            <button id="close_patient_modal" class="px-4 py-2 bg-gray-300 rounded-md">Close</button>
        </div>
    </div>
</div>

<div id="consultant_search_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <h3 class="text-xl font-semibold mb-4">Search Consultant</h3>
        <input type="text" id="consultant_search_query" class="w-full px-3 py-2 border rounded-md" placeholder="Enter Code, Name, or Department">
        <div id="consultant_search_results" class="mt-4 max-h-60 overflow-y-auto"></div>
        <div class="text-right mt-4">
            <button id="close_consultant_modal" class="px-4 py-2 bg-gray-300 rounded-md">Close</button>
        </div>
    </div>
</div>

<div id="print_area" class="hidden p-4 bg-white border border-gray-300 rounded-lg shadow-xl" style="width: 80mm; font-family: sans-serif;">
    <div class="text-center mb-4">
        <h1 class="text-xl font-bold text-gray-800">KHAZIR HOSPITAL</h1>
        <h2 class="text-md font-semibold text-gray-700">OPD Slip</h2>
    </div>
    
    <div class="mb-4 border-b pb-2">
        <p class="text-sm"><strong>Date:</strong> <span id="print_registration_date"></span></p>
        <p class="text-sm"><strong>Time:</strong> <span id="print_registration_time"></span></p>
    </div>
    
    <div class="text-center my-6">
        <p class="text-md font-bold text-gray-800 mb-2">Token Number</p>
        <p class="text-6xl font-extrabold text-gray-900 leading-none" style="font-size: 60px;">
            <span id="print_token_number"></span>
        </p>
    </div>
    
    <div class="mb-4 border-b pb-2">
        <p class="text-sm mb-1"><strong>Patient:</strong> <span id="print_patient_name"></span></p>
        <p class="text-sm mb-1"><strong>MR No:</strong> <span id="print_mr_number"></span></p>
        <p class="text-sm mb-1"><strong>Doctor:</strong> <span id="print_doctor_name"></span></p>
        <p class="text-sm"><strong>Fee:</strong> <span id="print_total_amount"></span></p>
    </div>
    
    <div class="text-center text-xs text-gray-500 mt-4">
        <p>Software by Switch2Itech</p>
        <p>Printed: {{ date('Y-m-d H:i') }}</p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- Main Form Elements ---
        const outdoorPatientForm = document.getElementById('outdoor_patient_form');
        const appointmentNumberInput = document.getElementById('appointment_number');
        const registrationDateInput = document.getElementById('registration_date');
        const registrationTimeInput = document.getElementById('registration_time');
        const mrNumberInput = document.getElementById('mr_number');
        const patientNameInput = document.getElementById('patient_name');
        const ageInput = document.getElementById('age');
        const genderInput = document.getElementById('gender');
        const doctorCodeSelect = document.getElementById('doctor_code');
        const doctorNameInput = document.getElementById('doctor_name');
        const doctorFeeInput = document.getElementById('doctor_fee');
        const referredByInput = document.getElementById('referred_by');
        const totalAmountInput = document.getElementById('total_amount');
        const tokenNumberInput = document.getElementById('token_number');
        const saveBtn = document.getElementById('save_btn');
        const printSlipBtn = document.getElementById('print_slip_btn');

        // --- Search Modal Elements ---
        const patientSearchModal = document.getElementById('patient_search_modal');
        const patientSearchQueryInput = document.getElementById('patient_search_query');
        const patientSearchResults = document.getElementById('patient_search_results');
        const closePatientModalBtn = document.getElementById('close_patient_modal');

        // Consultant Search Modal Elements
        const consultantSearchModal = document.getElementById('consultant_search_modal');
        const consultantSearchQueryInput = document.getElementById('consultant_search_query');
        const consultantSearchResults = document.getElementById('consultant_search_results');
        const closeConsultantModalBtn = document.getElementById('close_consultant_modal');
        const generateTokenUrl = "{{ route('api.generate-token-number', ['doctor_code' => 'REPLACE_ME']) }}";

        // Patient and Doctor Data Storage
        let patientData = null;
        let doctorData = null;

        // --- AJAX Helpers ---
        async function fetchData(url) {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return await response.json();
        }

        async function sendData(url, method, data) {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            return await response.json();
        }

        // --- General Helpers ---
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                const context = this;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), delay);
            };
        }

        // --- Patient Details Autopopulation & Appointment Check ---
        async function fetchPatientDetails(mrNo) {
            try {
                const patient = await fetchData(`/api/get-by-mr-no/${mrNo}`);
                if (patient) {
                    patientData = patient; // Store fetched patient data
                    patientNameInput.value = patient.name;
                    ageInput.value = patient.age;
                    genderInput.value = patient.gender;
                    mrNumberInput.value = patient.mr_number;
                    // Move focus to the next field
                    doctorCodeSelect.focus();
                    // Recalculate fees after patient data is loaded
                    calculateFees();
                } else {
                    // Clear fields if no patient found
                    patientData = null;
                    patientNameInput.value = '';
                    ageInput.value = '';
                    genderInput.value = '';
                    alert('No patient found with this MR Number. Please register them first.');
                    calculateFees();
                }
            } catch (error) {
                console.error('Error fetching patient data:', error);
                alert('Failed to fetch patient data. Please try again.');
            }
        }

        async function checkBookedAppointment(mrNumber) {
            try {
                const response = await fetch(`/api/check-booked-appointment?mr_number=${mrNumber}`);
                const result = await response.json();
                if (result.hasBookedAppointment) {
                    alert(`Warning: This patient has a pre-booked appointment for today!
    Booking Details:
    Date: ${result.appointmentDetails.appointment_date}
    Doctor: ${result.appointmentDetails.doctor_name}`);
                }
            } catch (error) {
                console.error('Error checking for booked appointment:', error);
            }
        }

        // --- Patient Search Modal Logic ---
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
                event.preventDefault();
                patientSearchModal.classList.remove('hidden');
                // Use setTimeout to ensure focus is applied after the modal has been rendered
                setTimeout(() => patientSearchQueryInput.focus(), 100);
                fetchAndDisplayPatients('');
            }
        });

        patientSearchQueryInput.addEventListener('input', debounce(async function() {
            const query = this.value.trim();
            if (query.length > 2) {
                await fetchAndDisplayPatients(query);
            } else {
                patientSearchResults.innerHTML = '';
            }
        }, 300));

        // Arrow key navigation for patient search results
        patientSearchModal.addEventListener('keydown', function(event) {
            const results = patientSearchResults.querySelectorAll('.search-result-row');
            let activeIndex = -1;
            results.forEach((el, index) => {
                if (el.classList.contains('bg-blue-100')) {
                    activeIndex = index;
                }
            });

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                const nextIndex = (activeIndex + 1) % results.length;
                results.forEach(el => el.classList.remove('bg-blue-100'));
                results[nextIndex].classList.add('bg-blue-100');
                results[nextIndex].focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                const prevIndex = (activeIndex - 1 + results.length) % results.length;
                results.forEach(el => el.classList.remove('bg-blue-100'));
                results[prevIndex].classList.add('bg-blue-100');
                results[prevIndex].focus();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const selectedItem = patientSearchResults.querySelector('.bg-blue-100');
                if (selectedItem) {
                    selectedItem.click();
                }
            } else if (event.key === 'Escape') {
                patientSearchModal.classList.add('hidden');
            }
        });

        async function fetchAndDisplayPatients(query) {
            const url = `/api/search-patient?query=${query}`;
            try {
                const patients = await fetchData(url);
                let tableHtml = '<table class="min-w-full divide-y divide-gray-200">';
                tableHtml += '<thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MR No</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CNIC</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobile No</th></tr></thead>';
                tableHtml += '<tbody class="bg-white divide-y divide-gray-200">';

                if (patients.length > 0) {
                    patients.forEach(patient => {
                        tableHtml += `
                                <tr class="search-result-row hover:bg-gray-100 cursor-pointer" data-mr-no="${patient.mr_number}" tabindex="0">
                                    <td class="px-6 py-4 whitespace-nowrap">${patient.name}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${patient.mr_number}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${patient.cnic || 'N/A'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${patient.mobile_number || 'N/A'}</td>
                                </tr>
                            `;
                    });
                } else {
                    tableHtml += `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No patients found.</td></tr>`;
                }
                tableHtml += '</tbody></table>';
                patientSearchResults.innerHTML = tableHtml;

                // Add click event listeners to the new table rows
                patientSearchResults.querySelectorAll('.search-result-row').forEach(row => {
                    row.addEventListener('click', () => {
                        const mrNumber = row.getAttribute('data-mr-no');
                        mrNumberInput.value = mrNumber;
                        fetchPatientDetails(mrNumber);
                        checkBookedAppointment(mrNumber);
                        patientSearchModal.classList.add('hidden');
                    });
                });

                // We are not auto-focusing the first row, as the user should be able to type.
                // The arrow key navigation will handle focus when needed.
            } catch (error) {
                console.error('Error searching for patients:', error);
                patientSearchResults.innerHTML = '<div class="p-3 text-red-500">Failed to load search results.</div>';
            }
        }

        closePatientModalBtn.addEventListener('click', () => {
            patientSearchModal.classList.add('hidden');
        });

        mrNumberInput.addEventListener('keydown', async (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                const mrNumber = mrNumberInput.value.trim();
                if (mrNumber) {
                    await fetchPatientDetails(mrNumber);
                    await checkBookedAppointment(mrNumber);
                } else {
                    // If MR number is empty, show the search modal
                    patientSearchModal.classList.remove('hidden');
                    setTimeout(() => patientSearchQueryInput.focus(), 100);
                    await fetchAndDisplayPatients('');
                }
            }
        });

        // --- Consultant Search Modal Logic ---
        doctorCodeSelect.addEventListener('keydown', async (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                consultantSearchModal.classList.remove('hidden');
                setTimeout(() => consultantSearchQueryInput.focus(), 100);
                await fetchAndDisplayConsultants('');
            }
        });

        consultantSearchQueryInput.addEventListener('input', debounce(async function() {
            const query = this.value.trim();
            if (query.length > 2) {
                await fetchAndDisplayConsultants(query);
            } else {
                consultantSearchResults.innerHTML = '';
            }
        }, 300));

        // Arrow key navigation for consultant search results
        consultantSearchModal.addEventListener('keydown', function(event) {
            const results = consultantSearchResults.querySelectorAll('.search-result-row');
            let activeIndex = -1;
            results.forEach((el, index) => {
                if (el.classList.contains('bg-blue-100')) {
                    activeIndex = index;
                }
            });

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                const nextIndex = (activeIndex + 1) % results.length;
                results.forEach(el => el.classList.remove('bg-blue-100'));
                results[nextIndex].classList.add('bg-blue-100');
                results[nextIndex].focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                const prevIndex = (activeIndex - 1 + results.length) % results.length;
                results.forEach(el => el.classList.remove('bg-blue-100'));
                results[prevIndex].classList.add('bg-blue-100');
                results[prevIndex].focus();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const selectedItem = consultantSearchResults.querySelector('.bg-blue-100');
                if (selectedItem) {
                    selectedItem.click();
                }
            } else if (event.key === 'Escape') {
                consultantSearchModal.classList.add('hidden');
            }
        });

        async function fetchAndDisplayConsultants(query) {
            const url = `/api/search-doctor?query=${query}`;
            try {
                const doctors = await fetchData(url);
                let tableHtml = '<table class="min-w-full divide-y divide-gray-200">';
                tableHtml += '<thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th></tr></thead>';
                tableHtml += '<tbody class="bg-white divide-y divide-gray-200">';

                if (doctors.length > 0) {
                    doctors.forEach(doctor => {
                        tableHtml += `
                                <tr class="search-result-row hover:bg-gray-100 cursor-pointer" data-doctor-code="${doctor.code}" tabindex="0">
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.code}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.name}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.department.name || 'N/A'}</td>
                                </tr>
                            `;
                    });
                } else {
                    tableHtml += `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No consultants found.</td></tr>`;
                }
                tableHtml += '</tbody></table>';
                consultantSearchResults.innerHTML = tableHtml;

                consultantSearchResults.querySelectorAll('.search-result-row').forEach(row => {
                    row.addEventListener('click', () => {
                        const doctorCode = row.getAttribute('data-doctor-code');
                        const option = doctorCodeSelect.querySelector(`option[value="${doctorCode}"]`);
                        if (option) {
                            option.selected = true;
                            calculateFees();
                            // This is the corrected call to the token number function
                            fetchAndSetTokenNumber(doctorCode);
                        }
                        consultantSearchModal.classList.add('hidden');
                    });
                });

                // We are not auto-focusing the first row, as the user should be able to type.
                // The arrow key navigation will handle focus when needed.
            } catch (error) {
                console.error('Error searching for consultants:', error);
                consultantSearchResults.innerHTML = '<div class="p-3 text-red-500">Failed to load search results.</div>';
            }
        }

        closeConsultantModalBtn.addEventListener('click', () => {
            consultantSearchModal.classList.add('hidden');
        });

        // --- Doctor & Fee Calculation ---
        function calculateFees() {
            const selectedOption = doctorCodeSelect.options[doctorCodeSelect.selectedIndex];
            if (selectedOption && selectedOption.value !== "" && patientData) {
                const doctorName = selectedOption.getAttribute('data-name');
                let doctorFee = 0;

                if (patientData.is_welfare) {
                    doctorFee = parseFloat(selectedOption.getAttribute('data-welfare-normal-fee')) || 0;
                } else {
                    doctorFee = parseFloat(selectedOption.getAttribute('data-general-normal-fee')) || 0;
                }

                doctorNameInput.value = doctorName;
                doctorFeeInput.value = doctorFee.toFixed(2);
                totalAmountInput.value = doctorFee.toFixed(2);
            } else {
                doctorNameInput.value = '';
                doctorFeeInput.value = '0.00';
                totalAmountInput.value = '0.00';
            }
        }
        async function fetchAndSetTokenNumber(doctorCode) {
            if (doctorCode) {
                try {
                    // Correctly replace the placeholder with the actual doctor code
                    const url = generateTokenUrl.replace('REPLACE_ME', doctorCode);
                    const response = await fetchData(url);
                    tokenNumberInput.value = response.token_number;
                } catch (error) {
                    console.error('Error fetching token number:', error);
                    tokenNumberInput.value = 'N/A';
                }
            } else {
                tokenNumberInput.value = '';
            }
        }

        doctorCodeSelect.addEventListener('change', () => {
            calculateFees();
            const doctorCode = doctorCodeSelect.value;
            if (doctorCode) {
                fetchAndSetTokenNumber(doctorCode);
            } else {
                tokenNumberInput.value = '';
            }
        });

        // --- Form Actions ---
        outdoorPatientForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const appointmentData = {
                appointment_number: appointmentNumberInput.value,
                appointment_date: registrationDateInput.value,
                appointment_time: registrationTimeInput.value,
                mr_number: mrNumberInput.value,
                patient_name: patientNameInput.value,
                age: ageInput.value,
                gender: genderInput.value,
                doctor_code: doctorCodeSelect.value,
                doctor_name: doctorNameInput.value,
                doctor_fee: parseFloat(doctorFeeInput.value),
                referred_by: referredByInput.value,
                total_amount: parseFloat(totalAmountInput.value),
                token_number: tokenNumberInput.value,
            };

            try {
                const response = await sendData(outdoorPatientForm.action, 'POST', appointmentData);
                alert(response.message);
                console.log(response);
                window.location.href = response.redirect_url;
            } catch (error) {
                console.error('Error registering patient:', error);
                alert('An error occurred while registering the patient. Please check the console for details.');
            }
        });

        printSlipBtn.addEventListener('click', () => {
            populatePrintArea();
            printDiv('print_area');
        });

      function populatePrintArea() {
    document.getElementById('print_registration_date').textContent = registrationDateInput.value;
    document.getElementById('print_registration_time').textContent = registrationTimeInput.value;
    document.getElementById('print_patient_name').textContent = patientNameInput.value;
    document.getElementById('print_mr_number').textContent = mrNumberInput.value;
    document.getElementById('print_token_number').textContent = tokenNumberInput.value;
    document.getElementById('print_doctor_name').textContent = doctorNameInput.value;
    document.getElementById('print_total_amount').textContent = totalAmountInput.value;
}

        function printDiv(divId) {
            const printContents = document.getElementById(divId).innerHTML;
            const printWindow = window.open('', '_blank', 'width=300,height=400'); // Use small window size for thermal

            printWindow.document.open();
            printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title>OPD Slip</title>
            <style>
                /*
                * Print-specific styles for 80mm thermal paper.
                * A width of 80mm is approximately 2.95 inches, so we use a max-width and let the browser scale.
                */
                @page {
                    size: 80mm auto; /* 80mm wide, auto height */
                    margin: 0;
                }
                body {
                    margin: 0;
                    padding: 0;
                    font-family: sans-serif;
                    font-size: 10px; /* Small font size for thermal paper */
                    width: 80mm;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .text-md { font-size: 1.125em; } /* 18px */
                .text-xl { font-size: 1.25em; } /* 20px */
                .text-6xl { 
                    font-size: 4em; /* Very large font for the token number */
                    font-weight: 800; 
                    margin-top: 1rem;
                    margin-bottom: 1rem;
                }
                .font-bold { font-weight: bold; }
                .font-semibold { font-weight: 600; }
                .font-extrabold { font-weight: 800; }
                .mb-4 { margin-bottom: 1rem; }
                .mb-2 { margin-bottom: 0.5rem; }
                .mt-4 { margin-top: 1rem; }
                .my-6 { margin-top: 1.5rem; margin-bottom: 1.5rem; }
                .border-b { border-bottom: 1px solid #e5e7eb; }
                .pb-2 { padding-bottom: 0.5rem; }
            </style>
        </head>
        <body>
            ${printContents}
        </body>
        </html>
    `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }
        // --- Initial setup on page load ---
        calculateFees();
    });
</script>
@endsection