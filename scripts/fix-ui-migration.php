<?php
/** Fix incorrect hms-filter-panel usage + normalize flash alerts + fix button class corruption */

$viewsPath = __DIR__ . '/../resources/views';
$exclude = ['print', 'pdf', 'dbconn', '/components/', '/layouts/'];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
$changed = 0;

$flashSuccess = '/@if\s*\(\s*session\(\'success\'\)\s*\)\s*<div class="bg-green-100[^@]+@endif\s*/s';
$flashError = '/@if\s*\(\s*\$errors->any\(\)\s*\)\s*<div class="bg-red-100[^@]+@endif\s*/s';

foreach ($iterator as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    $path = $file->getPathname();
    $rel = str_replace('\\', '/', $path);
    foreach ($exclude as $e) { if (str_contains($rel, $e)) continue 2; }

    $c = file_get_contents($path);
    $orig = $c;

    // Revert mistaken filter-panel on full form panels
    $c = str_replace('hms-filter-panel', 'hms-panel hms-panel-padded mb-5', $c);

    // Fix corrupted button classes from checkbox migration
    $c = str_replace('class="hms-checkbox-row bg-purple-600 hover:bg-purple-700 text-white font-medium py-1.5 px-3 rounded-lg text-xs whitespace-nowrap"', 'class="hms-btn hms-btn-purple hms-btn-sm"', $c);

    // Normalize selects that still use hms-input
    $c = preg_replace('/<select([^>]*?)class="hms-input"/', '<select$1class="hms-select"', $c);

    // Flash alerts → partial (only standard patterns)
    if (preg_match($flashSuccess, $c) || preg_match($flashError, $c)) {
        $c = preg_replace($flashSuccess, "@include('partials.flash-alerts')\n\n", $c);
        $c = preg_replace($flashError, '', $c); // errors handled by flash partial
        // Deduplicate if both were present
        $c = preg_replace("/@include\('partials\.flash-alerts'\)\s*\n\s*@include\('partials\.flash-alerts'\)/", "@include('partials.flash-alerts')", $c);
    }

    // Icon action buttons in tables
    $c = str_replace('class="p-2 bg-yellow-100 text-yellow-600 rounded-lg hover:bg-yellow-200 transition-colors"', 'class="hms-btn hms-btn-icon hms-btn-icon-edit"', $c);
    $c = str_replace('class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"', 'class="hms-btn hms-btn-icon hms-btn-icon-delete"', $c);
    $c = str_replace('class="flex item-center justify-center space-x-2"', 'class="hms-table-actions"', $c);

    // Table action links
    $c = str_replace('class="text-red-600 hover:text-red-900 remove-test-btn"', 'class="hms-btn hms-btn-ghost hms-btn-sm text-red-600 remove-test-btn"', $c);

    if ($c !== $orig) {
        file_put_contents($path, $c);
        $changed++;
        echo "Fixed: " . basename($path) . "\n";
    }
}

echo "Fixed {$changed} files.\n";
