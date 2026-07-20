<?php

namespace App\Http\Controllers\Lims;

use App\Http\Controllers\Controller;
use App\Models\CollectionCenter;
use App\Models\LimsSample;
use App\Models\LimsSampleBatch;
use App\Models\LimsSampleBatchItem;
use App\Services\Lims\SampleTransitService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SampleBatchController extends Controller
{
    public function __construct(
        private readonly SampleTransitService $transit,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', LimsSampleBatch::class);

        $query = LimsSampleBatch::query()
            ->with(['collectionCenter', 'destinationSite'])
            ->withCount('items')
            ->orderByDesc('id');

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

        $batches = $query->paginate(25)->withQueryString();

        $centers = null;
        $user = $request->user();
        if ($user && ($user->isSuperAdmin() || $user->isMainLabScope())) {
            $centers = CollectionCenter::query()
                ->where('kind', CollectionCenter::KIND_COLLECTION_CENTER)
                ->orderBy('code')
                ->get();
        }

        return view('lims.sample_batches.index', [
            'batches' => $batches,
            'centers' => $centers,
            'filters' => [
                'status' => $request->query('status'),
                'collection_center_id' => $request->query('collection_center_id'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
            ],
            'canCreate' => $user && $user->can('create', LimsSampleBatch::class),
        ]);
    }

    public function create()
    {
        $this->authorize('create', LimsSampleBatch::class);

        $user = auth()->user();
        $centers = null;
        $lockedCenter = null;

        if ($user?->isCollectionCenterScope() && $user->collection_center_id) {
            $lockedCenter = CollectionCenter::query()->find($user->collection_center_id);
        } else {
            $centers = CollectionCenter::query()
                ->where('kind', CollectionCenter::KIND_COLLECTION_CENTER)
                ->where('is_active', true)
                ->orderBy('code')
                ->get();
        }

        return view('lims.sample_batches.create', [
            'centers' => $centers,
            'lockedCenter' => $lockedCenter,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', LimsSampleBatch::class);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'collection_center_id' => ['nullable', 'integer', 'exists:collection_centers,id'],
        ]);

        try {
            $batch = $this->transit->createOpenBatch($request->user(), [
                'notes' => $data['notes'] ?? null,
                'collection_center_id' => isset($data['collection_center_id'])
                    ? (int) $data['collection_center_id']
                    : null,
                'create_idempotency_key' => (string) Str::uuid(),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['batch' => $e->getMessage()]);
        }

        return redirect()
            ->route('pathology.sample_batches.show', $batch)
            ->with('success', "Manifest {$batch->manifest_no} created.");
    }

    public function show(LimsSampleBatch $sampleBatch)
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('view', $batch);

        $batch->load([
            'collectionCenter',
            'destinationSite',
            'items.sample',
            'items.booking',
            'events' => fn ($q) => $q->orderBy('occurred_at')->orderBy('id'),
        ]);

        $availableSamples = collect();
        if ($batch->isOpen() && auth()->user()?->can('update', $batch)) {
            $inBatchIds = $batch->items->pluck('sample_id')->all();
            $availableSamples = LimsSample::query()
                ->where('collection_center_id', $batch->collection_center_id)
                ->where('status', LimsSample::STATUS_COLLECTED)
                ->when($inBatchIds !== [], fn ($q) => $q->whereNotIn('id', $inBatchIds))
                ->whereDoesntHave('batchItems', function ($q) {
                    $q->where('receive_status', LimsSampleBatchItem::RECEIVE_PENDING);
                })
                ->orderByDesc('id')
                ->limit(50)
                ->get();
        }

        return view('lims.sample_batches.show', [
            'batch' => $batch,
            'availableSamples' => $availableSamples,
            'canUpdate' => auth()->user()?->can('update', $batch) ?? false,
            'canDispatch' => auth()->user()?->can('dispatch', $batch) ?? false,
            'canMarkInTransit' => auth()->user()?->can('markInTransit', $batch) ?? false,
            'canReceive' => auth()->user()?->can('receive', $batch) ?? false,
        ]);
    }

    public function addItem(Request $request, LimsSampleBatch $sampleBatch)
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('update', $batch);

        $data = $request->validate([
            'sample_id' => ['nullable', 'integer', 'exists:lims_samples,id'],
            'barcode' => ['nullable', 'string', 'max:191'],
        ]);

        if (empty($data['sample_id']) && empty($data['barcode'])) {
            return back()->withErrors(['barcode' => 'Enter a barcode or select a sample.']);
        }

        try {
            $this->transit->addItem(
                $batch,
                isset($data['sample_id']) ? (int) $data['sample_id'] : null,
                $data['barcode'] ?? null,
                $request->user()
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Sample added to batch.');
    }

    public function removeItem(LimsSampleBatch $sampleBatch, int $sampleId)
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('update', $batch);

        try {
            $this->transit->removeItem($batch, $sampleId, request()->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', 'Sample removed from batch.');
    }

    public function dispatchBatch(Request $request, LimsSampleBatch $sampleBatch)
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('dispatch', $batch);

        $data = $request->validate([
            'courier_name' => ['required', 'string', 'max:191'],
            'courier_ref' => ['nullable', 'string', 'max:191'],
        ]);

        $data['idempotency_key'] = (string) Str::uuid();

        try {
            $this->transit->dispatch($batch, $data, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Batch dispatched.');
    }

    public function markInTransit(Request $request, LimsSampleBatch $sampleBatch)
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('markInTransit', $batch);

        $data = $request->validate([
            'location_label' => ['nullable', 'string', 'max:191'],
        ]);

        $data['idempotency_key'] = (string) Str::uuid();

        try {
            $this->transit->markInTransit($batch, $data, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Batch marked in transit.');
    }

    public function receive(Request $request, LimsSampleBatch $sampleBatch)
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()->findOrFail($sampleBatch->id);
        $this->authorize('receive', $batch);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.sample_id' => ['required', 'integer'],
            'items.*.receive_status' => ['required', Rule::in([
                LimsSampleBatchItem::RECEIVE_RECEIVED,
                LimsSampleBatchItem::RECEIVE_MISSING,
                LimsSampleBatchItem::RECEIVE_REJECTED,
            ])],
            'items.*.receive_note' => ['nullable', 'string', 'max:500'],
        ]);

        $data['idempotency_key'] = (string) Str::uuid();

        try {
            $this->transit->receive($batch, $data, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return back()->with('success', 'Batch received at Main Lab.');
    }
}
