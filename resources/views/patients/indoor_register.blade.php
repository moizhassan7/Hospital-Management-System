@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Indoor Patient Registration</h2>
         <div class="flex space-x-4">
          <a href="{{ route('patients.register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Patient
            </a>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
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
        <form id="indoor_patient_form" action="{{ route('patients.store_indoor') }}" method="POST">
            @csrf
            
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No (Medical Record No):</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001">
                </div>
                <div>
                    <label for="registration_date" class="block text-gray-700 text-sm font-bold mb-2">Registration Date:</label>
                    <input type="date" id="registration_date" name="registration_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="number" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-bold mb-2">Gender:</label>
                    <input type="text" id="gender" name="gender" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="patient_type" class="block text-gray-700 text-sm font-bold mb-2">Patient Type:</label>
                    <input type="text" id="patient_type" name="patient_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
            </div>
            
            <input type="hidden" id="is_welfare" name="is_welfare" value="0">

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Admission & Fee Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="file_no" class="block text-gray-700 text-sm font-bold mb-2">File No:</label>
                    <input type="text" id="file_no" name="file_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>
                <div>
                    <label for="slip_no" class="block text-gray-700 text-sm font-bold mb-2">Slip No:</label>
                    <input type="text" id="slip_no" name="slip_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>

                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Admission Type:</label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="admission_type" id="ward_radio" value="ward" class="form-radio h-5 w-5 text-blue-600 focus:ring-blue-500" checked>
                            <span class="ml-2 text-gray-700">Ward</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="admission_type" id="room_radio" value="room" class="form-radio h-5 w-5 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Room</span>
                        </label>
                    </div>
                </div>

                <div id="ward_section">
                    <label for="ward_number" class="block text-gray-700 text-sm font-bold mb-2">Ward Number:</label>
                    <select id="ward_number" name="ward_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Ward</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->id }}" data-beds="{{ $ward->number_of_beds }}">{{ $ward->name }} ({{ $ward->number_of_beds }} beds)</option>
                        @endforeach
                    </select>
                </div>

                <div id="room_section" class="hidden">
                    <label for="room_id" class="block text-gray-700 text-sm font-bold mb-2">Room Number:</label>
                    <select id="room_id" name="room_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Room</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label id="bed_info_label" for="bed_info" class="block text-gray-700 text-sm font-bold mb-2">Number of Beds:</label>
                    <input type="text" id="bed_info" name="bed_info" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                    <input type="hidden" name="bed_no" id="bed_no">
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
                    <label for="consultant_input" class="block text-gray-700 text-sm font-bold mb-2">Consultant:</label>
                    <input type="text" id="consultant_input" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search or press Enter">
                    <input type="hidden" name="consultant_id" id="consultant_id">
                </div>
                 <div>
                    <label for="consultant_fee" class="block text-gray-700 text-sm font-bold mb-2">Consultant Fee ($):</label>
                    <input type="number" id="consultant_fee" name="consultant_fee" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-calculated" readonly>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3 flex items-center mt-6 space-x-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_operation" id="is_operation" value="1" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 text-sm font-bold">Is Operation?</span>
                    </label>
                    <div id="operation_date_field" class="hidden">
                        <label for="operation_date" class="block text-gray-700 text-sm font-bold mb-2">Operation Date (Optional):</label>
                        <input type="date" id="operation_date" name="operation_date" class="shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get Elements
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const ageInput = document.getElementById('age');
            const genderInput = document.getElementById('gender');
            const patientTypeInput = document.getElementById('patient_type');
            const isWelfareInput = document.getElementById('is_welfare');
            const fileNoInput = document.getElementById('file_no');
            const slipNoInput = document.getElementById('slip_no');
            const wardRadio = document.getElementById('ward_radio');
            const roomRadio = document.getElementById('room_radio');
            const wardSection = document.getElementById('ward_section');
            const roomSection = document.getElementById('room_section');
            const wardNumberSelect = document.getElementById('ward_number');
            const roomNumberSelect = document.getElementById('room_id');
            const bedInfoInput = document.getElementById('bed_info');
            const bedNoLabel = document.getElementById('bed_info_label');
            const isOperationCheckbox = document.getElementById('is_operation');
            const operationDateField = document.getElementById('operation_date_field');
            const admissionFeeInput = document.getElementById('admission_fee');
            const advanceFeeInput = document.getElementById('advance_fee');
            const totalAmountInput = document.getElementById('total_amount');
            const consultantInput = document.getElementById('consultant_input');
            const consultantIdInput = document.getElementById('consultant_id');
            const consultantFeeInput = document.getElementById('consultant_fee');

            // Patient Search Modal Elements
            const patientSearchModal = document.getElementById('patient_search_modal');
            const patientSearchQueryInput = document.getElementById('patient_search_query');
            const patientSearchResults = document.getElementById('patient_search_results');
            const closePatientModalBtn = document.getElementById('close_patient_modal');

            // Consultant Search Modal Elements
            const consultantSearchModal = document.getElementById('consultant_search_modal');
            const consultantSearchQueryInput = document.getElementById('consultant_search_query');
            const consultantSearchResults = document.getElementById('consultant_search_results');
            const closeConsultantModalBtn = document.getElementById('close_consultant_modal');
            
            // Debounce function to prevent excessive API calls
            function debounce(func, delay) {
                let timeout;
                return function(...args) {
                    const context = this;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            }

            // --- Auto Number Generation ---
            function generateFileAndSlipNumber(gender) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const genderCode = (gender && gender.toLowerCase() === 'male') ? '1' : '0';
                
                const formattedNextNumber = '{{ $formattedNextNumber }}';

                const fileNumber = `${year}${month}${genderCode}${formattedNextNumber}`;
                const slipNumber = `${genderCode}${year}${month}${formattedNextNumber}`;

                fileNoInput.value = fileNumber;
                slipNoInput.value = slipNumber;
            }

            // --- AJAX Helpers ---
            async function fetchData(url) {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            }

            // --- Patient and Consultant Search Logic ---

            // Global shortcut listener
            document.addEventListener('keydown', function(event) {
                if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
                    event.preventDefault();
                    patientSearchModal.classList.remove('hidden');
                    // Use setTimeout to ensure focus is applied after the modal has been rendered
                    setTimeout(() => patientSearchQueryInput.focus(), 100);
                    fetchAndDisplayPatients('');
                }
            });

            // Patient search modal logic
            patientSearchQueryInput.addEventListener('input', debounce(async function() {
                const query = this.value;
                await fetchAndDisplayPatients(query);
            }, 300));
            
            // Keyboard navigation for patient search results
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
                const url = `{{ route('patients.api_search_patient') }}?query=${query}`;
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

                    patientSearchResults.querySelectorAll('.search-result-row').forEach(row => {
                        row.addEventListener('click', () => {
                            const mrNumber = row.getAttribute('data-mr-no');
                            mrNoInput.value = mrNumber;
                            populatePatientFields(patients.find(p => p.mr_number === mrNumber));
                            patientSearchModal.classList.add('hidden');
                        });
                    });

                    // Do not auto-focus the first row. The user should start typing.
                    // The focus will be handled by setTimeout in the keydown listener.
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    patientSearchResults.innerHTML = '<div class="p-2 text-red-500">Failed to fetch patient data.</div>';
                }
            }

            closePatientModalBtn.addEventListener('click', () => {
                patientSearchModal.classList.add('hidden');
            });

            mrNoInput.addEventListener('keydown', async function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.value === '') {
                        patientSearchModal.classList.remove('hidden');
                        setTimeout(() => patientSearchQueryInput.focus(), 100);
                        await fetchAndDisplayPatients('');
                    } else {
                        await fetchAndPopulatePatientDetails(this.value);
                    }
                }
            });

            async function fetchAndPopulatePatientDetails(mrNo) {
                try {
                    const response = await fetch(`/patients/api/get-by-mr-no/${mrNo}`);
                    const patient = await response.json();
                    if (patient) {
                        populatePatientFields(patient);
                    } else {
                        alert('No patient found with this MR No.');
                        clearPatientFields();
                    }
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    alert('Failed to fetch patient data. Please try again.');
                }
            }

            function populatePatientFields(patient) {
                patientNameInput.value = patient.name;
                ageInput.value = patient.age;
                genderInput.value = patient.gender;
                patientTypeInput.value = patient.is_welfare ? 'Welfare' : 'Normal';
                isWelfareInput.value = patient.is_welfare ? '1' : '0';
                generateFileAndSlipNumber(patient.gender);
                updateDoctorFee(); // Update fee based on patient type
            }

            function clearPatientFields() {
                patientNameInput.value = '';
                ageInput.value = '';
                genderInput.value = '';
                patientTypeInput.value = '';
                isWelfareInput.value = '0';
                fileNoInput.value = '';
                slipNoInput.value = '';
                updateDoctorFee(); // Recalculate fee to default
            }

            // Consultant search modal logic
            consultantInput.addEventListener('keydown', async function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    consultantSearchModal.classList.remove('hidden');
                    setTimeout(() => consultantSearchQueryInput.focus(), 100);
                    await fetchAndDisplayConsultants('');
                }
            });

            consultantSearchQueryInput.addEventListener('input', debounce(async function() {
                const query = this.value;
                await fetchAndDisplayConsultants(query);
            }, 300));
            
            // Keyboard navigation for consultant search results
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
                const url = `{{ route('patients.api_search_doctor') }}?query=${query}`;
                try {
                    const doctors = await fetchData(url);
                    let tableHtml = '<table class="min-w-full divide-y divide-gray-200">';
                    tableHtml += '<thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee</th></tr></thead>';
                    tableHtml += '<tbody class="bg-white divide-y divide-gray-200">';

                    if (doctors.length > 0) {
                        doctors.forEach(doctor => {
                            // Determine which fee to display
                            const isWelfare = isWelfareInput.value === '1';
                            const fee = isWelfare ? (doctor.welfare_normal_fee || '0.00') : (doctor.general_normal_fee || '0.00');
                            tableHtml += `
                                <tr class="search-result-row hover:bg-gray-100 cursor-pointer" data-doctor-id="${doctor.id}" data-doctor-fee="${fee}" tabindex="0">
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.code}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.name}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.department.name || 'N/A'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">$${fee}</td>
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
                            const doctorId = row.getAttribute('data-doctor-id');
                            const doctorName = row.querySelector('td:nth-child(2)').textContent;
                            const doctorFee = row.getAttribute('data-doctor-fee');
                            
                            consultantInput.value = doctorName;
                            consultantIdInput.value = doctorId;
                            consultantFeeInput.value = parseFloat(doctorFee).toFixed(2);
                            calculateTotal();

                            consultantSearchModal.classList.add('hidden');
                        });
                    });
                    
                    // Do not auto-focus the first row. The user should start typing.
                    // The focus will be handled by setTimeout in the keydown listener.
                } catch (error) {
                    console.error('Error searching for consultants:', error);
                    consultantSearchResults.innerHTML = '<div class="p-3 text-red-500">Failed to load search results.</div>';
                }
            }

            closeConsultantModalBtn.addEventListener('click', () => {
                consultantSearchModal.classList.add('hidden');
            });

            // --- Admission Type & Bed Logic ---
            wardRadio.addEventListener('change', () => {
                wardSection.classList.remove('hidden');
                roomSection.classList.add('hidden');
                bedNoLabel.textContent = 'Number of Beds:';
                bedInfoInput.value = ''; 
                bedInfoInput.readOnly = true;
            });

            roomRadio.addEventListener('change', () => {
                roomSection.classList.remove('hidden');
                wardSection.classList.add('hidden');
                bedNoLabel.textContent = 'Bed No:';
                bedInfoInput.value = ''; 
                bedInfoInput.readOnly = false;
            });
            
            wardNumberSelect.addEventListener('change', () => {
                const selectedOption = wardNumberSelect.options[wardNumberSelect.selectedIndex];
                const beds = selectedOption.getAttribute('data-beds');
                if (beds) {
                    bedInfoInput.value = beds;
                    bedInfoInput.readOnly = true;
                } else {
                    bedInfoInput.value = '';
                    bedInfoInput.readOnly = true;
                }
            });
            
            roomNumberSelect.addEventListener('change', () => {
                const selectedRoomId = roomNumberSelect.value;
                if (selectedRoomId) {
                    bedInfoInput.value = 'Room ' + selectedRoomId;
                } else {
                    bedInfoInput.value = '';
                }
                bedInfoInput.readOnly = true;
            });
            
            // To handle the hidden bed_no field that will be submitted
            const indoorForm = document.getElementById('indoor_patient_form');
            indoorForm.addEventListener('submit', function(event) {
                if (wardRadio.checked) {
                    const selectedWard = wardNumberSelect.options[wardNumberSelect.selectedIndex];
                    const bedNumber = selectedWard.getAttribute('data-beds');
                    document.getElementById('bed_no').value = bedNumber;
                } else {
                    const bedNumber = bedInfoInput.value;
                    document.getElementById('bed_no').value = bedNumber;
                }
            });

            // --- Operation Date & Fee Calculation ---
            isOperationCheckbox.addEventListener('change', () => {
                if (isOperationCheckbox.checked) {
                    operationDateField.classList.remove('hidden');
                } else {
                    operationDateField.classList.add('hidden');
                    document.getElementById('operation_date').value = '';
                }
            });

            function calculateTotal() {
                const admission = parseFloat(admissionFeeInput.value) || 0;
                const advance = parseFloat(advanceFeeInput.value) || 0;
                const consultantFee = parseFloat(consultantFeeInput.value) || 0;
                const total = admission + consultantFee - advance;
                totalAmountInput.value = total.toFixed(2);
            }

            function updateDoctorFee() {
                const doctorId = consultantIdInput.value;
                const isWelfare = isWelfareInput.value === '1';

                if (doctorId) {
                    const url = `{{ route('patients.api_get_doctor_fee') }}?consultant_id=${doctorId}&is_welfare=${isWelfare}`;
                    fetchData(url)
                        .then(data => {
                            consultantFeeInput.value = parseFloat(data.fee).toFixed(2);
                            calculateTotal();
                        })
                        .catch(error => {
                            console.error('Error fetching doctor fee:', error);
                            consultantFeeInput.value = '0.00';
                            calculateTotal();
                        });
                } else {
                    consultantFeeInput.value = '0.00';
                    calculateTotal();
                }
            }

            consultantIdInput.addEventListener('change', updateDoctorFee);
            admissionFeeInput.addEventListener('input', calculateTotal);
            advanceFeeInput.addEventListener('input', calculateTotal);
            consultantFeeInput.addEventListener('input', calculateTotal); // Recalculate if fee is manually changed

            // Initial setup
            calculateTotal();
            wardRadio.checked = true;
            wardSection.classList.remove('hidden');
            roomSection.classList.add('hidden');
            bedNoLabel.textContent = 'Number of Beds:';
            bedInfoInput.readOnly = true;
        });
    </script>
@endsection