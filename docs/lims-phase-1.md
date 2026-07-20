# LIMS Phase 1 — Normalized Bookings & Dual-Write

**Status:** Implemented (additive; does not cut over UI from `laboratory_patients`)  
**Timezone:** `Asia/Karachi`  
**Depends on:** Phase 0 (`docs/lims-phase-0.md`)

## What was added

| Artifact | Purpose |
|----------|---------|
| `lab_number_sequences` | Per-CC monthly sequence (row lock / upsert) |
| `lims_bookings` | Normalized booking header + CC-prefixed `lab_number` |
| `lims_booking_items` | One row per test (from `selected_tests` JSON) |
| `lims_invoices` / `lims_payments` | 1:1 invoice per booking; cash payment when `paid_amount` > 0 |
| `LabNumberAllocator` | Safe lab number allocation (never `MAX+1`) |
| `LimsBookingSync` | Dual-write from legacy `LaboratoryPatient` |
| Models + CC scope | `LimsBooking`, `LimsBookingItem`, `LimsInvoice`, `LimsPayment` |

## Migrations

1. `2026_07_21_130000_create_lims_bookings_tables`
2. `2026_07_21_130001_create_lims_invoices_and_payments_tables`

```bash
php artisan migrate
php artisan db:seed --class=LimsOrganizationSeeder
```

Seeder also ensures `lab_number_sequences` rows for MAIN and CC1 for the current `Asia/Karachi` `YYYYMM`.

## Lab number allocation

Format: `{prefix}-{YYYYMM}-{seq:04d}` e.g. `CC1-202607-0001` / `MAIN-202607-0001`.

```php
use App\Services\Lims\LabNumberAllocator;

$result = app(LabNumberAllocator::class)->allocate($collectionCenterId);
// [
//   'lab_number' => 'CC1-202607-0001',
//   'year_month' => '202607',
//   'seq' => 1,
//   'prefix' => 'CC1',
// ]
```

Under the hood (PostgreSQL, concurrency-safe):

```sql
INSERT INTO lab_number_sequences (collection_center_id, year_month, next_seq)
VALUES ($1, $2, 2)
ON CONFLICT (collection_center_id, year_month)
DO UPDATE SET next_seq = lab_number_sequences.next_seq + 1
RETURNING (next_seq - 1) AS seq;
```

**Note:** Legacy UI still shows daily 2-digit `lab_registration_no`. LIMS `lab_number` is allocated independently during dual-write and stored on `lims_bookings` (bridge via `laboratory_patient_id`).

## Dual-write

### Wired

| Path | Behavior |
|------|----------|
| `BookingController::store` | After `LaboratoryPatient::create`, calls `LimsBookingSync::syncQuietly()` |

`syncQuietly` logs failures and **never** rolls back the legacy booking / Sample Portal redirect.

Sync steps:

1. Resolve tenancy: auth user’s `collection_center_id` if CC scope; else Main Lab (`kind = main_lab`) for the org (default `MMC`).
2. Upsert `lims_patients` by `(organization_id, mr_no)` (allocate MR via `MrNumberAllocator` if missing).
3. Create `lims_bookings` + allocate lab number; or refresh existing row matched by `laboratory_patient_id`.
4. Replace `lims_booking_items` from `selected_tests`.
5. Upsert `lims_invoices` (`invoice_no = INV-{lab_number}`); create/update cash `lims_payments` when `paid_amount > 0` (idempotency key `lp-{id}-initial`).

### Not wired yet (document for later)

| Path | Notes |
|------|-------|
| `Api\CloudSyncController::receiveSyncPayload` | Desktop → cloud upsert of `laboratory_patients` |
| Sample Portal vial / test status updates | Should refresh `lims_booking_items.sample_status` |
| Result Entry / report completion | Booking status → `reported` (P5-ish) |
| Any ad-hoc `LaboratoryPatient` updates outside Booking UI | Call `LimsBookingSync::syncFromLaboratoryPatient()` or `syncQuietly()` |

## Tenancy

- `BelongsToCollectionCenterScope` applied on bookings, items, invoices, payments.
- Dual-write **stamps** `collection_center_id` from auth (CC) or MAIN (Main Lab / unscoped).
- Clients must not supply CC id for CC users (forced from auth in sync).

## Deviations from architecture doc

1. **Tables `lims_bookings` / `lims_booking_items` / `lims_invoices` / `lims_payments`** — `lims_` prefix consistent with Phase 0 `lims_patients`; avoids generic name collisions.
2. **`patient_id` → `lims_patients`** — not legacy HMS `patients`.
3. **`doctor_id` nullable; `refer_by_doctor_name` kept** — `doctors` table is P3; self-ref CHECK relaxed so non-self-referred bookings may lack `doctor_id`.
4. **`test_category_id` nullable, no FK** — no `test_categories` table in this codebase yet.
5. **`laboratory_patient_id` bridge column** — not in architecture; required for dual-write idempotency.
6. **Lab number not written back to `lab_registration_no`** — preserves daily 2-digit UI/receipts until cutover.
7. **Payment soft-replace on amount change** — architecture says write-once amounts; dual-write refresh force-deletes+recreates the initial payment when `paid_amount` changes.

## How to verify

```bash
# Migrations
php artisan migrate

# Seed org + sequences
php artisan db:seed --class=LimsOrganizationSeeder

# Smoke: sequential lab numbers (tinker)
php artisan tinker
>>> $cc = \App\Models\CollectionCenter::where('code','CC1')->first();
>>> $a = app(\App\Services\Lims\LabNumberAllocator::class);
>>> $a->allocate($cc->id);
>>> $a->allocate($cc->id);
# Expect …-0001 then …-0002 for current Asia/Karachi YYYYMM

# Create a pathology booking in the UI, then:
>>> \App\Models\LimsBooking::withoutGlobalScopes()->latest('id')->first();
# Should show laboratory_patient_id, lab_number, items, invoice
```
