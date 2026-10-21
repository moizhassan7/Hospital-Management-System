@extends('layouts.app')

@section('title', 'All doctors')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header title="All doctors" subtitle="Every registered doctor with their department, speciality and status.">
        <x-slot:actions>
            <a href="{{ route('doctors.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Doctors
            </a>
            <a href="{{ route('doctors.create') }}" class="btn btn-primary">
                <x-ui.icon name="user-plus" />Add doctor
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.panel flush>
        <form action="{{ route('doctors.all') }}" method="GET" class="table-toolbar">
            <x-ui.search-input name="search" :value="request('search')" class="toolbar-search" placeholder="Search by name or code" />
            <button type="submit" class="btn btn-secondary">Search</button>
            @if (request()->filled('search'))
                <a href="{{ route('doctors.all') }}" class="btn btn-ghost">Clear</a>
            @endif
            <span class="toolbar-spacer"></span>
            <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg">{{ $doctors->total() }}</span> doctors</span>
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="num w-12">#</th>
                        <th>Doctor</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Department</th>
                        <th>Speciality</th>
                        <th>Mobile no.</th>
                        <th class="num">Fee</th>
                        <th>Status</th>
                        <th class="actions"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($doctors as $index => $doctor)
                        @php
                            $picture = $doctor->picture
                                ? (\Illuminate\Support\Str::startsWith($doctor->picture, ['http', 'storage/', '/storage/']) ? asset(ltrim($doctor->picture, '/')) : asset('storage/' . $doctor->picture))
                                : null;
                            $initials = collect(preg_split('/\s+/', trim(preg_replace('/^dr\.?\s*/i', '', $doctor->name))))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                        @endphp
                        <tr @class(['is-inactive' => ! $doctor->is_active])>
                            <td class="num muted">{{ $doctors->firstItem() + $index }}</td>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="relative inline-grid shrink-0">
                                        <span class="avatar avatar-sm bg-brand-50 text-[11px] text-brand-800" aria-hidden="true">{{ $initials }}</span>
                                        @if ($picture)
                                            <img class="avatar-sm absolute inset-0" src="{{ $picture }}" alt="" onerror="this.remove()">
                                        @endif
                                    </span>
                                    <span class="cell-strong">{{ $doctor->name }}</span>
                                </div>
                            </td>
                            <td><span class="id">{{ $doctor->code }}</span></td>
                            <td>{{ $doctor->type }}</td>
                            <td>{{ $doctor->department->name ?? 'N/A' }}</td>
                            <td>{{ $doctor->speciality->name ?? 'N/A' }}</td>
                            <td class="nowrap">{{ $doctor->mobile_number ?? 'N/A' }}</td>
                            <td class="num">{{ $currency }} {{ number_format($doctor->fee, 2) }}</td>
                            <td><x-ui.badge :tone="$doctor->is_active ? 'success' : 'neutral'" dot>{{ $doctor->is_active ? 'Active' : 'Inactive' }}</x-ui.badge></td>
                            <td class="actions">
                                <div class="row-actions">
                                    <a href="{{ route('doctors.edit', $doctor->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                    <button type="button" onclick="confirmDelete({{ $doctor->id }})" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="p-0">
                                @if (request()->filled('search'))
                                    <x-ui.empty-state icon="search" title="No doctors match “{{ request('search') }}”" description="Try a different name or doctor code." />
                                @else
                                    <x-ui.empty-state icon="stethoscope" title="No doctors yet" description="Doctors you add will appear here.">
                                        <x-slot:actions>
                                            <a href="{{ route('doctors.create') }}" class="btn btn-primary btn-sm"><x-ui.icon name="user-plus" />Add doctor</a>
                                        </x-slot:actions>
                                    </x-ui.empty-state>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($doctors->hasPages())
            <div class="table-footer">
                {{ $doctors->links() }}
            </div>
        @endif
    </x-ui.panel>

    <x-ui.dialog id="deleteConfirmationModal" title="Delete this doctor?" description="This permanently deletes the doctor's record. This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete doctor</button>
        </x-slot:footer>
    </x-ui.dialog>

    <!-- Hidden form for DELETE request -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        const deleteConfirmationModal = document.getElementById('deleteConfirmationModal');
        const cancelDeleteButton = document.getElementById('cancelDeleteButton');
        const confirmDeleteButton = document.getElementById('confirmDeleteButton');
        const deleteForm = document.getElementById('deleteForm');
        let doctorIdToDelete = null;

        function confirmDelete(doctorId) {
            doctorIdToDelete = doctorId;
            deleteConfirmationModal.classList.remove('hidden');
        }

        cancelDeleteButton.addEventListener('click', () => {
            deleteConfirmationModal.classList.add('hidden');
            doctorIdToDelete = null;
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (doctorIdToDelete !== null) {
                deleteForm.action = `/doctors/${doctorIdToDelete}`;
                deleteForm.submit();
            }
            deleteConfirmationModal.classList.add('hidden');
            doctorIdToDelete = null;
        });

        deleteConfirmationModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('hidden');
                doctorIdToDelete = null;
            }
        });
    </script>
@endsection
