<?php
/**
 * One-time UI migration script — normalizes Blade views to HMS design system classes.
 * Run: php scripts/migrate-ui-classes.php
 */

$viewsPath = __DIR__ . '/../resources/views';
$excludePatterns = ['/print/', '/pdf/', 'print_', '_pdf', 'dbconn', 'login.blade'];

$replacements = [
    // Inputs
    'shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent' => 'hms-input',
    'shadow appearance-none border rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent' => 'hms-input hms-input-lg',
    'shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent flex-grow' => 'hms-input flex-1',
    'shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight focus:outline-none' => 'hms-input hms-input-readonly',
    'shadow appearance-none border rounded-lg w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none' => 'hms-input',

    // Labels
    'block text-gray-700 text-sm font-bold mb-2' => 'hms-label',
    'block text-gray-700 text-sm font-semibold mb-2' => 'hms-label',

    // Form grids
    'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6' => 'hms-form-grid mb-6',
    'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' => 'hms-form-grid',
    'grid grid-cols-1 md:grid-cols-2 gap-6 mb-6' => 'hms-form-grid-2 mb-6',
    'grid grid-cols-1 md:grid-cols-2 gap-6' => 'hms-form-grid-2',
    'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4' => 'hms-detail-grid',
    'grid grid-cols-1 md:grid-cols-3 gap-6' => 'hms-form-grid',

    // Panels
    'hms-panel mb-8' => 'hms-panel hms-panel-padded mb-5',
    'class="hms-panel"' => 'class="hms-panel hms-panel-padded"',
    'hms-panel overflow-hidden' => 'hms-panel hms-panel-flush',

    // Tables
    'overflow-x-auto mb-6' => 'hms-table-wrap mb-5',
    'overflow-x-auto' => 'hms-table-wrap',
    'min-w-full bg-white rounded-lg overflow-hidden border border-gray-200' => 'hms-table',
    'min-w-full bg-white rounded-lg overflow-hidden' => 'hms-table',
    'min-w-full leading-normal' => 'hms-table',

    // Form actions
    'flex justify-end space-x-4 mt-6' => 'hms-form-actions',
    'flex items-center justify-end space-x-4 mt-6' => 'hms-form-actions',
    'flex items-end justify-end' => 'flex items-end justify-end',

    // Buttons — primary blues
    'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2' => 'hms-btn hms-btn-primary',
    'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition-colors duration-200 ease-in-out flex items-center' => 'hms-btn hms-btn-primary',
    'bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full' => 'hms-btn hms-btn-primary w-full hms-btn-lg',

    // Other button colors
    'bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2' => 'hms-btn hms-btn-secondary',
    'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2' => 'hms-btn hms-btn-danger',
    'bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2' => 'hms-btn hms-btn-success',
    'bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2' => 'hms-btn hms-btn-indigo',
    'bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out' => 'hms-btn hms-btn-purple',
    'bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2' => 'hms-btn hms-btn-purple',
    'bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2' => 'hms-btn hms-btn-teal',
    'bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-full shadow-lg transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2' => 'hms-btn hms-btn-warning',

    // Badges
    'bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full' => 'hms-badge hms-badge-blue',

    // Search bars
    'flex items-center space-x-4' => 'hms-search-bar',

    // Checkbox
    'form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500' => 'hms-checkbox',
    'inline-flex items-center' => 'hms-checkbox-row',

];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

$changed = 0;
$files = 0;

foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $relative = str_replace('\\', '/', substr($path, strlen($viewsPath)));

    foreach ($excludePatterns as $pattern) {
        if (str_contains($relative, $pattern)) {
            continue 2;
        }
    }

    if (!str_ends_with($relative, '.blade.php')) {
        continue;
    }

    // Skip layouts/components/partials that we maintain manually
    if (str_starts_with($relative, '/components/') || str_starts_with($relative, '/layouts/')) {
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;
    $files++;

    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }

    // Normalize page toolbar structure
    $content = preg_replace(
        '/<div class="hms-page-toolbar">\s*<h2 class="hms-page-heading">([^<]+)<\/h2>/',
        '<div class="hms-page-toolbar"><div><h2 class="hms-page-heading">$1</h2></div>',
        $content
    );

    if ($content !== $original) {
        file_put_contents($path, $content);
        $changed++;
        echo "Updated: {$relative}\n";
    }
}

echo "\nDone. Scanned {$files} files, updated {$changed}.\n";
