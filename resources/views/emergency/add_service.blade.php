@extends('layouts.app')

@section('title', isset($emergencyService) ? 'Edit emergency service' : 'Emergency services')

@section('content')
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp

    <x-ui.page-header :title="isset($emergencyService) ? 'Edit emergency service' : 'Emergency services'" subtitle="Billable services offered in the emergency department.">
        <x-slot:actions>
            <a href="{{ route('emergency.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Emergency
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($emergencyService) ? 'Edit ' . $emergencyService->name : 'Add service'" :description="isset($emergencyService) ? 'Changes apply immediately after saving.' : 'For example an ambulance fee.'" :icon="isset($emergencyService) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($emergencyService) ? route('emergency.update_service', $emergencyService->id) : route('emergency.store_service') }}" method="POST">
                @csrf
                @isset($emergencyService)
                    @method('PUT')
                @endisset
                <div class="panel-body space-y-4">
                    <x-ui.field label="Service name" for="service_name" error="service_name" required>
                        <input type="text" id="service_name" name="service_name" class="input" placeholder="e.g. Ambulance fee" value="{{ old('service_name', $emergencyService->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <x-ui.field label="Service fee" for="service_fee" error="service_fee" required>
                        <div class="input-group has-start">
                            <span class="input-affix is-start">{{ $currency }}</span>
                            <input type="number" id="service_fee" name="service_fee" class="input input-money" placeholder="500.00" value="{{ old('service_fee', $emergencyService->fee ?? '0.00') }}" min="0" step="0.01" required>
                        </div>
                    </x-ui.field>
                </div>
                <div class="panel-footer">
                    @isset($emergencyService)
                        <a href="{{ route('emergency.add_service') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($emergencyService) ? 'save' : 'plus' }}" />{{ isset($emergencyService) ? 'Update service' : 'Save service' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Existing services" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#services-table">{{ count($emergencyServices) }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter services" data-table-filter="#services-table" />
            </div>
            <div class="table-wrap">
                <table id="services-table" class="table">
                    <thead>
                        <tr>
                            <th class="num w-12">#</th>
                            <th>ID</th>
                            <th>Service</th>
                            <th class="num">Fee</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($emergencyServices as $index => $service)
                            <tr @class(['bg-brand-50/50' => isset($emergencyService) && $emergencyService->id === $service->id])>
                                <td class="num muted">{{ $index + 1 }}</td>
                                <td><span class="id">{{ $service->id }}</span></td>
                                <td class="cell-strong">{{ $service->name }}</td>
                                <td class="num">{{ $currency }} {{ number_format($service->fee, 2) }}</td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('emergency.edit_service', $service->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <button type="button" onclick="confirmDelete({{ $service->id }})" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="5" class="p-0">
                                    <x-ui.empty-state icon="siren" title="No emergency services yet" description="Add the first service using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>

    <x-ui.dialog id="deleteConfirmationModal" title="Delete this service?" description="This permanently deletes the emergency service. This action cannot be undone." size="sm" icon="alert-triangle" tone="danger">
        <x-slot:footer>
            <button type="button" id="cancelDeleteButton" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" id="confirmDeleteButton" class="btn btn-danger">Delete service</button>
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
        let serviceIdToDelete = null;

        function confirmDelete(serviceId) {
            serviceIdToDelete = serviceId;
            deleteConfirmationModal.classList.remove('hidden');
        }

        cancelDeleteButton.addEventListener('click', () => {
            deleteConfirmationModal.classList.add('hidden');
            serviceIdToDelete = null;
        });

        confirmDeleteButton.addEventListener('click', () => {
            if (serviceIdToDelete !== null) {
                deleteForm.action = `/emergency/${serviceIdToDelete}`;
                deleteForm.submit();
            }
            deleteConfirmationModal.classList.add('hidden');
            serviceIdToDelete = null;
        });

        deleteConfirmationModal.addEventListener('click', (event) => {
            if (event.target === deleteConfirmationModal) {
                deleteConfirmationModal.classList.add('hidden');
                serviceIdToDelete = null;
            }
        });
    </script>
@endsection
