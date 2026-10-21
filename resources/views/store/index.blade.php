@extends('layouts.app')

@section('title', 'Store')

@section('content')
    <x-ui.page-header title="Store" subtitle="Item master, stock movements and suppliers." />

    <div class="stack">
        <section>
            <h2 class="section-heading">Items</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('store.consumable_items')" icon="package" title="Consumable items" description="Items that are used up, such as medicines and bandages." primary />
                <x-ui.module-link :href="route('store.non_consumable_items')" icon="archive" title="Non-consumable items" description="Reusable assets such as equipment and furniture." />
                <x-ui.module-link :href="route('store.definitions')" icon="layers" title="Categories & locations" description="Item categories and storage locations used on both item types." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Stock movements</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('store.purchase_stock')" icon="shopping-cart" title="Purchase stock" description="Record consumable or non-consumable items received." />
                <x-ui.module-link :href="route('store.issue_stock')" icon="send" title="Issue stock" description="Issue items to departments and staff." />
                <x-ui.module-link :href="route('store.return_stock')" icon="undo" title="Return stock" description="Process returns of non-consumable items." />
            </div>
        </section>

        <section>
            <h2 class="section-heading">Partners</h2>
            <div class="module-grid">
                <x-ui.module-link :href="route('store.supplier')" icon="truck" title="Suppliers" description="Vendors the store purchases from." />
            </div>
        </section>
    </div>
@endsection
