<?php

namespace App\Support;

class LabParameterOptions
{
    /**
     * Standard color options for Urine and bodily fluids.
     *
     * @return list<string>
     */
    public static function urineColorOptions(): array
    {
        return [
            'Pale Yellow',
            'Yellow',
            'Dark Yellow',
            'Amber',
            'Straw',
            'Reddish',
            'Red / Bloody',
            'Orange',
            'Brown',
            'Dark Brown',
            'Colorless / Clear',
            'Milky / White',
            'Greenish',
            'Smoky',
        ];
    }

    /**
     * Standard clarity / turbidity options.
     *
     * @return list<string>
     */
    public static function turbidityOptions(): array
    {
        return [
            'Clear',
            'Slightly Cloudy',
            'Cloudy',
            'Turbid',
            'Hazy',
        ];
    }

    /**
     * Standard deposit / sediment options.
     *
     * @return list<string>
     */
    public static function depositOptions(): array
    {
        return [
            'Nil',
            'Trace',
            'Slight',
            'Moderate',
            'Heavy',
            'Present',
        ];
    }

    /**
     * Standard chemical strip test options (Protein, Glucose, Ketone, Bilirubin, Blood, Nitrite, etc.).
     *
     * @return list<string>
     */
    public static function chemicalDipstickOptions(): array
    {
        return [
            'Nil',
            'Negative',
            'Trace',
            '+ (Present)',
            '++ (Moderate)',
            '+++ (Marked)',
            '++++ (Large)',
            'Positive',
        ];
    }

    /**
     * Standard microscopic cell count ranges (Pus Cells, RBCs, Epithelial Cells per HPF).
     *
     * @return list<string>
     */
    public static function microscopicCellOptions(): array
    {
        return [
            'Nil',
            '0 - 1',
            '1 - 2',
            '2 - 4',
            '3 - 5',
            '4 - 6',
            '6 - 8',
            '8 - 10',
            '10 - 15',
            '15 - 20',
            '20 - 25',
            '25 - 30',
            '30 - 40',
            '40 - 50',
            'Plenty',
            'Crowded',
        ];
    }

    /**
     * Standard crystals and casts options.
     *
     * @return list<string>
     */
    public static function castAndCrystalOptions(): array
    {
        return [
            'Nil',
            'Few',
            'Moderate',
            'Numerous',
            'Present',
            '0 - 1',
            '1 - 2',
            'Seen',
            'Not Seen',
        ];
    }

    /**
     * Standard organism / bacteria options.
     *
     * @return list<string>
     */
    public static function organismOptions(): array
    {
        return [
            'Nil',
            'Few',
            'Moderate',
            'Plenty',
            'Present',
            'Seen',
            'Not Seen',
        ];
    }

    /**
     * Standard qualitative / serology test options.
     *
     * @return list<string>
     */
    public static function qualitativeOptions(): array
    {
        return [
            'Negative',
            'Positive',
            'Non-Reactive',
            'Reactive',
        ];
    }

    /**
     * Resolve dropdown options list for a given parameter name and optional test context.
     *
     * @param  string  $parameterName
     * @param  string  $testName
     * @return list<string>|null
     */
    public static function getOptionsFor(string $parameterName, string $testName = ''): ?array
    {
        $normalized = strtolower(trim(rtrim($parameterName, '.')));
        $testNormalized = strtolower(trim($testName));

        // Color / Colour
        if (str_contains($normalized, 'color') || str_contains($normalized, 'colour')) {
            return self::urineColorOptions();
        }

        // Turbidity / Clarity / Appearance
        if (str_contains($normalized, 'turbid') || str_contains($normalized, 'appearance') || str_contains($normalized, 'clarity')) {
            return self::turbidityOptions();
        }

        // Deposit / Sediment / Consistency
        if (str_contains($normalized, 'deposit') || str_contains($normalized, 'sediment') || str_contains($normalized, 'consistency')) {
            return self::depositOptions();
        }

        // Urine / Fluid Chemical Tests
        if (
            str_contains($testNormalized, 'urine') ||
            str_contains($testNormalized, 'c/e') ||
            str_contains($testNormalized, 'routine') ||
            str_contains($testNormalized, 'stool') ||
            str_contains($testNormalized, 'fluid')
        ) {
            // Dipstick chemical parameters
            if (
                in_array($normalized, ['protein', 'glucose', 'sugar', 'ketone', 'ketones', 'bilirubin', 'blood', 'nitrite', 'leukocytes', 'leukocyte', 'urobilinogen', 'reducing substances', 'reducing substance', 'myoglobins', 'hemosiderin', 'fat globules'])
            ) {
                return self::chemicalDipstickOptions();
            }

            // Microscopic cells
            if (
                str_contains($normalized, 'pus cell') ||
                str_contains($normalized, 'pus cells') ||
                str_contains($normalized, 'epithelial') ||
                str_starts_with($normalized, 'rbc') ||
                $normalized === 'rbc' ||
                $normalized === 'wbc' ||
                $normalized === 'pus cells' ||
                $normalized === 'red blood cells'
            ) {
                return self::microscopicCellOptions();
            }

            // Crystals & Casts
            if (
                str_contains($normalized, 'crystal') ||
                str_contains($normalized, 'cast') ||
                str_contains($normalized, 'amorphous') ||
                str_contains($normalized, 'phosphate') ||
                str_contains($normalized, 'oxalate') ||
                str_contains($normalized, 'hyaline') ||
                str_contains($normalized, 'granular')
            ) {
                return self::castAndCrystalOptions();
            }

            // Bacteria & Organisms
            if (
                str_contains($normalized, 'bacteria') ||
                str_contains($normalized, 'yeast') ||
                str_contains($normalized, 'trichomonas') ||
                str_contains($normalized, 'mucus') ||
                str_contains($normalized, 'fungus')
            ) {
                return self::organismOptions();
            }
        }

        // Serology / Qualitative Rapid Tests
        if (
            str_contains($testNormalized, 'hbsag') ||
            str_contains($testNormalized, 'hcv') ||
            str_contains($testNormalized, 'hiv') ||
            str_contains($testNormalized, 'vdrl') ||
            str_contains($testNormalized, 'typhoid') ||
            str_contains($testNormalized, 'widal') ||
            str_contains($testNormalized, 'pregnancy') ||
            str_contains($testNormalized, 'malaria') ||
            str_contains($testNormalized, 'ict') ||
            str_contains($testNormalized, 'troponin') ||
            str_contains($testNormalized, 'dengue')
        ) {
            return self::qualitativeOptions();
        }

        return null;
    }
}
