@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Laboratory Patient Registration</h2>
        <a href="{{ route('laboratory.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Laboratory Management
        </a>
    </div>

    <!-- Patient Registration Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="lab_patient_registration_form" action="#" method="POST"> {{-- Action will be updated later for actual submission --}}
            @csrf {{-- Laravel CSRF token --}}

            <!-- Patient Details Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="mr_no" class="block text-gray-700 text-sm font-bold mb-2">MR No:</label>
                    <input type="text" id="mr_no" name="mr_no" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN001" required>
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
                    <label for="refer_by_doctor_name" class="block text-gray-700 text-sm font-bold mb-2">Refer by Doctor Name:</label>
                    <input type="text" id="refer_by_doctor_name" name="refer_by_doctor_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700  leading-tight focus:outline-none" placeholder="Doctor Name">
                </div>
            </div>

            <!-- Test Selection Section -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Select Tests</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="select_test" class="block text-gray-700 text-sm font-bold mb-2">Select Test:</label>
                    <select id="select_test" name="select_test" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Select a Test --</option>
                        {{-- Static Tests with prices, report times, and share percentages --}}
                        @php
                            $availableTests = [
                                ['id' => 'T001', 'name' => 'Complete Blood Count (CBC)', 'price' => 50.00, 'report_time' => 24, 'lab_share_percent' => 0.60, 'hospital_share_percent' => 0.40],
                                ['id' => 'T002', 'name' => 'Blood Glucose Fasting', 'price' => 30.00, 'report_time' => 6, 'lab_share_percent' => 0.70, 'hospital_share_percent' => 0.30],
                                ['id' => 'T003', 'name' => 'Urine Routine Examination', 'price' => 25.00, 'report_time' => 12, 'lab_share_percent' => 0.50, 'hospital_share_percent' => 0.50],
                                ['id' => 'T004', 'name' => 'X-Ray Chest PA View', 'price' => 120.00, 'report_time' => 1, 'lab_share_percent' => 0.80, 'hospital_share_percent' => 0.20],
                                ['id' => 'T005', 'name' => 'Thyroid Function Test (TFT)', 'price' => 80.00, 'report_time' => 48, 'lab_share_percent' => 0.65, 'hospital_share_percent' => 0.35],
                            ];
                        @endphp
                        @foreach($availableTests as $test)
                            <option value="{{ $test['id'] }}"
                                    data-name="{{ $test['name'] }}"
                                    data-price="{{ $test['price'] }}"
                                    data-report-time="{{ $test['report_time'] }}"
                                    data-lab-share-percent="{{ $test['lab_share_percent'] }}"
                                    data-hospital-share-percent="{{ $test['hospital_share_percent'] }}">
                                {{ $test['name'] }} - {{ number_format($test['price'], 2) }}
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

            <!-- Selected Tests Table -->
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carry Out</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report (Hrs)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab Share</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospital Share</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="selected_tests_table_body" class="divide-y divide-gray-200">
                        {{-- Tests will be added here dynamically by JavaScript --}}
                    </tbody>
                </table>
            </div>

            <!-- Billing Summary Section -->
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
                    <label for="lab_share_total" class="block text-gray-700 text-sm font-bold mb-2">Total Lab Share (PKR):</label>
                    <input type="text" id="lab_share_total" name="lab_share_total" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="0.00" readonly>
                </div>
                <div>
                    <label for="hospital_share_total" class="block text-gray-700 text-sm font-bold mb-2">Total Hospital Share (PKR):</label>
                    <input type="text" id="hospital_share_total" name="hospital_share_total" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="0.00" readonly>
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

            <!-- Action Buttons -->
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
            const referByDoctorCodeSelect = document.getElementById('refer_by_doctor_code');
            const referByDoctorNameInput = document.getElementById('refer_by_doctor_name');
            const newPatientBtn = document.createElement('button'); // Create new patient button dynamically
            newPatientBtn.type = 'button';
            newPatientBtn.id = 'new_patient_btn';
            newPatientBtn.className = 'bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-full shadow-md transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 ml-4';
            newPatientBtn.textContent = 'New Patient';
            mrNoInput.parentNode.appendChild(newPatientBtn); // Append next to MR No.

            // --- Test Selection Section Elements ---
            const selectTestDropdown = document.getElementById('select_test');
            const addTestToTableBtn = document.getElementById('add_test_to_table');
            const selectedTestsTableBody = document.getElementById('selected_tests_table_body');

            // --- Billing Summary Section Elements ---
            const subTotalInput = document.getElementById('sub_total');
            const discountInput = document.getElementById('discount');
            const grandTotalInput = document.getElementById('grand_total');
            const labShareTotalInput = document.getElementById('lab_share_total');
            const hospitalShareTotalInput = document.getElementById('hospital_share_total');
            const paidAmountInput = document.getElementById('paid_amount');
            const dueAmountInput = document.getElementById('due_amount');
            const previousDueInput = document.getElementById('previous_due');

            // --- Action Buttons ---
            const printSlipBtn = document.getElementById('print_slip_btn');
            const newRegistrationBtn = document.getElementById('new_registration_btn');
            const saveBtn = document.querySelector('button[type="submit"]'); // The save button

            let selectedTests = []; // Array to hold tests added to the table

            // --- Functions ---

            // Auto-populate Doctor Name
            function updateReferByDoctorName() {
                const selectedOption = referByDoctorCodeSelect.options[referByDoctorCodeSelect.selectedIndex];
                if (selectedOption && selectedOption.value !== "") {
                    referByDoctorNameInput.value = selectedOption.getAttribute('data-doctor-name');
                } else {
                    referByDoctorNameInput.value = '';
                }
            }

            // Toggle Refer By Doctor fields based on Self-Referred checkbox
            function toggleReferByDoctorFields() {
                const isSelfReferred = selfReferredCheckbox.checked;
                referByDoctorCodeSelect.disabled = isSelfReferred;
                referByDoctorNameInput.disabled = isSelfReferred;
                if (isSelfReferred) {
                    referByDoctorCodeSelect.value = '';
                    referByDoctorNameInput.value = '';
                }
            }

            // Function to add selected test to the table
            function addTestToTable() {
                const selectedOption = selectTestDropdown.options[selectTestDropdown.selectedIndex];

                if (!selectedOption || selectedOption.value === "") {
                    alert('Please select a test to add.');
                    return;
                }

                const testId = selectedOption.value;
                const testName = selectedOption.getAttribute('data-name');
                const testPrice = parseFloat(selectedOption.getAttribute('data-price'));
                const reportTime = selectedOption.getAttribute('data-report-time');
                const labSharePercent = parseFloat(selectedOption.getAttribute('data-lab-share-percent'));
                const hospitalSharePercent = parseFloat(selectedOption.getAttribute('data-hospital-share-percent'));

                // Check if test is already added
                if (selectedTests.some(test => test.id === testId)) {
                    alert('This test has already been added.');
                    return;
                }

                const labShare = testPrice * labSharePercent;
                const hospitalShare = testPrice * hospitalSharePercent;

                const newTest = {
                    id: testId,
                    name: testName,
                    price: testPrice,
                    report_time: reportTime,
                    lab_share: labShare,
                    hospital_share: hospitalShare,
                    carry_out: false // Default to not carried out
                };
                selectedTests.push(newTest);
                renderSelectedTests();
                calculateBillingSummary();
                selectTestDropdown.value = ""; // Reset dropdown
            }

            // Function to render/re-render the selected tests table
            function renderSelectedTests() {
                selectedTestsTableBody.innerHTML = ''; // Clear existing rows

                selectedTests.forEach((test, index) => {
                    const row = selectedTestsTableBody.insertRow();
                    row.innerHTML = `
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${test.name}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${test.price.toFixed(2)}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-green-600 carry-out-checkbox" data-test-id="${test.id}" ${test.carry_out ? 'checked' : ''}>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${test.report_time}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${test.lab_share.toFixed(2)}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${test.hospital_share.toFixed(2)}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="text-red-600 hover:text-red-900 remove-test-btn" data-test-id="${test.id}">Remove</button>
                        </td>
                    `;
                });

                // Add event listeners for new checkboxes
                document.querySelectorAll('.carry-out-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const testId = this.dataset.testId;
                        const testIndex = selectedTests.findIndex(t => t.id === testId);
                        if (testIndex !== -1) {
                            selectedTests[testIndex].carry_out = this.checked;
                            // You might trigger an update to a backend here if needed
                        }
                    });
                });

                // Add event listeners for new remove buttons
                document.querySelectorAll('.remove-test-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const testIdToRemove = this.dataset.testId;
                        selectedTests = selectedTests.filter(test => test.id !== testIdToRemove);
                        renderSelectedTests(); // Re-render table
                        calculateBillingSummary(); // Recalculate totals
                    });
                });
            }

            // Function to calculate billing summary
            function calculateBillingSummary() {
                let subTotal = selectedTests.reduce((sum, test) => sum + test.price, 0);
                let totalLabShare = selectedTests.reduce((sum, test) => sum + test.lab_share, 0);
                let totalHospitalShare = selectedTests.reduce((sum, test) => sum + test.hospital_share, 0);

                const discount = parseFloat(discountInput.value) || 0;
                const paid = parseFloat(paidAmountInput.value) || 0;
                const previousDue = parseFloat(previousDueInput.value) || 0;

                const grandTotal = subTotal - discount;
                const currentDue = (grandTotal + previousDue) - paid;

                subTotalInput.value = subTotal.toFixed(2);
                grandTotalInput.value = grandTotal.toFixed(2);
                labShareTotalInput.value = totalLabShare.toFixed(2);
                hospitalShareTotalInput.value = totalHospitalShare.toFixed(2);
                dueAmountInput.value = currentDue.toFixed(2);
            }

            // Function to reset the form
            function resetForm() {
                document.getElementById('lab_patient_registration_form').reset();
                selectedTests = []; // Clear selected tests
                renderSelectedTests(); // Clear table
                calculateBillingSummary(); // Reset totals
                toggleReferByDoctorFields(); // Reset doctor fields state
                updateReferByDoctorName(); // Clear doctor name/shift
            }

            // --- Event Listeners ---
            referByDoctorCodeSelect.addEventListener('change', updateReferByDoctorName);
            selfReferredCheckbox.addEventListener('change', toggleReferByDoctorFields);
            addTestToTableBtn.addEventListener('click', addTestToTable);

            discountInput.addEventListener('input', calculateBillingSummary);
            paidAmountInput.addEventListener('input', calculateBillingSummary);
            previousDueInput.addEventListener('input', calculateBillingSummary);

            newPatientBtn.addEventListener('click', function() {
                // In a real application, this might navigate to a full patient registration form
                // or clear the current form for a new patient entry.
                alert('Simulating new patient registration. Form will be reset.');
                resetForm();
            });

            newRegistrationBtn.addEventListener('click', resetForm);

            printSlipBtn.addEventListener('click', function() {
                alert('Simulating Print Slip action.');
                // In a real application, this would trigger a print function or generate a PDF.
            });

            // Initial setup
            toggleReferByDoctorFields(); // Set initial state of doctor fields
            calculateBillingSummary(); // Calculate initial totals
        });
    </script>
@endsection
