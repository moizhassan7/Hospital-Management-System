@extends('layouts.app')

@section('title', isset($supplier) ? 'Edit supplier' : 'Suppliers')

@section('content')
    <x-ui.page-header :title="isset($supplier) ? 'Edit supplier' : 'Suppliers'" :subtitle="isset($supplier) ? $supplier->name . ' · ' . $supplier->company_name : 'Vendors the store purchases from.'">
        <x-slot:actions>
            <a href="{{ route('store.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Store
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[380px_minmax(0,1fr)]">
        <x-ui.panel :title="isset($supplier) ? 'Edit ' . $supplier->name : 'Add supplier'" :icon="isset($supplier) ? 'pencil' : 'plus'" class="xl:sticky xl:top-[84px]" flush>
            <form action="{{ isset($supplier) ? route('store.supplier.update', $supplier->id) : route('store.supplier.store') }}" method="POST">
                @csrf
                @isset($supplier)
                    @method('PUT')
                @endisset
                <div class="panel-body space-y-4">
                    <x-ui.field label="Contact person" for="name" error="name" required>
                        <input type="text" id="name" name="name" @class(['input', 'is-invalid' => $errors->has('name')]) placeholder="e.g. Jane Doe" value="{{ old('name', $supplier->name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <x-ui.field label="Company name" for="company_name" error="company_name" required>
                        <input type="text" id="company_name" name="company_name" @class(['input', 'is-invalid' => $errors->has('company_name')]) placeholder="e.g. Pharma Inc." value="{{ old('company_name', $supplier->company_name ?? '') }}" autocomplete="off" required>
                    </x-ui.field>
                    <x-ui.field label="Contact number" for="contact_number" error="contact_number">
                        <input type="tel" id="contact_number" name="contact_number" @class(['input', 'is-invalid' => $errors->has('contact_number')]) placeholder="e.g. 03xx-xxxxxxx" value="{{ old('contact_number', $supplier->contact_number ?? '') }}" autocomplete="off">
                    </x-ui.field>
                    <x-ui.field label="Address" for="address" error="address">
                        <input type="text" id="address" name="address" @class(['input', 'is-invalid' => $errors->has('address')]) placeholder="Street, city" value="{{ old('address', $supplier->address ?? '') }}">
                    </x-ui.field>
                    <x-ui.field label="Reference" for="reference" error="reference" hint="Who referred or approved this supplier.">
                        <input type="text" id="reference" name="reference" @class(['input', 'is-invalid' => $errors->has('reference')]) placeholder="e.g. Dr. Smith" value="{{ old('reference', $supplier->reference ?? '') }}">
                    </x-ui.field>
                    <label class="option-row">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="checkbox" @checked(old('is_active', $supplier->is_active ?? true))>
                        <span>
                            <span class="option-row-title">Active supplier</span>
                            <span class="option-row-desc">Inactive suppliers stay on record for history.</span>
                        </span>
                    </label>
                </div>
                <div class="panel-footer">
                    @isset($supplier)
                        <a href="{{ route('store.supplier') }}" class="btn btn-ghost">Cancel</a>
                    @endisset
                    <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                        <x-ui.icon name="{{ isset($supplier) ? 'save' : 'plus' }}" />{{ isset($supplier) ? 'Update supplier' : 'Add supplier' }}
                    </button>
                </div>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Supplier list" flush>
            <x-slot:actions>
                <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#suppliers-table">{{ $suppliers->count() }}</span> total</span>
            </x-slot:actions>
            <div class="table-toolbar">
                <x-ui.search-input name="" class="toolbar-search" placeholder="Filter by name, company or number" data-table-filter="#suppliers-table" />
            </div>
            <div class="table-wrap">
                <table id="suppliers-table" class="table">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Contact</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th class="actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $s)
                            <tr @class(['bg-brand-50/50' => isset($supplier) && $supplier->id === $s->id])>
                                <td>
                                    <span class="cell-strong">{{ $s->company_name }}</span>
                                    <span class="cell-sub">{{ $s->name }}</span>
                                </td>
                                <td>
                                    <span class="id">{{ $s->contact_number ?: '—' }}</span>
                                    @if ($s->address)
                                        <span class="cell-sub">{{ $s->address }}</span>
                                    @endif
                                </td>
                                <td class="muted">{{ $s->reference ?: '—' }}</td>
                                <td><x-ui.badge :tone="$s->is_active ? 'success' : 'neutral'" dot>{{ $s->is_active ? 'Active' : 'Inactive' }}</x-ui.badge></td>
                                <td class="actions">
                                    <div class="row-actions">
                                        <a href="{{ route('store.supplier.edit', $s->id) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                        <form action="{{ route('store.supplier.destroy', $s->id) }}" method="POST" class="inline"
                                            data-confirm="This permanently removes {{ $s->company_name }} from the supplier list."
                                            data-confirm-title="Delete this supplier?" data-confirm-label="Delete supplier">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-empty-row>
                                <td colspan="5" class="p-0">
                                    <x-ui.empty-state icon="truck" title="No suppliers yet" description="Add the vendors you purchase from using the form." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.panel>
    </div>
@endsection
