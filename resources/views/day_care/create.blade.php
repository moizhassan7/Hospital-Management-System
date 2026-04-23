@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Day Care Procedure</h2>
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
        <form id="day_care_form" action="{{ route('day-care.store') }}" method="POST">
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
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="number" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-bold mb-2">Gender:</label>
                    <input type="text" id="gender" name="gender" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="patient_type" class="block text-gray-700 text-sm font-bold mb-2">Patient Type:</label>
                    <input type="text" id="patient_type" name="patient_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Normal / Welfare" readonly>
                    <input type="hidden" id="is_welfare" name="is_welfare" value="0">
                </div>
                <div>
                    <label for="hours_of_stay" class="block text-gray-700 text-sm font-bold mb-2">Hours of Stay:</label>
                    <input type="number" id="hours_of_stay" name="hours_of_stay" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" value="0">
                </div>
            </div>

            {{-- Procedure Details --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Procedure Details</h3>
            <div class="mb-6">
                <label for="procedure_details" class="block text-gray-700 text-sm font-bold mb-2">Procedure Notes:</label>
                <textarea id="procedure_details" name="procedure_details" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Describe the day care procedure here..."></textarea>
            </div>

            {{-- Consultant Fees Section --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Consultant Fees</h3>
            <div class="mb-6">
                <label for="consultant_search" class="block text-gray-700 text-sm font-bold mb-2">Add Consultant:</label>
                <input type="text" id="consultant_search" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search by name or code">
                <div id="consultant_results" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-48 overflow-y-auto hidden"></div>
            </div>

            <div id="consultants_list" class="space-y-4">
                {{-- Dynamic consultant fee entries will be added here --}}
            </div>

            {{-- Billing Section --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Billing & Payments</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="total_bill" class="block text-gray-700 text-sm font-bold mb-2">Total Bill ($):</label>
                    <input type="text" id="total_bill" name="total_bill" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-calculated" readonly>
                </div>
                <div>
                    <label for="amount_paid" class="block text-gray-700 text-sm font-bold mb-2">Amount Paid ($):</label>
                    <input type="number" id="amount_paid" name="amount_paid" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" required>
                </div>
                <div>
                    <label for="amount_receivable" class="block text-gray-700 text-sm font-bold mb-2">Amount Receivable ($):</label>
                    <input type="text" id="amount_receivable" name="amount_receivable" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-calculated" readonly>
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save
                </button>
                <button type="button" id="print_slip_btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Print Receipt
                </button>
            </div>
        </form>
    </div>

    {{-- Search Modals --}}
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

    {{-- Receipt Print Area (hidden) --}}
    <div id="print_area" class="hidden p-8 bg-white border border-gray-300 rounded-lg shadow-xl" style="width: 210mm; min-height: 297mm; margin: 20mm auto; font-family: 'Inter', sans-serif;">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Hospital Name</h1>
            <h2 class="text-2xl font-semibold text-gray-700">Day Care Receipt</h2>
        </div>
        <div id="print_content"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Patient details fields
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const ageInput = document.getElementById('age');
            const genderInput = document.getElementById('gender');
            const patientTypeInput = document.getElementById('patient_type');
            const isWelfareInput = document.getElementById('is_welfare');
            const hoursOfStayInput = document.getElementById('hours_of_stay');
            const procedureDetailsInput = document.getElementById('procedure_details');
            const consultantsListDiv = document.getElementById('consultants_list');

            // Billing fields
            const totalBillInput = document.getElementById('total_bill');
            const amountPaidInput = document.getElementById('amount_paid');
            const amountReceivableInput = document.getElementById('amount_receivable');

            // Data storage
            let selectedConsultants = {};
            let patientData = {};

            // Productivity Modal Elements
            const patientSearchModal = document.getElementById('patient_search_modal');
            const patientSearchQueryInput = document.getElementById('patient_search_query');
            const patientSearchResults = document.getElementById('patient_search_results');
            const closePatientModalBtn = document.getElementById('close_patient_modal');
            const consultantSearchInput = document.getElementById('consultant_search');
            const consultantResultsDiv = document.getElementById('consultant_results');
            const consultantSearchModal = document.getElementById('consultant_search_modal');
            const consultantSearchQueryInput = document.getElementById('consultant_search_query');
            const consultantSearchResults = document.getElementById('consultant_search_results');
            const closeConsultantModalBtn = document.getElementById('close_consultant_modal');
            const printSlipBtn = document.getElementById('print_slip_btn');
            const dayCareForm = document.getElementById('day_care_form');
            const printAreaDiv = document.getElementById('print_area');

            // Helper function for debounce
            function debounce(func, delay) {
                let timeout;
                return function(...args) {
                    const context = this;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            }

            // --- API Helper ---
            async function fetchData(url) {
                const response = await fetch(url);
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || `HTTP error! status: ${response.status}`);
                }
                return await response.json();
            }

            // --- Patient Search Logic ---
            mrNoInput.addEventListener('keydown', function(event) {
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
                            mrNoInput.value = mrNumber;
                            fetchPatientDetails(mrNumber);
                            patientSearchModal.classList.add('hidden');
                        });
                    });
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    patientSearchResults.innerHTML = '<div class="p-2 text-red-500">Failed to fetch patient data.</div>';
                }
            }

            async function fetchPatientDetails(mrNo) {
                try {
                    const url = `/api/get-by-mr-no/${mrNo}`;
                    const patient = await fetchData(url);
                    if (patient) {
                        patientData = patient; // Store for global use
                        patientNameInput.value = patient.name;
                        ageInput.value = patient.age;
                        genderInput.value = patient.gender;
                        patientTypeInput.value = patient.is_welfare ? 'Welfare' : 'Normal';
                        isWelfareInput.value = patient.is_welfare ? '1' : '0';
                        // Move focus to next key field
                        hoursOfStayInput.focus();
                    } else {
                        alert('No patient found with this MR Number.');
                        clearPatientFields();
                    }
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    alert('Failed to fetch patient data. Please try again.');
                    clearPatientFields();
                }
            }

            function clearPatientFields() {
                patientData = {};
                patientNameInput.value = '';
                ageInput.value = '';
                genderInput.value = '';
                patientTypeInput.value = '';
                isWelfareInput.value = '0';
                hoursOfStayInput.value = '0';
                procedureDetailsInput.value = '';
                consultantsListDiv.innerHTML = '';
                selectedConsultants = {};
                calculateAllTotals();
            }

            // --- Consultant Search Logic ---
            consultantSearchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.value.length > 2) {
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
            
            async function fetchAndDisplayConsultants(query) {
                const url = `/api/search-doctor?query=${query}`;
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
                            addConsultantToList(doctor.id, doctor.name, fee);
                            consultantSearchModal.classList.add('hidden');
                        });
                    });
                } catch (error) {
                    console.error('Error searching for consultants:', error);
                    consultantSearchResults.innerHTML = '<div class="p-3 text-red-500">Failed to load search results.</div>';
                }
            }

            function addConsultantToList(id, name, fee) {
                if (selectedConsultants[id]) {
                    alert('This consultant has already been added.');
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
                        <label class="text-sm font-medium text-gray-700">Fee ($):</label>
                        <input type="number" class="w-24 text-right px-2 py-1 border rounded-md bg-gray-50 fee-input" value="${parseFloat(fee).toFixed(2)}" readonly>
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Qty:</label>
                        <input type="number" value="1" min="1" class="qty-input w-16 text-right px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${id}">
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Discount ($):</label>
                        <input type="number" value="0" min="0" class="discount-input w-24 text-right px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${id}">
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Total ($):</label>
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
                qtyInput.addEventListener('input', updateConsultantTotal);
                discountInput.addEventListener('input', updateConsultantTotal);
                
                function updateConsultantTotal() {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const discount = parseFloat(discountInput.value) || 0;
                    const fee = selectedConsultants[id].fee;
                    
                    const total = (fee * qty) - discount;
                    consultantItem.querySelector('.total-amount-cell').textContent = total.toFixed(2);
                    
                    selectedConsultants[id].qty = qty;
                    selectedConsultants[id].discount = discount;
                    selectedConsultants[id].total = total;
                    
                    calculateAllTotals();
                }
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

                const totalBill = totalConsultantFees; // Initial total bill is just consultant fees
                totalBillInput.value = totalBill.toFixed(2);

                const amountPaid = parseFloat(amountPaidInput.value) || 0;
                const amountReceivable = totalBill - amountPaid;
                amountReceivableInput.value = amountReceivable.toFixed(2);
            }
            
            amountPaidInput.addEventListener('input', calculateAllTotals);

            // --- Form Submission ---
            dayCareForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                
                const consultantsData = JSON.stringify(Object.values(selectedConsultants));

                const formData = {
                    mr_no: mrNoInput.value,
                    procedure_details: procedureDetailsInput.value,
                    hours_of_stay: hoursOfStayInput.value,
                    consultants: consultantsData,
                    total_bill: totalBillInput.value,
                    amount_paid: amountPaidInput.value,
                    amount_receivable: amountReceivableInput.value,
                };
                
                try {
                    const response = await fetchData(dayCareForm.action, 'POST', formData);
                    alert(response.message);
                    
                    // You can handle the receipt generation here if needed
                    // For now, it will just show an alert.
                } catch (error) {
                    alert('An error occurred during save. Please check the console.');
                    console.error(error);
                }
            });
            
            async function fetchData(url, method, data = {}) {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                };
                
                const response = await fetch(url, options);
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || `HTTP error! status: ${response.status}`);
                }
                return await response.json();
            }

            // --- Print Receipt Logic ---
            printSlipBtn.addEventListener('click', function() {
                if (Object.keys(selectedConsultants).length === 0) {
                    alert('Please add at least one consultant before printing.');
                    return;
                }

                let consultantsHtml = '';
                for (const id in selectedConsultants) {
                    const c = selectedConsultants[id];
                    consultantsHtml += `
                        <p class="mb-1 text-lg">
                            <strong>${c.name}:</strong> 
                            $${c.fee.toFixed(2)} x ${c.qty} - $${c.discount.toFixed(2)} = 
                            <span class="font-bold">$${c.total.toFixed(2)}</span>
                        </p>
                    `;
                }

                const html = `
                    <div class="mb-6 border-b pb-4">
                        <p class="text-lg mb-2"><strong>Patient Name:</strong> ${patientNameInput.value}</p>
                        <p class="text-lg mb-2"><strong>MR No:</strong> ${mrNoInput.value}</p>
                        <p class="text-lg mb-2"><strong>Hours of Stay:</strong> ${hoursOfStayInput.value}</p>
                    </div>

                    <div class="mb-8">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Consultant Details</h3>
                        ${consultantsHtml}
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-xl font-bold text-gray-800 mt-4">Total Bill: <span class="float-right">$${totalBillInput.value}</span></p>
                        <p class="text-lg font-semibold text-gray-700 mt-2">Amount Paid: <span class="float-right">$${parseFloat(amountPaidInput.value).toFixed(2)}</span></p>
                        <p class="text-lg font-semibold text-gray-700 mt-2">Amount Receivable: <span class="float-right">$${amountReceivableInput.value}</span></p>
                    </div>
                `;

                printAreaDiv.innerHTML = html;
                printDiv('print_area');
            });

            function printDiv(divId) {
                const printContents = document.getElementById(divId).innerHTML;
                const originalContents = document.body.innerHTML;

                document.body.innerHTML = originalContents;
                const printWindow = window.open('', '_blank');
                printWindow.document.open();
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <title>Day Care Receipt</title>
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
            calculateAllTotals();
        });
    </script>
@endsection