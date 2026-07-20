<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LimsCashClosure;
use App\Services\Lims\CashClosureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CashClosureController extends Controller
{
    public function __construct(
        private readonly CashClosureService $closures,
    ) {}

    /**
     * CC: open a cash shift for the caller's collection center.
     */
    public function open(Request $request): JsonResponse
    {
        $this->authorize('open', LimsCashClosure::class);

        $data = $request->validate([
            'opening_float' => ['nullable', 'numeric', 'min:0'],
            'shift_label' => ['nullable', 'string', 'max:64'],
            'collection_center_id' => ['nullable', 'integer', 'exists:collection_centers,id'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
        ]);

        $closure = $this->closures->openShift($request->user(), [
            'opening_float' => $data['opening_float'] ?? 0,
            'shift_label' => $data['shift_label'] ?? null,
            'collection_center_id' => $data['collection_center_id'] ?? null,
            'idempotency_key' => $data['idempotency_key']
                ?? $request->header('Idempotency-Key'),
        ]);

        return response()->json(['data' => $closure], 201);
    }

    /**
     * CC: current open shift (or null).
     */
    public function current(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LimsCashClosure::class);

        $user = $request->user();
        $ccId = $user->isCollectionCenterScope()
            ? (int) $user->collection_center_id
            : (int) $request->query('collection_center_id');

        if (! $ccId) {
            return response()->json(['message' => 'collection_center_id is required.'], 422);
        }

        $closure = $this->closures->currentOpen($ccId);
        if ($closure !== null) {
            $this->authorize('view', $closure);
        }

        return response()->json(['data' => $closure]);
    }

    /**
     * CC: pre-close summary for the current open shift.
     */
    public function summaryCurrent(Request $request): JsonResponse
    {
        $user = $request->user();
        $ccId = $user->isCollectionCenterScope()
            ? (int) $user->collection_center_id
            : (int) $request->query('collection_center_id');

        if (! $ccId) {
            return response()->json(['message' => 'collection_center_id is required.'], 422);
        }

        $closure = $this->closures->currentOpen($ccId);
        if ($closure === null) {
            return response()->json(['message' => 'No open cash closure for this center.'], 404);
        }

        $this->authorize('view', $closure);

        return response()->json([
            'data' => $this->closures->buildSummary($closure, persist: true),
        ]);
    }

    /**
     * CC / Main Lab: pre-close (or post-submit) summary for a closure.
     */
    public function summary(LimsCashClosure $cashClosure): JsonResponse
    {
        $closure = LimsCashClosure::withoutGlobalScopes()->findOrFail($cashClosure->id);
        $this->authorize('view', $closure);

        return response()->json([
            'data' => $this->closures->buildSummary($closure, persist: $closure->isOpen()),
        ]);
    }

    /**
     * CC: submit counted cash → variance.
     */
    public function submit(Request $request, LimsCashClosure $cashClosure): JsonResponse
    {
        $closure = LimsCashClosure::withoutGlobalScopes()->findOrFail($cashClosure->id);
        $this->authorize('submit', $closure);

        $data = $request->validate([
            'counted_cash_total' => ['required', 'numeric'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
        ]);

        try {
            $submitted = $this->closures->submit(
                $closure,
                (float) $data['counted_cash_total'],
                $request->user(),
                $data['idempotency_key'] ?? $request->header('Idempotency-Key'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $submitted]);
    }

    /**
     * Main Lab: list closures by business_date.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LimsCashClosure::class);

        $data = $request->validate([
            'business_date' => ['required', 'date'],
            'collection_center_id' => ['nullable', 'integer', 'exists:collection_centers,id'],
            'status' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $ccFilter = $data['collection_center_id'] ?? null;

        if ($user->isCollectionCenterScope()) {
            $ccFilter = (int) $user->collection_center_id;
        }

        $rows = $this->closures->listByDate($data['business_date'], $ccFilter);

        if (! empty($data['status'])) {
            $rows = $rows->where('status', $data['status'])->values();
        }

        return response()->json(['data' => $rows->values()]);
    }

    /**
     * Main Lab: variance / detail view.
     */
    public function show(LimsCashClosure $cashClosure): JsonResponse
    {
        $closure = LimsCashClosure::withoutGlobalScopes()->findOrFail($cashClosure->id);
        $this->authorize('view', $closure);

        $summary = $this->closures->buildSummary($closure, persist: false);

        return response()->json([
            'data' => [
                'closure' => $closure,
                'summary' => $summary,
                'variance_cash' => $closure->variance_cash,
                'counted_cash_total' => $closure->counted_cash_total,
                'system_cash_total' => $closure->system_cash_total,
                'due_total' => $closure->due_total,
            ],
        ]);
    }

    /**
     * Main Lab: approve → locked.
     */
    public function approve(Request $request, LimsCashClosure $cashClosure): JsonResponse
    {
        $closure = LimsCashClosure::withoutGlobalScopes()->findOrFail($cashClosure->id);
        $this->authorize('approve', $closure);

        try {
            $locked = $this->closures->approve($closure, $request->user());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $locked]);
    }
}
