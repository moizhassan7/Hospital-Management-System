@extends('layouts.app')

@section('page_title', 'Patient Registration')

@section('content')
    @include('partials.page-shell-start', [
        'title' => 'Pathology Patient Registration',
        'subtitle' => 'Register patient and book pathology tests',
        'backUrl' => route('pathology.index'),
        'backLabel' => 'Back to Pathology',
    ])

    <form id="lab_patient_registration_form" action="{{ route('laboratory.patient_registration.store') }}" method="POST">
        @csrf
        <input type="hidden" id="selected_tests_json_input" name="tests">

        <div class="hms-panel mb-5">
            <div class="hms-panel-header">
                <div>
                    <h3 class="hms-panel-title">Patient details</h3>
                    <p class="hms-panel-subtitle">Search by MR number or enter manually</p>
                </div>
            </div>
            <div class="hms-panel-body">
                <div class="hms-form-grid">
                    <x-form.field label="MR No" for="mr_no">
                        <input type="text" id="mr_no" name="mr_no" class="hms-input" placeholder="e.g., MRN001">
                    </x-form.field>
                    <x-form.field label="Patient name" for="patient_name" :required="true">
                        <input type="text" id="patient_name" name="patient_name" class="hms-input" placeholder="e.g., John Doe" required>
                    </x-form.field>
                    <x-form.field label="Gender" for="gender" :required="true">
                        <select id="gender" name="gender" class="hms-select" required>
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </x-form.field>
                    <x-form.field label="Contact no" for="contact_no">
                        <input type="tel" id="contact_no" name="contact_no" class="hms-input" placeholder="e.g., +923xx-xxxxxxx">
                    </x-form.field>
                    <x-form.field label="Age" for="age" :required="true">
                        <input type="number" id="age" name="age" class="hms-input" placeholder="e.g., 30" min="0" required>
                    </x-form.field>
                    <x-form.field label="File no" for="file_no">
                        <input type="text" id="file_no" name="file_no" class="hms-input" placeholder="e.g., F-001">
                    </x-form.field>
                    <x-form.field label="Priority" for="priority" :required="true">
                        <select id="priority" name="priority" class="hms-select" required>
                            <option value="">Select priority</option>
                            <option value="Routine">Routine</option>
                            <option value="Urgent">Urgent</option>
                            <option value="STAT">STAT</option>
                        </select>
                    </x-form.field>
                    <div class="hms-field flex justify-center">
                        <label class="hms-checkbox-row mt-6">
                            <input type="checkbox" name="self_referred" id="self_referred" class="hms-checkbox">
                            <span class="hms-checkbox-label">Self-referred</span>
                        </label>
                    </div>
                    <x-form.field label="Refer by doctor" for="refer_by_doctor_name">
                        <select id="refer_by_doctor_name" name="refer_by_doctor_name" class="hms-select">
                            <option value="">Select doctor</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->name }}">{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </x-form.field>
                </div>
            </div>
        </div>

        <div class="hms-panel mb-5">
            <div class="hms-panel-header">
                <div>
                    <h3 class="hms-panel-title">Select tests</h3>
                    <p class="hms-panel-subtitle">Add tests to the registration slip</p>
                </div>
            </div>
            <div class="hms-panel-body">
                <div class="hms-form-grid-2 mb-5">
                    <x-form.field label="Test" for="select_test">
                        <select id="select_test" name="select_test" class="hms-select">
                            <option value="">— Select a test —</option>
                            @foreach($tests as $test)
                                <option value="{{ $test->id }}"
                                        data-id="{{ $test->id }}"
                                        data-name="{{ $test->name }}"
                                        data-price="{{ $test->price }}"
                                        data-report-time="{{ $test->report_time }}"
                                        data-lab-share-percent="{{ $test->lab_share_percent }}"
                                        data-hospital-share-percent="{{ $test->hospital_share_percent }}">
                                    {{ $test->name }} — Rs {{ number_format($test->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </x-form.field>
                    <div class="flex items-end">
                        <button type="button" id="add_test_to_table" class="hms-btn hms-btn-indigo">Add test</button>
                    </div>
                </div>

                <div class="hms-table-wrap">
                    <table class="hms-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Carry out</th>
                                <th>Report (hrs)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="selected_tests_table_body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="hms-panel mb-5">
            <div class="hms-panel-header">
                <h3 class="hms-panel-title">Billing summary</h3>
            </div>
            <div class="hms-panel-body">
                <div class="hms-form-grid">
                    <x-form.field label="Sub total (PKR)" for="sub_total">
                        <input type="text" id="sub_total" name="sub_total" class="hms-input hms-input-readonly" value="0.00" readonly>
                    </x-form.field>
                    <x-form.field label="Discount (PKR)" for="discount">
                        <input type="number" id="discount" name="discount" class="hms-input" value="0.00" min="0" step="0.01">
                    </x-form.field>
                    <x-form.field label="Grand total (PKR)" for="grand_total">
                        <input type="text" id="grand_total" name="grand_total" class="hms-input hms-input-readonly" value="0.00" readonly>
                    </x-form.field>
                    <x-form.field label="Paid (PKR)" for="paid_amount">
                        <input type="number" id="paid_amount" name="paid_amount" class="hms-input" value="0.00" min="0" step="0.01">
                    </x-form.field>
                    <x-form.field label="Due (PKR)" for="due_amount">
                        <input type="text" id="due_amount" name="due_amount" class="hms-input hms-input-readonly" value="0.00" readonly>
                    </x-form.field>
                    <x-form.field label="Previous due (PKR)" for="previous_due">
                        <input type="number" id="previous_due" name="previous_due" class="hms-input" value="0.00" min="0" step="0.01">
                    </x-form.field>
                </div>

                <div class="hms-form-actions">
                    <button type="button" id="print_slip_btn" class="hms-btn hms-btn-secondary">Print slip</button>
                    <button type="button" id="new_registration_btn" class="hms-btn hms-btn-danger">New</button>
                    <button type="submit" class="hms-btn hms-btn-primary">Save registration</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mrNoInput = document.getElementById('mr_no');
            const patientNameInput = document.getElementById('patient_name');
            const genderSelect = document.getElementById('gender');
            const contactNoInput = document.getElementById('contact_no');
            const ageInput = document.getElementById('age');
            const fileNoInput = document.getElementById('file_no');
            const selfReferredCheckbox = document.getElementById('self_referred');
            const referByDoctorNameSelect = document.getElementById('refer_by_doctor_name');
            const selectTestDropdown = document.getElementById('select_test');
            const addTestToTableBtn = document.getElementById('add_test_to_table');
            const selectedTestsTableBody = document.getElementById('selected_tests_table_body');
            const subTotalInput = document.getElementById('sub_total');
            const discountInput = document.getElementById('discount');
            const grandTotalInput = document.getElementById('grand_total');
            const paidAmountInput = document.getElementById('paid_amount');
            const dueAmountInput = document.getElementById('due_amount');
            const previousDueInput = document.getElementById('previous_due');
            const newRegistrationBtn = document.getElementById('new_registration_btn');
            const form = document.getElementById('lab_patient_registration_form');
            const selectedTestsJsonInput = document.getElementById('selected_tests_json_input');
            let selectedTests = [];

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
                        .then(response => response.status === 404 ? null : (response.ok ? response.json() : Promise.reject()))
                        .then(data => populatePatientDetails(data))
                        .catch(() => populatePatientDetails(null));
                }
            }

            function toggleReferByDoctorFields() {
                referByDoctorNameSelect.disabled = selfReferredCheckbox.checked;
                if (selfReferredCheckbox.checked) referByDoctorNameSelect.value = '';
            }

            function addTestToTable() {
                const selectedOption = selectTestDropdown.options[selectTestDropdown.selectedIndex];
                if (!selectedOption || selectedOption.value === "") { alert('Please select a test to add.'); return; }
                const testId = selectedOption.value;
                if (selectedTests.some(test => test.id === testId)) { alert('This test has already been added.'); return; }
                selectedTests.push({
                    id: testId,
                    name: selectedOption.getAttribute('data-name'),
                    price: parseFloat(selectedOption.getAttribute('data-price')),
                    report_time: selectedOption.getAttribute('data-report-time'),
                    carry_out: true
                });
                renderSelectedTests();
                calculateBillingSummary();
                selectTestDropdown.value = "";
            }

            function renderSelectedTests() {
                selectedTestsTableBody.innerHTML = '';
                selectedTests.forEach((test) => {
                    const row = selectedTestsTableBody.insertRow();
                    row.innerHTML = `
                        <td>${test.name}</td>
                        <td>Rs ${test.price.toFixed(2)}</td>
                        <td><input type="checkbox" class="hms-checkbox carry-out-checkbox" data-test-id="${test.id}" ${test.carry_out ? 'checked' : ''}></td>
                        <td>${test.report_time} Hrs</td>
                        <td><button type="button" class="hms-btn hms-btn-ghost hms-btn-sm text-red-600 remove-test-btn" data-test-id="${test.id}">Remove</button></td>
                    `;
                });
                document.querySelectorAll('.carry-out-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const testIndex = selectedTests.findIndex(t => t.id === this.dataset.testId);
                        if (testIndex !== -1) selectedTests[testIndex].carry_out = this.checked;
                        calculateBillingSummary();
                    });
                });
                document.querySelectorAll('.remove-test-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        selectedTests = selectedTests.filter(test => test.id !== this.dataset.testId);
                        renderSelectedTests();
                        calculateBillingSummary();
                    });
                });
            }

            function calculateBillingSummary() {
                const subTotal = selectedTests.filter(t => t.carry_out).reduce((sum, t) => sum + t.price, 0);
                const discount = parseFloat(discountInput.value) || 0;
                const paid = parseFloat(paidAmountInput.value) || 0;
                const previousDue = parseFloat(previousDueInput.value) || 0;
                const grandTotal = subTotal - discount;
                subTotalInput.value = subTotal.toFixed(2);
                grandTotalInput.value = grandTotal.toFixed(2);
                paidAmountInput.value = paid.toFixed(2);
                dueAmountInput.value = ((grandTotal + previousDue) - paid).toFixed(2);
            }

            function resetForm() {
                form.reset();
                selectedTests = [];
                renderSelectedTests();
                calculateBillingSummary();
                toggleReferByDoctorFields();
            }

            mrNoInput.addEventListener('input', fetchPatientDetails);
            selfReferredCheckbox.addEventListener('change', toggleReferByDoctorFields);
            addTestToTableBtn.addEventListener('click', addTestToTable);
            discountInput.addEventListener('input', calculateBillingSummary);
            paidAmountInput.addEventListener('input', calculateBillingSummary);
            previousDueInput.addEventListener('input', calculateBillingSummary);
            newRegistrationBtn.addEventListener('click', resetForm);
            document.getElementById('print_slip_btn').addEventListener('click', () => alert('Simulating Print Slip action.'));
            form.addEventListener('submit', function(event) {
                if (selectedTests.length === 0) { event.preventDefault(); alert('Please add at least one test before saving.'); return; }
                selectedTestsJsonInput.value = JSON.stringify(selectedTests);
            });
            toggleReferByDoctorFields();
            calculateBillingSummary();
        });
    </script>
@endsection
