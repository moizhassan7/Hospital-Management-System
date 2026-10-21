@extends('layouts.app')

@section('title', isset($emergencyCharge) ? 'Edit emergency charges' : 'Emergency charges')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header :title="isset($emergencyCharge) ? 'Edit emergency charges' : 'Emergency charges'" subtitle="Hourly charges applied to patients in the emergency department.">
        <x-slot:actions>
            <a href="{{ route('departments.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />All setup
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($emergencyCharge) ? 'Edit ' . $emergencyCharge->charge_id : 'Add charges'" :description="isset($emergencyCharge) ? 'Changes apply immediately after saving.' : 'Charges for the first hour and for the hours after it.'" :icon="isset($emergencyCharge) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($emergencyCharge) ? route('emergency_charges.update', $emergencyCharge->id) : route('emergency_charges.store') }}" method="POST">
                @csrf
                @isset($emergencyCharge)
                    @method('PUT')
                @endisset
                <div class="panel-body space-y-4">
                    <x-ui.field label="First hour charges" for="first_hour_charges" error="first_hour_charges" required>
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="first_hour_charges" name="first_hour_charges" class="input input-money" placeholder="100.00" value="{{ old('first_hour_charges', $emergencyCharge->first_hour_charges ?? '0.00') }}" min="0" step="0.01" required>
                        </div>
                    </x-ui.field>
                    <x-ui.field label="Other hours charges" for="other_hours_charges" error="other_hours_charges" hint="Charged for each hour after the first." required>
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="other_hours_charges" name="other_hours_charges" class="input input-money" placeholder="50.00" value="{{ old('other_hours_charges', $emergencyCharge->other_hours_charges ?? '0.00') }}" min="0" step="0.01" required>
                        </div>
                    </x-ui.field>
                </div>
                <div class="panel-footer">
                    @isset($emergencyCharge)
                        <a href="{{ route('emergency_charges.add') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($emergencyCharge) ? 'save' : 'plus' }}" />{{ isset($emergencyCharge) ? 'Update charges' : 'Save charges' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing emergency charges" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg">{{ count($emergencyCharges) }}</span> total</span>
            </x-slot:actions>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>Charge ID</th>
                            <th class="num">First hour</th>
                            <th class="num">Other hours</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($emergencyCharges as $index => $charge)
                            <tr @class(['bg-brand-50/50' => isset($emergencyCharge) && $emergencyCharge->id === $charge->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $charge->charge_id }}</span></td>
                                <td class="num">{{ $currency }} {{ number_format($charge->first_hour_charges, 2) }}</td>
                                <td class="num">{{ $currency }} {{ number_format($charge->other_hours_charges, 2) }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('emergency_charges.edit', $charge->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <button type="button" onclick="confirmDelete({{ $charge->id }})" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    <x-ui.empty-state icon="banknote" title="No emergency charges yet" description="Add the hourly charges using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.dialog id="deleteConfirmationModal" title="Delete this charge record?" description="This permanently deletes the emergency charge record. This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete charges</button>
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
        let chargeIdToDelete = null;

        function confirmDelete(chargeId) {
            chargeIdToDelete = chargeId;
            deleteConfirmationModal.classList.remove('hidden');
        }

        cancelDeleteButton.addEventListener('click', () => {
            deleteConfirmationModal.classList.add('hidden');
            chargeIdToDelete = null;
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (chargeIdToDelete !== null) {
                deleteForm.action = `/emergency-charges/${chargeIdToDelete}`;
                deleteForm.submit();
            }
            deleteConfirmationModal.classList.add('hidden');
            chargeIdToDelete = null;
        });

        deleteConfirmationModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('hidden');
                chargeIdToDelete = null;
            }
        });
    </script>
@endsection
