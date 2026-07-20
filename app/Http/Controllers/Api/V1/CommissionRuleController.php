<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LimsCommissionRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommissionRuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LimsCommissionRule::class);

        $query = LimsCommissionRule::query()->orderByDesc('id');

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', (int) $request->query('doctor_id'));
        }
        if ($request->filled('test_id')) {
            $query->where('test_id', (int) $request->query('test_id'));
        }
        if ($request->filled('collection_center_id')) {
            $query->where('collection_center_id', (int) $request->query('collection_center_id'));
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json([
            'data' => $query->limit(200)->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LimsCommissionRule::class);

        $data = $this->validatedRule($request);
        $data['organization_id'] = $request->user()->organization_id
            ?? LimsCommissionRule::query()->value('organization_id');
        $data['created_by'] = $request->user()->id;
        $data['is_active'] = $data['is_active'] ?? true;
        $data['priority'] = $data['priority'] ?? 100;

        if (empty($data['organization_id'])) {
            $org = \App\Models\Organization::query()->where('code', 'MMC')->first()
                ?? \App\Models\Organization::query()->orderBy('id')->first();
            $data['organization_id'] = $org?->id;
        }

        $rule = LimsCommissionRule::query()->create($data);

        return response()->json(['data' => $rule], 201);
    }

    public function update(Request $request, LimsCommissionRule $commissionRule): JsonResponse
    {
        $rule = LimsCommissionRule::query()->findOrFail($commissionRule->id);
        $this->authorize('update', $rule);

        // Updates affect future applicability only — never rewrite past snapshots.
        $data = $this->validatedRule($request, partial: true);
        $rule->fill($data);
        $rule->save();

        return response()->json(['data' => $rule->fresh()]);
    }

    public function deactivate(Request $request, LimsCommissionRule $commissionRule): JsonResponse
    {
        $rule = LimsCommissionRule::query()->findOrFail($commissionRule->id);
        $this->authorize('deactivate', $rule);

        $today = now('Asia/Karachi')->toDateString();
        $rule->effective_to = $today;
        $rule->is_active = false;
        $rule->save();

        return response()->json(['data' => $rule->fresh()]);
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
            'test_id' => ['nullable', 'integer', 'exists:tests,id'],
            'collection_center_id' => ['nullable', 'integer', 'exists:collection_centers,id'],
            'basis' => $basisRule,
            'amount' => ['nullable', 'numeric', 'min:0'],
            'percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'effective_from' => [$partial ? 'sometimes' : 'required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $basis = $data['basis'] ?? null;
        if ($basis === 'fixed') {
            if (! array_key_exists('amount', $data) || $data['amount'] === null) {
                abort(response()->json(['message' => 'amount is required for fixed basis.'], 422));
            }
            $data['percent'] = null;
        }
        if ($basis === 'percent') {
            if (! array_key_exists('percent', $data) || $data['percent'] === null) {
                abort(response()->json(['message' => 'percent is required for percent basis.'], 422));
            }
            $data['amount'] = null;
        }

        return $data;
    }
}
