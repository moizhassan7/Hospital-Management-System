<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restrict queries to the authenticated user's collection center when
 * user_scope = collection_center. Main Lab / Super Admin see all rows.
 *
 * Apply on Booking, Invoice, Payment, Sample, CashClosure, Batch in later phases.
 * Do not apply to LimsPatient (org-wide readable).
 */
class BelongsToCollectionCenterScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if ($user === null) {
            return;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return;
        }

        if (method_exists($user, 'isMainLabScope') && $user->isMainLabScope()) {
            return;
        }

        if (! empty($user->collection_center_id)) {
            $builder->where(
                $model->getTable().'.collection_center_id',
                $user->collection_center_id
            );
        }
    }
}
