<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConsumableItem;
use App\Models\NonConsumableItem;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Services\StoreStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    /**
     * Items that can be added to a purchase bill. Assets already recorded on a
     * bill carry that bill's purchase id.
     */
    public function searchItems(Request $request)
    {
        $items = StoreStock::searchItems($request->query('query'));

        $assetKeys = $items->where('type', StoreStock::NON_CONSUMABLE)->pluck('id');
        $purchasedOn = PurchaseBillItem::query()
            ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
            ->whereIn('purchase_bill_items.item_id', $assetKeys)
            ->pluck('purchase_bills.purchase_id', 'purchase_bill_items.item_id');

        return response()->json($items->map(fn ($item) => $item + [
            'purchased_on' => $purchasedOn[$item['id']] ?? null,
        ])->values());
    }

    /**
     * Save a supplier bill. Consumable stock rises by the received quantity
     * (stock is derived from bill lines) and item prices follow the bill.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|string|max:255|unique:purchase_bills,purchase_id',
            'purchase_date' => 'required|date',
            'bill_id' => 'required|string|max:255|unique:purchase_bills,bill_id',
            'bill_date' => 'required|date',
            'supplier_name' => 'required|string|max:255',
            'sub_total' => 'required|numeric',
            'total_tax' => 'required|numeric',
            'total_bill_discount' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'received_payment' => 'required|numeric|min:0',
            'balance' => 'required|numeric',
            'remarks' => 'nullable|string|max:1000',
            'bill_items' => 'required|array|min:1',
            'bill_items.*.id' => ['required', 'string', 'distinct', StoreStock::keyRule()],
            'bill_items.*.name' => 'required|string|max:255',
            'bill_items.*.unit' => 'nullable|string|max:50',
            'bill_items.*.qty' => 'required|integer|min:1',
            'bill_items.*.purchase_price' => 'required|numeric|min:0',
            'bill_items.*.sale_price' => 'required|numeric|min:0',
            'bill_items.*.tax' => 'required|numeric|min:0',
            'bill_items.*.discount_value' => 'required|numeric|min:0',
            'bill_items.*.discount_type' => 'required|string|in:percentage,flat',
            'bill_items.*.total' => 'required|numeric',
        ], [
            'bill_id.unique' => 'This bill number has already been recorded.',
            'bill_items.*.id.distinct' => 'Each item can appear on the bill only once.',
        ]);

        $bill = DB::transaction(function () use ($validated) {
            $lines = collect($validated['bill_items'])->map(fn ($line) => $line + ['ref' => StoreStock::parse($line['id'])]);
            $consumables = ConsumableItem::whereIn('id', $lines->where('ref.type', StoreStock::CONSUMABLE)->pluck('ref.id'))->lockForUpdate()->get()->keyBy('id');
            $assets = NonConsumableItem::whereIn('id', $lines->where('ref.type', StoreStock::NON_CONSUMABLE)->pluck('ref.id'))->lockForUpdate()->get()->keyBy('id');
            $assetBills = PurchaseBillItem::query()
                ->join('purchase_bills', 'purchase_bills.id', '=', 'purchase_bill_items.purchase_bill_id')
                ->whereIn('purchase_bill_items.item_id', $lines->where('ref.type', StoreStock::NON_CONSUMABLE)->pluck('id'))
                ->pluck('purchase_bills.purchase_id', 'purchase_bill_items.item_id');

            $errors = [];
            foreach ($lines as $index => $line) {
                $ref = $line['ref'];
                if ($ref['type'] === StoreStock::CONSUMABLE) {
                    if (! $consumables->has($ref['id'])) {
                        $errors["bill_items.$index.id"] = "{$line['name']} is no longer in the item list.";
                    }
                    continue;
                }

                $asset = $assets->get($ref['id']);
                if (! $asset) {
                    $errors["bill_items.$index.id"] = "{$line['name']} is no longer in the asset list.";
                } elseif ((int) $line['qty'] !== 1) {
                    $errors["bill_items.$index.qty"] = "{$asset->name} ({$asset->asset_tag}) is a single asset; enter quantity 1.";
                } elseif (isset($assetBills[$line['id']])) {
                    $errors["bill_items.$index.id"] = "{$asset->name} ({$asset->asset_tag}) is already recorded on purchase {$assetBills[$line['id']]}.";
                }
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            $bill = PurchaseBill::create(collect($validated)->only([
                'purchase_id', 'purchase_date', 'bill_id', 'bill_date', 'supplier_name', 'sub_total', 'total_tax',
                'total_bill_discount', 'grand_total', 'received_payment', 'balance', 'remarks',
            ])->all());

            foreach ($lines as $line) {
                $bill->items()->create([
                    'item_id' => $line['id'],
                    'item_name' => $line['name'],
                    'item_unit' => (string) ($line['unit'] ?? ''),
                    'quantity' => $line['qty'],
                    'purchase_price' => $line['purchase_price'],
                    'sale_price' => $line['sale_price'],
                    'tax_percentage' => $line['tax'],
                    'discount_value' => $line['discount_value'],
                    'discount_type' => $line['discount_type'],
                    'item_total' => $line['total'],
                ]);

                if ($line['ref']['type'] === StoreStock::CONSUMABLE) {
                    $consumables[$line['ref']['id']]->update([
                        'purchase_price' => $line['purchase_price'],
                        'sale_price' => $line['sale_price'],
                    ]);
                } else {
                    $assets[$line['ref']['id']]->update(['purchase_price' => $line['purchase_price']]);
                }
            }

            return $bill;
        });

        return response()->json(['message' => 'Purchase saved successfully!', 'purchase_id' => $bill->purchase_id], 201);
    }
}
