# LIMS Phase 4 — Cash Closures & Main Lab Reconciliation

**Status: CANCELLED / REMOVED** — do **not** rebuild this feature.

Cash close / day-end drawers were fully removed from the codebase and database
(`2026_07_21_170000_drop_lims_cash_closures_table`). Historical create migration
`2026_07_21_160000_create_lims_cash_closures_table` may still exist for DBs that
already ran it; the drop migration cleans up.

## What was removed

| Artifact | Notes |
|----------|--------|
| `lims_cash_closures` table | Dropped |
| `cash_closure_status` enum | Dropped |
| `lims_payments.cash_closure_id` | Column + FK dropped |
| Controllers / Service / Model / Policy | Deleted |
| Blade `/pathology/cash-closures` + API `/api/v1/cash-closures*` | Removed |
| LabPermissions `Cash Close` / `Cash Approve` | Removed |
| `scratch/smoke_lims_phase4.php` | Deleted |

## Do not re-implement

Future agents: use Financial Summary / existing billing for cash reporting.
Phase 4 cash close is **out of scope permanently** unless product explicitly
re-requests it.
