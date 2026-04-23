<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestHead;
use App\Models\Test;
use App\Models\TestParticular;

class LaboratorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Hematology' => [
                'Complete Blood Count' => [
                    ['name' => 'Red Blood Cell Count (Male)', 'unit' => '10^6/µL', 'min' => 4.50, 'max' => 5.90, 'note' => 'Decreased: Anemia/Hemorrhage; Increased: Erythrocytosis'],
                    ['name' => 'Red Blood Cell Count (Female)', 'unit' => '10^6/µL', 'min' => 4.00, 'max' => 5.20, 'note' => 'Falsely decreased in hemodilution.'],
                    ['name' => 'Hemoglobin (Male)', 'unit' => 'g/dL', 'min' => 13.5, 'max' => 17.5, 'note' => 'Primary oxygen-carrying protein. Panic <7 or >20.'],
                    ['name' => 'Hemoglobin (Female)', 'unit' => 'g/dL', 'min' => 12.0, 'max' => 16.0, 'note' => 'Severity of anemia grade.'],
                    ['name' => 'Hematocrit (Male)', 'unit' => '%', 'min' => 41.0, 'max' => 53.0, 'note' => 'Volume proportion of erythrocytes.'],
                    ['name' => 'Hematocrit (Female)', 'unit' => '%', 'min' => 36.0, 'max' => 46.0, 'note' => 'Elevated in severe dehydration.'],
                    ['name' => 'Mean Corpuscular Volume (MCV)', 'unit' => 'fL/cell', 'min' => 80, 'max' => 100, 'note' => '<80: Microcytosis; >100: Macrocytosis.'],
                    ['name' => 'White Blood Cell (WBC)', 'unit' => '10^3/µL', 'min' => 4.5, 'max' => 11.0, 'note' => 'Leukocytosis suggests infection. Panic >50.'],
                    ['name' => 'Platelet Count (PLT)', 'unit' => '10^3/µL', 'min' => 150, 'max' => 400, 'note' => 'Panic low <20; high >1000.'],
                ],
                'Coagulation Profile' => [
                    ['name' => 'Prothrombin Time (PT)', 'unit' => 'seconds', 'min' => 10, 'max' => 13, 'note' => 'Extrinsic pathway assessment.'],
                    ['name' => 'International Normalized Ratio (INR)', 'unit' => 'ratio', 'min' => 0.9, 'max' => 1.1, 'note' => 'Warfarin target: 2.0-3.0.'],
                ],
            ],
            'Biochemistry' => [
                'Basic Metabolic Panel' => [
                    ['name' => 'Sodium (Na+)', 'unit' => 'mEq/L', 'min' => 136, 'max' => 146, 'note' => 'Major extracellular cation.'],
                    ['name' => 'Potassium (K+)', 'unit' => 'mEq/L', 'min' => 3.5, 'max' => 5.0, 'note' => 'Cardiac function. Panic <3.0 or >6.0.'],
                    ['name' => 'BUN', 'unit' => 'mg/dL', 'min' => 8, 'max' => 23, 'note' => 'Renal impairment/dehydration marker.'],
                    ['name' => 'Creatinine (SCr)', 'unit' => 'mg/dL', 'min' => 0.6, 'max' => 1.2, 'note' => 'Specific glomerular filtration marker.'],
                    ['name' => 'Glucose (Fasting)', 'unit' => 'mg/dL', 'min' => 70, 'max' => 100, 'note' => 'Fasting >126 suggests diabetes.'],
                ],
                'Hepatic Function Panel' => [
                    ['name' => 'Albumin', 'unit' => 'g/dL', 'min' => 3.5, 'max' => 5.5, 'note' => 'Chronic hepatic disease marker.'],
                    ['name' => 'ALT', 'unit' => 'U/L', 'min' => 10, 'max' => 40, 'note' => 'Specific biomarker for acute hepatic injury.'],
                    ['name' => 'AST', 'unit' => 'U/L', 'min' => 12, 'max' => 38, 'note' => 'Elevated in alcoholic liver disease.'],
                ],
                'Lipid Panel' => [
                    ['name' => 'LDL Cholesterol', 'unit' => 'mg/dL', 'min' => 0, 'max' => 99, 'note' => 'Primary atherogenic lipid.'],
                ],
                'Cardiac Markers' => [
                    ['name' => 'Troponin I', 'unit' => 'ng/mL', 'min' => 0.00, 'max' => 0.04, 'note' => 'Specific for myocardial infarction.'],
                ],
            ],
            'Endocrinology' => [
                'Thyroid Panel' => [
                    ['name' => 'TSH', 'unit' => 'mIU/L', 'min' => 0.3, 'max' => 4.0, 'note' => 'Pituitary hormone for thyroid function.'],
                    ['name' => 'Free T4', 'unit' => 'ng/dL', 'min' => 0.7, 'max' => 2.1, 'note' => 'Depressed in hypothyroidism.'],
                ],
                'Adrenal Function' => [
                    ['name' => 'Cortisol (8:00 AM)', 'unit' => 'µg/dL', 'min' => 5.0, 'max' => 25.0, 'note' => 'Highest in early morning.'],
                ],
            ],
            'Microbiology' => [
                'Blood Culture' => [
                    ['name' => 'Aerobic/Anaerobic Growth', 'unit' => 'Text', 'min' => null, 'max' => null, 'note' => 'Any growth is a critical panic value.'],
                ],
                'Respiratory Culture' => [
                    ['name' => 'Sputum Culture', 'unit' => 'Text', 'min' => null, 'max' => null, 'note' => 'Identifies lower respiratory pathogens.'],
                ],
                'Infectious PCR' => [
                    ['name' => 'SARS-CoV-2 PCR', 'unit' => 'Text', 'min' => null, 'max' => null, 'note' => 'Direct molecular detection of viral RNA.'],
                ],
            ],
            'Urinalysis' => [
                'Physio-chemical' => [
                    ['name' => 'pH', 'unit' => 'unit', 'min' => 5.0, 'max' => 8.0, 'note' => 'High pH may indicate urea-splitting UTI.'],
                    ['name' => 'Specific Gravity', 'unit' => 'ratio', 'min' => 1.003, 'max' => 1.030, 'note' => "Kidney's concentrating ability."],
                ],
                'Microscopic Analysis' => [
                    ['name' => 'WBC', 'unit' => '/hpf', 'min' => 0, 'max' => 5, 'note' => 'Pyuria (>10/hpf) defines infection.'],
                ],
            ],
            'Toxicology' => [
                'Therapeutic Drugs' => [
                    ['name' => 'Acetaminophen', 'unit' => 'µg/mL', 'min' => 10, 'max' => 20, 'note' => 'Toxicity risk >150 µg/mL.'],
                    ['name' => 'Phenytoin', 'unit' => 'µg/mL', 'min' => 10, 'max' => 20, 'note' => 'Anti-epileptic with narrow index.'],
                ],
            ],
        ];

        $testCounter = 1;
        foreach ($data as $headName => $tests) {
            $head = TestHead::firstOrCreate(['name' => $headName]);

            foreach ($tests as $testName => $particulars) {
                $test = Test::firstOrCreate(
                    ['name' => $testName, 'test_head_id' => $head->id],
                    [
                        'test_id' => 'T' . str_pad($testCounter++, 3, '0', STR_PAD_LEFT),
                        'price' => 500.00, // Default price
                        'type' => 'Standard',
                        'priority' => 'Normal',
                        'report_time' => 24,
                    ]
                );

                foreach ($particulars as $p) {
                    TestParticular::firstOrCreate(
                        ['name' => $p['name'], 'test_id' => $test->id],
                        [
                            'unit' => $p['unit'],
                            'normal_range_min' => $p['min'],
                            'normal_range_max' => $p['max'],
                            'reference_text' => $p['note'],
                        ]
                    );
                }
            }
        }
    }
}
