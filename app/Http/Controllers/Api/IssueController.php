<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockIssue;
use App\Models\ConsumableItem; // Assuming you might fetch from here
use App\Models\NonConsumableItem; // Assuming you might fetch from here
use Illuminate\Http\Request;
use Illuminate\Support\Str; // For Str::random()

class IssueController extends Controller
{
    // --- API for Employee Search (Static for now, but ready for dynamic) ---
    public function searchEmployees(Request $request)
    {
        $searchTerm = $request->query('query');
        // In a real application, you'd query your 'Employees' table here.
        // For now, using static data.
        $allEmployees = [
            ['id' => 'EMP001', 'name' => 'Dr. Jane Doe', 'department' => 'Cardiology', 'designation' => 'Consultant'],
            ['id' => 'EMP002', 'name' => 'Nurse Emily', 'department' => 'Emergency', 'designation' => 'Staff Nurse'],
            ['id' => 'EMP003', 'name' => 'John Smith', 'department' => 'Pharmacy', 'designation' => 'Pharmacist'],
            ['id' => 'EMP004', 'name' => 'Sarah Brown', 'department' => 'Admin', 'designation' => 'Receptionist'],
            ['id' => 'EMP005', 'name' => 'Lab Tech Alex', 'department' => 'Laboratory', 'designation' => 'Technician'],
        ];

        if ($searchTerm) {
            $filteredEmployees = array_filter($allEmployees, function($emp) use ($searchTerm) {
                return Str::contains(strtolower($emp['id']), strtolower($searchTerm)) ||
                       Str::contains(strtolower($emp['name']), strtolower($searchTerm));
            });
            return response()->json(array_values($filteredEmployees)); // array_values to re-index
        }

        return response()->json($allEmployees);
    }

    // --- API for Item Search (Dynamic from DB) ---
    public function searchItems(Request $request)
    {
        $searchTerm = $request->query('query');

        // Search in Consumable Items
        $consumableItems = ConsumableItem::select('id', 'name', 'unit', 'sale_price as price')
            ->where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('barcode', 'like', '%' . $searchTerm . '%');
            })
            ->get()
            ->map(function ($item) {
                $item->type = 'Consumable';
                $item->available_qty = $item->current_stock; // Assuming current_stock is available_qty
                return $item;
            });

        // Search in Non-Consumable Items
        $nonConsumableItems = NonConsumableItem::select('id', 'name', 'unit', 'purchase_price as price') // Use purchase_price for non-consumables
            ->where('is_active', true)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('barcode', 'like', '%' . $searchTerm . '%')
                      ->orWhere('asset_tag', 'like', '%' . $searchTerm . '%');
            })
            ->get()
            ->map(function ($item) {
                $item->type = 'Non-Consumable';
                $item->available_qty = 1; // Non-consumables are usually 1 unit per asset_tag
                return $item;
            });

        // Combine and return results
        $combinedItems = $consumableItems->merge($nonConsumableItems);

        return response()->json($combinedItems);
    }


    // --- API for Storing Issue Transaction ---
    public function store(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string|unique:stock_issues,transaction_id',
            'issue_date' => 'required|date',
            'issue_time' => 'required',
            'employee_id' => 'required|string|max:255',
            'employee_name' => 'required|string|max:255',
            'employee_department' => 'required|string|max:255',
            'employee_designation' => 'required|string|max:255',
            'total_issued_quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
            'issued_items' => 'required|array|min:1', // Must have at least one item
            'issued_items.*.id' => 'required|string', // Item ID
            'issued_items.*.name' => 'required|string', // Item Name
            'issued_items.*.unit' => 'required|string', // Item Unit
            'issued_items.*.price' => 'required|numeric|min:0', // Price at time of issue
            'issued_items.*.issue_qty' => 'required|integer|min:1', // Quantity issued
            'issued_items.*.total_price' => 'required|numeric|min:0', // Total price for this line
        ]);

        // Create the main stock issue record
        $stockIssue = StockIssue::create([
            'transaction_id' => $request->transaction_id,
            'issue_date' => $request->issue_date,
            'issue_time' => $request->issue_time,
            'employee_id' => $request->employee_id,
            'employee_name' => $request->employee_name,
            'employee_department' => $request->employee_department,
            'employee_designation' => $request->employee_designation,
            'total_issued_quantity' => $request->total_issued_quantity,
            'remarks' => $request->remarks,
        ]);

        // Attach issued items to the stock issue
        foreach ($request->issued_items as $itemData) {
            $stockIssue->items()->create([
                'item_id' => $itemData['id'],
                'item_name' => $itemData['name'],
                'item_unit' => $itemData['unit'],
                'item_price' => $itemData['price'],
                'issued_quantity' => $itemData['issue_qty'],
                'total_price' => $itemData['total_price'],
            ]);

            // --- IMPORTANT: Update actual stock quantity in Consumable/NonConsumable tables ---
            // This logic needs to be refined based on your actual stock management.
            // For example, if item_id starts with 'C', update ConsumableItem; if 'N', update NonConsumableItem.
            // This is a simplified example and might need more robust handling (e.g., checking item type from DB).
            if (Str::startsWith($itemData['id'], 'C')) {
                $consumable = ConsumableItem::find($itemData['id']);
                if ($consumable) {
                    $consumable->current_stock -= $itemData['issue_qty'];
                    $consumable->save();
                }
            }
            // For non-consumables, you might change their status or assign them, not reduce quantity.
            // This part needs careful consideration based on your exact inventory model.
            // Example:
            // else if (Str::startsWith($itemData['id'], 'N')) {
            //     $nonConsumable = NonConsumableItem::find($itemData['id']);
            //     if ($nonConsumable) {
            //         $nonConsumable->current_status = 'Issued'; // Example status change
            //         $nonConsumable->location_id = null; // Example: remove from store location
            //         $nonConsumable->save();
            //     }
            // }
            // --- End Stock Update Logic ---
        }

        return response()->json(['message' => 'Stock issued successfully!', 'transaction_id' => $stockIssue->transaction_id], 201);
    }
}