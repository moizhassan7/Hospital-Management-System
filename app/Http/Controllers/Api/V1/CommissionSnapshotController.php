<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LimsCommissionSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommissionSnapshotController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LimsCommissionSnapshot::class);

        $query = LimsCommissionSnapshot::query()->orderByDesc('id');

        // CC scope is enforced by BelongsToCollectionCenterScope; Main Lab sees all.
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', (int) $request->query('doctor_id'));
        }
        if ($request->filled('booking_id')) {
            $query->where('booking_id', (int) $request->query('booking_id'));
        }
        if ($request->filled('collection_center_id')) {
            $query->where('collection_center_id', (int) $request->query('collection_center_id'));
        }
        if ($request->filled('is_clawed_back')) {
            $query->where(
                'is_clawed_back',
                filter_var($request->query('is_clawed_back'), FILTER_VALIDATE_BOOLEAN)
            );
        }

        return response()->json([
            'data' => $query->limit(200)->get(),
        ]);
    }
}
