@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">
            @isset($consumableItem)
                Edit Consumable Item: {{ $consumableItem->name }}
            @else
                Add New Consumable Item
            @endisset
        </h2>
        <a href="{{ route('store.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Store Management
        </a>
    </div>

    <!-- Success/Error Messages -->
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

    <!-- Add/Edit Consumable Item Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Item Details</h3>
        <form action="{{ isset($consumableItem) ? route('store.consumable_items.update', $consumableItem->id) : route('store.consumable_items.store') }}" method="POST">
            @csrf
            @isset($consumableItem)
                @method('PUT') {{-- Use PUT method for updates --}}
            @endisset
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Item Name:</label>
                    <input type="text" id="name" name="name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror" placeholder="e.g., Bandage, Syringe" value="{{ old('name', $consumableItem->name ?? '') }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="unit" class="block text-gray-700 text-sm font-bold mb-2">Unit:</label>
                    <select id="unit" name="unit" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('unit') border-red-500 @enderror">
                        <option value="">Select Unit</option>
                        <option value="pcs" {{ old('unit', $consumableItem->unit ?? '') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                        <option value="box" {{ old('unit', $consumableItem->unit ?? '') == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="ml" {{ old('unit', $consumableItem->unit ?? '') == 'ml' ? 'selected' : '' }}>ML</option>
                        <option value="g" {{ old('unit', $consumableItem->unit ?? '') == 'g' ? 'selected' : '' }}>Grams</option>
                        <option value="tablet" {{ old('unit', $consumableItem->unit ?? '') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                    </select>
                    @error('unit')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="purchase_price" class="block text-gray-700 text-sm font-bold mb-2">Purchase Price ($):</label>
                    <input type="number" id="purchase_price" name="purchase_price" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('purchase_price') border-red-500 @enderror" placeholder="e.g., 1.00" min="0" step="0.01" value="{{ old('purchase_price', $consumableItem->purchase_price ?? '') }}" required>
                    @error('purchase_price')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sale_price" class="block text-gray-700 text-sm font-bold mb-2">Sale Price ($):</label>
                    <input type="number" id="sale_price" name="sale_price" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('sale_price') border-red-500 @enderror" placeholder="e.g., 1.50" min="0" step="0.01" value="{{ old('sale_price', $consumableItem->sale_price ?? '') }}" required>
                    @error('sale_price')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                    <div class="flex items-center space-x-2">
                        <select id="category_id" name="category_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('category_id') border-red-500 @enderror" required>
                            <option value="">Select Category</option>
                            {{-- Dynamically rendered Categories from database --}}
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $consumableItem->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" id="add_category_btn" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                    </div>
                    @error('category_id')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="company_name" class="block text-gray-700 text-sm font-bold mb-2">Company Name:</label>
                    <input type="text" id="company_name" name="company_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('company_name') border-red-500 @enderror" placeholder="e.g., Pharma Corp" value="{{ old('company_name', $consumableItem->company_name ?? '') }}" required>
                    @error('company_name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="min_stock_level" class="block text-gray-700 text-sm font-bold mb-2">Minimum Stock Level:</label>
                    <input type="number" id="min_stock_level" name="min_stock_level" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('min_stock_level') border-red-500 @enderror" placeholder="e.g., 20" min="0" value="{{ old('min_stock_level', $consumableItem->min_stock_level ?? '') }}" required>
                    @error('min_stock_level')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="location_id" class="block text-gray-700 text-sm font-bold mb-2">Location:</label>
                    <div class="flex items-center space-x-2">
                        <select id="location_id" name="location_id" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('location_id') border-red-500 @enderror" required>
                            <option value="">Select Location</option>
                            {{-- Dynamically rendered Locations from database --}}
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('location_id', $consumableItem->location_id ?? '') == $location->id ? 'selected' : '' }}>
                                    {{ $location->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" id="add_location_btn" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </button>
                    </div>
                    @error('location_id')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="barcode" class="block text-gray-700 text-sm font-bold mb-2">Barcode:</label>
                    <input type="text" id="barcode" name="barcode" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('barcode') border-red-500 @enderror" placeholder="e.g., 123456789012" value="{{ old('barcode', $consumableItem->barcode ?? '') }}">
                    @error('barcode')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-1 flex items-center mt-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500" {{ old('is_active', $consumableItem->is_active ?? true) ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700 text-sm font-bold">Item is Active</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    @isset($consumableItem)
                        Update Item
                    @else
                        Add Item
                    @endisset
                </button>
            </div>
        </form>
    </div>

    <!-- Consumable Items List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Consumable Items</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">P. Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S. Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Stock Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Dynamically rendered Consumable Items Data from database --}}
                    @forelse($consumableItems as $index => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->unit ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->purchase_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($item->sale_price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->category->name ?? 'N/A' }}</td> {{-- Display category name --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->company_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->min_stock_level }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->location->name ?? 'N/A' }}</td> {{-- Display location name --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->barcode ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($item->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('store.consumable_items.edit', $item->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <button type="button" onclick="confirmDelete({{ $item->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No consumable items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div id="deleteConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Deletion</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Are you sure you want to delete this consumable item? This action cannot be undone.</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="cancelDeleteButton" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-24 mr-2 shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button id="confirmDeleteButton" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-24 shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900" id="categoryModalTitle">Add/Edit Category</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('categoryModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="categoryForm" action="#" method="POST">
                @csrf
                <input type="hidden" id="category_edit_id" name="id">
                <div class="mb-4">
                    <label for="category_name" class="block text-gray-700 text-sm font-bold mb-2">Category Name:</label>
                    <input type="text" id="category_name" name="name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Medicines" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('categoryModal')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-full">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full">Save Category</button>
                </div>
            </form>

            <div class="mt-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">Existing Categories</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sr. No.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoryTableBody" class="divide-y divide-gray-200">
                            {{-- Categories will be rendered dynamically by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Modal -->
    <div id="locationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-xl font-semibold text-gray-900" id="locationModalTitle">Add/Edit Location</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeModal('locationModal')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="locationForm" action="#" method="POST">
                @csrf
                <input type="hidden" id="location_edit_id" name="id">
                <div class="mb-4">
                    <label for="location_name" class="block text-gray-700 text-sm font-bold mb-2">Location Name:</label>
                    <input type="text" id="location_name" name="name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., Main Store" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal('locationModal')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-full">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-full">Save Location</button>
                </div>
            </form>

            <div class="mt-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-3">Existing Locations</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border rounded-lg overflow-hidden">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sr. No.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Location Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="locationTableBody" class="divide-y divide-gray-200">
                            {{-- Locations will be rendered dynamically by JavaScript --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Hidden form for DELETE request (for main items) -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- Main Item Delete Confirmation Modal ---
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

        // --- General Modal Functions ---
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

        // --- Category Modal Elements ---
        const categoryModal = document.getElementById('categoryModal');
        const addCategoryBtn = document.getElementById('add_category_btn');
        const categoryForm = document.getElementById('categoryForm');
        const categoryNameInput = document.getElementById('category_name');
        const categoryEditIdInput = document.getElementById('category_edit_id');
        const categoryModalTitle = document.getElementById('categoryModalTitle');
        const categoryTableBody = document.getElementById('categoryTableBody');
        const categorySelect = document.getElementById('category_id'); // Main form's category dropdown

        // --- Location Modal Elements ---
        const locationModal = document.getElementById('locationModal');
        const addLocationBtn = document.getElementById('add_location_btn');
        const locationForm = document.getElementById('locationForm');
        const locationNameInput = document.getElementById('location_name');
        const locationEditIdInput = document.getElementById('location_edit_id');
        const locationModalTitle = document.getElementById('locationModalTitle');
        const locationTableBody = document.getElementById('locationTableBody');
        const locationSelect = document.getElementById('location_id'); // Main form's location dropdown

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
                    'X-CSRF-TOKEN': csrfToken // Include CSRF token
                },
                body: JSON.stringify(data)
            });
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            return await response.json();
        }

        // --- Category Functions (Dynamic) ---
        async function populateCategoryDropdownAndTable() {
            try {
                const categories = await fetchData('/api/categories'); // Fetch from API
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                categoryTableBody.innerHTML = '';
                categories.forEach((cat, index) => {
                    // Populate main form dropdown
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    categorySelect.appendChild(option);

                    // Populate modal table
                    const row = categoryTableBody.insertRow();
                    row.innerHTML = `
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${cat.name}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <button type="button" onclick="editCategory(${cat.id}, '${cat.name}')" class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
                            <button type="button" onclick="deleteCategory(${cat.id})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    `;
                });
                // Re-select old value if editing
                if (categorySelect.dataset.oldValue) {
                    categorySelect.value = categorySelect.dataset.oldValue;
                    delete categorySelect.dataset.oldValue;
                } else if (categorySelect.dataset.currentValue) {
                    categorySelect.value = categorySelect.dataset.currentValue;
                }
            } catch (error) {
                console.error('Error fetching categories:', error);
                // Display a user-friendly error message
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
                    await populateCategoryDropdownAndTable(); // Refresh lists
                    // Optionally, reset main form's category if the deleted one was selected
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
                await populateCategoryDropdownAndTable(); // Refresh lists
            } catch (error) {
                console.error('Error saving category:', error);
                alert('Failed to save category: ' + error.message);
            }
        });

        // --- Location Functions (Dynamic) ---
        async function populateLocationDropdownAndTable() {
            try {
                const locations = await fetchData('/api/locations'); // Fetch from API
                locationSelect.innerHTML = '<option value="">Select Location</option>';
                locationTableBody.innerHTML = '';
                locations.forEach((loc, index) => {
                    // Populate main form dropdown
                    const option = document.createElement('option');
                    option.value = loc.id;
                    option.textContent = loc.name;
                    locationSelect.appendChild(option);

                    // Populate modal table
                    const row = locationTableBody.insertRow();
                    row.innerHTML = `
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${loc.name}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <button type="button" onclick="editLocation(${loc.id}, '${loc.name}')" class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
                            <button type="button" onclick="deleteLocation(${loc.id})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    `;
                });
                // Re-select old value if editing
                if (locationSelect.dataset.oldValue) {
                    locationSelect.value = locationSelect.dataset.oldValue;
                    delete locationSelect.dataset.oldValue;
                } else if (locationSelect.dataset.currentValue) {
                    locationSelect.value = locationSelect.dataset.currentValue;
                }
            } catch (error) {
                console.error('Error fetching locations:', error);
                // Display a user-friendly error message
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
                    await populateLocationDropdownAndTable(); // Refresh lists
                    // Optionally, reset main form's location if the deleted one was selected
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
                await populateLocationDropdownAndTable(); // Refresh lists
            } catch (error) {
                console.error('Error saving location:', error);
                alert('Failed to save location: ' + error.message);
            }
        });

        // --- Initial Load ---
        document.addEventListener('DOMContentLoaded', async () => {
            // Store old values for edit mode before populating dropdowns
            categorySelect.dataset.oldValue = categorySelect.value;
            locationSelect.dataset.oldValue = locationSelect.value;

            await populateCategoryDropdownAndTable();
            await populateLocationDropdownAndTable();
        });
        // if the item is not in used, mark the table color grey!
        

    </script>
@endsection
