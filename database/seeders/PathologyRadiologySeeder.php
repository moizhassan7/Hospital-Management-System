<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TestHead;
use App\Models\Test;
use App\Models\TestParticular;

class PathologyRadiologySeeder extends Seeder
{
    public function run()
    {
        // --- PATHOLOGY ---
        $pathologyData = [
            [
                'name' => 'Hematology',
                'tests' => [
                    [
                        'name' => 'Complete Blood Count (CBC)',
                        'price' => 1200,
                        'type' => 'Blood',
                        'particulars' => [
                            ['name' => 'Hemoglobin', 'unit' => 'g/dL', 'min' => 13.5, 'max' => 17.5],
                            ['name' => 'WBC Count', 'unit' => 'x10^9/L', 'min' => 4.0, 'max' => 11.0],
                            ['name' => 'Platelet Count', 'unit' => 'x10^9/L', 'min' => 150, 'max' => 450],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Biochemistry',
                'tests' => [
                    [
                        'name' => 'Blood Glucose (Random)',
                        'price' => 500,
                        'type' => 'Blood',
                        'particulars' => [
                            ['name' => 'Glucose', 'unit' => 'mg/dL', 'min' => 70, 'max' => 140],
                        ]
                    ],
                    [
                        'name' => 'Liver Function Test (LFT)',
                        'price' => 2500,
                        'type' => 'Blood',
                        'particulars' => [
                            ['name' => 'Bilirubin Total', 'unit' => 'mg/dL', 'min' => 0.1, 'max' => 1.2],
                            ['name' => 'SGPT (ALT)', 'unit' => 'U/L', 'min' => 7, 'max' => 56],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Urinalysis',
                'tests' => [
                    [
                        'name' => 'Urine Routine Examination',
                        'price' => 600,
                        'type' => 'Urine',
                        'particulars' => [
                            ['name' => 'Color', 'unit' => '', 'min' => null, 'max' => null, 'note' => 'Straw/Yellow'],
                            ['name' => 'Specific Gravity', 'unit' => '', 'min' => 1.005, 'max' => 1.030],
                            ['name' => 'Protein', 'unit' => '', 'min' => null, 'max' => null, 'note' => 'Negative'],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Microbiology',
                'tests' => [
                    [
                        'name' => 'H. Pylori (Serum)',
                        'price' => 1500,
                        'type' => 'Blood',
                        'particulars' => [
                            ['name' => 'H. Pylori Antibodies', 'unit' => 'Index', 'min' => 0, 'max' => 0.9, 'note' => 'Negative < 0.9'],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Immunology',
                'tests' => [
                    [
                        'name' => 'Thyroid Stimulating Hormone (TSH)',
                        'price' => 1800,
                        'type' => 'Blood',
                        'particulars' => [
                            ['name' => 'TSH', 'unit' => 'uIU/mL', 'min' => 0.4, 'max' => 4.0],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Serology',
                'tests' => [
                    [
                        'name' => 'Hepatitis B Surface Antigen (HBsAg)',
                        'price' => 1000,
                        'type' => 'Blood',
                        'particulars' => [
                            ['name' => 'HBsAg', 'unit' => '', 'min' => null, 'max' => null, 'note' => 'Non-reactive'],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Coagulation',
                'tests' => [
                    [
                        'name' => 'Prothrombin Time (PT)',
                        'price' => 1200,
                        'type' => 'Blood',
                        'particulars' => [
                            ['name' => 'PT Patient', 'unit' => 'Seconds', 'min' => 11, 'max' => 13.5],
                            ['name' => 'INR', 'unit' => 'Ratio', 'min' => 0.8, 'max' => 1.2],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Endocrinology',
                'tests' => [
                    [
                        'name' => 'Vitamin D (25-OH)',
                        'price' => 3500,
                        'type' => 'Blood',
                        'particulars' => [
                            ['name' => 'Vitamin D Total', 'unit' => 'ng/mL', 'min' => 30, 'max' => 100],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Histopathology',
                'tests' => [
                    [
                        'name' => 'Biopsy Small Tissue',
                        'price' => 4500,
                        'type' => 'Tissue',
                        'particulars' => [
                            ['name' => 'Microscopic Findings', 'unit' => '', 'min' => null, 'max' => null],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'Cytology',
                'tests' => [
                    [
                        'name' => 'Pap Smear',
                        'price' => 2000,
                        'type' => 'Fluid',
                        'particulars' => [
                            ['name' => 'Cytological Interpretation', 'unit' => '', 'min' => null, 'max' => null],
                        ]
                    ],
                ]
            ],
        ];

        foreach ($pathologyData as $head) {
            $th = TestHead::where('name', $head['name'])->first();
            if (!$th) {
                $th = TestHead::create(['name' => $head['name'], 'category' => 'Pathology']);
            } else {
                $th->update(['category' => 'Pathology']);
            }
            
            foreach ($head['tests'] as $test) {
                $t = Test::where('name', $test['name'])->first();
                if (!$t) {
                    $t = Test::create([
                        'test_id' => 'PATH-' . strtoupper(substr($head['name'], 0, 3)) . '-' . rand(100, 999),
                        'name' => $test['name'],
                        'price' => $test['price'],
                        'type' => $test['type'],
                        'report_format' => 'Quantitative',
                        'test_head_id' => $th->id,
                        'category' => 'Pathology',
                        'priority' => 'Routine',
                        'report_time' => 24
                    ]);
                }
                
                foreach ($test['particulars'] as $part) {
                    $tp = TestParticular::where('test_id', $t->id)->where('name', $part['name'])->first();
                    if (!$tp) {
                        TestParticular::create([
                            'test_id' => $t->id,
                            'name' => $part['name'],
                            'unit' => $part['unit'],
                            'normal_range_min' => $part['min'],
                            'normal_range_max' => $part['max'],
                            'reference_text' => $part['note'] ?? null,
                        ]);
                    }
                }
            }
        }

        // --- RADIOLOGY ---
        $radiologyData = [
            [
                'name' => 'X-Ray',
                'tests' => [
                    ['name' => 'Chest X-Ray PA View', 'price' => 800, 'type' => 'Imaging', 'template' => '<h1>Chest X-Ray Report</h1><p><b>Bony Thorax:</b> Intact.</p><p><b>Lung Fields:</b> Clear.</p><p><b>Heart:</b> Normal size.</p>'],
                    ['name' => 'X-Ray Knee AP/Lat', 'price' => 1200, 'type' => 'Imaging', 'template' => '<h1>X-Ray Knee Report</h1><p><b>Bones:</b> No fracture seen.</p><p><b>Joint Space:</b> Maintained.</p>'],
                ]
            ],
            [
                'name' => 'Ultrasound',
                'tests' => [
                    ['name' => 'Ultrasound Whole Abdomen', 'price' => 2500, 'type' => 'Imaging', 'template' => '<h1>Ultrasound Abdomen</h1><p><b>Liver:</b> Normal size and echotexture.</p><p><b>Gall Bladder:</b> No stones.</p><p><b>Kidneys:</b> Normal.</p>'],
                    ['name' => 'Ultrasound Pelvis', 'price' => 1800, 'type' => 'Imaging', 'template' => '<h1>Ultrasound Pelvis</h1><p><b>Bladder:</b> Well distended.</p><p><b>Uterus:</b> Normal.</p>'],
                ]
            ],
            [
                'name' => 'CT Scan',
                'tests' => [
                    ['name' => 'CT Brain Plain', 'price' => 6000, 'type' => 'Imaging', 'template' => '<h1>CT Brain Report</h1><p><b>Brain Parenchyma:</b> Normal.</p><p><b>Ventricles:</b> Normal.</p>'],
                ]
            ],
            [
                'name' => 'MRI',
                'tests' => [
                    ['name' => 'MRI Lumbar Spine', 'price' => 12000, 'type' => 'Imaging', 'template' => '<h1>MRI Lumbar Spine</h1><p><b>Vertebrae:</b> Alignment is normal.</p><p><b>Discs:</b> No prolapse.</p>'],
                ]
            ],
            [
                'name' => 'Mammography',
                'tests' => [
                    ['name' => 'Mammography Bilateral', 'price' => 4500, 'type' => 'Imaging', 'template' => '<h1>Mammography Report</h1><p><b>Findings:</b> No suspicious masses or calcifications.</p>'],
                ]
            ],
            [
                'name' => 'Bone Densitometry (DEXA)',
                'tests' => [
                    ['name' => 'DEXA Scan Hip/Spine', 'price' => 3500, 'type' => 'Imaging', 'template' => '<h1>DEXA Report</h1><p><b>T-Score:</b> Normal.</p>'],
                ]
            ],
            [
                'name' => 'Fluoroscopy',
                'tests' => [
                    ['name' => 'Barium Swallow', 'price' => 5500, 'type' => 'Imaging', 'template' => '<h1>Barium Swallow Report</h1><p><b>Esophagus:</b> Normal passage of contrast.</p>'],
                ]
            ],
            [
                'name' => 'Interventional Radiology',
                'tests' => [
                    ['name' => 'USG Guided Biopsy', 'price' => 8000, 'type' => 'Imaging', 'template' => '<h1>Interventional Report</h1><p><b>Procedure:</b> Biopsy taken under USG guidance.</p>'],
                ]
            ],
            [
                'name' => 'Nuclear Medicine',
                'tests' => [
                    ['name' => 'Thyroid Scan', 'price' => 7000, 'type' => 'Imaging', 'template' => '<h1>Thyroid Scan</h1><p><b>Uptake:</b> Normal.</p>'],
                ]
            ],
            [
                'name' => 'PET-CT',
                'tests' => [
                    ['name' => 'Whole Body PET-CT', 'price' => 35000, 'type' => 'Imaging', 'template' => '<h1>PET-CT Report</h1><p><b>Findings:</b> No FDG avid lesions.</p>'],
                ]
            ],
        ];

        foreach ($radiologyData as $head) {
            $th = TestHead::where('name', $head['name'])->first();
            if (!$th) {
                $th = TestHead::create(['name' => $head['name'], 'category' => 'Radiology']);
            } else {
                $th->update(['category' => 'Radiology']);
            }

            foreach ($head['tests'] as $test) {
                $t = Test::where('name', $test['name'])->first();
                if (!$t) {
                    $t = Test::create([
                        'test_id' => 'RAD-' . strtoupper(substr($head['name'], 0, 3)) . '-' . rand(100, 999),
                        'name' => $test['name'],
                        'price' => $test['price'],
                        'type' => $test['type'],
                        'report_format' => 'Radiology',
                        'template' => $test['template'] ?? null,
                        'test_head_id' => $th->id,
                        'category' => 'Radiology',
                        'priority' => 'Routine',
                        'report_time' => 48
                    ]);
                }
                
                // For Radiology, we usually add standard particulars if it's descriptive
                $particulars = ['Clinical Indication', 'Technique', 'Findings', 'Impression'];
                foreach ($particulars as $p) {
                    $tp = TestParticular::where('test_id', $t->id)->where('name', $p)->first();
                    if (!$tp) {
                        TestParticular::create(['test_id' => $t->id, 'name' => $p]);
                    }
                }
            }
        }
    }
}
