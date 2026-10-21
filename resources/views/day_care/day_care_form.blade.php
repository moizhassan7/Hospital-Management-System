@extends('layouts.app')

@section('title', 'Day care procedure')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="Day care procedure" subtitle="Record a same-day procedure, the consultants involved and the payment collected.">
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
            <a href="{{ route('patients.register') }}" class="btn btn-secondary">
                <x-ui.icon name="user-plus" />Register new patient
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div id="status-message" class="hidden mb-4" role="status" aria-live="polite"></div>

    <form id="day_care_form" action="{{ route('day-care.store') }}" method="POST">
        @csrf

        <div class="workspace">
            <div class="stack">
                <x-ui.panel title="Patient" description="Load the patient by MR number." icon="user" flush>
                    <div class="panel-body space-y-4">
                        <div class="form-grid">
                            <x-ui.field label="MR number" for="mr_no" hint="Press Enter to load. Ctrl + F searches by name, CNIC or mobile." required class="md:col-span-8">
                                <div class="flex max-w-md gap-2">
                                    <input type="text" id="mr_no" name="mr_no" class="input input-mono" placeholder="e.g. MRN001" autocomplete="off" required>
                                    <button type="button" id="open_patient_search" class="btn btn-secondary">
                                        <x-ui.icon name="search" />Find patient
                                    </button>
                                </div>
                            </x-ui.field>
                            <x-ui.field label="Hours of stay" for="hours_of_stay" class="md:col-span-4">
                                <div class="input-group has-end">
                                    <input type="number" id="hours_of_stay" name="hours_of_stay" class="input input-num" min="0" value="0">
                                    <span class="input-affix is-end">hrs</span>
                                </div>
                            </x-ui.field>
                        </div>

                        <div class="patient-band shadow-none" role="group" aria-label="Loaded patient">
                            <div class="band-cell band-cell-grow">
                                <label for="patient_name" class="band-label">Patient name <span class="field-tag ml-0">Auto</span></label>
                                <input type="text" id="patient_name" name="patient_name" class="band-input" placeholder="No patient loaded yet" readonly>
                            </div>
                            <div class="band-cell w-20">
                                <label for="age" class="band-label">Age</label>
                                <input type="number" id="age" name="age" class="band-input" placeholder="—" readonly>
                            </div>
                            <div class="band-cell w-28">
                                <label for="gender" class="band-label">Gender</label>
                                <input type="text" id="gender" name="gender" class="band-input" placeholder="—" readonly>
                            </div>
                            <div class="band-cell w-32">
                                <label for="patient_type" class="band-label">Patient type</label>
                                <input type="text" id="patient_type" name="patient_type" class="band-input" placeholder="—" readonly>
                                <input type="hidden" id="is_welfare" name="is_welfare" value="0">
                            </div>
                        </div>
                    </div>
                </x-ui.panel>

                <x-ui.panel title="Procedure" icon="clipboard" flush>
                    <div class="panel-body">
                        <x-ui.field label="Procedure notes" for="procedure_details">
                            <textarea id="procedure_details" name="procedure_details" rows="3" class="textarea" placeholder="Describe the day care procedure"></textarea>
                        </x-ui.field>
                    </div>
                </x-ui.panel>

                <x-ui.panel title="Consultants" description="Billed at the consultant's normal or welfare fee." icon="stethoscope" flush>
                    <div class="panel-body space-y-4">
                        <x-ui.field label="Add consultant" for="consultant_search" hint="Press Enter or use Find. In a row, Enter moves to the next field.">
                            <div class="flex max-w-xl gap-2">
                                <div class="input-group has-start flex-1">
                                    <span class="input-affix is-start"><x-ui.icon name="stethoscope" /></span>
                                    <input type="text" id="consultant_search" class="input" placeholder="Name or code" autocomplete="off">
                                </div>
                                <button type="button" id="open_consultant_search" class="btn btn-secondary"><x-ui.icon name="search" />Find</button>
                            </div>
                            <div id="consultant_results" class="hidden"></div>
                        </x-ui.field>

                        <div class="line-items" style="--line-cols: minmax(0, 1fr) 104px 72px 104px 104px 32px">
                            <div class="line-head">
                                <span>Consultant</span>
                                <span class="text-right">Fee ({{ $currency }})</span>
                                <span class="text-right">Qty</span>
                                <span class="text-right">Discount</span>
                                <span class="text-right">Total</span>
                                <span></span>
                            </div>
                            <div id="consultants_list" class="line-list"></div><div class="line-empty">Add at least one consultant before saving.</div>
                        </div>
                    </div>
                </x-ui.panel>
            </div>

            <aside class="workspace-rail">
                <x-ui.panel title="Billing" icon="receipt" flush>
                    <div class="panel-body space-y-4">
                        <x-ui.field label="Total bill" for="total_bill" tag="Calculated" hint="Sum of consultant totals.">
                            <div class="input-group has-start">
                                <span class="input-affix is-start">{{ $currency }}</span>
                                <input type="text" id="total_bill" name="total_bill" class="input input-money input-total" placeholder="0.00" readonly>
                            </div>
                        </x-ui.field>
                        <div class="rounded-lg border border-warning-100 bg-warning-50 p-3">
                            <x-ui.field label="Amount paid" for="amount_paid" required>
                                <div class="input-group has-start">
                                    <span class="input-affix is-start">{{ $currency }}</span>
                                    <input type="number" id="amount_paid" name="amount_paid" class="input input-money font-bold" placeholder="0.00" min="0" required>
                                </div>
                            </x-ui.field>
                        </div>
                        <x-ui.field label="Amount receivable" for="amount_receivable" tag="Calculated">
                            <div class="input-group has-start">
                                <span class="input-affix is-start">{{ $currency }}</span>
                                <input type="text" id="amount_receivable" name="amount_receivable" class="input input-money font-bold" placeholder="0.00" readonly>
                            </div>
                        </x-ui.field>
                    </div>
                    <x-slot:footer class="flex-col items-stretch">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <x-ui.icon name="save" />Save
                        </button>
                        <button type="button" id="print_slip_btn" class="btn btn-secondary btn-block">
                            <x-ui.icon name="printer" />Print receipt
                        </button>
                    </x-slot:footer>
                </x-ui.panel>
            </aside>
        </div>
    </form>

    {{-- Search Modals --}}
    <x-ui.dialog id="patient_search_modal" title="Find patient" description="Search by MR number, name, CNIC or mobile number. Use ↑ ↓ and Enter to choose." size="lg" icon="search">
        <div class="input-group has-start mb-3">
            <span class="input-affix is-start"><x-ui.icon name="search" /></span>
            <input type="text" id="patient_search_query" class="input" placeholder="MR no., name, CNIC or mobile" autocomplete="off">
        </div>
        <div id="patient_search_results" class="result-list"></div>
        <x-slot:footer>
            <button type="button" id="close_patient_modal" class="btn btn-secondary" data-dialog-close>Close</button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog id="consultant_search_modal" title="Find consultant" description="Search by code, name or department. Use ↑ ↓ and Enter to choose." size="lg" icon="stethoscope">
        <div class="input-group has-start mb-3">
            <span class="input-affix is-start"><x-ui.icon name="search" /></span>
            <input type="text" id="consultant_search_query" class="input" placeholder="Code, name or department" autocomplete="off">
        </div>
        <div id="consultant_search_results" class="result-list"></div>
        <x-slot:footer>
            <button type="button" id="close_consultant_modal" class="btn btn-secondary" data-dialog-close>Close</button>
        </x-slot:footer>
    </x-ui.dialog>

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

            function showStatusMessage(message, isSuccess = true) {
                const statusMessageDiv = document.getElementById('status-message');
                statusMessageDiv.textContent = message;
                statusMessageDiv.className = message ? `alert ${isSuccess ? 'alert-success' : 'alert-danger'} mb-4` : 'hidden mb-4';
            }

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

            // Shortcut to open patient search modal
            document.addEventListener('keydown', function(event) {
                if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
                    event.preventDefault();
                    patientSearchModal.classList.remove('hidden');
                    setTimeout(() => patientSearchQueryInput.focus(), 100);
                    fetchAndDisplayPatients('');
                }
            });

            document.getElementById('open_patient_search').addEventListener('click', function() {
                patientSearchModal.classList.remove('hidden');
                setTimeout(() => patientSearchQueryInput.focus(), 100);
                fetchAndDisplayPatients(patientSearchQueryInput.value);
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
                    const prevIndex = (activeIndex - 1 + results.length + results.length) % results.length;
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
                    let tableHtml = '<table class="table table-compact">';
                    tableHtml += '<thead><tr><th>Name</th><th>MR No</th><th>CNIC</th><th>Mobile No</th></tr></thead>';
                    tableHtml += '<tbody>';
                    if (patients.length > 0) {
                        patients.forEach(patient => {
                            tableHtml += `
                                <tr class="search-result-row" data-mr-no="${patient.mr_number}" tabindex="0">
                                    <td class="cell-strong">${patient.name}</td>
                                    <td><span class="id">${patient.mr_number}</span></td>
                                    <td class="muted">${patient.cnic || 'N/A'}</td>
                                    <td class="muted">${patient.mobile_number || 'N/A'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableHtml += `<tr><td colspan="4" class="table-empty">No patients found.</td></tr>`;
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
                    patientSearchResults.innerHTML = '<div class="result-message is-error">Failed to fetch patient data.</div>';
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
                    consultantSearchModal.classList.remove('hidden');
                    setTimeout(() => consultantSearchQueryInput.focus(), 100);
                    fetchAndDisplayConsultants('');
                }
            });

            document.getElementById('open_consultant_search').addEventListener('click', function() {
                consultantSearchModal.classList.remove('hidden');
                setTimeout(() => consultantSearchQueryInput.focus(), 100);
                fetchAndDisplayConsultants(consultantSearchQueryInput.value);
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
                    let tableHtml = '<table class="table table-compact">';
                    tableHtml += '<thead><tr><th>Code</th><th>Name</th><th>Department</th><th class="num">Fee</th></tr></thead>';
                    tableHtml += '<tbody>';

                    if (doctors.length > 0) {
                        doctors.forEach(doctor => {
                            const fee = patientData.is_welfare ? (doctor.welfare_normal_fee || '0.00') : (doctor.general_normal_fee || '0.00');
                            tableHtml += `
                                <tr class="search-result-row" data-doctor='${JSON.stringify(doctor)}' tabindex="0">
                                    <td><span class="id">${doctor.code}</span></td>
                                    <td class="cell-strong">${doctor.name}</td>
                                    <td class="muted">${doctor.department.name || 'N/A'}</td>
                                    <td class="num">{{ get_setting('currency_symbol', 'Rs') }}${fee}</td>
                                </tr>
                            `;
                        });
                    } else {
                        tableHtml += `<tr><td colspan="4" class="table-empty">No consultants found.</td></tr>`;
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
                    consultantSearchResults.innerHTML = '<div class="result-message is-error">Failed to load search results.</div>';
                }
            }

            function addConsultantToList(id, name, fee) {
                if (selectedConsultants[id]) {
                    alert('This consultant has already been added.');
                    return;
                }

                const consultantItem = document.createElement('div');
                consultantItem.className = 'line-row consultant-item';
                consultantItem.dataset.id = id;
                consultantItem.innerHTML = `
                    <span class="truncate font-semibold">${name}</span>
                    <input type="number" class="input input-sm input-money fee-input" value="${parseFloat(fee).toFixed(2)}" aria-label="Fee" readonly>
                    <input type="number" value="1" min="1" class="input input-sm input-num qty-input" aria-label="Quantity" data-id="${id}">
                    <input type="number" value="0" min="0" class="input input-sm input-money discount-input" aria-label="Discount" data-id="${id}">
                    <span class="total-amount-cell text-right font-bold tabular-nums">${parseFloat(fee).toFixed(2)}</span>
                    <button type="button" class="btn btn-danger-ghost btn-icon btn-sm remove-consultant-btn" data-id="${id}" aria-label="Remove ${name}">
                        <svg class="lc-icon" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
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
                    }
                });
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

                const totalBill = totalConsultantFees;
                totalBillInput.value = totalBill.toFixed(2);

                const amountPaid = parseFloat(amountPaidInput.value) || 0;
                const amountReceivable = totalBill - amountPaid;
                amountReceivableInput.value = amountReceivable.toFixed(2);
            }

            amountPaidInput.addEventListener('input', calculateAllTotals);

            // --- Form Submission ---
            dayCareForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                await saveForm(false);
            });

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
// --- Form Submission ---
            async function saveForm(print = false) {
                // Manually check for required fields
                if (!mrNoInput.value || Object.keys(selectedConsultants).length === 0 || !amountPaidInput.value) {
                    showStatusMessage('Please fill out all required fields: MR No, at least one Consultant, and Amount Paid.', false);
                    return;
                }

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
                    showStatusMessage(response.message, true);

                    if (print) {
                        printReceipt();
                    }

                    dayCareForm.reset();
                    clearPatientFields();
                    calculateAllTotals();
                } catch (error) {
                    showStatusMessage('An error occurred during save. Please check the console.', false);
                    console.error(error);
                }
            }

            printSlipBtn.addEventListener('click', async function(event) {
                event.preventDefault();
                await saveForm(true);
            });

            // --- Print Receipt Logic ---
            function printReceipt() {
                if (Object.keys(selectedConsultants).length === 0) {
                    showStatusMessage('Please add at least one consultant before printing.', false);
                    return;
                }

                let consultantsHtml = '';
                for (const id in selectedConsultants) {
                    const c = selectedConsultants[id];
                    consultantsHtml += `
                        <p class="mb-1 text-lg">
                            <strong>${c.name}:</strong>
                            {{ get_setting('currency_symbol', 'Rs') }}${c.fee.toFixed(2)} x ${c.qty} - {{ get_setting('currency_symbol', 'Rs') }}${c.discount.toFixed(2)} =
                            <span class="font-bold">{{ get_setting('currency_symbol', 'Rs') }}${c.total.toFixed(2)}</span>
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
                        <p class="text-xl font-bold text-gray-800 mt-4">Total Bill: <span class="float-right">{{ get_setting('currency_symbol', 'Rs') }}${totalBillInput.value}</span></p>
                        <p class="text-lg font-semibold text-gray-700 mt-2">Amount Paid: <span class="float-right">{{ get_setting('currency_symbol', 'Rs') }}${parseFloat(amountPaidInput.value).toFixed(2)}</span></p>
                        <p class="text-lg font-semibold text-gray-700 mt-2">Amount Receivable: <span class="float-right">{{ get_setting('currency_symbol', 'Rs') }}${amountReceivableInput.value}</span></p>
                    </div>
                `;

                const printWindow = window.open('', '_blank');
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
                        <div class="p-8 bg-white border border-gray-300 rounded-lg shadow-xl" style="width: 210mm; min-height: 297mm; margin: 20mm auto;">
                            <div class="text-center mb-8">
                                <h1 class="text-4xl font-bold text-gray-800 mb-2">{{ get_setting('hospital_name', 'Hospital') }}</h1>
                                <h2 class="text-2xl font-semibold text-gray-700">Day Care Receipt</h2>
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
