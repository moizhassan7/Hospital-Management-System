<?php

namespace App\Console\Commands;

use App\Jobs\ExportLaboratoryPatientsJob;
use App\Services\LaboratoryPatientExportService;
use Illuminate\Console\Command;

class ExportLaboratoryPatientsCommand extends Command
{
    protected $signature = 'pathology:export-patients
                            {date_from : Start date (e.g. 11-07-2026 or 2026-07-11)}
                            {date_to : End date (e.g. 13-07-2026 or 2026-07-13)}
                            {--queue : Run export in background queue worker}
                            {--output= : Optional custom output path under storage/app}';

    protected $description = 'Export laboratory patient data for a date range to Excel';

    public function handle(LaboratoryPatientExportService $exportService): int
    {
        $dateFrom = (string) $this->argument('date_from');
        $dateTo = (string) $this->argument('date_to');

        [$from, $to] = $exportService->parseDateRange($dateFrom, $dateTo);

        $this->info(sprintf(
            'Exporting patients registered from %s to %s...',
            $from->format('d-m-Y h:i A'),
            $to->format('d-m-Y h:i A'),
        ));

        $summaryCount = $exportService->getPatientSummaryRows($dateFrom, $dateTo)->count();

        if ($summaryCount === 0) {
            $this->warn('No patients found in this date range.');

            return self::SUCCESS;
        }

        $this->line("Found {$summaryCount} patient(s).");

        if ($this->option('queue')) {
            ExportLaboratoryPatientsJob::dispatch($dateFrom, $dateTo, $this->option('output'));
            $this->info('Export job dispatched to queue. Check storage/app/exports after worker finishes.');

            return self::SUCCESS;
        }

        $path = (new ExportLaboratoryPatientsJob($dateFrom, $dateTo, $this->option('output')))->handle($exportService);

        $this->info("Excel file created: {$path}");

        return self::SUCCESS;
    }
}
