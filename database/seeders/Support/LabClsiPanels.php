<?php

namespace Database\Seeders\Support;

/**
 * CLSI / ISO 15189 canonical panel definitions and major-panel ID map.
 */
class LabClsiPanels
{
    /** @var array<int, array{source: string, test_key?: string, panel?: string}> */
    public const MAJOR_PANEL_MAP = [
        20346 => ['source' => 'document', 'test_key' => 'cbc'],
        7248 => ['source' => 'document', 'test_key' => 'cbc_peripheral'],
        138 => ['source' => 'clsi', 'panel' => 'lft'],
        152 => ['source' => 'document', 'test_key' => 'urine'],
        20356 => ['source' => 'document', 'test_key' => 'urine'],
        8 => ['source' => 'document', 'test_key' => 'lipid_profile'],
        37 => ['source' => 'clsi', 'panel' => 'rft'],
        153 => ['source' => 'document', 'test_key' => 'semen'],
        63 => ['source' => 'clsi', 'panel' => 'pt_inr'],
        40 => ['source' => 'clsi', 'panel' => 'electrolytes'],
        1161 => ['source' => 'clsi', 'panel' => 'coagulation'],
        1162 => ['source' => 'clsi', 'panel' => 'pt_inr'],
        64 => ['source' => 'clsi', 'panel' => 'aptt'],
        65 => ['source' => 'clsi', 'panel' => 'inr_only'],
        151 => ['source' => 'clsi', 'panel' => 'hepatitis_screening'],
        1160 => ['source' => 'clsi', 'panel' => 'tft'],
        1170 => ['source' => 'clsi', 'panel' => 'tft'],
        1174 => ['source' => 'clsi', 'panel' => 'electrolytes'],
        124 => ['source' => 'clsi', 'panel' => 'pregnancy'],
        2202 => ['source' => 'clsi', 'panel' => 'culture'],
        7 => ['source' => 'document', 'test_key' => 'hba1c'],
        159 => ['source' => 'clsi', 'panel' => 'tft'],
        9252 => ['source' => 'document', 'test_key' => 'dengue_screening'],
        21366 => ['source' => 'clsi', 'panel' => 'torch'],
        7243 => ['source' => 'clsi', 'panel' => 'ogtt'],
    ];

    /** @var array<string, list<array<string, mixed>>> */
    public const PANELS = [
        'lft' => [
            ['name' => 'Bilirubin Total', 'unit' => 'mg/dL', 'reference' => '0.3-1.0'],
            ['name' => 'Bilirubin Direct', 'unit' => 'mg/dL', 'reference' => '0.0-0.2'],
            ['name' => 'Bilirubin Indirect', 'unit' => 'mg/dL', 'reference' => '0.2-0.8', 'is_calculated' => true, 'formula' => 'indirect_bilirubin'],
            ['name' => 'SGOT (AST)', 'unit' => 'U/L', 'reference' => '0-40'],
            ['name' => 'SGPT (ALT)', 'unit' => 'U/L', 'reference' => '0-41'],
            ['name' => 'Alkaline Phosphatase (ALP)', 'unit' => 'U/L', 'reference' => '44-147'],
            ['name' => 'Gamma GT', 'unit' => 'U/L', 'reference' => '5-50'],
            ['name' => 'Total Protein', 'unit' => 'g/dL', 'reference' => '6.4-8.3'],
            ['name' => 'Albumin', 'unit' => 'g/dL', 'reference' => '3.5-5.2'],
            ['name' => 'Globulins', 'unit' => 'g/dL', 'reference' => '2.0-3.5'],
            ['name' => 'A/G Ratio', 'unit' => 'Ratio', 'reference' => '1.0-2.0', 'is_calculated' => true, 'formula' => 'ag_ratio'],
        ],
        'rft' => [
            ['name' => 'Urea', 'unit' => 'mg/dL', 'reference' => '15-39'],
            ['name' => 'Creatinine', 'unit' => 'mg/dL', 'reference' => '0.70-1.40'],
            ['name' => 'BUN (Blood Urea Nitrogen)', 'unit' => 'mg/dL', 'reference' => '8-25', 'is_calculated' => true, 'formula' => 'bun_from_urea'],
            ['name' => 'Estimated Glomerular Filtration Rate (eGFR)', 'unit' => 'mL/min/1.73m²', 'reference' => '>90', 'is_calculated' => true, 'formula' => 'egfr_mdrd'],
        ],
        'pt_inr' => [
            ['name' => 'PT', 'unit' => 'Sec.', 'reference' => '11-14'],
            ['name' => 'Control', 'unit' => 'Sec.', 'reference' => '11-14'],
            ['name' => 'INR', 'unit' => 'Ratio', 'reference' => '0.8-1.2', 'is_calculated' => true, 'formula' => 'inr_from_pt'],
        ],
        'aptt' => [
            ['name' => 'Control', 'unit' => 'Sec.', 'reference' => '25-35'],
            ['name' => 'aPTT', 'unit' => 'Sec.', 'reference' => '25-35'],
        ],
        'inr_only' => [
            ['name' => 'INR', 'unit' => 'Ratio', 'reference' => '0.8-1.2'],
        ],
        'coagulation' => [
            ['name' => 'PT', 'unit' => 'Sec.', 'reference' => '11-14'],
            ['name' => 'Control (PT)', 'unit' => 'Sec.', 'reference' => '11-14'],
            ['name' => 'INR', 'unit' => 'Ratio', 'reference' => '0.8-1.2'],
            ['name' => 'aPTT', 'unit' => 'Sec.', 'reference' => '25-35'],
        ],
        'electrolytes' => [
            ['name' => 'Sodium', 'unit' => 'mEq/L', 'reference' => '136-145'],
            ['name' => 'Potassium', 'unit' => 'mEq/L', 'reference' => '3.5-5.1'],
            ['name' => 'Chloride', 'unit' => 'mEq/L', 'reference' => '98-107'],
            ['name' => 'Calcium', 'unit' => 'mg/dL', 'reference' => '8.5-10.5'],
        ],
        'tft' => [
            ['name' => 'T3', 'unit' => 'ng/dL', 'reference' => '80-200'],
            ['name' => 'T4', 'unit' => 'ug/dL', 'reference' => '5.0-12.0'],
            ['name' => 'TSH', 'unit' => 'mIU/L', 'reference' => '0.4-4.0'],
        ],
        'hepatitis_screening' => [
            ['name' => 'HBsAg', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'Anti-HCV', 'unit' => null, 'reference' => 'Negative'],
        ],
        'pregnancy' => [
            ['name' => 'Pregnancy Test', 'unit' => null, 'reference' => 'Negative'],
        ],
        'culture' => [
            ['name' => 'Specimen Type', 'unit' => null, 'reference' => null],
            ['name' => 'Organism Isolated', 'unit' => null, 'reference' => null],
            ['name' => 'Colony Count', 'unit' => 'CFU/mL', 'reference' => 'Significant: >10^5 CFU/mL (urine)'],
            ['name' => 'Antibiotic Sensitivity', 'unit' => null, 'reference' => 'S / I / R per CLSI M100'],
        ],
        'torch' => [
            ['name' => 'Toxoplasma IgG', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'Toxoplasma IgM', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'Rubella IgG', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'Rubella IgM', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'CMV IgG', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'CMV IgM', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'HSV IgG', 'unit' => null, 'reference' => 'Negative'],
            ['name' => 'HSV IgM', 'unit' => null, 'reference' => 'Negative'],
        ],
        'ogtt' => [
            ['name' => 'Fasting Blood Glucose', 'unit' => 'mg/dL', 'reference' => '70-100'],
            ['name' => '1 Hour Blood Glucose', 'unit' => 'mg/dL', 'reference' => '<180'],
            ['name' => '2 Hour Blood Glucose', 'unit' => 'mg/dL', 'reference' => '<140'],
        ],
        'hba1c' => [
            ['name' => 'HbA1c', 'unit' => '%', 'reference' => 'Normal: <5.7 | Prediabetes: 5.7-6.4 | Diabetes: >=6.5'],
        ],
    ];
}
