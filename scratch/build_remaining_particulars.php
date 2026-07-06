<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$zeroTests = json_decode(file_get_contents(__DIR__ . '/zero_particular_tests.json'), true);
$excelRaw = file_get_contents(__DIR__ . '/../database/seeders/particulars_data.json');
$excelRaw = preg_replace('/^\xEF\xBB\xBF/', '', $excelRaw);
$excelRows = json_decode($excelRaw, true);

// Group excel rows by test_excel_id and test_name
$byExcelId = [];
$byTestName = [];
foreach ($excelRows as $row) {
    $id = trim((string) ($row['test_excel_id'] ?? ''));
    $tn = strtolower(trim((string) ($row['test_name'] ?? '')));
    if ($id !== '') {
        $byExcelId[$id][] = $row;
    }
    if ($tn !== '') {
        $byTestName[$tn][] = $row;
    }
}

function normalize(string $s): string
{
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', ' ', $s) ?? $s;

    return trim(preg_replace('/\s+/', ' ', $s) ?? $s);
}

function rowToParticular(array $row): array
{
    $male = trim((string) ($row['male'] ?? ''));
    $female = trim((string) ($row['female'] ?? ''));
    $child = trim((string) ($row['child'] ?? ''));
    $refParts = array_filter([$male, $female, $child]);
    $reference = null;
    if ($male !== '' && ($female !== '' || $child !== '')) {
        $reference = 'Male: ' . $male;
        if ($female !== '') {
            $reference .= ' | Female: ' . $female;
        }
        if ($child !== '') {
            $reference .= ' | Child: ' . $child;
        }
    } elseif ($male !== '') {
        $reference = $male;
    }

    return [
        'name' => trim((string) $row['name']),
        'unit' => trim((string) ($row['unit'] ?? '')) ?: null,
        'reference' => $reference,
    ];
}

function defaultParticularsForTest(array $test): array
{
    $name = $test['name'];
    $norm = normalize($name);
    $head = normalize((string) ($test['head'] ?? ''));

    // Panel templates (CLSI / common lab practice)
    $panels = [
        'tft' => [
            ['name' => 'TSH', 'unit' => 'mIU/L', 'reference' => '0.4-4.0'],
            ['name' => 'Free T3', 'unit' => 'pg/mL', 'reference' => '2.0-4.4'],
            ['name' => 'Free T4', 'unit' => 'ng/dL', 'reference' => '0.8-1.8'],
        ],
        'thyroid t3 t4 tsh' => [
            ['name' => 'T3', 'unit' => 'ng/dL', 'reference' => '80-200'],
            ['name' => 'T4', 'unit' => 'ug/dL', 'reference' => '5.0-12.0'],
            ['name' => 'TSH', 'unit' => 'mIU/L', 'reference' => '0.4-4.0'],
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
        'widal' => [
            ['name' => 'Salmonella typhi O', 'unit' => null, 'reference' => '<1:80'],
            ['name' => 'Salmonella typhi H', 'unit' => null, 'reference' => '<1:80'],
            ['name' => 'Salmonella paratyphi AH', 'unit' => null, 'reference' => '<1:80'],
            ['name' => 'Salmonella paratyphi BH', 'unit' => null, 'reference' => '<1:80'],
        ],
        'coombs direct' => [
            ['name' => 'Direct Coombs Test', 'unit' => null, 'reference' => 'Negative'],
        ],
        'coombs indirect' => [
            ['name' => 'Indirect Coombs Test', 'unit' => null, 'reference' => 'Negative'],
        ],
        'csf examination' => [
            ['name' => 'Appearance', 'unit' => null, 'reference' => 'Clear'],
            ['name' => 'Colour', 'unit' => null, 'reference' => 'Colourless'],
            ['name' => 'Protein', 'unit' => 'mg/dL', 'reference' => '15-45'],
            ['name' => 'Glucose', 'unit' => 'mg/dL', 'reference' => '40-70'],
            ['name' => 'WBC Count', 'unit' => '/cumm', 'reference' => '0-5'],
            ['name' => 'RBC Count', 'unit' => '/cumm', 'reference' => 'Nil'],
        ],
        'peripheral blood film' => [
            ['name' => 'RBC Morphology', 'unit' => null, 'reference' => 'Normocytic Normochromic'],
            ['name' => 'WBC Morphology', 'unit' => null, 'reference' => 'Normal'],
            ['name' => 'Platelets', 'unit' => null, 'reference' => 'Adequate'],
            ['name' => 'Parasites', 'unit' => null, 'reference' => 'Not seen'],
        ],
        'peripheral smear' => [
            ['name' => 'RBC Morphology', 'unit' => null, 'reference' => 'Normocytic Normochromic'],
            ['name' => 'WBC Morphology', 'unit' => null, 'reference' => 'Normal'],
            ['name' => 'Platelets', 'unit' => null, 'reference' => 'Adequate'],
            ['name' => 'Parasites', 'unit' => null, 'reference' => 'Not seen'],
        ],
        'ogtt' => [
            ['name' => 'Fasting Blood Glucose', 'unit' => 'mg/dL', 'reference' => '70-100'],
            ['name' => '1 Hour Blood Glucose', 'unit' => 'mg/dL', 'reference' => '<180'],
            ['name' => '2 Hour Blood Glucose', 'unit' => 'mg/dL', 'reference' => '<140'],
        ],
        'gct' => [
            ['name' => '1 Hour Blood Glucose', 'unit' => 'mg/dL', 'reference' => '<140'],
        ],
    ];

    // Match panel keys
    foreach ($panels as $key => $items) {
        if (str_contains($norm, str_replace(' ', '', $key)) || str_contains($norm, $key)) {
            return $items;
        }
    }

    if (str_contains($norm, 'coomb') && str_contains($norm, 'direct')) {
        return $panels['coombs direct'];
    }
    if (str_contains($norm, 'coomb') && str_contains($norm, 'indirect')) {
        return $panels['coombs indirect'];
    }
    if (str_contains($norm, 'widal')) {
        return $panels['widal'];
    }
    if (str_contains($norm, 'torch')) {
        return $panels['torch'];
    }
    if (str_contains($norm, 'thyroid') && str_contains($norm, 't3') && str_contains($norm, 'tsh')) {
        return $panels['thyroid t3 t4 tsh'];
    }
    if (str_contains($norm, 'csf') && str_contains($norm, 'exam')) {
        return $panels['csf examination'];
    }
    if (str_contains($norm, 'peripheral') && (str_contains($norm, 'film') || str_contains($norm, 'smear'))) {
        return $panels['peripheral blood film'];
    }
    if (str_contains($norm, 'oral glucose') || $norm === 'ogtt') {
        return $panels['ogtt'];
    }
    if (str_contains($norm, 'dengue') && (str_contains($norm, 'igg') || str_contains($norm, 'igm'))) {
        $p = [];
        if (str_contains($norm, 'igg') || str_contains($norm, 'igg igm')) {
            $p[] = ['name' => 'Dengue IgG', 'unit' => null, 'reference' => 'Negative'];
        }
        if (str_contains($norm, 'igm') || str_contains($norm, 'igg igm')) {
            $p[] = ['name' => 'Dengue IgM', 'unit' => null, 'reference' => 'Negative'];
        }

        return $p ?: [['name' => $name, 'unit' => null, 'reference' => 'Negative']];
    }

    // Standard single-analyte reference ranges (common lab values)
    $standards = [
        'alp' => ['name' => 'Alkaline Phosphatase', 'unit' => 'U/L', 'reference' => '44-147'],
        'cl' => ['name' => 'Chloride', 'unit' => 'mEq/L', 'reference' => '98-107'],
        'chloride' => ['name' => 'Chloride', 'unit' => 'mEq/L', 'reference' => '98-107'],
        'hdl' => ['name' => 'HDL Cholesterol', 'unit' => 'mg/dL', 'reference' => 'Male: >40 | Female: >50'],
        'ldl' => ['name' => 'LDL Cholesterol', 'unit' => 'mg/dL', 'reference' => '<100'],
        'ldh' => ['name' => 'LDH', 'unit' => 'U/L', 'reference' => '140-280'],
        'triglyceride' => ['name' => 'Triglycerides', 'unit' => 'mg/dL', 'reference' => '<150'],
        'phosphate' => ['name' => 'Phosphate', 'unit' => 'mg/dL', 'reference' => '2.5-4.5'],
        'po4' => ['name' => 'Phosphate', 'unit' => 'mg/dL', 'reference' => '2.5-4.5'],
        'platelet' => ['name' => 'Platelet Count', 'unit' => '10^3/uL', 'reference' => '150-450'],
        'plt' => ['name' => 'Platelet Count', 'unit' => '10^3/uL', 'reference' => '150-450'],
        'blood urea' => ['name' => 'Blood Urea', 'unit' => 'mg/dL', 'reference' => '15-40'],
        'magnesium' => ['name' => 'Magnesium', 'unit' => 'mg/dL', 'reference' => '1.7-2.2'],
        'magnisium' => ['name' => 'Magnesium', 'unit' => 'mg/dL', 'reference' => '1.7-2.2'],
        'psa' => ['name' => 'PSA', 'unit' => 'ng/mL', 'reference' => '<4.0'],
        'vitamin d' => ['name' => 'Vitamin D (25-OH)', 'unit' => 'ng/mL', 'reference' => '30-100'],
        'vit b12' => ['name' => 'Vitamin B12', 'unit' => 'pg/mL', 'reference' => '200-900'],
        'folic acid' => ['name' => 'Folic Acid', 'unit' => 'ng/mL', 'reference' => '3.0-17.0'],
        'folate' => ['name' => 'Folate', 'unit' => 'ng/mL', 'reference' => '3.0-17.0'],
        't3' => ['name' => 'T3', 'unit' => 'ng/dL', 'reference' => '80-200'],
        't4' => ['name' => 'T4', 'unit' => 'ug/dL', 'reference' => '5.0-12.0'],
        'tsh' => ['name' => 'TSH', 'unit' => 'mIU/L', 'reference' => '0.4-4.0'],
        'trop' => ['name' => 'Troponin', 'unit' => 'ng/mL', 'reference' => '<0.04'],
        'procalcitonin' => ['name' => 'Procalcitonin', 'unit' => 'ng/mL', 'reference' => '<0.5'],
        'ammonia' => ['name' => 'Ammonia', 'unit' => 'umol/L', 'reference' => '11-35'],
        'bicarbonate' => ['name' => 'Bicarbonate', 'unit' => 'mEq/L', 'reference' => '22-28'],
        'zinc' => ['name' => 'Zinc', 'unit' => 'ug/dL', 'reference' => '70-120'],
        'iron tibc' => ['name' => 'TIBC', 'unit' => 'ug/dL', 'reference' => '250-450'],
        'tibc' => ['name' => 'TIBC', 'unit' => 'ug/dL', 'reference' => '250-450'],
        'cea' => ['name' => 'CEA', 'unit' => 'ng/mL', 'reference' => '<3.0'],
        'ca 19' => ['name' => 'CA 19-9', 'unit' => 'U/mL', 'reference' => '<37'],
        'pth' => ['name' => 'PTH', 'unit' => 'pg/mL', 'reference' => '15-65'],
        'ige' => ['name' => 'IgE', 'unit' => 'IU/mL', 'reference' => '<100'],
        'c peptide' => ['name' => 'C-Peptide', 'unit' => 'ng/mL', 'reference' => '0.8-3.1'],
        'insulin' => ['name' => 'Insulin', 'unit' => 'uIU/mL', 'reference' => '2.6-24.9'],
        'progesterone' => ['name' => 'Progesterone', 'unit' => 'ng/mL', 'reference' => null],
        '17 oh' => ['name' => '17-OH Progesterone', 'unit' => 'ng/dL', 'reference' => null],
        'rh antibody' => ['name' => 'Rh Antibody Titre', 'unit' => null, 'reference' => 'Negative'],
        'bt' => ['name' => 'Bleeding Time', 'unit' => 'min', 'reference' => '2-7'],
        'ct' => ['name' => 'Clotting Time', 'unit' => 'min', 'reference' => '4-10'],
        'pack cell' => ['name' => 'Packed Cell Volume', 'unit' => '%', 'reference' => '35-47'],
        'pcv' => ['name' => 'Packed Cell Volume', 'unit' => '%', 'reference' => '35-47'],
        'bsr' => ['name' => 'Random Blood Sugar', 'unit' => 'mg/dL', 'reference' => '70-140'],
        'rbs' => ['name' => 'Random Blood Sugar', 'unit' => 'mg/dL', 'reference' => '70-140'],
        'ketone' => ['name' => 'Ketones', 'unit' => null, 'reference' => 'Negative'],
        'gram stain' => ['name' => 'Gram Stain', 'unit' => null, 'reference' => 'Report'],
        'afb' => ['name' => 'AFB Stain', 'unit' => null, 'reference' => 'Not seen'],
        'brucella' => ['name' => 'Brucella Agglutination', 'unit' => null, 'reference' => 'Negative'],
        'ana' => ['name' => 'ANA', 'unit' => null, 'reference' => 'Negative'],
        'anti ccp' => ['name' => 'Anti-CCP', 'unit' => 'U/mL', 'reference' => '<20'],
        'ra factor' => ['name' => 'RA Factor', 'unit' => 'IU/mL', 'reference' => '<14'],
        'vdrl' => ['name' => 'VDRL', 'unit' => null, 'reference' => 'Non-reactive'],
        'hbsag' => ['name' => 'HBsAg', 'unit' => null, 'reference' => 'Negative'],
        'hcv' => ['name' => 'Anti-HCV', 'unit' => null, 'reference' => 'Negative'],
        'hiv' => ['name' => 'HIV I & II', 'unit' => null, 'reference' => 'Negative'],
        'h pylori' => ['name' => 'H. pylori', 'unit' => null, 'reference' => 'Negative'],
        'ns1' => ['name' => 'Dengue NS1 Antigen', 'unit' => null, 'reference' => 'Negative'],
        'pcr' => ['name' => 'PCR Result', 'unit' => null, 'reference' => 'Detected/Not Detected'],
        'culture' => ['name' => 'Organism', 'unit' => null, 'reference' => null],
        'c s' => ['name' => 'Culture & Sensitivity', 'unit' => null, 'reference' => 'Report'],
        'biopsy' => ['name' => 'Histopathology Report', 'unit' => null, 'reference' => null],
        'fnac' => ['name' => 'FNAC Report', 'unit' => null, 'reference' => null],
        'cytology' => ['name' => 'Cytology Report', 'unit' => null, 'reference' => null],
        'pap smear' => ['name' => 'Pap Smear', 'unit' => null, 'reference' => null],
        'fluid exam' => ['name' => 'Appearance', 'unit' => null, 'reference' => 'Clear'],
    ];

    foreach ($standards as $key => $item) {
        if ($norm === $key || str_contains($norm, $key)) {
            return [$item];
        }
    }

    // Culture/sensitivity pattern
    if (preg_match('/\bc\s*\/\s*s\b|culture|sensiti/i', $name)) {
        return [
            ['name' => 'Organism Isolated', 'unit' => null, 'reference' => null],
            ['name' => 'Colony Count', 'unit' => null, 'reference' => null],
            ['name' => 'Sensitivity', 'unit' => null, 'reference' => null],
        ];
    }

    // Biopsy / histopathology
    if (str_contains($head, 'histopath') || str_contains($head, 'biopsy') || str_contains($norm, 'biopsy') || str_contains($norm, 'fnac')) {
        return [
            ['name' => 'Specimen', 'unit' => null, 'reference' => null],
            ['name' => 'Microscopic Findings', 'unit' => null, 'reference' => null],
            ['name' => 'Impression', 'unit' => null, 'reference' => null],
        ];
    }

    // Fluid examination
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

    // Screening / serology default
    if (preg_match('/screen|elisa|antibod|antigen|igg|igm|iga|anti /i', $name)) {
        return [['name' => $name, 'unit' => null, 'reference' => 'Negative']];
    }

    // Default: single parameter named after test
    return [['name' => $name, 'unit' => null, 'reference' => null]];
}

$output = [];
$stats = ['excel_id' => 0, 'excel_name' => 0, 'catalog_name' => 0, 'default' => 0];

foreach ($zeroTests as $test) {
    $rows = null;
    $source = 'default';

    $catalogId = trim((string) ($test['test_id'] ?? ''));
    $catalogPk = (string) ($test['id'] ?? '');

    if ($catalogId !== '' && isset($byExcelId[$catalogId])) {
        $rows = $byExcelId[$catalogId];
        $source = 'excel_id';
    } elseif ($catalogPk !== '' && isset($byExcelId[$catalogPk])) {
        $rows = $byExcelId[$catalogPk];
        $source = 'excel_id_pk';
    }

    $normName = normalize($test['name']);
    if ($rows === null) {
        foreach ($byTestName as $tn => $candidates) {
            if ($tn === $normName || str_contains($tn, $normName) || str_contains($normName, $tn)) {
                $rows = $candidates;
                $source = 'excel_name';
                break;
            }
        }
    }

    if ($rows !== null && count($rows) > 0) {
        $particulars = array_values(array_map('rowToParticular', $rows));
        $stats['excel_id']++;
    } else {
        $particulars = defaultParticularsForTest($test);
        $stats['default']++;
    }

    $output[] = [
        'test_key' => 't_' . $test['id'],
        'lookup_names' => array_values(array_unique(array_filter([
            strtolower($test['name']),
            $catalogId !== '' ? strtolower($catalogId) : null,
        ]))),
        'test_name' => $test['name'],
        'catalog_id' => (int) $test['id'],
        'source' => $source,
        'particulars' => $particulars,
    ];
}

file_put_contents(
    __DIR__ . '/../database/seeders/data/remaining_test_particulars.json',
    json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo 'Generated ' . count($output) . ' test blocks' . PHP_EOL;
echo 'Stats: ' . json_encode($stats) . PHP_EOL;
