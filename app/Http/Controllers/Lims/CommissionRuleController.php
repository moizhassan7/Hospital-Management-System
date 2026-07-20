<?php

namespace App\Http\Controllers\Lims;

use App\Http\Controllers\Controller;
use App\Models\CollectionCenter;
use App\Models\LimsCommissionRule;
use App\Models\LimsCommissionSnapshot;
use App\Models\LimsDoctor;
use App\Models\LimsTestCategory;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommissionRuleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', LimsCommissionRule::class);

        $query = LimsCommissionRule::query()
            ->with(['doctor', 'testCategory', 'collectionCenter'])
            ->orderByDesc('is_active')
            ->orderByDesc('id');

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', (int) $request->query('doctor_id'));
        }

        $rules = $query->limit(300)->get();
        $doctors = LimsDoctor::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('lims.commission_rules.index', compact('rules', 'doctors'));
    }

    public function create()
    {
        $this->authorize('create', LimsCommissionRule::class);

        return view('lims.commission_rules.form', $this->formData(new LimsCommissionRule([
            'basis' => LimsCommissionRule::BASIS_PERCENT,
            'priority' => 100,
            'is_active' => true,
            'effective_from' => now('Asia/Karachi')->toDateString(),
        ])));
    }

    public function store(Request $request)
    {
        $this->authorize('create', LimsCommissionRule::class);

        $data = $this->validatedRule($request);
        $data['organization_id'] = $this->resolveOrganizationId($request);
        $data['created_by'] = $request->user()?->id;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['priority'] = $data['priority'] ?? 100;

        LimsCommissionRule::query()->create($data);

        return redirect()
            ->route('pathology.commission_rules.index')
            ->with('success', 'Commission rule created.');
    }

    public function edit(LimsCommissionRule $commissionRule)
    {
        $this->authorize('update', $commissionRule);

        return view('lims.commission_rules.form', $this->formData($commissionRule));
    }

    public function update(Request $request, LimsCommissionRule $commissionRule)
    {
        $this->authorize('update', $commissionRule);

        // Updates affect future applicability only — never rewrite past snapshots.
        $data = $this->validatedRule($request, partial: true);
        $commissionRule->fill($data)->save();

        return redirect()
            ->route('pathology.commission_rules.index')
            ->with('success', 'Commission rule updated (future bookings only).');
    }

    public function deactivate(LimsCommissionRule $commissionRule)
    {
        $this->authorize('deactivate', $commissionRule);

        $today = now('Asia/Karachi')->toDateString();
        $commissionRule->effective_to = $today;
        $commissionRule->is_active = false;
        $commissionRule->save();

        return back()->with('success', 'Commission rule deactivated.');
    }

    public function snapshots(Request $request)
    {
        $this->authorize('viewAny', LimsCommissionRule::class);

        $snapshots = LimsCommissionSnapshot::query()
            ->with(['doctor', 'collectionCenter'])
            ->orderByDesc('snapshotted_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('lims.commission_rules.snapshots', compact('snapshots'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(LimsCommissionRule $rule): array
    {
        return [
            'rule' => $rule,
            'doctors' => LimsDoctor::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'categories' => LimsTestCategory::query()->orderBy('name')->get(['id', 'code', 'name']),
            'centers' => CollectionCenter::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedRule(Request $request, bool $partial = false): array
    {
        $basisRule = $partial
            ? ['sometimes', Rule::in(['fixed', 'percent'])]
            : ['required', Rule::in(['fixed', 'percent'])];

        $data = $request->validate([
            'doctor_id' => ['nullable', 'integer', 'exists:lims_doctors,id'],
            'test_category_id' => ['nullable', 'integer', 'exists:lims_test_categories,id'],
            'collection_center_id' => ['nullable', 'integer', 'exists:collection_centers,id'],
            'basis' => $basisRule,
            'amount' => ['nullable', 'numeric', 'min:0'],
            'percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'effective_from' => [$partial ? 'sometimes' : 'required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $basis = $data['basis'] ?? null;
        if ($basis === 'fixed') {
            if (! array_key_exists('amount', $data) || $data['amount'] === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => 'Amount is required for fixed basis.',
                ]);
            }
            $data['percent'] = null;
        }
        if ($basis === 'percent') {
            if (! array_key_exists('percent', $data) || $data['percent'] === null) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'percent' => 'Percent is required for percent basis.',
                ]);
            }
            $data['amount'] = null;
        }

        foreach (['doctor_id', 'test_category_id', 'collection_center_id'] as $nullableFk) {
            if (array_key_exists($nullableFk, $data) && $data[$nullableFk] === '') {
                $data[$nullableFk] = null;
            }
        }

        return $data;
    }

    private function resolveOrganizationId(Request $request): int
    {
        $orgId = $request->user()?->organization_id
            ?? Organization::query()->where('code', 'MMC')->value('id')
            ?? Organization::query()->orderBy('id')->value('id');

        if (! $orgId) {
            abort(422, 'No organization found. Seed LIMS organization first.');
        }

        return (int) $orgId;
    }
}
