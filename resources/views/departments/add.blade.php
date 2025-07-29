@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">
            @isset($department)
                Edit Department: {{ $department->name }}
            @else
                Add New Department
            @endisset
        </h2>
        <a href="{{ route('departments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Departments
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

    <!-- Add/Edit Department Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Department Details</h3>
        <form action="{{ isset($department) ? route('departments.update', $department->id) : route('departments.store') }}" method="POST">
            @csrf
            @isset($department)
                @method('PUT') {{-- Use PUT method for updates --}}
            @endisset
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="department_name" class="block text-gray-700 text-sm font-bold mb-2">Department Name:</label>
                    <input type="text" id="department_name" name="department_name" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('department_name') border-red-500 @enderror" placeholder="e.g., Cardiology" value="{{ old('department_name', $department->name ?? '') }}" autocomplete="off" required>
                    @error('department_name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="number_of_beds" class="block text-gray-700 text-sm font-bold mb-2">Number of Beds:</label>
                    <input type="number" id="number_of_beds" name="number_of_beds" class="shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('number_of_beds') border-red-500 @enderror" placeholder="e.g., 50" min="0" value="{{ old('number_of_beds', $department->number_of_beds ?? '') }}" autocomplete="off" required>
                    @error('number_of_beds')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500" {{ old('is_active', $department->is_active ?? false) ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-700 text-sm font-bold">Department is currently in use</span>
                </label>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    @isset($department)
                        Update Department
                    @else
                        Add Department
                    @endisset
                </button>
            </div>
        </form>
    </div>

    <!-- Department List Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Existing Departments</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sr. No.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dept ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dept Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. of Beds</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    {{-- Dynamically rendered Department Data from database --}}
                    @forelse($departments as $index => $dept)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $dept->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $dept->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $dept->number_of_beds }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $dept->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $dept->is_active ? 'In Use' : 'Not In Use' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('departments.edit', $dept->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                {{-- <button type="button" onclick="confirmDelete({{ $dept->id }})" class="text-red-600 hover:text-red-900 ">Delete</button> --}}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No departments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div id="deleteConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Deletion</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">Are you sure you want to delete this department? This action cannot be undone.</p>
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

    <!-- Hidden form for DELETE request -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        // JavaScript for custom delete confirmation modal
        const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
        const cancelDeleteButton = document.getElementById('cancelDeleteButton');
        const confirmDeleteButton = document.getElementById('confirmDeleteButton');
        const deleteForm = document.getElementById('deleteForm');
        let departmentIdToDelete = null;

        function confirmDelete(departmentId) {
            departmentIdToDelete = departmentId;
            deleteConfirmationModal.classList.remove('hidden');
        }

        cancelDeleteButton.addEventListener('click', () => {
            deleteConfirmationModal.classList.add('hidden');
            departmentIdToDelete = null;
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (departmentIdToDelete !== null) {
                deleteForm.action = `/departments/${departmentIdToDelete}`; // Construct the delete URL
                deleteForm.submit();
            }
            deleteConfirmationModal.classList.add('hidden');
            departmentIdToDelete = null;
        });

        // Close modal if clicked outside (optional)
        deleteConfirmationModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('hidden');
                departmentIdToDelete = null;
            }
        });
        // If the Department is not active, make the table row disabled as grayed out
        document.querySelectorAll('tbody tr').forEach(row => {
            const statusCell = row.querySelector('td:nth-child(5)');
            if (statusCell && statusCell.textContent.trim() === 'Not In Use') {
                row.classList.add('opacity-50', 'cursor-not-allowed');
                row.querySelectorAll('td').forEach(cell => {
                    cell.classList.add('text-gray-500');
                });
            }
        });
    </script>
@endsection
