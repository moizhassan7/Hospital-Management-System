@extends('layouts.app')

@section('title', 'OPD registration')

@section('content')
@php $currency = get_setting('currency_symbol', 'Rs'); @endphp

<x-ui.page-header title="OPD registration" subtitle="Register walk-in patients for today's clinic. Saving issues a token and opens the slip for printing.">
    <x-slot:actions>
        <a href="{{ route('patients.index') }}" class="btn btn-ghost">
            <x-ui.icon name="arrow-left" />Patients
        </a>
        <a href="{{ route('patients.register') }}" class="btn btn-secondary">
            <x-ui.icon name="user-plus" />Register new patient
        </a>
    </x-slot:actions>
</x-ui.page-header>

<form id="outdoor_patient_form" action="{{ route('patients.store_outdoor') }}" method="POST">
    @csrf

    <div class="workspace">
        <div class="stack">
            <x-ui.panel title="Patient" description="Load the patient by MR number." icon="user" flush>
                <div class="panel-body space-y-4">
                    <x-ui.field label="MR number" for="mr_number" hint="Press Enter to load. Ctrl + F searches by name, CNIC or mobile." required>
                        <div class="flex max-w-md gap-2">
                            <input type="text" id="mr_number" name="mr_number" class="input input-mono" placeholder="e.g. MRN001" autocomplete="off" required>
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
                        <div class="band-cell w-24">
                            <label for="age" class="band-label">Age</label>
                            <input type="text" id="age" name="age" class="band-input" placeholder="—" readonly>
                        </div>
                        <div class="band-cell w-28">
                            <label for="gender" class="band-label">Gender</label>
                            <input type="text" id="gender" name="gender" class="band-input" placeholder="—" readonly>
                        </div>
                    </div>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Consultation" description="The doctor the patient will see today." icon="stethoscope" flush>
                <div class="panel-body">
                    <div class="form-grid">
                        <x-ui.field label="Doctor" for="doctor_code" hint="Press Enter on this field to search consultants." required class="md:col-span-7">
                            <select id="doctor_code" name="doctor_code" class="select" required>
                                <option value="">Select doctor</option>
                                @foreach ($doctors as $doctor)
                                    {{-- Store all fee data on each option --}}
                                    <option
                                        value="{{ $doctor->code }}"
                                        data-name="{{ $doctor->name }}"
                                        data-general-normal-fee="{{ $doctor->general_normal_fee }}"
                                        data-welfare-normal-fee="{{ $doctor->welfare_normal_fee }}">
                                        {{ $doctor->code }} — {{ $doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <div class="flex items-start pt-[25px] md:col-span-5">
                            <button type="button" id="open_consultant_search" class="btn btn-secondary">
                                <x-ui.icon name="search" />Find consultant
                            </button>
                        </div>
                        <x-ui.field label="Doctor name" for="doctor_name" tag="Auto" class="md:col-span-7">
                            <input type="text" id="doctor_name" name="doctor_name" class="input" placeholder="Filled from the selected doctor" readonly>
                        </x-ui.field>
                        <x-ui.field label="Referred by" for="referred_by" class="md:col-span-5">
                            <input type="text" id="referred_by" name="referred_by" class="input" placeholder="e.g. Dr. Ali" autocomplete="off">
                        </x-ui.field>
                    </div>
                </div>
            </x-ui.panel>
        </div>

        <aside class="workspace-rail">
            <x-ui.panel title="Visit slip" icon="receipt" flush>
                <div class="panel-body space-y-4">
                    <x-ui.field label="Appointment no." for="appointment_number" tag="Auto">
                        <input type="text" id="appointment_number" name="appointment_number" class="input input-mono" value="AP-{{ date('Ymd') }}-{{ $dailySerial }}" readonly>
                    </x-ui.field>
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.field label="Date" for="registration_date">
                            <input type="date" id="registration_date" name="registration_date" class="input" value="{{ date('Y-m-d') }}" readonly>
                        </x-ui.field>
                        <x-ui.field label="Time" for="registration_time">
                            <input type="time" id="registration_time" name="registration_time" class="input" value="{{ date('H:i') }}" readonly>
                        </x-ui.field>
                    </div>
                    <x-ui.field label="Token no." for="token_number" hint="Suggested from reserved tokens for this doctor today. You may edit it.">
                        <input type="number" id="token_number" name="token_number" min="1" step="1" class="input input-mono input-total text-center" placeholder="Issued when a doctor is chosen">
                    </x-ui.field>
                    <input type="hidden" id="keep_original_token" name="keep_original_token" value="0">
                    <input type="hidden" id="idempotency_key" name="idempotency_key" value="">

                    <div class="ledger border-t border-line-soft pt-2">
                        <div class="ledger-row">
                            <span class="ledger-op" aria-hidden="true">+</span>
                            <label for="doctor_fee" class="ledger-label">Doctor fee <small>Normal or welfare rate</small></label>
                            <div class="input-group has-start">
                                <span class="input-affix is-start">{{ $currency }}</span>
                                <input type="number" id="doctor_fee" name="doctor_fee" class="input input-money" placeholder="0.00" min="0" step="0.01" readonly>
                            </div>
                        </div>
                        <div class="ledger-row ledger-total">
                            <span class="ledger-op" aria-hidden="true">=</span>
                            <label for="total_amount" class="ledger-label">Total amount</label>
                            <div class="input-group has-start">
                                <span class="input-affix is-start">{{ $currency }}</span>
                                <input type="text" id="total_amount" name="total_amount" class="input input-money input-total" placeholder="0.00" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <x-slot:footer class="flex-col items-stretch">
                    <button type="submit" id="save_btn" class="btn btn-primary btn-lg btn-block">
                        <x-ui.icon name="check" />Save and print slip
                    </button>
                    <button type="button" id="print_slip_btn" class="btn btn-secondary btn-block">
                        <x-ui.icon name="printer" />Reprint latest slip
                    </button>
                </x-slot:footer>
            </x-ui.panel>
        </aside>
    </div>
</form>

<x-ui.dialog id="patient_search_modal" title="Find patient" description="Type at least three characters of an MR number, name, CNIC or mobile number. Use ↑ ↓ and Enter to choose." size="lg" icon="search">
    <div class="input-group has-start mb-3">
        <span class="input-affix is-start"><x-ui.icon name="search" /></span>
        <input type="text" id="patient_search_query" class="input" placeholder="MR no., name, CNIC or mobile" autocomplete="off">
    </div>
    <div id="patient_search_results" class="result-list"></div>
    <x-slot:footer>
        <button type="button" id="close_patient_modal" class="btn btn-secondary" data-dialog-close>Close</button>
    </x-slot:footer>
</x-ui.dialog>

<x-ui.dialog id="token_confirm_modal" title="Confirm appointment token" description="Would you like to keep the originally assigned token or assign a different token?" size="lg" icon="hash">
    <div class="space-y-4">
        <dl class="grid gap-3 sm:grid-cols-2 text-sm">
            <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Patient</dt><dd id="token_dialog_patient" class="font-semibold">—</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">MR number</dt><dd id="token_dialog_mr">—</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Doctor</dt><dd id="token_dialog_doctor">—</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Original token</dt><dd id="token_dialog_original">—</dd></div>
            <div class="sm:col-span-2"><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Availability</dt><dd id="token_dialog_availability" class="field-hint">—</dd></div>
        </dl>
        <fieldset class="space-y-2">
            <label class="flex items-start gap-2">
                <input type="radio" name="outdoor_token_choice" id="token_choice_keep" value="keep">
                <span>Keep original token</span>
            </label>
            <label class="flex items-start gap-2">
                <input type="radio" name="outdoor_token_choice" id="token_choice_other" value="other">
                <span>Assign another token</span>
            </label>
        </fieldset>
        <x-ui.field label="Token number" for="token_dialog_number" required>
            <input type="number" min="1" step="1" id="token_dialog_number" class="input input-mono">
        </x-ui.field>
    </div>
    <x-slot:footer>
        <button type="button" class="btn btn-ghost" data-dialog-close>Cancel</button>
        <button type="button" class="btn btn-primary" id="token_dialog_confirm">Confirm token &amp; collect fee</button>
    </x-slot:footer>
</x-ui.dialog>

<x-ui.dialog id="consultant_search_modal" title="Find consultant" description="Type at least three characters of a code, name or department. Use ↑ ↓ and Enter to choose." size="lg" icon="stethoscope">
    <div class="input-group has-start mb-3">
        <span class="input-affix is-start"><x-ui.icon name="search" /></span>
        <input type="text" id="consultant_search_query" class="input" placeholder="Code, name or department" autocomplete="off">
    </div>
    <div id="consultant_search_results" class="result-list"></div>
    <x-slot:footer>
        <button type="button" id="close_consultant_modal" class="btn btn-secondary" data-dialog-close>Close</button>
    </x-slot:footer>
</x-ui.dialog>

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
        const generateTokenUrl = "{{ route('api.suggest_token') }}";

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

        let bookedVisit = null;

        async function checkBookedAppointment(mrNumber) {
            bookedVisit = null;
            document.getElementById('keep_original_token').value = '0';
            try {
                const result = await fetchData(`/api/check-booked-appointment?mr_number=${encodeURIComponent(mrNumber)}`);
                if (result.hasBookedAppointment) {
                    bookedVisit = result;
                    const details = result.appointmentDetails;
                    const option = doctorCodeSelect.querySelector(`option[value="${details.doctor_code}"]`);
                    if (option) {
                        option.selected = true;
                        doctorNameInput.value = details.doctor_name;
                    }
                    if (details.token_number) {
                        tokenNumberInput.value = details.token_number;
                    }
                    const status = result.is_paid ? 'PAID — reserved token' : 'UNPAID — provisional token';
                    alert(`This patient has a pre-booked appointment for today (${status}).
Doctor: ${details.doctor_name}
Token: #${details.token_number || '—'}
${result.is_paid ? 'Check-in will keep the reserved token. No further fee is requested.' : 'Collect the fee next. You will be asked to keep or change the original token.'}`);
                    calculateFees();
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

        document.getElementById('open_patient_search').addEventListener('click', async function() {
            patientSearchModal.classList.remove('hidden');
            setTimeout(() => patientSearchQueryInput.focus(), 100);
            await fetchAndDisplayPatients('');
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
                patientSearchResults.innerHTML = '<div class="result-message is-error">Failed to load search results.</div>';
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

        document.getElementById('open_consultant_search').addEventListener('click', async function() {
            consultantSearchModal.classList.remove('hidden');
            setTimeout(() => consultantSearchQueryInput.focus(), 100);
            await fetchAndDisplayConsultants('');
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
                let tableHtml = '<table class="table table-compact">';
                tableHtml += '<thead><tr><th>Code</th><th>Name</th><th>Department</th></tr></thead>';
                tableHtml += '<tbody>';

                if (doctors.length > 0) {
                    doctors.forEach(doctor => {
                        tableHtml += `
                                <tr class="search-result-row" data-doctor-code="${doctor.code}" tabindex="0">
                                    <td><span class="id">${doctor.code}</span></td>
                                    <td class="cell-strong">${doctor.name}</td>
                                    <td class="muted">${doctor.department.name || 'N/A'}</td>
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
                consultantSearchResults.innerHTML = '<div class="result-message is-error">Failed to load search results.</div>';
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
                    const date = registrationDateInput.value;
                    const url = `${generateTokenUrl}?doctor_code=${encodeURIComponent(doctorCode)}&appointment_date=${encodeURIComponent(date)}`;
                    const response = await fetchData(url);
                    tokenNumberInput.value = response.token_number;
                } catch (error) {
                    console.error('Error fetching token number:', error);
                    tokenNumberInput.value = '';
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

        function openTokenConfirmDialog() {
            const details = bookedVisit.appointmentDetails;
            const original = bookedVisit.token?.preferred || details.token_number;
            const available = !!bookedVisit.token?.available;
            const suggestion = bookedVisit.token?.suggestion;
            document.getElementById('token_dialog_patient').textContent = details.patient_name;
            document.getElementById('token_dialog_mr').textContent = details.mr_number;
            document.getElementById('token_dialog_doctor').textContent = details.doctor_name;
            document.getElementById('token_dialog_original').textContent = original ? '#' + original : '—';
            document.getElementById('token_dialog_availability').textContent = available
                ? 'Token available.'
                : (bookedVisit.token?.conflict || 'Original token is not available.');
            const keepRadio = document.getElementById('token_choice_keep');
            const otherRadio = document.getElementById('token_choice_other');
            const numberInput = document.getElementById('token_dialog_number');
            keepRadio.disabled = !available;
            keepRadio.checked = available;
            otherRadio.checked = !available;
            numberInput.value = available ? original : suggestion;
            numberInput.readOnly = available;
            keepRadio.onchange = otherRadio.onchange = () => {
                if (keepRadio.checked && available) {
                    numberInput.value = original;
                    numberInput.readOnly = true;
                } else {
                    numberInput.value = suggestion;
                    numberInput.readOnly = false;
                }
            };
            LC.openDialog('token_confirm_modal');
        }

        document.getElementById('token_dialog_confirm').addEventListener('click', () => {
            const keepRadio = document.getElementById('token_choice_keep');
            const numberInput = document.getElementById('token_dialog_number');
            tokenNumberInput.value = numberInput.value;
            document.getElementById('keep_original_token').value = keepRadio.checked ? '1' : '0';
            outdoorPatientForm.dataset.tokenConfirmed = '1';
            LC.closeDialog('token_confirm_modal');
            outdoorPatientForm.requestSubmit();
        });

        // --- Form Actions ---
        outdoorPatientForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (bookedVisit && !bookedVisit.is_paid && !outdoorPatientForm.dataset.tokenConfirmed) {
                openTokenConfirmDialog();
                return;
            }

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
                keep_original_token: document.getElementById('keep_original_token').value === '1',
                idempotency_key: document.getElementById('idempotency_key').value || `opd-${Date.now()}`,
            };

            try {
                const response = await sendData(outdoorPatientForm.action, 'POST', appointmentData);
                alert(response.message);

                // Open the receipt in a new popup window tailored for thermal printer
                if (response.receipt_url) {
                    window.open(response.receipt_url, 'ReceiptWindow', 'width=350,height=600');
                }

                // Redirect back to clear the form
                window.location.href = response.redirect_url;
            } catch (error) {
                console.error('Error registering patient:', error);
                alert('An error occurred while registering the patient. Please check the console for details.');
            }
        });

        printSlipBtn.addEventListener('click', async () => {
            const mrNo = mrNumberInput.value.trim();
            if (!mrNo) {
                alert('Please enter a Patient MR Number first to reprint their latest slip, or save a new registration to print a new slip.');
                return;
            }
            try {
                const response = await fetch(`/api/latest-opd-appointment/${mrNo}`);
                const result = await response.json();
                if (result.success) {
                    window.open(`/patients/outdoor-receipt/${result.appointment_number}`, 'ReceiptWindow', 'width=350,height=600');
                } else {
                    alert('No previous OPD slip found for this patient.');
                }
            } catch (e) {
                console.error('Error fetching previous slip:', e);
                alert('An error occurred while trying to fetch the slip.');
            }
        });
        // --- Initial setup on page load ---
        calculateFees();
    });
</script>
@endsection
