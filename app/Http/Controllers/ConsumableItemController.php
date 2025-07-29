<?php 

namespace App\Http\Controllers; 

use App\Models\ConsumableItem;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;

class ConsumableItemController extends Controller
{
    public function add(ConsumableItem $consumableItem = null)
    {
        $consumableItems = ConsumableItem::with(['category', 'location'])->get(); // Eager load relationships
        $categories = Category::all(); // Fetch all categories for dropdown
        $locations = Location::all(); // Fetch all locations for dropdown

        return view('store.consumable_items', compact('consumableItems', 'consumableItem', 'categories', 'locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:consumable_items,name',
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id', 
            'company_name' => 'required|string|max:255',
            'min_stock_level' => 'required|integer|min:0',
            'location_id' => 'required|exists:locations,id', 
            'barcode' => 'nullable|string|max:255|unique:consumable_items,barcode',
            'is_active' => 'boolean',
        ]);

        $item = new ConsumableItem();
        $item->name = $request->input('name');
        $item->unit = $request->input('unit');
        $item->purchase_price = $request->input('purchase_price');
        $item->sale_price = $request->input('sale_price');
        $item->category_id = $request->input('category_id');
        $item->company_name = $request->input('company_name');
        $item->min_stock_level = $request->input('min_stock_level');
        $item->location_id = $request->input('location_id');
        $item->barcode = $request->input('barcode');
        $item->is_active = $request->has('is_active');
        $item->save();

        return redirect()->route('store.consumable_items')->with('success', 'Consumable item added successfully!');
    }

    public function update(Request $request, ConsumableItem $consumableItem)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:consumable_items,name,' . $consumableItem->id,
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'company_name' => 'required|string|max:255',
            'min_stock_level' => 'required|integer|min:0',
            'location_id' => 'required|exists:locations,id',
            'barcode' => 'nullable|string|max:255|unique:consumable_items,barcode,' . $consumableItem->id,
            'is_active' => 'boolean',
        ]);

        $consumableItem->name = $request->input('name');
        $consumableItem->unit = $request->input('unit');
        $consumableItem->purchase_price = $request->input('purchase_price');
        $consumableItem->sale_price = $request->input('sale_price');
        $consumableItem->category_id = $request->input('category_id');
        $consumableItem->company_name = $request->input('company_name');
        $consumableItem->min_stock_level = $request->input('min_stock_level');
        $consumableItem->location_id = $request->input('location_id');
        $consumableItem->barcode = $request->input('barcode');
        $consumableItem->is_active = $request->has('is_active');
        $consumableItem->save();

        return redirect()->route('store.consumable_items')->with('success', 'Consumable item updated successfully!');
    }

    public function destroy(ConsumableItem $consumableItem)
    {
        $consumableItem->delete();
        return redirect()->route('store.consumable_items')->with('success', 'Consumable item deleted successfully!');
    }
}