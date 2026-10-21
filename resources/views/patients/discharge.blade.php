@extends('layouts.app')

@section('title', 'Patient discharge')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="Patient discharge" subtitle="Load an admitted patient, record treatment and discharge details, then settle the final bill.">
        <x-slot:actions>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form id="discharge_form" action="{{ route('patients.store_discharge') }}" method="POST">
        @csrf

        <div class="workspace">
            <div class="stack">
                {{-- Patient identification --}}
                <x-ui.panel title="Patient" description="Only admitted (indoor) patients can be discharged." icon="user" flush>
                    <div class="panel-body space-y-4">
                        <div class="form-grid">
                            <x-ui.field label="MR number" for="mr_no" hint="Press Enter to load. Ctrl + F searches indoor patients." required class="md:col-span-7">
                                <div class="flex gap-2">
                                    <input type="text" id="mr_no" name="mr_no" class="input input-mono" placeholder="e.g. MRN001" autocomplete="off" required>
                                    <button type="button" id="open_patient_search" class="btn btn-secondary">
                                        <x-ui.icon name="search" />Find patient
                                    </button>
                                </div>
                            </x-ui.field>
                            <x-ui.field label="Admission date" for="admission_date" tag="Auto" class="md:col-span-5 xl:col-span-3 xl:col-start-10">
                                <input type="date" id="admission_date" name="admission_date" class="input" readonly>
                            </x-ui.field>
                        </div>

                        <div class="patient-band shadow-none" role="group" aria-label="Loaded patient">
                            <div class="band-cell band-cell-grow">
                                <label for="patient_name" class="band-label">Patient name <span class="field-tag ml-0">Auto</span></label>
                                <input type="text" id="patient_name" name="patient_name" class="band-input" placeholder="No patient loaded yet" readonly>
                            </div>
                            <div class="band-cell w-24">
                                <label for="age" class="band-label">Age</label>
                                <input type="number" id="age" name="age" class="band-input" placeholder="—" readonly>
                            </div>
                            <div class="band-cell w-32">
                                <label for="patient_type" class="band-label">Patient type</label>
                                <input type="text" id="patient_type" name="patient_type" class="band-input" placeholder="—" readonly>
                                <input type="hidden" id="is_welfare" name="is_welfare" value="0">
                            </div>
                        </div>

                        <x-ui.field label="Cause of admission" for="cause">
                            <input type="text" id="cause" name="cause" class="input" placeholder="Cause of admission" autocomplete="off">
                        </x-ui.field>
                    </div>
                </x-ui.panel>

                {{-- Treatment --}}
                <x-ui.panel title="Consultants & diagnosis" description="Consultant visits are billed per visit; diagnoses print on the slip." icon="stethoscope" flush>
                    <x-ui.form-section>
                        <div class="form-grid">
                            <x-ui.field label="Add consultant" for="consultant_search" hint="Press Enter or use Find to choose a consultant." class="md:col-span-6">
                                <div class="flex gap-2">
                                    <div class="input-group has-start flex-1">
                                        <span class="input-affix is-start"><x-ui.icon name="stethoscope" /></span>
                                        <input type="text" id="consultant_search" class="input" placeholder="Name or code" autocomplete="off">
                                    </div>
                                    <button type="button" id="open_consultant_search" class="btn btn-secondary"><x-ui.icon name="search" />Find</button>
                                </div>
                                <div id="consultant_results" class="hidden"></div>
                            </x-ui.field>
                            <x-ui.field label="Add diagnosis" for="diagnosis_search" hint="Type three or more letters, then choose from the list." class="md:col-span-6">
                                <div class="autocomplete">
                                    <div class="input-group has-start">
                                        <span class="input-affix is-start"><x-ui.icon name="search" /></span>
                                        <input type="text" id="diagnosis_search" class="input" placeholder="Search diagnoses" autocomplete="off">
                                    </div>
                                    <div id="diagnosis_results" class="autocomplete-menu hidden"></div>
                                </div>
                            </x-ui.field>
                            <x-ui.field label="Selected diagnoses" class="md:col-span-12">
                                <div id="diagnoses_list" class="chip-list"></div>
                            </x-ui.field>
                        </div>
                    </x-ui.form-section>

                    <x-ui.form-section title="Consultant fees">
                        <div class="line-items" style="--line-cols: minmax(0, 1fr) 104px 72px 104px 104px 32px">
                            <div class="line-head">
                                <span>Consultant</span>
                                <span class="text-right">Fee ({{ $currency }})</span>
                                <span class="text-right">Visits</span>
                                <span class="text-right">Discount</span>
                                <span class="text-right">Total</span>
                                <span></span>
                            </div>
                            <div id="consultants_list" class="line-list"></div><div class="line-empty">No consultants added yet.</div>
                        </div>
                    </x-ui.form-section>

                    <x-ui.form-section title="Anesthesia">
                        <div class="option-row flex-wrap">
                            <label for="is_anesthesia" class="flex min-w-0 flex-1 items-center gap-3">
                                <input type="hidden" name="is_anesthesia" value="0">
                                <input type="checkbox" id="is_anesthesia" name="is_anesthesia" value="1" class="switch">
                                <span>
                                    <span class="option-row-title block">Is anesthesia?</span>
                                    <span class="option-row-desc block">Turn on if anesthesia was given during this admission.</span>
                                </span>
                            </label>
                            <div id="anesthesia_type_field" class="hidden w-full sm:w-56">
                                <x-ui.field label="Anesthesia type" for="anesthesia_type">
                                    <select id="anesthesia_type" name="anesthesia_type" class="select">
                                        <option value="">Select type</option>
                                        <option value="Type 1">Type 1</option>
                                        <option value="Type 2">Type 2</option>
                                        <option value="Type 3">Type 3</option>
                                        <option value="Type 4">Type 4</option>
                                    </select>
                                </x-ui.field>
                            </div>
                        </div>
                    </x-ui.form-section>
                </x-ui.panel>

                {{-- Discharge details --}}
                <x-ui.panel title="Discharge details" icon="file-text" flush>
                    <div class="panel-body">
                        <div class="form-grid">
                            <x-ui.field label="Discharge date" for="discharge_date" required class="md:col-span-4 xl:col-span-3">
                                <input type="date" id="discharge_date" name="discharge_date" class="input" value="{{ date('Y-m-d') }}" required>
                            </x-ui.field>
                            <x-ui.field label="Discharge time" for="discharge_time" required class="md:col-span-4 xl:col-span-3">
                                <input type="time" id="discharge_time" name="discharge_time" class="input" value="{{ date('H:i') }}" required>
                            </x-ui.field>
                            <x-ui.field label="Discharge status" for="discharge_status" required class="md:col-span-4 xl:col-span-6">
                                <select id="discharge_status" name="discharge_status" class="select" required>
                                    <option value="">Select status</option>
                                    <option value="Recovered">Recovered</option>
                                    <option value="Referred">Referred to another hospital</option>
                                    <option value="Against Medical Advice">Against Medical Advice (AMA)</option>
                                    <option value="Expired">Expired</option>
                                    <option value="Transferred">Transferred</option>
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Certifying doctor" for="certifying_doctor" class="md:col-span-7 xl:col-span-6">
                                <select id="certifying_doctor" name="certifying_doctor_id" class="select">
                                    <option value="">Select doctor</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->name }} ({{ $doctor->code }})</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Payment clearance" for="payment_clearance_status" required class="md:col-span-5 xl:col-span-3">
                                <select id="payment_clearance_status" name="payment_clearance_status" class="select" required>
                                    <option value="">Select status</option>
                                    <option value="Cleared">Cleared</option>
                                    <option value="Pending">Pending</option>
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Discharge summary" for="discharge_summary" class="md:col-span-12">
                                <textarea id="discharge_summary" name="discharge_summary" rows="3" class="textarea" placeholder="Summary of the hospital stay and treatment"></textarea>
                            </x-ui.field>
                            <x-ui.field label="Medication at discharge" for="medication_at_discharge" class="md:col-span-6">
                                <textarea id="medication_at_discharge" name="medication_at_discharge" rows="2" class="textarea" placeholder="Medicines prescribed at discharge"></textarea>
                            </x-ui.field>
                            <x-ui.field label="Follow-up instructions" for="follow_up_instructions" class="md:col-span-6">
                                <textarea id="follow_up_instructions" name="follow_up_instructions" rows="2" class="textarea" placeholder="Instructions for care after discharge"></textarea>
                            </x-ui.field>
                        </div>
                    </div>
                </x-ui.panel>
            </div>

            {{-- Billing statement --}}
            <aside class="workspace-rail">
                <x-ui.panel title="Billing statement" icon="receipt" flush>
                    <div class="panel-body pt-2">
                        <p class="mt-2 mb-1 text-[11px] font-semibold tracking-wider text-fg-muted uppercase">Expenses</p>
                        <div class="ledger">
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">+</span>
                                <label for="admission_fee" class="ledger-label">Admission fee</label>
                                <input type="text" id="admission_fee" name="admission_fee" class="input input-money" placeholder="0.00" readonly>
                            </div>
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">+</span>
                                <label for="consultant_fees_total" class="ledger-label">Consultant fees</label>
                                <input type="text" id="consultant_fees_total" class="input input-money" placeholder="0.00" readonly>
                            </div>
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">+</span>
                                <label for="room_rent" class="ledger-label">Room / ward rent
                                    <small><span id="room_days_display">0</span> days @ {{ $currency }} <span id="room_rent_rate_display">0</span>/day</small>
                                </label>
                                <input type="hidden" id="room_days" name="room_days" value="0">
                                <input type="text" id="room_rent" name="room_rent" class="input input-money" readonly value="0.00">
                            </div>
                            <div class="ledger-row ledger-total">
                                <span class="ledger-op" aria-hidden="true">=</span>
                                <label for="total_bill" class="ledger-label">Total bill</label>
                                <input type="text" id="total_bill" name="total_bill" class="input input-money font-bold" placeholder="0.00" readonly>
                            </div>
                        </div>

                        <p class="mt-5 mb-1 text-[11px] font-semibold tracking-wider text-fg-muted uppercase">Settlement</p>
                        <div class="ledger">
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">−</span>
                                <label for="advance_fee" class="ledger-label">Advances paid <small>Installments on record</small></label>
                                <input type="text" id="advance_fee" name="advance_fee" class="input input-money text-success-700" placeholder="0.00" readonly>
                            </div>
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">−</span>
                                <label for="discount" class="ledger-label">Discount</label>
                                <input type="number" id="discount" name="discount" class="input input-money" min="0" value="0" step="0.01">
                            </div>
                            <div class="ledger-row ledger-total">
                                <span class="ledger-op" aria-hidden="true">=</span>
                                <label for="amount_receivable" class="ledger-label">Amount receivable</label>
                                <input type="text" id="amount_receivable" name="amount_receivable" class="input input-money font-bold" placeholder="0.00" readonly>
                            </div>
                        </div>

                        <div class="mt-4 rounded-lg border border-warning-100 bg-warning-50 p-3">
                            <x-ui.field label="Pay now" for="amount_paid" required>
                                <div class="input-group has-start">
                                    <span class="input-affix is-start">{{ $currency }}</span>
                                    <input type="number" id="amount_paid" name="amount_paid" class="input input-money input-total" placeholder="0.00" min="0" required step="0.01">
                                </div>
                            </x-ui.field>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3 rounded-lg bg-ink-900 px-3.5 py-3 text-white">
                            <label for="current_remaining" class="text-[13px] font-semibold">Remaining balance</label>
                            <div class="flex items-center gap-1 font-display text-lg font-extrabold tabular-nums">
                                <span class="text-sm text-ink-300">{{ $currency }}</span>
                                <input type="text" id="current_remaining" name="current_remaining" class="w-28 border-0 bg-transparent p-0 text-right text-white outline-none" readonly tabindex="-1">
                            </div>
                        </div>
                    </div>
                    <x-slot:footer class="flex-col items-stretch">
                        <button type="submit" class="btn btn-primary btn-lg btn-block" data-busy-label="Discharging…">
                            <x-ui.icon name="log-out" />Discharge patient
                        </button>
                        <button type="button" id="print_slip_btn" class="btn btn-secondary btn-block">
                            <x-ui.icon name="printer" />Print slip
                        </button>
                    </x-slot:footer>
                </x-ui.panel>
            </aside>
        </div>
    </form>

    {{-- Search Modals (Patient and Consultant) --}}
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

    {{-- Discharge Slip Area for Printing --}}
    <div id="discharge_slip_area" class="hidden" style="width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm; font-family: 'Inter', sans-serif;">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">{{ get_setting('hospital_name', 'Hospital Management System') }}</h1>
            <h2 class="text-xl font-semibold text-gray-700">Patient Discharge Summary</h2>
        </div>

        <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-gray-700 mb-8 border-b pb-4">
            <div class="col-span-2">
                <h3 class="font-bold text-lg mb-2 border-b pb-1">Patient Information</h3>
            </div>
            <p><strong>MR No:</strong> <span id="slip_mr_no"></span></p>
            <p><strong>Name:</strong> <span id="slip_patient_name"></span></p>
            <p><strong>Age:</strong> <span id="slip_age"></span></p>
            <p><strong>Patient Type:</strong> <span id="slip_patient_type"></span></p>
            <p class="col-span-2"><strong>Cause of Admission:</strong> <span id="slip_cause"></span></p>
            <p class="col-span-2"><strong>Diagnosis:</strong> <span id="slip_diagnoses"></span></p>
        </div>

        <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-gray-700 mb-8 border-b pb-4">
            <div class="col-span-2">
                <h3 class="font-bold text-lg mb-2 border-b pb-1">Admission & Discharge Details</h3>
            </div>
            <p><strong>Admission Date:</strong> <span id="slip_admission_date"></span></p>
            <p><strong>Discharge Date:</strong> <span id="slip_discharge_date"></span></p>
            <p><strong>Discharge Status:</strong> <span id="slip_discharge_status"></span></p>
            <p><strong>Certifying Doctor:</strong> <span id="slip_certifying_doctor"></span></p>
            <p class="col-span-2"><strong>Discharge Summary:</strong> <span id="slip_discharge_summary"></span></p>
            <p class="col-span-2"><strong>Medication at Discharge:</strong> <span id="slip_medication"></span></p>
            <p class="col-span-2"><strong>Follow-up Instructions:</strong> <span id="slip_follow_up"></span></p>
        </div>

        <div class="mb-8 border-b pb-4">
            <h3 class="font-bold text-lg mb-2 border-b pb-1">Consultant Fees Breakdown</h3>
            <table class="w-full text-sm border-collapse border border-gray-400">
                <thead>
                    <tr class="font-bold text-gray-800 bg-gray-200">
                        <td class="w-1/2 py-2 px-2 border border-gray-400">Doctor Name</td>
                        <td class="w-1/6 py-2 px-2 text-right border border-gray-400">Qty</td>
                        <td class="w-1/6 py-2 px-2 text-right border border-gray-400">Price</td>
                        <td class="w-1/6 py-2 px-2 text-right border border-gray-400">Total</td>
                    </tr>
                </thead>
                <tbody id="slip_consultant_fees">
                    </tbody>
            </table>
        </div>

        <div class="mb-8">
            <h3 class="font-bold text-lg mb-2 border-b pb-1">Billing Summary</h3>
            <table class="w-full text-gray-700 border-collapse border border-gray-400">
                <tbody>
                    <tr>
                        <td class="py-1 px-2 border border-gray-400"><strong>Total Consultant Fees:</strong></td>
                        <td class="py-1 px-2 text-right border border-gray-400" id="slip_total_consultant_fees"></td>
                    </tr>
                    <tr>
                        <td class="py-1 px-2 border border-gray-400"><strong>Admission Fee:</strong></td>
                        <td class="py-1 px-2 text-right border border-gray-400" id="slip_admission_fee"></td>
                    </tr>
                    <tr>
                        <td class="py-1 px-2 border border-gray-400"><strong>Room / Ward Rent:</strong></td>
                        <td class="py-1 px-2 text-right border border-gray-400" id="slip_room_rent"></td>
                    </tr>
                    <tr>
                        <td class="py-1 px-2 border border-gray-400"><strong>Total Bill:</strong></td>
                        <td class="py-1 px-2 text-right border border-gray-400" id="slip_total_bill"></td>
                    </tr>
                    <tr>
                        <td class="py-1 px-2 border border-gray-400"><strong>Discount:</strong></td>
                        <td class="py-1 px-2 text-right border border-gray-400" id="slip_discount"></td>
                    </tr>
                    <tr>
                        <td class="py-1 px-2 border border-gray-400"><strong>Advance Paid:</strong></td>
                        <td class="py-1 px-2 text-right border border-gray-400" id="slip_advance_fee"></td>
                    </tr>
                    <tr>
                        <td class="py-1 px-2 border border-gray-400"><strong>Amount Receivable:</strong></td>
                        <td class="py-1 px-2 text-right border border-gray-400" id="slip_amount_receivable"></td>
                    </tr>
                    <tr class="font-bold text-gray-900 bg-gray-100">
                        <td class="py-2 px-2 border border-gray-400"><strong>Amount Paid:</strong></td>
                        <td class="py-2 px-2 text-right border border-gray-400" id="slip_amount_paid"></td>
                    </tr>
                    <tr class="font-bold text-gray-900 bg-gray-100">
                        <td class="py-2 px-2 border border-gray-400"><strong>Current Remaining:</strong></td>
                        <td class="py-2 px-2 text-right border border-gray-400" id="slip_current_remaining"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-end mt-16 text-sm text-gray-700">
            <div>
                <p class="border-t border-gray-500 pt-2">Cashier Signature</p>
            </div>
            <div>
                <p class="border-t border-gray-500 pt-2">Certifying Doctor Signature</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Patient details and fields
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const admissionDateInput = document.getElementById('admission_date');
            const ageInput = document.getElementById('age');
            const patientTypeInput = document.getElementById('patient_type');
            const isWelfareInput = document.getElementById('is_welfare');
            const admissionFeeInput = document.getElementById('admission_fee');
            const advanceFeeInput = document.getElementById('advance_fee');

            // Consultant and diagnosis fields
            const consultantSearchInput = document.getElementById('consultant_search');
            const consultantResultsDiv = document.getElementById('consultant_results');
            const diagnosisSearchInput = document.getElementById('diagnosis_search');
            const diagnosisResultsDiv = document.getElementById('diagnosis_results');
            const diagnosesListDiv = document.getElementById('diagnoses_list');
            const consultantsListDiv = document.getElementById('consultants_list');

            // Anesthesia fields
            const isAnesthesiaCheckbox = document.getElementById('is_anesthesia');
            const anesthesiaTypeField = document.getElementById('anesthesia_type_field');

            // Discharge fields
            const dischargeStatusSelect = document.getElementById('discharge_status');
            const certifyingDoctorSelect = document.getElementById('certifying_doctor');
            const paymentClearanceSelect = document.getElementById('payment_clearance_status');
            const dischargeSummaryTextarea = document.getElementById('discharge_summary');
            const dischargeDateInput = document.getElementById('discharge_date');

            // Billing fields
            const totalBillInput = document.getElementById('total_bill');
            const discountInput = document.getElementById('discount');
            const amountReceivableInput = document.getElementById('amount_receivable');
            const amountPaidInput = document.getElementById('amount_paid');
            const currentRemainingInput = document.getElementById('current_remaining');

            // Data storage for form submission
            let selectedConsultants = {};
            let selectedDiagnoses = {};
            let patientData = {};
            let indoorPatientData = {};
            let roomRentPerDay = 0;
            let admissionDatetime = null;

            // Productivity Modal Elements
            const patientSearchModal = document.getElementById('patient_search_modal');
            const patientSearchQueryInput = document.getElementById('patient_search_query');
            const patientSearchResults = document.getElementById('patient_search_results');
            const closePatientModalBtn = document.getElementById('close_patient_modal');

            const consultantSearchModal = document.getElementById('consultant_search_modal');
            const consultantSearchQueryInput = document.getElementById('consultant_search_query');
            const consultantSearchResults = document.getElementById('consultant_search_results');
            const closeConsultantModalBtn = document.getElementById('close_consultant_modal');

            const printSlipBtn = document.getElementById('print_slip_btn');

            // Print slip specific elements
            const consultantFeesTableBody = document.getElementById('slip_consultant_fees');
            const slipTotalConsultantFees = document.getElementById('slip_total_consultant_fees');
            const slipAdmissionFee = document.getElementById('slip_admission_fee');


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
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            }

            // --- Patient and Indoor Data Lookup ---
            mrNoInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    const mrNo = this.value;
                    if (mrNo) {
                        fetchIndoorPatientData(mrNo);
                    }
                }
            });

            // Added patient search modal trigger with Ctrl+F
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

            // Patient search modal logic
            patientSearchQueryInput.addEventListener('input', debounce(async function() {
                const query = this.value;
                await fetchAndDisplayPatients(query);
            }, 300));

            closePatientModalBtn.addEventListener('click', () => {
                patientSearchModal.classList.add('hidden');
            });

            // Re-adding keyboard navigation for patient search results
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
                            fetchIndoorPatientData(mrNumber);
                            patientSearchModal.classList.add('hidden');
                        });
                    });
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    patientSearchResults.innerHTML = '<div class="result-message is-error">Failed to fetch patient data.</div>';
                }
            }


            async function fetchIndoorPatientData(mrNo) {
                try {
                    // Use the comprehensive API from InpatientDetailController
                    const response = await fetch(`/api/get-details-by-mr-no/${mrNo}`);
                    const data = await response.json();

                    if (data.found && data.patient && data.indoor_patient) {
                        patientData = data.patient;
                        indoorPatientData = data.indoor_patient;

                        patientNameInput.value = patientData.name;
                        ageInput.value = patientData.age;
                        patientTypeInput.value = patientData.is_welfare ? 'Welfare' : 'Normal';
                        isWelfareInput.value = patientData.is_welfare ? '1' : '0';
                        admissionDateInput.value = indoorPatientData.registration_date;
                        admissionFeeInput.value = parseFloat(indoorPatientData.admission_fee).toFixed(2);
                        advanceFeeInput.value = parseFloat(indoorPatientData.total_advance_paid || indoorPatientData.advance_fee).toFixed(2);

                        roomRentPerDay = parseFloat(data.room_rent_per_day) || 0;
                        admissionDatetime = data.admission_datetime;

                        // NEW: Populate consultant visits and diagnoses from fetched data
                        if (data.patient_discharge) {
                            const pd = data.patient_discharge;
                            try {
                                let parsedConsultants = typeof pd.consultants === 'string' ? JSON.parse(pd.consultants) : pd.consultants || {};
                                if (Array.isArray(parsedConsultants)) {
                                    let obj = {};
                                    parsedConsultants.forEach(c => { if(c && c.id) obj[c.id] = c; });
                                    selectedConsultants = obj;
                                } else {
                                    selectedConsultants = parsedConsultants;
                                }
                            } catch(e){ selectedConsultants = {}; }

                            try {
                                let parsedDiagnoses = typeof pd.diagnoses === 'string' ? JSON.parse(pd.diagnoses) : pd.diagnoses || {};
                                if (Array.isArray(parsedDiagnoses)) {
                                    let obj = {};
                                    parsedDiagnoses.forEach((name, i) => obj[name] = name);
                                    selectedDiagnoses = obj;
                                } else {
                                    selectedDiagnoses = parsedDiagnoses;
                                }
                            } catch(e){ selectedDiagnoses = {}; }

                            document.getElementById('discharge_date').value = pd.discharge_date || '';
                            document.getElementById('discharge_time').value = pd.discharge_time || '';
                            document.getElementById('discharge_status').value = pd.discharge_status || '';
                            document.getElementById('certifying_doctor').value = pd.certifying_doctor_id || '';
                            document.getElementById('discharge_summary').value = pd.discharge_summary || '';
                            document.getElementById('medication_at_discharge').value = pd.medication_at_discharge || '';
                            document.getElementById('follow_up_instructions').value = pd.follow_up_instructions || '';
                            document.getElementById('cause').value = pd.cause || '';
                            document.getElementById('payment_clearance_status').value = pd.payment_clearance_status || '';
                            document.getElementById('discount').value = pd.discount || 0;
                            document.getElementById('amount_paid').value = pd.amount_paid || 0;

                            // Re-enable print slip button if disabled
                            document.getElementById('print_slip_btn').disabled = false;
                        } else {
                            selectedConsultants = data.inpatient_detail?.consultant_visits || {};
                            selectedDiagnoses = data.inpatient_detail?.diagnoses || {};
                        }

                        renderConsultantsList();
                        renderDiagnosesList();
                        calculateAllTotals();
                        // New: Focus on the "Cause" field after patient data is populated
                        document.getElementById('cause').focus();
                    } else {
                        alert('No indoor patient found with this MR Number.');
                        clearPatientFields();
                    }
                } catch (error) {
                    console.error('Error fetching indoor patient data:', error);
                    alert('Failed to fetch patient data. Please try again.');
                    clearPatientFields();
                }
            }

            function clearPatientFields() {
                patientData = {};
                patientNameInput.value = '';
                ageInput.value = '';
                patientTypeInput.value = '';
                isWelfareInput.value = '0';
                admissionDateInput.value = '';
                admissionFeeInput.value = '';
                advanceFeeInput.value = '';
                consultantsListDiv.innerHTML = '';
                diagnosesListDiv.innerHTML = '';
                selectedConsultants = {};
                selectedDiagnoses = {};
                calculateAllTotals();
            }

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
                for (const id in selectedConsultants) {
                    const visit = selectedConsultants[id];
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

                    const qtyInput = consultantItem.querySelector('.qty-input');
                    const discountInput = consultantItem.querySelector('.discount-input');
                    qtyInput.addEventListener('input', () => updateConsultantTotal(id, consultantItem));
                    discountInput.addEventListener('input', () => updateConsultantTotal(id, consultantItem));
                }
            }

            function addConsultantToList(id, name, fee) {
                if (selectedConsultants[id]) {
                    // If the consultant already exists, update their details
                    selectedConsultants[id].qty += 1;
                    selectedConsultants[id].total += parseFloat(fee);
                } else {
                    // Otherwise, add the new consultant
                    selectedConsultants[id] = {
                        id: id,
                        name: name,
                        fee: parseFloat(fee),
                        qty: 1,
                        discount: 0,
                        total: parseFloat(fee)
                    };
                }

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
                const fee = selectedConsultants[id].fee;

                const total = (fee * qty) - discount;
                consultantItem.querySelector('.total-amount-cell').textContent = total.toFixed(2);

                selectedConsultants[id].qty = qty;
                selectedConsultants[id].discount = discount;
                selectedConsultants[id].total = total;

                calculateAllTotals();
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

            // --- Diagnosis Search and List Management ---
            diagnosisSearchInput.addEventListener('input', debounce(async function() {
                const query = this.value;
                if (query.length > 2) {
                    const url = `/api/search-diagnosis?query=${query}`;
                    const diagnoses = await fetchData(url);
                    displayDiagnosisResults(diagnoses);
                } else {
                    diagnosisResultsDiv.classList.add('hidden');
                }
            }, 300));

            diagnosisSearchInput.addEventListener('focus', function() {
                if (this.value.length > 2) {
                    diagnosisResultsDiv.classList.remove('hidden');
                }
            });

            document.addEventListener('click', function(event) {
                if (!diagnosisSearchInput.contains(event.target) && !diagnosisResultsDiv.contains(event.target)) {
                    diagnosisResultsDiv.classList.add('hidden');
                }
            });

            function displayDiagnosisResults(diagnoses) {
                let html = '';
                if (diagnoses.length > 0) {
                    diagnoses.forEach(diagnosis => {
                        html += `<div class="autocomplete-option" data-id="${diagnosis.id}" data-name="${diagnosis.name}">${diagnosis.name}</div>`;
                    });
                } else {
                    html = '<div class="autocomplete-empty">No diagnoses found.</div>';
                }
                diagnosisResultsDiv.innerHTML = html;
                diagnosisResultsDiv.classList.remove('hidden');
            }

            diagnosisResultsDiv.addEventListener('click', function(event) {
                const item = event.target.closest('div');
                if (item) {
                    const diagnosisId = item.dataset.id;
                    const diagnosisName = item.dataset.name;

                    if (!selectedDiagnoses[diagnosisId]) {
                        addDiagnosisToList(diagnosisId, diagnosisName);
                    }

                    diagnosisSearchInput.value = '';
                    diagnosisResultsDiv.classList.add('hidden');
                }
            });

            function addDiagnosisToList(id, name) {
                const span = document.createElement('span');
                span.className = 'chip';
                span.innerHTML = `
                    <span>${name}</span>
                    <button type="button" class="remove-diagnosis-btn chip-remove" data-id="${id}" aria-label="Remove ${name}">&times;</button>
                `;
                diagnosesListDiv.appendChild(span);
                selectedDiagnoses[id] = name;
            }

            function renderDiagnosesList() {
                diagnosesListDiv.innerHTML = '';
                for (const id in selectedDiagnoses) {
                    const name = selectedDiagnoses[id];
                    addDiagnosisToList(id, name);
                }
            }

            diagnosesListDiv.addEventListener('click', function(event) {
                if (event.target.classList.contains('remove-diagnosis-btn')) {
                    const id = event.target.dataset.id;
                    delete selectedDiagnoses[id];
                    event.target.closest('span').remove();
                }
            });

            // --- Anesthesia Checkbox ---
            isAnesthesiaCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    anesthesiaTypeField.classList.remove('hidden');
                } else {
                    anesthesiaTypeField.classList.add('hidden');
                    document.getElementById('anesthesia_type').value = '';
                }
            });

            // --- Billing Calculations ---
            function calculateAllTotals() {
                let totalConsultantFees = 0;
                for (const id in selectedConsultants) {
                    totalConsultantFees += selectedConsultants[id].total;
                }

                // Update consultant fees display
                const consultantFeesTotalInput = document.getElementById('consultant_fees_total');
                if(consultantFeesTotalInput) consultantFeesTotalInput.value = totalConsultantFees.toFixed(2);

                // Room Rent Calculation
                let roomDays = 0;
                let roomRent = 0;

                if (admissionDatetime) {
                    const dischargeDateStr = document.getElementById('discharge_date').value;
                    const dischargeTimeStr = document.getElementById('discharge_time').value;

                    if (dischargeDateStr && dischargeTimeStr) {
                        // Support for Safari which might need a different date format, though YYYY-MM-DDTHH:mm:ss is standard ISO
                        const start = new Date(admissionDatetime.replace(' ', 'T'));
                        const end = new Date(`${dischargeDateStr}T${dischargeTimeStr}:00`);

                        // Difference in hours
                        let diffMs = end - start;
                        if (diffMs < 0) diffMs = 0;

                        const diffHours = diffMs / (1000 * 60 * 60);
                        // Convert to 24-hour periods, rounded up. Minimum 1 day.
                        roomDays = Math.ceil(diffHours / 24);
                        if (roomDays === 0 && diffMs > 0) roomDays = 1; // If less than an hour, still 1 day
                        else if (roomDays === 0) roomDays = 1; // Default minimum

                        roomRent = roomDays * roomRentPerDay;
                    }
                }

                const roomDaysDisplay = document.getElementById('room_days_display');
                if(roomDaysDisplay) roomDaysDisplay.textContent = roomDays;

                const roomRentRateDisplay = document.getElementById('room_rent_rate_display');
                if(roomRentRateDisplay) roomRentRateDisplay.textContent = roomRentPerDay.toFixed(2);

                const roomDaysInput = document.getElementById('room_days');
                if(roomDaysInput) roomDaysInput.value = roomDays;

                const roomRentInput = document.getElementById('room_rent');
                if(roomRentInput) roomRentInput.value = roomRent.toFixed(2);

                const admissionFee = parseFloat(admissionFeeInput.value) || 0;
                const totalBill = totalConsultantFees + admissionFee + roomRent;
                totalBillInput.value = totalBill.toFixed(2);

                const discount = parseFloat(discountInput.value) || 0;
                const advanceFee = parseFloat(advanceFeeInput.value) || 0;
                const amountReceivable = totalBill - discount - advanceFee;
                amountReceivableInput.value = amountReceivable.toFixed(2);

                const amountPaid = parseFloat(amountPaidInput.value) || 0;
                const currentRemaining = amountReceivable - amountPaid;
                currentRemainingInput.value = currentRemaining.toFixed(2);
            }

            discountInput.addEventListener('input', calculateAllTotals);
            amountPaidInput.addEventListener('input', calculateAllTotals);
            document.getElementById('discharge_date').addEventListener('change', calculateAllTotals);
            document.getElementById('discharge_time').addEventListener('change', calculateAllTotals);

            // --- Form Submission ---
            const dischargeForm = document.getElementById('discharge_form');
            dischargeForm.addEventListener('submit', function(event) {
                // Manually add hidden inputs for dynamic data
                const hiddenConsultantsInput = document.createElement('input');
                hiddenConsultantsInput.type = 'hidden';
                hiddenConsultantsInput.name = 'consultants';
                hiddenConsultantsInput.value = JSON.stringify(Object.values(selectedConsultants));
                this.appendChild(hiddenConsultantsInput);

                const hiddenDiagnosesInput = document.createElement('input');
                hiddenDiagnosesInput.type = 'hidden';
                hiddenDiagnosesInput.name = 'diagnoses';
                hiddenDiagnosesInput.value = JSON.stringify(Object.values(selectedDiagnoses));
                this.appendChild(hiddenDiagnosesInput);
            });

            // --- Print Slip Logic ---
            printSlipBtn.addEventListener('click', () => {
                populateDischargeSlip();
                printDischargeSlip('discharge_slip_area');
            });

            function populateDischargeSlip() {
                // Patient & Admission
                document.getElementById('slip_mr_no').textContent = mrNoInput.value;
                document.getElementById('slip_patient_name').textContent = patientNameInput.value;
                document.getElementById('slip_age').textContent = ageInput.value;
                document.getElementById('slip_patient_type').textContent = patientTypeInput.value;
                document.getElementById('slip_cause').textContent = document.getElementById('cause').value;
                document.getElementById('slip_admission_date').textContent = admissionDateInput.value;

                // Discharge Details
                document.getElementById('slip_discharge_date').textContent = dischargeDateInput.value;
                document.getElementById('slip_discharge_status').textContent = dischargeStatusSelect.selectedIndex >= 0 ? dischargeStatusSelect.options[dischargeStatusSelect.selectedIndex].text : '';
                document.getElementById('slip_certifying_doctor').textContent = certifyingDoctorSelect.selectedIndex >= 0 ? certifyingDoctorSelect.options[certifyingDoctorSelect.selectedIndex].text : '';
                document.getElementById('slip_discharge_summary').textContent = dischargeSummaryTextarea.value;
                document.getElementById('slip_medication').textContent = document.getElementById('medication_at_discharge').value;
                document.getElementById('slip_follow_up').textContent = document.getElementById('follow_up_instructions').value;

                // Diagnoses (Concatenate them into a string)
                const diagnosesArray = Object.values(selectedDiagnoses);
                document.getElementById('slip_diagnoses').textContent = diagnosesArray.join(', ');

                // Populate Consultant Fees Table
                consultantFeesTableBody.innerHTML = ''; // Clear previous data
                let totalConsultantFees = 0;
                for (const id in selectedConsultants) {
                    const visit = selectedConsultants[id];
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="py-1 px-2 border border-gray-400">${visit.name}</td>
                        <td class="py-1 px-2 text-right border border-gray-400">${visit.qty}</td>
                        <td class="py-1 px-2 text-right border border-gray-400">{{ get_setting('currency_symbol', 'Rs') }}${visit.fee.toFixed(2)}</td>
                        <td class="py-1 px-2 text-right border border-gray-400">{{ get_setting('currency_symbol', 'Rs') }}${visit.total.toFixed(2)}</td>
                    `;
                    consultantFeesTableBody.appendChild(row);
                    totalConsultantFees += visit.total;

                    // If a discount exists, add a row for it
                    if (visit.discount > 0) {
                        const discountRow = document.createElement('tr');
                        discountRow.innerHTML = `
                            <td class="py-1 px-2 border-l border-gray-400 italic text-gray-500 pl-5">Discount:</td>
                            <td class="py-1 px-2 border-t border-r border-b border-gray-400 text-right text-gray-500"></td>
                            <td class="py-1 px-2 border-t border-b border-gray-400 text-right text-red-500"></td>
                            <td class="py-1 px-2 border-r border-gray-400 text-right text-red-500">-{{ get_setting('currency_symbol', 'Rs') }}${visit.discount.toFixed(2)}</td>
                        `;
                        consultantFeesTableBody.appendChild(discountRow);
                    }
                }

                // Populate Billing Summary
                slipTotalConsultantFees.textContent = `{{ get_setting('currency_symbol', 'Rs') }}${totalConsultantFees.toFixed(2)}`;
                slipAdmissionFee.textContent = `{{ get_setting('currency_symbol', 'Rs') }}${parseFloat(admissionFeeInput.value).toFixed(2)}`;
                document.getElementById('slip_room_rent').textContent = `{{ get_setting('currency_symbol', 'Rs') }}${document.getElementById('room_rent').value} (${document.getElementById('room_days').value} Days)`;
                document.getElementById('slip_total_bill').textContent = `{{ get_setting('currency_symbol', 'Rs') }}${totalBillInput.value}`;
                document.getElementById('slip_discount').textContent = `{{ get_setting('currency_symbol', 'Rs') }}${discountInput.value}`;
                document.getElementById('slip_advance_fee').textContent = `{{ get_setting('currency_symbol', 'Rs') }}${advanceFeeInput.value}`;
                document.getElementById('slip_amount_receivable').textContent = `{{ get_setting('currency_symbol', 'Rs') }}${amountReceivableInput.value}`;
                document.getElementById('slip_amount_paid').textContent = `{{ get_setting('currency_symbol', 'Rs') }}${amountPaidInput.value}`;
                document.getElementById('slip_current_remaining').textContent = `{{ get_setting('currency_symbol', 'Rs') }}${currentRemainingInput.value}`;
            }

            function printDischargeSlip(divId) {
                const printContents = document.getElementById(divId).innerHTML;
                const originalContents = document.body.innerHTML;

                // Create a new window for printing
                const printWindow = window.open('', '_blank');
                printWindow.document.open();
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <title>Discharge Slip</title>
                        <style>
                            body { font-family: 'Inter', sans-serif; margin: 0; padding: 20mm; font-size: 14px; }
                            h1, h2, h3 { color: #1f2937; margin: 0; }
                            p { margin: 0; }
                            .text-center { text-align: center; }
                            .mb-8 { margin-bottom: 2rem; }
                            .mb-2 { margin-bottom: 0.5rem; }
                            .mt-16 { margin-top: 4rem; }
                            .border-b { border-bottom: 1px solid #e5e7eb; }
                            .pb-4 { padding-bottom: 1rem; }
                            .pb-1 { padding-bottom: 0.25rem; }
                            .font-bold { font-weight: 700; }
                            .font-semibold { font-weight: 600; }
                            .text-sm { font-size: 0.875rem; }
                            .text-lg { font-size: 1.125rem; }
                            .text-xl { font-size: 1.25rem; }
                            .text-3xl { font-size: 1.875rem; }
                            .grid { display: grid; }
                            .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                            .gap-x-8 { gap-x: 2rem; }
                            .gap-y-2 { gap-y: 0.5rem; }
                            .col-span-2 { grid-column: span 2 / span 2; }
                            .border-t { border-top: 1px solid #6b7280; }
                            .pt-2 { padding-top: 0.5rem; }

                            /* Table-specific styles for printing */
                            table { border-collapse: collapse; width: 100%; }
                            th, td { border: 1px solid #9ca3af; padding: 0.5rem; text-align: left; }
                            th { background-color: #e5e7eb; }
                            .text-right { text-align: right; }
                            .italic { font-style: italic; }
                            .pl-5 { padding-left: 1.25rem; }
                            .text-gray-500 { color: #6b7280; }
                            .text-red-500 { color: #ef4444; }
                        </style>
                    </head>
                    <body>
                        ${printContents}
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            }
        });
    </script>
@endsection
