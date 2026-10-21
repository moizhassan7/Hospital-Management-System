@extends('layouts.app')

@section('title', isset($consumableItem) ? 'Edit consumable item' : 'Consumable items')

@section('content')
    @php
        $currency = get_setting('currency_symbol', 'Rs');
    @endphp

    <x-ui.page-header :title="isset($consumableItem) ? 'Edit consumable item' : 'Consumable items'" :subtitle="isset($consumableItem) ? $consumableItem->name : 'Items that are used up, such as medicines, syringes and dressings.'">
        <x-slot:actions>
            <a href="{{ route('store.definitions') }}" class="btn btn-secondary">
                <x-ui.icon name="layers" />Categories & locations
            </a>
            <a href="{{ route('store.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Store
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="stack">
        <x-ui.panel :title="isset($consumableItem) ? 'Edit ' . $consumableItem->name : 'Add consumable item'" :icon="isset($consumableItem) ? 'pencil' : 'plus'" flush>
            <form action="{{ isset($consumableItem) ? route('store.consumable_items.update', $consumableItem->id) : route('store.consumable_items.store') }}" method="POST">
                @csrf
                @isset($consumableItem)
                    @method('PUT')
                @endisset
                <div class="panel-body">
                    <div class="form-grid">
                        <x-ui.field label="Item name" for="name" error="name" required class="md:col-span-6">
                            <input type="text" id="name" name="name" @class(['input', 'is-invalid' => $errors->has('name')]) placeholder="e.g. Bandage, Syringe" value="{{ old('name', $consumableItem->name ?? '') }}" autocomplete="off" required>
                        </x-ui.field>
                        <x-ui.field label="Unit" for="unit" error="unit" class="md:col-span-3">
                            <select id="unit" name="unit" @class(['select', 'is-invalid' => $errors->has('unit')])>
                                <option value="">Select unit</option>
                                <option value="pcs" @selected(old('unit', $consumableItem->unit ?? '') == 'pcs')>Pcs</option>
                                <option value="box" @selected(old('unit', $consumableItem->unit ?? '') == 'box')>Box</option>
                                <option value="ml" @selected(old('unit', $consumableItem->unit ?? '') == 'ml')>ML</option>
                                <option value="g" @selected(old('unit', $consumableItem->unit ?? '') == 'g')>Grams</option>
                                <option value="tablet" @selected(old('unit', $consumableItem->unit ?? '') == 'tablet')>Tablet</option>
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Barcode" for="barcode" error="barcode" class="md:col-span-3">
                            <input type="text" id="barcode" name="barcode" @class(['input input-mono', 'is-invalid' => $errors->has('barcode')]) placeholder="e.g. 123456789012" value="{{ old('barcode', $consumableItem->barcode ?? '') }}">
                        </x-ui.field>
                        <x-ui.field :label="'Purchase price (' . $currency . ')'" for="purchase_price" error="purchase_price" required class="md:col-span-3">
                            <input type="number" id="purchase_price" name="purchase_price" @class(['input input-num', 'is-invalid' => $errors->has('purchase_price')]) placeholder="e.g. 1.00" min="0" step="0.01" value="{{ old('purchase_price', $consumableItem->purchase_price ?? '') }}" required>
                        </x-ui.field>
                        <x-ui.field :label="'Sale price (' . $currency . ')'" for="sale_price" error="sale_price" required class="md:col-span-3">
                            <input type="number" id="sale_price" name="sale_price" @class(['input input-num', 'is-invalid' => $errors->has('sale_price')]) placeholder="e.g. 1.50" min="0" step="0.01" value="{{ old('sale_price', $consumableItem->sale_price ?? '') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Minimum stock level" for="min_stock_level" error="min_stock_level" required class="md:col-span-3">
                            <input type="number" id="min_stock_level" name="min_stock_level" @class(['input input-num', 'is-invalid' => $errors->has('min_stock_level')]) placeholder="e.g. 20" min="0" value="{{ old('min_stock_level', $consumableItem->min_stock_level ?? '') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Company name" for="company_name" error="company_name" required class="md:col-span-3">
                            <input type="text" id="company_name" name="company_name" @class(['input', 'is-invalid' => $errors->has('company_name')]) placeholder="e.g. Pharma Corp" value="{{ old('company_name', $consumableItem->company_name ?? '') }}" required>
                        </x-ui.field>
                        <x-ui.field label="Category" for="category_id" error="category_id" required class="md:col-span-6">
                            <div class="input-group">
                                <select id="category_id" name="category_id" @class(['select', 'is-invalid' => $errors->has('category_id')]) required>
                                    <option value="">Select category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $consumableItem->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" id="add_category_btn" class="input-group-btn btn btn-secondary" title="Add category">
                                    <x-ui.icon name="plus" />
                                </button>
                            </div>
                        </x-ui.field>
                        <x-ui.field label="Location" for="location_id" error="location_id" required class="md:col-span-6">
                            <div class="input-group">
                                <select id="location_id" name="location_id" @class(['select', 'is-invalid' => $errors->has('location_id')]) required>
                                    <option value="">Select location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" @selected(old('location_id', $consumableItem->location_id ?? '') == $location->id)>{{ $location->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" id="add_location_btn" class="input-group-btn btn btn-secondary" title="Add location">
                                    <x-ui.icon name="plus" />
                                </button>
                            </div>
                        </x-ui.field>
                        <label class="option-row md:col-span-12">
                            <input type="checkbox" name="is_active" id="is_active" value="1" class="checkbox" @checked(old('is_active', $consumableItem->is_active ?? true))>
                            <span>
                                <span class="option-row-title">Item is active</span>
                                <span class="option-row-desc">Inactive items stay on record but are not offered on new stock movements.</span>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="panel-footer">
                    @isset($consumableItem)
                        <a href="{{ route('store.consumable_items') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($consumableItem) ? 'save' : 'plus' }}" />{{ isset($consumableItem) ? 'Update item' : 'Add item' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing consumable items" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#consumable-items-table">{{ count($consumableItems) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter items" data-table-filter="#consumable-items-table" />
            </div>
            <div class="table-wrap">
                <table id="consumable-items-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Unit</th>
                            <th class="num">P. price</th>
                            <th class="num">S. price</th>
                            <th>Category</th>
                            <th>Company</th>
                            <th class="num">Min stock</th>
                            <th>Location</th>
                            <th>Barcode</th>
                            <th>Status</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consumableItems as $index => $item)
                            <tr @class(['bg-brand-50/50' => isset($consumableItem) && $consumableItem->id === $item->id, 'is-inactive' => ! $item->is_active])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $item->id }}</span></td>
                                <td class="cell-strong">{{ $item->name }}</td>
                                <td>{{ $item->unit ?? '—' }}</td>
                                <td class="num"><x-ui.money :amount="$item->purchase_price" /></td>
                                <td class="num"><x-ui.money :amount="$item->sale_price" /></td>
                                <td>{{ $item->category->name ?? '—' }}</td>
                                <td>{{ $item->company_name ?? '—' }}</td>
                                <td class="num">{{ $item->min_stock_level }}</td>
                                <td>{{ $item->location->name ?? '—' }}</td>
                                <td><span class="id">{{ $item->barcode ?? '—' }}</span></td>
                                <td><x-ui.badge :tone="$item->is_active ? 'success' : 'neutral'" dot>{{ $item->is_active ? 'Active' : 'Inactive' }}</x-ui.badge></td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('store.consumable_items.edit', $item->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <button type="button" onclick="confirmDelete({{ $item->id }})" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="13" class="p-0">
                                    <x-ui.empty-state icon="package" title="No consumable items yet" description="Add medicines and supplies using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.dialog id="deleteConfirmationModal" title="Confirm deletion" description="Are you sure you want to delete this consumable item? This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete</button>
        </x-slot:footer>
    </x-ui.dialog>

    <x-ui.dialog id="categoryModal" title="Category" icon="layers" size="lg">
        <h3 class="sr-only" id="categoryModalTitle">Add/Edit Category</h3>
        <form id="categoryForm" action="#" method="POST">
            @csrf
            <input type="hidden" id="category_edit_id" name="id">
            <x-ui.field label="Category name" for="category_name" required>
                <input type="text" id="category_name" name="name" class="input" placeholder="e.g. Medicines" required>
            </x-ui.field>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeModal('categoryModal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save category</button>
            </div>
        </form>
        <div class="mt-6">
            <h4 class="mb-2 text-[13px] font-semibold text-fg">Existing categories</h4>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Category name</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody"></tbody>
                </table>
            </div>
        </div>
    </x-ui.dialog>

    <x-ui.dialog id="locationModal" title="Location" icon="map-pin" size="lg">
        <h3 class="sr-only" id="locationModalTitle">Add/Edit Location</h3>
        <form id="locationForm" action="#" method="POST">
            @csrf
            <input type="hidden" id="location_edit_id" name="id">
            <x-ui.field label="Location name" for="location_name" required>
                <input type="text" id="location_name" name="name" class="input" placeholder="e.g. Main Store" required>
            </x-ui.field>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeModal('locationModal')" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-primary">Save location</button>
            </div>
        </form>
        <div class="mt-6">
            <h4 class="mb-2 text-[13px] font-semibold text-fg">Existing locations</h4>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Location name</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody id="locationTableBody"></tbody>
                </table>
            </div>
        </div>
    </x-ui.dialog>

    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
        const cancelDeleteButton = document.getElementById('cancelDeleteButton');
        const confirmDeleteButton = document.getElementById('confirmDeleteButton');
        const deleteForm = document.getElementById('deleteForm');
        let itemIdToDelete = null;

        window.confirmDelete = function(itemId) {
            itemIdToDelete = itemId;
            deleteConfirmationModal.classList.remove('hidden');
        }

        cancelDeleteButton.addEventListener('click', () => {
            deleteConfirmationModal.classList.add('hidden');
            itemIdToDelete = null;
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (itemIdToDelete !== null) {
                deleteForm.action = `/store/consumable-items/${itemIdToDelete}`;
                deleteForm.submit();
            }
            deleteConfirmationModal.classList.add('hidden');
            itemIdToDelete = null;
        });

        deleteConfirmationModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('hidden');
                itemIdToDelete = null;
            }
        });

        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            if (modalId === 'categoryModal') {
                categoryForm.reset();
                categoryEditIdInput.value = '';
                categoryModalTitle.textContent = 'Add New Category';
            } else if (modalId === 'locationModal') {
                locationForm.reset();
                locationEditIdInput.value = '';
                locationModalTitle.textContent = 'Add New Location';
            }
        }

        const categoryModal = document.getElementById('categoryModal');
        const addCategoryBtn = document.getElementById('add_category_btn');
        const categoryForm = document.getElementById('categoryForm');
        const categoryNameInput = document.getElementById('category_name');
        const categoryEditIdInput = document.getElementById('category_edit_id');
        const categoryModalTitle = document.getElementById('categoryModalTitle');
        const categoryTableBody = document.getElementById('categoryTableBody');
        const categorySelect = document.getElementById('category_id');

        const locationModal = document.getElementById('locationModal');
        const addLocationBtn = document.getElementById('add_location_btn');
        const locationForm = document.getElementById('locationForm');
        const locationNameInput = document.getElementById('location_name');
        const locationEditIdInput = document.getElementById('location_edit_id');
        const locationModalTitle = document.getElementById('locationModalTitle');
        const locationTableBody = document.getElementById('locationTableBody');
        const locationSelect = document.getElementById('location_id');

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

        async function populateCategoryDropdownAndTable() {
            try {
                const categories = await fetchData('/api/categories');
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                categoryTableBody.innerHTML = '';
                categories.forEach((cat, index) => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    categorySelect.appendChild(option);

                    const row = categoryTableBody.insertRow();
                    row.innerHTML = `
                        <td class="num muted">${index + 1}</td>
                        <td class="cell-strong">${cat.name}</td>
                        <td class="actions">
                            <button type="button" onclick="editCategory(${cat.id}, '${cat.name}')" class="btn btn-ghost btn-sm">Edit</button>
                            <button type="button" onclick="deleteCategory(${cat.id})" class="btn btn-danger-ghost btn-sm">Delete</button>
                        </td>
                    `;
                });
                if (categorySelect.dataset.oldValue) {
                    categorySelect.value = categorySelect.dataset.oldValue;
                    delete categorySelect.dataset.oldValue;
                } else if (categorySelect.dataset.currentValue) {
                    categorySelect.value = categorySelect.dataset.currentValue;
                }
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        }

        addCategoryBtn.addEventListener('click', () => {
            categoryForm.reset();
            categoryEditIdInput.value = '';
            categoryModalTitle.textContent = 'Add New Category';
            openModal('categoryModal');
        });

        window.editCategory = function(id, name) {
            categoryNameInput.value = name;
            categoryEditIdInput.value = id;
            categoryModalTitle.textContent = `Edit Category: ${name}`;
            openModal('categoryModal');
        };

        window.deleteCategory = async function(id) {
            if (confirm('Are you sure you want to delete this category?')) {
                try {
                    await sendData(`/api/categories/${id}`, 'DELETE');
                    alert('Category deleted successfully!');
                    await populateCategoryDropdownAndTable();
                    if (categorySelect.value == id) {
                        categorySelect.value = '';
                    }
                } catch (error) {
                    console.error('Error deleting category:', error);
                    alert('Failed to delete category: ' + error.message);
                }
            }
        };

        categoryForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            const id = categoryEditIdInput.value;
            const name = categoryNameInput.value;
            try {
                if (id) {
                    await sendData(`/api/categories/${id}`, 'PUT', { name: name });
                    alert('Category updated successfully!');
                } else {
                    await sendData('/api/categories', 'POST', { name: name });
                    alert('Category added successfully!');
                }
                closeModal('categoryModal');
                await populateCategoryDropdownAndTable();
            } catch (error) {
                console.error('Error saving category:', error);
                alert('Failed to save category: ' + error.message);
            }
        });

        async function populateLocationDropdownAndTable() {
            try {
                const locations = await fetchData('/api/locations');
                locationSelect.innerHTML = '<option value="">Select Location</option>';
                locationTableBody.innerHTML = '';
                locations.forEach((loc, index) => {
                    const option = document.createElement('option');
                    option.value = loc.id;
                    option.textContent = loc.name;
                    locationSelect.appendChild(option);

                    const row = locationTableBody.insertRow();
                    row.innerHTML = `
                        <td class="num muted">${index + 1}</td>
                        <td class="cell-strong">${loc.name}</td>
                        <td class="actions">
                            <button type="button" onclick="editLocation(${loc.id}, '${loc.name}')" class="btn btn-ghost btn-sm">Edit</button>
                            <button type="button" onclick="deleteLocation(${loc.id})" class="btn btn-danger-ghost btn-sm">Delete</button>
                        </td>
                    `;
                });
                if (locationSelect.dataset.oldValue) {
                    locationSelect.value = locationSelect.dataset.oldValue;
                    delete locationSelect.dataset.oldValue;
                } else if (locationSelect.dataset.currentValue) {
                    locationSelect.value = locationSelect.dataset.currentValue;
                }
            } catch (error) {
                console.error('Error fetching locations:', error);
            }
        }

        addLocationBtn.addEventListener('click', () => {
            locationForm.reset();
            locationEditIdInput.value = '';
            locationModalTitle.textContent = 'Add New Location';
            openModal('locationModal');
        });

        window.editLocation = function(id, name) {
            locationNameInput.value = name;
            locationEditIdInput.value = id;
            locationModalTitle.textContent = `Edit Location: ${name}`;
            openModal('locationModal');
        };

        window.deleteLocation = async function(id) {
            if (confirm('Are you sure you want to delete this location?')) {
                try {
                    await sendData(`/api/locations/${id}`, 'DELETE');
                    alert('Location deleted successfully!');
                    await populateLocationDropdownAndTable();
                    if (locationSelect.value == id) {
                        locationSelect.value = '';
                    }
                } catch (error) {
                    console.error('Error deleting location:', error);
                    alert('Failed to delete location: ' + error.message);
                }
            }
        };

        locationForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            const id = locationEditIdInput.value;
            const name = locationNameInput.value;
            try {
                if (id) {
                    await sendData(`/api/locations/${id}`, 'PUT', { name: name });
                    alert('Location updated successfully!');
                } else {
                    await sendData('/api/locations', 'POST', { name: name });
                    alert('Location added successfully!');
                }
                closeModal('locationModal');
                await populateLocationDropdownAndTable();
            } catch (error) {
                console.error('Error saving location:', error);
                alert('Failed to save location: ' + error.message);
            }
        });

        document.addEventListener('DOMContentLoaded', async () => {
            categorySelect.dataset.oldValue = categorySelect.value;
            locationSelect.dataset.oldValue = locationSelect.value;

            await populateCategoryDropdownAndTable();
            await populateLocationDropdownAndTable();
        });
    </script>
@endsection
