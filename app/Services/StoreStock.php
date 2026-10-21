<?php

namespace App\Services;

use App\Models\ConsumableItem;
use App\Models\Doctor;
use App\Models\NonConsumableItem;
use App\Models\PurchaseBillItem;
use App\Models\StockIssueItem;
use App\Models\StockReturn;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Store stock is derived from movements instead of being stored on items:
 * a consumable's on-hand quantity is purchased − issued + returned, and an
 * asset (non-consumable) is out of the store while its issues to a person
 * outnumber the returns from that person.
 *
 * Movement rows reference items by a typed key ("C-12" consumable, "N-5" asset)
 * because the two item tables number their rows independently.
 */
class StoreStock
{
    public const CONSUMABLE = 'Consumable';
    public const NON_CONSUMABLE = 'Non-Consumable';

    /** Asset statuses that block issuing. */
    public const UNISSUABLE_STATUSES = ['Disposed', 'Under Maintenance'];

    public static function key(string $type, int $id): string
    {
        return ($type === self::CONSUMABLE ? 'C-' : 'N-') . $id;
    }

    /**
     * @return array{type: string, id: int}|null
     */
    public static function parse(mixed $key): ?array
    {
        if (! is_string($key) || ! preg_match('/^([CN])-(\d+)$/', $key, $match)) {
            return null;
        }

        return [
            'type' => $match[1] === 'C' ? self::CONSUMABLE : self::NON_CONSUMABLE,
            'id' => (int) $match[2],
        ];
    }

    /**
     * Validation rule closure for item keys.
     */
    public static function keyRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (self::parse($value) === null) {
                $fail('The selected store item is not valid.');
            }
        };
    }

    /**
     * On-hand quantity per consumable id.
     *
     * @param  iterable<int>  $ids
     * @return array<int, int>
     */
    public static function consumableOnHand(iterable $ids): array
    {
        $keys = collect($ids)->mapWithKeys(fn ($id) => [self::key(self::CONSUMABLE, (int) $id) => (int) $id]);
        if ($keys->isEmpty()) {
            return [];
        }

        $totals = fn (string $table, string $column) => DB::table($table)
            ->whereIn('item_id', $keys->keys())
            ->groupBy('item_id')
            ->selectRaw("item_id, SUM($column) as total")
            ->pluck('total', 'item_id');

        $purchased = $totals((new PurchaseBillItem)->getTable(), 'quantity');
        $issued = $totals((new StockIssueItem)->getTable(), 'issued_quantity');
        $returned = $totals((new StockReturn)->getTable(), 'quantity');

        return $keys->mapWithKeys(fn ($id, $key) => [
            $id => (int) ($purchased[$key] ?? 0) - (int) ($issued[$key] ?? 0) + (int) ($returned[$key] ?? 0),
        ])->all();
    }

    /**
     * Assets currently out of the store, keyed by asset id.
     *
     * @param  iterable<int>|null  $assetIds  limit to these assets
     * @param  string|null  $employeeId  limit to assets held by this person
     * @return Collection<int, array{employee_id: string, employee_name: string, item_name: string, issued_on: ?string, outstanding: int}>
     */
    public static function assetCustody(?iterable $assetIds = null, ?string $employeeId = null): Collection
    {
        $keys = $assetIds === null ? null : collect($assetIds)->map(fn ($id) => self::key(self::NON_CONSUMABLE, (int) $id))->values();
        if ($keys !== null && $keys->isEmpty()) {
            return collect();
        }

        $issued = DB::table('stock_issue_items as line')
            ->join('stock_issues as issue', 'issue.id', '=', 'line.stock_issue_id')
            ->where('line.item_id', 'like', 'N-%')
            ->when($keys !== null, fn ($query) => $query->whereIn('line.item_id', $keys))
            ->when($employeeId !== null, fn ($query) => $query->where('issue.employee_id', $employeeId))
            ->groupBy('line.item_id', 'issue.employee_id')
            ->selectRaw('line.item_id, issue.employee_id, MAX(issue.employee_name) as employee_name, MAX(line.item_name) as item_name, MAX(issue.issue_date) as issued_on, SUM(line.issued_quantity) as quantity')
            ->get();

        $returned = DB::table('stock_returns')
            ->where('item_id', 'like', 'N-%')
            ->when($keys !== null, fn ($query) => $query->whereIn('item_id', $keys))
            ->when($employeeId !== null, fn ($query) => $query->where('employee_id', $employeeId))
            ->groupBy('item_id', 'employee_id')
            ->selectRaw('item_id, employee_id, SUM(quantity) as quantity')
            ->get()
            ->keyBy(fn ($row) => $row->item_id . '|' . $row->employee_id);

        return $issued
            ->map(fn ($row) => [
                'asset_id' => self::parse($row->item_id)['id'],
                'employee_id' => $row->employee_id,
                'employee_name' => $row->employee_name,
                'item_name' => $row->item_name,
                'issued_on' => $row->issued_on,
                'outstanding' => (int) $row->quantity - (int) ($returned[$row->item_id . '|' . $row->employee_id]->quantity ?? 0),
            ])
            ->filter(fn ($row) => $row['outstanding'] > 0)
            ->keyBy('asset_id')
            ->map(fn ($row) => collect($row)->except('asset_id')->all());
    }

    /**
     * Active store items matching a name, barcode or asset tag, with availability.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function searchItems(?string $term, int $limit = 50): Collection
    {
        $term = mb_strtolower(trim((string) $term));
        $like = '%' . $term . '%';

        $consumables = ConsumableItem::where('is_active', true)
            ->when($term !== '', fn ($query) => $query->where(fn ($where) => $where
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(barcode) LIKE ?', [$like])))
            ->orderBy('name')
            ->limit($limit)
            ->get();
        $onHand = self::consumableOnHand($consumables->pluck('id'));

        $assets = NonConsumableItem::where('is_active', true)
            ->when($term !== '', fn ($query) => $query->where(fn ($where) => $where
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(barcode) LIKE ?', [$like])
                ->orWhereRaw('LOWER(asset_tag) LIKE ?', [$like])))
            ->orderBy('name')
            ->limit($limit)
            ->get();
        $custody = self::assetCustody($assets->pluck('id'));

        $consumableRows = $consumables->map(function (ConsumableItem $item) use ($onHand) {
            $available = $onHand[$item->id] ?? 0;

            return [
                'id' => self::key(self::CONSUMABLE, $item->id),
                'item_id' => $item->id,
                'type' => self::CONSUMABLE,
                'name' => $item->name,
                'unit' => (string) $item->unit,
                'code' => $item->barcode,
                'purchase_price' => (float) $item->purchase_price,
                'sale_price' => (float) $item->sale_price,
                'price' => (float) $item->sale_price,
                'available_qty' => $available,
                'min_stock_level' => (int) $item->min_stock_level,
                'unavailable_reason' => $available > 0 ? null : 'Out of stock',
            ];
        });

        $assetRows = $assets->map(function (NonConsumableItem $asset) use ($custody) {
            $holder = $custody[$asset->id] ?? null;
            $reason = $holder
                ? 'Issued to ' . $holder['employee_name']
                : (in_array($asset->current_status, self::UNISSUABLE_STATUSES, true) ? $asset->current_status : null);

            return [
                'id' => self::key(self::NON_CONSUMABLE, $asset->id),
                'item_id' => $asset->id,
                'type' => self::NON_CONSUMABLE,
                'name' => $asset->name,
                'unit' => (string) $asset->unit,
                'code' => $asset->asset_tag,
                'purchase_price' => (float) $asset->purchase_price,
                'sale_price' => 0.0,
                'price' => (float) $asset->purchase_price,
                'available_qty' => $reason ? 0 : 1,
                'status' => $asset->current_status,
                'unavailable_reason' => $reason,
            ];
        });

        return $consumableRows->concat($assetRows)->values();
    }

    /**
     * System users and active doctors who can receive store items.
     * Ids are typed ("U-3" user, "D-7" doctor) and stored on issue/return rows.
     *
     * @return Collection<int, array{id: string, name: string, department: string, designation: string, kind: string}>
     */
    public static function searchStaff(?string $term, int $limit = 25): Collection
    {
        $term = mb_strtolower(trim((string) $term));
        $like = '%' . $term . '%';

        $users = User::with('roles:id,name')
            ->when($term !== '', fn ($query) => $query->where(fn ($where) => $where
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(username) LIKE ?', [$like])))
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (User $user) => [
                'id' => 'U-' . $user->id,
                'name' => $user->name,
                'department' => '',
                'designation' => $user->roles->pluck('name')->join(', '),
                'kind' => 'Staff',
            ]);

        $doctors = Doctor::with('department:id,name')
            ->where('is_active', true)
            ->when($term !== '', fn ($query) => $query->where(fn ($where) => $where
                ->whereRaw('LOWER(name) LIKE ?', [$like])
                ->orWhereRaw('LOWER(code) LIKE ?', [$like])))
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Doctor $doctor) => [
                'id' => 'D-' . $doctor->id,
                'name' => $doctor->name,
                'department' => (string) ($doctor->department->name ?? ''),
                'designation' => (string) ($doctor->type ?: 'Doctor'),
                'kind' => 'Doctor',
            ]);

        return $users->concat($doctors)->values();
    }
}
