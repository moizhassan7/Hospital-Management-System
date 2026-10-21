<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NonConsumableItem;
use App\Models\StockReturn;
use App\Services\StoreStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnController extends Controller
{
    /**
     * Assets currently held by a person (issued and not yet returned).
     */
    public function searchIssuedItems(Request $request)
    {
        $employeeId = trim((string) $request->query('employee_id'));
        if ($employeeId === '') {
            return response()->json([]);
        }

        $custody = StoreStock::assetCustody(null, $employeeId);
        $assets = NonConsumableItem::whereIn('id', $custody->keys())->get()->keyBy('id');

        return response()->json($custody->map(fn ($held, $assetId) => [
            'item_id' => StoreStock::key(StoreStock::NON_CONSUMABLE, $assetId),
            'item_name' => $assets[$assetId]->name ?? $held['item_name'],
            'asset_tag' => $assets[$assetId]->asset_tag ?? null,
            'item_type' => StoreStock::NON_CONSUMABLE,
            'quantity' => $held['outstanding'],
            'issued_on' => $held['issued_on'],
        ])->values());
    }

    /**
     * Receive an asset back from the person it was issued to.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_id' => 'required|string|max:255|unique:stock_returns,return_id',
            'return_date' => 'required|date',
            'employee_id' => 'required|string|max:255',
            'employee_name' => 'required|string|max:255',
            'item_id' => ['required', 'string', StoreStock::keyRule()],
            'item_name' => 'required|string|max:255',
            'item_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        $ref = StoreStock::parse($validated['item_id']);
        if ($ref['type'] !== StoreStock::NON_CONSUMABLE) {
            throw ValidationException::withMessages(['item_id' => 'Only assets (non-consumable items) can be returned.']);
        }

        DB::transaction(function () use ($validated, $ref) {
            $asset = NonConsumableItem::whereKey($ref['id'])->lockForUpdate()->first();
            $held = StoreStock::assetCustody([$ref['id']], $validated['employee_id'])->get($ref['id']);

            if (! $asset || ! $held) {
                throw ValidationException::withMessages(['item_id' => "{$validated['item_name']} is not currently issued to {$validated['employee_name']}."]);
            }
            if ((int) $validated['quantity'] > $held['outstanding']) {
                throw ValidationException::withMessages(['quantity' => "Only {$held['outstanding']} of {$asset->name} is outstanding."]);
            }

            StockReturn::create([
                'return_id' => $validated['return_id'],
                'return_date' => $validated['return_date'],
                'employee_id' => $validated['employee_id'],
                'employee_name' => $validated['employee_name'],
                'item_id' => $validated['item_id'],
                'item_name' => $asset->name,
                'item_type' => StoreStock::NON_CONSUMABLE,
                'quantity' => (int) $validated['quantity'],
                'remarks' => $validated['remarks'] ?? null,
            ]);
        });

        return response()->json(['message' => 'Stock returned successfully!', 'return_id' => $validated['return_id']], 201);
    }
}
