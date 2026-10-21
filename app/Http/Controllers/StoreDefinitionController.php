<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\NonConsumableItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Item categories and storage locations shared by consumable and non-consumable items.
 */
class StoreDefinitionController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('consumableItems')->orderBy('name')->get();
        $locations = Location::withCount('consumableItems')->orderBy('name')->get();

        $nonConsumableByCategory = NonConsumableItem::whereNotNull('category_id')
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');
        $nonConsumableByLocation = NonConsumableItem::whereNotNull('location_id')
            ->selectRaw('location_id, count(*) as total')
            ->groupBy('location_id')
            ->pluck('total', 'location_id');

        $category = $request->filled('category') ? Category::find($request->query('category')) : null;
        $location = $request->filled('location') ? Location::find($request->query('location')) : null;

        return view('store.definitions', compact(
            'categories', 'locations', 'nonConsumableByCategory', 'nonConsumableByLocation', 'category', 'location'
        ));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
        ]);

        Category::create(['name' => trim($validated['category_name'])]);

        return redirect()->route('store.definitions')->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $category->update(['name' => trim($validated['category_name'])]);

        return redirect()->route('store.definitions')->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();

        return redirect()->route('store.definitions')->with('success', 'Category deleted.');
    }

    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'location_name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')],
        ]);

        Location::create(['name' => trim($validated['location_name'])]);

        return redirect()->route('store.definitions')->with('success', 'Location added.');
    }

    public function updateLocation(Request $request, Location $location)
    {
        $validated = $request->validate([
            'location_name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($location->id)],
        ]);

        $location->update(['name' => trim($validated['location_name'])]);

        return redirect()->route('store.definitions')->with('success', 'Location updated.');
    }

    public function destroyLocation(Location $location)
    {
        $location->delete();

        return redirect()->route('store.definitions')->with('success', 'Location deleted.');
    }
}
