@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Purchase Stock</h2></div>
        <a href="{{ route('store.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Store Management
        </a>
    </div>

    <!-- Purchase Stock Form -->
    <div class="hms-panel hms-panel-padded mb-5">
        <form id="purchase_stock_form" action="#" method="POST">
            @csrf

            <!-- Purchase Details -->
            <h3 class="hms-section-title">Purchase Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div>
                    <label for="purchase_id" class="hms-label">Purchase ID:</label>
                    <input type="text" id="purchase_id" name="purchase_id" class="hms-input hms-input-readonly" value="PUR-{{ strtoupper(Str::random(6)) }}" readonly>
                </div>
                <div>
                    <label for="purchase_date" class="hms-label">Date:</label>
                    <input type="date" id="purchase_date" name="purchase_date" class="hms-input hms-input-readonly" value="{{ date('Y-m-d') }}" readonly>
                </div>
                <div>
                    <label for="bill_id" class="hms-label">Bill ID:</label>
                    <input type="text" id="bill_id" name="bill_id" class="hms-input" placeholder="e.g., INV-001" required>
                </div>
                <div>
                    <label for="bill_date" class="hms-label">Bill Date:</label>
                    <input type="date" id="bill_date" name="bill_date" class="hms-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="lg:col-span-2">
                    <label for="supplier_name" class="hms-label">Supplier Name:</label>
                    <input type="text" id="supplier_name" name="supplier_name" class="hms-input" placeholder="e.g., Pharma Inc." required>
                </div>
            </div>

            <!-- Item Details -->
            <h3 class="hms-section-title mt-8">Add Item to Bill</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="lg:col-span-2">
                    <label for="item_search" class="hms-label">Item ID/Name:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="item_search" name="item_search" class="hms-input" placeholder="Search Item">
                        <button type="button" id="search_item_btn" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </div>
                    <input type="hidden" id="item_id" name="item_id">
                    <input type="hidden" id="item_name_hidden" name="item_name_hidden">
                    <input type="hidden" id="item_unit_hidden" name="item_unit_hidden">
                    <input type="hidden" id="item_type_hidden" name="item_type_hidden">
                </div>
                <div>
                    <label for="qty" class="hms-label">Quantity:</label>
                    <input type="number" id="qty" name="qty" class="hms-input" placeholder="0" min="1">
                </div>
                <div>
                    <label for="purchase_price" class="hms-label">Purchase Price ($):</label>
                    <input type="number" id="purchase_price" name="purchase_price" class="hms-input" placeholder="0.00" min="0" step="0.01">
                </div>
                <div>
                    <label for="sale_price" class="hms-label">Sale Price ($):</label>
                    <input type="number" id="sale_price" name="sale_price" class="hms-input" placeholder="0.00" min="0" step="0.01">
                </div>
                <div>
                    <label for="tax" class="hms-label">Tax (%):</label>
                    <input type="number" id="tax" name="tax" class="hms-input" placeholder="0" min="0" max="100">
                </div>
                <div>
                    <label for="discount_value" class="hms-label">Discount:</label>
                    <input type="number" id="discount_value" name="discount_value" class="hms-input" placeholder="0" min="0" step="0.01">
                </div>
                <div class="flex flex-col justify-end">
                    <div class="hms-search-bar">
                        <label class="hms-checkbox-row">
                            <input type="radio" name="discount_type" value="percentage" checked class="form-radio h-4 w-4 text-blue-600">
                            <span class="ml-2 text-gray-700 text-sm">Percentage</span>
                        </label>
                        <label class="hms-checkbox-row">
                            <input type="radio" name="discount_type" value="flat" class="form-radio h-4 w-4 text-blue-600">
                            <span class="ml-2 text-gray-700 text-sm">Flat</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label for="logistic_charges" class="hms-label">Logistic Charges ($):</label>
                    <input type="number" id="logistic_charges" name="logistic_charges" class="hms-input" placeholder="0.00" min="0" step="0.01">
                </div>
                <div>
                    <label for="extra_charges" class="hms-label">Extra Charges ($):</label>
                    <input type="number" id="extra_charges" name="extra_charges" class="hms-input" placeholder="0.00" min="0" step="0.01">
                </div>
            </div>
            <div class="flex justify-end mb-6">
                <button type="button" id="add_item_to_bill_btn" class="hms-btn hms-btn-indigo">
                    Add Item to Bill
                </button>
            </div>

            <!-- Bill Items Table -->
            <div class="hms-table-wrap mb-5">
                <table class="hms-table">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P. Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S. Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax (%)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bill_items_table_body" class="divide-y divide-gray-200">
                        {{-- Items will be added here dynamically by JavaScript --}}
                    </tbody>
                </table>
            </div>

            <!-- Payment Summary -->
            <h3 class="hms-section-title mt-8">Payment Summary</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div>
                    <label for="sub_total" class="hms-label">Sub Total ($):</label>
                    <input type="text" id="sub_total" name="sub_total" class="hms-input hms-input-readonly" value="0.00" readonly>
                </div>
                <div>
                    <label for="total_tax" class="hms-label">Total Tax ($):</label>
                    <input type="text" id="total_tax" name="total_tax" class="hms-input hms-input-readonly" value="0.00" readonly>
                </div>
                <div>
                    <label for="total_bill_discount" class="hms-label">Total Discount ($):</label>
                    <input type="text" id="total_bill_discount" name="total_bill_discount" class="hms-input hms-input-readonly" value="0.00" readonly>
                </div>
                <div>
                    <label for="grand_total" class="hms-label">Grand Total ($):</label>
                    <input type="text" id="grand_total" name="grand_total" class="hms-input hms-input-readonly" value="0.00" readonly>
                </div>
                <div class="lg:col-span-2">
                    <label for="received_payment" class="hms-label">Received Payment ($):</label>
                    <input type="number" id="received_payment" name="received_payment" class="hms-input" placeholder="0.00" min="0" step="0.01" value="0.00">
                </div>
                <div class="lg:col-span-2">
                    <label for="balance" class="hms-label">Balance ($):</label>
                    <input type="text" id="balance" name="balance" class="hms-input hms-input-readonly" value="0.00" readonly>
                </div>
                <div class="lg:col-span-4">
                    <label for="remarks" class="hms-label">Remarks:</label>
                    <textarea id="remarks" name="remarks" rows="3" class="hms-input" placeholder="Any additional remarks"></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="hms-form-actions">
                <button type="button" id="save_btn" class="hms-btn hms-btn-success">
                    Save
                </button>
                <button type="button" id="print_slip_btn" class="hms-btn hms-btn-primary">
                    Print Slip
                </button>
                <button type="button" id="new_btn" class="hms-btn hms-btn-danger">
                    New
                </button>
            </div>
        </form>
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
                <input type="text" id="item_search_input_modal" class="hms-input" placeholder="Search by ID, Name or Barcode">
            </div>
            <div class="hms-table-wrap">
                <table class="min-w-full bg-white border rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="item_search_results_body" class="divide-y divide-gray-200">
                        {{-- Items will be populated here dynamically by JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Print Format (Hidden) -->
    <div id="print_area" class="hidden p-8 bg-white border border-gray-300 rounded-lg shadow-xl" style="width: 210mm; min-height: 297mm; margin: 20mm auto; font-family: 'Inter', sans-serif;">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Hospital Store</h1>
            <h2 class="text-2xl font-semibold text-gray-700">Stock Purchase Slip</h2>
        </div>

        <div class="mb-6 border-b pb-4">
            <div class="flex justify-between mb-2">
                <p class="text-lg text-gray-700"><strong>Purchase ID:</strong> <span id="print_purchase_id"></span></p>
                <p class="text-lg text-gray-700"><strong>Date:</strong> <span id="print_purchase_date"></span></p>
            </div>
            <p class="text-lg text-gray-700 mb-2"><strong>Bill ID:</strong> <span id="print_bill_id"></span></p>
            <p class="text-lg text-gray-700"><strong>Supplier:</strong> <span id="print_supplier_name"></span></p>
        </div>

        <div class="mb-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Purchased Items:</h3>
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="border px-4 py-2 text-left text-sm font-medium text-gray-600">Item Name</th>
                        <th class="border px-4 py-2 text-left text-sm font-medium text-gray-600">Qty</th>
                        <th class="border px-4 py-2 text-left text-sm font-medium text-gray-600">P. Price</th>
                        <th class="border px-4 py-2 text-left text-sm font-medium text-gray-600">S. Price</th>
                        <th class="border px-4 py-2 text-right text-sm font-medium text-gray-600">Total</th>
                    </tr>
                </thead>
                <tbody id="print_bill_items_body">
                    {{-- Items will be populated here --}}
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 border-t-2 border-gray-300">
                        <td colspan="4" class="border px-4 py-2 text-right text-lg font-bold text-gray-800">Sub Total:</td>
                        <td class="border px-4 py-2 text-right text-lg font-bold text-gray-800" id="print_sub_total"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="border px-4 py-2 text-right text-lg font-bold text-gray-800">Total Discount:</td>
                        <td class="border px-4 py-2 text-right text-lg font-bold text-gray-800" id="print_total_bill_discount"></td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="4" class="border px-4 py-2 text-right text-lg font-bold text-gray-800">Total Tax:</td>
                        <td class="border px-4 py-2 text-right text-lg font-bold text-gray-800" id="print_total_tax"></td>
                    </tr>
                    <tr class="bg-gray-50 border-t-2 border-gray-300">
                        <td colspan="4" class="border px-4 py-2 text-right text-lg font-bold text-gray-800">Grand Total:</td>
                        <td class="border px-4 py-2 text-right text-lg font-bold text-gray-800" id="print_grand_total"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mb-8">
            <p class="text-lg text-gray-700"><strong>Remarks:</strong> <span id="print_remarks"></span></p>
        </div>

        <div class="flex justify-between items-end mt-16">
            <div class="text-center">
                <p class="border-t border-gray-500 pt-2 text-gray-700">Purchased By (Signature)</p>
            </div>
            <div class="text-center">
                <p class="border-t border-gray-500 pt-2 text-gray-700">Received By (Signature)</p>
            </div>
        </div>

        <div class="text-center text-sm text-gray-500 mt-8">
            <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>
            <p>{{ config('hospital.name') }}</p>
        </div>
    </div>

    <script>
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- Main Form Elements ---
        const purchaseStockForm = document.getElementById('purchase_stock_form');
        const purchaseIdInput = document.getElementById('purchase_id');
        const purchaseDateInput = document.getElementById('purchase_date');
        const billIdInput = document.getElementById('bill_id');
        const billDateInput = document.getElementById('bill_date');
        const supplierNameInput = document.getElementById('supplier_name');

        const itemSearchInput = document.getElementById('item_search');
        const itemIdHidden = document.getElementById('item_id');
        const itemNameHidden = document.getElementById('item_name_hidden');
        const itemUnitHidden = document.getElementById('item_unit_hidden');
        const itemTypeHidden = document.getElementById('item_type_hidden');
        const qtyInput = document.getElementById('qty');
        const purchasePriceInput = document.getElementById('purchase_price');
        const salePriceInput = document.getElementById('sale_price');
        const taxInput = document.getElementById('tax');
        const discountValueInput = document.getElementById('discount_value');
        const discountTypeRadios = document.querySelectorAll('input[name="discount_type"]');
        const logisticChargesInput = document.getElementById('logistic_charges');
        const extraChargesInput = document.getElementById('extra_charges');
        const addItemToBillBtn = document.getElementById('add_item_to_bill_btn');

        const billItemsTableBody = document.getElementById('bill_items_table_body');
        const subTotalInput = document.getElementById('sub_total');
        const totalTaxInput = document.getElementById('total_tax');
        const totalBillDiscountInput = document.getElementById('total_bill_discount');
        const grandTotalInput = document.getElementById('grand_total');
        const receivedPaymentInput = document.getElementById('received_payment');
        const balanceInput = document.getElementById('balance');
        const remarksInput = document.getElementById('remarks');

        const saveBtn = document.getElementById('save_btn');
        const printSlipBtn = document.getElementById('print_slip_btn');
        const newBtn = document.getElementById('new_btn');

        let billItems = [];

        // --- Modals ---
        const itemSearchModal = document.getElementById('itemSearchModal');
        const itemSearchInputModal = document.getElementById('item_search_input_modal');
        const itemSearchResultsBody = document.getElementById('item_search_results_body');

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

        // --- Item Search Logic ---
        searchItemBtn.addEventListener('click', () => {
            itemSearchInputModal.value = '';
            fetchAndRenderItems();
            openModal(itemSearchModal);
        });

        itemSearchInputModal.addEventListener('input', () => {
            const searchTerm = itemSearchInputModal.value;
            fetchAndRenderItems(searchTerm);
        });
        
        async function fetchAndRenderItems(searchTerm = '') {
            try {
                const url = `/api/purchase/search-items?query=${searchTerm}`;
                const items = await fetchData(url);
                itemSearchResultsBody.innerHTML = '';
                items.forEach(item => {
                    const row = itemSearchResultsBody.insertRow();
                    row.innerHTML = `
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.id}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.name}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.unit}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${item.purchase_price.toFixed(2)}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded-full select-item-btn"
                                data-id="${item.id}" data-name="${item.name}"
                                data-unit="${item.unit}" data-price="${item.purchase_price}"
                                data-sale-price="${item.sale_price}" data-type="${item.type}">Select</button>
                        </td>
                    `;
                });
                itemSearchResultsBody.querySelectorAll('.select-item-btn').forEach(button => {
                    button.addEventListener('click', (event) => {
                        const { id, name, unit, price, salePrice, type } = event.target.dataset;
                        itemSearchInput.value = `${name} (${id})`;
                        itemIdHidden.value = id;
                        itemNameHidden.value = name;
                        itemUnitHidden.value = unit;
                        itemTypeHidden.value = type;
                        purchasePriceInput.value = parseFloat(price).toFixed(2);
                        salePriceInput.value = parseFloat(salePrice).toFixed(2);
                        closeModal(itemSearchModal);
                    });
                });
            } catch (error) {
                console.error('Error fetching items:', error);
                itemSearchResultsBody.innerHTML = `<tr><td colspan="5" class="px-4 py-2 text-center text-red-500">Failed to load items.</td></tr>`;
            }
        }

        // --- Add Item to Bill Logic ---
        addItemToBillBtn.addEventListener('click', () => {
            const itemId = itemIdHidden.value;
            const itemName = itemNameHidden.value;
            const itemUnit = itemUnitHidden.value;
            const qty = parseInt(qtyInput.value);
            const purchasePrice = parseFloat(purchasePriceInput.value);
            const salePrice = parseFloat(salePriceInput.value);
            const tax = parseFloat(taxInput.value) || 0;
            const discountValue = parseFloat(discountValueInput.value) || 0;
            const discountType = document.querySelector('input[name="discount_type"]:checked').value;
            const logisticCharges = parseFloat(logisticChargesInput.value) || 0;
            const extraCharges = parseFloat(extraChargesInput.value) || 0;

            if (!itemId || !itemName || isNaN(qty) || qty <= 0 || isNaN(purchasePrice) || purchasePrice <= 0) {
                alert('Please select an item and fill in valid quantity and price.');
                return;
            }

            const subtotalPerItem = qty * purchasePrice;
            const taxAmount = subtotalPerItem * (tax / 100);
            const discountAmount = discountType === 'percentage' ? subtotalPerItem * (discountValue / 100) : discountValue;
            const finalTotal = subtotalPerItem + taxAmount - discountAmount + logisticCharges + extraCharges;

            const newItem = {
                id: itemId,
                name: itemName,
                unit: itemUnit,
                qty: qty,
                purchase_price: purchasePrice,
                sale_price: salePrice,
                tax: tax,
                discount_value: discountValue,
                discount_type: discountType,
                item_total: finalTotal,
                tax_amount: taxAmount,
                discount_amount: discountAmount,
                logistic_charges: logisticCharges,
                extra_charges: extraCharges,
            };
            billItems.push(newItem);
            renderBillItemsTable();
            calculateSummary();
            clearItemForm();
        });

        function renderBillItemsTable() {
            billItemsTableBody.innerHTML = '';
            let srNo = 1;
            billItems.forEach(item => {
                const discountDisplay = item.discount_type === 'percentage'
                    ? `${item.discount_value}%`
                    : `$${item.discount_value.toFixed(2)}`;
                const row = billItemsTableBody.insertRow();
                row.innerHTML = `
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${srNo++}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.name}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.unit}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.qty}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${item.purchase_price.toFixed(2)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${item.sale_price.toFixed(2)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${item.tax.toFixed(2)}%</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${discountDisplay}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">$${item.item_total.toFixed(2)}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                        <button type="button" class="text-red-600 hover:text-red-900 remove-bill-item-btn" data-id="${item.id}">Remove</button>
                    </td>
                `;
            });
            billItemsTableBody.querySelectorAll('.remove-bill-item-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const idToRemove = event.target.dataset.id;
                    billItems = billItems.filter(item => item.id !== idToRemove);
                    renderBillItemsTable();
                    calculateSummary();
                });
            });
        }

        function clearItemForm() {
            itemSearchInput.value = '';
            itemIdHidden.value = '';
            itemNameHidden.value = '';
            itemUnitHidden.value = '';
            itemTypeHidden.value = '';
            qtyInput.value = '';
            purchasePriceInput.value = '';
            salePriceInput.value = '';
            taxInput.value = '';
            discountValueInput.value = '';
            logisticChargesInput.value = '';
            extraChargesInput.value = '';
            document.querySelector('input[name="discount_type"][value="percentage"]').checked = true;
        }

        // --- Payment Summary Logic ---
        function calculateSummary() {
            let subTotal = 0;
            let totalTax = 0;
            let totalBillDiscount = 0;
            let totalLogisticCharges = 0;
            let totalExtraCharges = 0;

            billItems.forEach(item => {
                subTotal += item.qty * item.purchase_price;
                totalTax += (item.qty * item.purchase_price) * (item.tax / 100);
                totalBillDiscount += item.discount_type === 'percentage'
                    ? (item.qty * item.purchase_price) * (item.discount_value / 100)
                    : item.discount_value;
                totalLogisticCharges += item.logistic_charges;
                totalExtraCharges += item.extra_charges;
            });

            const grandTotal = subTotal + totalTax - totalBillDiscount + totalLogisticCharges + totalExtraCharges;
            const receivedPayment = parseFloat(receivedPaymentInput.value) || 0;
            const balance = grandTotal - receivedPayment;

            subTotalInput.value = subTotal.toFixed(2);
            totalTaxInput.value = totalTax.toFixed(2);
            totalBillDiscountInput.value = totalBillDiscount.toFixed(2);
            grandTotalInput.value = grandTotal.toFixed(2);
            balanceInput.value = balance.toFixed(2);
        }

        receivedPaymentInput.addEventListener('input', calculateSummary);
        logisticChargesInput.addEventListener('input', calculateSummary);
        extraChargesInput.addEventListener('input', calculateSummary);
        taxInput.addEventListener('input', calculateSummary);
        discountValueInput.addEventListener('input', calculateSummary);
        discountTypeRadios.forEach(radio => radio.addEventListener('change', calculateSummary));

        // --- Form Actions ---
        saveBtn.addEventListener('click', async () => {
            if (billItems.length === 0) {
                alert('Please add at least one item to the bill.');
                return;
            }
            if (!billIdInput.value || !supplierNameInput.value) {
                alert('Please fill out all purchase details.');
                return;
            }
            const purchaseData = {
                purchase_id: purchaseIdInput.value,
                purchase_date: purchaseDateInput.value,
                bill_id: billIdInput.value,
                bill_date: billDateInput.value,
                supplier_name: supplierNameInput.value,
                sub_total: parseFloat(subTotalInput.value),
                total_tax: parseFloat(totalTaxInput.value),
                total_bill_discount: parseFloat(totalBillDiscountInput.value),
                grand_total: parseFloat(grandTotalInput.value),
                received_payment: parseFloat(receivedPaymentInput.value),
                balance: parseFloat(balanceInput.value),
                remarks: remarksInput.value,
                bill_items: billItems
            };

            try {
                const response = await sendData('/api/purchase/store', 'POST', purchaseData);
                alert(`Purchase saved successfully! Purchase ID: ${response.purchase_id}`);
                resetForm();
            } catch (error) {
                console.error('Error saving purchase:', error);
                alert('An error occurred while saving the purchase. Please try again.');
            }
        });

        printSlipBtn.addEventListener('click', () => {
            if (billItems.length === 0) {
                alert('Please add at least one item to the bill.');
                return;
            }
            alert('Printing slip... (Simulated)');
            populatePrintArea();
            printDiv('print_area');
        });

        newBtn.addEventListener('click', () => {
            resetForm();
            alert('Form reset for a new purchase.');
        });

        function resetForm() {
            purchaseStockForm.reset();
            purchaseIdInput.value = `PUR-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;
            purchaseDateInput.value = `{{ date('Y-m-d') }}`;
            billItems = [];
            renderBillItemsTable();
            calculateSummary();
            clearItemForm();
        }

        // --- Print Functionality ---
        function populatePrintArea() {
            document.getElementById('print_purchase_id').textContent = purchaseIdInput.value;
            document.getElementById('print_purchase_date').textContent = purchaseDateInput.value;
            document.getElementById('print_bill_id').textContent = billIdInput.value;
            document.getElementById('print_supplier_name').textContent = supplierNameInput.value;
            document.getElementById('print_remarks').textContent = remarksInput.value || 'N/A';
            document.getElementById('print_sub_total').textContent = subTotalInput.value;
            document.getElementById('print_total_bill_discount').textContent = totalBillDiscountInput.value;
            document.getElementById('print_total_tax').textContent = totalTaxInput.value;
            document.getElementById('print_grand_total').textContent = grandTotalInput.value;
            
            const printTableBody = document.getElementById('print_bill_items_body');
            printTableBody.innerHTML = '';
            billItems.forEach(item => {
                const row = printTableBody.insertRow();
                row.innerHTML = `
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${item.name}</td>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${item.qty}</td>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">$${item.purchase_price.toFixed(2)}</td>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">$${item.sale_price.toFixed(2)}</td>
                    <td class="border px-4 py-2 text-right text-sm text-gray-900">$${item.item_total.toFixed(2)}</td>
                `;
            });
        }

        function printDiv(divId) {
            const printContents = document.getElementById(divId).innerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = originalContents;
            const printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(`
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Print Purchase Slip</title>
                    <style>
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
        }

        // Initial setup on page load
        document.addEventListener('DOMContentLoaded', () => {
            // Re-generate new purchase ID
            purchaseIdInput.value = `PUR-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;
        });
    </script>
@endsection