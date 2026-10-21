@extends('layouts.app')

@section('title', isset($speciality) ? 'Edit speciality' : 'Specialities')

@section('content')
    <x-ui.page-header :title="isset($speciality) ? 'Edit speciality' : 'Specialities'" subtitle="Medical specialities and the department each one belongs to.">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All setup
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($speciality) ? 'Edit ' . $speciality->name : 'Add speciality'" :description="isset($speciality) ? 'Changes apply immediately after saving.' : 'For example Cardiac Surgery under Cardiology.'" :icon="isset($speciality) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($speciality) ? route('specialities.update', $speciality->id) : route('specialities.store') }}" method="POST">
                @csrf
                @isset($speciality)
                    @method('PUT')
                @endisset
                <div class="panel-body space-y-4">
                    <x-ui.field label="Department" for="department_id" error="department_id" required>
                        <select id="department_id" name="department_id" @class(['select', 'is-invalid' => $errors->has('department_id')]) required>
                            <option value="">Select a department</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $speciality->department_id ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Speciality name" for="speciality_name" error="speciality_name" required>
                        <input type="text" id="speciality_name" name="speciality_name" @class(['input', 'is-invalid' => $errors->has('speciality_name')]) placeholder="e.g. Cardiac Surgery" value="{{ old('speciality_name', $speciality->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                </div>
                <div class="panel-footer">
                    @isset($speciality)
                        <a href="{{ route('specialities.add') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($speciality) ? 'save' : 'plus' }}" />{{ isset($speciality) ? 'Update speciality' : 'Add speciality' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing specialities" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#specialities-table">{{ count($specialities) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter by speciality or department" data-table-filter="#specialities-table" />
            </div>
            <div class="table-wrap">
                <table id="specialities-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Speciality ID</th>
                            <th>Speciality</th>
                            <th>Department</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($specialities as $index => $spec)
                            <tr @class(['bg-brand-50/50' => isset($speciality) && $speciality->id === $spec->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $spec->id }}</span></td>
                                <td class="cell-strong">{{ $spec->name }}</td>
                                <td>{{ $spec->department->name ?? 'N/A' }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('specialities.edit', $spec->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <button type="button" onclick="confirmDelete({{ $spec->id }})" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="5" class="p-0">
                                    <x-ui.empty-state icon="award" title="No specialities yet" description="Add the first speciality using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.dialog id="deleteConfirmationModal" title="Delete this speciality?" description="This permanently deletes the speciality. This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete speciality</button>
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
        let specialityIdToDelete = null;

        function confirmDelete(specialityId) {
            specialityIdToDelete = specialityId;
            deleteConfirmationModal.classList.remove('hidden');
        }

        cancelDeleteButton.addEventListener('click', () => {
            deleteConfirmationModal.classList.add('hidden');
            specialityIdToDelete = null;
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (specialityIdToDelete !== null) {
                deleteForm.action = `/specialities/${specialityIdToDelete}`; // Construct the delete URL
                deleteForm.submit();
            }
            deleteConfirmationModal.classList.add('hidden');
            specialityIdToDelete = null;
        });

        // Close modal if clicked outside (optional)
        deleteConfirmationModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('hidden');
                specialityIdToDelete = null;
            }
        });
    </script>
@endsection
