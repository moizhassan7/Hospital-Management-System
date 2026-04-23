@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Laboratory Patient Registration</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Laboratory Management
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
            <strong class="font-bold">Validation Error!</strong>
            <span class="block sm:inline">Please correct the following errors:</span>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="lab_patient_registration_form" action="{{ route('laboratory.patient_registration.store') }}" method="POST">
            @csrf
            {{-- This hidden input will hold the JSON string of selected tests --}}
            <input type="hidden" id="selected_tests_json_input" name="tests">

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No:</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001">
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., John Doe" required>
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-bold mb-2">Gender:</label>
                    <select id="gender" name="gender" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="contact_no" class="block text-gray-700 text-sm font-bold mb-2">Contact No:</label>
                    <input type="tel" id="contact_no" name="contact_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., +923xx-xxxxxxx">
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="number" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 30" min="0" required>
                </div>
                <div>
                    <label for="file_no" class="block text-gray-700 text-sm font-bold mb-2">File No:</label>
                    <input type="text" id="file_no" name="file_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., F-001">
                </div>
                <div>
                    <label for="priority" class="block text-gray-700 text-sm font-bold mb-2">Priority:</label>
                    <select id="priority" name="priority" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Priority</option>
                        <option value="Routine">Routine</option>
                        <option value="Urgent">Urgent</option>
                        <option value="STAT">STAT</option>
                    </select>
                </div>
                <div class="flex items-center mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="self_referred" id="self_referred" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 text-sm font-bold">Self-Referred</span>
                    </label>
                </div>
                <div>
                    <label for="refer_by_doctor_name" class="block text-gray-700 text-sm font-bold mb-2">Refer by Doctor:</label>
                    <select id="refer_by_doctor_name" name="refer_by_doctor_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Doctor</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->name }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Select Tests</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="select_test" class="block text-gray-700 text-sm font-bold mb-2">Select Test:</label>
                    <select id="select_test" name="select_test" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Select a Test --</option>
                        @foreach($tests as $test)
                            <option value="{{ $test->id }}"
                                    data-id="{{ $test->id }}"
                                    data-name="{{ $test->name }}"
                                    data-price="{{ $test->price }}"
                                    data-report-time="{{ $test->report_time }}"
                                    data-lab-share-percent="{{ $test->lab_share_percent }}"
                                    data-hospital-share-percent="{{ $test->hospital_share_percent }}">
                                {{ $test->name }} - Rs {{ number_format($test->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end justify-end">
                    <button type="button" id="add_test_to_table" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Add Test
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto mb-6">
                <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carry Out</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report (Hrs)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="selected_tests_table_body" class="divide-y divide-gray-200">
                        {{-- Tests will be added here dynamically by JavaScript --}}
                    </tbody>
                </table>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Billing Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="sub_total" class="block text-gray-700 text-sm font-bold mb-2">Sub Total (PKR):</label>
                    <input type="text" id="sub_total" name="sub_total" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="0.00" readonly>
                </div>
                <div>
                    <label for="discount" class="block text-gray-700 text-sm font-bold mb-2">Discount (PKR):</label>
                    <input type="number" id="discount" name="discount" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="0.00" min="0" step="0.01">
                </div>
                <div>
                    <label for="grand_total" class="block text-gray-700 text-sm font-bold mb-2">Grand Total (PKR):</label>
                    <input type="text" id="grand_total" name="grand_total" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="0.00" readonly>
                </div>
                <div>
                    <label for="paid_amount" class="block text-gray-700 text-sm font-bold mb-2">Paid (PKR):</label>
                    <input type="number" id="paid_amount" name="paid_amount" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="0.00" min="0" step="0.01">
                </div>
                <div>
                    <label for="due_amount" class="block text-gray-700 text-sm font-bold mb-2">Due (PKR):</label>
                    <input type="text" id="due_amount" name="due_amount" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="0.00" readonly>
                </div>
                <div>
                    <label for="previous_due" class="block text-gray-700 text-sm font-bold mb-2">Previous Due (PKR):</label>
                    <input type="number" id="previous_due" name="previous_due" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="0.00" min="0" step="0.01">
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" id="print_slip_btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Print Slip
                </button>
                <button type="button" id="new_registration_btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    New
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save
                </button>
            </div>
        </form>
    </div>

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

                selectedTests.forEach((test, index) => {
                    const row = selectedTestsTableBody.insertRow();
                    row.innerHTML = `
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${test.name}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">Rs ${test.price.toFixed(2)}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-green-600 carry-out-checkbox" data-test-id="${test.id}" ${test.carry_out ? 'checked' : ''}>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${test.report_time} Hrs</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-test-btn" data-test-id="${test.id}">Remove</button>
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
            toggleReferByDoctorFields();
            calculateBillingSummary();
        });
    </script>
@endsection
