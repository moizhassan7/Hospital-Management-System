<?php

namespace App\Http\Controllers;

use App\Models\NonConsumableItem; // Import NonConsumableItem model
use App\Models\Category; // Import Category model
use App\Models\Location; // Import Location model
use Illuminate\Http\Request;

class NonConsumableItemController extends Controller
{
    /**
     * Display the form to add a new non-consumable item and a list of existing items.
     * Also handles displaying an item for editing.
     */
    public function add(NonConsumableItem $nonConsumableItem = null)
    {
        $nonConsumableItems = NonConsumableItem::with(['category', 'location'])->get(); // Eager load relationships
        $categories = Category::all(); // Fetch all categories for dropdown
        $locations = Location::all(); // Fetch all locations for dropdown

        return view('store.non_consumable_items', compact('nonConsumableItems', 'nonConsumableItem', 'categories', 'locations'));
    }

    /**
     * Store a newly created non-consumable item in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|max:255|unique:non_consumable_items,asset_tag',
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'company_name' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
            'depreciation_rate' => 'required|numeric|min:0|max:100',
            'current_status' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'barcode' => 'nullable|string|max:255|unique:non_consumable_items,barcode',
            'is_active' => 'boolean',
        ]);

        $item = new NonConsumableItem();
        $item->name = $request->input('name');
        $item->asset_tag = $request->input('asset_tag');
        $item->unit = $request->input('unit');
        $item->purchase_price = $request->input('purchase_price');
        $item->category_id = $request->input('category_id');
        $item->company_name = $request->input('company_name');
        $item->purchase_date = $request->input('purchase_date');
        $item->warranty_expiry_date = $request->input('warranty_expiry_date');
        $item->depreciation_rate = $request->input('depreciation_rate');
        $item->current_status = $request->input('current_status');
        $item->location_id = $request->input('location_id');
        $item->barcode = $request->input('barcode');
        $item->is_active = $request->has('is_active');
        $item->save();

        return redirect()->route('store.non_consumable_items')->with('success', 'Non-consumable item added successfully!');
    }

    /**
     * Update the specified non-consumable item in storage.
     */
    public function update(Request $request, NonConsumableItem $nonConsumableItem)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'asset_tag' => 'required|string|max:255|unique:non_consumable_items,asset_tag,' . $nonConsumableItem->id,
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'company_name' => 'nullable|string|max:255',
            'purchase_date' => 'required|date',
            'warranty_expiry_date' => 'nullable|date|after_or_equal:purchase_date',
            'depreciation_rate' => 'required|numeric|min:0|max:100',
            'current_status' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'barcode' => 'nullable|string|max:255|unique:non_consumable_items,barcode,' . $nonConsumableItem->id,
            'is_active' => 'boolean',
        ]);

        $nonConsumableItem->name = $request->input('name');
        $nonConsumableItem->asset_tag = $request->input('asset_tag');
        $nonConsumableItem->unit = $request->input('unit');
        $nonConsumableItem->purchase_price = $request->input('purchase_price');
        $nonConsumableItem->category_id = $request->input('category_id');
        $nonConsumableItem->company_name = $request->input('company_name');
        $nonConsumableItem->purchase_date = $request->input('purchase_date');
        $nonConsumableItem->warranty_expiry_date = $request->input('warranty_expiry_date');
        $nonConsumableItem->depreciation_rate = $request->input('depreciation_rate');
        $nonConsumableItem->current_status = $request->input('current_status');
        $nonConsumableItem->location_id = $request->input('location_id');
        $nonConsumableItem->barcode = $request->input('barcode');
        $nonConsumableItem->is_active = $request->has('is_active');
        $nonConsumableItem->save();

        return redirect()->route('store.non_consumable_items')->with('success', 'Non-consumable item updated successfully!');
    }

    /**
     * Remove the specified non-consumable item from storage.
     */
    public function destroy(NonConsumableItem $nonConsumableItem)
    {
        $nonConsumableItem->delete();
        return redirect()->route('store.non_consumable_items')->with('success', 'Non-consumable item deleted successfully!');
    }
}