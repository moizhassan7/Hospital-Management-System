<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$row = App\Models\Desktop\DesktopRegTest::where('inv', '1')->first()
    ?? App\Models\Desktop\DesktopRegTest::orderBy('id')->first();

if ($row) {
    print_r($row->toArray());
}
