<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$db = Illuminate\Support\Facades\DB::connection('desktop');

$tables = $db->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' AND TABLE_NAME LIKE '%invoice%' ORDER BY TABLE_NAME");
foreach ($tables as $t) {
    echo $t->TABLE_NAME . PHP_EOL;
}

foreach (['invoice', 'Invoice', 'INVOICE', 'inv'] as $name) {
    try {
        $cols = $db->select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ?", [$name]);
        if ($cols) {
            echo "\n=== {$name} columns ===\n";
            foreach ($cols as $c) {
                if (stripos($c->COLUMN_NAME, 'collect') !== false || stripos($c->COLUMN_NAME, 'simple') !== false || stripos($c->COLUMN_NAME, 'inv') !== false || $c->COLUMN_NAME === 'id') {
                    echo '  ' . $c->COLUMN_NAME . PHP_EOL;
                }
            }
            foreach ($cols as $c) {
                echo '  ' . $c->COLUMN_NAME . PHP_EOL;
            }
        }
    } catch (Throwable $e) {
    }
}
