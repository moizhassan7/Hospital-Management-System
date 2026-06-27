<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LaboratoryPatient extends Model
{
    use HasFactory;

    protected $fillable = [
        'mr_no',
        'lab_registration_no',
        'patient_name',
        'gender',
        'contact_no',
        'age',
        'file_no',
        'priority',
        'self_referred',
        'refer_by_doctor_name',
        'desktop_invoice',
        'selected_tests',
        'sub_total',
        'discount',
        'grand_total',
        'lab_share_total',
        'hospital_share_total',
        'paid_amount',
        'due_amount',
        'previous_due',
        'status', // Add this if you followed the previous instructions
    ];

    protected $casts = [
        'selected_tests' => 'array',
        'self_referred' => 'boolean',
    ];

    /**
     * Get the tests associated with the patient from the selected_tests array.
     */
    public function sampleVials()
    {
        return $this->hasMany(LabSampleVial::class);
    }

    public function tests()
    {
        // Check if selected_tests is not null or empty
        if (!$this->selected_tests) {
            return collect();
        }

        $selectedTests = is_string($this->selected_tests) ? json_decode($this->selected_tests, true) : $this->selected_tests;

        // Get the test IDs from the selected_tests array
        $testIds = collect($selectedTests)->pluck('id');

        // Return a collection of Test models
        return Test::whereIn('id', $testIds)->get();
    }

    public function getSelectedTestsArray(): array
    {
        if (!$this->selected_tests) {
            return [];
        }

        return is_string($this->selected_tests)
            ? json_decode($this->selected_tests, true)
            : $this->selected_tests;
    }

    public function setSelectedTestsArray(array $tests): void
    {
        if (!$this->exists) {
            return;
        }

        $this->selected_tests = $tests;
        $this->saveQuietly();
    }

    public static function generateLabRegistrationNo(): string
    {
        do {
            $number = 'LAB' . date('ymd') . strtoupper(Str::random(4));
        } while (static::where('lab_registration_no', $number)->exists());

        return $number;
    }

    public function markTestsSampleCollected(array $testIds): void
    {
        $tests = $this->getSelectedTestsArray();
        $now = now()->toDateTimeString();

        foreach ($tests as &$test) {
            if (in_array($test['id'], $testIds)) {
                $test['sample_status'] = LabSampleVial::STATUS_COLLECTED;
                $test['sample_collected_at'] = $now;
            }
        }

        $this->setSelectedTestsArray($tests);
    }

    public function markTestsReceivedInLab(array $testIds): void
    {
        $tests = $this->getSelectedTestsArray();
        $now = now()->toDateTimeString();

        foreach ($tests as &$test) {
            if (in_array($test['id'], $testIds)) {
                $test['sample_status'] = LabSampleVial::STATUS_IN_LAB;
                $test['sample_received_in_lab_at'] = $now;
                if (empty($test['sample_collected_at'])) {
                    $test['sample_collected_at'] = $now;
                }
            }
        }

        $this->setSelectedTestsArray($tests);
        $this->syncVialStatusesForTests();
    }

    public function updateTestSampleStatus(int $testId, string $status): void
    {
        $tests = $this->getSelectedTestsArray();
        $now = now()->toDateTimeString();

        foreach ($tests as &$test) {
            if ((int) $test['id'] === $testId) {
                $test['sample_status'] = $status;
                if ($status === LabSampleVial::STATUS_COLLECTED && empty($test['sample_collected_at'])) {
                    $test['sample_collected_at'] = $now;
                }
                if ($status === LabSampleVial::STATUS_IN_LAB && empty($test['sample_received_in_lab_at'])) {
                    $test['sample_received_in_lab_at'] = $now;
                }
                break;
            }
        }

        $this->setSelectedTestsArray($tests);
        $this->syncVialStatusesForTests();
    }

    private function isManualSampleStatus(string $status): bool
    {
        return in_array($status, [
            LabSampleVial::STATUS_EXPIRED,
            LabSampleVial::STATUS_REJECTED,
        ], true);
    }

    public function markTestResultCompleted(int $testId): void
    {
        $tests = $this->getSelectedTestsArray();
        $now = now()->toDateTimeString();

        foreach ($tests as &$test) {
            if ((int) $test['id'] === $testId) {
                if ($this->isManualSampleStatus($test['sample_status'] ?? '')) {
                    break;
                }
                $test['status'] = 'Completed';
                $test['sample_status'] = LabSampleVial::STATUS_COMPLETED;
                $test['result_completed_at'] = $now;
                $test['result_reported_at'] = $now;
                break;
            }
        }

        $this->setSelectedTestsArray($tests);
        $this->markVialsReportedForTest($testId, $now);
        $this->syncVialStatusesForTests();
    }

    private function markVialsReportedForTest(int $testId, string $reportedAt): void
    {
        foreach ($this->sampleVials as $vial) {
            if (!in_array($testId, $vial->test_ids ?? [], true)) {
                continue;
            }

            if (!$vial->reported_at) {
                $vial->reported_at = $reportedAt;
            }
            $vial->status = LabSampleVial::STATUS_COMPLETED;
            $vial->save();
        }
    }

    public function syncSampleStatusFromResults(): void
    {
        $tests = $this->getSelectedTestsArray();
        $changed = false;

        foreach ($tests as &$test) {
            $currentStatus = $test['sample_status'] ?? '';

            if ($this->isManualSampleStatus($currentStatus)) {
                continue;
            }

            if (($test['status'] ?? '') === 'Completed' && $currentStatus !== LabSampleVial::STATUS_COMPLETED) {
                $test['sample_status'] = LabSampleVial::STATUS_COMPLETED;
                $changed = true;
            }
        }

        if ($changed) {
            $this->setSelectedTestsArray($tests);
            $this->syncVialStatusesForTests();
        }
    }

    public function syncVialStatusesForTests(): void
    {
        $testsById = collect($this->getSelectedTestsArray())->keyBy('id');

        foreach ($this->sampleVials as $vial) {
            $testIds = $vial->test_ids ?? [];
            if (empty($testIds)) {
                continue;
            }

            $statuses = collect($testIds)->map(fn ($id) => $testsById->get($id)['sample_status'] ?? LabSampleVial::STATUS_NOT_COLLECTED);

            if ($statuses->contains(LabSampleVial::STATUS_EXPIRED)) {
                $vial->status = LabSampleVial::STATUS_EXPIRED;
            } elseif ($statuses->contains(LabSampleVial::STATUS_REJECTED)) {
                $vial->status = LabSampleVial::STATUS_REJECTED;
            } elseif ($statuses->every(fn ($s) => $s === LabSampleVial::STATUS_COMPLETED)) {
                $vial->status = LabSampleVial::STATUS_COMPLETED;
            } elseif ($statuses->contains(LabSampleVial::STATUS_PROCESSING) || $statuses->contains(LabSampleVial::STATUS_COMPLETED)) {
                $vial->status = LabSampleVial::STATUS_PROCESSING;
            } elseif ($statuses->every(fn ($s) => in_array($s, [LabSampleVial::STATUS_COLLECTED, LabSampleVial::STATUS_IN_LAB, LabSampleVial::STATUS_PROCESSING, LabSampleVial::STATUS_COMPLETED], true))) {
                if ($statuses->contains(LabSampleVial::STATUS_IN_LAB)) {
                    $vial->status = LabSampleVial::STATUS_IN_LAB;
                }
            }

            $vial->save();
        }
    }
}