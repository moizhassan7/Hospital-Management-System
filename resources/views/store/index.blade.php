@extends('layouts.app')

@section('page_title', 'Store')

@section('content')
    @include('partials.page-toolbar', [
        'title' => 'Store Management',
        'subtitle' => 'Inventory, stock, and suppliers',
        'backUrl' => route('dashboard'),
        'backLabel' => 'Back to Dashboard',
    ])

    <div class="hms-hub-list">
        <x-hub-action title="Consumable Items" description="Manage items that are used up (e.g., medicines, bandages)." :href="route('store.consumable_items')" button-text="Manage consumables" icon-color="text-blue-600" button-class="bg-blue-500 hover:bg-blue-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 11h.01M7 15h.01M17 12h.01M17 16h.01M17 20h.01M13 8h.01M13 12h.01M13 16h.01M18 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Non-Consumable Items" description="Manage reusable assets (e.g., equipment, furniture)." :href="route('store.non_consumable_items')" button-text="Manage non-consumables" icon-color="text-green-600" button-class="bg-green-500 hover:bg-green-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2H7a2 2 0 00-2 2v2m7-4h.01M7 16h.01"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Issue Stock" description="Issue consumable or non-consumable items to departments or staff." :href="route('store.issue_stock')" button-text="Issue stock" icon-color="text-purple-600" button-class="bg-purple-500 hover:bg-purple-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v4a1 1 0 001 1h4a1 1 0 001-1V7m0 10v3m-4-3v3m-4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Purchase Stock" description="Purchase consumable or non-consumable items." :href="route('store.purchase_stock')" button-text="Purchase stock" icon-color="text-purple-600" button-class="bg-purple-500 hover:bg-purple-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Suppliers" description="Manage hospital suppliers and vendors." :href="route('store.supplier')" button-text="Manage suppliers" icon-color="text-red-600" button-class="bg-red-500 hover:bg-red-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg></x-slot:icon>
        </x-hub-action>

        <x-hub-action title="Return Stock" description="Process returns of non-consumable items." :href="route('store.return_stock')" button-text="Return stock" icon-color="text-indigo-600" button-class="bg-indigo-500 hover:bg-indigo-600">
            <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 15.5l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg></x-slot:icon>
        </x-hub-action>
    </div>
@endsection
