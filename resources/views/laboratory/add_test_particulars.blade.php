@extends('layouts.app')

@section('content')
    <div class="hms-page-toolbar"><div><h2 class="hms-page-heading">Pathology Test Particulars</h2></div>
        <a href="{{ route('pathology.index') }}" class="hms-back-btn">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Pathology
        </a>
    </div>

    @include('partials.flash-alerts')

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

    @php
        $isEdit = isset($particular);
        $selectedTestHeadId = old('test_head_id', $isEdit ? ($particular->test->test_head_id ?? '') : '');
        $selectedTestHeadName = '';
        if ($isEdit && $particular->test && $particular->test->testHead) {
            $selectedTestHeadName = $particular->test->testHead->name;
        } elseif ($selectedTestHeadId) {
            $selectedTestHeadName = optional($testHeads->firstWhere('id', (int) $selectedTestHeadId))->name ?? '';
        }
        $selectedTestId = old('test_id', $isEdit ? $particular->test_id : '');
        $selectedTestName = ($isEdit && $particular->test) ? $particular->test->name : '';
        $testHeadsJson = $testHeads->map(fn ($h) => ['id' => $h->id, 'name' => $h->name])->values();
    @endphp

    <style>
        .hms-combobox { position: relative; }
        .hms-combobox-list {
            position: absolute;
            z-index: 40;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            max-height: 15rem;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.15);
        }
        .hms-combobox-list[hidden] { display: none; }
        .hms-combobox-option {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #374151;
            cursor: pointer;
        }
        .hms-combobox-option:hover,
        .hms-combobox-option.is-active {
            background: #eff6ff;
            color: #1d4ed8;
        }
        .hms-combobox-empty {
            padding: 0.5rem 0.75rem;
            font-size: 0.8125rem;
            color: #9ca3af;
        }
        .hms-combobox-selected {
            margin-top: 0.375rem;
            font-size: 0.8125rem;
            color: #059669;
        }
        .hms-combobox-selected[hidden] { display: none; }
        .hms-combobox-input[disabled] { background: #f3f4f6; cursor: not-allowed; }
    </style>

    <div class="hms-panel hms-panel-padded mb-5">
        @if($isEdit)
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Edit Test Particular</h3>
            <form action="{{ route('laboratory.add_test_particulars.update', $particular->id) }}" method="POST" id="particular-form">
                @csrf
                @method('PUT')
        @else
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Define New Test Particulars</h3>
            <p class="text-sm text-gray-500 mb-4">Pick a Test Head and Test Name, then add one or more particulars to the cart before saving them all at once.</p>
            {{-- Selection + entry fields live outside the save form; the cart is submitted via #bulk-save-form below. --}}
            <div id="particular-entry">
        @endif
            <div class="hms-form-grid mb-6">
                <div>
                    <label for="test_head_search" class="hms-label">Test Head:</label>
                    <div class="hms-combobox" data-combobox="test-head">
                        <input type="text" id="test_head_search" class="hms-input hms-combobox-input" placeholder="Search Test Head…" autocomplete="off" value="{{ $selectedTestHeadName }}" role="combobox" aria-expanded="false" aria-autocomplete="list">
                        <input type="hidden" id="test_head_id" name="test_head_id" value="{{ $selectedTestHeadId }}">
                        <ul class="hms-combobox-list" id="test_head_list" role="listbox" hidden></ul>
                    </div>
                </div>
                <div>
                    <label for="test_name_search" class="hms-label">Test Name:</label>
                    <div class="hms-combobox" data-combobox="test-name">
                        <input type="text" id="test_name_search" class="hms-input hms-combobox-input" placeholder="Select Test Head first…" autocomplete="off" value="{{ $selectedTestName }}" role="combobox" aria-expanded="false" aria-autocomplete="list" @unless($isEdit) disabled @endunless>
                        <input type="hidden" id="test_id" value="{{ $selectedTestId }}">
                        <ul class="hms-combobox-list" id="test_name_list" role="listbox" hidden></ul>
                    </div>
                </div>
                <div>
                    <label for="particular_name" class="hms-label">Particular Name:</label>
                    <input type="text" id="particular_name" @if($isEdit) name="particular_name" @endif class="hms-input" placeholder="e.g., Hemoglobin, Glucose" value="{{ old('particular_name', $particular->name ?? '') }}" @if($isEdit) required @endif>
                </div>
                <div>
                    <label for="unit" class="hms-label">Unit:</label>
                    <input type="text" id="unit" @if($isEdit) name="unit" @endif class="hms-input" placeholder="e.g., g/dL, mg/dL" value="{{ old('unit', $particular->unit ?? '') }}">
                </div>
                <div>
                    <label for="normal_range_min" class="hms-label">Normal Range (Min):</label>
                    <input type="text" id="normal_range_min" @if($isEdit) name="normal_range_min" @endif class="hms-input" placeholder="e.g., 12.0 or Negative" value="{{ old('normal_range_min', $particular->normal_range_min ?? '') }}">
                </div>
                <div>
                    <label for="normal_range_max" class="hms-label">Normal Range (Max):</label>
                    <input type="text" id="normal_range_max" @if($isEdit) name="normal_range_max" @endif class="hms-input" placeholder="e.g., 16.0 or Positive" value="{{ old('normal_range_max', $particular->normal_range_max ?? '') }}">
                </div>
                <div>
                    <label for="sort_order" class="hms-label">Report Order:</label>
                    <input type="number" id="sort_order" @if($isEdit) name="sort_order" @endif class="hms-input" placeholder="e.g., 1" step="1" min="0" value="{{ old('sort_order', $isEdit ? $particular->sort_order : 1) }}">
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-3">
                    <label for="reference_text" class="hms-label">Reference Text/Notes:</label>
                    <textarea id="reference_text" @if($isEdit) name="reference_text" @endif rows="2" class="hms-input" placeholder="e.g., Varies by age and gender">{{ old('reference_text', $particular->reference_text ?? '') }}</textarea>
                </div>
            </div>
        @if($isEdit)
            <input type="hidden" name="test_id" id="edit_test_id" value="{{ $selectedTestId }}">
            <div class="flex justify-end gap-3">
                <a href="{{ route('pathology.add_test_particulars') }}" class="hms-btn hms-btn-ghost">Cancel</a>
                <button type="submit" class="hms-btn hms-btn-primary">Update Particular</button>
            </div>
        </form>
        @else
            <div class="flex justify-end gap-3">
                <button type="button" id="add-to-cart-btn" class="hms-btn hms-btn-primary">Add to Cart</button>
            </div>
            </div>{{-- /#particular-entry --}}

            <div class="mt-6" id="cart-section">
                <div class="flex flex-col gap-3 mb-3 sm:flex-row sm:items-center sm:justify-between">
                    <h4 class="text-lg font-semibold text-gray-800">Particulars Cart <span id="cart-count" class="text-sm font-normal text-gray-500">(0)</span></h4>
                </div>
                <p id="cart-empty" class="text-sm text-gray-500 mb-3">No particulars added yet. Fill the fields above and click “Add to Cart”.</p>
                <div class="hms-table-wrap" id="cart-table-wrap" hidden>
                    <table class="hms-table" id="cart-table">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Order</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Particular Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Normal Range</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200" id="cart-body"></tbody>
                    </table>
                </div>

                <form action="{{ route('laboratory.add_test_particulars.store_bulk') }}" method="POST" id="bulk-save-form" class="flex justify-end gap-3 mt-4">
                    @csrf
                    <input type="hidden" name="test_id" id="bulk_test_id" value="{{ $selectedTestId }}">
                    <div id="bulk-hidden-inputs"></div>
                    <button type="submit" id="save-cart-btn" class="hms-btn hms-btn-primary" disabled>Save All Particulars</button>
                </form>
            </div>
        @endif
    </div>

    @php $currentSearch = $search ?? ''; @endphp
    <div class="hms-panel hms-panel-padded" id="existing-particulars-panel">
        <div class="flex flex-col gap-4 mb-4 sm:flex-row sm:items-start sm:justify-between">
            <h3 class="text-2xl font-semibold text-gray-800">Existing Test Particulars</h3>
            <div class="w-full sm:max-w-md shrink-0">
                <div class="hms-search-bar w-full flex items-center gap-2" id="existing-particulars-search-form">
                    <input type="text" id="existing-particulars-search" class="hms-input flex-1" placeholder="Search particular, test, head, unit, range…" autocomplete="off" value="{{ $currentSearch }}">
                    <button type="button" id="existing-particulars-clear" class="hms-btn hms-btn-ghost{{ $currentSearch === '' ? ' hidden' : '' }}">Clear</button>
                </div>
            </div>
        </div>
        <div id="existing-particulars-results">
            @include('laboratory.partials.test_particulars_list')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const IS_EDIT = @json($isEdit);
            const TEST_HEADS = @json($testHeadsJson);
            const TESTS_BY_HEAD_URL = `{{ route('api.tests_by_head', ['testHeadId' => 'TEST_HEAD_ID']) }}`;

            /* ---------- Existing particulars: live AJAX search (no full page reload) ---------- */
            const particularsResults = document.getElementById('existing-particulars-results');
            const particularsSearchInput = document.getElementById('existing-particulars-search');
            const particularsClearBtn = document.getElementById('existing-particulars-clear');
            let particularsSearchTimer = null;

            function particularsListUrl(query, page) {
                const url = new URL(window.location.pathname, window.location.origin);
                const q = String(query || '').trim();
                if (q !== '') {
                    url.searchParams.set('q', q);
                }
                if (page && page > 1) {
                    url.searchParams.set('page', String(page));
                }
                return url.toString();
            }

            function toggleParticularsClearButton(query) {
                if (!particularsClearBtn) {
                    return;
                }
                particularsClearBtn.classList.toggle('hidden', String(query || '').trim() === '');
            }

            function loadParticularsList(url) {
                if (!particularsResults) {
                    return;
                }
                particularsResults.classList.add('opacity-50', 'pointer-events-none');

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Search failed');
                        }
                        return response.text();
                    })
                    .then(function (html) {
                        particularsResults.innerHTML = html;
                        window.history.replaceState({}, '', url);
                    })
                    .catch(function (error) {
                        console.error('Particulars search error:', error);
                    })
                    .finally(function () {
                        particularsResults.classList.remove('opacity-50', 'pointer-events-none');
                    });
            }

            if (particularsSearchInput && particularsResults) {
                particularsSearchInput.addEventListener('input', function () {
                    const query = particularsSearchInput.value;
                    toggleParticularsClearButton(query);
                    clearTimeout(particularsSearchTimer);
                    particularsSearchTimer = setTimeout(function () {
                        loadParticularsList(particularsListUrl(query, 1));
                    }, 350);
                });

                particularsSearchInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(particularsSearchTimer);
                        loadParticularsList(particularsListUrl(particularsSearchInput.value, 1));
                    }
                });
            }

            if (particularsClearBtn && particularsSearchInput) {
                particularsClearBtn.addEventListener('click', function () {
                    particularsSearchInput.value = '';
                    toggleParticularsClearButton('');
                    loadParticularsList(particularsListUrl('', 1));
                    particularsSearchInput.focus();
                });
            }

            if (particularsResults) {
                particularsResults.addEventListener('click', function (e) {
                    const pageLink = e.target.closest('.existing-particulars-pagination a');
                    if (!pageLink || !pageLink.href) {
                        return;
                    }
                    e.preventDefault();
                    loadParticularsList(pageLink.href);
                });
            }

            /* ---------- Reusable combobox ---------- */
            function createCombobox(searchInputId, hiddenInputId, listId, options) {
                const searchInput = document.getElementById(searchInputId);
                const hiddenInput = document.getElementById(hiddenInputId);
                const list = document.getElementById(listId);
                const opts = options || {};
                let items = [];
                let activeIndex = -1;

                function open() {
                    if (searchInput.disabled) { return; }
                    render(searchInput.value);
                    list.hidden = false;
                    searchInput.setAttribute('aria-expanded', 'true');
                }

                function close() {
                    list.hidden = true;
                    activeIndex = -1;
                    searchInput.setAttribute('aria-expanded', 'false');
                }

                function setItems(newItems) {
                    items = (newItems || []).map(i => ({ id: String(i.id), name: i.name }));
                }

                function render(filterText) {
                    const query = String(filterText || '').trim().toLowerCase();
                    const matches = items.filter(i => query === '' || i.name.toLowerCase().includes(query));
                    list.innerHTML = '';
                    activeIndex = -1;

                    if (matches.length === 0) {
                        const empty = document.createElement('li');
                        empty.className = 'hms-combobox-empty';
                        empty.textContent = items.length === 0 ? (opts.emptySourceText || 'No options available') : 'No matches found';
                        list.appendChild(empty);
                        return;
                    }

                    matches.forEach(match => {
                        const li = document.createElement('li');
                        li.className = 'hms-combobox-option';
                        li.setAttribute('role', 'option');
                        li.dataset.id = match.id;
                        li.textContent = match.name;
                        li.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            select(match.id, match.name);
                        });
                        list.appendChild(li);
                    });
                }

                function select(id, name) {
                    hiddenInput.value = id;
                    searchInput.value = name;
                    close();
                    if (typeof opts.onSelect === 'function') { opts.onSelect(id, name); }
                }

                function clearSelection() {
                    hiddenInput.value = '';
                    if (typeof opts.onClear === 'function') { opts.onClear(); }
                }

                function setDisabled(disabled, placeholder) {
                    searchInput.disabled = disabled;
                    if (placeholder) { searchInput.placeholder = placeholder; }
                }

                function reset() {
                    searchInput.value = '';
                    clearSelection();
                }

                searchInput.addEventListener('focus', open);
                searchInput.addEventListener('click', open);
                searchInput.addEventListener('input', function () {
                    // Typing invalidates any previous exact selection until re-picked.
                    if (hiddenInput.value) {
                        const current = items.find(i => i.id === hiddenInput.value);
                        if (!current || current.name !== searchInput.value) { clearSelection(); }
                    }
                    render(searchInput.value);
                    list.hidden = false;
                });

                searchInput.addEventListener('keydown', function (e) {
                    const optionEls = Array.from(list.querySelectorAll('.hms-combobox-option'));
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (list.hidden) { open(); return; }
                        activeIndex = Math.min(activeIndex + 1, optionEls.length - 1);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        activeIndex = Math.max(activeIndex - 1, 0);
                    } else if (e.key === 'Enter') {
                        if (!list.hidden && activeIndex >= 0 && optionEls[activeIndex]) {
                            e.preventDefault();
                            const el = optionEls[activeIndex];
                            select(el.dataset.id, el.textContent);
                        }
                        return;
                    } else if (e.key === 'Escape') {
                        close();
                        return;
                    } else {
                        return;
                    }
                    optionEls.forEach((el, idx) => el.classList.toggle('is-active', idx === activeIndex));
                    if (optionEls[activeIndex]) { optionEls[activeIndex].scrollIntoView({ block: 'nearest' }); }
                });

                document.addEventListener('click', function (e) {
                    if (!list.hidden && !searchInput.parentElement.contains(e.target)) { close(); }
                });

                return { setItems, render, select, reset, setDisabled, get value() { return hiddenInput.value; } };
            }

            /* ---------- Wire up Test Head + Test Name comboboxes ---------- */
            const testNameCombo = createCombobox('test_name_search', 'test_id', 'test_name_list', {
                emptySourceText: 'Select a Test Head first',
                onSelect: syncTestId,
                onClear: syncTestId
            });

            const testHeadCombo = createCombobox('test_head_search', 'test_head_id', 'test_head_list', {
                emptySourceText: 'No test heads available',
                onSelect: function (id) { loadTests(id, ''); },
                onClear: function () {
                    testNameCombo.reset();
                    testNameCombo.setItems([]);
                    testNameCombo.setDisabled(true, 'Select Test Head first…');
                    syncTestId();
                }
            });
            testHeadCombo.setItems(TEST_HEADS);

            const bulkTestIdInput = document.getElementById('bulk_test_id');
            const editTestIdInput = document.getElementById('edit_test_id');

            // Existing particular counts per test (used to suggest the next report order).
            const TEST_PARTICULAR_COUNTS = {};

            function syncTestId() {
                const testId = document.getElementById('test_id').value;
                if (bulkTestIdInput) { bulkTestIdInput.value = testId; }
                if (editTestIdInput) { editTestIdInput.value = testId; }
            }

            function loadTests(testHeadId, testIdToSelect) {
                if (!testHeadId) {
                    testNameCombo.setItems([]);
                    testNameCombo.setDisabled(true, 'Select Test Head first…');
                    return;
                }
                testNameCombo.setDisabled(false, 'Loading tests…');
                const url = TESTS_BY_HEAD_URL.replace('TEST_HEAD_ID', encodeURIComponent(testHeadId));
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(t => { TEST_PARTICULAR_COUNTS[t.id] = t.particulars_count || 0; });
                        testNameCombo.setItems(data);
                        testNameCombo.setDisabled(false, 'Search Test Name…');
                        if (testIdToSelect) {
                            const match = data.find(t => String(t.id) === String(testIdToSelect));
                            if (match) { testNameCombo.select(String(match.id), match.name); }
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching tests:', error);
                        testNameCombo.setItems([]);
                        testNameCombo.setDisabled(false, 'Error loading tests');
                    });
            }

            // Preselect on load (edit mode, or validation redirect with old input).
            const initialHeadId = document.getElementById('test_head_id').value;
            const initialTestId = document.getElementById('test_id').value;
            if (initialHeadId) { loadTests(initialHeadId, initialTestId); }
            syncTestId();

            /* ---------- Cart workflow (create mode only) ---------- */
            if (!IS_EDIT) {
                const cart = [];
                const cartBody = document.getElementById('cart-body');
                const cartEmpty = document.getElementById('cart-empty');
                const cartTableWrap = document.getElementById('cart-table-wrap');
                const cartCount = document.getElementById('cart-count');
                const saveBtn = document.getElementById('save-cart-btn');
                const addBtn = document.getElementById('add-to-cart-btn');
                const bulkHiddenInputs = document.getElementById('bulk-hidden-inputs');
                const bulkForm = document.getElementById('bulk-save-form');

                const nameInput = document.getElementById('particular_name');
                const unitInput = document.getElementById('unit');
                const minInput = document.getElementById('normal_range_min');
                const maxInput = document.getElementById('normal_range_max');
                const refInput = document.getElementById('reference_text');
                const orderInput = document.getElementById('sort_order');

                // Track whether the user manually set a report order. If not, we
                // keep auto-suggesting a position that appends after existing rows.
                let orderTouched = false;
                orderInput.addEventListener('input', function () { orderTouched = true; });

                function escapeHtml(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                }

                function renderCart() {
                    cartBody.innerHTML = '';
                    cart.forEach((item, index) => {
                        const tr = document.createElement('tr');
                        const range = [item.normal_range_min, item.normal_range_max]
                            .map(v => (v === '' || v === null || v === undefined) ? '' : v).join(' - ');
                        tr.innerHTML =
                            '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">' + escapeHtml(item.sort_order) + '</td>' +
                            '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">' + escapeHtml(item.particular_name) + '</td>' +
                            '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">' + escapeHtml(item.unit) + '</td>' +
                            '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">' + escapeHtml(range) + '</td>' +
                            '<td class="px-4 py-3 text-sm text-gray-900">' + escapeHtml(item.reference_text) + '</td>' +
                            '<td class="px-4 py-3 whitespace-nowrap text-sm font-medium"><button type="button" class="text-red-600 hover:text-red-900" data-remove="' + index + '">Remove</button></td>';
                        cartBody.appendChild(tr);
                    });

                    const hasItems = cart.length > 0;
                    cartEmpty.hidden = hasItems;
                    cartTableWrap.hidden = !hasItems;
                    cartCount.textContent = '(' + cart.length + ')';
                    saveBtn.disabled = !hasItems;
                }

                function nextOrder() {
                    // Suggest a position after the test's existing particulars plus
                    // whatever is already queued in the cart, so the default appends
                    // instead of pushing the existing report order down.
                    const testId = document.getElementById('test_id').value;
                    const existing = parseInt(TEST_PARTICULAR_COUNTS[testId], 10) || 0;
                    return existing + cart.length + 1;
                }

                function clearParticularFields() {
                    nameInput.value = '';
                    unitInput.value = '';
                    minInput.value = '';
                    maxInput.value = '';
                    refInput.value = '';
                    orderInput.value = nextOrder();
                    orderTouched = false;
                    nameInput.focus();
                }

                addBtn.addEventListener('click', function () {
                    if (!document.getElementById('test_id').value) {
                        alert('Please select a Test Head and Test Name first.');
                        return;
                    }
                    const name = nameInput.value.trim();
                    if (!name) {
                        alert('Particular Name is required.');
                        nameInput.focus();
                        return;
                    }
                    const min = minInput.value.trim();
                    const max = maxInput.value.trim();
                    if (min !== '' && max !== '' && !isNaN(parseFloat(min)) && !isNaN(parseFloat(max)) && parseFloat(max) < parseFloat(min)) {
                        alert('Normal Range (Max) must be greater than or equal to Min.');
                        return;
                    }
                    let order;
                    if (orderTouched) {
                        order = parseInt(orderInput.value, 10);
                        if (isNaN(order) || order < 0) { order = nextOrder(); }
                    } else {
                        order = nextOrder();
                    }

                    cart.push({
                        particular_name: name,
                        unit: unitInput.value.trim(),
                        normal_range_min: min,
                        normal_range_max: max,
                        reference_text: refInput.value.trim(),
                        sort_order: order
                    });
                    renderCart();
                    clearParticularFields();
                });

                cartBody.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-remove]');
                    if (!btn) { return; }
                    cart.splice(parseInt(btn.dataset.remove, 10), 1);
                    renderCart();
                });

                bulkForm.addEventListener('submit', function (e) {
                    if (cart.length === 0) {
                        e.preventDefault();
                        alert('Add at least one particular to the cart before saving.');
                        return;
                    }
                    if (!document.getElementById('test_id').value) {
                        e.preventDefault();
                        alert('Please select a Test Head and Test Name.');
                        return;
                    }
                    bulkHiddenInputs.innerHTML = '';
                    cart.forEach((item, index) => {
                        const fields = {
                            particular_name: item.particular_name,
                            unit: item.unit,
                            normal_range_min: item.normal_range_min,
                            normal_range_max: item.normal_range_max,
                            reference_text: item.reference_text,
                            sort_order: item.sort_order
                        };
                        Object.keys(fields).forEach(key => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'particulars[' + index + '][' + key + ']';
                            input.value = fields[key] === null || fields[key] === undefined ? '' : fields[key];
                            bulkHiddenInputs.appendChild(input);
                        });
                    });
                });

                // Initialize report order suggestion.
                orderInput.value = orderInput.value || nextOrder();
                renderCart();
            }
        });
    </script>
@endsection
