<?php

namespace App\Jobs;

use App\Exports\LaboratoryPatientsExport;
use App\Services\LaboratoryPatientExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportLaboratoryPatientsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(
        public readonly string $dateFrom,
        public readonly string $dateTo,
        public readonly ?string $outputPath = null,
    ) {}

    public function handle(LaboratoryPatientExportService $exportService): string
    {
        $fileName = $exportService->buildExportFileName($this->dateFrom, $this->dateTo);
        $relativePath = $this->outputPath ?? 'exports/' . $fileName;

        Storage::disk('local')->makeDirectory('exports');

        Excel::store(
            new LaboratoryPatientsExport($this->dateFrom, $this->dateTo),
            $relativePath,
            'local',
        );

        $fullPath = Storage::disk('local')->path($relativePath);

        Log::info('Laboratory patient export completed.', [
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'path' => $fullPath,
        ]);

        return $fullPath;
    }
}
