@extends('layouts.app')

@section('title', 'Book appointment')

@section('content')
@php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="Book appointment" subtitle="Schedule a future OPD visit. Saving without payment stores a provisional token; collecting the fee reserves it.">
        <x-slot:actions>
            <a href="{{ route('patients.appointments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="clipboard" />Appointments
            </a>
            <a href="{{ route('patients.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Patients
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form id="booking_form" action="{{ route('patients.store_appointment') }}" method="POST" class="max-w-[1120px]">
        @csrf
        <input type="hidden" name="intent" id="intent" value="save">
        <input type="hidden" name="idempotency_key" id="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

        <x-ui.panel flush>
            <x-ui.form-section title="Patient" description="Load the patient by MR number." icon="user">
                <div class="space-y-4">
                    <x-ui.field label="MR number" for="mr_number" error="mr_number" hint="Press Enter to load the patient's details." required class="max-w-xs">
                        <input type="text" id="mr_number" name="mr_number" @class(['input input-mono', 'is-invalid' => $errors->has('mr_number')]) placeholder="e.g. MRN001" value="{{ old('mr_number') }}" autocomplete="off" required>
                    </x-ui.field>

                    <div class="patient-band shadow-none" role="group" aria-label="Loaded patient">
                        <div class="band-cell band-cell-grow">
                            <label for="patient_name" class="band-label">Patient name <span class="field-tag ml-0">Auto</span></label>
                            <input type="text" id="patient_name" name="patient_name" class="band-input" placeholder="No patient loaded yet" value="{{ old('patient_name') }}" readonly>
                        </div>
                        <div class="band-cell w-28">
                            <label for="age" class="band-label">Age</label>
                            <input type="text" id="age" name="age" class="band-input" placeholder="—" value="{{ old('age') }}" readonly>
                        </div>
                        <div class="band-cell w-32">
                            <label for="gender" class="band-label">Gender</label>
                            <input type="text" id="gender" name="gender" class="band-input" placeholder="—" value="{{ old('gender') }}" readonly>
                        </div>
                    </div>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Appointment" description="Doctor, date and a suggested token. You may edit the number; the server checks it again on save." icon="calendar">
                <div class="form-grid">
                    <x-ui.field label="Appointment no." for="appointment_number" tag="Auto" class="md:col-span-4 xl:col-span-3">
                        <input type="text" id="appointment_number" name="appointment_number" class="input input-mono" placeholder="Issued on save" readonly>
                    </x-ui.field>
                    <x-ui.field label="Date" for="appointment_date" error="appointment_date" required class="md:col-span-4 xl:col-span-3">
                        <input type="date" id="appointment_date" name="appointment_date" @class(['input', 'is-invalid' => $errors->has('appointment_date')]) value="{{ old('appointment_date') }}" required>
                    </x-ui.field>
                    <x-ui.field label="Time" for="appointment_time" error="appointment_time" required class="md:col-span-4 xl:col-span-2">
                        <input type="time" id="appointment_time" name="appointment_time" @class(['input', 'is-invalid' => $errors->has('appointment_time')]) value="{{ old('appointment_time', '09:00') }}" required>
                    </x-ui.field>

                    <x-ui.field label="Doctor" for="doctor_code" error="doctor_code" required class="md:col-span-7 xl:col-span-6 md:col-start-1 xl:col-start-1">
                        <select id="doctor_code" name="doctor_code" @class(['select', 'is-invalid' => $errors->has('doctor_code')]) required>
                            <option value="">Select doctor</option>
                            @foreach ($doctors as $doctor)
                                <option
                                    value="{{ $doctor->code }}"
                                    data-name="{{ $doctor->name }}"
                                    data-department="{{ $doctor->department->name ?? '' }}"
                                    data-general-normal-fee="{{ $doctor->general_normal_fee }}"
                                    data-welfare-normal-fee="{{ $doctor->welfare_normal_fee }}"
                                    data-fee="{{ $doctor->fee }}"
                                    {{ old('doctor_code') == $doctor->code ? 'selected' : '' }}
                                >{{ $doctor->code }} — {{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Referred by" for="referred_by" error="referred_by" class="md:col-span-5 xl:col-span-4">
                        <input type="text" id="referred_by" name="referred_by" class="input" placeholder="e.g. Dr. Ali" value="{{ old('referred_by') }}" autocomplete="off">
                    </x-ui.field>
                    <x-ui.field label="Department" for="department_name" tag="Auto" class="md:col-span-4">
                        <input type="text" id="department_name" class="input" placeholder="From the selected doctor" readonly>
                    </x-ui.field>
                    <x-ui.field label="Token number" for="token_number" error="token_number" hint="Suggested from guaranteed reservations for this doctor and date. You may type another number." required class="md:col-span-4">
                        <input type="number" id="token_number" name="token_number" min="1" step="1" @class(['input input-mono input-total text-center', 'is-invalid' => $errors->has('token_number')]) value="{{ old('token_number') }}" required>
                        <p id="token_feedback" class="field-hint"></p>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Payment" description="Leave unpaid to store a provisional token. Collecting the full fee reserves it." icon="banknote">
                <div class="form-grid">
                    <x-ui.field label="Consultation fee" for="doctor_fee" tag="Auto" class="md:col-span-4">
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="doctor_fee" name="doctor_fee" class="input input-money" value="{{ old('doctor_fee', '0.00') }}" min="0" step="0.01" readonly>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Amount paid" for="amount_paid" error="amount_paid" class="md:col-span-4">
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="amount_paid" name="amount_paid" class="input input-money" value="{{ old('amount_paid', '0.00') }}" min="0" step="0.01">
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Payment method" for="payment_method" class="md:col-span-4">
                        <select id="payment_method" name="payment_method" class="select">
                            <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                            <option value="bank" @selected(old('payment_method') === 'bank')>Bank</option>
                            <option value="cheque" @selected(old('payment_method') === 'cheque')>Cheque</option>
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Payment status" for="payment_status_label" tag="Auto" class="md:col-span-6">
                        <input type="text" id="payment_status_label" class="input" value="UNPAID — PROVISIONAL TOKEN" readonly>
                    </x-ui.field>
                </div>
            </x-ui.form-section>

            <x-ui.form-section title="Booking summary" icon="clipboard">
                <dl id="booking_summary" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 text-sm">
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Patient</dt><dd id="summary_patient" class="font-semibold">—</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Doctor</dt><dd id="summary_doctor" class="font-semibold">—</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Appointment date</dt><dd id="summary_date" class="font-semibold">—</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Token</dt><dd id="summary_token" class="font-semibold">—</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Fee status</dt><dd id="summary_fee" class="font-semibold">Unpaid</dd></div>
                    <div><dt class="text-[11px] font-semibold uppercase tracking-wider text-fg-muted">Reservation</dt><dd id="summary_reservation" class="font-semibold">Provisional</dd></div>
                </dl>
            </x-ui.form-section>

            <x-slot:footer class="is-sticky">
                <a href="{{ route('patients.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" id="book_btn" class="btn btn-secondary" data-intent="save" data-busy-label="Saving…">
                    <x-ui.icon name="calendar-plus" />Save appointment without payment
                </button>
                <button type="submit" id="pay_btn" class="btn btn-primary" data-intent="pay" data-busy-label="Collecting…">
                    <x-ui.icon name="banknote" />Collect fee &amp; confirm appointment
                </button>
            </x-slot:footer>
        </x-ui.panel>
    </form>

    <script>
        const mrNumberInput = document.getElementById('mr_number');
        const patientNameInput = document.getElementById('patient_name');
        const ageInput = document.getElementById('age');
        const genderInput = document.getElementById('gender');
        const doctorSelect = document.getElementById('doctor_code');
        const dateInput = document.getElementById('appointment_date');
        const tokenInput = document.getElementById('token_number');
        const tokenFeedback = document.getElementById('token_feedback');
        const feeInput = document.getElementById('doctor_fee');
        const amountPaidInput = document.getElementById('amount_paid');
        const paymentStatusLabel = document.getElementById('payment_status_label');
        const departmentInput = document.getElementById('department_name');
        const intentInput = document.getElementById('intent');
        const form = document.getElementById('booking_form');
        let patientData = null;
        let tokenManual = false;
        let tokenSuggestSeq = 0;

        async function fetchPatientDetails(mrNo) {
            try {
                const patient = await LC.json(`/api/get-by-mr-no/${encodeURIComponent(mrNo)}`);
                if (patient) {
                    patientData = patient;
                    patientNameInput.value = patient.name;
                    ageInput.value = patient.age;
                    genderInput.value = patient.gender;
                    applyFee();
                } else {
                    patientData = null;
                    patientNameInput.value = '';
                    ageInput.value = '';
                    genderInput.value = '';
                    LC.toast('No patient found with this MR number.', 'danger');
                }
                refreshSummary();
            } catch (error) {
                LC.toast(error.message || 'Failed to fetch patient data.', 'danger');
            }
        }

        function selectedDoctor() {
            const option = doctorSelect.options[doctorSelect.selectedIndex];
            if (!option || !option.value) return null;
            return option;
        }

        function applyFee() {
            const option = selectedDoctor();
            departmentInput.value = option ? (option.dataset.department || '') : '';
            let fee = 0;
            if (option && option.value) {
                if (patientData?.is_welfare) {
                    fee = parseFloat(option.dataset.welfareNormalFee || option.dataset.fee || '0') || 0;
                } else {
                    fee = parseFloat(option.dataset.generalNormalFee || option.dataset.fee || '0') || 0;
                }
            }
            feeInput.value = fee.toFixed(2);
            if (parseFloat(amountPaidInput.value || '0') === 0 || intentInput.value === 'save') {
                // leave amount as typed
            }
            refreshSummary();
        }

        async function suggestToken() {
            const option = selectedDoctor();
            if (!option?.value || !dateInput.value) {
                if (!tokenManual) tokenInput.value = '';
                return;
            }
            const seq = ++tokenSuggestSeq;
            try {
                const data = await LC.json(`/api/suggest-token?doctor_code=${encodeURIComponent(option.value)}&appointment_date=${encodeURIComponent(dateInput.value)}`);
                if (seq !== tokenSuggestSeq) return;
                if (!tokenManual) {
                    tokenInput.value = data.token_number;
                }
                await validateToken();
            } catch (error) {
                tokenFeedback.textContent = error.message || 'Could not suggest a token.';
            }
            refreshSummary();
        }

        async function validateToken() {
            const option = selectedDoctor();
            const token = parseInt(tokenInput.value, 10);
            if (!option?.value || !dateInput.value || !token) {
                tokenFeedback.textContent = '';
                return;
            }
            try {
                const data = await LC.json(`/api/validate-token?doctor_code=${encodeURIComponent(option.value)}&appointment_date=${encodeURIComponent(dateInput.value)}&token_number=${token}`);
                tokenFeedback.textContent = data.available
                    ? `Token #${token} is available to reserve if the fee is collected.`
                    : data.message + ' It can still be stored as a provisional number.';
                tokenFeedback.className = data.available ? 'field-hint' : 'field-error';
            } catch (error) {
                tokenFeedback.textContent = error.message;
                tokenFeedback.className = 'field-error';
            }
        }

        function refreshSummary() {
            const option = selectedDoctor();
            const fee = parseFloat(feeInput.value || '0');
            const paid = parseFloat(amountPaidInput.value || '0');
            const paying = intentInput.value === 'pay' || paid + 0.009 >= fee && fee > 0;
            document.getElementById('summary_patient').textContent = patientNameInput.value || '—';
            document.getElementById('summary_doctor').textContent = option ? option.dataset.name : '—';
            document.getElementById('summary_date').textContent = dateInput.value || '—';
            document.getElementById('summary_token').textContent = tokenInput.value ? '#' + tokenInput.value : '—';
            document.getElementById('summary_fee').textContent = paying ? 'Paid' : 'Unpaid';
            document.getElementById('summary_reservation').textContent = paying ? 'Reserved' : 'Provisional';
            paymentStatusLabel.value = paying ? 'PAID — RESERVED TOKEN' : 'UNPAID — PROVISIONAL TOKEN';
        }

        mrNumberInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                fetchPatientDetails(this.value);
            }
        });
        doctorSelect.addEventListener('change', () => { tokenManual = false; applyFee(); suggestToken(); });
        dateInput.addEventListener('change', () => { tokenManual = false; suggestToken(); });
        tokenInput.addEventListener('input', () => { tokenManual = true; validateToken(); refreshSummary(); });
        amountPaidInput.addEventListener('input', refreshSummary);

        form.querySelectorAll('[data-intent]').forEach((button) => {
            button.addEventListener('click', () => {
                intentInput.value = button.getAttribute('data-intent');
                if (intentInput.value === 'pay' && parseFloat(amountPaidInput.value || '0') <= 0) {
                    amountPaidInput.value = feeInput.value || '0.00';
                }
                refreshSummary();
            });
        });

        form.addEventListener('submit', () => refreshSummary());

        if (mrNumberInput.value) fetchPatientDetails(mrNumberInput.value);
        applyFee();
        if (doctorSelect.value && dateInput.value) suggestToken();
        refreshSummary();
    </script>
@endsection
