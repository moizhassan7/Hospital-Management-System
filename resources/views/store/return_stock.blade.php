@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Return Stock</h2>
        <a href="{{ route('store.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Store Management
        </a>
    </div>

    <!-- Return Stock Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form id="return_stock_form" action="#" method="POST">
            @csrf

            <!-- Return Details -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Return Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div>
                    <label for="return_id" class="block text-gray-700 text-sm font-bold mb-2">Return ID:</label>
                    <input type="text" id="return_id" name="return_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="RTN-{{ strtoupper(Str::random(6)) }}" readonly>
                </div>
                <div>
                    <label for="return_date" class="block text-gray-700 text-sm font-bold mb-2">Return Date:</label>
                    <input type="date" id="return_date" name="return_date" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none" value="{{ date('Y-m-d') }}" readonly>
                </div>
                <div class="lg:col-span-2">
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
            </div>

            <!-- Issued Items for Return -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Issued Items (Select to Return)</h3>
            <div id="issued_items_container" class="overflow-x-auto mb-6">
                <table class="min-w-full bg-white rounded-lg overflow-hidden border border-gray-200">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Select</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued On</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="issued_items_body" class="divide-y divide-gray-200">
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">Please select an employee to see issued items.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Remarks -->
            <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2 mt-8">Return Details</h3>
            <div class="mb-6">
                <label for="remarks" class="block text-gray-700 text-sm font-bold mb-2">Remarks for Return:</label>
                <textarea id="remarks" name="remarks" rows="3" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Item is no longer needed"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 mt-6">
                <button type="submit" id="return_btn" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Process Return
                </button>
                <button type="button" id="new_return_btn" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    New Return
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
                        {{-- Static Employee Data for Search (will be dynamic via API call) --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // --- Main Form Elements ---
        const returnStockForm = document.getElementById('return_stock_form');
        const returnIdInput = document.getElementById('return_id');
        const returnDateInput = document.getElementById('return_date');
        const employeeSearchInput = document.getElementById('employee_search');
        const employeeIdHidden = document.getElementById('employee_id');
        const employeeNameHidden = document.getElementById('employee_name_hidden');
        const searchEmployeeBtn = document.getElementById('search_employee_btn');
        const issuedItemsBody = document.getElementById('issued_items_body');
        const remarksInput = document.getElementById('remarks');
        const returnBtn = document.getElementById('return_btn');
        const newReturnBtn = document.getElementById('new_return_btn');

        // --- Modals ---
        const employeeSearchModal = document.getElementById('employeeSearchModal');
        const employeeSearchInputModal = document.getElementById('employee_search_input_modal');
        const employeeSearchResultsBody = document.getElementById('employee_search_results_body');
        
        // --- General Modal Functions ---
        function openModal(modal) {
            modal.classList.remove('hidden');
        }

        function closeModal(modal) {
            modal.classList.add('hidden');
        }

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
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            return await response.json();
        }
        
        // --- Employee Search & Item Fetching Logic ---
        searchEmployeeBtn.addEventListener('click', async () => {
            employeeSearchInputModal.value = '';
            try {
                // In a real app, you would fetch all active employees via API
                const employees = await fetchData('/api/issue/search-employees'); // Using the same endpoint
                renderEmployeeSearchResults(employees);
                openModal(employeeSearchModal);
            } catch (error) {
                console.error('Error fetching employees:', error);
                alert('Failed to load employee data. Please check the backend.');
            }
        });

        employeeSearchInput.addEventListener('input', () => {
            employeeIdHidden.value = '';
            employeeNameHidden.value = '';
            issuedItemsBody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Please select an employee to see issued items.</td></tr>`;
        });
        
        employeeSearchResultsBody.addEventListener('click', async (event) => {
            if (event.target.classList.contains('select-employee-btn')) {
                const { id, name } = event.target.dataset;
                employeeSearchInput.value = `${name} (${id})`;
                employeeIdHidden.value = id;
                employeeNameHidden.value = name;
                closeModal(employeeSearchModal);
                await fetchIssuedItems(id); // Fetch items for the selected employee
            }
        });

        async function fetchIssuedItems(employeeId) {
            issuedItemsBody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Loading issued items...</td></tr>`;
            try {
                const items = await fetchData(`/api/return/search-issued-items?employee_id=${employeeId}`);
                if (items.length > 0) {
                    issuedItemsBody.innerHTML = '';
                    items.forEach(item => {
                        const row = issuedItemsBody.insertRow();
                        row.innerHTML = `
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                <input type="checkbox" class="form-checkbox h-5 w-5 text-green-600 rounded return-item-checkbox" 
                                       data-item-id="${item.item_id}" data-item-name="${item.item_name}" data-item-type="${item.item_type}" data-qty="${item.quantity}">
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.item_name}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.item_type}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.issued_on}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">N/A</td>
                        `;
                    });
                } else {
                    issuedItemsBody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">No non-consumable items issued to this employee.</td></tr>`;
                }
            } catch (error) {
                console.error('Error fetching issued items:', error);
                issuedItemsBody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">Failed to load issued items.</td></tr>`;
            }
        }

        // --- Form Actions ---
        returnBtn.addEventListener('click', async (event) => {
            event.preventDefault();
            const selectedItems = Array.from(document.querySelectorAll('.return-item-checkbox:checked')).map(checkbox => checkbox.dataset);
            
            if (selectedItems.length === 0) {
                alert('Please select at least one item to return.');
                return;
            }
            if (!employeeIdHidden.value) {
                alert('Please select an employee to process the return.');
                return;
            }

            const returnData = selectedItems.map(item => ({
                return_id: `RTN-${Math.random().toString(36).substring(2, 8).toUpperCase()}`,
                return_date: returnDateInput.value,
                employee_id: employeeIdHidden.value,
                employee_name: employeeSearchInput.value.split('(')[0].trim(),
                item_id: item.itemId,
                item_name: item.itemName,
                item_type: item.itemType,
                quantity: parseInt(item.quantity),
                remarks: remarksInput.value,
            }));
            
            try {
                // In a real app, you would send a single request with all items, but for this structure, we'll loop
                for (const item of returnData) {
                    await sendData('/api/return/store', 'POST', item);
                }
                alert('Selected items returned successfully!');
                resetForm();
            } catch (error) {
                console.error('Error processing return:', error);
                alert('An error occurred while processing the return. Please try again.');
            }
        });
        
        newReturnBtn.addEventListener('click', () => {
            resetForm();
        });

        function resetForm() {
            returnStockForm.reset();
            returnIdInput.value = `RTN-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;
            returnDateInput.value = `{{ date('Y-m-d') }}`;
            employeeIdHidden.value = '';
            employeeNameHidden.value = '';
            issuedItemsBody.innerHTML = `<tr><td colspan="5" class="px-4 py-4 text-center text-gray-500">Please select an employee to see issued items.</td></tr>`;
        }

        // Initial setup on page load
        document.addEventListener('DOMContentLoaded', () => {
            returnIdInput.value = `RTN-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;
        });
    </script>
@endsection
