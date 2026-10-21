@extends('layouts.app')

@section('title', 'Purchase stock')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="Purchase stock" subtitle="Record a supplier bill. Consumable stock rises by the quantity received; assets are recorded at their cost.">
        <x-slot:actions>
            <a href="{{ route('store.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Store
            </a>
            <a href="{{ route('store.supplier') }}" class="btn btn-secondary">
                <x-ui.icon name="truck" />Suppliers
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form id="purchase_stock_form" action="#" method="POST" class="stack" novalidate>
        @csrf

        <x-ui.panel title="Bill" icon="receipt">
            <div class="form-grid">
                <x-ui.field label="Purchase ID" for="purchase_id" tag="Auto" class="md:col-span-3">
                    <input type="text" id="purchase_id" name="purchase_id" class="input input-mono" value="PUR-{{ strtoupper(Str::random(6)) }}" readonly>
                </x-ui.field>
                <x-ui.field label="Purchase date" for="purchase_date" class="md:col-span-3">
                    <input type="date" id="purchase_date" name="purchase_date" class="input" value="{{ date('Y-m-d') }}" readonly>
                </x-ui.field>
                <x-ui.field label="Supplier" for="supplier_name" required class="md:col-span-6"
                    :hint="$suppliers->isEmpty() ? 'No suppliers saved yet. Type the name, or add suppliers first.' : 'Pick a saved supplier or type a name.'">
                    <input type="text" id="supplier_name" name="supplier_name" class="input" list="supplier_options" placeholder="Company name" autocomplete="off" required>
                    <datalist id="supplier_options">
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->company_name }}">{{ $supplier->name }}</option>
                        @endforeach
                    </datalist>
                </x-ui.field>
                <x-ui.field label="Supplier bill no." for="bill_id" required class="md:col-span-3">
                    <input type="text" id="bill_id" name="bill_id" class="input input-mono" placeholder="e.g. INV-001" autocomplete="off" required>
                </x-ui.field>
                <x-ui.field label="Bill date" for="bill_date" required class="md:col-span-3">
                    <input type="date" id="bill_date" name="bill_date" class="input" value="{{ date('Y-m-d') }}" required>
                </x-ui.field>
            </div>
        </x-ui.panel>

        <x-ui.panel title="Items" icon="package" flush>
            <div class="panel-body border-b border-line bg-subtle">
                <div class="form-grid">
                    <x-ui.field label="Item" for="item_search" class="md:col-span-6">
                        <div class="flex gap-2">
                            <input type="text" id="item_search" name="item_search" class="input" placeholder="No item selected" readonly>
                            <button type="button" id="search_item_btn" class="btn btn-secondary"><x-ui.icon name="search" />Find item</button>
                        </div>
                        <p id="item_meta" class="field-hint">Find an item to add it to the bill.</p>
                        <input type="hidden" id="item_id" name="item_id">
                        <input type="hidden" id="item_name_hidden" name="item_name_hidden">
                        <input type="hidden" id="item_unit_hidden" name="item_unit_hidden">
                        <input type="hidden" id="item_type_hidden" name="item_type_hidden">
                    </x-ui.field>
                    <x-ui.field label="Quantity" for="qty" class="md:col-span-2">
                        <input type="number" id="qty" name="qty" class="input input-num" placeholder="0" min="1">
                    </x-ui.field>
                    <x-ui.field label="Purchase price" for="purchase_price" class="md:col-span-2">
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="purchase_price" name="purchase_price" class="input input-money" placeholder="0.00" min="0" step="0.01">
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Sale price" for="sale_price" class="md:col-span-2">
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="sale_price" name="sale_price" class="input input-money" placeholder="0.00" min="0" step="0.01">
                        </div>
                    </x-ui.field>

                    <x-ui.field label="Tax" for="tax" class="md:col-span-2">
                        <div class="input-group has-end">
                            <input type="number" id="tax" name="tax" class="input input-num" placeholder="0" min="0" max="100">
                            <span class="input-affix is-end">%</span>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Discount" for="discount_value" class="md:col-span-4">
                        <div class="flex gap-2">
                            <input type="number" id="discount_value" name="discount_value" class="input input-money min-w-0 flex-1" placeholder="0" min="0" step="0.01">
                            <div class="segmented shrink-0" role="radiogroup" aria-label="Discount type">
                                <label class="segmented-option">
                                    <input type="radio" name="discount_type" value="percentage" checked>
                                    <span>%</span>
                                </label>
                                <label class="segmented-option">
                                    <input type="radio" name="discount_type" value="flat">
                                    <span>{{ $currency }}</span>
                                </label>
                            </div>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Logistic charges" for="logistic_charges" class="md:col-span-2">
                        <input type="number" id="logistic_charges" name="logistic_charges" class="input input-money" placeholder="0.00" min="0" step="0.01">
                    </x-ui.field>
                    <x-ui.field label="Extra charges" for="extra_charges" class="md:col-span-2">
                        <input type="number" id="extra_charges" name="extra_charges" class="input input-money" placeholder="0.00" min="0" step="0.01">
                    </x-ui.field>
                    <div class="flex items-end md:col-span-2">
                        <button type="button" id="add_item_to_bill_btn" class="btn btn-primary btn-block"><x-ui.icon name="plus" />Add to bill</button>
                    </div>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="num w-10">#</th>
                            <th>Item</th>
                            <th class="num">Qty</th>
                            <th class="num">Cost</th>
                            <th class="num">Sale</th>
                            <th class="num">Tax</th>
                            <th class="num">Discount</th>
                            <th class="num">Charges</th>
                            <th class="num">Total ({{ $currency }})</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody id="bill_items_table_body"></tbody>
                </table>
            </div>
        </x-ui.panel>

        <div class="grid items-start gap-5 lg:grid-cols-[minmax(0,1fr)_380px]">
            <x-ui.panel title="Remarks" icon="file-text">
                <label for="remarks" class="sr-only">Remarks</label>
                <textarea id="remarks" name="remarks" rows="4" class="textarea" placeholder="Delivery notes, payment terms or anything else about this bill"></textarea>
            </x-ui.panel>

            <x-ui.panel title="Payment" icon="banknote" flush>
                <div class="panel-body space-y-4">
                    <div class="ledger">
                        <div class="ledger-row">
                            <span class="ledger-op"></span>
                            <span class="ledger-label">Sub total<small>Quantity × cost</small></span>
                            <output id="sub_total" class="text-right font-mono tabular-nums">0.00</output>
                        </div>
                        <div class="ledger-row">
                            <span class="ledger-op">+</span>
                            <span class="ledger-label">Tax</span>
                            <output id="total_tax" class="text-right font-mono tabular-nums">0.00</output>
                        </div>
                        <div class="ledger-row">
                            <span class="ledger-op">−</span>
                            <span class="ledger-label">Discount</span>
                            <output id="total_bill_discount" class="text-right font-mono tabular-nums">0.00</output>
                        </div>
                        <div class="ledger-row">
                            <span class="ledger-op">+</span>
                            <span class="ledger-label">Logistic &amp; extra charges</span>
                            <output id="total_charges" class="text-right font-mono tabular-nums">0.00</output>
                        </div>
                        <div class="ledger-row ledger-total">
                            <span class="ledger-op">=</span>
                            <span class="ledger-label">Grand total</span>
                            <output id="grand_total" class="text-right font-mono text-[15px] font-bold tabular-nums">0.00</output>
                        </div>
                    </div>
                    <x-ui.field label="Paid to supplier" for="received_payment">
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="received_payment" name="received_payment" class="input input-money font-bold" placeholder="0.00" min="0" step="0.01" value="0.00">
                        </div>
                    </x-ui.field>
                    <div class="flex items-center justify-between rounded-lg border border-line bg-subtle px-3 py-2.5">
                        <span class="text-[13px] font-medium text-fg-muted">Balance payable</span>
                        <output id="balance" class="font-mono text-[15px] font-bold tabular-nums">0.00</output>
                    </div>
                </div>
                <x-slot:footer class="flex-col items-stretch">
                    <button type="button" id="print_slip_btn" class="btn btn-primary btn-lg btn-block"><x-ui.icon name="printer" />Save and print slip</button>
                    <button type="button" id="save_btn" class="btn btn-secondary btn-block"><x-ui.icon name="save" />Save purchase</button>
                    <button type="button" id="new_btn" class="btn btn-ghost btn-block"><x-ui.icon name="rotate-ccw" />New purchase</button>
                </x-slot:footer>
            </x-ui.panel>
        </div>
    </form>

    <x-ui.panel title="Recent purchases" icon="clock" flush class="mt-5">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Purchase</th>
                        <th>Supplier</th>
                        <th>Bill no.</th>
                        <th class="num">Items</th>
                        <th class="num">Grand total</th>
                        <th class="num">Balance</th>
                    </tr>
                </thead>
                <tbody id="recent_purchases">
                    @forelse ($recent as $bill)
                        <tr>
                            <td>
                                <span class="id">{{ $bill->purchase_id }}</span>
                                <span class="cell-sub">{{ optional($bill->purchase_date)->format('d M Y') }}</span>
                            </td>
                            <td class="cell-strong">{{ $bill->supplier_name }}</td>
                            <td>
                                <span class="id">{{ $bill->bill_id }}</span>
                                <span class="cell-sub">{{ optional($bill->bill_date)->format('d M Y') }}</span>
                            </td>
                            <td class="num">{{ $bill->items_count }}</td>
                            <td class="num">{{ number_format((float) $bill->grand_total, 2) }}</td>
                            <td @class(['num', 'font-semibold text-warning-700' => (float) $bill->balance > 0])>{{ number_format((float) $bill->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr data-empty-row>
                            <td colspan="6" class="table-empty">No purchases recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.panel>

    <x-ui.dialog id="itemSearchModal" title="Find item" description="Search active items by name, barcode or asset tag. Use ↑ ↓ and Enter to choose." size="xl" icon="package">
        <div class="input-group has-start mb-3">
            <span class="input-affix is-start"><x-ui.icon name="search" /></span>
            <input type="text" id="item_search_input_modal" class="input" placeholder="Name, barcode or asset tag" autocomplete="off">
        </div>
        <div id="item_search_results" class="result-list"></div>
        <x-slot:footer>
            <button type="button" class="btn btn-secondary" data-dialog-close>Close</button>
        </x-slot:footer>
    </x-ui.dialog>

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
            <p>Hospital Management System</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currency = @json($currency);
            const today = @json(date('Y-m-d'));
            const $ = (id) => document.getElementById(id);
            const money = (value) => (Number(value) || 0).toFixed(2);
            const esc = LC.escape;

            const purchaseStockForm = $('purchase_stock_form');
            const purchaseIdInput = $('purchase_id');
            const purchaseDateInput = $('purchase_date');
            const billIdInput = $('bill_id');
            const billDateInput = $('bill_date');
            const supplierNameInput = $('supplier_name');

            const itemSearchInput = $('item_search');
            const itemMeta = $('item_meta');
            const itemIdHidden = $('item_id');
            const itemNameHidden = $('item_name_hidden');
            const itemUnitHidden = $('item_unit_hidden');
            const itemTypeHidden = $('item_type_hidden');
            const qtyInput = $('qty');
            const purchasePriceInput = $('purchase_price');
            const salePriceInput = $('sale_price');
            const taxInput = $('tax');
            const discountValueInput = $('discount_value');
            const logisticChargesInput = $('logistic_charges');
            const extraChargesInput = $('extra_charges');
            const addItemToBillBtn = $('add_item_to_bill_btn');

            const billItemsTableBody = $('bill_items_table_body');
            const subTotalOutput = $('sub_total');
            const totalTaxOutput = $('total_tax');
            const totalBillDiscountOutput = $('total_bill_discount');
            const totalChargesOutput = $('total_charges');
            const grandTotalOutput = $('grand_total');
            const receivedPaymentInput = $('received_payment');
            const balanceOutput = $('balance');
            const remarksInput = $('remarks');

            const saveBtn = $('save_btn');
            const printSlipBtn = $('print_slip_btn');
            const newBtn = $('new_btn');

            let billItems = [];
            let selected = null;
            let totals = { subTotal: 0, totalTax: 0, totalBillDiscount: 0, grandTotal: 0, balance: 0 };

            const isAsset = (item) => item && item.type !== 'Consumable';
            const newPurchaseId = () => `PUR-${Math.random().toString(36).substring(2, 8).toUpperCase()}`;

            // --- Item search ---
            const picker = LC.searchPicker({
                dialog: 'itemSearchModal',
                input: 'item_search_input_modal',
                results: 'item_search_results',
                load: (term) => LC.json(`/api/purchase/search-items?query=${encodeURIComponent(term)}`),
                emptyText: 'No active items match. Add items under Consumable or Non-consumable items first.',
                disabledReason: (item) => {
                    if (item.purchased_on) return `Already recorded on purchase ${item.purchased_on}`;
                    if (billItems.some((line) => line.id === item.id)) return 'Already on this bill';
                    return null;
                },
                columns: [
                    { label: 'Item', render: (i) => `<span class="cell-strong">${esc(i.name)}</span>${i.code ? `<span class="cell-sub">${esc(i.code)}</span>` : ''}` },
                    { label: 'Type', render: (i) => `<span class="badge ${isAsset(i) ? 'badge-info' : 'badge-neutral'}">${isAsset(i) ? 'Asset' : 'Consumable'}</span>` },
                    { label: 'Unit', render: (i) => esc(i.unit || '—') },
                    { label: `Cost (${currency})`, className: 'num', render: (i) => money(i.purchase_price) },
                    { label: 'In store', className: 'num', render: (i) => (isAsset(i) ? esc(i.purchased_on ? `On ${i.purchased_on}` : i.status || '—') : `${i.available_qty} ${esc(i.unit || '')}`) },
                ],
                onPick: selectItem,
            });

            $('search_item_btn').addEventListener('click', () => picker.open());
            itemSearchInput.addEventListener('click', () => picker.open());

            function selectItem(item) {
                selected = item;
                itemSearchInput.value = item.code ? `${item.name} (${item.code})` : item.name;
                itemIdHidden.value = item.id;
                itemNameHidden.value = item.name;
                itemUnitHidden.value = item.unit || '';
                itemTypeHidden.value = item.type;
                purchasePriceInput.value = money(item.purchase_price);

                if (isAsset(item)) {
                    qtyInput.value = 1;
                    qtyInput.readOnly = true;
                    salePriceInput.value = '0.00';
                    salePriceInput.disabled = true;
                    itemMeta.textContent = `Asset ${item.code || ''} — recorded once, quantity 1.`;
                    purchasePriceInput.focus();
                } else {
                    qtyInput.readOnly = false;
                    salePriceInput.disabled = false;
                    salePriceInput.value = money(item.sale_price);
                    itemMeta.textContent = `Consumable — ${item.available_qty} ${item.unit || ''} in store now (minimum ${item.min_stock_level}).`;
                    qtyInput.value = '';
                    qtyInput.focus();
                }
            }

            // --- Add item to bill (line formula unchanged) ---
            addItemToBillBtn.addEventListener('click', () => {
                const itemId = itemIdHidden.value;
                const itemName = itemNameHidden.value;
                const itemUnit = itemUnitHidden.value;
                const qty = parseInt(qtyInput.value);
                const purchasePrice = parseFloat(purchasePriceInput.value);
                const salePrice = parseFloat(salePriceInput.value) || 0;
                const tax = parseFloat(taxInput.value) || 0;
                const discountValue = parseFloat(discountValueInput.value) || 0;
                const discountType = document.querySelector('input[name="discount_type"]:checked').value;
                const logisticCharges = parseFloat(logisticChargesInput.value) || 0;
                const extraCharges = parseFloat(extraChargesInput.value) || 0;

                if (!itemId || !itemName) {
                    LC.toast('Find and select an item first.', 'warning');
                    picker.open();
                    return;
                }
                if (isNaN(qty) || qty <= 0) {
                    LC.toast('Enter a quantity of at least 1.', 'warning');
                    qtyInput.focus();
                    return;
                }
                if (isNaN(purchasePrice) || purchasePrice <= 0) {
                    LC.toast('Enter the purchase price.', 'warning');
                    purchasePriceInput.focus();
                    return;
                }
                if (billItems.some((line) => line.id === itemId)) {
                    LC.toast(`${itemName} is already on this bill. Remove it to change the line.`, 'warning');
                    return;
                }

                const subtotalPerItem = qty * purchasePrice;
                const taxAmount = subtotalPerItem * (tax / 100);
                const discountAmount = discountType === 'percentage' ? subtotalPerItem * (discountValue / 100) : discountValue;
                const finalTotal = subtotalPerItem + taxAmount - discountAmount + logisticCharges + extraCharges;

                billItems.push({
                    id: itemId,
                    name: itemName,
                    unit: itemUnit,
                    type: itemTypeHidden.value,
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
                });
                renderBillItemsTable();
                calculateSummary();
                clearItemForm();
            });

            function renderBillItemsTable() {
                if (!billItems.length) {
                    billItemsTableBody.innerHTML = '<tr data-empty-row><td colspan="10" class="table-empty">No items on this bill yet. Find an item above and add it.</td></tr>';
                    return;
                }
                billItemsTableBody.innerHTML = billItems.map((item, index) => {
                    const discountDisplay = item.discount_type === 'percentage' ? `${item.discount_value}%` : money(item.discount_value);
                    const charges = item.logistic_charges + item.extra_charges;
                    return `<tr>
                        <td class="num muted">${index + 1}</td>
                        <td><span class="cell-strong">${esc(item.name)}</span><span class="cell-sub">${isAsset(item) ? 'Asset' : 'Consumable'}${item.unit ? ' · ' + esc(item.unit) : ''}</span></td>
                        <td class="num">${item.qty}</td>
                        <td class="num">${money(item.purchase_price)}</td>
                        <td class="num">${isAsset(item) ? '—' : money(item.sale_price)}</td>
                        <td class="num">${item.tax.toFixed(2)}%</td>
                        <td class="num">${discountDisplay}</td>
                        <td class="num">${charges ? money(charges) : '—'}</td>
                        <td class="num font-semibold">${money(item.item_total)}</td>
                        <td class="actions"><button type="button" class="btn btn-danger-ghost btn-icon btn-sm remove-bill-item-btn" data-id="${esc(item.id)}" aria-label="Remove ${esc(item.name)}"><svg class="lc-icon" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button></td>
                    </tr>`;
                }).join('');
            }

            billItemsTableBody.addEventListener('click', (event) => {
                const button = event.target.closest('.remove-bill-item-btn');
                if (!button) return;
                billItems = billItems.filter((item) => item.id !== button.dataset.id);
                renderBillItemsTable();
                calculateSummary();
            });

            function clearItemForm() {
                selected = null;
                itemSearchInput.value = '';
                itemMeta.textContent = 'Find an item to add it to the bill.';
                itemIdHidden.value = '';
                itemNameHidden.value = '';
                itemUnitHidden.value = '';
                itemTypeHidden.value = '';
                qtyInput.value = '';
                qtyInput.readOnly = false;
                purchasePriceInput.value = '';
                salePriceInput.value = '';
                salePriceInput.disabled = false;
                taxInput.value = '';
                discountValueInput.value = '';
                logisticChargesInput.value = '';
                extraChargesInput.value = '';
                document.querySelector('input[name="discount_type"][value="percentage"]').checked = true;
            }

            // --- Payment summary (formula unchanged) ---
            function calculateSummary() {
                let subTotal = 0;
                let totalTax = 0;
                let totalBillDiscount = 0;
                let totalLogisticCharges = 0;
                let totalExtraCharges = 0;

                billItems.forEach((item) => {
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

                totals = { subTotal, totalTax, totalBillDiscount, grandTotal, balance };
                subTotalOutput.textContent = money(subTotal);
                totalTaxOutput.textContent = money(totalTax);
                totalBillDiscountOutput.textContent = money(totalBillDiscount);
                totalChargesOutput.textContent = money(totalLogisticCharges + totalExtraCharges);
                grandTotalOutput.textContent = money(grandTotal);
                balanceOutput.textContent = money(balance);
            }

            receivedPaymentInput.addEventListener('input', calculateSummary);

            // --- Save ---
            function setBusy(busy) {
                [saveBtn, printSlipBtn, newBtn].forEach((button) => (button.disabled = busy));
                purchaseStockForm.setAttribute('aria-busy', busy ? 'true' : 'false');
            }

            async function save(print) {
                if (!billItems.length) {
                    LC.toast('Add at least one item to the bill.', 'warning');
                    return;
                }
                const missing = [[supplierNameInput, 'supplier'], [billIdInput, 'supplier bill number'], [billDateInput, 'bill date']].find(([input]) => !input.value.trim());
                if (missing) {
                    LC.toast(`Enter the ${missing[1]}.`, 'warning');
                    missing[0].focus();
                    return;
                }

                calculateSummary();
                const purchaseData = {
                    purchase_id: purchaseIdInput.value,
                    purchase_date: purchaseDateInput.value,
                    bill_id: billIdInput.value.trim(),
                    bill_date: billDateInput.value,
                    supplier_name: supplierNameInput.value.trim(),
                    sub_total: Number(money(totals.subTotal)),
                    total_tax: Number(money(totals.totalTax)),
                    total_bill_discount: Number(money(totals.totalBillDiscount)),
                    grand_total: Number(money(totals.grandTotal)),
                    received_payment: parseFloat(receivedPaymentInput.value) || 0,
                    balance: Number(money(totals.balance)),
                    remarks: remarksInput.value,
                    bill_items: billItems.map((item) => ({
                        id: item.id,
                        name: item.name,
                        unit: item.unit,
                        qty: item.qty,
                        purchase_price: item.purchase_price,
                        sale_price: item.sale_price,
                        tax: item.tax,
                        discount_value: item.discount_value,
                        discount_type: item.discount_type,
                        total: Number(money(item.item_total)),
                    })),
                };

                // Open the slip window during the click so pop-up blockers allow it.
                const slipWindow = print ? window.open('', '_blank') : null;
                setBusy(true);
                try {
                    const response = await LC.json('/api/purchase/store', { method: 'POST', body: purchaseData });
                    LC.toast(`Purchase ${response.purchase_id} saved.`, 'success');
                    if (slipWindow) {
                        populatePrintArea();
                        writeSlip(slipWindow);
                    }
                    addRecentRow(purchaseData);
                    resetForm();
                } catch (error) {
                    slipWindow?.close();
                    LC.toast(error.messages.join(' '), 'danger', { timeout: 10000 });
                } finally {
                    setBusy(false);
                }
            }

            saveBtn.addEventListener('click', () => save(false));
            printSlipBtn.addEventListener('click', () => save(true));

            newBtn.addEventListener('click', async () => {
                if (billItems.length && !(await LC.confirm({ title: 'Start a new purchase?', message: 'The items on this bill have not been saved and will be cleared.', confirmLabel: 'Clear bill', tone: 'danger' }))) {
                    return;
                }
                resetForm();
            });

            function resetForm() {
                purchaseStockForm.reset();
                purchaseIdInput.value = newPurchaseId();
                purchaseDateInput.value = today;
                billDateInput.value = today;
                receivedPaymentInput.value = '0.00';
                billItems = [];
                renderBillItemsTable();
                calculateSummary();
                clearItemForm();
            }

            function addRecentRow(data) {
                const body = $('recent_purchases');
                body.querySelector('[data-empty-row]')?.remove();
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><span class="id">${esc(data.purchase_id)}</span><span class="cell-sub">${esc(data.purchase_date)}</span></td>
                    <td class="cell-strong">${esc(data.supplier_name)}</td>
                    <td><span class="id">${esc(data.bill_id)}</span><span class="cell-sub">${esc(data.bill_date)}</span></td>
                    <td class="num">${data.bill_items.length}</td>
                    <td class="num">${money(data.grand_total)}</td>
                    <td class="num${data.balance > 0 ? ' font-semibold text-warning-700' : ''}">${money(data.balance)}</td>`;
                body.prepend(row);
            }

            // --- Print slip (content unchanged) ---
            function populatePrintArea() {
                $('print_purchase_id').textContent = purchaseIdInput.value;
                $('print_purchase_date').textContent = purchaseDateInput.value;
                $('print_bill_id').textContent = billIdInput.value;
                $('print_supplier_name').textContent = supplierNameInput.value;
                $('print_remarks').textContent = remarksInput.value || 'N/A';
                $('print_sub_total').textContent = money(totals.subTotal);
                $('print_total_bill_discount').textContent = money(totals.totalBillDiscount);
                $('print_total_tax').textContent = money(totals.totalTax);
                $('print_grand_total').textContent = money(totals.grandTotal);

                $('print_bill_items_body').innerHTML = billItems.map((item) => `<tr>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${esc(item.name)}</td>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${item.qty}</td>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${esc(currency)}${money(item.purchase_price)}</td>
                    <td class="border px-4 py-2 text-left text-sm text-gray-900">${esc(currency)}${money(item.sale_price)}</td>
                    <td class="border px-4 py-2 text-right text-sm text-gray-900">${esc(currency)}${money(item.item_total)}</td>
                </tr>`).join('');
            }

            function writeSlip(printWindow) {
                printWindow.document.open();
                printWindow.document.write(`<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Print Purchase Slip</title><style>
                    body { font-family: 'Inter', sans-serif; margin: 0; padding: 20mm; }
                    h1, h2, h3 { color: #1f2937; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
                    th { background-color: #f3f4f6; }
                    .text-center { text-align: center; } .text-right { text-align: right; }
                    .mb-8 { margin-bottom: 2rem; } .mb-6 { margin-bottom: 1.5rem; } .mb-4 { margin-bottom: 1rem; } .mb-2 { margin-bottom: 0.5rem; }
                    .mt-8 { margin-top: 2rem; } .mt-16 { margin-top: 4rem; }
                    .border-b { border-bottom: 1px solid #e5e7eb; } .border-t-2 { border-top: 2px solid #d1d5db; }
                    .font-bold { font-weight: 700; } .font-semibold { font-weight: 600; }
                    .text-sm { font-size: 0.875rem; } .text-lg { font-size: 1.125rem; } .text-xl { font-size: 1.25rem; } .text-2xl { font-size: 1.5rem; } .text-4xl { font-size: 2.25rem; }
                    .text-gray-800 { color: #1f2937; } .text-gray-700 { color: #374151; } .text-gray-600 { color: #4b5563; }
                    .bg-gray-100 { background-color: #f3f4f6; } .bg-gray-50 { background-color: #f9fafb; }
                    .flex { display: flex; } .justify-between { justify-content: space-between; } .items-end { align-items: flex-end; }
                    .pt-2 { padding-top: 0.5rem; } .border-t { border-top: 1px solid #6b7280; }
                </style></head><body>${$('print_area').innerHTML}</body></html>`);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            }

            renderBillItemsTable();
            calculateSummary();
        });
    </script>
@endsection
