@extends('layouts.app')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Patient Discharge Form</h2>
        <a href="{{ route('patients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Patient Management
        </a>
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
        <form id="discharge_form" action="{{ route('patients.store_discharge') }}" method="POST">
            @csrf

            {{-- Patient Identification Section --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Identification</h3>
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
                    <label for="admission_date" class="block text-gray-700 text-sm font-bold mb-2">Admission Date:</label>
                    <input type="date" id="admission_date" name="admission_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="number" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" readonly>
                </div>
                <div>
                    <label for="patient_type" class="block text-gray-700 text-sm font-bold mb-2">Patient Type:</label>
                    <input type="text" id="patient_type" name="patient_type" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Normal / Welfare" readonly>
                    <input type="hidden" id="is_welfare" name="is_welfare" value="0">
                </div>
                <div>
                    <label for="cause" class="block text-gray-700 text-sm font-bold mb-2">Cause:</label>
                    <input type="text" id="cause" name="cause" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Cause of admission">
                </div>
            </div>

            {{-- Consultant Fees Section --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Consultant & Diagnosis</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="consultant_search" class="block text-gray-700 text-sm font-bold mb-2">Add Consultant:</label>
                    <input type="text" id="consultant_search" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search by name or code">
                    <div id="consultant_results" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-48 overflow-y-auto hidden"></div>
                </div>
                <div>
                    <label for="diagnosis_search" class="block text-gray-700 text-sm font-bold mb-2">Add Diagnosis:</label>
                    <input type="text" id="diagnosis_search" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search by name">
                    <div id="diagnosis_results" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-48 overflow-y-auto hidden"></div>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Selected Diagnoses:</label>
                    <div id="diagnoses_list" class="flex flex-wrap gap-2"></div>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-2">Consultant Fees</h4>
                <div id="consultants_list" class="space-y-4">
                    {{-- Dynamic consultant fee entries will be added here --}}
                </div>
            </div>

            {{-- Anesthesia Section --}}
            <div class="flex items-center mt-8 mb-4">
                <input type="hidden" name="is_anesthesia" value="0">
                <input type="checkbox" id="is_anesthesia" name="is_anesthesia" value="1" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500">
                <label for="is_anesthesia" class="ml-2 text-gray-700 text-sm font-bold">Is Anesthesia?</label>
                <div id="anesthesia_type_field" class="ml-8 hidden">
                    <label for="anesthesia_type" class="block text-gray-700 text-sm font-bold mb-2">Anesthesia Type:</label>
                    <select id="anesthesia_type" name="anesthesia_type" class="shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Type</option>
                        <option value="Type 1">Type 1</option>
                        <option value="Type 2">Type 2</option>
                        <option value="Type 3">Type 3</option>
                        <option value="Type 4">Type 4</option>
                    </select>
                </div>
            </div>

            {{-- Discharge Details Section --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Discharge Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="discharge_date" class="block text-gray-700 text-sm font-bold mb-2">Discharge Date:</label>
                    <input type="date" id="discharge_date" name="discharge_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('Y-m-d') }}" required>
                </div>
                <div>
                    <label for="discharge_time" class="block text-gray-700 text-sm font-bold mb-2">Discharge Time:</label>
                    <input type="time" id="discharge_time" name="discharge_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ date('H:i') }}" required>
                </div>
                <div>
                    <label for="discharge_status" class="block text-gray-700 text-sm font-bold mb-2">Discharge Status:</label>
                    <select id="discharge_status" name="discharge_status" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Status</option>
                        <option value="Recovered">Recovered</option>
                        <option value="Referred">Referred to another hospital</option>
                        <option value="Against Medical Advice">Against Medical Advice (AMA)</option>
                        <option value="Expired">Expired</option>
                        <option value="Transferred">Transferred</option>
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="discharge_summary" class="block text-gray-700 text-sm font-bold mb-2">Discharge Summary:</label>
                    <textarea id="discharge_summary" name="discharge_summary" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Summary of patient's hospital stay and treatment"></textarea>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="medication_at_discharge" class="block text-gray-700 text-sm font-bold mb-2">Medication at Discharge:</label>
                    <textarea id="medication_at_discharge" name="medication_at_discharge" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Medications prescribed at discharge"></textarea>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="follow_up_instructions" class="block text-gray-700 text-sm font-bold mb-2">Follow-up Instructions:</label>
                    <textarea id="follow_up_instructions" name="follow_up_instructions" rows="2" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Instructions for post-discharge care"></textarea>
                </div>
                <div>
                    <label for="certifying_doctor" class="block text-gray-700 text-sm font-bold mb-2">Certifying Doctor:</label>
                    <select id="certifying_doctor" name="certifying_doctor_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Doctor</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->name }} ({{ $doctor->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="payment_clearance_status" class="block text-gray-700 text-sm font-bold mb-2">Payment Clearance Status:</label>
                    <select id="payment_clearance_status" name="payment_clearance_status" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select Status</option>
                        <option value="Cleared">Cleared</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>

            {{-- Billing Section --}}
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Billing & Payments</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="total_bill" class="block text-gray-700 text-sm font-bold mb-2">Total Bill ($):</label>
                    <input type="text" id="total_bill" name="total_bill" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-calculated" readonly>
                </div>
                <div>
                    <label for="admission_fee" class="block text-gray-700 text-sm font-bold mb-2">Admission Fee ($):</label>
                    <input type="text" id="admission_fee" name="admission_fee" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="discount" class="block text-gray-700 text-sm font-bold mb-2">Discount ($):</label>
                    <input type="number" id="discount" name="discount" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="0" value="0">
                </div>
                <div>
                    <label for="advance_fee" class="block text-gray-700 text-sm font-bold mb-2">Advance Fee ($):</label>
                    <input type="text" id="advance_fee" name="advance_fee" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="amount_receivable" class="block text-gray-700 text-sm font-bold mb-2">Amount Receivable ($):</label>
                    <input type="text" id="amount_receivable" name="amount_receivable" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-calculated" readonly>
                </div>
                <div>
                    <label for="amount_paid" class="block text-gray-700 text-sm font-bold mb-2">Amount Paid ($):</label>
                    <input type="number" id="amount_paid" name="amount_paid" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="0" required>
                </div>
                <div>
                    <label for="current_remaining" class="block text-gray-700 text-sm font-bold mb-2">Current Remaining ($):</label>
                    <input type="text" id="current_remaining" name="current_remaining" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-calculated" readonly>
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-4">
                <button type="button" id="print_slip_btn" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Print Slip
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Discharge Patient
                </button>
            </div>
        </form>
    </div>
    
    {{-- Search Modals (Patient and Consultant) --}}
    <div id="patient_search_modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <h3 class="text-xl font-semibold mb-4">Search Indoor Patient</h3>
            <input type="text" id="patient_search_query" class="w-full px-3 py-2 border rounded-md" placeholder="Enter MR No, Name, or Mobile No.">
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

    {{-- Discharge Slip Area for Printing --}}
    <div id="discharge_slip_area" class="hidden" style="width: 210mm; min-height: 297mm; margin: 0 auto; padding: 20mm; font-family: 'Inter', sans-serif;">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">KHAZIR HOSPITAL</h1>
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
                            fetchIndoorPatientData(mrNumber);
                            patientSearchModal.classList.add('hidden');
                        });
                    });
                } catch (error) {
                    console.error('Error fetching patient data:', error);
                    patientSearchResults.innerHTML = '<div class="p-2 text-red-500">Failed to fetch patient data.</div>';
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
                        advanceFeeInput.value = parseFloat(indoorPatientData.advance_fee).toFixed(2);
                        
                        // NEW: Populate consultant visits and diagnoses from fetched data
                        selectedConsultants = data.inpatient_detail?.consultant_visits || {};
                        selectedDiagnoses = data.inpatient_detail?.diagnoses || {};
                        
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
                            consultantSearchInput.value = '';
                        });
                    });
                } catch (error) {
                    console.error('Error searching for consultants:', error);
                    consultantSearchResults.innerHTML = '<div class="p-3 text-red-500">Failed to load search results.</div>';
                }
            }
            
            function renderConsultantsList() {
                consultantsListDiv.innerHTML = '';
                for (const id in selectedConsultants) {
                    const visit = selectedConsultants[id];
                    const consultantItem = document.createElement('div');
                    consultantItem.classList.add('flex', 'items-center', 'space-x-4', 'bg-gray-100', 'p-4', 'rounded-lg', 'shadow-sm', 'consultant-item');
                    consultantItem.dataset.id = id;
                    consultantItem.innerHTML = `
                        <div class="flex-grow">
                            <span class="font-semibold text-gray-800">${visit.name}</span>
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Fee ($):</label>
                            <input type="number" class="w-24 text-right px-2 py-1 border rounded-md bg-gray-50 fee-input" value="${parseFloat(visit.fee).toFixed(2)}" readonly>
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Qty:</label>
                            <input type="number" value="${visit.qty}" min="1" class="qty-input w-16 text-right px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${id}">
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Discount ($):</label>
                            <input type="number" value="${visit.discount}" min="0" class="discount-input w-24 text-right px-2 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" data-id="${id}">
                        </div>
                        <div class="flex-shrink-0 flex items-center space-x-2">
                            <label class="text-sm font-medium text-gray-700">Total ($):</label>
                            <span class="font-bold text-gray-800 total-amount-cell w-24 text-right">${parseFloat(visit.total).toFixed(2)}</span>
                        </div>
                        <button type="button" class="text-red-600 hover:text-red-800 remove-consultant-btn" data-id="${id}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                        html += `<div class="p-2 cursor-pointer hover:bg-gray-100" data-id="${diagnosis.id}" data-name="${diagnosis.name}">${diagnosis.name}</div>`;
                    });
                } else {
                    html = '<div class="p-2 text-gray-500">No diagnoses found.</div>';
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
                span.classList.add('bg-blue-200', 'text-blue-800', 'px-3', 'py-1', 'rounded-full', 'flex', 'items-center', 'space-x-2');
                span.innerHTML = `
                    <span>${name}</span>
                    <button type="button" class="remove-diagnosis-btn text-blue-800 hover:text-blue-900" data-id="${id}">&times;</button>
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

                const admissionFee = parseFloat(admissionFeeInput.value) || 0;
                const totalBill = totalConsultantFees + admissionFee;
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
                document.getElementById('slip_discharge_status').textContent = dischargeStatusSelect.options[dischargeStatusSelect.selectedIndex].text;
                document.getElementById('slip_certifying_doctor').textContent = certifyingDoctorSelect.options[certifyingDoctorSelect.selectedIndex].text;
                document.getElementById('slip_discharge_summary').textContent = dischargeSummaryTextarea.value;
                
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
                        <td class="py-1 px-2 text-right border border-gray-400">$${visit.fee.toFixed(2)}</td>
                        <td class="py-1 px-2 text-right border border-gray-400">$${visit.total.toFixed(2)}</td>
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
                            <td class="py-1 px-2 border-r border-gray-400 text-right text-red-500">-$${visit.discount.toFixed(2)}</td>
                        `;
                        consultantFeesTableBody.appendChild(discountRow);
                    }
                }
                
                // Populate Billing Summary
                slipTotalConsultantFees.textContent = `$${totalConsultantFees.toFixed(2)}`;
                slipAdmissionFee.textContent = `$${parseFloat(admissionFeeInput.value).toFixed(2)}`;
                document.getElementById('slip_total_bill').textContent = `$${totalBillInput.value}`;
                document.getElementById('slip_discount').textContent = `$${discountInput.value}`;
                document.getElementById('slip_advance_fee').textContent = `$${advanceFeeInput.value}`;
                document.getElementById('slip_amount_paid').textContent = `$${amountPaidInput.value}`;
                document.getElementById('slip_current_remaining').textContent = `$${currentRemainingInput.value}`;
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
                printWindow.print();
                printWindow.close();
            }
        });
    </script>
@endsection