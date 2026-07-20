<?php

namespace App\Services\Lims;

use App\Models\LimsCommissionRule;
use App\Models\LimsDoctor;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Resolve the most specific active commission rule for a booking line.
 *
 * Order (most specific wins, then lower priority):
 * (doctor+test+CC) → (doctor+test) → (doctor+category+CC) → (doctor+category)
 * → (doctor) → org defaults (doctor_id null) with same specificity ladder.
 */
class CommissionRuleResolver
{
    public function resolve(
        int $organizationId,
        ?int $doctorId,
        ?int $testId,
        ?int $testCategoryId,
        int $collectionCenterId,
        CarbonInterface|string $asOfDate,
    ): ?LimsCommissionRule {
        $date = $asOfDate instanceof CarbonInterface
            ? $asOfDate->toDateString()
            : (string) $asOfDate;

        $candidates = LimsCommissionRule::query()
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            })
            ->where(function ($q) use ($doctorId) {
                $q->whereNull('doctor_id');
                if ($doctorId !== null) {
                    $q->orWhere('doctor_id', $doctorId);
                }
            })
            ->where(function ($q) use ($testId) {
                $q->whereNull('test_id');
                if ($testId !== null) {
                    $q->orWhere('test_id', $testId);
                }
            })
            ->where(function ($q) use ($testCategoryId) {
                $q->whereNull('test_category_id');
                if ($testCategoryId !== null) {
                    $q->orWhere('test_category_id', $testCategoryId);
                }
            })
            ->where(function ($q) use ($collectionCenterId) {
                $q->whereNull('collection_center_id')
                    ->orWhere('collection_center_id', $collectionCenterId);
            })
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        return $this->pickBest($candidates);
    }

    /**
     * @param  Collection<int, LimsCommissionRule>  $candidates
     */
    private function pickBest(Collection $candidates): LimsCommissionRule
    {
        return $candidates
            ->sort(function (LimsCommissionRule $a, LimsCommissionRule $b) {
                $sa = $this->specificity($a);
                $sb = $this->specificity($b);
                if ($sa !== $sb) {
                    return $sb <=> $sa; // higher specificity first
                }
                if ((int) $a->priority !== (int) $b->priority) {
                    return (int) $a->priority <=> (int) $b->priority;
                }

                return (int) $a->id <=> (int) $b->id;
            })
            ->first();
    }

    private function specificity(LimsCommissionRule $rule): int
    {
        // doctor-scoped rules always beat org defaults; then test > category > CC.
        return ($rule->doctor_id ? 1000 : 0)
            + ($rule->test_id ? 100 : 0)
            + ($rule->test_category_id ? 10 : 0)
            + ($rule->collection_center_id ? 1 : 0);
    }

    /**
     * Compute frozen commission amount from a rule (never mutate later).
     */
    public function calculateAmount(LimsCommissionRule $rule, float $baseAmount): float
    {
        if ($rule->basis === LimsCommissionRule::BASIS_FIXED) {
            return round((float) $rule->amount, 2);
        }

        return round($baseAmount * ((float) $rule->percent) / 100, 2);
    }

    /**
     * Find or create a referring doctor from legacy refer_by_doctor_name.
     */
    public function findOrCreateDoctor(int $organizationId, string $name, ?string $phone = null): LimsDoctor
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Doctor name is required.');
        }

        $existing = LimsDoctor::query()
            ->where('organization_id', $organizationId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->whereNull('deleted_at')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return LimsDoctor::query()->create([
            'organization_id' => $organizationId,
            'code' => null,
            'name' => $name,
            'phone' => $phone,
            'is_active' => true,
        ]);
    }
}
