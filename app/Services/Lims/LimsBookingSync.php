<?php

namespace App\Services\Lims;

use App\Models\CollectionCenter;
use App\Models\LaboratoryPatient;
use App\Models\LimsBooking;
use App\Models\LimsBookingItem;
use App\Models\LimsDoctor;
use App\Models\LimsInvoice;
use App\Models\LimsPatient;
use App\Models\LimsPayment;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Dual-write bridge: LaboratoryPatient (legacy) → normalized LIMS booking/invoice rows.
 *
 * Additive only — never mutates laboratory_patients. Failures are logged and
 * rethrown when called inside an explicit caller transaction; BookingController
 * wraps sync so a sync failure does not roll back the legacy booking.
 */
class LimsBookingSync
{
    public function __construct(
        private readonly LabNumberAllocator $labNumberAllocator,
        private readonly MrNumberAllocator $mrNumberAllocator,
        private readonly CommissionRuleResolver $commissionRuleResolver,
        private readonly CommissionSnapshotService $commissionSnapshotService,
    ) {}

    /**
     * Create or refresh LIMS rows for a legacy laboratory_patients booking.
     *
     * @param  int|null  $collectionCenterId  Explicit CC for Main Lab / global bookers.
     *                                        Ignored for CC-scoped users (always their own CC).
     * @param  int|null  $preferredDoctorId   When set (and not self-referred), link this lims_doctors row.
     */
    public function syncFromLaboratoryPatient(
        LaboratoryPatient $patient,
        ?User $actor = null,
        ?int $collectionCenterId = null,
        ?int $preferredDoctorId = null,
    ): LimsBooking {
        return DB::transaction(function () use ($patient, $actor, $collectionCenterId, $preferredDoctorId) {
            [$organization, $collectionCenter] = $this->resolveTenancy($actor, $collectionCenterId);

            $limsPatient = $this->upsertLimsPatient($patient, $organization, $collectionCenter, $actor);

            $existing = LimsBooking::withoutGlobalScopes()
                ->where('laboratory_patient_id', $patient->id)
                ->first();

            if ($existing !== null) {
                return $this->refreshBooking(
                    $existing,
                    $patient,
                    $limsPatient,
                    $collectionCenter,
                    $actor,
                    $preferredDoctorId,
                );
            }

            return $this->createBooking(
                $patient,
                $limsPatient,
                $organization,
                $collectionCenter,
                $actor,
                $preferredDoctorId,
            );
        });
    }

    /**
     * @return array{0: Organization, 1: CollectionCenter}
     */
    public function resolveTenancy(?User $actor = null, ?int $collectionCenterId = null): array
    {
        $actor ??= auth()->user();

        $organization = null;
        $collectionCenter = null;

        if ($actor instanceof User) {
            if ($actor->organization_id) {
                $organization = Organization::query()->find($actor->organization_id);
            }

            // CC-scoped users are always locked to their assigned center.
            if ($actor->isCollectionCenterScope() && $actor->collection_center_id) {
                $collectionCenter = CollectionCenter::query()->find($actor->collection_center_id);
            }
        }

        $organization ??= Organization::query()->where('code', 'MMC')->first()
            ?? Organization::query()->orderBy('id')->first();

        if ($organization === null) {
            throw new RuntimeException(
                'No LIMS organization found. Run LimsOrganizationSeeder before dual-write.'
            );
        }

        // Main Lab / global: honour explicit booking-time CC selection.
        if ($collectionCenter === null && $collectionCenterId) {
            $collectionCenter = CollectionCenter::query()
                ->where('organization_id', $organization->id)
                ->where('id', $collectionCenterId)
                ->where('is_active', true)
                ->first();
        }

        if ($collectionCenter === null) {
            // Fallback: book against the MAIN site.
            $collectionCenter = CollectionCenter::query()
                ->where('organization_id', $organization->id)
                ->where('kind', CollectionCenter::KIND_MAIN_LAB)
                ->first();
        }

        if ($collectionCenter === null) {
            throw new RuntimeException(
                "No Main Lab collection center for organization {$organization->id}."
            );
        }

        return [$organization, $collectionCenter];
    }

    private function upsertLimsPatient(
        LaboratoryPatient $patient,
        Organization $organization,
        CollectionCenter $collectionCenter,
        ?User $actor,
    ): LimsPatient {
        $mrNo = trim((string) ($patient->mr_no ?? ''));

        if ($mrNo === '') {
            $mrNo = $this->mrNumberAllocator->allocate($organization->id);
        }

        $gender = $this->normalizeGender($patient->gender);

        $existing = LimsPatient::query()
            ->where('organization_id', $organization->id)
            ->where('mr_no', $mrNo)
            ->first();

        if ($existing !== null) {
            $existing->fill([
                'full_name' => trim((string) $patient->patient_name),
                'gender' => $gender,
                'age_years' => $patient->age !== null ? (int) $patient->age : null,
                'contact_no' => $patient->contact_no ? trim((string) $patient->contact_no) : null,
            ]);
            $existing->save();

            return $existing;
        }

        return LimsPatient::query()->create([
            'organization_id' => $organization->id,
            'mr_no' => $mrNo,
            'full_name' => trim((string) $patient->patient_name),
            'gender' => $gender,
            'age_years' => $patient->age !== null ? (int) $patient->age : null,
            'contact_no' => $patient->contact_no ? trim((string) $patient->contact_no) : null,
            'created_at_cc_id' => $collectionCenter->id,
            'created_by' => $actor?->id,
        ]);
    }

    private function createBooking(
        LaboratoryPatient $patient,
        LimsPatient $limsPatient,
        Organization $organization,
        CollectionCenter $collectionCenter,
        ?User $actor,
        ?int $preferredDoctorId = null,
    ): LimsBooking {
        $allocated = $this->labNumberAllocator->allocate($collectionCenter->id);

        $selfReferred = (bool) $patient->self_referred;
        $doctorName = $selfReferred
            ? null
            : ($patient->refer_by_doctor_name ? trim((string) $patient->refer_by_doctor_name) : null);

        $doctorId = null;
        $isSelf = $selfReferred || $doctorName === null;
        if (! $isSelf) {
            $doctorId = $this->resolveDoctorId((int) $organization->id, $doctorName, $preferredDoctorId);
        }

        $booking = LimsBooking::withoutGlobalScopes()->create([
            'organization_id' => $organization->id,
            'collection_center_id' => $collectionCenter->id,
            'patient_id' => $limsPatient->id,
            'doctor_id' => $doctorId,
            'refer_by_doctor_name' => $doctorName,
            'self_referred' => $isSelf,
            'lab_number' => $allocated['lab_number'],
            'lab_number_year_month' => $allocated['year_month'],
            'lab_number_seq' => $allocated['seq'],
            'status' => LimsBooking::STATUS_BOOKED,
            'booked_at' => $patient->created_at ?? now(),
            'booked_by' => $actor?->id,
            'sync_id' => $patient->sync_id ?: (string) Str::uuid(),
            'laboratory_patient_id' => $patient->id,
        ]);

        $this->replaceItems($booking, $patient);
        $this->upsertInvoiceAndPayment($booking, $patient, $actor);

        // Snapshot + CREDIT in same TX as booking sync (write-once; never recalculate later).
        $this->commissionSnapshotService->snapshotBooking($booking, $actor);

        return $booking->fresh(['items', 'invoice.payments', 'commissionSnapshots']);
    }

    private function refreshBooking(
        LimsBooking $booking,
        LaboratoryPatient $patient,
        LimsPatient $limsPatient,
        CollectionCenter $collectionCenter,
        ?User $actor,
        ?int $preferredDoctorId = null,
    ): LimsBooking {
        $selfReferred = (bool) $patient->self_referred;
        $doctorName = $selfReferred
            ? null
            : ($patient->refer_by_doctor_name ? trim((string) $patient->refer_by_doctor_name) : null);

        $isSelf = $selfReferred || $doctorName === null;
        $doctorId = $booking->doctor_id;
        if ($isSelf) {
            $doctorId = null;
        } elseif ($preferredDoctorId || $doctorId === null) {
            $doctorId = $this->resolveDoctorId((int) $booking->organization_id, $doctorName, $preferredDoctorId);
        }

        $booking->fill([
            'patient_id' => $limsPatient->id,
            'doctor_id' => $doctorId,
            'refer_by_doctor_name' => $doctorName,
            'self_referred' => $isSelf,
            // Keep collection_center_id / lab_number stable on update
            'collection_center_id' => $booking->collection_center_id ?: $collectionCenter->id,
        ]);
        $booking->save();

        $this->replaceItems($booking, $patient);
        $this->upsertInvoiceAndPayment($booking, $patient, $actor);

        // Ensure snapshots for any new items only — never mutate existing amounts.
        $this->commissionSnapshotService->snapshotBooking($booking, $actor);

        return $booking->fresh(['items', 'invoice.payments', 'commissionSnapshots']);
    }

    /**
     * Prefer an explicitly selected lims_doctors id; otherwise find/create by name.
     */
    private function resolveDoctorId(int $organizationId, ?string $doctorName, ?int $preferredDoctorId): ?int
    {
        if ($preferredDoctorId) {
            $preferred = LimsDoctor::query()
                ->where('id', $preferredDoctorId)
                ->where('organization_id', $organizationId)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            if ($preferred !== null) {
                return (int) $preferred->id;
            }
        }

        if ($doctorName === null || $doctorName === '') {
            return null;
        }

        return $this->commissionRuleResolver
            ->findOrCreateDoctor($organizationId, $doctorName)
            ->id;
    }

    private function replaceItems(LimsBooking $booking, LaboratoryPatient $patient): void
    {
        $tests = $patient->getSelectedTestsArray();
        $seenTestIds = [];

        foreach ($tests as $test) {
            if (! is_array($test) || empty($test['id'])) {
                continue;
            }

            $testId = (int) $test['id'];

            if (isset($seenTestIds[$testId])) {
                continue;
            }
            $seenTestIds[$testId] = true;

            $price = (float) ($test['price'] ?? 0);
            $name = trim((string) ($test['name'] ?? 'Test'));
            $lineStatus = strtolower((string) ($test['status'] ?? 'pending'));
            $sampleStatus = LimsBookingItem::mapLegacySampleStatus(
                isset($test['sample_status']) ? (string) $test['sample_status'] : null
            );

            $item = LimsBookingItem::withoutGlobalScopes()
                ->withTrashed()
                ->where('booking_id', $booking->id)
                ->where('test_id', $testId)
                ->first();

            $itemAttrs = [
                'collection_center_id' => $booking->collection_center_id,
                'test_category_id' => null,
                'test_name_snapshot' => $name !== '' ? $name : 'Test',
                'list_price' => $price,
                'net_price' => $price,
                'discount_amount' => 0,
                'status' => $lineStatus !== '' ? $lineStatus : 'pending',
                'sample_status' => $sampleStatus,
                'deleted_at' => null,
            ];

            if ($item !== null) {
                $item->fill($itemAttrs);
                $item->save();
            } else {
                LimsBookingItem::withoutGlobalScopes()->create(array_merge($itemAttrs, [
                    'booking_id' => $booking->id,
                    'test_id' => $testId,
                ]));
            }
        }

        // Soft-delete items no longer in selected_tests
        if ($seenTestIds !== []) {
            LimsBookingItem::withoutGlobalScopes()
                ->where('booking_id', $booking->id)
                ->whereNotIn('test_id', array_keys($seenTestIds))
                ->whereNull('deleted_at')
                ->get()
                ->each(fn (LimsBookingItem $item) => $item->delete());
        }
    }

    private function upsertInvoiceAndPayment(
        LimsBooking $booking,
        LaboratoryPatient $patient,
        ?User $actor,
    ): void {
        $subTotal = (float) ($patient->sub_total ?? 0);
        $discount = (float) ($patient->discount ?? 0);
        $grandTotal = (float) ($patient->grand_total ?? 0);
        $paidTotal = (float) ($patient->paid_amount ?? 0);
        $dueTotal = (float) ($patient->due_amount ?? max(0, $grandTotal - $paidTotal));

        $invoice = LimsInvoice::withoutGlobalScopes()
            ->where('booking_id', $booking->id)
            ->first();

        $attrs = [
            'organization_id' => $booking->organization_id,
            'collection_center_id' => $booking->collection_center_id,
            'invoice_no' => $invoice?->invoice_no ?? ('INV-'.$booking->lab_number),
            'status' => LimsInvoice::statusFromAmounts($grandTotal, $paidTotal),
            'sub_total' => $subTotal,
            'discount_total' => $discount,
            'grand_total' => $grandTotal,
            'paid_total' => $paidTotal,
            'due_total' => $dueTotal,
            'invoiced_at' => $invoice?->invoiced_at ?? ($patient->created_at ?? now()),
            'created_by' => $invoice?->created_by ?? $actor?->id,
        ];

        if ($invoice === null) {
            $invoice = LimsInvoice::withoutGlobalScopes()->create(array_merge($attrs, [
                'booking_id' => $booking->id,
            ]));
        } else {
            $invoice->fill($attrs);
            $invoice->save();
        }

        // Legacy booking has a single paid_amount — one cash payment when > 0.
        // Idempotent key ties payment to legacy patient so refresh does not duplicate.
        $idempotencyKey = 'lp-'.$patient->id.'-initial';

        $existingPayment = LimsPayment::withoutGlobalScopes()
            ->withTrashed()
            ->where('invoice_id', $invoice->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($paidTotal > 0) {
            if ($existingPayment !== null) {
                // Dual-write refresh: force-replace when amount changed (unique idempotency key).
                if ($existingPayment->trashed() || (float) $existingPayment->amount !== $paidTotal) {
                    $existingPayment->forceDelete();
                    $this->createPayment($invoice, $booking, $paidTotal, $actor, $idempotencyKey);
                }
            } else {
                $this->createPayment($invoice, $booking, $paidTotal, $actor, $idempotencyKey);
            }
        } elseif ($existingPayment !== null && ! $existingPayment->trashed()) {
            $existingPayment->delete();
        }
    }

    private function createPayment(
        LimsInvoice $invoice,
        LimsBooking $booking,
        float $amount,
        ?User $actor,
        string $idempotencyKey,
    ): LimsPayment {
        return LimsPayment::withoutGlobalScopes()->create([
            'organization_id' => $booking->organization_id,
            'collection_center_id' => $booking->collection_center_id,
            'invoice_id' => $invoice->id,
            'method' => LimsPayment::METHOD_CASH,
            'amount' => $amount,
            'paid_at' => $invoice->invoiced_at ?? now(),
            'received_by' => $actor?->id,
            'idempotency_key' => $idempotencyKey,
            'notes' => 'Dual-write from laboratory_patients.paid_amount',
            'created_at' => now(),
        ]);
    }

    private function normalizeGender(?string $gender): string
    {
        $g = ucfirst(strtolower(trim((string) $gender)));

        return in_array($g, ['Male', 'Female', 'Other'], true) ? $g : 'Other';
    }

    /**
     * Best-effort sync that never breaks the legacy booking path.
     */
    public function syncQuietly(
        LaboratoryPatient $patient,
        ?User $actor = null,
        ?int $collectionCenterId = null,
        ?int $preferredDoctorId = null,
    ): ?LimsBooking {
        try {
            return $this->syncFromLaboratoryPatient($patient, $actor, $collectionCenterId, $preferredDoctorId);
        } catch (Throwable $e) {
            Log::warning('LimsBookingSync failed (legacy booking kept)', [
                'laboratory_patient_id' => $patient->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
