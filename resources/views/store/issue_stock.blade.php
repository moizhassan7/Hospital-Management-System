@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Issue Stock</h2>
        <a href="{{ route('store.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Store Management
        </a>
    </div>

    <!-- Issue Stock Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="issue_stock_form" action="#" method="POST">
            @csrf

            <!-- Transaction Details -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Transaction Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="transaction_id" class="block text-gray-700 text-sm font-bold mb-2">Transaction ID:</label>
                    <input type="text" id="transaction_id" name="transaction_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="TXN-{{ strtoupper(Str::random(6)) }}" readonly>
                </div>
                <div>
                    <label for="issue_date" class="block text-gray-700 text-sm font-bold mb-2">Date:</label>
                    <input type="date" id="issue_date" name="issue_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ date('Y-m-d') }}" readonly>
                </div>
                <div>
                    <label for="issue_time" class="block text-gray-700 text-sm font-bold mb-2">Time:</label>
                    <input type="time" id="issue_time" name="issue_time" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ date('H:i') }}" readonly>
                </div>
            </div>

            <!-- Employee Details -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Issued To (Employee)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="employee_search" class="block text-gray-700 text-sm font-bold mb-2">Employee ID/Name:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="employee_search" name="employee_search" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search Employee" readonly required>
                        <button type="button" id="search_employee_btn" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                    <input type="hidden" id="employee_id" name="employee_id" required>
                    <input type="hidden" id="employee_name_hidden" name="employee_name_hidden">
                </div>
                <div>
                    <label for="employee_department" class="block text-gray-700 text-sm font-bold mb-2">Department:</label>
                    <input type="text" id="employee_department" name="employee_department" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="employee_designation" class="block text-gray-700 text-sm font-bold mb-2">Designation:</label>
                    <input type="text" id="employee_designation" name="employee_designation" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
            </div>

            <!-- Item Details -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Item Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div>
                    <label for="item_search" class="block text-gray-700 text-sm font-bold mb-2">Item ID/Name:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="item_search" name="item_search" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search Item" readonly>
                        <button type="button" id="search_item_btn" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                    <input type="hidden" id="item_id" name="item_id">
                    <input type="hidden" id="item_name_hidden" name="item_name_hidden">
                    <input type="hidden" id="item_unit_hidden" name="item_unit_hidden">
                    <input type="hidden" id="item_price_hidden" name="item_price_hidden">
                    <input type="hidden" id="item_type_hidden" name="item_type_hidden"> {{-- Added to pass item type --}}
                </div>
                <div>
                    <label for="available_quantity" class="block text-gray-700 text-sm font-bold mb-2">Available Quantity:</label>
                    <input type="number" id="available_quantity" name="available_quantity" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" placeholder="Auto-populated" readonly>
                </div>
                <div>
                    <label for="issue_quantity" class="block text-gray-700 text-sm font-bold mb-2">Issue Quantity:</label>
                    <input type="number" id="issue_quantity" name="issue_quantity" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter quantity" min="1">
                </div>
            </div>
            <div class="flex justify-end mb-6">
                <button type="button" id="add_item_to_cart_btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Add Item to List
                </button>
            </div>

            <!-- Item Cart Table -->
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (per unit)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="issued_items_table_body" class="divide-y divide-gray-200">
                        {{-- Items will be added here dynamically by JavaScript --}}
                    </tbody>
                </table>
            </div>

            <!-- Summary and Remarks -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="remarks" class="block text-gray-700 text-sm font-bold mb-2">Remarks:</label>
                    <textarea id="remarks" name="remarks" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Any additional remarks"></textarea>
                </div>
                 <div class="flex flex-col justify-end">
                    <label for="total_issued_quantity" class="block text-gray-700 text-sm font-bold mb-2">Total Issued Quantity:</label>
                    <input type="text" id="total_issued_quantity" name="total_issued_quantity" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="0" readonly>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" id="issued_btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Issued
                </button>
                <button type="button" id="issued_and_print_btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Issued and Print
                </button>
                <button type="button" id="new_issue_btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    New
                </button>
            </div>
        </form>
    </div>

    <!-- Employee Search Modal -->
    <div id="employeeSearchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Search Employee</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('employeeSearchModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="mb-4">
                <input type="text" id="employee_search_input_modal" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search by ID or Name">
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Designation</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employee_search_results_body" class="divide-y divide-gray-200">
                        {{-- Static Employee Data for Search --}}
                        @php
                            $staticEmployees = [
                                ['id' => 'EMP001', 'name' => 'Dr. Jane Doe', 'department' => 'Cardiology', 'designation' => 'Consultant'],
                                ['id' => 'EMP002', 'name' => 'Nurse Emily', 'department' => 'Emergency', 'designation' => 'Staff Nurse'],
                                ['id' => 'EMP003', 'name' => 'John Smith', 'department' => 'Pharmacy', 'designation' => 'Pharmacist'],
                                ['id' => 'EMP004', 'name' => 'Sarah Brown', 'department' => 'Admin', 'designation' => 'Receptionist'],
                            ];
                        @endphp
                        @foreach($staticEmployees as $emp)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $emp['id'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $emp['name'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $emp['department'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $emp['designation'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                    <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-full select-employee-btn"
                                        data-id="{{ $emp['id'] }}" data-name="{{ $emp['name'] }}"
                                        data-department="{{ $emp['department'] }}" data-designation="{{ $emp['designation'] }}">Select</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Item Search Modal -->
    <div id="itemSearchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Search Item</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('itemSearchModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="mb-4">
                <input type="text" id="item_search_input_modal" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search by ID, Name or Barcode">
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Available Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="item_search_results_body" class="divide-y divide-gray-200">
                        {{-- Static Item Data for Search --}}
                        @php
                            $staticItems = [
                                ['id' => 'C001', 'name' => 'Bandage Roll', 'type' => 'Consumable', 'unit' => 'pcs', 'price' => 5.00, 'available_qty' => 150],
                                ['id' => 'C002', 'name' => 'Syringe 5ml', 'type' => 'Consumable', 'unit' => 'pcs', 'price' => 2.50, 'available_qty' => 500],
                                ['id' => 'N001', 'name' => 'Hospital Bed', 'type' => 'Non-Consumable', 'unit' => 'unit', 'price' => 1200.00, 'available_qty' => 10],
                                ['id' => 'N002', 'name' => 'ECG Machine', 'type' => 'Non-Consumable', 'unit' => 'unit', 'price' => 5000.00, 'available_qty' => 3],
                                ['id' => 'C003', 'name' => 'Painkiller Tablet', 'type' => 'Consumable', 'unit' => 'tablet', 'price' => 1.00, 'available_qty' => 2000],
                            ];
                        @endphp
                        @foreach($staticItems as $item)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['id'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['name'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['type'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['unit'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${{ number_format($item['price'], 2) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $item['available_qty'] }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                    <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-full select-item-btn"
                                        data-id="{{ $item['id'] }}" data-name="{{ $item['name'] }}"
                                        data-type="{{ $item['type'] }}" data-unit="{{ $item['unit'] }}"
                                        data-price="{{ $item['price'] }}" data-available-qty="{{ $item['available_qty'] }}">Select</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Format (Hidden) -->
    <div id="print_area" class="hidden p-8 bg-white border border-gray-300 rounded-lg shadow-xl" style="width: 210mm; min-height: 297mm; margin: 20mm auto; font-family: 'Inter', sans-serif;">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Hospital Store</h1>
            <h2 class="text-2xl font-semibold text-gray-700">Stock Issue Slip</h2>
        </div>

        <div class="mb-6 border-b pb-4">
            <div class="flex justify-between mb-2">
                <p class="text-lg text-gray-700"><strong>Transaction ID:</strong> <span id="print_transaction_id"></span></p>
                <p class="text-lg text-gray-700"><strong>Date:</strong> <span id="print_issue_date"></span></p>
            </div>
            <p class="text-lg text-gray-700 mb-2"><strong>Issued To:</strong> <span id="print_employee_name"></span> (<span id="print_employee_id"></span>)</p>
            <p class="text-lg text-gray-700"><strong>Department:</strong> <span id="print_employee_department"></span>, <strong>Designation:</strong> <span id="print_employee_designation"></span></p>
        </div>

        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Issued Items:</h3>
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="border px-4 py-2 text-left text-sm font-medium text-gray-600">Sr. No.</th>
                        <th class="border px-4 py-2 text-left text-sm font-medium text-gray-600">Item Name</th>
                        <th class="border px-4 py-2 text-left text-sm font-medium text-gray-600">Unit</th>
                        <th class="border px-4 py-2 text-right text-sm font-medium text-gray-600">Price</th>
                        <th class="border px-4 py-2 text-right text-sm font-medium text-gray-600">Issued Qty</th>
                        <th class="border px-4 py-2 text-right text-sm font-medium text-gray-600">Total</th>
                    </tr>
                </thead>
                <tbody id="print_issued_items_body">
                    {{-- Items will be populated here --}}
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-300">
                        <td colspan="4" class="border px-4 py-2 text-right text-lg font-bold text-gray-800">Total Issued Quantity:</td>
                        <td class="border px-4 py-2 text-right text-lg font-bold text-gray-800" id="print_total_issued_quantity"></td>
                        <td class="border px-4 py-2 text-right text-lg font-bold text-gray-800" id="print_grand_total_price"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mb-8">
            <p class="text-lg text-gray-700"><strong>Remarks:</strong> <span id="print_remarks"></span></p>
        </div>

        <div class="flex justify-between items-end mt-16">
            <div class="text-center">
                <p class="border-t border-gray-500 pt-2 text-gray-700">Issued By (Signature)</p>
            </div>
            <div class="text-center">
                <p class="border-t border-gray-500 pt-2 text-gray-700">Received By (Signature)</p>
            </div>
        </div>

        <div class="text-center text-sm text-gray-500 mt-8">
            <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
            <p>Hospital Management System</p>
        </div>
    </div>


    <script>
        // --- Main Form Elements ---
        const issueStockForm = document.getElementById('issue_stock_form');
        const transactionIdInput = document.getElementById('transaction_id');
        const issueDateInput = document.getElementById('issue_date');
        const issueTimeInput = document.getElementById('issue_time');

        const employeeSearchInput = document.getElementById('employee_search');
        const employeeIdHidden = document.getElementById('employee_id');
        const employeeNameHidden = document.getElementById('employee_name_hidden');
        const employeeDepartmentInput = document.getElementById('employee_department');
        const employeeDesignationInput = document.getElementById('employee_designation');
        const searchEmployeeBtn = document.getElementById('search_employee_btn');

        const itemSearchInput = document.getElementById('item_search');
        const itemIdHidden = document.getElementById('item_id');
        const itemNameHidden = document.getElementById('item_name_hidden');
        const itemUnitHidden = document.getElementById('item_unit_hidden');
        const itemPriceHidden = document.getElementById('item_price_hidden');
        const availableQuantityInput = document.getElementById('available_quantity');
        const issueQuantityInput = document.getElementById('issue_quantity');
        const searchItemBtn = document.getElementById('search_item_btn');
        const addItemToCartBtn = document.getElementById('add_item_to_cart_btn');
        const remarksInput = document.getElementById('remarks');

        const issuedItemsTableBody = document.getElementById('issued_items_table_body');
        const totalIssuedQuantityInput = document.getElementById('total_issued_quantity');

        const issuedBtn = document.getElementById('issued_btn');
        const issuedAndPrintBtn = document.getElementById('issued_and_print_btn');
        const newIssueBtn = document.getElementById('new_issue_btn');

        let issuedItems = []; // Array to hold items added to the cart

        // --- Modals ---
        const employeeSearchModal = document.getElementById('employeeSearchModal');
        const employeeSearchInputModal = document.getElementById('employee_search_input_modal');
        const employeeSearchResultsBody = document.getElementById('employee_search_results_body');

        const itemSearchModal = document.getElementById('itemSearchModal');
        const itemSearchInputModal = document.getElementById('item_search_input_modal');
        const itemSearchResultsBody = document.getElementById('item_search_results_body');

        // --- Static Data (for simulation, replace with AJAX calls to backend later) ---
        const ALL_EMPLOYEES = [
            { id: 'EMP001', name: 'Dr. Jane Doe', department: 'Cardiology', designation: 'Consultant' },
            { id: 'EMP002', name: 'Nurse Emily', department: 'Emergency', designation: 'Staff Nurse' },
            { id: 'EMP003', name: 'John Smith', department: 'Pharmacy', designation: 'Pharmacist' },
            { id: 'EMP004', name: 'Sarah Brown', department: 'Admin', designation: 'Receptionist' },
            { id: 'EMP005', name: 'Lab Tech Alex', department: 'Laboratory', designation: 'Technician' },
        ];

        const ALL_ITEMS = [
            { id: 'C001', name: 'Bandage Roll', type: 'Consumable', unit: 'pcs', price: 5.00, available_qty: 150 },
            { id: 'C002', name: 'Syringe 5ml', type: 'Consumable', unit: 'pcs', price: 2.50, available_qty: 500 },
            { id: 'N001', name: 'Hospital Bed', type: 'Non-Consumable', unit: 'unit', price: 1200.00, available_qty: 10 },
            { id: 'N002', name: 'ECG Machine', type: 'Non-Consumable', unit: 'unit', price: 5000.00, available_qty: 3 },
            { id: 'C003', name: 'Painkiller Tablet', type: 'Consumable', unit: 'tablet', price: 1.00, available_qty: 2000 },
            { id: 'C004', 'name': 'Gloves (Pair)', 'type': 'Consumable', 'unit': 'pair', 'price': 0.75, 'available_qty': 1000 },
            { id: 'N003', 'name': 'Wheelchair', 'type': 'Non-Consumable', 'unit': 'unit', 'price': 300.00, 'available_qty': 5 },
        ];

        // --- General Modal Functions ---
        function openModal(modal) {
            modal.classList.remove('hidden');
        }

        function closeModal(modal) {
            modal.classList.add('hidden');
        }

        // --- Employee Search Logic ---
        searchEmployeeBtn.addEventListener('click', () => {
            employeeSearchInputModal.value = ''; // Clear previous search
            renderEmployeeSearchResults(ALL_EMPLOYEES); // Show all initially
            openModal(employeeSearchModal);
        });

        employeeSearchInputModal.addEventListener('input', () => {
            const searchTerm = employeeSearchInputModal.value.toLowerCase();
            const filteredEmployees = ALL_EMPLOYEES.filter(emp =>
                emp.id.toLowerCase().includes(searchTerm) ||
                emp.name.toLowerCase().includes(searchTerm)
            );
            renderEmployeeSearchResults(filteredEmployees);
        });

        function renderEmployeeSearchResults(employees) {
            employeeSearchResultsBody.innerHTML = '';
            employees.forEach(emp => {
                const row = employeeSearchResultsBody.insertRow();
                row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${emp.id}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${emp.name}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${emp.department}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${emp.designation}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-full select-employee-btn"
                            data-id="${emp.id}" data-name="${emp.name}"
                            data-department="${emp.department}" data-designation="${emp.designation}">Select</button>
                    </td>
                `;
            });
            // Attach event listeners to new select buttons
            employeeSearchResultsBody.querySelectorAll('.select-employee-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const { id, name, department, designation } = event.target.dataset;
                    employeeSearchInput.value = `${name} (${id})`;
                    employeeIdHidden.value = id;
                    employeeNameHidden.value = name;
                    employeeDepartmentInput.value = department;
                    employeeDesignationInput.value = designation;
                    closeModal(employeeSearchModal);
                });
            });
        }

        // --- Item Search Logic ---
        searchItemBtn.addEventListener('click', () => {
            itemSearchInputModal.value = ''; // Clear previous search
            renderItemSearchResults(ALL_ITEMS); // Show all initially
            openModal(itemSearchModal);
        });

        itemSearchInputModal.addEventListener('input', () => {
            const searchTerm = itemSearchInputModal.value.toLowerCase();
            const filteredItems = ALL_ITEMS.filter(item =>
                item.id.toLowerCase().includes(searchTerm) ||
                item.name.toLowerCase().includes(searchTerm)
            );
            renderItemSearchResults(filteredItems);
        });

        function renderItemSearchResults(items) {
            itemSearchResultsBody.innerHTML = '';
            items.forEach(item => {
                const row = itemSearchResultsBody.insertRow();
                row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.id}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.name}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.type}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.unit}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${item.price.toFixed(2)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.available_qty}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-full select-item-btn"
                            data-id="${item.id}" data-name="${item.name}"
                            data-type="${item.type}" data-unit="${item.unit}"
                            data-price="${item.price}" data-available-qty="${item.available_qty}">Select</button>
                    </td>
                `;
            });
            // Attach event listeners to new select buttons
            itemSearchResultsBody.querySelectorAll('.select-item-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const { id, name, unit, price, availableQty, type } = event.target.dataset; // Added type
                    itemSearchInput.value = `${name} (${id})`;
                    itemIdHidden.value = id;
                    itemNameHidden.value = name;
                    itemUnitHidden.value = unit;
                    itemPriceHidden.value = price;
                    availableQuantityInput.value = availableQty;
                    issueQuantityInput.max = availableQty; // Set max for issue quantity
                    issueQuantityInput.value = ''; // Clear previous issue quantity
                    itemTypeHidden.value = type; // Store item type
                    closeModal(itemSearchModal);
                });
            });
        }

        // --- Add Item to Cart Logic ---
        addItemToCartBtn.addEventListener('click', () => {
            const itemId = itemIdHidden.value;
            const itemName = itemNameHidden.value;
            const itemUnit = itemUnitHidden.value;
            const itemPrice = parseFloat(itemPriceHidden.value);
            const itemType = itemTypeHidden.value; // Get item type
            const availableQty = parseInt(availableQuantityInput.value);
            const issueQty = parseInt(issueQuantityInput.value);

            if (!itemId || !itemName || isNaN(itemPrice) || isNaN(availableQty) || isNaN(issueQty) || issueQty <= 0) {
                alert('Please select an item and enter a valid issue quantity.');
                return;
            }
            if (issueQty > availableQty) {
                alert('Issue quantity cannot exceed available quantity.');
                return;
            }
            if (issuedItems.some(item => item.id === itemId)) {
                alert('This item is already in the list. Edit the quantity or remove it first.');
                return;
            }

            const totalPrice = itemPrice * issueQty;

            const newItem = {
                id: itemId,
                name: itemName,
                unit: itemUnit,
                price: itemPrice,
                type: itemType, // Store item type in cart
                available_qty: availableQty, // Keep track of original available
                issue_qty: issueQty,
                total_price: totalPrice
            };
            issuedItems.push(newItem);
            renderIssuedItemsTable();
            calculateTotals();
            clearItemSelection();
        });

        function renderIssuedItemsTable() {
            issuedItemsTableBody.innerHTML = '';
            let srNo = 1;
            issuedItems.forEach(item => {
                const row = issuedItemsTableBody.insertRow();
                row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${srNo++}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.name}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.unit}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${item.price.toFixed(2)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.issue_qty}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${item.total_price.toFixed(2)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="text-red-600 hover:text-red-900 remove-issued-item-btn" data-id="${item.id}">Remove</button>
                    </td>
                `;
            });

            issuedItemsTableBody.querySelectorAll('.remove-issued-item-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const idToRemove = event.target.dataset.id;
                    issuedItems = issuedItems.filter(item => item.id !== idToRemove);
                    renderIssuedItemsTable();
                    calculateTotals();
                });
            });
        }

        function calculateTotals() {
            let totalQty = 0;
            let grandTotalPrice = 0;
            issuedItems.forEach(item => {
                totalQty += item.issue_qty;
                grandTotalPrice += item.total_price;
            });
            totalIssuedQuantityInput.value = totalQty;

            // Update print area totals as well
            document.getElementById('print_total_issued_quantity').textContent = totalQty;
            document.getElementById('print_grand_total_price').textContent = `$${grandTotalPrice.toFixed(2)}`;
        }

        function clearItemSelection() {
            itemSearchInput.value = '';
            itemIdHidden.value = '';
            itemNameHidden.value = '';
            itemUnitHidden.value = '';
            itemPriceHidden.value = '';
            availableQuantityInput.value = '';
            issueQuantityInput.value = '';
            issueQuantityInput.max = '';
            itemTypeHidden.value = ''; // Clear item type
        }

        // --- Form Actions ---
        issuedBtn.addEventListener('click', async () => { // Made async
            if (issuedItems.length === 0) {
                alert('Please add at least one item to issue.');
                return;
            }
            if (!employeeIdHidden.value) {
                alert('Please select an employee to issue stock to.');
                return;
            }

            const issueData = {
                transaction_id: transactionIdInput.value,
                issue_date: issueDateInput.value,
                issue_time: issueTimeInput.value,
                employee_id: employeeIdHidden.value,
                employee_name: employeeNameHidden.value,
                employee_department: employeeDepartmentInput.value,
                employee_designation: employeeDesignationInput.value,
                total_issued_quantity: parseInt(totalIssuedQuantityInput.value),
                remarks: remarksInput.value,
                issued_items: issuedItems.map(item => ({ // Map to send only necessary data
                    id: item.id,
                    name: item.name,
                    unit: item.unit,
                    price: item.price,
                    issue_qty: item.issue_qty,
                    total_price: item.total_price,
                    type: item.type // Include type for backend stock update logic
                }))
            };

            try {
                const response = await fetch('/api/issue/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(issueData)
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    let errorMessage = 'Failed to issue stock.';
                    if (errorData.message) {
                        errorMessage += '\n' + errorData.message;
                    }
                    if (errorData.errors) {
                        for (const field in errorData.errors) {
                            errorMessage += `\n${field}: ${errorData.errors[field].join(', ')}`;
                        }
                    }
                    alert(errorMessage);
                    return;
                }

                const result = await response.json();
                alert(`Stock Issued Successfully! Transaction ID: ${result.transaction_id}`);
                resetForm();
            } catch (error) {
                console.error('Error issuing stock:', error);
                alert('An error occurred while issuing stock. Please try again.');
            }
        });

        issuedAndPrintBtn.addEventListener('click', async () => { // Made async
            if (issuedItems.length === 0) {
                alert('Please add at least one item to issue.');
                return;
            }
            if (!employeeIdHidden.value) {
                alert('Please select an employee to issue stock to.');
                return;
            }

            const issueData = {
                transaction_id: transactionIdInput.value,
                issue_date: issueDateInput.value,
                issue_time: issueTimeInput.value,
                employee_id: employeeIdHidden.value,
                employee_name: employeeNameHidden.value,
                employee_department: employeeDepartmentInput.value,
                employee_designation: employeeDesignationInput.value,
                total_issued_quantity: parseInt(totalIssuedQuantityInput.value),
                remarks: remarksInput.value,
                issued_items: issuedItems.map(item => ({
                    id: item.id,
                    name: item.name,
                    unit: item.unit,
                    price: item.price,
                    issue_qty: item.issue_qty,
                    total_price: item.total_price,
                    type: item.type
                }))
            };

            try {
                const response = await fetch('/api/issue/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(issueData)
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    let errorMessage = 'Failed to issue stock for printing.';
                    if (errorData.message) {
                        errorMessage += '\n' + errorData.message;
                    }
                    if (errorData.errors) {
                        for (const field in errorData.errors) {
                            errorMessage += `\n${field}: ${errorData.errors[field].join(', ')}`;
                        }
                    }
                    alert(errorMessage);
                    return;
                }

                const result = await response.json();
                alert(`Stock Issued Successfully! Printing Slip for Transaction ID: ${result.transaction_id}`);
                populatePrintArea();
                printDiv('print_area');
                resetForm();
            } catch (error) {
                console.error('Error issuing stock for print:', error);
                alert('An error occurred while issuing stock for print. Please try again.');
            }
        });

        newIssueBtn.addEventListener('click', () => {
            resetForm();
            alert('Form reset for a new issue transaction.');
        });

        function resetForm() {
            issueStockForm.reset();
            // Re-generate new transaction ID
            transactionIdInput.value = `TXN-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;
            issueDateInput.value = `{{ date('Y-m-d') }}`;
            issueTimeInput.value = `{{ date('H:i') }}`;

            employeeIdHidden.value = '';
            employeeNameHidden.value = '';
            employeeDepartmentInput.value = '';
            employeeDesignationInput.value = '';

            issuedItems = [];
            renderIssuedItemsTable();
            calculateTotals(); // Reset totals display
            clearItemSelection();
        }

        // --- Print Functionality ---
        function populatePrintArea() {
            document.getElementById('print_transaction_id').textContent = transactionIdInput.value;
            document.getElementById('print_issue_date').textContent = issueDateInput.value;
            document.getElementById('print_employee_name').textContent = employeeNameHidden.value;
            document.getElementById('print_employee_id').textContent = employeeIdHidden.value;
            document.getElementById('print_employee_department').textContent = employeeDepartmentInput.value;
            document.getElementById('print_employee_designation').textContent = employeeDesignationInput.value;
            document.getElementById('print_remarks').textContent = remarksInput.value || 'N/A';

            const printTableBody = document.getElementById('print_issued_items_body');
            printTableBody.innerHTML = '';
            let printSrNo = 1;
            issuedItems.forEach(item => {
                const row = printTableBody.insertRow();
                row.innerHTML = `
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${printSrNo++}</td>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${item.name}</td>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${item.unit}</td>
                    <td class="border px-4 py-2 text-right text-sm text-gray-900">$${item.price.toFixed(2)}</td>
                    <td class="border px-4 py-2 text-right text-sm text-gray-900">${item.issue_qty}</td>
                    <td class="border px-4 py-2 text-right text-sm text-gray-900">$${item.total_price.toFixed(2)}</td>
                `;
            });
        }

        function printDiv(divId) {
            const printContents = document.getElementById(divId).innerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = originalContents; // Restore original content first
            const printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(`
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Print Stock Issue Slip</title>
                    <style>
                        /* Basic print styles */
                        body { font-family: 'Inter', sans-serif; margin: 0; padding: 20mm; }
                        h1, h2, h3 { color: #1f2937; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
                        th { background-color: #f3f4f6; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .mb-8 { margin-bottom: 2rem; }
                        .mb-6 { margin-bottom: 1.5rem; }
                        .mb-4 { margin-bottom: 1rem; }
                        .mb-2 { margin-bottom: 0.5rem; }
                        .mt-8 { margin-top: 2rem; }
                        .mt-16 { margin-top: 4rem; }
                        .border-b { border-bottom: 1px solid #e5e7eb; }
                        .border-t-2 { border-top: 2px solid #d1d5db; }
                        .font-bold { font-weight: 700; }
                        .font-semibold { font-weight: 600; }
                        .text-sm { font-size: 0.875rem; }
                        .text-lg { font-size: 1.125rem; }
                        .text-xl { font-size: 1.25rem; }
                        .text-2xl { font-size: 1.5rem; }
                        .text-4xl { font-size: 2.25rem; }
                        .text-gray-800 { color: #1f2937; }
                        .text-gray-700 { color: #374151; }
                        .text-gray-600 { color: #4b5563; }
                        .text-gray-500 { color: #6b7280; }
                        .bg-gray-100 { background-color: #f3f4f6; }
                        .bg-gray-50 { background-color: #f9fafb; }
                        .flex { display: flex; }
                        .justify-between { justify-content: space-between; }
                        .items-end { align-items: flex-end; }
                        .pt-2 { padding-top: 0.5rem; }
                        .border-t { border-top: 1px solid #6b7280; }
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
            // No need to reload the main window if print window is used
        }

        // Initial setup on page load
        document.addEventListener('DOMContentLoaded', () => {
            // Ensure transaction ID, date, time are set on load
            transactionIdInput.value = `TXN-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;
            issueDateInput.value = `{{ date('Y-m-d') }}`;
            issueTimeInput.value = `{{ date('H:i') }}`;
        });
    </script>
@endsection
