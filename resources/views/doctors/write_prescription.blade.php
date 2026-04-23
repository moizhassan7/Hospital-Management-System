@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Write Prescription</h2>
        <a href="{{ route('doctors.dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="prescription_form" action="#" method="POST">
            @csrf

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Patient Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div>
                    <label for="mr_number" class="block text-gray-700 text-sm font-bold mb-2">MR Number:</label>
                    <input type="text" id="mr_number" name="mr_number" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., MRN-001" required>
                </div>
                <div>
                    <label for="patient_name" class="block text-gray-700 text-sm font-bold mb-2">Patient Name:</label>
                    <input type="text" id="patient_name" name="patient_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="" readonly>
                </div>
                <div>
                    <label for="age" class="block text-gray-700 text-sm font-bold mb-2">Age:</label>
                    <input type="text" id="age" name="age" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="" readonly>
                </div>
                <div>
                    <label for="sex" class="block text-gray-700 text-sm font-bold mb-2">Sex:</label>
                    <input type="text" id="sex" name="sex" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="" readonly>
                </div>
                <div class="lg:col-span-2">
                    <label for="current_date" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
                    <input type="text" id="current_date" name="current_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ date('Y-m-d') }}" readonly>
                </div>
                <div class="lg:col-span-2">
                    <label for="current_time" class="block text-gray-700 text-sm font-bold mb-2">Time:</label>
                    <input type="text" id="current_time" name="current_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ date('H:i') }}" readonly>
                </div>
                <div class="col-span-1 sm:col-span-2 lg:col-span-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Patient History:</label>
                    <div id="history_list" class="space-y-3 p-4 bg-gray-50 rounded-lg max-h-48 overflow-y-auto">
                        <p class="text-gray-500 text-sm">Enter an MR Number to view previous visits.</p>
                    </div>
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Vitals</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div>
                    <label for="bp" class="block text-gray-700 text-sm font-bold mb-2">B.P. (mmHg):</label>
                    <input type="text" id="bp" name="bp" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 120/80">
                </div>
                <div>
                    <label for="pulse" class="block text-gray-700 text-sm font-bold mb-2">Pulse (bpm):</label>
                    <input type="number" id="pulse" name="pulse" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 72">
                </div>
                <div>
                    <label for="oxygen" class="block text-gray-700 text-sm font-bold mb-2">Oxygen (%):</label>
                    <input type="number" id="oxygen" name="oxygen" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 98">
                </div>
                <div>
                    <label for="temperature" class="block text-gray-700 text-sm font-bold mb-2">Temperature (°F):</label>
                    <input type="number" id="temperature" name="temperature" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 98.6" step="0.1">
                </div>
                <div>
                    <label for="weight" class="block text-gray-700 text-sm font-bold mb-2">Weight (kg):</label>
                    <input type="number" id="weight" name="weight" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 70">
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Patient Complaints</h3>
            <div class="mb-6">
                <textarea id="complaints" name="complaints" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Describe the patient's complaints here..."></textarea>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Diagnoses & Reports</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="diagnoses_select" class="block text-gray-700 text-sm font-bold mb-2">Diagnoses:</label>
                    <select id="diagnoses_select" multiple class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </select>
                </div>
                
                <div>
                    <label for="reports_select" class="block text-gray-700 text-sm font-bold mb-2">Reports:</label>
                    <select id="reports_select" multiple class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </select>
                </div>

                <div class="flex items-center space-x-4">
                    <button type="button" id="add_diagnoses_btn" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Add Diagnoses
                    </button>
                    <button type="button" id="add_reports_btn" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Add Reports
                    </button>
                </div>
            </div>

            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Prescription (Rx)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="medicine_search" class="block text-gray-700 text-sm font-bold mb-2">Add Medicine:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="medicine_search" name="medicine_search" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search for a medicine" readonly>
                        <button type="button" id="search_medicine_btn" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <div class="w-full">
                            <label for="medicine_groups" class="block text-gray-700 text-sm font-bold mb-2">Medicine Groups:</label>
                            <select id="medicine_groups" name="medicine_groups" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select a Group</option>
                                <option value="group_fever">Fever</option>
                                <option value="group_headache">Headache</option>
                                <option value="group_cold_flu">Cold & Flu</option>
                            </select>
                        </div>
                        <button type="button" id="add_medicine_group_btn" class="bg-indigo-500 hover:bg-indigo-600 text-white p-2 rounded-lg shadow-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 mt-6" title="Add New Medicine Group">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                    </div>
                </div>
                <div class="col-span-1 sm:col-span-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label for="dosage_frequency" class="block text-gray-700 text-sm font-bold mb-2">Dosage & Frequency:</label>
                            <input type="text" id="dosage_frequency" name="dosage_frequency" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 1 tablet twice a day">
                        </div>
                        <div>
                            <label for="duration" class="block text-gray-700 text-sm font-bold mb-2">Duration (Days):</label>
                            <input type="number" id="duration" name="duration" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 7">
                        </div>
                        <div class="flex items-end">
                            <button type="button" id="add_medicine_btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 w-full">
                                Add Medicine
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto mb-6">
                <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sr. No.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dosage & Frequency</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="prescription_table_body" class="divide-y divide-gray-200">
                        {{-- Prescribed medicines will be added here dynamically --}}
                    </tbody>
                </table>
            </div>
            
            <div class="mb-6">
                <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Doctor's Notes:</label>
                <textarea id="notes" name="notes" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Add any final notes for the patient"></textarea>
            </div>
            
            <div class="flex items-center mb-6">
                <label for="next_visit_date" class="block text-gray-700 text-sm font-bold mr-4">Next Visit Date:</label>
                <input type="date" id="next_visit_date" name="next_visit_date" class="shadow appearance-none border rounded-lg w-48 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <button type="submit" id="save_btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Save
                </button>
                <button type="button" id="print_btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Print
                </button>
            </div>
        </form>
    </div>

    <div id="medicineSearchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Search Medicines</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('medicineSearchModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="mb-4">
                <input type="text" id="medicine_search_input_modal" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search by name">
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="medicine_search_results_body" class="divide-y divide-gray-200">
                        {{-- Static Medicine Data will be rendered here --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="addDiagnosisModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Add New Diagnosis</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('addDiagnosisModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="mb-4">
                <label for="new_diagnosis_name" class="block text-gray-700 text-sm font-bold mb-2">Diagnosis Name:</label>
                <input type="text" id="new_diagnosis_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Pneumonia">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg" onclick="closeModal('addDiagnosisModal')">Cancel</button>
                <button type="button" id="save_new_diagnosis_btn" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Add</button>
            </div>
        </div>
    </div>

    <div id="addReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Add New Report Type</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('addReportModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="mb-4">
                <label for="new_report_name" class="block text-gray-700 text-sm font-bold mb-2">Report Name:</label>
                <input type="text" id="new_report_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Blood Culture">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg" onclick="closeModal('addReportModal')">Cancel</button>
                <button type="button" id="save_new_report_btn" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Add</button>
            </div>
        </div>
    </div>

    <div id="addMedicineGroupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Add New Medicine Group</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('addMedicineGroupModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="mb-4">
                <label for="new_group_name" class="block text-gray-700 text-sm font-bold mb-2">Group Name:</label>
                <input type="text" id="new_group_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Common Cold Treatment">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Medicines in this Group:</label>
                <div id="new_group_medicines_container" class="space-y-4">
                    </div>
                <button type="button" id="add_group_medicine_field_btn" class="mt-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out">
                    + Add Medicine
                </button>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg" onclick="closeModal('addMedicineGroupModal')">Cancel</button>
                <button type="button" id="save_new_group_btn" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Save Group</button>
            </div>
        </div>
    </div>

    <div id="historyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Patient History Details</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('historyModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="history_modal_content">
                </div>
        </div>
    </div>
    
    <div id="reportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Lab Report Details</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('reportModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="report_content" class="text-gray-700">
                </div>
        </div>
    </div>

    <div id="messageBox" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Message</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('messageBox')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <p id="messageText" class="text-gray-700 mb-4"></p>
            <div class="flex justify-end">
                <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg" onclick="closeModal('messageBox')">OK</button>
            </div>
        </div>
    </div>
    
    <div id="printable-prescription" class="print-only p-8">
        </div>
    
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script>
    // --- DATA STRUCTURES (SIMULATED) ---
    const ALL_PATIENT_DATA = {
        'MRN-001': {
            name: 'Jane Doe',
            age: 35,
            sex: 'Female',
            visits: [
                {
                    date: '2024-05-10',
                    complaints: 'Seasonal allergies, sneezing, and watery eyes.',
                    notes: 'Initial consultation for seasonal allergies. Patient reports sneezing and watery eyes. No fever or cough.',
                    vitals: { bp: '118/78', pulse: 75, oxygen: 99, temp: 98.4, weight: 65 },
                    diagnoses: ['Seasonal Allergies'],
                    reports: ['RPT001'],
                    medicines: [{ name: 'Cetirizine', dosage: '1 tablet Daily', duration: 7 }]
                },
                {
                    date: '2023-11-15',
                    complaints: 'Occasional shortness of breath with physical exertion.',
                    notes: 'Follow-up for mild asthma. Patient reports occasional shortness of breath with physical exertion. Lung sounds clear.',
                    vitals: { bp: '122/80', pulse: 78, oxygen: 98, temp: 98.6, weight: 64 },
                    diagnoses: ['Mild Asthma'],
                    reports: [],
                    medicines: [{ name: 'Inhaler', dosage: '2 puffs As needed', duration: 30 }]
                }
            ]
        },
        'MRN-002': {
            name: 'John Smith',
            age: 52,
            sex: 'Male',
            visits: [
                {
                    date: '2024-06-20',
                    complaints: 'Routine check-up, no major issues.',
                    notes: 'Routine check-up. Patient reports no major issues. Blood pressure elevated.',
                    vitals: { bp: '140/90', pulse: 85, oxygen: 98, temp: 98.7, weight: 85 },
                    diagnoses: ['Mild Hypertension'],
                    reports: ['RPT003'],
                    medicines: [{ name: 'Amlodipine', dosage: '5mg Daily', duration: 30 }]
                }
            ]
        }
    };

    let ALL_DIAGNOSES = ['Fever', 'Headache', 'Cough', 'Body Aches', 'Seasonal Allergies', 'Mild Asthma', 'Mild Hypertension'];
    let ALL_REPORTS = [
        { id: 'RPT001', name: 'Complete Blood Count (CBC)', content: 'White Blood Cell (WBC) Count: 8.5 x 10^9/L (Normal: 4.5-11.0). Red Blood Cell (RBC) Count: 4.8 x 10^12/L (Normal: 4.2-5.4). Hemoglobin: 14.2 g/dL (Normal: 12.0-16.0). All results are within normal limits.' },
        { id: 'RPT002', name: 'Chest X-ray Report', content: 'FINDINGS: Normal chest radiograph. The lungs are clear without evidence of consolidation, effusion, or pneumothorax. The cardiac silhouette is of normal size. Bony structures appear unremarkable. IMPRESSION: No acute cardiopulmonary disease.' },
        { id: 'RPT003', name: 'Lipid Panel', content: 'Total Cholesterol: 215 mg/dL (High). HDL Cholesterol: 45 mg/dL (Low). LDL Cholesterol: 140 mg/dL (High). Triglycerides: 150 mg/dL (Normal).' },
    ];
    let ALL_MEDICINES = [
        { id: 'MED001', name: 'Paracetamol', unit: '500mg' },
        { id: 'MED002', name: 'Ibuprofen', unit: '200mg' },
        { id: 'MED003', name: 'Amoxicillin', unit: '250mg' },
        { id: 'MED004', name: 'Cetirizine', unit: '10mg' },
        { id: 'MED005', name: 'Omeprazole', unit: '20mg' },
        { id: 'MED006', name: 'Atorvastatin', unit: '10mg' },
        { id: 'MED007', name: 'Lisinopril', unit: '2.5mg' },
        { id: 'MED008', name: 'Metformin', unit: '500mg' },
        { id: 'MED009', name: 'Ciprofloxacin', unit: '500mg' },
    ];
    let MEDICINE_GROUPS = {
        'group_fever': [{ name: 'Paracetamol', dosage: '1-2 tablets Every 6 hours', duration: 3 }],
        'group_headache': [{ name: 'Ibuprofen', dosage: '1 tablet As needed', duration: 1 }],
        'group_cold_flu': [
            { name: 'Cetirizine', dosage: '1 tablet Daily', duration: 5 },
            { name: 'Paracetamol', dosage: '1 tablet Twice daily', duration: 5 }
        ]
    };

    // --- GLOBAL STATE ---
    let prescribedMedicines = [];
    let diagnosesSelect, reportsSelect;

    // --- HELPER FUNCTIONS ---
    function showMessage(message) {
        const messageBox = document.getElementById('messageBox');
        document.getElementById('messageText').textContent = message;
        messageBox.classList.remove('hidden');
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function clearMedicineFields() {
        document.getElementById('medicine_search').value = '';
        document.getElementById('dosage_frequency').value = '';
        document.getElementById('duration').value = '';
    }

    function renderPrescriptionTable() {
        const prescriptionTableBody = document.getElementById('prescription_table_body');
        prescriptionTableBody.innerHTML = '';
        prescribedMedicines.forEach((med, index) => {
            const row = prescriptionTableBody.insertRow();
            row.innerHTML = `
                <td class="px-4 py-2 text-sm text-gray-900">${index + 1}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${med.name}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${med.dosage}</td>
                <td class="px-4 py-2 text-sm text-gray-900">${med.duration} days</td>
                <td class="px-4 py-2 text-sm font-medium">
                    <button type="button" class="text-red-600 hover:text-red-900 remove-medicine-btn" data-index="${index}">Remove</button>
                </td>
            `;
        });
        prescriptionTableBody.querySelectorAll('.remove-medicine-btn').forEach(button => {
            button.addEventListener('click', (event) => {
                const indexToRemove = parseInt(event.target.dataset.index);
                prescribedMedicines.splice(indexToRemove, 1);
                renderPrescriptionTable();
            });
        });
    }

    function renderMedicineSearchResults(medicines) {
        const medicineSearchResultsBody = document.getElementById('medicine_search_results_body');
        medicineSearchResultsBody.innerHTML = '';
        medicines.forEach(med => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${med.id}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${med.name}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${med.unit}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                    <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-full select-medicine-btn"
                        data-name="${med.name}">Select</button>
                </td>
            `;
            medicineSearchResultsBody.appendChild(row);
        });
    }

    // --- PRINTING LOGIC ---
    function generatePrintContent() {
        const selectedDiagnoses = diagnosesSelect.getValue();
        const selectedReports = reportsSelect.getValue().map(id => {
            const report = ALL_REPORTS.find(r => r.id === id);
            return report ? report.name : '';
        });

        let content = `
            <div class="p-8 font-sans text-gray-800 leading-relaxed">
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-bold">KHAZIR HOSPITAL</h1>
                    <p class="text-lg">Dr. Moiz Hassan, M.D. - General Practitioner</p>
                    <p class="text-sm">123 Medical, Pakistan | Phone: (123) 456-7890</p>
                </div>
                <div class="grid grid-cols-2 gap-x-6 border-b pb-4 mb-4">
                    <div>
                        <p><strong>Patient Name:</strong> ${document.getElementById('patient_name').value}</p>
                        <p><strong>MR Number:</strong> ${document.getElementById('mr_number').value}</p>
                    </div>
                    <div class="text-right">
                        <p><strong>Date:</strong> ${document.getElementById('current_date').value}</p>
                        <p><strong>Time:</strong> ${document.getElementById('current_time').value}</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-x-6 border-b pb-4 mb-4">
                    <p><strong>Age:</strong> ${document.getElementById('age').value}</p>
                    <p><strong>Sex:</strong> ${document.getElementById('sex').value}</p>
                    <p><strong>B.P:</strong> ${document.getElementById('bp').value || 'N/A'}</p>
                    <p><strong>Pulse:</strong> ${document.getElementById('pulse').value || 'N/A'}</p>
                    <p><strong>Temp:</strong> ${document.getElementById('temperature').value || 'N/A'}</p>
                    <p><strong>Weight:</strong> ${document.getElementById('weight').value || 'N/A'}</p>
                    <p><strong>Oxygen:</strong> ${document.getElementById('oxygen').value || 'N/A'}</p>
                </div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold border-b pb-1">Complaints</h2>
                    <p>${document.getElementById('complaints').value || 'N/A'}</p>
                </div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold border-b pb-1">Diagnoses</h2>
                    <p>${selectedDiagnoses.join(', ') || 'N/A'}</p>
                </div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold border-b pb-1">Reports</h2>
                    <p>${selectedReports.join(', ') || 'N/A'}</p>
                </div>
                <div class="mb-6">
                    <h2 class="text-xl font-bold border-b pb-1">Rx</h2>
                    <ol class="list-decimal pl-5 space-y-2">
                        ${prescribedMedicines.map((med, index) => `
                            <li>
                                <p><strong>${med.name}</strong> - ${med.dosage}, for ${med.duration} days.</p>
                            </li>
                        `).join('')}
                    </ol>
                </div>
                <div class="mb-4">
                    <h2 class="text-xl font-bold border-b pb-1">Notes</h2>
                    <p>${document.getElementById('notes').value || 'N/A'}</p>
                </div>
                <div class="mb-6">
                    <p><strong>Next Visit Date:</strong> ${document.getElementById('next_visit_date').value || 'N/A'}</p>
                </div>
                <div class="text-right mt-16">
                    <p class="font-semibold border-t pt-2">Signature: __________________________</p>
                </div>
            </div>
        `;
        return content;
    }

    function printPrescription() {
        const contentToPrint = generatePrintContent();

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>Prescription</title>
                <style>
                    body { font-family: 'Inter', sans-serif; margin: 0; padding: 20mm; font-size: 14px; }
                    h1, h2, h3 { color: #1f2937; margin: 0; }
                    p { margin: 0; }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    .mb-6 { margin-bottom: 1.5rem; }
                    .mb-4 { margin-bottom: 1rem; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mt-16 { margin-top: 4rem; }
                    .border-b { border-bottom: 1px solid #e5e7eb; }
                    .font-bold { font-weight: 700; }
                    .font-semibold { font-weight: 600; }
                    .text-sm { font-size: 0.875rem; }
                    .text-lg { font-size: 1.125rem; }
                    .text-xl { font-size: 1.25rem; }
                    .text-3xl { font-size: 1.875rem; }
                    .grid { display: grid; }
                    .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                    .gap-2 { gap: 0.5rem; }
                    .border-t { border-top: 1px solid #6b7280; }
                    .pt-2 { padding-top: 0.5rem; }
                    .pl-5 { padding-left: 1.25rem; }
                    .space-y-2 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.5rem; }
                </style>
            </head>
            <body>
                ${contentToPrint}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
    
    // --- EVENT LISTENERS ---
    document.addEventListener('DOMContentLoaded', () => {
        const mrNumberInput = document.getElementById('mr_number');
        const patientNameInput = document.getElementById('patient_name');
        const ageInput = document.getElementById('age');
        const sexInput = document.getElementById('sex');
        const historyList = document.getElementById('history_list');
        const searchMedicineBtn = document.getElementById('search_medicine_btn');
        const addMedicineBtn = document.getElementById('add_medicine_btn');
        const medicineGroupsSelect = document.getElementById('medicine_groups');
        const medicineSearchInput = document.getElementById('medicine_search');
        const medicineSearchInputModal = document.getElementById('medicine_search_input_modal');
        const medicineSearchResultsBody = document.getElementById('medicine_search_results_body');
        const saveBtn = document.getElementById('save_btn');
        const printBtn = document.getElementById('print_btn');
        const addDiagnosisBtn = document.getElementById('add_diagnoses_btn');
        const saveNewDiagnosisBtn = document.getElementById('save_new_diagnosis_btn');
        const addReportsBtn = document.getElementById('add_reports_btn');
        const saveNewReportBtn = document.getElementById('save_new_report_btn');
        const addMedicineGroupBtn = document.getElementById('add_medicine_group_btn');
        const addGroupMedicineFieldBtn = document.getElementById('add_group_medicine_field_btn');
        const newGroupMedicinesContainer = document.getElementById('new_group_medicines_container');
        const saveNewGroupBtn = document.getElementById('save_new_group_btn');

        // Initialize Tom Select for diagnoses and reports
        diagnosesSelect = new TomSelect('#diagnoses_select', {
            plugins: ['remove_button'],
            options: ALL_DIAGNOSES.map(d => ({ value: d, text: d })),
            create: true,
        });

        reportsSelect = new TomSelect('#reports_select', {
            plugins: ['remove_button'],
            options: ALL_REPORTS.map(r => ({ value: r.id, text: r.name })),
            render: {
                option: function(data, escape) {
                    return `<div>
                        <span>${escape(data.text)}</span>
                        <button type="button" class="ml-2 text-blue-600 hover:text-blue-800 text-sm view-report-btn" data-report-id="${escape(data.value)}">View</button>
                    </div>`;
                }
            }
        });

        // Patient data lookup simulation
        mrNumberInput.addEventListener('input', (e) => {
            const mrNumber = e.target.value.toUpperCase();
            const patient = ALL_PATIENT_DATA[mrNumber];

            if (patient) {
                patientNameInput.value = patient.name;
                ageInput.value = patient.age;
                sexInput.value = patient.sex;
                historyList.innerHTML = '';

                patient.visits.forEach((visit, index) => {
                    const historyItem = document.createElement('div');
                    historyItem.className = "bg-gray-50 p-3 rounded-lg flex justify-between items-center cursor-pointer hover:bg-gray-100 transition-colors duration-200";
                    historyItem.innerHTML = `
                        <div>
                            <p class="font-medium text-gray-900"><strong>Visit Date:</strong> ${visit.date}</p>
                            <p class="text-sm text-gray-600 truncate max-w-sm"><strong>Notes:</strong> ${visit.notes}</p>
                        </div>
                        <button type="button" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold view-history-btn" data-index="${index}" data-mr-number="${mrNumber}">View Details</button>
                    `;
                    historyList.appendChild(historyItem);
                });
            } else {
                patientNameInput.value = '';
                ageInput.value = '';
                sexInput.value = '';
                historyList.innerHTML = `<p class="text-gray-500 text-sm">No patient found with this MR Number.</p>`;
            }
        });

        // View History Details Modal (Event delegation)
        document.addEventListener('click', (e) => {
            if (e.target && e.target.matches('.view-history-btn')) {
                const index = parseInt(e.target.dataset.index);
                const mrNumber = e.target.dataset.mrNumber;
                const visit = ALL_PATIENT_DATA[mrNumber].visits[index];

                const historyModalContent = document.getElementById('history_modal_content');
                const historyHtml = `
                    <div class="mb-4">
                        <p class="text-lg"><strong>Date:</strong> ${visit.date}</p>
                        <p class="text-lg"><strong>Complaints:</strong> ${visit.complaints || 'N/A'}</p>
                        <p class="text-lg"><strong>Notes:</strong> ${visit.notes}</p>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-semibold">Vitals</h4>
                        <p>B.P.: ${visit.vitals.bp}, Pulse: ${visit.vitals.pulse}, O2: ${visit.vitals.oxygen}%, Temp: ${visit.vitals.temp}°F, Weight: ${visit.vitals.weight}kg</p>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-semibold">Diagnoses & Reports</h4>
                        <p><strong>Diagnoses:</strong> ${visit.diagnoses.join(', ') || 'N/A'}</p>
                        <p><strong>Reports:</strong> ${visit.reports.join(', ') || 'N/A'}</p>
                    </div>
                    <div class="mb-4">
                        <h4 class="font-semibold">Prescription</h4>
                        <ul class="list-disc pl-5">
                            ${visit.medicines.map(med => `<li>${med.name} - ${med.dosage}</li>`).join('')}
                        </ul>
                    </div>
                `;
                historyModalContent.innerHTML = historyHtml;
                openModal('historyModal');
            }
        });

        // View Report Details Modal (Event delegation)
        document.addEventListener('click', (e) => {
            if (e.target && e.target.matches('.view-report-btn')) {
                const reportId = e.target.dataset.reportId;
                const report = ALL_REPORTS.find(r => r.id === reportId);
                const reportContent = document.getElementById('report_content');
                if (report) {
                    reportContent.innerHTML = `<h4 class="text-lg font-semibold mb-2">${report.name}</h4><pre class="whitespace-pre-wrap">${report.content}</pre>`;
                    openModal('reportModal');
                }
            }
        });

        // Add Diagnosis button
        addDiagnosisBtn.addEventListener('click', () => {
            openModal('addDiagnosisModal');
        });

        // Save new diagnosis
        saveNewDiagnosisBtn.addEventListener('click', () => {
            const newDiagnosisName = document.getElementById('new_diagnosis_name').value.trim();
            if (newDiagnosisName) {
                if (!ALL_DIAGNOSES.includes(newDiagnosisName)) {
                    ALL_DIAGNOSES.push(newDiagnosisName);
                    diagnosesSelect.addOption({ value: newDiagnosisName, text: newDiagnosisName });
                    showMessage(`Diagnosis "${newDiagnosisName}" added.`);
                } else {
                    showMessage(`Diagnosis "${newDiagnosisName}" already exists.`);
                }
                document.getElementById('new_diagnosis_name').value = '';
                closeModal('addDiagnosisModal');
            } else {
                showMessage('Please enter a diagnosis name.');
            }
        });

        // Add Report button
        addReportsBtn.addEventListener('click', () => {
            openModal('addReportModal');
        });

        // Save new report
        saveNewReportBtn.addEventListener('click', () => {
            const newReportName = document.getElementById('new_report_name').value.trim();
            if (newReportName) {
                const newReportId = 'RPT' + Math.floor(Math.random() * 10000);
                const newReport = { id: newReportId, name: newReportName, content: `Simulated report content for ${newReportName}.` };
                ALL_REPORTS.push(newReport);
                reportsSelect.addOption({ value: newReportId, text: newReportName });
                showMessage(`Report type "${newReportName}" added.`);
                document.getElementById('new_report_name').value = '';
                closeModal('addReportModal');
            } else {
                showMessage('Please enter a report name.');
            }
        });

        // Add Medicine Group button
        addMedicineGroupBtn.addEventListener('click', () => {
            // Clear existing fields
            newGroupMedicinesContainer.innerHTML = '';
            document.getElementById('new_group_name').value = '';
            // Add one empty field by default
            addGroupMedicineFieldBtn.click();
            openModal('addMedicineGroupModal');
        });

        // Add medicine field to the group creation modal
        addGroupMedicineFieldBtn.addEventListener('click', () => {
            const index = newGroupMedicinesContainer.children.length;
            const newField = document.createElement('div');
            newField.className = "flex items-center space-x-2";
            newField.innerHTML = `
                <div class="flex-1">
                    <label class="block text-gray-700 text-sm mb-1">Medicine Name</label>
                    <input type="text" name="group_medicine_name[]" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700" placeholder="e.g., Paracetamol">
                </div>
                <div class="flex-1">
                    <label class="block text-gray-700 text-sm mb-1">Dosage & Frequency</label>
                    <input type="text" name="group_dosage_frequency[]" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700" placeholder="e.g., 1 tablet twice a day">
                </div>
                <div class="flex-1">
                    <label class="block text-gray-700 text-sm mb-1">Duration (Days)</label>
                    <input type="number" name="group_duration[]" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700" placeholder="e.g., 7">
                </div>
                <button type="button" class="text-red-600 hover:text-red-800 self-end mb-2 remove-group-medicine-field">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            `;
            newGroupMedicinesContainer.appendChild(newField);
        });

        // Remove medicine field from the group creation modal
        document.addEventListener('click', (e) => {
            if (e.target && e.target.matches('.remove-group-medicine-field')) {
                e.target.closest('.flex.items-center.space-x-2').remove();
            }
        });

        // Save new medicine group
        saveNewGroupBtn.addEventListener('click', () => {
            const newGroupName = document.getElementById('new_group_name').value.trim();
            const medicineNames = Array.from(document.querySelectorAll('input[name="group_medicine_name[]"]')).map(el => el.value);
            const dosages = Array.from(document.querySelectorAll('input[name="group_dosage_frequency[]"]')).map(el => el.value);
            const durations = Array.from(document.querySelectorAll('input[name="group_duration[]"]')).map(el => el.value);

            if (!newGroupName) {
                showMessage('Please enter a group name.');
                return;
            }

            if (medicineNames.every(name => !name)) {
                showMessage('Please add at least one medicine to the group.');
                return;
            }

            const newGroupKey = 'group_' + newGroupName.toLowerCase().replace(/\s/g, '_');
            const newGroupMedicines = [];
            for (let i = 0; i < medicineNames.length; i++) {
                if (medicineNames[i] && dosages[i] && durations[i]) {
                    newGroupMedicines.push({
                        name: medicineNames[i],
                        dosage: dosages[i],
                        duration: parseInt(durations[i])
                    });
                }
            }

            if (newGroupMedicines.length > 0) {
                MEDICINE_GROUPS[newGroupKey] = newGroupMedicines;
                const medicineGroupsSelect = document.getElementById('medicine_groups');
                const newOption = new Option(newGroupName, newGroupKey);
                medicineGroupsSelect.add(newOption);
                showMessage(`New group "${newGroupName}" saved successfully!`);
                closeModal('addMedicineGroupModal');
            } else {
                showMessage('Please fill out all fields for the medicines in the group.');
            }
        });

        // Medicine search modal
        searchMedicineBtn.addEventListener('click', () => {
            medicineSearchInputModal.value = '';
            renderMedicineSearchResults(ALL_MEDICINES);
            openModal('medicineSearchModal');
        });

        medicineSearchInputModal.addEventListener('keyup', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filteredMedicines = ALL_MEDICINES.filter(med =>
                med.name.toLowerCase().includes(searchTerm)
            );
            renderMedicineSearchResults(filteredMedicines);
        });

        // Select a medicine from the modal
        document.addEventListener('click', (e) => {
            if (e.target && e.target.matches('.select-medicine-btn')) {
                const medicineName = e.target.dataset.name;
                document.getElementById('medicine_search').value = medicineName;
                closeModal('medicineSearchModal');
            }
        });

        // Add medicine to table logic
        addMedicineBtn.addEventListener('click', () => {
            const medicineName = medicineSearchInput.value;
            const dosageFrequency = document.getElementById('dosage_frequency').value;
            const duration = document.getElementById('duration').value;
            if (medicineName && dosageFrequency && duration) {
                const newMed = { name: medicineName, dosage: dosageFrequency, duration };
                prescribedMedicines.push(newMed);
                renderPrescriptionTable();
                clearMedicineFields();
            } else {
                showMessage('Please fill out all prescription details (Medicine, Dosage & Frequency, Duration).');
            }
        });

        // Medicine group selection
        medicineGroupsSelect.addEventListener('change', (e) => {
            const selectedGroup = e.target.value;
            if (selectedGroup && MEDICINE_GROUPS[selectedGroup]) {
                const medsToAdd = MEDICINE_GROUPS[selectedGroup];
                prescribedMedicines = [...prescribedMedicines, ...medsToAdd];
                renderPrescriptionTable();
                clearMedicineFields();
                showMessage(`Added ${medsToAdd.length} medicine(s) from the "${selectedGroup.replace('group_', '').replace(/_/g, ' ')}" group.`);
            }
        });

        // Save and Print buttons
        saveBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showMessage('Prescription saved successfully! (Simulated)');
        });

        printBtn.addEventListener('click', (e) => {
            e.preventDefault();
            printPrescription();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                document.getElementById('save_btn').click();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                document.getElementById('print_btn').click();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
                e.preventDefault();
                document.getElementById('search_medicine_btn').click();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                document.getElementById('add_diagnoses_btn').click();
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                document.getElementById('add_reports_btn').click();
            }
        });

        // Initial load for date/time fields
        document.getElementById('current_date').value = new Date().toISOString().split('T')[0];
        document.getElementById('current_time').value = new Date().toLocaleTimeString('en-US', {hour12: false, hour: '2-digit', minute: '2-digit'});
    });
</script>
@endsection