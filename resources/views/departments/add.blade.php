@extends('layouts.app')

@section('title', isset($department) ? 'Edit department' : 'Manage departments')

@section('content')
    <x-ui.page-header :title="isset($department) ? 'Edit department' : 'Manage departments'" subtitle="Clinical departments and the number of beds each one holds.">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All setup
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($department) ? 'Edit ' . $department->name : 'Add department'" :description="isset($department) ? 'Changes apply immediately after saving.' : 'Create a department such as Cardiology or Pediatrics.'" :icon="isset($department) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($department) ? route('departments.update', $department->id) : route('departments.store') }}" method="POST">
                @csrf
                @isset($department)
                    @method('PUT')
                @endisset
                <div class="panel-body space-y-4">
                    <x-ui.field label="Department name" for="department_name" error="department_name" required>
                        <input type="text" id="department_name" name="department_name" @class(['input', 'is-invalid' => $errors->has('department_name')]) placeholder="e.g. Cardiology" value="{{ old('department_name', $department->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <x-ui.field label="Number of beds" for="number_of_beds" error="number_of_beds" required>
                        <input type="number" id="number_of_beds" name="number_of_beds" @class(['input input-num w-36', 'is-invalid' => $errors->has('number_of_beds')]) placeholder="e.g. 50" min="0" value="{{ old('number_of_beds', $department->number_of_beds ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <label for="is_active" class="option-row">
                        <span>
                            <span class="option-row-title block">In use</span>
                            <span class="option-row-desc block">Department is currently in use</span>
                        </span>
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="switch" {{ old('is_active', $department->is_active ?? false) ? 'checked' : '' }}>
                    </label>
                </div>
                <div class="panel-footer">
                    @isset($department)
                        <a href="{{ route('departments.add') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($department) ? 'save' : 'plus' }}" />{{ isset($department) ? 'Update department' : 'Add department' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing departments" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#departments-table">{{ count($departments) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter departments" data-table-filter="#departments-table" />
            </div>
            <div class="table-wrap">
                <table id="departments-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Dept ID</th>
                            <th>Department</th>
                            <th class="num">Beds</th>
                            <th>Status</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($departments as $index => $dept)
                            <tr @class(['bg-brand-50/50' => isset($department) && $department->id === $dept->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $dept->id }}</span></td>
                                <td class="cell-strong">{{ $dept->name }}</td>
                                <td class="num">{{ $dept->number_of_beds }}</td>
                                <td><x-ui.badge :tone="$dept->is_active ? 'success' : 'neutral'">{{ $dept->is_active ? 'In Use' : 'Not In Use' }}</x-ui.badge></td>
                                <td class="actions">
                                    <a href="{{ route('departments.edit', $dept->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                    {{-- <button type="button" onclick="confirmDelete({{ $dept->id }})" class="btn btn-danger-ghost btn-sm">Delete</button> --}}
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="6" class="p-0">
                                    <x-ui.empty-state icon="building" title="No departments yet" description="Add the first department using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.dialog id="deleteConfirmationModal" title="Delete this department?" description="This permanently removes the department. This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete department</button>
        </x-slot:footer>
    </x-ui.dialog>

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
        // If the Department is not active, show the table row as muted
        document.querySelectorAll('#departments-table tbody tr').forEach(row => {
            const statusCell = row.querySelector('td:nth-child(5)');
            if (statusCell && statusCell.textContent.trim() === 'Not In Use') {
                row.classList.add('is-inactive');
            }
        });
    </script>
@endsection
