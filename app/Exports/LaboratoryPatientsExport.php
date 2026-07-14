<?php

namespace App\Exports;

use App\Services\LaboratoryPatientExportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaboratoryPatientsExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo,
    ) {}

    /**
     * @return array<int, LaboratoryPatientsSheetExport>
     */
    public function sheets(): array
    {
        $service = app(LaboratoryPatientExportService::class);

        return [
            new LaboratoryPatientsSheetExport(
                title: 'Patient Summary',
                headings: [
                    'Registered At',
                    'Last Updated At',
                    'Lab Registration No',
                    'MR No',
                    'Patient Name',
                    'Gender',
                    'Age',
                    'Contact No',
                    'File No',
                    'Referred By',
                    'Self Referred',
                    'Desktop Invoice',
                    'Tests',
                    'Test Count',
                    'Sub Total',
                    'Discount',
                    'Grand Total',
                    'Paid Amount',
                    'Due Amount',
                    'Previous Due',
                    'Status',
                ],
                rows: $service->getPatientSummaryRows($this->dateFrom, $this->dateTo),
            ),
            new LaboratoryPatientsSheetExport(
                title: 'Patient Test Details',
                headings: [
                    'Registered At',
                    'Last Updated At',
                    'Lab Registration No',
                    'MR No',
                    'Patient Name',
                    'Gender',
                    'Age',
                    'Contact No',
                    'File No',
                    'Referred By',
                    'Self Referred',
                    'Desktop Invoice',
                    'Test Name',
                    'Test Price',
                    'Test Status',
                    'Sample Status',
                    'Sample Collected At',
                    'Sample Received In Lab At',
                    'Result Completed At',
                    'Result Entered By',
                    'Has Results',
                    'Sub Total',
                    'Discount',
                    'Grand Total',
                    'Paid Amount',
                    'Due Amount',
                    'Previous Due',
                    'Patient Status',
                ],
                rows: $service->getPatientTestDetailRows($this->dateFrom, $this->dateTo),
            ),
        ];
    }
}
