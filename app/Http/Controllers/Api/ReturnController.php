<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockReturn;
use App\Models\StockIssue;
use App\Models\NonConsumableItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    // --- API for searching issued non-consumable items by employee ---
    public function searchIssuedItems(Request $request)
    {
        $employeeId = $request->query('employee_id');

        if (!$employeeId) {
            return response()->json([]);
        }

        // In a real system, you would query the stock_issue_items table
        // to find all issued non-consumable items for this employee that haven't been returned yet.
        // For now, let's use static data to demonstrate the flow.
        $staticIssuedItems = [
            'EMP001' => [
                ['item_id' => 'N001', 'item_name' => 'Hospital Bed', 'item_type' => 'Non-Consumable', 'quantity' => 1, 'issued_on' => '2025-07-20'],
                ['item_id' => 'N002', 'item_name' => 'ECG Machine', 'item_type' => 'Non-Consumable', 'quantity' => 1, 'issued_on' => '2025-07-22'],
            ],
            'EMP002' => [
                ['item_id' => 'N003', 'item_name' => 'Wheelchair', 'item_type' => 'Non-Consumable', 'quantity' => 1, 'issued_on' => '2025-07-21'],
            ],
        ];

        return response()->json($staticIssuedItems[$employeeId] ?? []);
    }

    // --- API for storing the return transaction ---
    public function store(Request $request)
    {
        $request->validate([
            'return_id' => 'required|string|unique:stock_returns,return_id',
            'return_date' => 'required|date',
            'employee_id' => 'required|string',
            'employee_name' => 'required|string',
            'item_id' => 'required|string',
            'item_name' => 'required|string',
            'item_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            StockReturn::create($request->all());

            // --- IMPORTANT: Update stock status in the main table ---
            // In a real application, you'd find the item by its ID and update its status.
            // Example:
            // if ($request->item_type == 'Non-Consumable') {
            //     $item = NonConsumableItem::where('asset_tag', $request->item_id)->first();
            //     if ($item) {
            //         $item->current_status = 'Operational'; // Set status back to available
            //         $item->save();
            //     }
            // }

            DB::commit();

            return response()->json(['message' => 'Stock returned successfully!', 'return_id' => $request->return_id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to process return.', 'error' => $e->getMessage()], 500);
        }
    }
}