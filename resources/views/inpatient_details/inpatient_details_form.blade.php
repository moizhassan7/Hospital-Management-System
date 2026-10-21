@extends('layouts.app')

@section('title', 'Inpatient details')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="Inpatient details" subtitle="Record consultant visits and part-payments for an admitted patient during their stay.">
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Success/Error Message Container --}}
    <div id="status-message" class="hidden mb-4" role="status" aria-live="polite"></div>

    <form id="inpatient_form" action="{{ route('inpatient.store') }}" method="POST">
        @csrf

        <div class="workspace">
            <div class="stack">
                <x-ui.panel title="Patient" description="Only admitted (indoor) patients have inpatient details." icon="user" flush>
                    <div class="panel-body space-y-4">
                        <x-ui.field label="MR number" for="mr_no" hint="Press Enter to load. Ctrl + F searches indoor patients." required>
                            <div class="flex max-w-md gap-2">
                                <input type="text" id="mr_no" name="mr_no" class="input input-mono" placeholder="e.g. MRN001" autocomplete="off" required>
                                <button type="button" id="open_patient_search" class="btn btn-secondary">
                                    <x-ui.icon name="search" />Find patient
                                </button>
                            </div>
                        </x-ui.field>

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
                                <label for="patient_type" class="band-label">Type</label>
                                <input type="text" id="patient_type" name="patient_type" class="band-input" placeholder="—" readonly>
                                <input type="hidden" id="is_welfare" name="is_welfare" value="0">
                            </div>
                            <div class="band-cell w-32">
                                <label for="admission_date" class="band-label">Admitted</label>
                                <input type="text" id="admission_date" name="admission_date" class="band-input" placeholder="—" readonly>
                            </div>
                            <div class="band-cell w-32">
                                <label for="room_info" class="band-label">Room / ward</label>
                                <input type="text" id="room_info" name="room_info" class="band-input" placeholder="—" readonly>
                            </div>
                        </div>
                    </div>
                </x-ui.panel>

                <x-ui.panel title="Consultant visits" description="Visits are billed at the consultant's normal or welfare fee." icon="stethoscope" flush>
                    <div class="panel-body space-y-4">
                        <x-ui.field label="Add consultant" for="consultant_search" hint="Type a name or code and press Enter, or use Find. In a row, Enter moves to the next field; Ctrl + B jumps to payments.">
                            <div class="flex max-w-xl gap-2">
                                <div class="input-group has-start flex-1">
                                    <span class="input-affix is-start"><x-ui.icon name="stethoscope" /></span>
                                    <input type="text" id="consultant_search" class="input" placeholder="Name or code" autocomplete="off">
                                </div>
                                <button type="button" id="open_consultant_search" class="btn btn-secondary"><x-ui.icon name="search" />Find</button>
                            </div>
                        </x-ui.field>

                        <div class="line-items" style="--line-cols: minmax(0, 1fr) 104px 72px 104px 104px 32px">
                            <div class="line-head">
                                <span>Consultant</span>
                                <span class="text-right">Fee ({{ $currency }})</span>
                                <span class="text-right">Visits</span>
                                <span class="text-right">Discount</span>
                                <span class="text-right">Total</span>
                                <span></span>
                            </div>
                            <div id="consultants_list" class="line-list"></div><div class="line-empty">No consultant visits recorded yet.</div>
                        </div>
                    </div>
                </x-ui.panel>
            </div>

            <aside class="workspace-rail">
                <x-ui.panel title="Payments" icon="wallet" flush>
                    <div class="panel-body space-y-4">
                        <div>
                            <p class="mb-1 text-[11px] font-semibold tracking-wider text-fg-muted uppercase">Payment history</p>
                            <div id="payments_list" class="divide-y divide-line-soft"></div>
                        </div>
                        <x-ui.field label="Add new payment" for="payment_amount">
                            <div class="input-group has-start">
                                <span class="input-affix is-start">{{ $currency }}</span>
                                <input type="number" id="payment_amount" name="payment_amount" class="input input-money" min="0" placeholder="5000">
                            </div>
                        </x-ui.field>
                    </div>
                    <x-slot:footer class="flex-col items-stretch">
                        <button type="submit" id="save_btn" class="btn btn-primary btn-lg btn-block">
                            <x-ui.icon name="save" />Save changes
                        </button>
                        <button type="button" id="print_btn" class="btn btn-secondary btn-block">
                            <x-ui.icon name="printer" />Print receipt
                        </button>
                    </x-slot:footer>
                </x-ui.panel>
            </aside>
        </div>
    </form>

    {{-- Search Modals --}}
    <x-ui.dialog id="patient_search_modal" title="Find indoor patient" description="Search by MR number, name or mobile number. Use ↑ ↓ and Enter to choose." size="lg" icon="search">
        <div class="input-group has-start mb-3">
            <span class="input-affix is-start"><x-ui.icon name="search" /></span>
            <input type="text" id="patient_search_query" class="input" placeholder="MR no., name or mobile" autocomplete="off">
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
                statusMessageDiv.className = message ? `alert ${isSuccess ? 'alert-success' : 'alert-danger'} mb-4` : 'hidden mb-4';
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
                            fetchInpatientDetailsData(mrNumber);
                            patientSearchModal.classList.add('hidden');
                        });
                    });
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    patientSearchResults.innerHTML = '<div class="result-message is-error">Failed to fetch patient data.</div>';
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

            document.getElementById('open_consultant_search').addEventListener('click', function() {
                consultantSearchModal.classList.remove('hidden');
                setTimeout(() => consultantSearchQueryInput.focus(), 100);
                fetchAndDisplayConsultants(consultantSearchInput.value || consultantSearchQueryInput.value);
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
                            addConsultantVisit(doctor.id, doctor.name, fee);
                            consultantSearchModal.classList.add('hidden');
                            consultantSearchInput.value = '';
                        });
                    });
                } catch (error) {
                    console.error('Error searching for consultants:', error);
                    consultantSearchResults.innerHTML = '<div class="result-message is-error">Failed to load search results.</div>';
                }
            }

            function renderConsultantsList() {
                consultantsListDiv.innerHTML = '';
                for (const id in consultantVisits) {
                    const visit = consultantVisits[id];
                    const consultantItem = document.createElement('div');
                    consultantItem.className = 'line-row consultant-item';
                    consultantItem.dataset.id = id;
                    consultantItem.innerHTML = `
                        <span class="truncate font-semibold">${visit.name}</span>
                        <input type="number" class="input input-sm input-money fee-input" value="${parseFloat(visit.fee).toFixed(2)}" aria-label="Fee" readonly>
                        <input type="number" value="${visit.qty}" min="1" class="input input-sm input-num qty-input" aria-label="Visits" data-id="${id}">
                        <input type="number" value="${visit.discount}" min="0" class="input input-sm input-money discount-input" aria-label="Discount" data-id="${id}">
                        <span class="total-amount-cell text-right font-bold tabular-nums">${parseFloat(visit.total).toFixed(2)}</span>
                        <button type="button" class="btn btn-danger-ghost btn-icon btn-sm remove-consultant-btn" data-id="${id}" aria-label="Remove ${visit.name}">
                            <svg class="lc-icon" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
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
                    paymentItem.className = 'flex items-center justify-between py-2 text-[13px]';
                    paymentItem.innerHTML = `
                        <span class="text-fg-muted">Payment on ${payment.date}</span>
                        <span class="font-semibold tabular-nums">{{ get_setting('currency_symbol', 'Rs') }}${parseFloat(payment.amount).toFixed(2)}</span>
                    `;
                    paymentsListDiv.appendChild(paymentItem);
                });
            }

            function billingTotals() {
                const totalBill = Object.values(consultantVisits).reduce((sum, visit) => {
                    const line = parseFloat(visit.total);
                    if (!Number.isNaN(line)) {
                        return sum + line;
                    }
                    return sum + ((parseFloat(visit.fee) || 0) * (parseFloat(visit.qty) || 0) - (parseFloat(visit.discount) || 0));
                }, 0);
                const advance = parseFloat(indoorPatientData.total_advance_paid || indoorPatientData.advance_fee) || 0;
                const visitPayments = paymentHistory.reduce((sum, payment) => sum + (parseFloat(payment.amount) || 0), 0);
                const paid = advance + visitPayments;
                return {
                    totalBill,
                    paid,
                    receivable: totalBill - paid,
                };
            }

            function calculateAllTotals() {
                billingTotals();
            }

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
                const totals = billingTotals();

                let consultantVisitsHtml = '';
                for (const id in consultantVisits) {
                    const visit = consultantVisits[id];
                    consultantVisitsHtml += `
                        <div class="flex justify-between py-1 border-b border-gray-200">
                            <span>${visit.name}</span>
                            <span>{{ get_setting('currency_symbol', 'Rs') }}${(visit.fee * visit.qty - visit.discount).toFixed(2)}</span>
                        </div>
                    `;
                }

                let paymentHistoryHtml = '';
                paymentHistory.forEach(payment => {
                     paymentHistoryHtml += `
                        <div class="flex justify-between py-1 border-b border-gray-200">
                            <span>Payment on ${payment.date}</span>
                            <span>{{ get_setting('currency_symbol', 'Rs') }}${parseFloat(payment.amount).toFixed(2)}</span>
                        </div>
                    `;
                });

                const html = `
                    <div class="text-center mb-8">
                        <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ get_setting('hospital_name', 'Hospital') }}</h1>
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
                            <span class="text-xl font-bold text-gray-800">{{ get_setting('currency_symbol', 'Rs') }}${totals.totalBill.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-lg font-semibold text-gray-700">Total Paid:</span>
                            <span class="text-lg font-semibold text-gray-700">{{ get_setting('currency_symbol', 'Rs') }}${totals.paid.toFixed(2)}</span>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-xl font-bold text-red-700">Balance Due:</span>
                            <span class="text-xl font-bold text-red-700">{{ get_setting('currency_symbol', 'Rs') }}${totals.receivable.toFixed(2)}</span>
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
