# LIMS Phase 2 — Sample Batches & Transit

**Status:** Implemented (additive; Sample Portal / Lab Attendant / Result Entry unchanged for primary UX)  
**Timezone:** `Asia/Karachi`  
**Depends on:** Phase 0 + Phase 1

## What was added

| Artifact | Purpose |
|----------|---------|
| `lims_samples` | Normalized sample/vial row; bridge `lab_sample_vial_id` |
| `lims_sample_batches` | Manifest / batch header (open → dispatched → in_transit → received) |
| `lims_sample_batch_items` | Batch membership + per-item receive status |
| `lims_transit_events` | Append-only transit audit (idempotent keys) |
| `lims_manifest_sequences` | Safe daily manifest allocator (never `MAX+1`) |
| `ManifestNumberAllocator` | `{prefix}-M-{YYYYMMDD}-{seq:04d}` |
| `LimsSampleSync` | Dual-write vial ↔ `lims_samples`; push receive → legacy `in_lab` |
| `SampleTransitService` | Transit state machine + idempotent dispatch/in-transit/receive |
| API `/api/v1/sample-batches*` | REST transit workflow (session auth; Sanctum not installed) |
| `LimsSampleBatchPolicy` | CC = own center; Main Lab = view/receive globally |

## Migrations

1. `2026_07_21_140000_create_lims_samples_and_batches_tables`

```bash
php artisan migrate
php artisan db:seed --class=LimsOrganizationSeeder
```

Enums (PostgreSQL): reuses Phase 1 `sample_status`; adds `batch_status`, `transit_event_type`.

## Manifest allocation

```php
use App\Services\Lims\ManifestNumberAllocator;

$result = app(ManifestNumberAllocator::class)->allocate($collectionCenterId);
// [
//   'manifest_no' => 'CC1-M-20260721-0001',
//   'business_date' => '20260721',
//   'seq' => 1,
//   'prefix' => 'CC1',
// ]
```

## Transit API (`/api/v1`)

Auth: session cookie (same browser login as web app). Header `Idempotency-Key` or body `idempotency_key` on mutating POSTs.

| Method | Path | Actor | Purpose |
|--------|------|-------|---------|
| `GET` | `/sample-batches` | scoped | List (status / CC / date filters) |
| `POST` | `/sample-batches` | CC | Create open batch |
| `GET` | `/sample-batches/{id}` | CC or Main | Manifest + items + events |
| `POST` | `/sample-batches/{id}/items` | CC | Add collected sample (`sample_id` or `barcode`) |
| `DELETE` | `/sample-batches/{id}/items/{sampleId}` | CC | Remove before dispatch |
| `POST` | `/sample-batches/{id}/dispatch` | CC | Seal → dispatched |
| `POST` | `/sample-batches/{id}/in-transit` | CC/Main | Scan → in_transit |
| `POST` | `/sample-batches/{id}/receive` | Main Lab | Per-item received / missing / rejected |
| `GET` | `/sample-batches/{id}/events` | scoped | Transit event audit |

**Out of scope (P5):** `report-ready`, notification outbox / realtime.

## Dual-write / legacy bridge

### Wired

| Path | Behavior |
|------|----------|
| `SamplePortalController` collect / vial status | On `collected` (and later statuses), `LimsSampleSync::syncQuietly` |
| `LabAttendantController::scanBarcode` | After legacy `in_lab`, syncs `lims_samples` |
| `SampleTransitService::receive` | On item `received`, pushes vial → `in_lab` when safe; on `rejected`, sets legacy rejected |

Sync ensures `lims_bookings` exists (calls `LimsBookingSync` if needed), then upserts `lims_samples` by `lab_sample_vial_id`.

### Partial / not wired

| Path | Notes |
|------|-------|
| Dispatch/in-transit UI | No Blade/Inertia batch UI yet — API + services only |
| Legacy Lab Attendant receive | Still allows collected → in_lab **without** a batch (single-site path preserved) |
| `sample_tests` junction | Deferred; booking_item sample_status advanced via vial `test_ids` when present |
| Missing items | Legacy vial stays `collected` so CC can re-batch later |
| Shared `test_ids` across vials | Receive bridge updates **only** the matched vial (skips `syncVialStatusesForTests` cascade) |

## Tenancy

- `BelongsToCollectionCenterScope` on `LimsSample`, `LimsSampleBatch`
- Policy: CC mutates own batches; Main Lab receives any; Super Admin unrestricted
- Service layer mirrors policy for console/smoke (`actor === null` allowed)

## Deviations from architecture doc

1. **Tables `lims_*` prefix** — consistent with P0/P1.
2. **`lab_sample_vial_id` bridge** — required for dual-write idempotency.
3. **No Sanctum** — session auth middleware on `/api/v1` instead.
4. **No `sample_tests` table yet** — booking_item updates use vial `test_ids` when bridged.
5. **Active sample uniqueness** — partial unique on `receive_status = pending` (not a join to batch status); terminal receive frees the sample for re-batch (missing / re-ship).
6. **Create-batch idempotency** — via `transit_events(created)` key, not `sample_batches.idempotency_key` (reserved for dispatch).
7. **No transit UI** — Sample Portal still collected → (optional) Lab Attendant in_lab; hub-spoke transit is API-first for P2.

## How to verify

```bash
php artisan migrate
php artisan db:seed --class=LimsOrganizationSeeder
php scratch/smoke_lims_phase2.php
```

Expected smoke: create booking → sync samples as collected → open batch → add → dispatch → in-transit → partial receive (one received, one missing/rejected) → legacy vial `in_lab` for received only.
