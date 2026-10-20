<?php

namespace App\Console\Commands;

use App\Models\DayCareProcedure;
use App\Models\EmergencyPatient;
use App\Models\IndoorPatient;
use App\Models\IndoorPatientAdvance;
use App\Models\LaboratoryPatient;
use App\Models\OpdAppointment;
use App\Models\PatientDischarge;
use App\Services\LedgerPostingService;
use Illuminate\Console\Command;

class BackfillAccountingLedger extends Command
{
    protected $signature = 'finance:ledger-backfill {--dry-run : Show source counts without posting}';

    protected $description = 'Idempotently post existing hospital receipts and IPD settlements to the accounting ledger';

    public function handle(LedgerPostingService $ledger): int
    {
        $models = [
            'OPD receipts' => OpdAppointment::class,
            'IPD admission advances' => IndoorPatient::class,
            'IPD advance installments' => IndoorPatientAdvance::class,
            'Emergency receipts' => EmergencyPatient::class,
            'Day care receipts' => DayCareProcedure::class,
            'Laboratory receipts' => LaboratoryPatient::class,
            'IPD discharge settlements' => PatientDischarge::class,
        ];

        foreach ($models as $label => $model) {
            $count = $model::count();
            $this->line($label.': '.$count);
            if ($this->option('dry-run')) {
                continue;
            }
            $model::query()->with($this->relations($model))->chunkById(100, function ($records) use ($ledger) {
                foreach ($records as $record) {
                    $ledger->syncModel($record);
                }
            });
        }

        $this->info($this->option('dry-run') ? 'Dry run complete.' : 'Accounting ledger backfill complete.');

        return self::SUCCESS;
    }

    private function relations(string $model): array
    {
        return match ($model) {
            IndoorPatient::class => ['patient'],
            IndoorPatientAdvance::class => ['indoorPatient.patient'],
            DayCareProcedure::class, PatientDischarge::class => ['patient'],
            default => [],
        };
    }
}
