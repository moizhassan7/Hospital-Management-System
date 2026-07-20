<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$booking = App\Models\LimsBooking::withoutGlobalScopes()->latest('id')->firstOrFail();
$lp = $booking->laboratoryPatient;
$lp->paid_amount = max(1, (float) $lp->paid_amount - 50);
$lp->due_amount = (float) $lp->grand_total - (float) $lp->paid_amount;
$lp->save();

$synced = app(App\Services\Lims\LimsBookingSync::class)->syncFromLaboratoryPatient($lp->fresh());
echo "Refresh booking={$synced->id} paid={$synced->invoice->paid_total} due={$synced->invoice->due_total} payments=".$synced->invoice->payments()->withoutGlobalScopes()->count()."\n";
echo "OK\n";
