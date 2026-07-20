<?php

namespace App\Services\Lims;

use App\Models\CollectionCenter;
use App\Models\LabSampleVial;
use App\Models\LimsBooking;
use App\Models\LimsSample;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Dual-write bridge: LabSampleVial (legacy) ↔ lims_samples.
 *
 * Additive — never breaks Sample Portal. Failures are logged when using syncQuietly.
 */
class LimsSampleSync
{
    public function __construct(
        private readonly LimsBookingSync $bookingSync,
    ) {}

    /**
     * Create or refresh a lims_samples row from a legacy vial.
     * Ensures the parent lims_bookings row exists via laboratory_patient_id.
     */
    public function syncFromVial(LabSampleVial $vial, ?User $actor = null): LimsSample
    {
        return DB::transaction(function () use ($vial, $actor) {
            $vial->loadMissing('laboratoryPatient');
            $lp = $vial->laboratoryPatient;

            if ($lp === null) {
                throw new RuntimeException("Vial {$vial->id} has no laboratory_patient.");
            }

            $booking = LimsBooking::withoutGlobalScopes()
                ->where('laboratory_patient_id', $lp->id)
                ->first();

            if ($booking === null) {
                $booking = $this->bookingSync->syncFromLaboratoryPatient($lp, $actor);
            }

            [$organization, $collectionCenter] = $this->resolveTenancy($booking, $actor);

            $existing = LimsSample::withoutGlobalScopes()
                ->where('lab_sample_vial_id', $vial->id)
                ->first();

            $status = LimsSample::mapLegacyStatus($vial->status);

            $attrs = [
                'organization_id' => $organization->id,
                'collection_center_id' => $collectionCenter->id,
                'booking_id' => $booking->id,
                'barcode' => (string) $vial->barcode,
                'vial_type' => $vial->vial_type,
                'vial_number' => $vial->vial_number,
                'status' => $status,
                'collected_at' => $vial->collected_at,
                'expires_at' => $vial->expires_at,
                'lab_sample_vial_id' => $vial->id,
            ];

            if ($status === LimsSample::STATUS_COLLECTED || $vial->collected_at) {
                if ($actor instanceof User) {
                    // Stamp collector when first collected; keep existing stamp on refresh.
                    if ($existing === null || ! $existing->collected_by) {
                        $attrs['collected_by'] = $actor->id;
                        $attrs['collected_by_name'] = $this->actorDisplayName($actor);
                    }
                }
            }

            if ($status === LimsSample::STATUS_RECEIVED && $vial->received_in_lab_at) {
                $attrs['received_at'] = $vial->received_in_lab_at;
                if ($actor instanceof User && ($existing === null || ! $existing->received_by)) {
                    $attrs['received_by'] = $actor->id;
                    $attrs['received_by_name'] = $this->actorDisplayName($actor);
                }
            }

            if ($existing !== null) {
                // Do not regress transit statuses already past collected when legacy is still collected.
                if ($this->shouldPreserveTransitStatus($existing->status, $status)) {
                    unset($attrs['status']);
                }
                $existing->fill($attrs);
                $existing->save();

                return $existing->fresh();
            }

            return LimsSample::withoutGlobalScopes()->create($attrs);
        });
    }

    public function syncQuietly(LabSampleVial $vial, ?User $actor = null): ?LimsSample
    {
        try {
            return $this->syncFromVial($vial, $actor);
        } catch (Throwable $e) {
            Log::warning('LimsSampleSync failed', [
                'vial_id' => $vial->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Push LIMS receive/reject onto the legacy vial when safe.
     * Only advances collected → in_lab (or rejected); never regresses completed vials.
     */
    public function pushReceiveToLegacy(LimsSample $sample, string $receiveStatus, ?string $note = null): void
    {
        if (! $sample->lab_sample_vial_id) {
            return;
        }

        $vial = LabSampleVial::query()->find($sample->lab_sample_vial_id);

        if ($vial === null) {
            return;
        }

        $terminal = [
            LabSampleVial::STATUS_IN_LAB,
            LabSampleVial::STATUS_PROCESSING,
            LabSampleVial::STATUS_COMPLETED,
        ];

        if ($receiveStatus === 'received') {
            if (in_array($vial->status, $terminal, true)) {
                return;
            }
            if (! in_array($vial->status, [
                LabSampleVial::STATUS_COLLECTED,
                LabSampleVial::STATUS_NOT_COLLECTED,
            ], true)) {
                return;
            }

            // Update this vial only — do not call markTestsReceivedInLab(), which
            // runs syncVialStatusesForTests() and can advance sibling vials that
            // share the same test_ids (partial batch receive must stay per-vial).
            $vial->markReceivedInLab();
            $this->stampLegacyTestReceivedWithoutVialCascade($vial);

            return;
        }

        if ($receiveStatus === 'rejected') {
            if (in_array($vial->status, [LabSampleVial::STATUS_COMPLETED, LabSampleVial::STATUS_PROCESSING], true)) {
                return;
            }
            $vial->status = LabSampleVial::STATUS_REJECTED;
            $vial->save();
            foreach ($vial->test_ids ?? [] as $testId) {
                $vial->laboratoryPatient?->updateTestSampleStatus((int) $testId, LabSampleVial::STATUS_REJECTED);
            }
        }

        // missing: leave legacy vial as collected (still outstanding)
    }

    /**
     * Stamp selected_tests sample_status = in_lab for this vial's tests without
     * syncVialStatusesForTests (avoids advancing other vials on shared test_ids).
     */
    private function stampLegacyTestReceivedWithoutVialCascade(LabSampleVial $vial): void
    {
        $patient = $vial->laboratoryPatient;
        if ($patient === null) {
            return;
        }

        $testIds = $vial->test_ids ?? [];
        if ($testIds === []) {
            return;
        }

        $tests = $patient->getSelectedTestsArray();
        $now = now()->toDateTimeString();
        $changed = false;

        foreach ($tests as &$test) {
            if (! in_array((int) $test['id'], array_map('intval', $testIds), true)) {
                continue;
            }
            $test['sample_status'] = LabSampleVial::STATUS_IN_LAB;
            $test['sample_received_in_lab_at'] = $now;
            if (empty($test['sample_collected_at'])) {
                $test['sample_collected_at'] = $now;
            }
            $changed = true;
        }
        unset($test);

        if ($changed) {
            $patient->setSelectedTestsArray($tests);
        }
    }

    /**
     * @return array{0: Organization, 1: CollectionCenter}
     */
    private function resolveTenancy(LimsBooking $booking, ?User $actor): array
    {
        $organization = Organization::query()->find($booking->organization_id);
        $collectionCenter = CollectionCenter::query()->find($booking->collection_center_id);

        if ($organization === null || $collectionCenter === null) {
            throw new RuntimeException('Booking tenancy missing for sample sync.');
        }

        if ($actor instanceof User
            && $actor->isCollectionCenterScope()
            && $actor->collection_center_id
            && (int) $actor->collection_center_id !== (int) $collectionCenter->id
        ) {
            // Prefer actor CC only when booking was somehow unscoped; stamp from actor.
            $actorCc = CollectionCenter::query()->find($actor->collection_center_id);
            if ($actorCc) {
                $collectionCenter = $actorCc;
            }
        }

        return [$organization, $collectionCenter];
    }

    private function shouldPreserveTransitStatus(string $current, string $fromLegacy): bool
    {
        $transit = [
            LimsSample::STATUS_DISPATCHED,
            LimsSample::STATUS_IN_TRANSIT,
            LimsSample::STATUS_RECEIVED,
            LimsSample::STATUS_PROCESSING,
            LimsSample::STATUS_COMPLETED,
        ];

        return in_array($current, $transit, true)
            && in_array($fromLegacy, [LimsSample::STATUS_BOOKED, LimsSample::STATUS_COLLECTED], true);
    }

    private function actorDisplayName(User $actor): string
    {
        $name = trim((string) ($actor->name ?: $actor->username));

        return $name !== '' ? $name : 'User #'.$actor->id;
    }
}
