<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Models\Test;
use Illuminate\Support\Collection;

class PathologyFormulaService
{
    /**
     * @param  array<int, string|float|null>  $values  keyed by test_particular_id
     * @return array<int, string>
     */
    public function applyFormulas(Test $test, LaboratoryPatient $patient, array $values): array
    {
        $particulars = $test->testParticulars()->orderBy('sort_order')->get();
        $byKey = $this->mapByResultKey($particulars, $values);

        foreach ($particulars as $particular) {
            if (!$particular->is_calculated || !$particular->formula) {
                continue;
            }

            $calculated = match ($particular->formula) {
                'bun_from_urea' => $this->calcBun($byKey['urea'] ?? null),
                'egfr_mdrd' => $this->calcEgfr($byKey['creatinine'] ?? null, $patient),
                'inr_from_pt' => $this->calcInr($byKey['pt'] ?? null, $byKey['control'] ?? null),
                'ag_ratio' => $this->calcAgRatio($byKey['albumin'] ?? null, $byKey['globulins'] ?? null),
                'indirect_bilirubin' => $this->calcIndirectBilirubin(
                    $byKey['bilirubin_total'] ?? null,
                    $byKey['bilirubin_direct'] ?? null
                ),
                default => null,
            };

            if ($calculated !== null) {
                $values[$particular->id] = $calculated;
                if ($particular->result_key) {
                    $byKey[$particular->result_key] = $calculated;
                }
            }
        }

        return $values;
    }

    public function isAbnormal(?float $value, ?float $min, ?float $max): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($min !== null && $value < $min) {
            return 'low';
        }

        if ($max !== null && $value > $max) {
            return 'high';
        }

        return 'normal';
    }

    /**
     * @return null|'normal'|'abnormal'|'critical'
     */
    public function classifyValue(
        ?float $value,
        ?float $min,
        ?float $max,
        ?float $criticalMin = null,
        ?float $criticalMax = null
    ): ?string {
        if ($value === null) {
            return null;
        }

        if ($criticalMin !== null && $value < $criticalMin) {
            return 'critical';
        }

        if ($criticalMax !== null && $value > $criticalMax) {
            return 'critical';
        }

        if ($min !== null && $max !== null && $criticalMin === null && $criticalMax === null) {
            $span = max($max - $min, 0.0001);
            if ($value < $min - ($span * 0.5)) {
                return 'critical';
            }
            if ($value > $max + ($span * 0.5)) {
                return 'critical';
            }
        }

        if ($min !== null && $value < $min) {
            return 'abnormal';
        }

        if ($max !== null && $value > $max) {
            return 'abnormal';
        }

        return 'normal';
    }

    /**
     * @return null|'low'|'high'
     */
    public function resultFlag(?float $value, ?float $min, ?float $max): ?string
    {
        $abnormal = $this->isAbnormal($value, $min, $max);

        return in_array($abnormal, ['low', 'high'], true) ? $abnormal : null;
    }

    private function mapByResultKey(Collection $particulars, array $values): array
    {
        $byKey = [];

        foreach ($particulars as $particular) {
            if (!$particular->result_key) {
                continue;
            }

            $raw = $values[$particular->id] ?? null;
            $byKey[$particular->result_key] = is_numeric($raw) ? (float) $raw : $raw;
        }

        return $byKey;
    }

    private function calcBun(mixed $urea): ?string
    {
        if (!is_numeric($urea) || (float) $urea <= 0) {
            return null;
        }

        return $this->format((float) $urea / 2.14);
    }

    private function calcEgfr(mixed $creatinine, LaboratoryPatient $patient): ?string
    {
        if (!is_numeric($creatinine) || (float) $creatinine <= 0) {
            return null;
        }

        $scr = (float) $creatinine;
        $age = max(1, (int) $patient->age);
        $isFemale = str_contains(strtolower((string) $patient->gender), 'f');

        $gfr = 175 * pow($scr, -1.154) * pow($age, -0.203);
        if ($isFemale) {
            $gfr *= 0.742;
        }

        return $this->format($gfr);
    }

    private function calcInr(mixed $pt, mixed $control): ?string
    {
        if (!is_numeric($pt) || !is_numeric($control) || (float) $control <= 0) {
            return null;
        }

        return $this->format((float) $pt / (float) $control);
    }

    private function calcAgRatio(mixed $albumin, mixed $globulins): ?string
    {
        if (!is_numeric($albumin) || !is_numeric($globulins) || (float) $globulins <= 0) {
            return null;
        }

        return $this->format((float) $albumin / (float) $globulins);
    }

    private function calcIndirectBilirubin(mixed $total, mixed $direct): ?string
    {
        if (!is_numeric($total) || !is_numeric($direct)) {
            return null;
        }

        return $this->format(max(0, (float) $total - (float) $direct));
    }

    private function format(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
