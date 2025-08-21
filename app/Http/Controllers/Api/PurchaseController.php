<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseBill;
use App\Models\ConsumableItem; // Assuming you have these models
use App\Models\NonConsumableItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    // --- API for Item Search (Dynamic from DB) ---
    public function searchItems(Request $request)
    {
        $searchTerm = $request->query('query');

        $consumableItems = ConsumableItem::select('id', 'name', 'unit', 'purchase_price', 'sale_price')
            ->where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('barcode', 'like', '%' . $searchTerm . '%');
            })
            ->get()
            ->map(function ($item) {
                $item->type = 'Consumable';
                return $item;
            });

        $nonConsumableItems = NonConsumableItem::select('id', 'name', 'unit', 'purchase_price', 'asset_tag as barcode')
            ->where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('asset_tag', 'like', '%' . $searchTerm . '%');
            })
            ->get()
            ->map(function ($item) {
                $item->type = 'Non-Consumable';
                $item->sale_price = 0; // Or some default
                return $item;
            });

        $combinedItems = $consumableItems->merge($nonConsumableItems);

        return response()->json($combinedItems);
    }

    // --- API for Storing Purchase Transaction ---
    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|string|unique:purchase_bills,purchase_id',
            'purchase_date' => 'required|date',
            'bill_id' => 'required|string|unique:purchase_bills,bill_id',
            'bill_date' => 'required|date',
            'supplier_name' => 'required|string|max:255',
            'sub_total' => 'required|numeric',
            'total_tax' => 'required|numeric',
            'total_bill_discount' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'received_payment' => 'required|numeric',
            'balance' => 'required|numeric',
            'remarks' => 'nullable|string|max:1000',
            'bill_items' => 'required|array|min:1',
            'bill_items.*.id' => 'required|string',
            'bill_items.*.name' => 'required|string',
            'bill_items.*.unit' => 'required|string',
            'bill_items.*.qty' => 'required|integer|min:1',
            'bill_items.*.purchase_price' => 'required|numeric|min:0',
            'bill_items.*.sale_price' => 'required|numeric|min:0',
            'bill_items.*.tax' => 'required|numeric|min:0',
            'bill_items.*.discount_value' => 'required|numeric|min:0',
            'bill_items.*.discount_type' => 'required|string|in:percentage,flat',
            'bill_items.*.total' => 'required|numeric',
        ]);
        
        try {
            DB::beginTransaction();

            $purchaseBill = PurchaseBill::create($request->all());

            foreach ($request->bill_items as $itemData) {
                $purchaseBill->items()->create([
                    'item_id' => $itemData['id'],
                    'item_name' => $itemData['name'],
                    'item_unit' => $itemData['unit'],
                    'quantity' => $itemData['qty'],
                    'purchase_price' => $itemData['purchase_price'],
                    'sale_price' => $itemData['sale_price'],
                    'tax_percentage' => $itemData['tax'],
                    'discount_value' => $itemData['discount_value'],
                    'discount_type' => $itemData['discount_type'],
                    'item_total' => $itemData['total'],
                ]);

                // Update stock logic
                // Check if item is Consumable or Non-Consumable
                if (Str::startsWith($itemData['id'], 'C')) {
                    $consumable = ConsumableItem::find($itemData['id']);
                    if ($consumable) {
                        $consumable->current_stock += $itemData['qty'];
                        // You might also update purchase/sale price here
                        $consumable->purchase_price = $itemData['purchase_price'];
                        $consumable->sale_price = $itemData['sale_price'];
                        $consumable->save();
                    }
                }
                // Non-consumables might not have a quantity, they're added as individual assets
                else if (Str::startsWith($itemData['id'], 'N')) {
                    // For non-consumables, you might create new asset records based on quantity
                    // Or simply update the single asset's price
                }
            }

            DB::commit();

            return response()->json(['message' => 'Purchase saved successfully!', 'purchase_id' => $purchaseBill->purchase_id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to save purchase.', 'error' => $e->getMessage()], 500);
        }
    }
}