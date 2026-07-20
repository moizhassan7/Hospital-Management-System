# LIMS Phase 0 — Tenancy & Global Patients

**Status:** Implemented (additive; does not cut over `laboratory_patients`)  
**Timezone:** `Asia/Karachi`

## What was added

| Artifact | Purpose |
|----------|---------|
| `organizations` | Tenant root (code, name, timezone) |
| `collection_centers` | Sites with `kind` = `main_lab` \| `collection_center`; partial unique: one Main Lab per org |
| `users` columns | `organization_id`, `collection_center_id`, `user_scope`, `deleted_at` |
| `lims_patients` | Global MR registry; unique `(organization_id, mr_no)` |
| `mr_number_sequences` | Safe MR allocator (row lock / upsert) |
| Models / policies / scope stubs | Eloquent + Gate stubs for CC vs Main Lab |
| `LimsOrganizationSeeder` | Default MMC org, MAIN lab, CC1, MR sequence |

## Migrations

1. `2026_07_21_120000_create_lims_organizations_table`
2. `2026_07_21_120001_create_lims_collection_centers_table`
3. `2026_07_21_120002_add_lims_tenancy_to_users_table`
4. `2026_07_21_120003_create_lims_patients_and_mr_sequences_tables`

```bash
php artisan migrate
php artisan db:seed --class=LimsOrganizationSeeder
```

## MR allocation

Never use `MAX(mr_no)+1`. Use `App\Services\Lims\MrNumberAllocator`:

```php
use App\Services\Lims\MrNumberAllocator;

$mrNo = app(MrNumberAllocator::class)->allocate($organizationId);
// returns string e.g. "1", "2", …
```

Under the hood (PostgreSQL, concurrency-safe):

```sql
INSERT INTO mr_number_sequences (organization_id, next_value, updated_at)
VALUES ($1, 2, NOW())
ON CONFLICT (organization_id)
DO UPDATE SET next_value = mr_number_sequences.next_value + 1, updated_at = NOW()
RETURNING (next_value - 1) AS mr_no;
```

Seed with `ensureSequence($organizationId)` when creating an org (seeder does this).

## Tenancy helpers

- `User::isMainLabScope()` / `isCollectionCenterScope()`
- `User::organization()` / `collectionCenter()`
- `BelongsToCollectionCenterScope` — apply later on bookings/samples/etc.; **not** on `LimsPatient`
- Policies: `CollectionCenterPolicy`, `LimsPatientPolicy` (stubs)

**CHECK rule:** Main Lab users must have `collection_center_id IS NULL`; CC users must have a CC id.

## Deviations from architecture doc

1. **Table `lims_patients` instead of `patients`** — legacy migration `create_patients_table` already defines HMS indoor/outdoor `patients` with a different schema. Model: `LimsPatient`.
2. **`users.user_scope` default `main_lab`** — architecture defaulted to `collection_center`, which would violate the scope/CC CHECK for existing users with null `collection_center_id`.
3. **`lims_roles` not created** — Phase 0 keeps existing Role/Permission; role bridge is later.

## Existing pathology

`laboratory_patients` and Sample Portal / Result Entry are unchanged for reads. Dual-write from `BookingController::store` starts in Phase 1 — see `docs/lims-phase-1.md`.
