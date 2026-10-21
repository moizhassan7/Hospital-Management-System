@extends('layouts.app')

@section('title', 'Categories & locations')

@section('content')
    <x-ui.page-header title="Categories & locations" subtitle="Item categories and storage locations used by consumable and non-consumable items.">
        <x-slot:actions>
            <a href="{{ route('store.index') }}" class="btn btn-ghost">
                <x-ui.icon name="arrow-left" />Store
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-2">
        @foreach ([
            [
                'key' => 'category',
                'title' => 'Categories',
                'singular' => 'category',
                'icon' => 'layers',
                'items' => $categories,
                'editing' => $category,
                'nonConsumable' => $nonConsumableByCategory,
                'placeholder' => 'e.g. Medicines',
                'storeRoute' => 'store.categories.store',
                'updateRoute' => 'store.categories.update',
                'destroyRoute' => 'store.categories.destroy',
            ],
            [
                'key' => 'location',
                'title' => 'Locations',
                'singular' => 'location',
                'icon' => 'map-pin',
                'items' => $locations,
                'editing' => $location,
                'nonConsumable' => $nonConsumableByLocation,
                'placeholder' => 'e.g. Main store',
                'storeRoute' => 'store.locations.store',
                'updateRoute' => 'store.locations.update',
                'destroyRoute' => 'store.locations.destroy',
            ],
        ] as $set)
            @php
                $field = $set['key'] . '_name';
                $editing = $set['editing'];
                $tableId = $set['key'] . '-table';
            @endphp
            <x-ui.panel :title="$set['title']" :icon="$set['icon']" flush>
                <x-slot:actions>
                    <span class="text-[12.5px] text-fg-muted"><span class="font-semibold text-fg" data-filter-count="#{{ $tableId }}">{{ $set['items']->count() }}</span> total</span>
                </x-slot:actions>

                <form action="{{ $editing ? route($set['updateRoute'], $editing) : route($set['storeRoute']) }}" method="POST" class="border-b border-line bg-subtle px-5 py-4">
                    @csrf
                    @if ($editing)
                        @method('PUT')
                    @endif
                    <x-ui.field :label="$editing ? 'Rename ' . $editing->name : 'New ' . $set['singular']" :for="$field" :error="$field" required>
                        <div class="flex flex-wrap gap-2">
                            <input type="text" id="{{ $field }}" name="{{ $field }}" @class(['input min-w-0 flex-1', 'is-invalid' => $errors->has($field)]) placeholder="{{ $set['placeholder'] }}" value="{{ old($field, $editing->name ?? '') }}" autocomplete="off" maxlength="255" required @if ($editing) autofocus @endif>
                            @if ($editing)
                                <a href="{{ route('store.definitions') }}" class="btn btn-ghost">Cancel</a>
                            @endif
                            <button type="submit" class="btn btn-primary" data-busy-label="Saving…">
                                <x-ui.icon name="{{ $editing ? 'save' : 'plus' }}" />{{ $editing ? 'Update' : 'Add ' . $set['singular'] }}
                            </button>
                        </div>
                    </x-ui.field>
                </form>

                @if ($set['items']->count() > 6)
                    <div class="table-toolbar">
                        <x-ui.search-input name="" class="toolbar-search" placeholder="Filter {{ strtolower($set['title']) }}" data-table-filter="#{{ $tableId }}" />
                    </div>
                @endif

                <div class="table-wrap">
                    <table id="{{ $tableId }}" class="table">
                        <thead>
                            <tr>
                                <th class="num w-12">#</th>
                                <th>Name</th>
                                <th class="num">Items</th>
                                <th class="actions"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($set['items'] as $index => $item)
                                @php
                                    $consumable = (int) $item->consumable_items_count;
                                    $nonConsumable = (int) ($set['nonConsumable'][$item->id] ?? 0);
                                @endphp
                                <tr @class(['bg-brand-50/50' => $editing && $editing->id === $item->id])>
                                    <td class="num muted">{{ $index + 1 }}</td>
                                    <td class="cell-strong">{{ $item->name }}</td>
                                    <td class="num">
                                        {{ $consumable + $nonConsumable }}
                                        <span class="cell-sub">{{ $consumable }} consumable · {{ $nonConsumable }} non-consumable</span>
                                    </td>
                                    <td class="actions">
                                        <div class="row-actions">
                                            <a href="{{ route('store.definitions', [$set['key'] => $item->id]) }}" class="btn btn-ghost btn-sm"><x-ui.icon name="pencil" />Edit</a>
                                            <form action="{{ route($set['destroyRoute'], $item) }}" method="POST" class="inline"
                                                data-confirm="{{ $consumable + $nonConsumable > 0 ? 'The ' . ($consumable + $nonConsumable) . ' item(s) using “' . $item->name . '” stay in the store but will have no ' . $set['singular'] . '.' : '“' . $item->name . '” is not used by any item.' }}"
                                                data-confirm-title="Delete this {{ $set['singular'] }}?" data-confirm-label="Delete {{ $set['singular'] }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger-ghost btn-sm"><x-ui.icon name="trash" />Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="4" class="p-0">
                                        <x-ui.empty-state :icon="$set['icon']" :title="'No ' . strtolower($set['title']) . ' yet'" :description="'Add a ' . $set['singular'] . ' above to use it on store items.'" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>
        @endforeach
    </div>
@endsection
