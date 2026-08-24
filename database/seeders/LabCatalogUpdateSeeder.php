<?php

namespace Database\Seeders;

use App\Models\Test;
use App\Models\TestHead;
use App\Models\TestParticular;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LabCatalogUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds to update CBC, Urine, and catalog tests.
     */
    public function run(): void
    {
        $jsonPath = __DIR__ . '/data/lab_catalog.json';

        if (! file_exists($jsonPath)) {
            $this->command?->error("Missing {$jsonPath}.");
            return;
        }

        $catalog = json_decode(file_get_contents($jsonPath), true);

        if (! is_array($catalog) || ($catalog['tests'] ?? []) === []) {
            $this->command?->error('lab_catalog.json is empty or invalid.');
            return;
        }

        DB::transaction(function () use ($catalog) {
            foreach ($catalog['tests'] as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id <= 0) {
                    continue;
                }

                $test = Test::find($id);
                if (! $test) {
                    continue;
                }

                $particulars = $row['particulars'] ?? [];
                if (empty($particulars)) {
                    continue;
                }

                // Existing particulars keyed by external_id and by patient_type+slug
                $existingParticulars = TestParticular::where('test_id', $test->id)->get();
                $existingByExternalId = $existingParticulars->whereNotNull('external_id')->keyBy('external_id');
                $existingBySlug = $existingParticulars->keyBy(function ($item) {
                    return strtolower((string) $item->patient_type) . ':' . strtolower((string) $item->result_key);
                });

                $processedIds = [];

                foreach ($particulars as $sortOrder => $particularRow) {
                    $name = trim((string) ($particularRow['name'] ?? ''));
                    if ($name === '') {
                        continue;
                    }

                    $externalId = isset($particularRow['external_id']) ? (int) $particularRow['external_id'] : null;
                    $patientType = $this->nullableString($particularRow['patient_type'] ?? null);
                    $resultKey = $this->nullableString($particularRow['result_key'] ?? null) ?? Str::slug($name, '_');
                    $slugLookupKey = strtolower((string) $patientType) . ':' . strtolower($resultKey);

                    $attributes = [
                        'name' => $name,
                        'patient_type' => $patientType,
                        'result_key' => $resultKey,
                        'unit' => $this->nullableString($particularRow['unit'] ?? null),
                        'normal_range_min' => $this->nullableString($particularRow['normal_range_min'] ?? null),
                        'normal_range_max' => $this->nullableString($particularRow['normal_range_max'] ?? null),
                        'critical_range_min' => $this->nullableString($particularRow['critical_range_min'] ?? null),
                        'critical_range_max' => $this->nullableString($particularRow['critical_range_max'] ?? null),
                        'reference_range_text' => $this->nullableString($particularRow['reference_range_text'] ?? null),
                        'interpretation_name' => $this->nullableString($particularRow['interpretation_name'] ?? null),
                        'sort_order' => $sortOrder + 1,
                        'is_active' => true,
                    ];

                    $particular = null;
                    if ($externalId && $existingByExternalId->has($externalId)) {
                        $particular = $existingByExternalId->get($externalId);
                    } elseif ($existingBySlug->has($slugLookupKey)) {
                        $particular = $existingBySlug->get($slugLookupKey);
                    }

                    if ($particular) {
                        $particular->update($attributes);
                        $processedIds[] = $particular->id;
                    } else {
                        $newParticular = TestParticular::create(array_merge($attributes, [
                            'test_id' => $test->id,
                            'external_id' => $externalId,
                            'reference_text' => null,
                            'formula' => null,
                            'is_calculated' => false,
                        ]));
                        $processedIds[] = $newParticular->id;
                    }
                }
            }
        });

        Test::clearPathologyCache();

        $this->command?->info('Lab test catalog updated successfully with new CBC ordering and Urine parameter options.');
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
