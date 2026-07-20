# LIMS UI (Blade)

**Status:** Booking CC selection + Collection Centers + Doctors/Commission + **Sample Transit**  
**Timezone:** `Asia/Karachi`  
**Stack:** Server-rendered Blade under existing `layouts.app` / HMS sidebar patterns  
**APIs:** Phase 0–3 JSON APIs remain; UI calls models/services directly via web controllers

See also: `docs/lims-hub-spoke-architecture.md`, `docs/lims-phase-0.md` … `docs/lims-phase-3.md`  
**Phase 4 cash close:** **cancelled/removed** — see `docs/lims-phase-4.md`. Do not rebuild.

---

## Routes & access

Auth: session login + `EnsureModuleAccess`. Controllers also call `$this->authorize(...)`.

| Route name | URL | Who |
|------------|-----|-----|
| `pathology.bookings.create` | `GET /pathology/bookings/create` | `Create Booking` |
| `pathology.bookings.store` | `POST /pathology/bookings` | `Create Booking` |
| `pathology.collection_centers.index` | `GET /pathology/collection-centers` | Main Lab scope **or** `Manage Collection Centers` **or** Super Admin |
| `pathology.collection_centers.create` / `.store` | create form / POST | same |
| `pathology.collection_centers.edit` / `.update` | edit / PUT | same |
| `pathology.collection_centers.toggle` | `POST .../toggle` | activate/deactivate (Main Lab site cannot deactivate) |
| `pathology.lims_doctors.index` | `GET /pathology/lims-doctors` | Main Lab **or** `Commission Admin` **or** Super Admin |
| `pathology.lims_doctors.create` / `.store` / `.edit` / `.update` | CRUD | same |
| `pathology.lims_doctors.ledger` | `GET .../ledger` | Main Lab **or** Commission Admin / Doctor Payout |
| `pathology.lims_doctors.payout` | `POST .../payouts` | Main Lab **or** `Doctor Payout` / `Commission Admin` (`LimsDoctorPolicy::payout`) |
| `pathology.commission_rules.*` | `/pathology/commission-rules` | Main Lab **or** `Commission Admin` |
| `pathology.commission_snapshots.index` | `GET /pathology/commission-snapshots` | same (read-only list) |
| `pathology.sample_batches.index` | `GET /pathology/sample-batches` | CC / Main Lab scope **or** Sample Collection **or** Lab Attendant |
| `pathology.sample_batches.create` / `.store` | create open batch | same (+ policy `create`) |
| `pathology.sample_batches.show` | `GET /pathology/sample-batches/{id}` | own CC (CC user) or all (Main Lab) |
| `pathology.sample_batches.items.store` | `POST .../items` | CC of batch (open only) |
| `pathology.sample_batches.items.destroy` | `DELETE .../items/{sampleId}` | CC of batch (open only) |
| `pathology.sample_batches.dispatch` | `POST .../dispatch` | CC of batch → status **dispatched** |
| `pathology.sample_batches.in_transit` | `POST .../in-transit` | CC or Main Lab → **in_transit** |
| `pathology.sample_batches.receive` | `POST .../receive` | **Main Lab only** → per-item received / missing / rejected |

**Implied Main Lab access:** `EnsureModuleAccess` treats Main Lab `user_scope` as satisfying `Commission Admin`, `Manage Collection Centers`, and `Doctor Payout` (mirrors policies). Sample transit uses `LabPermissions::canAccessSampleTransit()`. Referring-doctor ledger/payout also allows bare `Doctor Payout` (CRUD still needs Commission Admin / Main Lab).

Re-seed permissions after deploy so `Manage Collection Centers` exists:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

Run custody stamp migration when deploying (idempotent column adds). Note: filename shares timestamp `150000` with commission tables — both already exist; do not rename ran migrations:

```bash
php artisan migrate
# 2026_07_21_150000_add_custody_actor_name_stamps
# 2026_07_21_150000_create_lims_commission_tables
```

---

## Sample transit — status matrix (CC vs Main Lab)

| Status | Meaning | Who sets it | Where in UI |
|--------|---------|-------------|-------------|
| **open** | Manifest being packed | **CC** (create batch, add/remove samples) | New batch + batch detail |
| **dispatched** | Left CC with courier | **CC** (dispatch: **required** courier name/ref + `dispatched_by`) | Batch detail → Dispatch |
| **in_transit** | Confirmed on the way | **CC or Main Lab** | Batch detail → Mark in transit |
| **received** (batch) | Intake complete at Main Lab | **Main Lab** | Batch detail → Receive |
| **received** (item) | Sample physically OK | **Main Lab** (per-item) | Receive form |
| **missing** (item) | Not found at receive | **Main Lab** (per-item) | Receive form |
| **rejected** (item) | Rejected at receive | **Main Lab** (per-item + note) | Receive form |

### Chain-of-custody actor rules (WHO)

Actors default to the **logged-in user** at the action time. Display names are snapshotted so renames do not rewrite history. No free-text override for collector / dispatcher / receiver (auth stamp only). Courier is a separate person/carrier name entered on dispatch.

| Step | Stored on | Fields | UI |
|------|-----------|--------|-----|
| **Collected** | `lims_samples` | `collected_by`, `collected_by_name` | Sample Portal vials table (**Collected by**); batch sample list; receive form |
| **Dispatched (who sent)** | `lims_sample_batches` + transit event | `dispatched_by`, `dispatched_by_name` | Batch **Chain of custody** card; dispatch form shows “Sent by (you)”; timeline |
| **Who took** | `lims_sample_batches` + event payload | `courier_name` (**required**), `courier_ref` (optional) | Dispatch form; custody card; batch index **Custody** column |
| **In transit** | `lims_transit_events` | `actor_user_id`, `actor_name` | Event timeline (actor name on every event) |
| **Received** | batch + samples | `received_by`, `received_by_name` | Custody card; timeline |
| **Item marked** | `lims_sample_batch_items` | `receive_marked_by`, `receive_marked_by_name` | Sample list “Marked by …” under receive badge |

**Stamping points in code:**

1. Collect — `LimsSampleSync::syncFromVial` via Sample Portal / Lab Attendant (first collect only; keeps existing stamp on refresh).
2. Dispatch — `SampleTransitService::dispatch` (requires `courier_name`).
3. In transit / receive — `SampleTransitService::markInTransit` / `receive` (batch + per-item markers).
4. Timeline — `SampleTransitService::appendEvent` always writes `actor_user_id` + `actor_name`.

Legacy **Sample Portal** collect + **Lab Attendant** single-vial scan remain; multi-CC custody uses Sample Batches (`/pathology/sample-batches`).

---

## Doctor payout (ledger)

On `GET /pathology/lims-doctors/{id}/ledger`, users with payout permission see a **Record payout** form (amount, method, notes) → `POST .../payouts` → `DoctorPayoutService` (same DEBIT path as the API). Ledger entries refresh after redirect. Negative balances after clawback-post-payout are allowed (warning shown).

---

## Report print custody

Printed pathology reports (`partials/pathology-report-header`) show:

| Label | Source |
|-------|--------|
| **Collected By** | Unique `lims_samples.collected_by_name` for the patient's `lims_bookings` row (`laboratory_patient_id`) |
| **Received By** | Unique `lims_samples.received_by_name` (Main Lab receive stamp) |
| **Printed By** | Existing result-entered / auth name |

Missing names → `—`. Multiple distinct actors → `FirstName (+N)`. Resolved in `PathologyReportService::buildReportData` (covers single print, PDF, print-all, front desk, online view).

---

## Booking → Collection Center

1. **CC-scoped user:** center is locked from `auth()->user()->collection_center_id` (read-only + hidden input).
2. **Main Lab / global:** required dropdown of **active** `collection_centers`.
3. On store, `BookingController` resolves CC id and passes it to `LimsBookingSync::syncQuietly($patient, $user, $collectionCenterId)`.
4. Sync **always** forces CC users onto their own center; Main Lab uses the selected id (active, same org); otherwise falls back to Main Lab site.
5. Post-booking redirect: Sample Portal + flash **Next: collect → add to Sample Batch → dispatch**.

Legacy `laboratory_patients` booking path is enhanced in place (not a separate SPA).

---

## Navigation

Sidebar **Lab workflow**:

- Sample Portal
- **Sample Transit** (batches)

Sidebar **LIMS setup** (when permitted):

- Collection Centers
- Referring Doctors
- Commission Rules

Pathology hub sections: Booking & collection → Sample transit → Results & print → Reports → LIMS network → Administration.

---

## Not in this UI pass / API-only leftovers

- Phase 5 realtime notify / outbox
- Deprecating JSON `selected_tests` (Phase 5)
- Cash close / day-end — **removed** (do not rebuild; see `docs/lims-phase-4.md`)
- Booking **cancel + clawback** — `POST /api/v1/bookings/{id}/cancel` only
- **Finalize invoice** safety net — `POST /api/v1/bookings/{id}/finalize-invoice` only
- Dedicated payout **history list** Blade (ledger DEBIT rows cover recent payouts; `GET /api/v1/doctors/{id}/payouts` remains)
