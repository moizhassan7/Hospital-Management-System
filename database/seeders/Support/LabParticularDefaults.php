<?php

namespace Database\Seeders\Support;

/**
 * Fallback particulars for tests without curated panel data (CLSI / common lab practice).
 */
class LabParticularDefaults
{
    /**
     * @return list<array{name: string, unit: string|null, reference: string|null, is_calculated?: bool, formula?: string|null}>
     */
    public static function forTest(int $id, string $name, string $head): array
    {
        $norm = self::normalize($name);
        $headNorm = self::normalize($head);

        foreach (self::panelByName($norm) as $panel) {
            if (self::matches($norm, $panel['keys'])) {
                return $panel['particulars'];
            }
        }

        foreach (self::singleAnalyteMap() as $key => $item) {
            if ($norm === $key || str_contains($norm, $key)) {
                return [$item];
            }
        }

        if (preg_match('/\bc\s*\/\s*s\b|culture|sensiti/i', $name)) {
            return LabClsiPanels::PANELS['culture'];
        }

        if (str_contains($headNorm, 'histopath') || str_contains($headNorm, 'biopsy') || str_contains($norm, 'biopsy') || str_contains($norm, 'fnac')) {
            return [
                ['name' => 'Specimen', 'unit' => null, 'reference' => null],
                ['name' => 'Microscopic Findings', 'unit' => null, 'reference' => null],
                ['name' => 'Impression', 'unit' => null, 'reference' => null],
            ];
        }

        if (str_contains($norm, 'fluid') && (str_contains($norm, 'exam') || str_contains($norm, 'exmin'))) {
            return [
                ['name' => 'Appearance', 'unit' => null, 'reference' => 'Clear'],
                ['name' => 'Colour', 'unit' => null, 'reference' => null],
                ['name' => 'Protein', 'unit' => 'g/dL', 'reference' => null],
                ['name' => 'Sugar', 'unit' => 'mg/dL', 'reference' => null],
                ['name' => 'WBC', 'unit' => '/cumm', 'reference' => null],
                ['name' => 'RBC', 'unit' => '/cumm', 'reference' => null],
            ];
        }

        if (preg_match('/screen|elisa|antibod|antigen|igg|igm|iga|anti /i', $name)) {
            return [['name' => $name, 'unit' => null, 'reference' => 'Negative']];
        }

        if (preg_match('/pcr|gene expert|molecular/i', $name)) {
            return [['name' => 'Result', 'unit' => null, 'reference' => 'Detected / Not Detected']];
        }

        return [['name' => $name, 'unit' => null, 'reference' => null]];
    }

    private static function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    /** @param list<string> $keys */
    private static function matches(string $norm, array $keys): bool
    {
        foreach ($keys as $key) {
            if ($norm === $key || str_contains($norm, $key)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<array{keys: list<string>, particulars: list<array<string, mixed>>}> */
    private static function panelByName(string $norm): array
    {
        return [
            ['keys' => ['widal'], 'particulars' => [
                ['name' => 'Salmonella typhi O', 'unit' => null, 'reference' => '<1:80'],
                ['name' => 'Salmonella typhi H', 'unit' => null, 'reference' => '<1:80'],
                ['name' => 'Salmonella paratyphi AH', 'unit' => null, 'reference' => '<1:80'],
                ['name' => 'Salmonella paratyphi BH', 'unit' => null, 'reference' => '<1:80'],
            ]],
            ['keys' => ['cross match with screening', 'cross match screening'], 'particulars' => self::crossMatchScreening()],
            ['keys' => ['cross match without screening'], 'particulars' => self::crossMatchBasic()],
            ['keys' => ['cross match with elisa'], 'particulars' => self::crossMatchElisa()],
            ['keys' => ['cross matching'], 'particulars' => self::crossMatching()],
            ['keys' => ['cross match'], 'particulars' => self::crossMatch()],
            ['keys' => ['blood group', 'abo blood'], 'particulars' => [
                ['name' => 'Blood Group', 'unit' => null, 'reference' => 'A/B/AB/O'],
                ['name' => 'Rh Factor', 'unit' => null, 'reference' => 'Positive/Negative'],
            ]],
            ['keys' => ['csf examination', 'csf exam'], 'particulars' => [
                ['name' => 'Appearance', 'unit' => null, 'reference' => 'Clear'],
                ['name' => 'Colour', 'unit' => null, 'reference' => 'Colourless'],
                ['name' => 'Protein', 'unit' => 'mg/dL', 'reference' => '15-45'],
                ['name' => 'Glucose', 'unit' => 'mg/dL', 'reference' => '40-70'],
                ['name' => 'WBC Count', 'unit' => '/cumm', 'reference' => '0-5'],
                ['name' => 'RBC Count', 'unit' => '/cumm', 'reference' => 'Nil'],
            ]],
            ['keys' => ['peripheral blood film', 'peripheral smear', 'peripheral blood smear'], 'particulars' => [
                ['name' => 'RBC Morphology', 'unit' => null, 'reference' => 'Normocytic Normochromic'],
                ['name' => 'WBC Morphology', 'unit' => null, 'reference' => 'Normal'],
                ['name' => 'Platelets', 'unit' => null, 'reference' => 'Adequate'],
                ['name' => 'Parasites', 'unit' => null, 'reference' => 'Not seen'],
            ]],
            ['keys' => ['dengue igg igm', 'dengue ab'], 'particulars' => [
                ['name' => 'Dengue IgG', 'unit' => null, 'reference' => 'Negative'],
                ['name' => 'Dengue IgM', 'unit' => null, 'reference' => 'Negative'],
            ]],
            ['keys' => ['spot urine protein', 'acr albumin', 'albumin creatinine'], 'particulars' => [
                ['name' => 'Urine Albumin/Protein', 'unit' => 'mg/dL', 'reference' => null],
                ['name' => 'Urine Creatinine', 'unit' => 'mg/dL', 'reference' => null],
                ['name' => 'Ratio', 'unit' => 'mg/g', 'reference' => 'Normal: <30'],
            ]],
        ];
    }

    /** @return list<array{name: string, unit: string|null, reference: string|null}> */
    private static function crossMatchScreening(): array
    {
        return [
            ['name' => 'Patient Name', 'unit' => null, 'reference' => null],
            ['name' => 'Blood Grouping (Patient)', 'unit' => null, 'reference' => 'A/B/AB/O'],
            ['name' => 'Rh Factor (Patient)', 'unit' => null, 'reference' => 'Positive/Negative'],
            ['name' => 'Donor Name', 'unit' => null, 'reference' => null],
            ['name' => 'Blood Grouping (Donor)', 'unit' => null, 'reference' => 'A/B/AB/O'],
            ['name' => 'Donor Haemoglobin', 'unit' => 'g/dL', 'reference' => null],
            ['name' => 'Rh Factor (Donor)', 'unit' => null, 'reference' => 'Positive/Negative'],
            ['name' => 'HBsAg', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'Anti-HCV Ab', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'Syphilis (VDRL)', 'unit' => null, 'reference' => 'Non-reactive'],
            ['name' => 'Malarial Parasite', 'unit' => null, 'reference' => 'Not seen'],
        ];
    }

    /** @return list<array{name: string, unit: string|null, reference: string|null}> */
    private static function crossMatchBasic(): array
    {
        return [
            ['name' => 'Patient Name', 'unit' => null, 'reference' => null],
            ['name' => 'Blood Group (Patient)', 'unit' => null, 'reference' => 'A/B/AB/O'],
            ['name' => 'Rh Factor (Patient)', 'unit' => null, 'reference' => 'Positive/Negative'],
            ['name' => 'Donor Name', 'unit' => null, 'reference' => null],
            ['name' => 'Blood Group (Donor)', 'unit' => null, 'reference' => 'A/B/AB/O'],
            ['name' => 'Rh Factor (Donor)', 'unit' => null, 'reference' => 'Positive/Negative'],
            ['name' => 'Compatibility', 'unit' => null, 'reference' => 'Compatible with patient serum'],
            ['name' => 'Remarks', 'unit' => null, 'reference' => null],
        ];
    }

    /** @return list<array{name: string, unit: string|null, reference: string|null}> */
    private static function crossMatchElisa(): array
    {
        $rows = self::crossMatchScreening();
        $rows[] = ['name' => 'Cross Match (ELISA)', 'unit' => null, 'reference' => 'Compatible/Incompatible'];

        return $rows;
    }

    /** @return list<array{name: string, unit: string|null, reference: string|null}> */
    private static function crossMatching(): array
    {
        return [
            ['name' => 'Patient Name', 'unit' => null, 'reference' => null],
            ['name' => 'Blood Group (Patient)', 'unit' => null, 'reference' => 'A/B/AB/O'],
            ['name' => 'Rh Factor (Patient)', 'unit' => null, 'reference' => 'Positive/Negative'],
            ['name' => 'Donor Name', 'unit' => null, 'reference' => null],
            ['name' => 'Blood Group (Donor)', 'unit' => null, 'reference' => 'A/B/AB/O'],
            ['name' => 'Rh Factor (Donor)', 'unit' => null, 'reference' => 'Positive/Negative'],
            ['name' => 'Compatibility (Compatible)', 'unit' => null, 'reference' => 'Cells of Donor are Compatible with patient serum'],
            ['name' => 'Compatibility (Incompatible)', 'unit' => null, 'reference' => 'Cells of Donor are not Compatible with patient serum'],
        ];
    }

    /** @return list<array{name: string, unit: string|null, reference: string|null}> */
    private static function crossMatch(): array
    {
        return [
            ['name' => 'Patient Name', 'unit' => null, 'reference' => null],
            ['name' => 'Blood Grouping (Patient)', 'unit' => null, 'reference' => null],
            ['name' => 'RH Factor (Patient)', 'unit' => null, 'reference' => null],
            ['name' => 'Donor Name', 'unit' => null, 'reference' => null],
            ['name' => 'RH Factor (Donor)', 'unit' => null, 'reference' => null],
            ['name' => 'Donor Haemoglobin', 'unit' => 'g/dL', 'reference' => null],
            ['name' => 'Cells of Donor are Compatible with patient Serum', 'unit' => null, 'reference' => null],
            ['name' => 'Cells of donor are not compatible with Patient', 'unit' => null, 'reference' => null],
            ['name' => 'HBsAg', 'unit' => null, 'reference' => null],
            ['name' => 'Anti-HCV Ab', 'unit' => null, 'reference' => null],
            ['name' => 'HIV', 'unit' => null, 'reference' => null],
            ['name' => 'Syphilis (VDRL)', 'unit' => null, 'reference' => null],
            ['name' => 'Malarial Parasite', 'unit' => null, 'reference' => null],
        ];
    }

    /** @return array<string, array{name: string, unit: string|null, reference: string|null}> */
    private static function singleAnalyteMap(): array
    {
        return [
            'alp' => ['name' => 'Alkaline Phosphatase', 'unit' => 'U/L', 'reference' => '44-147'],
            'alkaline phosphatase' => ['name' => 'Alkaline Phosphatase', 'unit' => 'U/L', 'reference' => '44-147'],
            'chloride' => ['name' => 'Chloride', 'unit' => 'mEq/L', 'reference' => '98-107'],
            'cl+' => ['name' => 'Chloride', 'unit' => 'mEq/L', 'reference' => '98-107'],
            'hdl' => ['name' => 'HDL Cholesterol', 'unit' => 'mg/dL', 'reference' => 'Male: >40 | Female: >50'],
            'ldl' => ['name' => 'LDL Cholesterol', 'unit' => 'mg/dL', 'reference' => '<100'],
            'ldh' => ['name' => 'LDH', 'unit' => 'U/L', 'reference' => '140-280'],
            'triglyceride' => ['name' => 'Triglycerides', 'unit' => 'mg/dL', 'reference' => '<150'],
            'phosphate' => ['name' => 'Phosphate', 'unit' => 'mg/dL', 'reference' => '2.5-4.5'],
            'po4' => ['name' => 'Phosphate', 'unit' => 'mg/dL', 'reference' => '2.5-4.5'],
            'blood urea' => ['name' => 'Blood Urea', 'unit' => 'mg/dL', 'reference' => '15-40'],
            'magnesium' => ['name' => 'Magnesium', 'unit' => 'mg/dL', 'reference' => '1.7-2.2'],
            'magnisium' => ['name' => 'Magnesium', 'unit' => 'mg/dL', 'reference' => '1.7-2.2'],
            'psa' => ['name' => 'PSA', 'unit' => 'ng/mL', 'reference' => '<4.0'],
            'vitamin d' => ['name' => 'Vitamin D (25-OH)', 'unit' => 'ng/mL', 'reference' => '30-100'],
            'vit b12' => ['name' => 'Vitamin B12', 'unit' => 'pg/mL', 'reference' => '200-900'],
            'folic acid' => ['name' => 'Folic Acid', 'unit' => 'ng/mL', 'reference' => '3.0-17.0'],
            't3' => ['name' => 'T3', 'unit' => 'ng/dL', 'reference' => '80-200'],
            't4' => ['name' => 'T4', 'unit' => 'ug/dL', 'reference' => '5.0-12.0'],
            'tsh' => ['name' => 'TSH', 'unit' => 'mIU/L', 'reference' => '0.4-4.0'],
            'troponin' => ['name' => 'Troponin', 'unit' => 'ng/mL', 'reference' => '<0.04'],
            'trop t' => ['name' => 'Troponin T', 'unit' => 'ng/mL', 'reference' => '<0.04'],
            'procalcitonin' => ['name' => 'Procalcitonin', 'unit' => 'ng/mL', 'reference' => '<0.5'],
            'ammonia' => ['name' => 'Ammonia', 'unit' => 'umol/L', 'reference' => '11-35'],
            'bicarbonate' => ['name' => 'Bicarbonate', 'unit' => 'mEq/L', 'reference' => '22-28'],
            'zinc' => ['name' => 'Zinc', 'unit' => 'ug/dL', 'reference' => '70-120'],
            'cea' => ['name' => 'CEA', 'unit' => 'ng/mL', 'reference' => '<3.0'],
            'ca 19' => ['name' => 'CA 19-9', 'unit' => 'U/mL', 'reference' => '<37'],
            'pth' => ['name' => 'PTH', 'unit' => 'pg/mL', 'reference' => '15-65'],
            'ige' => ['name' => 'IgE', 'unit' => 'IU/mL', 'reference' => '<100'],
            'esr' => ['name' => 'ESR', 'unit' => 'mm/hr', 'reference' => 'Male: 0-15 | Female: 0-20'],
            'uric acid' => ['name' => 'Uric Acid', 'unit' => 'mg/dL', 'reference' => '3.5-7.5'],
            'creatinine' => ['name' => 'Creatinine', 'unit' => 'mg/dL', 'reference' => '0.7-1.4'],
            'urea' => ['name' => 'Urea', 'unit' => 'mg/dL', 'reference' => '15-40'],
            'sodium' => ['name' => 'Sodium', 'unit' => 'mEq/L', 'reference' => '136-145'],
            'potassium' => ['name' => 'Potassium', 'unit' => 'mEq/L', 'reference' => '3.5-5.1'],
            'calcium' => ['name' => 'Calcium', 'unit' => 'mg/dL', 'reference' => '8.5-10.5'],
            'bsr' => ['name' => 'Random Blood Sugar', 'unit' => 'mg/dL', 'reference' => '70-140'],
            'blood sugar random' => ['name' => 'Random Blood Sugar', 'unit' => 'mg/dL', 'reference' => '70-140'],
            'blood sugar fasting' => ['name' => 'Fasting Blood Glucose', 'unit' => 'mg/dL', 'reference' => '70-100'],
            'bsf' => ['name' => 'Fasting Blood Glucose', 'unit' => 'mg/dL', 'reference' => '70-100'],
            'gram stain' => ['name' => 'Gram Stain', 'unit' => null, 'reference' => 'Report'],
            'afb' => ['name' => 'AFB Stain', 'unit' => null, 'reference' => 'Not seen'],
        ];
    }
}
