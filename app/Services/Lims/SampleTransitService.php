<?php

namespace App\Services\Lims;

use App\Models\CollectionCenter;
use App\Models\LimsBooking;
use App\Models\LimsBookingItem;
use App\Models\LimsSample;
use App\Models\LimsSampleBatch;
use App\Models\LimsSampleBatchItem;
use App\Models\LimsTransitEvent;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Sample batch transit state machine with idempotent dispatch / in-transit / receive.
 */
class SampleTransitService
{
    public function __construct(
        private readonly ManifestNumberAllocator $manifestAllocator,
        private readonly LimsSampleSync $sampleSync,
    ) {}

    /**
     * Create an open batch/manifest for a collection center (defaults to actor's CC).
     *
     * @param  array{notes?: string, destination_site_id?: int, create_idempotency_key?: string}  $data
     */
    public function createOpenBatch(?User $actor = null, array $data = []): LimsSampleBatch
    {
        $actor ??= auth()->user();
        [$organization, $collectionCenter] = $this->resolveOriginTenancy($actor);

        $destinationId = $data['destination_site_id']
            ?? $organization->mainLab?->id
            ?? CollectionCenter::query()
                ->where('organization_id', $organization->id)
                ->where('kind', CollectionCenter::KIND_MAIN_LAB)
                ->value('id');

        if (! $destinationId) {
            throw new RuntimeException('Main Lab destination site not found for organization.');
        }

        $createKey = $data['create_idempotency_key'] ?? null;
        if ($createKey) {
            $existingEvent = LimsTransitEvent::query()
                ->where('collection_center_id', $collectionCenter->id)
                ->where('event_type', LimsTransitEvent::TYPE_CREATED)
                ->where('idempotency_key', $createKey)
                ->first();

            if ($existingEvent) {
                return LimsSampleBatch::withoutGlobalScopes()
                    ->findOrFail($existingEvent->batch_id);
            }
        }

        return DB::transaction(function () use ($organization, $collectionCenter, $destinationId, $actor, $data, $createKey) {
            $allocated = $this->manifestAllocator->allocate($collectionCenter->id);

            $batch = LimsSampleBatch::withoutGlobalScopes()->create([
                'organization_id' => $organization->id,
                'collection_center_id' => $collectionCenter->id,
                'destination_site_id' => $destinationId,
                'manifest_no' => $allocated['manifest_no'],
                'status' => LimsSampleBatch::STATUS_OPEN,
                'sample_count' => 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->appendEvent(
                $batch,
                LimsTransitEvent::TYPE_CREATED,
                $actor,
                $createKey,
                ['manifest_no' => $batch->manifest_no]
            );

            return $batch->fresh(['items', 'events']);
        });
    }

    /**
     * Add a collected sample to an open batch (by sample id or barcode).
     */
    public function addItem(
        LimsSampleBatch $batch,
        ?int $sampleId = null,
        ?string $barcode = null,
        ?User $actor = null,
    ): LimsSampleBatchItem {
        $actor ??= auth()->user();
        $this->assertCanMutateBatch($batch, $actor);

        return DB::transaction(function () use ($batch, $sampleId, $barcode, $actor) {
            $batch = $this->lockBatch($batch->id);

            if ($batch->status !== LimsSampleBatch::STATUS_OPEN) {
                throw ValidationException::withMessages([
                    'batch' => 'Samples can only be added to an open batch.',
                ]);
            }

            $sample = $this->resolveSample($sampleId, $barcode, $batch);

            if ($sample->status !== LimsSample::STATUS_COLLECTED) {
                throw ValidationException::withMessages([
                    'sample' => "Sample must be collected (current: {$sample->status}).",
                ]);
            }

            if ((int) $sample->collection_center_id !== (int) $batch->collection_center_id) {
                throw ValidationException::withMessages([
                    'sample' => 'Sample belongs to a different collection center.',
                ]);
            }

            $alreadyInBatch = LimsSampleBatchItem::query()
                ->where('batch_id', $batch->id)
                ->where('sample_id', $sample->id)
                ->first();

            if ($alreadyInBatch) {
                return $alreadyInBatch;
            }

            $activeElsewhere = LimsSampleBatchItem::query()
                ->where('sample_id', $sample->id)
                ->where('receive_status', LimsSampleBatchItem::RECEIVE_PENDING)
                ->where('batch_id', '!=', $batch->id)
                ->exists();

            if ($activeElsewhere) {
                throw ValidationException::withMessages([
                    'sample' => 'Sample is already in another active batch.',
                ]);
            }

            $item = LimsSampleBatchItem::query()->create([
                'batch_id' => $batch->id,
                'sample_id' => $sample->id,
                'booking_id' => $sample->booking_id,
                'added_at' => now(),
                'receive_status' => LimsSampleBatchItem::RECEIVE_PENDING,
            ]);

            $batch->sample_count = LimsSampleBatchItem::query()->where('batch_id', $batch->id)->count();
            $batch->save();

            return $item;
        });
    }

    public function removeItem(LimsSampleBatch $batch, int $sampleId, ?User $actor = null): void
    {
        $actor ??= auth()->user();
        $this->assertCanMutateBatch($batch, $actor);

        DB::transaction(function () use ($batch, $sampleId) {
            $batch = $this->lockBatch($batch->id);

            if ($batch->status !== LimsSampleBatch::STATUS_OPEN) {
                throw ValidationException::withMessages([
                    'batch' => 'Samples can only be removed from an open batch.',
                ]);
            }

            $deleted = LimsSampleBatchItem::query()
                ->where('batch_id', $batch->id)
                ->where('sample_id', $sampleId)
                ->delete();

            if (! $deleted) {
                throw ValidationException::withMessages([
                    'sample' => 'Sample is not in this batch.',
                ]);
            }

            $batch->sample_count = LimsSampleBatchItem::query()->where('batch_id', $batch->id)->count();
            $batch->save();
        });
    }

    /**
     * @param  array{courier_name?: string, courier_ref?: string, sample_barcodes?: list<string>, idempotency_key: string}  $data
     */
    public function dispatch(LimsSampleBatch $batch, array $data, ?User $actor = null): LimsSampleBatch
    {
        $actor ??= auth()->user();
        $this->assertCanMutateBatch($batch, $actor);

        $idempotencyKey = $data['idempotency_key'] ?? null;
        if (! $idempotencyKey) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Idempotency key is required for dispatch.',
            ]);
        }

        // Idempotent replay by batch.idempotency_key or transit event
        $replay = LimsSampleBatch::withoutGlobalScopes()
            ->where('collection_center_id', $batch->collection_center_id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($replay && (int) $replay->id === (int) $batch->id && $replay->status !== LimsSampleBatch::STATUS_OPEN) {
            return $replay->load(['items.sample', 'events']);
        }

        if ($replay && (int) $replay->id !== (int) $batch->id) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Idempotency key already used for another batch.',
            ]);
        }

        $existingEvent = LimsTransitEvent::query()
            ->where('batch_id', $batch->id)
            ->where('event_type', LimsTransitEvent::TYPE_DISPATCHED)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingEvent) {
            return LimsSampleBatch::withoutGlobalScopes()
                ->with(['items.sample', 'events'])
                ->findOrFail($batch->id);
        }

        return DB::transaction(function () use ($batch, $data, $actor, $idempotencyKey) {
            $batch = $this->lockBatch($batch->id);

            if ($batch->status !== LimsSampleBatch::STATUS_OPEN) {
                if ($batch->idempotency_key === $idempotencyKey) {
                    return $batch->load(['items.sample', 'events']);
                }
                throw ValidationException::withMessages([
                    'batch' => "Batch cannot be dispatched from status {$batch->status}.",
                ]);
            }

            $items = LimsSampleBatchItem::query()
                ->where('batch_id', $batch->id)
                ->with('sample')
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'batch' => 'Cannot dispatch an empty batch.',
                ]);
            }

            $barcodes = $data['sample_barcodes'] ?? null;
            if (is_array($barcodes) && $barcodes !== []) {
                $expected = collect($barcodes)->map(fn ($b) => strtoupper(trim((string) $b)))->sort()->values();
                $actual = $items->map(fn ($i) => strtoupper(trim((string) $i->sample->barcode)))->sort()->values();
                if ($expected->all() !== $actual->all()) {
                    throw ValidationException::withMessages([
                        'sample_barcodes' => 'Provided barcodes do not match batch contents.',
                    ]);
                }
            }

            foreach ($items as $item) {
                $sample = $item->sample;
                if ($sample === null || $sample->status !== LimsSample::STATUS_COLLECTED) {
                    throw ValidationException::withMessages([
                        'sample' => "Sample {$item->sample_id} must be collected before dispatch.",
                    ]);
                }
            }

            $now = now();
            $batch->status = LimsSampleBatch::STATUS_DISPATCHED;
            $batch->dispatched_at = $now;
            $batch->dispatched_by = $actor?->id;
            $batch->courier_name = $data['courier_name'] ?? null;
            $batch->courier_ref = $data['courier_ref'] ?? null;
            $batch->idempotency_key = $idempotencyKey;
            $batch->save();

            $sampleIds = $items->pluck('sample_id')->all();
            LimsSample::withoutGlobalScopes()
                ->whereIn('id', $sampleIds)
                ->update(['status' => LimsSample::STATUS_DISPATCHED, 'updated_at' => $now]);

            $this->advanceBookingItemsForSamples($sampleIds, LimsBookingItem::SAMPLE_DISPATCHED);
            $this->refreshBookingStatuses($items->pluck('booking_id')->unique()->all());

            $this->appendEvent(
                $batch,
                LimsTransitEvent::TYPE_DISPATCHED,
                $actor,
                $idempotencyKey,
                [
                    'courier_name' => $batch->courier_name,
                    'courier_ref' => $batch->courier_ref,
                    'sample_ids' => $sampleIds,
                ]
            );

            return $batch->fresh(['items.sample', 'events']);
        });
    }

    /**
     * @param  array{idempotency_key: string, location_label?: string}  $data
     */
    public function markInTransit(LimsSampleBatch $batch, array $data, ?User $actor = null): LimsSampleBatch
    {
        $actor ??= auth()->user();
        $this->assertCanViewBatch($batch, $actor);

        $idempotencyKey = $data['idempotency_key'] ?? null;
        if (! $idempotencyKey) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Idempotency key is required for in-transit.',
            ]);
        }

        $existingEvent = LimsTransitEvent::query()
            ->where('batch_id', $batch->id)
            ->where('event_type', LimsTransitEvent::TYPE_SCANNED_IN_TRANSIT)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingEvent) {
            return LimsSampleBatch::withoutGlobalScopes()
                ->with(['items.sample', 'events'])
                ->findOrFail($batch->id);
        }

        return DB::transaction(function () use ($batch, $data, $actor, $idempotencyKey) {
            $batch = $this->lockBatch($batch->id);

            if ($batch->status === LimsSampleBatch::STATUS_IN_TRANSIT) {
                $this->appendEvent(
                    $batch,
                    LimsTransitEvent::TYPE_SCANNED_IN_TRANSIT,
                    $actor,
                    $idempotencyKey,
                    ['note' => 'idempotent_scan'],
                    $data['location_label'] ?? null
                );

                return $batch->fresh(['items.sample', 'events']);
            }

            if ($batch->status !== LimsSampleBatch::STATUS_DISPATCHED) {
                throw ValidationException::withMessages([
                    'batch' => "Batch cannot move to in_transit from status {$batch->status}.",
                ]);
            }

            $now = now();
            $batch->status = LimsSampleBatch::STATUS_IN_TRANSIT;
            $batch->in_transit_at = $now;
            $batch->save();

            $sampleIds = LimsSampleBatchItem::query()
                ->where('batch_id', $batch->id)
                ->pluck('sample_id')
                ->all();

            LimsSample::withoutGlobalScopes()
                ->whereIn('id', $sampleIds)
                ->where('status', LimsSample::STATUS_DISPATCHED)
                ->update(['status' => LimsSample::STATUS_IN_TRANSIT, 'updated_at' => $now]);

            $this->advanceBookingItemsForSamples($sampleIds, LimsBookingItem::SAMPLE_IN_TRANSIT);
            $this->refreshBookingStatuses(
                LimsSampleBatchItem::query()->where('batch_id', $batch->id)->pluck('booking_id')->unique()->all()
            );

            $this->appendEvent(
                $batch,
                LimsTransitEvent::TYPE_SCANNED_IN_TRANSIT,
                $actor,
                $idempotencyKey,
                [],
                $data['location_label'] ?? null
            );

            return $batch->fresh(['items.sample', 'events']);
        });
    }

    /**
     * @param  array{
     *   idempotency_key: string,
     *   received_at?: string,
     *   items: list<array{sample_id: int, receive_status: string, receive_note?: string}>
     * }  $data
     */
    public function receive(LimsSampleBatch $batch, array $data, ?User $actor = null): LimsSampleBatch
    {
        $actor ??= auth()->user();
        $this->assertCanReceive($actor);

        $idempotencyKey = $data['idempotency_key'] ?? null;
        if (! $idempotencyKey) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'Idempotency key is required for receive.',
            ]);
        }

        $existingEvent = LimsTransitEvent::query()
            ->where('batch_id', $batch->id)
            ->where('event_type', LimsTransitEvent::TYPE_RECEIVED)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingEvent) {
            return LimsSampleBatch::withoutGlobalScopes()
                ->with(['items.sample', 'events'])
                ->findOrFail($batch->id);
        }

        $itemsPayload = $data['items'] ?? null;
        if (! is_array($itemsPayload) || $itemsPayload === []) {
            throw ValidationException::withMessages([
                'items' => 'Receive requires a non-empty items list.',
            ]);
        }

        return DB::transaction(function () use ($batch, $data, $actor, $idempotencyKey, $itemsPayload) {
            $batch = $this->lockBatch($batch->id);

            if (! in_array($batch->status, [
                LimsSampleBatch::STATUS_DISPATCHED,
                LimsSampleBatch::STATUS_IN_TRANSIT,
            ], true)) {
                if ($batch->status === LimsSampleBatch::STATUS_RECEIVED) {
                    return $batch->load(['items.sample', 'events']);
                }
                throw ValidationException::withMessages([
                    'batch' => "Batch cannot be received from status {$batch->status}.",
                ]);
            }

            $batchItems = LimsSampleBatchItem::query()
                ->where('batch_id', $batch->id)
                ->get()
                ->keyBy('sample_id');

            $receivedAt = isset($data['received_at'])
                ? \Carbon\Carbon::parse($data['received_at'])
                : now();

            $now = now();
            $rejectedAny = false;

            foreach ($itemsPayload as $row) {
                $sampleId = (int) ($row['sample_id'] ?? 0);
                $receiveStatus = (string) ($row['receive_status'] ?? '');
                $note = $row['receive_note'] ?? null;

                if (! $batchItems->has($sampleId)) {
                    throw ValidationException::withMessages([
                        'items' => "Sample {$sampleId} is not in this batch.",
                    ]);
                }

                if (! in_array($receiveStatus, [
                    LimsSampleBatchItem::RECEIVE_RECEIVED,
                    LimsSampleBatchItem::RECEIVE_MISSING,
                    LimsSampleBatchItem::RECEIVE_REJECTED,
                ], true)) {
                    throw ValidationException::withMessages([
                        'items' => "Invalid receive_status for sample {$sampleId}.",
                    ]);
                }

                $item = $batchItems->get($sampleId);
                $item->receive_status = $receiveStatus;
                $item->receive_note = $note;
                $item->save();

                $sample = LimsSample::withoutGlobalScopes()->lockForUpdate()->find($sampleId);
                if ($sample === null) {
                    continue;
                }

                if ($receiveStatus === LimsSampleBatchItem::RECEIVE_RECEIVED) {
                    $sample->status = LimsSample::STATUS_RECEIVED;
                    $sample->received_at = $receivedAt;
                    $sample->received_by = $actor?->id;
                    $sample->save();
                    $this->advanceBookingItemsForSamples([$sampleId], LimsBookingItem::SAMPLE_RECEIVED);
                    $this->sampleSync->pushReceiveToLegacy($sample, 'received');
                } elseif ($receiveStatus === LimsSampleBatchItem::RECEIVE_REJECTED) {
                    $sample->status = LimsSample::STATUS_REJECTED;
                    $sample->rejected_at = $now;
                    $sample->reject_reason = $note;
                    $sample->save();
                    $this->markBookingItemsRejectedForSample($sample);
                    $this->sampleSync->pushReceiveToLegacy($sample, 'rejected', $note);
                    $rejectedAny = true;

                    $this->appendEvent(
                        $batch,
                        LimsTransitEvent::TYPE_REJECTED_ITEM,
                        $actor,
                        $idempotencyKey.'-reject-'.$sampleId,
                        ['sample_id' => $sampleId, 'note' => $note]
                    );
                }
                // missing: leave sample status as dispatched/in_transit; active unique drops when receive_status changes
            }

            // Any batch items not listed stay pending → treat as missing for partial receive completeness?
            // Architecture: caller supplies per-item statuses. Require all items covered.
            $covered = collect($itemsPayload)->pluck('sample_id')->map(fn ($id) => (int) $id)->all();
            $uncovered = $batchItems->keys()->diff($covered);
            if ($uncovered->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'All batch items must be included in receive payload (missing sample_ids: '
                        .$uncovered->implode(', ').').',
                ]);
            }

            $batch->status = LimsSampleBatch::STATUS_RECEIVED;
            $batch->received_at = $receivedAt;
            $batch->received_by = $actor?->id;
            $batch->save();

            $this->refreshBookingStatuses($batchItems->pluck('booking_id')->unique()->all());

            $this->appendEvent(
                $batch,
                LimsTransitEvent::TYPE_RECEIVED,
                $actor,
                $idempotencyKey,
                [
                    'items' => $itemsPayload,
                    'rejected_any' => $rejectedAny,
                ],
                'Main Lab'
            );

            return $batch->fresh(['items.sample', 'events']);
        });
    }

    /**
     * @return array{0: Organization, 1: CollectionCenter}
     */
    public function resolveOriginTenancy(?User $actor = null): array
    {
        $actor ??= auth()->user();

        $organization = null;
        $collectionCenter = null;

        if ($actor instanceof User) {
            if ($actor->organization_id) {
                $organization = Organization::query()->find($actor->organization_id);
            }
            if ($actor->isCollectionCenterScope() && $actor->collection_center_id) {
                $collectionCenter = CollectionCenter::query()->find($actor->collection_center_id);
            }
        }

        $organization ??= Organization::query()->where('code', 'MMC')->first()
            ?? Organization::query()->orderBy('id')->first();

        if ($organization === null) {
            throw new RuntimeException('No organization available for batch create.');
        }

        if ($collectionCenter === null) {
            // Main Lab / unscoped smoke: allow creating as MAIN site (still a valid origin).
            $collectionCenter = CollectionCenter::query()
                ->where('organization_id', $organization->id)
                ->where('kind', CollectionCenter::KIND_COLLECTION_CENTER)
                ->where('code', 'CC1')
                ->first()
                ?? CollectionCenter::query()
                    ->where('organization_id', $organization->id)
                    ->where('kind', CollectionCenter::KIND_COLLECTION_CENTER)
                    ->orderBy('id')
                    ->first();
        }

        if ($collectionCenter === null) {
            throw new RuntimeException('No collection center available for batch create.');
        }

        return [$organization, $collectionCenter];
    }

    private function lockBatch(int $batchId): LimsSampleBatch
    {
        $batch = LimsSampleBatch::withoutGlobalScopes()
            ->where('id', $batchId)
            ->lockForUpdate()
            ->first();

        if ($batch === null) {
            throw ValidationException::withMessages(['batch' => 'Batch not found.']);
        }

        return $batch;
    }

    private function resolveSample(?int $sampleId, ?string $barcode, LimsSampleBatch $batch): LimsSample
    {
        $query = LimsSample::withoutGlobalScopes()
            ->where('organization_id', $batch->organization_id);

        if ($sampleId) {
            $sample = $query->where('id', $sampleId)->first();
        } elseif ($barcode) {
            $sample = $query->where('barcode', trim($barcode))->first();
        } else {
            throw ValidationException::withMessages([
                'sample' => 'sample_id or barcode is required.',
            ]);
        }

        if ($sample === null) {
            throw ValidationException::withMessages([
                'sample' => 'Sample not found.',
            ]);
        }

        return $sample;
    }

    private function appendEvent(
        LimsSampleBatch $batch,
        string $eventType,
        ?User $actor,
        ?string $idempotencyKey,
        array $payload = [],
        ?string $locationLabel = null,
    ): LimsTransitEvent {
        if ($idempotencyKey) {
            $existing = LimsTransitEvent::query()
                ->where('batch_id', $batch->id)
                ->where('event_type', $eventType)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return LimsTransitEvent::query()->create([
            'batch_id' => $batch->id,
            'collection_center_id' => $batch->collection_center_id,
            'event_type' => $eventType,
            'occurred_at' => now(),
            'actor_user_id' => $actor?->id,
            'location_label' => $locationLabel,
            'payload' => $payload,
            'idempotency_key' => $idempotencyKey,
            'created_at' => now(),
        ]);
    }

    /**
     * Advance booking_items linked via legacy vial test_ids (sample_tests table is later).
     *
     * @param  list<int>  $sampleIds
     */
    private function advanceBookingItemsForSamples(array $sampleIds, string $sampleStatus): void
    {
        if ($sampleIds === []) {
            return;
        }

        $order = [
            LimsBookingItem::SAMPLE_BOOKED => 0,
            LimsBookingItem::SAMPLE_COLLECTED => 1,
            LimsBookingItem::SAMPLE_DISPATCHED => 2,
            LimsBookingItem::SAMPLE_IN_TRANSIT => 3,
            LimsBookingItem::SAMPLE_RECEIVED => 4,
            LimsBookingItem::SAMPLE_PROCESSING => 5,
            LimsBookingItem::SAMPLE_COMPLETED => 6,
        ];
        $targetRank = $order[$sampleStatus] ?? 0;

        $samples = LimsSample::withoutGlobalScopes()
            ->whereIn('id', $sampleIds)
            ->with('labSampleVial')
            ->get();

        foreach ($samples as $sample) {
            $testIds = $sample->labSampleVial?->test_ids ?? [];

            $query = LimsBookingItem::withoutGlobalScopes()
                ->where('booking_id', $sample->booking_id);

            if ($testIds !== []) {
                $query->whereIn('test_id', $testIds);
            }

            foreach ($query->get() as $item) {
                $currentRank = $order[$item->sample_status] ?? 0;
                if ($currentRank < $targetRank) {
                    $item->sample_status = $sampleStatus;
                    $item->save();
                }
            }
        }
    }

    /**
     * After reject, set booking_item sample_status for tests on the vial when bridged.
     */
    public function markBookingItemsRejectedForSample(LimsSample $sample): void
    {
        $vial = $sample->labSampleVial;
        $testIds = $vial?->test_ids ?? [];

        if ($testIds === []) {
            return;
        }

        LimsBookingItem::withoutGlobalScopes()
            ->where('booking_id', $sample->booking_id)
            ->whereIn('test_id', $testIds)
            ->update(['sample_status' => LimsBookingItem::SAMPLE_REJECTED]);
    }

    /**
     * @param  list<int>  $bookingIds
     */
    private function refreshBookingStatuses(array $bookingIds): void
    {
        foreach (array_unique($bookingIds) as $bookingId) {
            $booking = LimsBooking::withoutGlobalScopes()->find($bookingId);
            if ($booking === null || $booking->status === LimsBooking::STATUS_CANCELLED) {
                continue;
            }

            $samples = LimsSample::withoutGlobalScopes()
                ->where('booking_id', $bookingId)
                ->get();

            if ($samples->isEmpty()) {
                continue;
            }

            $statuses = $samples->pluck('status');
            $inFlight = [
                LimsSample::STATUS_BOOKED,
                LimsSample::STATUS_COLLECTED,
                LimsSample::STATUS_DISPATCHED,
                LimsSample::STATUS_IN_TRANSIT,
            ];

            $hasInFlight = $statuses->contains(fn ($s) => in_array($s, $inFlight, true));
            $allReceivedOrRejected = $statuses->every(fn ($s) => in_array($s, [
                LimsSample::STATUS_RECEIVED,
                LimsSample::STATUS_REJECTED,
                LimsSample::STATUS_EXPIRED,
                LimsSample::STATUS_PROCESSING,
                LimsSample::STATUS_COMPLETED,
            ], true));

            if ($allReceivedOrRejected) {
                $booking->status = LimsBooking::STATUS_RECEIVED;
            } elseif ($statuses->contains(LimsSample::STATUS_IN_TRANSIT)) {
                $booking->status = LimsBooking::STATUS_IN_TRANSIT;
            } elseif ($statuses->contains(LimsSample::STATUS_DISPATCHED)) {
                $booking->status = LimsBooking::STATUS_DISPATCHED;
            } elseif ($statuses->every(fn ($s) => $s === LimsSample::STATUS_COLLECTED)) {
                $booking->status = LimsBooking::STATUS_COLLECTED;
            } elseif ($statuses->contains(LimsSample::STATUS_COLLECTED)) {
                $booking->status = LimsBooking::STATUS_PARTIALLY_COLLECTED;
            } elseif (! $hasInFlight) {
                $booking->status = LimsBooking::STATUS_RECEIVED;
            }

            $booking->save();
        }
    }

    private function assertCanMutateBatch(LimsSampleBatch $batch, ?User $actor): void
    {
        if ($actor === null) {
            return; // console / smoke
        }
        if ($actor->isSuperAdmin() || $actor->isMainLabScope()) {
            return;
        }
        if ($actor->isCollectionCenterScope()
            && (int) $actor->collection_center_id === (int) $batch->collection_center_id
        ) {
            return;
        }

        throw ValidationException::withMessages([
            'batch' => 'Not allowed to mutate this batch.',
        ]);
    }

    private function assertCanViewBatch(LimsSampleBatch $batch, ?User $actor): void
    {
        if ($actor === null || $actor->isSuperAdmin() || $actor->isMainLabScope()) {
            return;
        }
        if ($actor->isCollectionCenterScope()
            && (int) $actor->collection_center_id === (int) $batch->collection_center_id
        ) {
            return;
        }

        throw ValidationException::withMessages([
            'batch' => 'Not allowed to view this batch.',
        ]);
    }

    private function assertCanReceive(?User $actor): void
    {
        if ($actor === null) {
            return;
        }
        if ($actor->isSuperAdmin() || $actor->isMainLabScope()) {
            return;
        }

        throw ValidationException::withMessages([
            'batch' => 'Only Main Lab can receive sample batches.',
        ]);
    }
}
