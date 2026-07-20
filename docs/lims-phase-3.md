# LIMS Phase 3 — Commission Rules, Snapshots & Doctor Ledger

**Status:** Implemented (additive; booking UI unchanged)  
**Timezone:** `Asia/Karachi`  
**Depends on:** Phase 0–2

## What was added

| Artifact | Purpose |
|----------|---------|
| `lims_doctors` | Referring doctors (separate from HMS clinical `doctors`) |
| `lims_test_categories` | Minimal categories for rule matching |
| `lims_commission_rules` | Fixed/percent rules; effective dates; optional CC/doctor/test scope |
| `lims_commission_snapshots` | Immutable commission amounts at booking time |
| `lims_doctor_ledgers` + `lims_ledger_entries` | Running balance + credit/debit/clawback |
| `lims_doctor_payouts` | Payout DEBIT (no recompute) |
| `CommissionRuleResolver` | Most-specific rule + find/create doctor by name |
| `CommissionSnapshotService` | Snapshot + CREDIT; cancel clawback |
| `DoctorPayoutService` | Payout DEBIT with idempotency |
| API `/api/v1` commission + ledger endpoints | Session auth (same as P2) |
| Policies | Main Lab / Commission Admin for rules & payouts; CC scoped snapshot read |

## Migrations

1. `2026_07_21_150000_create_lims_commission_tables`

```bash
php artisan migrate
php artisan db:seed --class=LimsOrganizationSeeder
```

Enums (PostgreSQL): `commission_basis`, `ledger_entry_type`, `ledger_ref_type`; reuses `payment_method` for payouts.

Also adds: `tests.test_category_id` FK; `lims_bookings.doctor_id` → `lims_doctors`; `lims_booking_items.test_category_id` FK.

## Critical invariants

- Snapshot at booking sync / confirm; **never** recalculate `commission_amount` later
- Rule edits only affect future bookings (`effective_from` / `booked_at`)
- Ledger idempotency keys: `credit:snapshot:{booking_item_id}`, `clawback:snapshot:{id}`
- Cancel after payout may drive balance **negative** (no block)

## Dual-write integration (`LimsBookingSync`)

On create/refresh of a non-self-referred booking:

1. Resolve/create `lims_doctors` from `refer_by_doctor_name`
2. Set `lims_bookings.doctor_id`
3. After items + invoice upsert, call `CommissionSnapshotService::snapshotBooking()` **in the same TX**
4. Existing snapshots for an item are left untouched (write-once)

Cancel: `POST /api/v1/bookings/{id}/cancel` → status `cancelled` + clawback frozen amounts.

## Commission API (`/api/v1`)

Auth: session cookie (same browser login). Header `Idempotency-Key` on payouts.

| Method | Path | Actor | Purpose |
|--------|------|-------|---------|
| `GET` | `/commission-rules` | Main Lab / Commission Admin | List / filter rules |
| `POST` | `/commission-rules` | Commission Admin | Create rule |
| `PUT` | `/commission-rules/{id}` | Commission Admin | Update **future** applicability only |
| `POST` | `/commission-rules/{id}/deactivate` | Commission Admin | Soft-end (`effective_to=today`, inactive) |
| `GET` | `/commission-snapshots` | scoped | Audit frozen amounts (CC = own center) |
| `POST` | `/bookings/{id}/finalize-invoice` | CC/Main | Ensure snapshots exist (no recompute) |
| `POST` | `/bookings/{id}/cancel` | CC/Main | Cancel + clawback ledger |
| `GET` | `/doctors/{id}/ledger` | Main / payout admin | Entries + balance |
| `POST` | `/doctors/{id}/payouts` | Main Lab | Debit ledger |
| `GET` | `/doctors/{id}/payouts` | Main Lab | Payout history |

**Out of scope:** `POST /bookings` create API — primary create remains Booking UI → `LimsBookingSync` (snapshots in same TX). Cash closures = P4.

## Wired

| Path | Behavior |
|------|----------|
| `LimsBookingSync` create/refresh | Doctor find/create + snapshot + CREDIT |
| `BookingController::store` | Unchanged; still calls `syncQuietly` → snapshots |

## Partial / not wired

| Path | Notes |
|------|-------|
| Commission admin UI | API + services only |
| Permission seed for `Commission Admin` / `Doctor Payout` | Constants added to `LabPermissions`; assign via existing permission UI |
| Cloud sync desktop bookings | Still unwired for dual-write (P1 note) |
| HMS clinical `doctors` table | Unrelated; LIMS uses `lims_doctors` |

## Deviations from architecture doc

1. **Tables `lims_*` prefix** — consistent with P0–P2; `lims_doctors` avoids collision with HMS `doctors`.
2. **No Sanctum** — session auth on `/api/v1`.
3. **No dedicated `POST /bookings`** — dual-write path is the create authority; finalize-invoice is the safety net.
4. **Zero-commission** — snapshot still written (`commission_amount=0`); ledger CREDIT skipped when amount ≤ 0.
5. **`lab_share_total` / `hospital_share_total`** — left at 0 on legacy rows; not dual-written into snapshots.

## How to verify

```bash
php artisan migrate
php artisan db:seed --class=LimsOrganizationSeeder
php scratch/smoke_lims_phase3.php
```

Expected smoke: rule → book → snapshot+credit → rule edit leaves old snapshot unchanged → cancel clawback → new book uses new rule → payout DEBIT.
