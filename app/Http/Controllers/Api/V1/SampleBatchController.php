<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LimsSampleBatch;
use App\Models\LimsTransitEvent;
use App\Services\Lims\SampleTransitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SampleBatchController extends Controller
{
    public function __construct(
        private readonly SampleTransitService $transit,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LimsSampleBatch::class);

        $query = LimsSampleBatch::query()->withCount('items')->orderByDesc('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($cc = $request->query('collection_center_id')) {
            $query->where('collection_center_id', (int) $cc);
        }
        if ($from = $request->query('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return response()->json([
            'data' => $query->limit(100)->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', LimsSampleBatch::class);

        $data = $request->validate([
            'notes' => ['nullable', 'string'],
            'destination_site_id' => ['nullable', 'integer', 'exists:collection_centers,id'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
        ]);

        $batch = $this->transit->createOpenBatch($request->user(), [
            'notes' => $data['notes'] ?? null,
            'destination_site_id' => $data['destination_site_id'] ?? null,
            'create_idempotency_key' => $data['idempotency_key']
                ?? $request->header('Idempotency-Key'),
        ]);

        return response()->json(['data' => $batch->load(['items', 'events'])], 201);
    }

    public function show(LimsSampleBatch $sampleBatch): JsonResponse
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('view', $batch);

        return response()->json([
            'data' => $batch->load(['items.sample', 'events', 'destinationSite', 'collectionCenter']),
        ]);
    }

    public function addItem(Request $request, LimsSampleBatch $sampleBatch): JsonResponse
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('update', $batch);

        $data = $request->validate([
            'sample_id' => ['nullable', 'integer', 'exists:lims_samples,id'],
            'barcode' => ['nullable', 'string', 'max:191'],
        ]);

        if (empty($data['sample_id']) && empty($data['barcode'])) {
            return response()->json([
                'message' => 'sample_id or barcode is required.',
            ], 422);
        }

        $item = $this->transit->addItem(
            $batch,
            isset($data['sample_id']) ? (int) $data['sample_id'] : null,
            $data['barcode'] ?? null,
            $request->user()
        );

        return response()->json(['data' => $item->load('sample')], 201);
    }

    public function removeItem(LimsSampleBatch $sampleBatch, int $sampleId): JsonResponse
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('update', $batch);

        $this->transit->removeItem($batch, $sampleId, request()->user());

        return response()->json(['message' => 'Sample removed from batch.']);
    }

    public function dispatchBatch(Request $request, LimsSampleBatch $sampleBatch): JsonResponse
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('dispatch', $batch);

        $data = $request->validate([
            'courier_name' => ['nullable', 'string', 'max:191'],
            'courier_ref' => ['nullable', 'string', 'max:191'],
            'sample_barcodes' => ['nullable', 'array'],
            'sample_barcodes.*' => ['string'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
        ]);

        $data['idempotency_key'] = $data['idempotency_key']
            ?? $request->header('Idempotency-Key');

        $batch = $this->transit->dispatch($batch, $data, $request->user());

        return response()->json(['data' => $batch]);
    }

    public function inTransit(Request $request, LimsSampleBatch $sampleBatch): JsonResponse
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('markInTransit', $batch);

        $data = $request->validate([
            'location_label' => ['nullable', 'string', 'max:191'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
        ]);

        $data['idempotency_key'] = $data['idempotency_key']
            ?? $request->header('Idempotency-Key');

        $batch = $this->transit->markInTransit($batch, $data, $request->user());

        return response()->json(['data' => $batch]);
    }

    public function receive(Request $request, LimsSampleBatch $sampleBatch): JsonResponse
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('receive', $batch);

        $data = $request->validate([
            'received_at' => ['nullable', 'date'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sample_id' => ['required', 'integer'],
            'items.*.receive_status' => ['required', Rule::in(['received', 'missing', 'rejected'])],
            'items.*.receive_note' => ['nullable', 'string'],
        ]);

        $data['idempotency_key'] = $data['idempotency_key']
            ?? $request->header('Idempotency-Key');

        $batch = $this->transit->receive($batch, $data, $request->user());

        return response()->json(['data' => $batch]);
    }

    public function events(LimsSampleBatch $sampleBatch): JsonResponse
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('view', $batch);

        $events = LimsTransitEvent::query()
            ->where('batch_id', $batch->id)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        return response()->json(['data' => $events]);
    }
}
