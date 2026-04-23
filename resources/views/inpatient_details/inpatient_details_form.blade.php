@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Manage Inpatient Details</h2>
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
        {{-- Success/Error Message Container --}}
        <div id="status-message" class="hidden mb-4"></div>

        <form id="inpatient_form" action="{{ route('inpatient.store') }}" method="POST">
            @csrf

            {{-- Patient Identification Section --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No (Medical Record No):</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001" required>
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="admission_date" class="block text-gray-700 text-sm font-bold mb-2">Admission Date:</label>
                    <input type="text" id="admission_date" name="admission_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="number" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>
                <div>
                    <label for="patient_type" class="block text-gray-700 text-sm font-bold mb-2">Patient Type:</label>
                    <input type="text" id="patient_type" name="patient_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Normal / Welfare" readonly>
                    <input type="hidden" id="is_welfare" name="is_welfare" value="0">
                </div>
                <div>
                    <label for="room_info" class="block text-gray-700 text-sm font-bold mb-2">Room/Ward:</label>
                    <input type="text" id="room_info" name="room_info" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
            </div>

            {{-- Consultant Visits Section --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Consultant Visits</h3>
            <div class="mb-6">
                <label for="consultant_search" class="block text-gray-700 text-sm font-bold mb-2">Add Consultant:</label>
                <input type="text" id="consultant_search" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search by name or code">
            </div>

            <div id="consultants_list" class="space-y-4">
                {{-- Dynamic consultant visit entries will be added here --}}
            </div>
            
            <div class="mt-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Payment Details</h3>
                <div id="payments_list" class="space-y-2 mb-4">
                    {{-- Dynamic payment history will be added here --}}
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="payment_amount" class="block text-gray-700 text-sm font-bold mb-2">Add New Payment:</label>
                        <input type="number" id="payment_amount" name="payment_amount" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="0" placeholder="e.g., 5000">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="submit" id="save_btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save Changes
                </button>
                <button type="button" id="print_btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Print Receipt
                </button>
            </div>
        </form>
    </div>

    {{-- Search Modals --}}
    <div id="patient_search_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <h3 class="text-xl font-semibold mb-4">Search Indoor Patient</h3>
            <input type="text" id="patient_search_query" class="w-full px-3 py-2 border rounded-md" placeholder="Enter MR No, Name, or Mobile No.">
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

    {{-- Receipt Print Area (hidden) --}}
    <div id="print_area" class="hidden p-8 bg-white border border-gray-300 rounded-lg shadow-xl" style="width: 210mm; min-height: 297mm; margin: 20mm auto; font-family: 'Inter', sans-serif;">
        <div id="print_content"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Patient details fields
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const admissionDateInput = document.getElementById('admission_date');
            const ageInput = document.getElementById('age');
            const patientTypeInput = document.getElementById('patient_type');
            const isWelfareInput = document.getElementById('is_welfare');
            const roomInfoInput = document.getElementById('room_info');
            
            // Consultant fields
            const consultantSearchInput = document.getElementById('consultant_search');
            const consultantsListDiv = document.getElementById('consultants_list');

            // Payment fields
            const paymentAmountInput = document.getElementById('payment_amount');
            const paymentsListDiv = document.getElementById('payments_list');

            // Data storage for form submission
            let consultantVisits = {};
            let paymentHistory = [];
            let patientData = {};
            let indoorPatientData = {};

            // Productivity Modal Elements
            const patientSearchModal = document.getElementById('patient_search_modal');
            const patientSearchQueryInput = document.getElementById('patient_search_query');
            const patientSearchResults = document.getElementById('patient_search_results');
            const closePatientModalBtn = document.getElementById('close_patient_modal');

            const consultantSearchModal = document.getElementById('consultant_search_modal');
            const consultantSearchQueryInput = document.getElementById('consultant_search_query');
            const consultantSearchResults = document.getElementById('consultant_search_results');
            const closeConsultantModalBtn = document.getElementById('close_consultant_modal');
            
            const printBtn = document.getElementById('print_btn');
            const saveBtn = document.getElementById('save_btn');
            const inpatientForm = document.getElementById('inpatient_form');

            // Helper function for debounce
            const debounce = (func, delay) => {
                let timeout;
                return function(...args) {
                    const context = this;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            };
            
            // --- API Helper ---
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
                    // Try to parse the error message if it's JSON
                    try {
                        const error = await response.json();
                        throw new Error(error.message || `HTTP error! status: ${response.status}`);
                    } catch (e) {
                        // If the response is not JSON, it might be an HTML error page
                        const errorText = await response.text();
                        throw new Error(`HTTP error! status: ${response.status}. Response: ${errorText}`);
                    }
                }
                return await response.json();
            }

            function showStatusMessage(message, isSuccess = true) {
                const statusMessageDiv = document.getElementById('status-message');
                statusMessageDiv.textContent = message;
                statusMessageDiv.classList.remove('hidden', 'bg-green-100', 'bg-red-100', 'border-green-400', 'border-red-400', 'text-green-700', 'text-red-700');
                if (isSuccess) {
                    statusMessageDiv.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-700', 'px-4', 'py-3', 'rounded-xl', 'relative');
                } else {
                    statusMessageDiv.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700', 'px-4', 'py-3', 'rounded-xl', 'relative');
                }
            }

            // --- Patient and Indoor Data Lookup ---
            mrNoInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    const mrNo = this.value;
                    if (mrNo) {
                        fetchInpatientDetailsData(mrNo);
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
                const url = `{{ route('patients.api_search_patient') }}?query=${query}`;
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
                            mrNoInput.value = mrNumber;
                            fetchInpatientDetailsData(mrNumber);
                            patientSearchModal.classList.add('hidden');
                        });
                    });
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    patientSearchResults.innerHTML = '<div class="p-2 text-red-500">Failed to fetch patient data.</div>';
                }
            }

            async function fetchInpatientDetailsData(mrNo) {
                try {
                    const url = `/api/get-details-by-mr-no/${mrNo}`;
                    const data = await fetchData(url);
                    
                    if (data.found) {
                        patientData = data.patient;
                        indoorPatientData = data.indoor_patient;
                        consultantVisits = data.inpatient_detail?.consultant_visits || {};
                        paymentHistory = data.inpatient_detail?.payment_history || [];

                        patientNameInput.value = patientData.name;
                        ageInput.value = patientData.age;
                        patientTypeInput.value = patientData.is_welfare ? 'Welfare' : 'Normal';
                        isWelfareInput.value = patientData.is_welfare ? '1' : '0';
                        admissionDateInput.value = indoorPatientData.registration_date;
                        roomInfoInput.value = indoorPatientData.room_id ? `Room ${indoorPatientData.room_id}` : `Ward ${indoorPatientData.ward_id}`;
                        
                        renderConsultantsList();
                        renderPaymentHistory();

                        showStatusMessage('Patient data loaded successfully.', true);
                    } else {
                        showStatusMessage('No inpatient record found for this MR Number.', false);
                        clearFormFields();
                    }
                } catch (error) {
                    console.error('Error fetching inpatient data:', error);
                    showStatusMessage('Failed to fetch inpatient data. Please try again.', false);
                    clearFormFields();
                }
            }

            function clearFormFields() {
                mrNoInput.value = '';
                patientNameInput.value = '';
                ageInput.value = '';
                patientTypeInput.value = '';
                isWelfareInput.value = '0';
                admissionDateInput.value = '';
                roomInfoInput.value = '';
                consultantSearchInput.value = '';
                paymentAmountInput.value = '';
                consultantsListDiv.innerHTML = '';
                paymentsListDiv.innerHTML = '';
                consultantVisits = {};
                paymentHistory = [];
                patientData = {};
                indoorPatientData = {};
                showStatusMessage('', true);
            }
            
            // --- Consultant Search Logic ---
            consultantSearchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.value) {
                        consultantSearchModal.classList.remove('hidden');
                        setTimeout(() => consultantSearchQueryInput.focus(), 100);
                        fetchAndDisplayConsultants(this.value);
                    }
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
                const url = `{{ route('patients.api_search_doctor') }}?query=${query}`;
                try {
                    const doctors = await fetchData(url);
                    let tableHtml = '<table class="min-w-full divide-y divide-gray-200">';
                    tableHtml += '<thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee</th></tr></thead>';
                    tableHtml += '<tbody class="bg-white divide-y divide-gray-200">';

                    if (doctors.length > 0) {
                        doctors.forEach(doctor => {
                            const fee = patientData.is_welfare ? (doctor.welfare_normal_fee || '0.00') : (doctor.general_normal_fee || '0.00');
                            tableHtml += `
                                <tr class="search-result-row hover:bg-gray-100 cursor-pointer" data-doctor='${JSON.stringify(doctor)}' tabindex="0">
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
                            const doctor = JSON.parse(row.getAttribute('data-doctor'));
                            const fee = patientData.is_welfare ? (doctor.welfare_normal_fee || 0) : (doctor.general_normal_fee || 0);
                            addConsultantVisit(doctor.id, doctor.name, fee);
                            consultantSearchModal.classList.add('hidden');
                            consultantSearchInput.value = '';
                        });
                    });
                } catch (error) {
                    console.error('Error searching for consultants:', error);
                    consultantSearchResults.innerHTML = '<div class="p-3 text-red-500">Failed to load search results.</div>';
                }
            }

            function renderConsultantsList() {
                consultantsListDiv.innerHTML = '';
                for (const id in consultantVisits) {
                    const visit = consultantVisits[id];
                    const consultantItem = document.createElement('div');
                    consultantItem.classList.add('flex', 'items-center', 'space-x-4', 'bg-gray-100', 'p-4', 'rounded-lg', 'shadow-sm', 'consultant-item');
                    consultantItem.dataset.id = id;
                    consultantItem.innerHTML = `
                        <div class="flex-grow">
                            <span class="font-semibold text-gray-800">${visit.name}</span>
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Fee ($):</label>
                            <input type="number" class="w-24 text-right px-2 py-1 border rounded-md bg-gray-50 fee-input" value="${parseFloat(visit.fee).toFixed(2)}" readonly>
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Qty:</label>
                            <input type="number" value="${visit.qty}" min="1" class="qty-input w-16 text-right px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${id}">
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Discount ($):</label>
                            <input type="number" value="${visit.discount}" min="0" class="discount-input w-24 text-right px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${id}">
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Total ($):</label>
                            <span class="font-bold text-gray-800 total-amount-cell w-24 text-right">${parseFloat(visit.total).toFixed(2)}</span>
                        </div>
                        <button type="button" class="text-red-600 hover:text-red-800 remove-consultant-btn" data-id="${id}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    `;
                    consultantsListDiv.appendChild(consultantItem);

                    // Add event listeners
                    const qtyInput = consultantItem.querySelector('.qty-input');
                    const discountInput = consultantItem.querySelector('.discount-input');
                    qtyInput.addEventListener('input', () => updateConsultantTotal(id, consultantItem));
                    discountInput.addEventListener('input', () => updateConsultantTotal(id, consultantItem));

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
                            paymentAmountInput.focus();
                        }
                    });
                }
            }

            function addConsultantVisit(id, name, fee) {
                if (consultantVisits[id]) {
                    showStatusMessage('This consultant has already been added.', false);
                    return;
                }
                
                consultantVisits[id] = {
                    id: id,
                    name: name,
                    fee: parseFloat(fee),
                    qty: 1,
                    discount: 0,
                    total: parseFloat(fee)
                };
                
                renderConsultantsList();
                calculateAllTotals();
                const newQtyInput = consultantsListDiv.lastElementChild.querySelector('.qty-input');
                if (newQtyInput) newQtyInput.focus();
            }

            function updateConsultantTotal(id, consultantItem) {
                const qtyInput = consultantItem.querySelector('.qty-input');
                const discountInput = consultantItem.querySelector('.discount-input');
                
                const qty = parseFloat(qtyInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;
                const fee = consultantVisits[id].fee;
                
                const total = (fee * qty) - discount;
                consultantItem.querySelector('.total-amount-cell').textContent = total.toFixed(2);
                
                consultantVisits[id].qty = qty;
                consultantVisits[id].discount = discount;
                consultantVisits[id].total = total;
                
                calculateAllTotals();
            }

            consultantsListDiv.addEventListener('click', function(event) {
                if (event.target.closest('.remove-consultant-btn')) {
                    const btn = event.target.closest('.remove-consultant-btn');
                    const id = btn.dataset.id;
                    delete consultantVisits[id];
                    btn.closest('.consultant-item').remove();
                    calculateAllTotals();
                }
            });

            // --- Payment History Management ---
            function renderPaymentHistory() {
                paymentsListDiv.innerHTML = '';
                paymentHistory.forEach(payment => {
                    const paymentItem = document.createElement('div');
                    paymentItem.classList.add('flex', 'justify-between', 'items-center', 'text-gray-700', 'text-sm');
                    paymentItem.innerHTML = `
                        <span>Payment on ${payment.date}</span>
                        <span class="font-semibold">$${parseFloat(payment.amount).toFixed(2)}</span>
                    `;
                    paymentsListDiv.appendChild(paymentItem);
                });
            }

       // --- Billing Calculations ---
            function calculateAllTotals() {
                let totalConsultantFees = 0;
                for (const id in selectedConsultants) {
                    totalConsultantFees += selectedConsultants[id].total;
                }

                const admissionFee = parseFloat(admissionFeeInput.value) || 0;
                const totalBill = totalConsultantFees + admissionFee;
                totalBillInput.value = totalBill.toFixed(2);

                const discount = parseFloat(discountInput.value) || 0;
                const advanceFee = parseFloat(advanceFeeInput.value) || 0;
                const amountReceivable = totalBill - discount - advanceFee;
                amountReceivableInput.value = amountReceivable.toFixed(2);

                const amountPaid = parseFloat(amountPaidInput.value) || 0;
                const currentRemaining = amountReceivable - amountPaid;
                currentRemainingInput.value = currentRemaining.toFixed(2);
            }

            paymentAmountInput.addEventListener('input', calculateAllTotals);
            
            // --- Form Submission ---
            saveBtn.addEventListener('click', async function(event) {
                event.preventDefault();
                
                // Add validation for required fields
                if (!patientData.id) {
                    showStatusMessage('Please load patient details first.', false);
                    mrNoInput.focus();
                    return;
                }
                
                const newPaymentAmount = parseFloat(paymentAmountInput.value) || 0;
                
                const formData = {
                    mr_no: mrNoInput.value,
                    consultant_visits: JSON.stringify(Object.values(consultantVisits)),
                    payment_amount: newPaymentAmount,
                };

                try {
                    const response = await fetchData(inpatientForm.action, 'POST', formData);
                    showStatusMessage(response.message, true);
                    
                    // Update local state with new data from backend
                    consultantVisits = response.inpatient_detail.consultant_visits;
                    paymentHistory = response.inpatient_detail.payment_history;

                    // Reset payment input and recalculate
                    paymentAmountInput.value = '';
                    renderConsultantsList();
                    renderPaymentHistory();
                    calculateAllTotals();
                } catch (error) {
                    showStatusMessage('An error occurred during save. Please check the console.', false);
                    console.error('Error during form submission:', error);
                }
            });

            printBtn.addEventListener('click', async function(event) {
                event.preventDefault();
                
                // You might want to save first before printing
                // For this example, we'll just print based on current data
                printReceipt();
            });
            
            // --- Print Receipt Logic ---
            function printReceipt() {
                const printContentDiv = document.getElementById('print_content');
                
                let consultantVisitsHtml = '';
                for (const id in consultantVisits) {
                    const visit = consultantVisits[id];
                    consultantVisitsHtml += `
                        <div class="flex justify-between py-1 border-b border-gray-200">
                            <span>${visit.name}</span>
                            <span>$${(visit.fee * visit.qty - visit.discount).toFixed(2)}</span>
                        </div>
                    `;
                }

                let paymentHistoryHtml = '';
                paymentHistory.forEach(payment => {
                     paymentHistoryHtml += `
                        <div class="flex justify-between py-1 border-b border-gray-200">
                            <span>Payment on ${payment.date}</span>
                            <span>$${parseFloat(payment.amount).toFixed(2)}</span>
                        </div>
                    `;
                });
                
                const html = `
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-800 mb-1">Hospital Name</h1>
                        <h2 class="text-xl font-semibold text-gray-700">Inpatient Details & Receipt</h2>
                    </div>

                    <div class="mb-6 border-b pb-4">
                        <div class="flex justify-between mb-1">
                            <span class="font-semibold text-gray-700">Patient:</span>
                            <span class="text-gray-900">${patientNameInput.value} (${mrNoInput.value})</span>
                        </div>
                        <div class="flex justify-between mb-1">
                            <span class="font-semibold text-gray-700">Admission Date:</span>
                            <span class="text-gray-900">${admissionDateInput.value}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-700">Room/Ward:</span>
                            <span class="text-gray-900">${roomInfoInput.value}</span>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Consultant Visits</h3>
                        ${consultantVisitsHtml || '<p class="text-gray-600">No visits recorded.</p>'}
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Payment History</h3>
                        ${paymentHistoryHtml || '<p class="text-gray-600">No payments recorded.</p>'}
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex justify-between mb-1">
                            <span class="text-xl font-bold text-gray-800">Total Bill:</span>
                            <span class="text-xl font-bold text-gray-800">$${totalBillInput.value}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-lg font-semibold text-gray-700">Total Paid:</span>
                            <span class="text-lg font-semibold text-gray-700">$${(parseFloat(indoorPatientData.advance_fee) || 0) + paymentHistory.reduce((sum, p) => sum + parseFloat(p.amount), 0)}</span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-xl font-bold text-red-700">Balance Due:</span>
                            <span class="text-xl font-bold text-red-700">$${amountReceivableInput.value}</span>
                        </div>
                    </div>
                `;

                printContentDiv.innerHTML = html;
                
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <title>Inpatient Receipt</title>
                        <style>
                            body { font-family: 'Inter', sans-serif; margin: 0; padding: 20mm; font-size: 14px; }
                            h1, h2, h3, h4 { color: #1f2937; margin: 0; }
                            p { margin: 0; }
                            .text-center { text-align: center; }
                            .flex { display: flex; }
                            .justify-between { justify-content: space-between; }
                            .mb-1 { margin-bottom: 0.25rem; }
                            .mb-2 { margin-bottom: 0.5rem; }
                            .mb-6 { margin-bottom: 1.5rem; }
                            .border-b { border-bottom: 1px solid #e5e7eb; }
                            .border-t { border-top: 1px solid #e5e7eb; }
                            .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
                            .pt-4 { padding-top: 1rem; }
                            .font-bold { font-weight: 700; }
                            .font-semibold { font-weight: 600; }
                            .text-lg { font-size: 1.125rem; }
                            .text-xl { font-size: 1.25rem; }
                            .text-2xl { font-size: 1.5rem; }
                            @media print {
                                body { font-size: 12px; }
                                h1 { font-size: 1.75rem; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="p-8" style="width: 210mm; min-height: 297mm; margin: 0 auto;">
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

        });
    </script>
@endsection