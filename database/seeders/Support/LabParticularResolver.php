<?php

namespace Database\Seeders\Support;

/**
 * Resolves test particulars from curated JSON sources + CLSI defaults.
 */
class LabParticularResolver
{
    /** @var array<int, list<array<string, mixed>>>|null */
    private static ?array $byCatalogId = null;

    /** @var array<string, list<array<string, mixed>>>|null */
    private static ?array $byNormalizedName = null;

    /** @var array<string, list<array<string, mixed>>>|null */
    private static ?array $documentByKey = null;

    /** @var array<string, list<array<string, mixed>>>|null */
    private static ?array $excelById = null;

    /**
     * @return list<array<string, mixed>>
     */
    public static function resolve(int $id, string $name, string $head): array
    {
        self::boot();

        if (isset(LabClsiPanels::MAJOR_PANEL_MAP[$id])) {
            $rows = self::fromMajorPanelMap($id);
            if ($rows !== []) {
                return $rows;
            }
        }

        if (isset(self::$byCatalogId[$id])) {
            return self::$byCatalogId[$id];
        }

        $norm = self::normalize($name);

        if (isset(self::$byNormalizedName[$norm])) {
            return self::$byNormalizedName[$norm];
        }

        foreach (self::$byNormalizedName as $key => $rows) {
            if (str_contains($norm, $key) || str_contains($key, $norm)) {
                return $rows;
            }
        }

        if (isset(self::$excelById[(string) $id])) {
            return self::$excelById[(string) $id];
        }

        foreach (self::documentBlocks() as $block) {
            if (self::matchesLookup($norm, $block['lookup_names'] ?? [])) {
                return self::normalizeParticularRows($block['particulars'] ?? []);
            }
        }

        return LabParticularDefaults::forTest($id, $name, $head);
    }

    private static function boot(): void
    {
        if (self::$byCatalogId !== null) {
            return;
        }

        self::$byCatalogId = [];
        self::$byNormalizedName = [];
        self::$documentByKey = [];
        self::$excelById = [];

        self::loadInternationalStandards();
        self::loadBloodBank();
        self::loadDocumentBlocks();
        self::loadExcelParticulars();
    }

    private static function loadInternationalStandards(): void
    {
        $path = database_path('seeders/data/international_standards_particulars.json');
        if (! is_file($path)) {
            return;
        }

        foreach (json_decode(file_get_contents($path), true) ?: [] as $block) {
            $id = (int) ($block['catalog_id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            if (! empty($block['template']) && $block['template'] === 'culture_sensitivity') {
                self::$byCatalogId[$id] = LabClsiPanels::PANELS['culture'];
                continue;
            }

            $rows = self::normalizeParticularRows($block['particulars'] ?? []);
            if ($rows !== []) {
                self::$byCatalogId[$id] = $rows;
            }
        }
    }

    private static function loadBloodBank(): void
    {
        $path = database_path('seeders/data/blood_bank_test_particulars.json');
        if (! is_file($path)) {
            return;
        }

        foreach (json_decode(file_get_contents($path), true) ?: [] as $block) {
            $rows = self::normalizeParticularRows($block['particulars'] ?? []);
            if ($rows === []) {
                continue;
            }

            foreach ($block['lookup_names'] ?? [] as $lookup) {
                self::$byNormalizedName[self::normalize($lookup)] = $rows;
            }
        }
    }

    private static function loadDocumentBlocks(): void
    {
        foreach (self::documentBlocks() as $block) {
            $key = (string) ($block['test_key'] ?? '');
            if ($key !== '') {
                self::$documentByKey[$key] = self::normalizeParticularRows($block['particulars'] ?? []);
            }

            $rows = self::normalizeParticularRows($block['particulars'] ?? []);
            if ($rows === []) {
                continue;
            }

            foreach ($block['lookup_names'] ?? [] as $lookup) {
                self::$byNormalizedName[self::normalize($lookup)] = $rows;
            }
        }
    }

    /** @return list<array<string, mixed>> */
    private static function documentBlocks(): array
    {
        $path = database_path('seeders/data/updated_test_particulars.json');
        if (! is_file($path)) {
            return [];
        }

        return json_decode(file_get_contents($path), true) ?: [];
    }

    private static function loadExcelParticulars(): void
    {
        $path = database_path('seeders/particulars_data.json');
        if (! is_file($path)) {
            return;
        }

        $raw = preg_replace('/^\xEF\xBB\xBF/', '', file_get_contents($path));
        $rows = json_decode($raw, true);
        if (! is_array($rows)) {
            return;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $id = trim((string) ($row['test_excel_id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $grouped[$id][] = $row;
        }

        foreach ($grouped as $id => $items) {
            self::$excelById[$id] = array_map([self::class, 'excelRowToParticular'], $items);
        }
    }

    /** @return list<array<string, mixed>> */
    private static function fromMajorPanelMap(int $id): array
    {
        $map = LabClsiPanels::MAJOR_PANEL_MAP[$id];

        if (($map['source'] ?? '') === 'document') {
            $key = (string) ($map['test_key'] ?? '');

            return self::$documentByKey[$key] ?? self::lookupDocumentByKey($key);
        }

        if (($map['source'] ?? '') === 'clsi') {
            $panel = (string) ($map['panel'] ?? '');

            return LabClsiPanels::PANELS[$panel] ?? [];
        }

        return [];
    }

    /** @return list<array<string, mixed>> */
    private static function lookupDocumentByKey(string $key): array
    {
        foreach (self::documentBlocks() as $block) {
            if (($block['test_key'] ?? '') === $key) {
                return self::normalizeParticularRows($block['particulars'] ?? []);
            }
        }

        return LabClsiPanels::PANELS[$key] ?? [];
    }

    /** @param list<string> $lookups */
    private static function matchesLookup(string $norm, array $lookups): bool
    {
        foreach ($lookups as $lookup) {
            $l = self::normalize($lookup);
            if ($norm === $l || str_contains($norm, $l) || str_contains($l, $norm)) {
                return true;
            }
        }

        return false;
    }

    /** @param list<array<string, mixed>> $rows */
    private static function normalizeParticularRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $reference = $row['reference'] ?? $row['reference_note'] ?? null;
            $out[] = [
                'name' => $name,
                'unit' => isset($row['unit']) && trim((string) $row['unit']) !== '' ? trim((string) $row['unit']) : null,
                'reference' => is_string($reference) && trim($reference) !== '' ? trim($reference) : null,
                'is_calculated' => (bool) ($row['is_calculated'] ?? false),
                'formula' => $row['formula'] ?? null,
            ];
        }

        return $out;
    }

    /** @param array<string, mixed> $row */
    private static function excelRowToParticular(array $row): array
    {
        $male = trim((string) ($row['male'] ?? ''));
        $female = trim((string) ($row['female'] ?? ''));
        $child = trim((string) ($row['child'] ?? ''));
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
            'name' => trim((string) ($row['name'] ?? '')),
            'unit' => trim((string) ($row['unit'] ?? '')) ?: null,
            'reference' => $reference,
        ];
    }

    private static function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }
}
