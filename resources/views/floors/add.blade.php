@extends('layouts.app')

@section('title', isset($floor) ? 'Edit floor' : 'Floors')

@section('content')
    <x-ui.page-header :title="isset($floor) ? 'Edit floor' : 'Floors'" subtitle="Floor levels used to organise rooms and wards.">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All setup
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($floor) ? 'Edit ' . $floor->name : 'Add floor'" :description="isset($floor) ? 'Changes apply immediately after saving.' : 'For example Ground floor or 1st floor.'" :icon="isset($floor) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($floor) ? route('floors.update', $floor->id) : route('floors.store') }}" method="POST">
                @csrf
                @isset($floor)
                    @method('PUT')
                @endisset
                <div class="panel-body">
                    <x-ui.field label="Floor name" for="floor_name" error="floor_name" required>
                        <input type="text" id="floor_name" name="floor_name" @class(['input', 'is-invalid' => $errors->has('floor_name')]) placeholder="e.g. Ground floor" value="{{ old('floor_name', $floor->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                </div>
                <div class="panel-footer">
                    @isset($floor)
                        <a href="{{ route('floors.add') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($floor) ? 'save' : 'plus' }}" />{{ isset($floor) ? 'Update floor' : 'Add floor' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing floors" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#floors-table">{{ count($floors) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter floors" data-table-filter="#floors-table" />
            </div>
            <div class="table-wrap">
                <table id="floors-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Floor ID</th>
                            <th>Floor name</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($floors as $index => $f)
                            <tr @class(['bg-brand-50/50' => isset($floor) && $floor->id === $f->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $f->id }}</span></td>
                                <td class="cell-strong">{{ $f->name }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('floors.edit', $f->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <button type="button" onclick="confirmDelete({{ $f->id }})" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="4" class="p-0">
                                    <x-ui.empty-state icon="layers" title="No floors yet" description="Add the first floor using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.dialog id="deleteConfirmationModal" title="Delete this floor?" description="This permanently deletes the floor. This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete floor</button>
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
        let floorIdToDelete = null;

        function confirmDelete(floorId) {
            floorIdToDelete = floorId;
            deleteConfirmationModal.classList.remove('hidden');
        }

        cancelDeleteButton.addEventListener('click', () => {
            deleteConfirmationModal.classList.add('hidden');
            floorIdToDelete = null;
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (floorIdToDelete !== null) {
                deleteForm.action = `/floors/${floorIdToDelete}`; // Construct the delete URL
                deleteForm.submit();
            }
            deleteConfirmationModal.classList.add('hidden');
            floorIdToDelete = null;
        });

        // Close modal if clicked outside (optional)
        deleteConfirmationModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('hidden');
                floorIdToDelete = null;
            }
        });
    </script>
@endsection
