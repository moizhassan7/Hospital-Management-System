@extends('layouts.app')

@section('title', isset($doctorType) ? 'Edit doctor type' : 'Doctor types')

@section('content')
    <x-ui.page-header :title="isset($doctorType) ? 'Edit doctor type' : 'Doctor types'" subtitle="Categories used when registering doctors, such as Consultant or Resident.">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All setup
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($doctorType) ? 'Edit ' . $doctorType->name : 'Add doctor type'" :description="isset($doctorType) ? 'Changes apply immediately after saving.' : 'For example Consultant, Resident or Intern.'" :icon="isset($doctorType) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($doctorType) ? route('doctor_types.update', $doctorType->id) : route('doctor_types.store') }}" method="POST">
                @csrf
                @isset($doctorType)
                    @method('PUT')
                @endisset
                <div class="panel-body">
                    <x-ui.field label="Doctor type name" for="doctor_type_name" error="doctor_type_name" required>
                        <input type="text" id="doctor_type_name" name="doctor_type_name" @class(['input', 'is-invalid' => $errors->has('doctor_type_name')]) placeholder="e.g. Consultant" value="{{ old('doctor_type_name', $doctorType->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                </div>
                <div class="panel-footer">
                    @isset($doctorType)
                        <a href="{{ route('doctor_types.add') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($doctorType) ? 'save' : 'plus' }}" />{{ isset($doctorType) ? 'Update doctor type' : 'Add doctor type' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing doctor types" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#doctor-types-table">{{ count($doctorTypes) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter doctor types" data-table-filter="#doctor-types-table" />
            </div>
            <div class="table-wrap">
                <table id="doctor-types-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Type ID</th>
                            <th>Doctor type</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($doctorTypes as $index => $type)
                            <tr @class(['bg-brand-50/50' => isset($doctorType) && $doctorType->id === $type->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $type->id }}</span></td>
                                <td class="cell-strong">{{ $type->name }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('doctor_types.edit', $type->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <button type="button" onclick="confirmDelete({{ $type->id }})" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="4" class="p-0">
                                    <x-ui.empty-state icon="contact" title="No doctor types yet" description="Add the first doctor type using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.dialog id="deleteConfirmationModal" title="Delete this doctor type?" description="This permanently deletes the doctor type. This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete doctor type</button>
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
        let doctorTypeIdToDelete = null;

        window.confirmDelete = function(doctorTypeId) { // Make global for onclick
            doctorTypeIdToDelete = doctorTypeId;
            deleteConfirmationModal.classList.remove('hidden');
        }

        cancelDeleteButton.addEventListener('click', () => {
            deleteConfirmationModal.classList.add('hidden');
            doctorTypeIdToDelete = null;
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (doctorTypeIdToDelete !== null) {
                deleteForm.action = `/doctor-types/${doctorTypeIdToDelete}`; // Construct the delete URL
                deleteForm.submit();
            }
            deleteConfirmationModal.classList.add('hidden');
            doctorTypeIdToDelete = null;
        });

        // Close modal if clicked outside (optional)
        deleteConfirmationModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('hidden');
                doctorTypeIdToDelete = null;
            }
        });
    </script>
@endsection
