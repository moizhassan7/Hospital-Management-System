<?php

namespace Database\Seeders;

/**
 * Maps pathology tests to standard blood collection tubes / specimen containers.
 *
 * Based on CLSI order-of-draw guidelines, Cleveland Clinic Laboratories,
 * Labcorp specimen requirements, and NCBI StatPearls phlebotomy references.
 */
class PathologyVialMapper
{
  public const VIAL_EDTA = 'EDTA (Purple)';
  public const VIAL_PLAIN = 'Plain (Red)';
  public const VIAL_FLUORIDE = 'Fluoride (Gray)';
  public const VIAL_CITRATE = 'Citrate (Blue)';
  public const VIAL_HEPARIN = 'Heparin (Green)';
  public const VIAL_URINE = 'Urine Container';
  public const VIAL_STOOL = 'Stool Container';
  public const VIAL_SERUM = 'Serum Separator';
  public const VIAL_GENERAL = 'General';

  /**
   * @return array{sample_vial: string, sample_expiry_hours: int, vials_required: int}
   */
  public static function resolve(string $testName, string $headName): array
  {
    $name = strtolower(trim($testName));

    foreach (self::keywordRules() as $rule) {
      foreach ($rule['keywords'] as $keyword) {
        if (self::matchesKeyword($name, strtolower($keyword))) {
          return [
            'sample_vial' => $rule['vial'],
            'sample_expiry_hours' => $rule['expiry'],
            'vials_required' => $rule['vials'],
          ];
        }
      }
    }

    return self::headDefaults()[$headName] ?? self::defaultBloodSerum();
  }

  private static function matchesKeyword(string $name, string $keyword): bool
  {
    if (str_starts_with($keyword, '/') && str_ends_with($keyword, '/')) {
      return (bool) preg_match($keyword, $name);
    }

    return str_contains($name, $keyword);
  }

  /**
   * @return array<string, array{sample_vial: string, sample_expiry_hours: int, vials_required: int}>
   */
  private static function headDefaults(): array
  {
    return [
      'Hematology' => [
        'sample_vial' => self::VIAL_EDTA,
        'sample_expiry_hours' => 48,
        'vials_required' => 1,
      ],
      'Biochemistry' => [
        'sample_vial' => self::VIAL_SERUM,
        'sample_expiry_hours' => 72,
        'vials_required' => 1,
      ],
      'Immunology' => [
        'sample_vial' => self::VIAL_SERUM,
        'sample_expiry_hours' => 72,
        'vials_required' => 1,
      ],
      'Serology' => [
        'sample_vial' => self::VIAL_SERUM,
        'sample_expiry_hours' => 72,
        'vials_required' => 1,
      ],
      'Microbiology' => [
        'sample_vial' => self::VIAL_GENERAL,
        'sample_expiry_hours' => 24,
        'vials_required' => 1,
      ],
      'Blood Bank' => [
        'sample_vial' => self::VIAL_EDTA,
        'sample_expiry_hours' => 48,
        'vials_required' => 1,
      ],
      'Histopathology' => [
        'sample_vial' => self::VIAL_GENERAL,
        'sample_expiry_hours' => 72,
        'vials_required' => 1,
      ],
      'Coagulation' => [
        'sample_vial' => self::VIAL_CITRATE,
        'sample_expiry_hours' => 6,
        'vials_required' => 1,
      ],
      'Urinalysis' => [
        'sample_vial' => self::VIAL_URINE,
        'sample_expiry_hours' => 24,
        'vials_required' => 1,
      ],
      'General Pathology' => [
        'sample_vial' => self::VIAL_GENERAL,
        'sample_expiry_hours' => 24,
        'vials_required' => 1,
      ],
    ];
  }

  /**
   * @return array<int, array{keywords: string[], vial: string, expiry: int, vials: int}>
   */
  private static function keywordRules(): array
  {
    return [
      // Coagulation — sodium citrate (blue), 9:1 ratio; stable ~4–6 hrs (CLSI / Labcorp)
      [
        'keywords' => ['pt / inr', 'prothrombin', 'aptt', '/\binr\b/', 'coagulation', 'd-dimer', 'd dimer', 'fibrinogen', 'bleeding time', 'clotting time', 'thrombophilia'],
        'vial' => self::VIAL_CITRATE,
        'expiry' => 6,
        'vials' => 1,
      ],
      // GTT — multiple fluoride tubes across timepoints (5 draws standard)
      [
        'keywords' => ['g.t.t', 'gtt', 'glucose tolerance', 'oral glucose'],
        'vial' => self::VIAL_FLUORIDE,
        'expiry' => 24,
        'vials' => 5,
      ],
      // Glucose — sodium fluoride (gray) prevents glycolysis
      [
        'keywords' => ['fasting blood glucose', 'random blood glucose', ' blood glucose', 'glucose fasting', 'lactate'],
        'vial' => self::VIAL_FLUORIDE,
        'expiry' => 24,
        'vials' => 1,
      ],
      // HbA1c — EDTA whole blood (NCCLS / lab standard)
      [
        'keywords' => ['hba1c', 'hb a1c', 'glycated hemoglobin', 'glycated haemoglobin'],
        'vial' => self::VIAL_EDTA,
        'expiry' => 48,
        'vials' => 1,
      ],
      // Hematology — K2EDTA lavender/purple
      [
        'keywords' => ['cbc', 'complete blood', 'hemoglobin', 'haemoglobin', 'hematocrit', 'haematocrit', ' pcv', 'platelet', ' wbc', ' rbc', ' mcv', ' mch', ' mchc', 'esr', 'reticulocyte', 'peripheral smear', 'blood group', 'blood typing', 'rh typing', 'coombs', 'eosinophil', 'malaria parasite', ' mp smear', 'packed cell', 'differential count', 'bleeding disorder screen'],
        'vial' => self::VIAL_EDTA,
        'expiry' => 48,
        'vials' => 1,
      ],
      // Blood culture — aerobic + anaerobic bottles
      [
        'keywords' => ['blood culture'],
        'vial' => self::VIAL_GENERAL,
        'expiry' => 24,
        'vials' => 2,
      ],
      // Cross match — EDTA + possible extra sample
      [
        'keywords' => ['cross match', 'crossmatch', 'compatibility'],
        'vial' => self::VIAL_EDTA,
        'expiry' => 48,
        'vials' => 2,
      ],
      // STAT / ammonia — lithium heparin (green)
      [
        'keywords' => ['ammonia', 'blood gas', ' abg', 'arterial blood gas'],
        'vial' => self::VIAL_HEPARIN,
        'expiry' => 24,
        'vials' => 1,
      ],
      // Urine specimens
      [
        'keywords' => ['urine', 'urinary'],
        'vial' => self::VIAL_URINE,
        'expiry' => 24,
        'vials' => 1,
      ],
      // Stool specimens
      [
        'keywords' => ['stool', 'fecal', 'faecal'],
        'vial' => self::VIAL_STOOL,
        'expiry' => 48,
        'vials' => 1,
      ],
      // Semen analysis
      [
        'keywords' => ['semen'],
        'vial' => self::VIAL_GENERAL,
        'expiry' => 1,
        'vials' => 1,
      ],
      // CSF, body fluids, swabs, biopsies
      [
        'keywords' => ['csf', 'synovial', 'pericardial', 'pleural', 'ascitic', 'swab', 'biopsy', 'fnac', 'histo', 'pap smear', 'sputum', 'pus ', 'wound', 'throat', 'hvs', 'bronchial', 'skin scraping', 'nail ', 'mucosal', 'gram stain', 'afb', 'culture & sensitivity', 'culture and sensitivity', ' c/s', 'mantoux', 'mycodot'],
        'vial' => self::VIAL_GENERAL,
        'expiry' => 24,
        'vials' => 1,
      ],
      // Bone marrow — EDTA or specialized container
      [
        'keywords' => ['bone marrow'],
        'vial' => self::VIAL_EDTA,
        'expiry' => 24,
        'vials' => 2,
      ],
    ];
  }

  /**
   * @return array{sample_vial: string, sample_expiry_hours: int, vials_required: int}
   */
  private static function defaultBloodSerum(): array
  {
    return [
      'sample_vial' => self::VIAL_SERUM,
      'sample_expiry_hours' => 72,
      'vials_required' => 1,
    ];
  }
}
