# UI redesign — route checklist

Order follows the sidebar (Dashboard → Settings). A page is **Done** when it uses the
shared shell, `x-ui.*` components and design-system classes, keeps every field name,
element id, form action and script hook, compiles, and renders without server errors.

Status values: **Done** · **New** (page did not exist before) · **Pending** · **Print** (print/PDF output intentionally unchanged) · **Note** (see column)

## Shell & foundation

| Item | File(s) | Status |
|---|---|---|
| Design tokens, components, print rules | `resources/css/app.css` | Done |
| Shell behaviour (sidebar, menus, dialogs, confirm, palette, filters, tabs, toasts) | `resources/js/ui.js` | Done |
| Layout, sidebar, topbar, flash, palette, confirm | `resources/views/layouts/**` | Done |
| Blade components | `resources/views/components/ui/*` | Done |
| Pagination | `resources/views/vendor/pagination/tailwind.blade.php` | Done |
| Sign in | `login.blade.php` | Done |

## Workspace

| Route | View | Status | Note |
|---|---|---|---|
| `dashboard` | `dashboard.blade.php` | Done | |
| `patients.index` | `patients/index.blade.php` | Done | |
| `patients.register` | `patients/patient_registration.blade.php` | Done | Same view serves edit mode |
| `patients.edit` / `patients.update` | `patients/patient_registration.blade.php` | New | MR number is fixed; age recalculated from date of birth |
| `patients.show` | `patients/show.blade.php` | New | Profile with OPD, admissions, emergency, day care and lab history |
| `patients.create` | — | Note | Redirects to `patients.register` |
| `patients.all` | `patients/all.blade.php` | Done | Rows link to profile and edit |
| `patients.book_appointment` | `patients/book_appointment.blade.php` | Done | Unpaid save stores a provisional token; collecting the fee reserves it |
| `patients.appointments.index` | `patients/appointments/index.blade.php` | New | Appointment desk with payment and token filters |
| `patients.appointments.show` | `patients/appointments/show.blade.php` | New | Collect fee dialog, check-in, reschedule, token reassignment |
| `patients.admission_form` | `patients/admission_form.blade.php` | Done | Prototype: form posts to `#`, marked Preview |
| `patients.birth_certificates` | `patients/birth_certificates.blade.php` | Done | Prototype: sample rows, marked Preview |
| `patients.death_certificates` | `patients/death_certificates.blade.php` | Done | Prototype: sample rows, marked Preview |
| `departments.index` | `departments/index.blade.php` | Done | |
| `departments.add` / `.edit` | `departments/add.blade.php` | Done | |
| `specialities.add` / `.edit` | `specialities/add.blade.php` | Done | |
| `diagnoses.index` / `.edit` | `diagnoses/index.blade.php` | New | Feeds the discharge form's diagnosis search |
| `floors.add` / `.edit` | `floors/add.blade.php` | Done | |
| `rooms.add` / `.edit` | `rooms/add.blade.php` | Done | |
| `doctor_types.add` / `.edit` | `doctor_types/add.blade.php` | Done | |
| `shifts.add` / `.edit` | `shifts/add.blade.php` | Done | |
| `emergency_charges.add` / `.edit` | `emergency_charges/add.blade.php` | Done | |

## Patient care

| Route | View | Status | Note |
|---|---|---|---|
| `patients.outdoor_register` (OPD) | `patients/outdoor_register.blade.php` | Done | Walk-in tokens skip reserved numbers; unpaid bookings open the token confirmation dialog |
| `patients.outdoor_receipt` | `patients/outdoor_receipt.blade.php` | Print | Thermal receipt window |
| `opd.consultation` | `opd/consultation.blade.php` | Done | Prototype: example doctors/fees, marked Preview |
| `patients.indoor_register` | `patients/indoor_register.blade.php` | Done | |
| `patients.discharge` | `patients/discharge.blade.php` | Done | Print slip block unchanged |
| `inpatient.manage` | `inpatient_details/inpatient_details_form.blade.php` | Done | |
| `emergency.index` | `emergency/index.blade.php` | Done | |
| `emergency.patients.create` | `emergency/create_patient.blade.php` | Done | |
| `emergency.patients.all` | `emergency/all_patients.blade.php` | New | |
| `emergency.patient.receipt` | `emergency/receipt.blade.php` | New | Reprintable receipt with the same figures as the JS receipt |
| `emergency.add_service` / `.edit_service` | `emergency/add_service.blade.php` | Done | Not linked from navigation (was hidden in the original hub) |
| `day-care.create` | `day_care/day_care_form.blade.php` | Done | |
| `procedures.index` | `procedures/index.blade.php` | New | Controller existed without routes |
| `procedures.create` / `.edit` | `procedures/create.blade.php` | New | |
| `doctors.index` | `doctors/dashboard.blade.php` | Done | |
| `doctors.create` / `.edit` | `doctors/create.blade.php` | Done | |
| `doctors.all` | `doctors/all.blade.php` | Done | |
| `doctors.login` (doctor portal) | `doctors/login.blade.php` | Done | Authenticates `doctors.email` / password |
| `doctors.dashboard` (doctor portal) | `doctors/doctor_dashboard.blade.php` | Done | Live OPD counts for the signed-in doctor |
| `doctors.write_prescription` | `doctors/write_prescription.blade.php` | Done | Simulated data, marked Preview |

## Diagnostics

| Route | View | Status | Note |
|---|---|---|---|
| `pathology.index` | `pathology/index.blade.php` | Done | |
| `radiology.index` | `radiology/index.blade.php` | Done | |
| `laboratory.index` | `laboratory/index.blade.php` | Done | |
| `*.patient_registration` | `laboratory/patient_registration.blade.php` | Done | |
| `*.result_entry.search` | `laboratory/result_entry.blade.php` | Done | |
| `laboratory.result_entry.show_form` / `.view` | `laboratory/result_entry_form.blade.php` | Done | |
| `laboratory.print_report` | `laboratory/print_report.blade.php` | Print | |
| `*.test_catalog` | `laboratory/test_catalog.blade.php` | Done | |
| `*.manage_test` / `.edit` | `laboratory/manage_test.blade.php` | Done | Quill template editor kept |
| `*.test_head` / `test_head.edit` | `laboratory/manage_test_head.blade.php` | Done | |
| `laboratory.add_test_particulars` | `laboratory/add_test_particulars.blade.php` | Done | Pathology and radiology now have matching routes |
| `laboratory.test_particular_details` | `laboratory/test_particular_details.blade.php` | Done | |
| `lab-attendant.index` | `lab-attendant/index.blade.php` | Done | |

## Operations & finance

| Route | View | Status | Note |
|---|---|---|---|
| `patients.advances` | `patients/advances/index.blade.php` | Pending | |
| `store.index` | `store/index.blade.php` | Done | |
| `store.consumable_items` / `.edit` | `store/consumable_items.blade.php` | Done | Quick-add dialogs kept (they still call `/api/categories` and `/api/locations`, which have no routes — use Categories & locations) |
| `store.non_consumable_items` / `.edit` | `store/non_consumable_items.blade.php` | Done | Same quick-add dialogs as consumables |
| `store.definitions` | `store/definitions.blade.php` | New | Categories & locations (item pages keep their quick-add dialogs) |
| `store.purchase_stock` | `store/purchase_stock.blade.php` | Pending | UI still old; JSON endpoints now persist real stock |
| `store.issue_stock` | `store/issue_stock.blade.php` | Pending | UI still old; search uses `/api/issue/*` against staff and items |
| `store.return_stock` | `store/return_stock.blade.php` | Pending | UI still old; returns outstanding assets |
| `store.supplier` / `.create` / `.edit` | `store/supplier.blade.php` | Done | Routes now use `SupplierController@add` |
| `finance.index` | `finance/index.blade.php` | Done | |
| `finance.vouchers.index` | `finance/vouchers/index.blade.php` | Done | |
| `finance.vouchers.create` | `finance/vouchers/create.blade.php` | Done | Original preview IIFE kept (type badge, doctor balance, CR/CP/BR/BP/CV) |
| `finance.vouchers.show` | `finance/vouchers/show.blade.php` | Done | Receive / reject / void field names unchanged |
| `finance.ledger.index` | `finance/ledger/index.blade.php` | Done | |
| `finance.reports.trial_balance` | `finance/reports/trial-balance.blade.php` | Done | |
| `finance.accounts.index` | `finance/accounts/index.blade.php` | Done | Mapping select ids remain `mapping-{id}` |
| `finance.categories.index` | `finance/categories/index.blade.php` | Done | System categories keep hidden `default_type`, `account_id`, `is_active` |

## Administration

| Route | View | Status | Note |
|---|---|---|---|
| `index` (reports hub) | `reports/index.blade.php` | Done | Permission-gated voucher/ledger/trial cards kept |
| `reports.indoor_patient_summary` | `reports/indoor_patient_summary.blade.php` | Done | |
| `reports.opd_summary` | `reports/opd_summary.blade.php` | Done | |
| `reports.indoor_discharge_history` | `reports/indoor_discharge_history.blade.php` | Done | |
| `reports.indoor_discharge_payment` | `reports/indoor_discharge_payment.blade.php` | Done | |
| `reports.doctor_shares` | `reports/doctor_shares.blade.php` | Done | Filter id `procedure_id` kept |
| `reports.hospital_shares` | `reports/hospital_shares.blade.php` | Done | Filter id `procedure_id` kept |
| `reports.revenue_doctors` | `reports/revenue_doctor.blade.php` | Done | `doctor_id` remains required |
| `reports.revenue_hospital` | `reports/revenue_hospital.blade.php` | Done | |
| `*.download` (PDF) | `reports/*_pdf.blade.php` | Print | DomPDF templates |
| `admin.user_manager` / `.edit` | `users/manager.blade.php` | Done | |
| `admin.roles.index` | `roles/index.blade.php` | Done | |
| `admin.roles.create` / `.edit` | `roles/create.blade.php` | Done | |
| `admin.settings.index` | `settings/index.blade.php` | Done | |

## Backend added for the new pages

New routes require sign-in (`auth` middleware); existing routes are unchanged.

- `DiagnosisController` (list, add, edit, delete) and `Diagnosis::$fillable`.
- `PatientController@searchDiagnosis` — the `/api/search-diagnosis` route already pointed to it; matching is case-insensitive.
- `PatientController@show` and `@update`; `patients.edit` reuses `@register`, which already accepted a patient.
- `StoreDefinitionController` for categories and locations. Deleting either keeps the items (the foreign keys are `ON DELETE SET NULL`).
- Routes for the existing `ProcedureController`.
- Supplier routes pointed at the existing `SupplierController@add`.
- `pathology.add_test_particulars` and `radiology.add_test_particulars` so the particulars form can redirect after save.

## Not routed (left as-is)

`dbconn.blade.php`, `day_care/create.blade.php`, `day_care/edit.blade.php`,
`day_care/index.blade.php`, `day_care/modals.blade.php`, `day_care/scripts.blade.php`
are not reachable from any route (`day_care/edit` also includes a view that does not exist).

## Pre-existing issues (now fixed)

- Indoor register and book appointment (and the unused day-care scripts partial) look up patients at `/api/get-by-mr-no/{mr}`.
- Day care save/print: `showStatusMessage` is defined; Print receipt calls `saveForm(true)` and then prints.
- Inpatient details Print receipt uses consultant-visit and payment totals instead of missing bill inputs.
- Store purchase / issue / return JSON routes persist movements. Item keys are `C-{id}` / `N-{id}`; on-hand stock is purchased − issued + returned. Issue/return search uses staff, doctors and catalogue items instead of sample rows.
- Emergency, day care and discharge consultant lists are decoded before save (`JsonList` cast), so they are no longer double-encoded.
- Procedure types are Major/Minor and performed-for is General/Male/Female. A migration remaps the old seed values; the seeder writes the allowed values.
- Dashboard upcoming appointments, patients-by-department and doctors-by-department come from OPD / indoor / doctor records. Occupied beds count only patients who are not discharged.
- Doctor portal authenticates against `doctors.email` / `doctors.password` (`doctor` guard). Set portal credentials on the doctor form. Dashboard and write-prescription require that session. Seeded `DOC001` gets `doctor@example.com` / `password` when it has no email yet.
- `reports.indoor_discharge_payment.download` renders `reports/indoor_discharge_payment_pdf.blade.php`.

## Resolved while adding missing pages

- `store.supplier` rendered a view that did not exist, `create`/`edit` called missing controller methods,
and delete posted to `/store/supplier/{id}` instead of `/supplier/{id}`.
- `emergency.all_patients` and `emergency.receipt` views did not exist.
- `patients.create`, `patients.edit`, `patients.show` returned "Coming Soon" strings.
- `/api/search-diagnosis` pointed to a method that did not exist, so the discharge diagnosis search failed.
