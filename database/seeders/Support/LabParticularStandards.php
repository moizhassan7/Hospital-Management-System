<?php

namespace Database\Seeders\Support;

/**
 * International pathology report conventions (CLSI / ISO 15189):
 * - CBC: WBC → RBC indices → platelets → differential
 * - LFT: bilirubin fraction → transaminases → ALP/GGT → proteins
 * - RFT: urea/BUN → creatinine → eGFR
 * - Lipids: total cholesterol → TG → HDL → LDL → VLDL
 * - Coagulation: PT → control → INR → aPTT
 * - Urine: physical → chemical → microscopic
 */
class LabParticularStandards
{
    /** @var array<string, int> */
    private const ANALYTE_ORDER = [
        // CBC / hematology
        'wbc' => 10,
        'wbc_tlc' => 10,
        'wbc_count' => 10,
        'tlc' => 10,
        'total_rbc' => 20,
        'rbc_count' => 20,
        'rbc' => 20,
        'haemoglobin' => 30,
        'hemoglobin' => 30,
        'hb' => 30,
        'hct_pcv' => 40,
        'hct' => 40,
        'pcv' => 40,
        'hematocrit' => 40,
        'mcv' => 50,
        'mch' => 60,
        'mchc' => 70,
        'rdw' => 75,
        'platelets' => 80,
        'neutrophils' => 90,
        'lymphocytes' => 100,
        'monocytes' => 110,
        'eosinophils' => 120,
        'basophils' => 130,
        'esr' => 140,
        'retic_count' => 150,

        // Liver function
        'total_bilirubin' => 200,
        'bilirubin_total' => 200,
        'direct_bilirubin' => 210,
        'bilirubin_direct' => 210,
        'indirect_bilirubin' => 220,
        'bilirubin_indirect' => 220,
        'ast_sgot' => 230,
        'sgot' => 230,
        'ast' => 230,
        'alt_sgpt' => 240,
        'sgpt' => 240,
        'alt' => 240,
        's_g_p_t_alt' => 240,
        'alkaline_phosphatase' => 250,
        'alp' => 250,
        'gamma_gt' => 260,
        'ggt' => 260,
        'total_protein' => 270,
        'albumin' => 280,
        'globulin' => 290,
        'globulins' => 290,
        'a_g_ratio' => 300,
        'ag_ratio' => 300,

        // Renal function
        'urea' => 400,
        'bun' => 410,
        'blood_urea_nitrogen' => 410,
        'creatinine' => 420,
        'estimated_glomerular_filtration_rate_egfr' => 430,
        'egfr' => 430,
        'uric_acid' => 440,
        'creatinine_clearance' => 450,

        // Electrolytes / minerals
        'sodium' => 500,
        'potassium' => 510,
        'chloride' => 520,
        'bicarbonate' => 530,
        'magnesium' => 540,
        'calcium' => 550,
        'phosphorus' => 560,

        // Lipid profile (NCEP order)
        'cholesterol' => 600,
        'total_cholesterol' => 600,
        'triglycerides' => 610,
        'hdl_cholesterol' => 620,
        'hdl' => 620,
        'ldl_cholesterol' => 630,
        'ldl' => 630,
        'vldl_cholesterol' => 640,
        'vldl' => 640,

        // Coagulation
        'pt' => 700,
        'prothrombin_time' => 700,
        'control' => 710,
        'inr' => 720,
        'aptt' => 730,
        'ptt_prothrombin_time' => 700,
        'clotting_time' => 740,
        'bleeding_time' => 750,

        // Glucose
        'sugar_fasting' => 800,
        'fasting_blood_glucose' => 800,
        'blood_glucose' => 800,
        'random_glucose' => 810,
        'sugar_random' => 810,
        'rbs' => 810,
        'hba1c' => 820,

        // Cardiac enzymes
        'ck_mb' => 900,
        'cpk_mb' => 900,
        'troponin' => 910,
        'ldh' => 920,

        // Urine — physical
        'color' => 1000,
        'appearance' => 1010,
        'deposit' => 1020,
        'specific_gravity' => 1030,
        'ph' => 1040,

        // Urine — chemical
        'protein' => 1100,
        'glucose' => 1110,
        'ketone' => 1120,
        'urine_ketone' => 1120,
        'bilirubin' => 1130,
        'urobilinogen' => 1140,
        'blood' => 1150,
        'nitrite' => 1160,
        'leukocyte_esterase' => 1170,

        // Urine — microscopic
        'pus_cells' => 1200,
        'rbc_urine' => 1210,
        'epithelial_cells' => 1220,
        'casts' => 1230,
        'crystals' => 1240,
        'yeast' => 1250,
        'bacteria' => 1260,
        'urates' => 1270,
    ];

    /** @var array<string, string> */
    private const NAME_ALIASES = [
        'wbc_tlc' => 'wbc',
        'wbc_count' => 'wbc',
        'tlc' => 'wbc',
        'total_rbc' => 'rbc',
        'rbc_count' => 'rbc',
        'haemoglobin' => 'hb',
        'hemoglobin' => 'hb',
        'hct_pcv' => 'hct',
        'pcv' => 'hct',
        'hematocrit' => 'hct',
        'total_bilirubin' => 'bilirubin_total',
        'direct_bilirubin' => 'bilirubin_direct',
        'indirect_bilirubin' => 'bilirubin_indirect',
        'ast_sgot' => 'sgot',
        'ast' => 'sgot',
        'alt_sgpt' => 'sgpt',
        'alt' => 'sgpt',
        's_g_p_t_alt' => 'sgpt',
        'alkaline_phosphatase' => 'alp',
        'gamma_gt' => 'ggt',
        'globulins' => 'globulin',
        'a_g_ratio' => 'ag_ratio',
        'blood_urea_nitrogen' => 'bun',
        'estimated_glomerular_filtration_rate_egfr' => 'egfr',
        'total_cholesterol' => 'cholesterol',
        'hdl_cholesterol' => 'hdl',
        'ldl_cholesterol' => 'ldl',
        'vldl_cholesterol' => 'vldl',
        'sugar_fasting' => 'fasting_blood_glucose',
        'fasting_blood_glucose' => 'fasting_blood_glucose',
        'blood_glucose' => 'fasting_blood_glucose',
        'random_glucose' => 'random_glucose',
        'sugar_random' => 'random_glucose',
        'rbs' => 'random_glucose',
        'prothrombin_time' => 'pt',
        'ptt_prothrombin_time' => 'pt',
        'urine_ketone' => 'ketone',
        'pus_cells' => 'pus_cells',
    ];

    /**
     * @var array<string, array{
     *     result_key: string,
     *     formula?: string|null,
     *     is_calculated?: bool,
     *     critical_range_min?: float|null,
     *     critical_range_max?: float|null,
     *     remarks?: string|null
     * }>
     */
    private const METADATA = [
        'urea' => ['result_key' => 'urea', 'critical_range_max' => 100.0],
        'bun' => ['result_key' => 'bun', 'formula' => 'bun_from_urea', 'is_calculated' => true],
        'creatinine' => ['result_key' => 'creatinine', 'critical_range_max' => 10.0],
        'egfr' => [
            'result_key' => 'egfr',
            'formula' => 'egfr_mdrd',
            'is_calculated' => true,
            'remarks' => "STAGE 1: GFR ≥90 (slight renal damage with normal or increased GFR).\n"
                . "STAGE 2: GFR 60–89 (mild decrease in GFR with mild kidney damage).\n"
                . "STAGE 3: GFR 30–59 (moderate decrease in GFR with moderate kidney damage).\n"
                . "STAGE 4: GFR 15–29 (severe decrease in GFR with severe kidney damage).\n"
                . "STAGE 5: GFR <15 (kidney failure — consider dialysis or renal transplant).",
        ],
        'bilirubin_total' => ['result_key' => 'bilirubin_total', 'critical_range_max' => 15.0],
        'bilirubin_direct' => ['result_key' => 'bilirubin_direct'],
        'bilirubin_indirect' => ['result_key' => 'bilirubin_indirect', 'formula' => 'indirect_bilirubin', 'is_calculated' => true],
        'sgot' => [
            'result_key' => 'sgot',
            'remarks' => "Children:\n1–3 years <50\n4–6 years <45\n7–9 years <40\n10–12 years <38\n13–15 years <36\n16–18 years <35",
        ],
        'sgpt' => ['result_key' => 'sgpt'],
        'alp' => ['result_key' => 'alp', 'remarks' => 'Child <645'],
        'total_protein' => ['result_key' => 'total_protein', 'remarks' => 'Newborn 4.6–7.0'],
        'albumin' => ['result_key' => 'albumin', 'critical_range_min' => 1.5],
        'globulin' => ['result_key' => 'globulins'],
        'ag_ratio' => ['result_key' => 'ag_ratio', 'formula' => 'ag_ratio', 'is_calculated' => true],
        'pt' => ['result_key' => 'pt'],
        'control' => ['result_key' => 'control'],
        'inr' => ['result_key' => 'inr', 'formula' => 'inr_from_pt', 'is_calculated' => true],
        'hb' => ['result_key' => 'hemoglobin', 'critical_range_min' => 7.0, 'critical_range_max' => 20.0],
        'wbc' => ['result_key' => 'wbc', 'critical_range_min' => 2.0, 'critical_range_max' => 30.0],
        'platelets' => ['result_key' => 'platelets', 'critical_range_min' => 50.0, 'critical_range_max' => 1000.0],
        'fasting_blood_glucose' => ['result_key' => 'glucose_fasting', 'critical_range_min' => 40.0, 'critical_range_max' => 500.0],
        'random_glucose' => ['result_key' => 'glucose_random', 'critical_range_min' => 40.0, 'critical_range_max' => 500.0],
        'potassium' => ['result_key' => 'potassium', 'critical_range_min' => 2.5, 'critical_range_max' => 6.5],
        'sodium' => ['result_key' => 'sodium', 'critical_range_min' => 120.0, 'critical_range_max' => 160.0],
        'calcium' => ['result_key' => 'calcium', 'critical_range_min' => 6.0, 'critical_range_max' => 13.0],
    ];

    public static function normalizeName(string $name): string
    {
        $normalized = strtolower(trim($name));
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';

        return trim($normalized, '_');
    }

    public static function canonicalKey(string $particularName): string
    {
        $normalized = self::normalizeName($particularName);

        return self::NAME_ALIASES[$normalized] ?? $normalized;
    }

    public static function reportRank(string $particularName): int
    {
        $canonical = self::canonicalKey($particularName);

        return self::ANALYTE_ORDER[$canonical] ?? 9000;
    }

    /**
     * @return array{
     *     result_key: string,
     *     formula: string|null,
     *     is_calculated: bool,
     *     critical_range_min: float|null,
     *     critical_range_max: float|null,
     *     remarks: string|null
     * }
     */
    public static function metadataFor(string $particularName): array
    {
        $canonical = self::canonicalKey($particularName);
        $meta = self::METADATA[$canonical] ?? [];

        return [
            'result_key' => $meta['result_key'] ?? self::defaultResultKey($particularName),
            'formula' => $meta['formula'] ?? null,
            'is_calculated' => (bool) ($meta['is_calculated'] ?? false),
            'critical_range_min' => $meta['critical_range_min'] ?? null,
            'critical_range_max' => $meta['critical_range_max'] ?? null,
            'remarks' => $meta['remarks'] ?? null,
        ];
    }

    public static function defaultResultKey(string $particularName): string
    {
        return self::canonicalKey($particularName);
    }

    /**
     * @param  list<array{name: string, _index?: int}>  $rows
     * @return list<array{name: string, sort_order: int, _index?: int}>
     */
    public static function assignReportOrders(array $rows): array
    {
        usort($rows, function (array $a, array $b): int {
            $rankA = self::reportRank($a['name']);
            $rankB = self::reportRank($b['name']);

            if ($rankA !== $rankB) {
                return $rankA <=> $rankB;
            }

            $indexA = $a['_index'] ?? 0;
            $indexB = $b['_index'] ?? 0;

            if ($indexA !== $indexB) {
                return $indexA <=> $indexB;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        foreach ($rows as $i => &$row) {
            $row['sort_order'] = $i + 1;
        }
        unset($row);

        return $rows;
    }
}
