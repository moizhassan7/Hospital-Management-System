@extends('layouts.app')

@section('title', ($category ?? 'Laboratory') . ' patient registration')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
        $hubRoute = $category == 'Pathology' ? route('pathology.index') : ($category == 'Radiology' ? route('radiology.index') : route('laboratory.index'));
    @endphp

    <x-ui.page-header :title="($category ?? 'Laboratory') . ' patient registration'" subtitle="Book tests for a patient, set the priority and collect payment.">
        <x-slot:actions>
            <a href="{{ $hubRoute }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />{{ $category ?? 'Laboratory' }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form id="lab_patient_registration_form" action="{{ route('laboratory.patient_registration.store') }}" method="POST">
        @csrf
        {{-- This hidden input will hold the JSON string of selected tests --}}
        <input type="hidden" id="selected_tests_json_input" name="tests">

        <div class="workspace">
            <div class="stack">
                <x-ui.panel title="Patient" description="Typing a registered MR number fills the details; otherwise enter them by hand." icon="user" flush>
                    <div class="panel-body">
                        <div class="form-grid">
                            <x-ui.field label="MR number" for="mr_no" error="mr_no" class="md:col-span-4 xl:col-span-3">
                                <input type="text" id="mr_no" name="mr_no" class="input input-mono" placeholder="e.g. MRN001" value="{{ old('mr_no') }}" autocomplete="off">
                            </x-ui.field>
                            <x-ui.field label="Patient name" for="patient_name" error="patient_name" required class="md:col-span-8 xl:col-span-6">
                                <input type="text" id="patient_name" name="patient_name" @class(['input', 'is-invalid' => $errors->has('patient_name')]) placeholder="Full name" value="{{ old('patient_name') }}" autocomplete="off" required>
                            </x-ui.field>
                            <x-ui.field label="File no." for="file_no" error="file_no" class="md:col-span-4 xl:col-span-3">
                                <input type="text" id="file_no" name="file_no" class="input input-mono" placeholder="F-001" value="{{ old('file_no') }}">
                            </x-ui.field>
                            <x-ui.field label="Gender" for="gender" error="gender" required class="md:col-span-4 xl:col-span-3 xl:col-start-1">
                                <select id="gender" name="gender" @class(['select', 'is-invalid' => $errors->has('gender')]) required>
                                    <option value="">Select gender</option>
                                    <option value="Male" @selected(old('gender') == 'Male')>Male</option>
                                    <option value="Female" @selected(old('gender') == 'Female')>Female</option>
                                    <option value="Other" @selected(old('gender') == 'Other')>Other</option>
                                </select>
                            </x-ui.field>
                            <x-ui.field label="Age" for="age" error="age" required class="md:col-span-3 xl:col-span-2">
                                <div class="input-group has-end">
                                    <input type="number" id="age" name="age" @class(['input input-num', 'is-invalid' => $errors->has('age')]) placeholder="30" min="0" value="{{ old('age') }}" required>
                                    <span class="input-affix is-end">yrs</span>
                                </div>
                            </x-ui.field>
                            <x-ui.field label="Contact no." for="contact_no" error="contact_no" class="md:col-span-5 xl:col-span-4">
                                <input type="tel" id="contact_no" name="contact_no" class="input tabular-nums" placeholder="+923xx-xxxxxxx" value="{{ old('contact_no') }}">
                            </x-ui.field>
                        </div>
                    </div>
                </x-ui.panel>

                <x-ui.panel title="Priority & referral" icon="alert-triangle" flush>
                    <div class="panel-body">
                        <div class="form-grid">
                            <x-ui.field label="Priority" error="priority" required class="md:col-span-12">
                                <div class="segmented" role="radiogroup" aria-label="Priority">
                                    @foreach (['Routine', 'Urgent', 'STAT'] as $level)
                                        <label class="segmented-option">
                                            <input type="radio" name="priority" id="priority_{{ strtolower($level) }}" value="{{ $level }}" @checked(old('priority') == $level) required>
                                            <span>{{ $level }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </x-ui.field>
                            <div class="md:col-span-5">
                                <label for="self_referred" class="option-row h-full">
                                    <span>
                                        <span class="option-row-title block">Self-referred</span>
                                        <span class="option-row-desc block">No referring doctor.</span>
                                    </span>
                                    <input type="checkbox" name="self_referred" id="self_referred" class="switch">
                                </label>
                            </div>
                            <x-ui.field label="Referred by doctor" for="refer_by_doctor_name" error="refer_by_doctor_name" class="md:col-span-7">
                                <select id="refer_by_doctor_name" name="refer_by_doctor_name" class="select">
                                    <option value="">Select doctor</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->name }}" @selected(old('refer_by_doctor_name') == $doctor->name)>{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                            </x-ui.field>
                        </div>
                    </div>
                </x-ui.panel>

                <x-ui.panel title="Tests" description="Untick “Carry out” to keep a test on the slip without charging it." icon="test-tube" flush>
                    <div class="table-toolbar">
                        <label for="select_test" class="sr-only">Select test</label>
                        <select id="select_test" name="select_test" class="select max-w-md flex-1">
                            <option value="">Select a test to add</option>
                            @foreach ($tests as $test)
                                <option value="{{ $test->id }}"
                                        data-id="{{ $test->id }}"
                                        data-name="{{ $test->name }}"
                                        data-price="{{ $test->price }}"
                                        data-report-time="{{ $test->report_time }}"
                                        data-lab-share-percent="{{ $test->lab_share_percent }}"
                                        data-hospital-share-percent="{{ $test->hospital_share_percent }}">
                                    {{ $test->name }} — {{ $currency }} {{ number_format($test->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" id="add_test_to_table" class="btn btn-primary">
                            <x-ui.icon name="plus" />Add test
                        </button>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Test</th>
                                    <th class="num">Price</th>
                                    <th>Carry out</th>
                                    <th class="num">Report in</th>
                                    <th class="actions"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody id="selected_tests_table_body">
                                {{-- Tests will be added here dynamically by JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                </x-ui.panel>
            </div>

            <aside class="workspace-rail">
                <x-ui.panel title="Billing" icon="receipt" flush>
                    <div class="panel-body pt-3">
                        <div class="ledger">
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true"></span>
                                <label for="sub_total" class="ledger-label">Sub total <small>Tests marked carry out</small></label>
                                <input type="text" id="sub_total" name="sub_total" class="input input-money" value="0.00" readonly>
                            </div>
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">−</span>
                                <label for="discount" class="ledger-label">Discount</label>
                                <input type="number" id="discount" name="discount" class="input input-money" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="ledger-row ledger-total">
                                <span class="ledger-op" aria-hidden="true">=</span>
                                <label for="grand_total" class="ledger-label">Grand total</label>
                                <input type="text" id="grand_total" name="grand_total" class="input input-money font-bold" value="0.00" readonly>
                            </div>
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">+</span>
                                <label for="previous_due" class="ledger-label">Previous due</label>
                                <input type="number" id="previous_due" name="previous_due" class="input input-money" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="ledger-row">
                                <span class="ledger-op" aria-hidden="true">−</span>
                                <label for="paid_amount" class="ledger-label">Paid</label>
                                <input type="number" id="paid_amount" name="paid_amount" class="input input-money" value="0.00" min="0" step="0.01">
                            </div>
                            <div class="ledger-row ledger-total">
                                <span class="ledger-op" aria-hidden="true">=</span>
                                <label for="due_amount" class="ledger-label">Due</label>
                                <input type="text" id="due_amount" name="due_amount" class="input input-money input-total" value="0.00" readonly>
                            </div>
                        </div>
                    </div>
                    <x-slot:footer class="flex-col items-stretch">
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <x-ui.icon name="save" />Save registration
                        </button>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" id="print_slip_btn" class="btn btn-secondary"><x-ui.icon name="printer" />Print slip</button>
                            <button type="button" id="new_registration_btn" class="btn btn-ghost"><x-ui.icon name="refresh" />New</button>
                        </div>
                    </x-slot:footer>
                </x-ui.panel>
            </aside>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Patient Details Section Elements ---
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const genderSelect = document.getElementById('gender');
            const contactNoInput = document.getElementById('contact_no');
            const ageInput = document.getElementById('age');
            const fileNoInput = document.getElementById('file_no');
            const selfReferredCheckbox = document.getElementById('self_referred');
            const referByDoctorNameSelect = document.getElementById('refer_by_doctor_name');

            // --- Test Selection Section Elements ---
            const selectTestDropdown = document.getElementById('select_test');
            const addTestToTableBtn = document.getElementById('add_test_to_table');
            const selectedTestsTableBody = document.getElementById('selected_tests_table_body');

            // --- Billing Summary Section Elements ---
            const subTotalInput = document.getElementById('sub_total');
            const discountInput = document.getElementById('discount');
            const grandTotalInput = document.getElementById('grand_total');
            const paidAmountInput = document.getElementById('paid_amount');
            const dueAmountInput = document.getElementById('due_amount');
            const previousDueInput = document.getElementById('previous_due');

            // --- Action Buttons ---
            const printSlipBtn = document.getElementById('print_slip_btn');
            const newRegistrationBtn = document.getElementById('new_registration_btn');
            const form = document.getElementById('lab_patient_registration_form');
            const selectedTestsJsonInput = document.getElementById('selected_tests_json_input');

            let selectedTests = [];

            // --- Functions ---
            function populatePatientDetails(patient) {
                if (patient) {
                    patientNameInput.value = patient.name;
                    genderSelect.value = patient.gender;
                    contactNoInput.value = patient.mobile_number;
                    ageInput.value = patient.age;
                    fileNoInput.value = patient.file_no;
                } else {
                    alert('Patient not found. Please enter details manually.');
                    patientNameInput.value = '';
                    genderSelect.value = '';
                    contactNoInput.value = '';
                    ageInput.value = '';
                    fileNoInput.value = '';
                }
            }

            function fetchPatientDetails() {
                const mrNo = mrNoInput.value;
                if (mrNo.length > 0) {
                    const url = `{{ route('laboratory.api_search_patient', ['mrNo' => 'MR_NO_PLACEHOLDER']) }}`.replace('MR_NO_PLACEHOLDER', mrNo);

                    fetch(url)
                        .then(response => {
                            if (response.status === 404) {
                                return null;
                            }
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            populatePatientDetails(data);
                        })
                        .catch(error => {
                            console.error('Error fetching patient details:', error);
                            populatePatientDetails(null);
                        });
                }
            }

            function toggleReferByDoctorFields() {
                const isSelfReferred = selfReferredCheckbox.checked;
                referByDoctorNameSelect.disabled = isSelfReferred;
                if (isSelfReferred) {
                    referByDoctorNameSelect.value = '';
                }
            }

            function addTestToTable() {
                const selectedOption = selectTestDropdown.options[selectTestDropdown.selectedIndex];

                if (!selectedOption || selectedOption.value === "") {
                    alert('Please select a test to add.');
                    return;
                }

                const testId = selectedOption.value;
                if (selectedTests.some(test => test.id === testId)) {
                    alert('This test has already been added.');
                    return;
                }

                const testName = selectedOption.getAttribute('data-name');
                const testPrice = parseFloat(selectedOption.getAttribute('data-price'));
                const reportTime = selectedOption.getAttribute('data-report-time');

                const newTest = {
                    id: testId,
                    name: testName,
                    price: testPrice,
                    report_time: reportTime,
                    carry_out: true
                };
                selectedTests.push(newTest);
                renderSelectedTests();
                calculateBillingSummary();
                selectTestDropdown.value = "";
            }

            function renderSelectedTests() {
                selectedTestsTableBody.innerHTML = '';

                if (selectedTests.length === 0) {
                    selectedTestsTableBody.innerHTML = '<tr><td colspan="5" class="table-empty">No tests added yet. Choose a test above and select Add test.</td></tr>';
                }

                selectedTests.forEach((test, index) => {
                    const row = selectedTestsTableBody.insertRow();
                    row.innerHTML = `
                        <td class="cell-strong">${test.name}</td>
                        <td class="num">{{ get_setting('currency_symbol', 'Rs') }} ${test.price.toFixed(2)}</td>
                        <td>
                            <input type="checkbox" class="checkbox carry-out-checkbox" aria-label="Carry out ${test.name}" data-test-id="${test.id}" ${test.carry_out ? 'checked' : ''}>
                        </td>
                        <td class="num">${test.report_time} hrs</td>
                        <td class="actions">
                            <button type="button" class="btn btn-danger-ghost btn-sm remove-test-btn" data-test-id="${test.id}">Remove</button>
                        </td>
                    `;
                });

                document.querySelectorAll('.carry-out-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const testId = this.dataset.testId;
                        const testIndex = selectedTests.findIndex(t => t.id === testId);
                        if (testIndex !== -1) {
                            selectedTests[testIndex].carry_out = this.checked;
                        }
                        calculateBillingSummary();
                    });
                });

                document.querySelectorAll('.remove-test-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const testIdToRemove = this.dataset.testId;
                        selectedTests = selectedTests.filter(test => test.id !== testIdToRemove);
                        renderSelectedTests();
                        calculateBillingSummary();
                    });
                });
            }

            function calculateBillingSummary() {
                const carriedOutTests = selectedTests.filter(test => test.carry_out);
                let subTotal = carriedOutTests.reduce((sum, test) => sum + test.price, 0);

                const discount = parseFloat(discountInput.value) || 0;
                const paid = parseFloat(paidAmountInput.value) || 0;
                const previousDue = parseFloat(previousDueInput.value) || 0;

                const grandTotal = subTotal - discount;
                const currentDue = (grandTotal + previousDue) - paid;

                subTotalInput.value = subTotal.toFixed(2);
                grandTotalInput.value = grandTotal.toFixed(2);
                paidAmountInput.value = paid.toFixed(2);
                dueAmountInput.value = currentDue.toFixed(2);
            }

            function resetForm() {
                form.reset();
                selectedTests = [];
                renderSelectedTests();
                calculateBillingSummary();
                toggleReferByDoctorFields();
            }

            // --- Event Listeners ---
            mrNoInput.addEventListener('input', fetchPatientDetails);
            selfReferredCheckbox.addEventListener('change', toggleReferByDoctorFields);
            addTestToTableBtn.addEventListener('click', addTestToTable);
            discountInput.addEventListener('input', calculateBillingSummary);
            paidAmountInput.addEventListener('input', calculateBillingSummary);
            previousDueInput.addEventListener('input', calculateBillingSummary);
            newRegistrationBtn.addEventListener('click', resetForm);
            printSlipBtn.addEventListener('click', () => alert('Simulating Print Slip action.'));

            // Handle form submission
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                if (selectedTests.length === 0) {
                    alert('Please add at least one test before saving.');
                    return;
                }

                selectedTestsJsonInput.value = JSON.stringify(selectedTests);

                this.submit();
            });

            // Initial setup
            renderSelectedTests();
            toggleReferByDoctorFields();
            calculateBillingSummary();
        });
    </script>
@endsection
