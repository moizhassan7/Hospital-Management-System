# LIMS Phase 4 — Cash Closures & Main Lab Reconciliation

**Status:** Implemented (additive; booking/payment UI unchanged)  
**Timezone:** `Asia/Karachi`  
**Depends on:** Phase 0–3

## What was added

| Artifact | Purpose |
|----------|---------|
| `lims_cash_closures` | Per CC / business_date / shift drawer; open → submitted → locked |
| `cash_closure_status` enum | `open`, `submitted`, `approved`, `rejected`, `locked` |
| FK `lims_payments.cash_closure_id` | Stamp payments to the open shift |
| `LimsCashClosure` + CC scope | Model + tenancy |
| `CashClosureService` | Open, summary, submit (variance), approve/lock |
| API `/api/v1/cash-closures*` | Session auth (same as P2/P3) |
| `LimsCashClosurePolicy` | CC own center; Main Lab global approve |
| LabPermissions `Cash Close` / `Cash Approve` | Constants for assignment via permission UI |

## Migrations

1. `2026_07_21_160000_create_lims_cash_closures_table`

```bash
php artisan migrate
php artisan db:seed --class=LimsOrganizationSeeder
```

Adds: `lims_cash_closures`; FK + index on `lims_payments.cash_closure_id` (column existed since P1).

**Soft constraint:** partial unique index — at most one `status=open` row per `collection_center_id`.

**Hard unique:** `(collection_center_id, business_date, shift_label)`.

## Business rules (§6.4 + edge case 17)

1. `business_date` = calendar date in `Asia/Karachi` at open time.
2. Open shift → payments created while open stamp `cash_closure_id`.
3. Pre-close summary: cash/card/other totals, booking counts, **due_total** (close allowed with unsettled due), optional commission credits (informational).
4. Submit with `counted_cash_total` → `variance_cash = counted − system_cash`; status `submitted`.
5. Main Lab approve → status **`locked`** (records `approved_at` / `approved_by`).
6. **Post-lock payment policy:** further payments that day do **not** auto-attach. `cash_closure_id` stays null until CC opens the next shift. Next open on the same business_date uses shift labels `day`, then `day-2`, `day-3`, … (or an explicit `shift_label`). Next calendar day starts again at `day`.

## Dual-write integration (`LimsBookingSync`)

On `createPayment`, resolves `CashClosureService::openClosureIdForCenter(cc)` and stamps `cash_closure_id` when a shift is open. Refresh that force-replaces a payment re-stamps against the **current** open shift (or null if locked).

## Cash closure API (`/api/v1`)

Auth: session cookie. Header `Idempotency-Key` on open/submit when desired.

| Method | Path | Actor | Purpose |
|--------|------|-------|---------|
| `POST` | `/cash-closures/open` | CC (Cash Close) | Open shift (idempotent if already open / same key) |
| `GET` | `/cash-closures/current` | CC | Current open row (or null) |
| `GET` | `/cash-closures/summary` | CC | Pre-close summary for current open |
| `GET` | `/cash-closures/{id}/summary` | scoped | Summary for a specific closure |
| `POST` | `/cash-closures/{id}/submit` | CC | Counted cash → variance → submitted |
| `GET` | `/cash-closures?business_date=` | Main / scoped | List by date (variance / unapproved) |
| `GET` | `/cash-closures/{id}` | scoped | Variance detail view |
| `POST` | `/cash-closures/{id}/approve` | Main Lab | Approve → locked |

**Out of scope (P5):** report-ready, outbox, realtime. No cash UI screens in this phase.

## Deviations from architecture doc

1. **Tables `lims_*` prefix** — consistent with P0–P3.
2. **No Sanctum** — session auth on `/api/v1`.
3. **`opened_by` + `due_total` columns** — architecture SQL omitted `opened_by`; both added for audit and edge case 17.
4. **One open per CC** (partial unique on `collection_center_id`) — stronger than architecture’s open index on `(cc, date, shift)`, which duplicated the hard unique.
5. **Approve → `locked`** directly (enum still includes `approved` for future / reject flows).
6. **No reject API** in P4 — status exists in enum only.

## How to verify

```bash
php artisan migrate
php artisan db:seed --class=LimsOrganizationSeeder
php scratch/smoke_lims_phase4.php
```

Expected smoke: open → paid booking stamps closure → summary totals → submit variance → Main Lab approve/lock → new payment has null `cash_closure_id` until next open (`day-2`).
