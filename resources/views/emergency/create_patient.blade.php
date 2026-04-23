@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Emergency Patient Registration</h2>
         <div class="flex space-x-4">
          <a href="{{ route('patients.register') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add New Patient
            </a>
        <a href="{{ route('emergency.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Emergency
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
        {{-- Success/Error Message Container --}}
        <div id="status-message" class="hidden mb-4"></div>

        <form id="emergency_patient_form" action="{{ route('emergency.patients.store') }}" method="POST">
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
                <div>
                    <label for="emergency_hours" class="block text-gray-700 text-sm font-bold mb-2">Emergency Hours:</label>
                    <input type="number" id="emergency_hours" name="emergency_hours" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 2" min="1" value="1" required>
                </div>
                <div>
                    <label for="patient_type" class="block text-gray-700 text-sm font-bold mb-2">Patient Type:</label>
                    <input type="text" id="patient_type" name="patient_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Normal / Welfare" readonly>
                    <input type="hidden" id="is_welfare" name="is_welfare" value="0">
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Consultant Fees</h3>
            <div class="mb-6">
                <label for="consultant_search" class="block text-gray-700 text-sm font-bold mb-2">Add Consultant:</label>
                <input type="text" id="consultant_search" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search by name or code">
            </div>

            <div id="consultants_list" class="space-y-4">
                {{-- Dynamic consultant fee entries will be added here --}}
            </div>

            <div class="mt-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Billing & Payments</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="first_hour_charges" class="block text-gray-700 text-sm font-bold mb-2">First Hour Charges (PKR):</label>
                        <input type="number" id="first_hour_charges" name="first_hour_charges" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ $emergencyCharges->first_hour_charges ?? 0 }}" readonly>
                    </div>
                    <div>
                        <label for="other_hours_charges" class="block text-gray-700 text-sm font-bold mb-2">Other Hours Charges (PKR):</label>
                        <input type="number" id="other_hours_charges" name="other_hours_charges" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ $emergencyCharges->other_hours_charges ?? 0 }}" readonly>
                    </div>
                    <div>
                        <label for="total_fee" class="block text-gray-700 text-sm font-bold mb-2">Total Fee (PKR):</label>
                        <input type="text" id="total_fee" name="total_fee" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 font-bold leading-tight focus:outline-none" readonly>
                    </div>
                    <div>
                        <label for="amount_paid" class="block text-gray-700 text-sm font-bold mb-2">Amount Paid (PKR):</label>
                        <input type="number" id="amount_paid" name="amount_paid" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" required>
                    </div>
                    <div>
                        <label for="amount_receivable" class="block text-gray-700 text-sm font-bold mb-2">Amount Receivable (PKR):</label>
                        <input type="text" id="amount_receivable" name="amount_receivable" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="submit" id="save_btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Save
                </button>
                <button type="button" id="print_btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save and Print
                </button>
            </div>
        </form>
    </div>

    {{-- Patient Search Modal --}}
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

    {{-- Consultant Search Modal --}}
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

    {{-- Receipt Print Area (hidden) --}}
    <div id="print_area" class="hidden p-8 bg-white border border-gray-300 rounded-lg shadow-xl" style="width: 210mm; min-height: 297mm; margin: 20mm auto; font-family: 'Inter', sans-serif;">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Hospital Name</h1>
            <h2 class="text-2xl font-semibold text-gray-700">Emergency Receipt</h2>
        </div>
        <div id="print_content"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Patient details fields
            const mrNumberInput = document.getElementById('mr_number');
            const patientNameInput = document.getElementById('patient_name');
            const ageInput = document.getElementById('age');
            const genderInput = document.getElementById('gender');
            const emergencyHoursInput = document.getElementById('emergency_hours');
            const firstHourChargesInput = document.getElementById('first_hour_charges');
            const otherHoursChargesInput = document.getElementById('other_hours_charges');
            const totalFeeInput = document.getElementById('total_fee');
            const amountPaidInput = document.getElementById('amount_paid');
            const amountReceivableInput = document.getElementById('amount_receivable');
            const consultantsListDiv = document.getElementById('consultants_list');
            const patientTypeInput = document.getElementById('patient_type');
            const isWelfareInput = document.getElementById('is_welfare');

            // Data storage
            let selectedConsultants = {};
            let patientData = {};

            // Productivity Modal Elements
            const patientSearchModal = document.getElementById('patient_search_modal');
            const patientSearchQueryInput = document.getElementById('patient_search_query');
            const patientSearchResults = document.getElementById('patient_search_results');
            const closePatientModalBtn = document.getElementById('close_patient_modal');
            const consultantSearchInput = document.getElementById('consultant_search');
            const consultantSearchModal = document.getElementById('consultant_search_modal');
            const consultantSearchQueryInput = document.getElementById('consultant_search_query');
            const consultantSearchResults = document.getElementById('consultant_search_results');
            const closeConsultantModalBtn = document.getElementById('close_consultant_modal');
            const printBtn = document.getElementById('print_btn');
            const saveBtn = document.getElementById('save_btn');
            const emergencyPatientForm = document.getElementById('emergency_patient_form');
            const printAreaDiv = document.getElementById('print_area');
            const statusMessageDiv = document.getElementById('status-message');

            // --- Helper Functions ---
            const debounce = (func, delay) => {
                let timeout;
                return function(...args) {
                    const context = this;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            };

            async function fetchData(url, method = 'GET', data = {}) {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                };
                if (method !== 'GET' && Object.keys(data).length > 0) {
                    options.body = JSON.stringify(data);
                }
                const response = await fetch(url, options);
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || `HTTP error! status: ${response.status}`);
                }
                return await response.json();
            }

            function showStatusMessage(message, isSuccess = true) {
                statusMessageDiv.textContent = message;
                statusMessageDiv.classList.remove('hidden', 'bg-green-100', 'bg-red-100', 'border-green-400', 'border-red-400', 'text-green-700', 'text-red-700');
                if (isSuccess) {
                    statusMessageDiv.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-700', 'px-4', 'py-3', 'rounded-xl', 'relative');
                } else {
                    statusMessageDiv.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700', 'px-4', 'py-3', 'rounded-xl', 'relative');
                }
            }

            function clearFormFields() {
                emergencyPatientForm.reset();
                selectedConsultants = {};
                patientData = {};
                consultantsListDiv.innerHTML = '';
                if (firstHourChargesInput) firstHourChargesInput.value = '0.00';
                if (otherHoursChargesInput) otherHoursChargesInput.value = '0.00';
                totalFeeInput.value = '0.00';
                amountReceivableInput.value = '0.00';
                amountPaidInput.value = '';
                statusMessageDiv.classList.add('hidden');
                calculateAllTotals();
            }

            // --- Patient Search Logic ---
            mrNumberInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.value) {
                        fetchPatientDetails(this.value);
                    }
                }
            });

            document.addEventListener('keydown', function(event) {
                if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
                    event.preventDefault();
                    patientSearchModal.classList.remove('hidden');
                    setTimeout(() => patientSearchQueryInput.focus(), 100);
                    fetchAndDisplayPatients('');
                }
            });
            
            patientSearchQueryInput.addEventListener('input', debounce(async function() {
                const query = this.value;
                await fetchAndDisplayPatients(query);
            }, 300));
            
            closePatientModalBtn.addEventListener('click', () => {
                patientSearchModal.classList.add('hidden');
            });
            
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
                                <tr class="search-result-row hover:bg-gray-100 cursor-pointer" data-mr-no="${patient.mr_number}">
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
                            mrNumberInput.value = mrNumber;
                            fetchPatientDetails(mrNumber);
                            patientSearchModal.classList.add('hidden');
                        });
                    });
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    patientSearchResults.innerHTML = '<div class="p-2 text-red-500">Failed to fetch patient data.</div>';
                }
            }

            async function fetchPatientDetails(mr_no) {
                try {
                    const url = `/api/get-by-mr-no/${mr_no}`;
                    const patient = await fetchData(url);
                    if (patient) {
                        patientData = patient;
                        if (patientNameInput) patientNameInput.value = patient.name;
                        if (ageInput) ageInput.value = patient.age;
                        if (genderInput) genderInput.value = patient.gender;
                        if (isWelfareInput) isWelfareInput.value = patient.is_welfare ? '1' : '0';
                        if (patientTypeInput) patientTypeInput.value = patient.is_welfare ? 'Welfare' : 'Normal';
                        
                        // Get emergency charges from a new API endpoint or a variable passed from the controller
                        // Assuming you pass it via a variable from the controller as in your original code
                        if (firstHourChargesInput) {
                           firstHourChargesInput.value = {{ $emergencyCharges->first_hour_charges ?? 0 }};
                        }
                        if (otherHoursChargesInput) {
                           otherHoursChargesInput.value = {{ $emergencyCharges->other_hours_charges ?? 0 }};
                        }

                        if (emergencyHoursInput) emergencyHoursInput.focus();
                        calculateAllTotals();
                    } else {
                        showStatusMessage('No patient found with this MR Number.', false);
                        clearFormFields();
                    }
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    showStatusMessage('Failed to fetch patient data. Please try again.', false);
                    clearFormFields();
                }
            }

            // --- Consultant Search Logic ---
            consultantSearchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    consultantSearchModal.classList.remove('hidden');
                    setTimeout(() => consultantSearchQueryInput.focus(), 100);
                    fetchAndDisplayConsultants('');
                }
            });
            
            consultantSearchQueryInput.addEventListener('input', debounce(async function() {
                const query = this.value;
                await fetchAndDisplayConsultants(query);
            }, 300));

            closeConsultantModalBtn.addEventListener('click', () => {
                consultantSearchModal.classList.add('hidden');
            });
            
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
                    tableHtml += '<thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee</th></tr></thead>';
                    tableHtml += '<tbody class="bg-white divide-y divide-gray-200">';
                    if (doctors.length > 0) {
                        doctors.forEach(doctor => {
                            const fee = patientData.is_welfare ? (doctor.welfare_emergency_fee || '0.00') : (doctor.general_emergency_fee || '0.00');
                            tableHtml += `
                                <tr class="search-result-row hover:bg-gray-100 cursor-pointer" data-doctor='${JSON.stringify(doctor)}' tabindex="0">
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.code}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.name}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">${doctor.department.name || 'N/A'}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">Rs ${fee}</td>
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
                            const doctor = JSON.parse(row.getAttribute('data-doctor'));
                            const fee = patientData.is_welfare ? (doctor.welfare_emergency_fee || 0) : (doctor.general_emergency_fee || 0);
                            addConsultantToList(doctor.id, doctor.name, fee);
                            consultantSearchModal.classList.add('hidden');
                            consultantSearchInput.value = '';
                        });
                    });
                } catch (error) {
                    console.error('Error searching for consultants:', error);
                    consultantSearchResults.innerHTML = '<div class="p-3 text-red-500">Failed to load search results.</div>';
                }
            }

            function addConsultantToList(id, name, fee) {
                if (selectedConsultants[id]) {
                    showStatusMessage('This consultant has already been added.', false);
                    return;
                }
                
                const consultantItem = document.createElement('div');
                consultantItem.classList.add('flex', 'items-center', 'space-x-4', 'bg-gray-100', 'p-4', 'rounded-lg', 'shadow-sm', 'consultant-item');
                consultantItem.dataset.id = id;
                consultantItem.innerHTML = `
                    <div class="flex-grow">
                        <span class="font-semibold text-gray-800">${name}</span>
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Fee (Rs):</label>
                        <input type="number" class="w-24 text-right px-2 py-1 border rounded-md bg-gray-50 fee-input" value="${parseFloat(fee).toFixed(2)}" readonly>
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Qty:</label>
                        <input type="number" value="1" min="1" class="qty-input w-16 text-right px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${id}">
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Discount (Rs):</label>
                        <input type="number" value="0" min="0" class="discount-input w-24 text-right px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${id}">
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Total (Rs):</label>
                        <span class="font-bold text-gray-800 total-amount-cell w-24 text-right">${parseFloat(fee).toFixed(2)}</span>
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800 remove-consultant-btn" data-id="${id}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                `;
                consultantsListDiv.appendChild(consultantItem);
                
                selectedConsultants[id] = {
                    id: id,
                    name: name,
                    fee: parseFloat(fee),
                    qty: 1,
                    discount: 0,
                    total: parseFloat(fee)
                };
                
                calculateAllTotals();
                
                const qtyInput = consultantItem.querySelector('.qty-input');
                const discountInput = consultantItem.querySelector('.discount-input');
                
                const handleUpdate = () => {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const discount = parseFloat(discountInput.value) || 0;
                    const fee = selectedConsultants[id].fee;
                    
                    const total = (fee * qty) - discount;
                    consultantItem.querySelector('.total-amount-cell').textContent = total.toFixed(2);
                    
                    selectedConsultants[id].qty = qty;
                    selectedConsultants[id].discount = discount;
                    selectedConsultants[id].total = total;
                    
                    calculateAllTotals();
                };

                qtyInput.addEventListener('input', handleUpdate);
                discountInput.addEventListener('input', handleUpdate);
                
                qtyInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        discountInput.focus();
                    }
                });
                
                discountInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const nextItem = consultantItem.nextElementSibling;
                        if (nextItem && nextItem.querySelector('.qty-input')) {
                            nextItem.querySelector('.qty-input').focus();
                        } else {
                            consultantSearchInput.focus();
                        }
                    } else if ((e.ctrlKey || e.metaKey) && e.key === 'b') { 
                         e.preventDefault();
                         amountPaidInput.focus();
                     }
                });
                
                // Set initial focus to the new quantity field
                qtyInput.focus();
            }

            consultantsListDiv.addEventListener('click', function(event) {
                if (event.target.closest('.remove-consultant-btn')) {
                    const btn = event.target.closest('.remove-consultant-btn');
                    const id = btn.dataset.id;
                    delete selectedConsultants[id];
                    btn.closest('.consultant-item').remove();
                    calculateAllTotals();
                }
            });

            // --- Billing Calculations ---
            function calculateAllTotals() {
                let totalConsultantFees = 0;
                for (const id in selectedConsultants) {
                    totalConsultantFees += selectedConsultants[id].total;
                }
                
                const hours = parseFloat(emergencyHoursInput.value) || 0;
                const firstHourFee = parseFloat(firstHourChargesInput.value) || 0;
                const otherHoursFee = parseFloat(otherHoursChargesInput.value) || 0;
                
                let totalHoursCharges = 0;
                if (hours > 0) {
                    totalHoursCharges += firstHourFee;
                    if (hours > 1) {
                        totalHoursCharges += (hours - 1) * otherHoursFee;
                    }
                }
                
                const totalBill = totalConsultantFees + totalHoursCharges;
                totalFeeInput.value = totalBill.toFixed(2);

                const amountPaid = parseFloat(amountPaidInput.value) || 0;
                const amountReceivable = totalBill - amountPaid;
                amountReceivableInput.value = amountReceivable.toFixed(2);
            }
            
            emergencyHoursInput.addEventListener('input', calculateAllTotals);
            firstHourChargesInput.addEventListener('input', calculateAllTotals);
            otherHoursChargesInput.addEventListener('input', calculateAllTotals);
            amountPaidInput.addEventListener('input', calculateAllTotals);

            // --- Form Submission ---
            async function saveForm(print = false) {
                if (!mrNumberInput.value || Object.keys(selectedConsultants).length === 0 || !amountPaidInput.value) {
                    showStatusMessage('Please fill out all required fields: MR No, at least one Consultant, and Amount Paid.', false);
                    return;
                }

                const consultantsData = JSON.stringify(Object.values(selectedConsultants));
                
                const formData = {
                    mr_number: mrNumberInput.value,
                    patient_name: patientNameInput.value,
                    age: ageInput.value,
                    gender: genderInput.value,
                    emergency_hours: emergencyHoursInput.value,
                    first_hour_charges: firstHourChargesInput.value,
                    other_hours_charges: otherHoursChargesInput.value,
                    total_fee: totalFeeInput.value,
                    amount_paid: amountPaidInput.value,
                    amount_receivable: amountReceivableInput.value,
                    consultants: consultantsData,
                };

                try {
                    const response = await fetchData(emergencyPatientForm.action, 'POST', formData);
                    showStatusMessage('Emergency Patient registered successfully!', true);
                    
                    if (print) {
                        printReceipt(formData);
                    }
                    
                    clearFormFields();
                } catch (error) {
                    showStatusMessage('An error occurred during save. Please check the console.', false);
                    console.error('Error during form submission:', error);
                }
            }

            saveBtn.addEventListener('click', async function(event) {
                event.preventDefault();
                await saveForm(false);
            });

            printBtn.addEventListener('click', async function(event) {
                event.preventDefault();
                await saveForm(true);
            });
            
            // --- Print Receipt Logic ---
            function printReceipt(data) {
                let consultantsHtml = '';
                if (selectedConsultants) {
                     for (const id in selectedConsultants) {
                        const c = selectedConsultants[id];
                        consultantsHtml += `
                            <p class="mb-1 text-lg">
                                <strong>${c.name}:</strong> 
                                Rs ${c.fee.toFixed(2)} x ${c.qty} - Rs ${c.discount.toFixed(2)} = 
                                <span class="font-bold">Rs ${c.total.toFixed(2)}</span>
                            </p>
                        `;
                    }
                }

                const html = `
                    <div class="mb-6 border-b pb-4">
                        <p class="text-lg mb-2"><strong>Patient Name:</strong> ${data.patient_name}</p>
                        <p class="text-lg mb-2"><strong>MR No:</strong> ${data.mr_number}</p>
                        <p class="text-lg mb-2"><strong>Emergency Hours:</strong> ${data.emergency_hours}</p>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Charges Breakdown</h3>
                        <p class="mb-1 text-lg"><strong>First Hour Charges:</strong> <span class="float-right">Rs ${parseFloat(data.first_hour_charges).toFixed(2)}</span></p>
                        ${data.emergency_hours > 1 ? `<p class="mb-1 text-lg"><strong>Additional Hours (${data.emergency_hours - 1}):</strong> <span class="float-right">Rs ${((data.emergency_hours - 1) * parseFloat(data.other_hours_charges)).toFixed(2)}</span></p>` : ''}
                        
                        <h3 class="text-xl font-semibold text-gray-800 mb-4 mt-6">Consultant Details</h3>
                        ${consultantsHtml || '<p class="text-lg text-gray-600">No consultants added.</p>'}
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-xl font-bold text-gray-800 mt-4">Total Bill: <span class="float-right">Rs ${parseFloat(data.total_fee).toFixed(2)}</span></p>
                        <p class="text-lg font-semibold text-gray-700 mt-2">Amount Paid: <span class="float-right">Rs ${parseFloat(data.amount_paid).toFixed(2)}</span></p>
                        <p class="text-lg font-semibold text-gray-700 mt-2">Amount Receivable: <span class="float-right">Rs ${parseFloat(data.amount_receivable).toFixed(2)}</span></p>
                    </div>
                `;

                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <title>Emergency Receipt</title>
                        <style>
                            body { font-family: 'Inter', sans-serif; margin: 0; padding: 20mm; font-size: 14px; }
                            h1, h2, h3 { color: #1f2937; margin: 0; }
                            p { margin: 0; }
                            .text-center { text-align: center; }
                            .float-right { float: right; }
                            .mb-6 { margin-bottom: 1.5rem; }
                            .mb-4 { margin-bottom: 1rem; }
                            .mb-2 { margin-bottom: 0.5rem; }
                            .mt-4 { margin-top: 1rem; }
                            .mt-2 { margin-top: 0.5rem; }
                            .border-b { border-bottom: 1px solid #e5e7eb; }
                            .border-t { border-top: 1px solid #e5e7eb; }
                            .pt-4 { padding-top: 1rem; }
                            .font-bold { font-weight: 700; }
                            .font-semibold { font-weight: 600; }
                            .text-sm { font-size: 0.875rem; }
                            .text-lg { font-size: 1.125rem; }
                            .text-xl { font-size: 1.25rem; }
                            .text-2xl { font-size: 1.5rem; }
                            .text-3xl { font-size: 1.875rem; }
                            .text-4xl { font-size: 2.25rem; }
                            @media print {
                                body { font-size: 12px; }
                                h1 { font-size: 2.5rem; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="p-8 bg-white border border-gray-300 rounded-lg shadow-xl" style="width: 210mm; min-height: 297mm; margin: 20mm auto;">
                            <div class="text-center mb-8">
                                <h1 class="text-4xl font-bold text-gray-800 mb-2">Hospital Name</h1>
                                <h2 class="text-2xl font-semibold text-gray-700">Emergency Receipt</h2>
                            </div>
                            ${html}
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }

            // --- Initial setup on page load ---
            calculateAllTotals();
        });
    </script>
@endsection