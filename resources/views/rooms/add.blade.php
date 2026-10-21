@extends('layouts.app')

@section('title', isset($room) ? 'Edit room' : 'Rooms & wards')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header :title="isset($room) ? 'Edit room' : 'Rooms & wards'" subtitle="Rooms and wards by floor, with bed counts and daily rent.">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All setup
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[380px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($room) ? 'Edit ' . $room->name : 'Add room or ward'" :description="isset($room) ? 'Changes apply immediately after saving.' : 'Private rooms and shared wards are both added here.'" :icon="isset($room) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($room) ? route('rooms.update', $room->id) : route('rooms.store') }}" method="POST">
                @csrf
                @isset($room)
                    @method('PUT')
                @endisset
                <div class="panel-body space-y-4">
                    <x-ui.field label="Floor" for="floor_id" error="floor_id" required>
                        <select id="floor_id" name="floor_id" @class(['select', 'is-invalid' => $errors->has('floor_id')]) required>
                            <option value="">Select a floor</option>
                            @foreach ($floors as $floor)
                                <option value="{{ $floor->id }}" {{ old('floor_id', $room->floor_id ?? '') == $floor->id ? 'selected' : '' }}>{{ $floor->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Room name" for="room_name" error="room_name" required>
                        <input type="text" id="room_name" name="room_name" @class(['input', 'is-invalid' => $errors->has('room_name')]) placeholder="e.g. Room 101 or Ward A" value="{{ old('room_name', $room->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <x-ui.field label="Per day rent" for="per_day_rent" error="per_day_rent">
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="per_day_rent" name="per_day_rent" @class(['input input-money', 'is-invalid' => $errors->has('per_day_rent')]) placeholder="500.00" min="0" step="0.01" value="{{ old('per_day_rent', $room->per_day_rent ?? '') }}">
                        </div>
                    </x-ui.field>

                    <label for="is_ward" class="option-row">
                        <span>
                            <span class="option-row-title block">This room is a ward</span>
                            <span class="option-row-desc block">Wards hold several beds. Bed count and rent become required.</span>
                        </span>
                        <input type="checkbox" name="is_ward" id="is_ward" value="1" class="switch" {{ old('is_ward', $room->is_ward ?? false) ? 'checked' : '' }}>
                    </label>

                    <div id="ward_details" class="{{ old('is_ward', $room->is_ward ?? false) ? '' : 'hidden' }}">
                        <x-ui.field label="Number of beds" for="number_of_beds" error="number_of_beds">
                            <input type="number" id="number_of_beds" name="number_of_beds" @class(['input input-num w-36', 'is-invalid' => $errors->has('number_of_beds')]) placeholder="10" min="0" value="{{ old('number_of_beds', $room->number_of_beds ?? '') }}">
                        </x-ui.field>
                    </div>
                </div>
                <div class="panel-footer">
                    @isset($room)
                        <a href="{{ route('rooms.add') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($room) ? 'save' : 'plus' }}" />{{ isset($room) ? 'Update room' : 'Add room' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing rooms and wards" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#rooms-table">{{ count($rooms) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter by room or floor" data-table-filter="#rooms-table" />
            </div>
            <div class="table-wrap">
                <table id="rooms-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Room ID</th>
                            <th>Room name</th>
                            <th>Floor</th>
                            <th>Type</th>
                            <th class="num">Beds</th>
                            <th class="num">Per day rent</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rooms as $index => $r)
                            <tr @class(['bg-brand-50/50' => isset($room) && $room->id === $r->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $r->id }}</span></td>
                                <td class="cell-strong">{{ $r->name }}</td>
                                <td>{{ $r->floor->name ?? 'N/A' }}</td>
                                <td><x-ui.badge :tone="$r->is_ward ? 'brand' : 'neutral'">{{ $r->is_ward ? 'Ward' : 'Room' }}</x-ui.badge></td>
                                <td class="num">{{ $r->number_of_beds ?? 'N/A' }}</td>
                                <td class="num">{{ $r->per_day_rent ? $currency . ' ' . number_format($r->per_day_rent, 2) : 'N/A' }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('rooms.edit', $r->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <button type="button" onclick="confirmDelete({{ $r->id }})" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="8" class="p-0">
                                    <x-ui.empty-state icon="door" title="No rooms yet" description="Add the first room or ward using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.dialog id="deleteConfirmationModal" title="Delete this room?" description="This permanently deletes the room. This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete room</button>
        </x-slot:footer>
    </x-ui.dialog>

    <!-- Hidden form for DELETE request -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        // JavaScript for conditional ward details and custom delete confirmation modal
        document.addEventListener('DOMContentLoaded', function () {
            const isWardCheckbox = document.getElementById('is_ward');
            const wardDetailsDiv = document.getElementById('ward_details');
            const numberOfBedsInput = document.getElementById('number_of_beds');
            const perDayRentInput = document.getElementById('per_day_rent');

            function toggleWardDetails() {
                if (isWardCheckbox.checked) {
                    wardDetailsDiv.classList.remove('hidden');
                    // Add 'required' attribute if not already present and checkbox is checked
                    if (!numberOfBedsInput.hasAttribute('required')) {
                        numberOfBedsInput.setAttribute('required', 'required');
                    }
                    if (!perDayRentInput.hasAttribute('required')) {
                        perDayRentInput.setAttribute('required', 'required');
                    }
                } else {
                    wardDetailsDiv.classList.add('hidden');
                    numberOfBedsInput.removeAttribute('required');
                    perDayRentInput.removeAttribute('required');
                    // Optionally clear values when hidden, but keep old() value if present for validation errors
                    if (!"{{ old('number_of_beds') }}" && !"{{ isset($room) && $room->number_of_beds }}") {
                        numberOfBedsInput.value = '';
                    }
                    if (!"{{ old('per_day_rent') }}" && !"{{ isset($room) && $room->per_day_rent }}") {
                        perDayRentInput.value = '';
                    }
                }
            }

            // Initial check on page load, considering old input and existing data for edit mode
            toggleWardDetails();

            // Listen for changes on the checkbox
            isWardCheckbox.addEventListener('change', toggleWardDetails);

            // JavaScript for custom delete confirmation modal
            const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
            const cancelDeleteButton = document.getElementById('cancelDeleteButton');
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            const deleteForm = document.getElementById('deleteForm');
            let roomIdToDelete = null;

            window.confirmDelete = function(roomId) { // Make global for onclick
                roomIdToDelete = roomId;
                deleteConfirmationModal.classList.remove('hidden');
            }

            cancelDeleteButton.addEventListener('click', () => {
                deleteConfirmationModal.classList.add('hidden');
                roomIdToDelete = null;
            });

            confirmDeleteButton.addEventListener('click', () => {
                if (roomIdToDelete !== null) {
                    deleteForm.action = `/rooms/${roomIdToDelete}`; // Construct the delete URL
                    deleteForm.submit();
                }
                deleteConfirmationModal.classList.add('hidden');
                roomIdToDelete = null;
            });

            // Close modal if clicked outside (optional)
            deleteConfirmationModal.addEventListener('click', (event) => {
                if (event.target === deleteConfirmationModal) {
                    deleteConfirmationModal.classList.add('hidden');
                    roomIdToDelete = null;
                }
            });
        });
    </script>
@endsection
