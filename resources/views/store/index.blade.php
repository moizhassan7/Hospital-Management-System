@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Store Management</h2>
        <a href="{{ route('dashboard') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center">
            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Consumable Items Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-blue-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 11h.01M7 15h.01M17 12h.01M17 16h.01M17 20h.01M13 8h.01M13 12h.01M13 16h.01M18 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2z"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Consumable Items</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Manage items that are used up (e.g., medicines, bandages).</p>
            <a href="{{ route('store.consumable_items') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Manage Consumables</a>
        </div>

        <!-- Non-Consumable Items Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-green-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2H7a2 2 0 00-2 2v2m7-4h.01M7 16h.01"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Non-Consumable Items</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Manage items that are reusable assets (e.g., equipment, furniture).</p>
            <a href="{{ route('store.non_consumable_items') }}" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Manage Non-Consumables</a>
        </div>

        <!-- Issue Stock Card (New) -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-purple-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v4a1 1 0 001 1h4a1 1 0 001-1V7m0 10v3m-4-3v3m-4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Issue Stock</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Issue consumable or non-consumable items to departments/staff.</p>
            <a href="{{ route('store.issue_stock') }}" class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Issue Stock</a>
        </div>
  <!-- Purchase Stock Card (New) -->
        <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
            <div class="text-purple-600 mb-4">
                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v4a1 1 0 001 1h4a1 1 0 001-1V7m0 10v3m-4-3v3m-4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Purchase Stock</h3>
            <p class="text-gray-600 text-center mb-4 text-sm">Purchase consumable or non-consumable items.</p>
            <a href="{{ route('store.purchase_stock') }}" class="bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Purchase Stock</a>
        </div>
<div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
    <div class="text-red-600 mb-4">
        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
    </div>
    <h3 class="text-xl font-semibold text-gray-800 mb-2">Suppliers</h3>
    <p class="text-gray-600 text-center mb-4 text-sm">Manage hospital's suppliers and vendors.</p>
    <a href="{{ route('store.supplier') }}" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Manage Suppliers</a>
</div>
<div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center justify-center transition-transform transform hover:scale-105 hover:shadow-2xl duration-300">
    <div class="text-indigo-600 mb-4">
        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 15.5l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
    </div>
    <h3 class="text-xl font-semibold text-gray-800 mb-2">Return Stock</h3>
    <p class="text-gray-600 text-center mb-4 text-sm">Process returns of non-consumable items.</p>
    <a href="{{ route('store.return_stock') }}" class="bg-indigo-500 hover:bg-indigo-600 text-white font-medium py-2 px-5 rounded-full shadow-md transition-colors duration-200 ease-in-out">Return Stock</a>
</div>
    </div>
@endsection
