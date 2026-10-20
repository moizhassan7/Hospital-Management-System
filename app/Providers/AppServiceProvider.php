<?php

namespace App\Providers;

use App\Models\DayCareProcedure;
use App\Models\EmergencyPatient;
use App\Models\IndoorPatient;
use App\Models\IndoorPatientAdvance;
use App\Models\LaboratoryPatient;
use App\Models\OpdAppointment;
use App\Models\PatientDischarge;
use App\Services\LedgerPostingService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        $collectionModels = [
            OpdAppointment::class,
            IndoorPatient::class,
            IndoorPatientAdvance::class,
            EmergencyPatient::class,
            DayCareProcedure::class,
            LaboratoryPatient::class,
            PatientDischarge::class,
        ];

        foreach ($collectionModels as $model) {
            $model::saved(function ($record) {
                if (Schema::hasTable('ledger_entries')) {
                    app(LedgerPostingService::class)->syncModel($record);
                }
            });
            $model::deleting(function ($record) {
                if (Schema::hasTable('ledger_entries')) {
                    app(LedgerPostingService::class)->reverseModel($record, auth()->id());
                }
            });
        }
    }
}
