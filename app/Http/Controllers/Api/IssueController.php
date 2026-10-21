<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsumableItem;
use App\Models\NonConsumableItem;
use App\Models\StockIssue;
use App\Services\StoreStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IssueController extends Controller
{
    /**
     * System users and active doctors who can receive items.
     */
    public function searchEmployees(Request $request)
    {
        return response()->json(StoreStock::searchStaff($request->query('query')));
    }

    /**
     * Items with their current availability.
     */
    public function searchItems(Request $request)
    {
        return response()->json(StoreStock::searchItems($request->query('query')));
    }

    /**
     * Issue items to a person. Consumables cannot exceed the quantity on hand;
     * an asset can be issued only while it is in the store.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|string|max:255|unique:stock_issues,transaction_id',
            'issue_date' => 'required|date',
            'issue_time' => 'required',
            'employee_id' => 'required|string|max:255',
            'employee_name' => 'required|string|max:255',
            'employee_department' => 'nullable|string|max:255',
            'employee_designation' => 'nullable|string|max:255',
            'total_issued_quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:1000',
            'issued_items' => 'required|array|min:1',
            'issued_items.*.id' => ['required', 'string', 'distinct', StoreStock::keyRule()],
            'issued_items.*.issue_qty' => 'required|integer|min:1',
        ], [
            'issued_items.*.id.distinct' => 'Each item can appear on the issue only once.',
        ]);

        $issue = DB::transaction(function () use ($validated) {
            $lines = collect($validated['issued_items'])->map(fn ($line) => $line + ['ref' => StoreStock::parse($line['id'])]);
            $consumableIds = $lines->where('ref.type', StoreStock::CONSUMABLE)->pluck('ref.id');
            $assetIds = $lines->where('ref.type', StoreStock::NON_CONSUMABLE)->pluck('ref.id');

            // Row locks make concurrent issues of the same item wait for each other.
            $consumables = ConsumableItem::whereIn('id', $consumableIds)->lockForUpdate()->get()->keyBy('id');
            $assets = NonConsumableItem::whereIn('id', $assetIds)->lockForUpdate()->get()->keyBy('id');
            $onHand = StoreStock::consumableOnHand($consumableIds);
            $custody = StoreStock::assetCustody($assetIds);

            $errors = [];
            $rows = [];
            foreach ($lines as $index => $line) {
                $ref = $line['ref'];
                $quantity = (int) $line['issue_qty'];

                if ($ref['type'] === StoreStock::CONSUMABLE) {
                    $item = $consumables->get($ref['id']);
                    $available = $onHand[$ref['id']] ?? 0;
                    if (! $item || ! $item->is_active) {
                        $errors["issued_items.$index.id"] = 'One of the items is no longer active in the store.';
                    } elseif ($quantity > $available) {
                        $errors["issued_items.$index.issue_qty"] = "Only {$available} {$item->unit} of {$item->name} in stock.";
                    } else {
                        $rows[] = [$item->name, (string) $item->unit, (float) $item->sale_price, $quantity, $line['id']];
                    }
                    continue;
                }

                $asset = $assets->get($ref['id']);
                if (! $asset || ! $asset->is_active) {
                    $errors["issued_items.$index.id"] = 'One of the assets is no longer active in the store.';
                } elseif ($quantity !== 1) {
                    $errors["issued_items.$index.issue_qty"] = "{$asset->name} ({$asset->asset_tag}) is a single asset; issue quantity must be 1.";
                } elseif (isset($custody[$asset->id])) {
                    $errors["issued_items.$index.id"] = "{$asset->name} ({$asset->asset_tag}) is already issued to {$custody[$asset->id]['employee_name']}.";
                } elseif (in_array($asset->current_status, StoreStock::UNISSUABLE_STATUSES, true)) {
                    $errors["issued_items.$index.id"] = "{$asset->name} ({$asset->asset_tag}) is {$asset->current_status} and cannot be issued.";
                } else {
                    $rows[] = [$asset->name, (string) $asset->unit, (float) $asset->purchase_price, 1, $line['id']];
                }
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            $issue = StockIssue::create([
                'transaction_id' => $validated['transaction_id'],
                'issue_date' => $validated['issue_date'],
                'issue_time' => $validated['issue_time'],
                'employee_id' => $validated['employee_id'],
                'employee_name' => $validated['employee_name'],
                'employee_department' => (string) ($validated['employee_department'] ?? ''),
                'employee_designation' => (string) ($validated['employee_designation'] ?? ''),
                'total_issued_quantity' => collect($rows)->sum(3),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            foreach ($rows as [$name, $unit, $price, $quantity, $key]) {
                $issue->items()->create([
                    'item_id' => $key,
                    'item_name' => $name,
                    'item_unit' => $unit,
                    'item_price' => $price,
                    'issued_quantity' => $quantity,
                    'total_price' => $price * $quantity,
                ]);
            }

            return $issue;
        });

        return response()->json(['message' => 'Stock issued successfully!', 'transaction_id' => $issue->transaction_id], 201);
    }
}
