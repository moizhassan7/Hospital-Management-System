<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Models\LabSampleVial;
use App\Models\Test;
use Illuminate\Support\Collection;

class BarcodeLabelPrintService
{
    public function labelWidthDots(): int
    {
        return (int) config('hospital.label.dpi', 203) * (float) config('hospital.label.width_in', 2);
    }

    public function labelHeightDots(): int
    {
        return (int) config('hospital.label.dpi', 203) * (float) config('hospital.label.height_in', 1);
    }

    public function labelWidthMm(): float
    {
        return (float) config('hospital.label.width_mm', 50.8);
    }

    public function labelHeightMm(): float
    {
        return (float) config('hospital.label.height_mm', 25.4);
    }

    /** @param Collection<int, LabSampleVial> $vials */
    public function buildZpl(LaboratoryPatient $patient, Collection $vials): string
    {
        return $vials->map(fn (LabSampleVial $vial) => $this->singleZpl($patient, $vial))->implode("\n");
    }

    /** @param Collection<int, LabSampleVial> $vials */
    public function buildTspl(LaboratoryPatient $patient, Collection $vials): string
    {
        return $vials->map(fn (LabSampleVial $vial) => $this->singleTspl($patient, $vial))->implode("\n");
    }

    /**
     * @param Collection<int, LabSampleVial> $vials
     * @return array<int, array{vial_id: int, patient_name: string, lab_no: string, barcode: string, human_barcode: string, vial_type: string, test_names: string, vial_volume: string, footer: string}>
     */
    public function buildLabelRows(LaboratoryPatient $patient, Collection $vials): array
    {
        return $vials
            ->map(fn (LabSampleVial $vial) => $this->labelRow($patient, $vial))
            ->values()
            ->all();
    }

    /** @return array{vial_id: int, patient_name: string, lab_no: string, barcode: string, human_barcode: string, vial_type: string, test_names: string, vial_volume: string, footer: string} */
    public function labelRow(LaboratoryPatient $patient, LabSampleVial $vial): array
    {
        $barcode = trim((string) $vial->barcode);
        $vialType = $this->vialTypeLine($vial);
        $testNames = $this->vialTestNamesLine($vial);
        $vialVolume = $this->vialVolumeLine($vial);

        return [
            'vial_id' => (int) $vial->id,
            'patient_name' => $this->patientDisplayName($patient),
            'lab_no' => $this->labRegistrationNo($patient),
            'barcode' => $barcode,
            'human_barcode' => $this->formatBarcodeHuman($barcode),
            'vial_type' => $vialType,
            'test_names' => $testNames,
            'vial_volume' => $vialVolume,
            'footer' => $this->composeFooterLine($vialType, $testNames, $vialVolume),
        ];
    }

    public function patientDisplayName(LaboratoryPatient $patient): string
    {
        return strtoupper(trim((string) $patient->patient_name));
    }

    public function labRegistrationNo(LaboratoryPatient $patient): string
    {
        $labNo = trim((string) ($patient->lab_registration_no ?? $patient->mr_no ?? ''));

        return $labNo !== '' ? $labNo : 'N/A';
    }

    public function generateBarcode(int $labPatientId, int $vialNumber = 1): string
    {
        $prefix = date('dmy');

        do {
            $suffix = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            $barcode = $prefix . $suffix;
        } while (LabSampleVial::where('barcode', $barcode)->exists());

        return $barcode;
    }

    public function formatBarcodeHuman(string $barcode): string
    {
        $barcode = preg_replace('/\s+/', '', strtoupper(trim($barcode))) ?? '';

        if ($barcode === '') {
            return '';
        }

        return '* ' . implode(' ', str_split($barcode)) . ' *';
    }

    public function vialFooterLine(LabSampleVial $vial): string
    {
        return $this->composeFooterLine(
            $this->vialTypeLine($vial),
            $this->vialTestNamesLine($vial),
            $this->vialVolumeLine($vial)
        );
    }

    public function vialTypeLine(LabSampleVial $vial): string
    {
        $vialType = trim((string) $vial->vial_type);

        return $vialType !== '' ? $vialType : 'General';
    }

    public function vialTestNamesLine(LabSampleVial $vial): string
    {
        return Test::whereIn('id', $vial->test_ids ?? [])
            ->pluck('name')
            ->filter()
            ->values()
            ->implode(', ');
    }

    public function vialVolumeLine(LabSampleVial $vial): string
    {
        return Test::whereIn('id', $vial->test_ids ?? [])
            ->pluck('vial_volume')
            ->map(fn ($volume) => trim((string) $volume))
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');
    }

    public function composeFooterLine(string $vialType, string $testNames, string $vialVolume): string
    {
        $parts = array_filter([$vialType, $testNames, $vialVolume], fn ($part) => trim($part) !== '');

        return implode(' · ', $parts);
    }

    private function singleZpl(LaboratoryPatient $patient, LabSampleVial $vial): string
    {
        $row = $this->labelRow($patient, $vial);
        $pw = $this->labelWidthDots();
        $ll = $this->labelHeightDots();
        $contentW = $pw - 16;

        $name = $this->sanitize($this->truncate($row['patient_name'], 18));
        $labNo = $this->sanitize($this->truncate($row['lab_no'], 10));
        $barcode = $this->sanitize($row['barcode']);
        $humanCode = $this->sanitize($this->truncate($row['human_barcode'], 42));
        $vialType = $this->sanitize($this->truncate($row['vial_type'], 24));
        $testNames = $this->sanitize($this->truncate($row['test_names'], 36));
        $vialVolume = $this->sanitize($this->truncate($row['vial_volume'], 16));

        $barY = 30;
        $barH = (int) min(52, max(38, $ll * 0.28));
        $humanY = $barY + $barH + 6;
        $metaY = $humanY + 22;
        $testsY = $metaY + 16;

        return <<<ZPL
^XA
^PW{$pw}
^LL{$ll}
^LH0,0
^CI28
^FO8,6^FB{$contentW},1,0,L,0^A0N,22,20^FD{$name}^FS
^FO8,6^FB{$contentW},1,0,R,0^A0N,22,20^FD{$labNo}^FS
^FO20,{$barY}^BY1,2,{$barH}^BCN,{$barH},N,N,N^FD{$barcode}^FS
^FO8,{$humanY}^FB{$contentW},1,0,C,0^A0N,18,16^FD{$humanCode}^FS
^FO8,{$metaY}^FB{$contentW},1,0,L,0^A0N,14,12^FD{$vialType}^FS
^FO8,{$metaY}^FB{$contentW},1,0,R,0^A0N,14,12^FD{$vialVolume}^FS
^FO8,{$testsY}^FB{$contentW},1,0,C,0^A0N,14,12^FD{$testNames}^FS
^XZ
ZPL;
    }

    private function singleTspl(LaboratoryPatient $patient, LabSampleVial $vial): string
    {
        $row = $this->labelRow($patient, $vial);
        $w = $this->labelWidthMm();
        $h = $this->labelHeightMm();
        $dotsPerMm = (int) config('hospital.label.dpi', 203) / 25.4;

        $name = $this->sanitize($this->truncate($row['patient_name'], 18));
        $labNo = $this->sanitize($this->truncate($row['lab_no'], 10));
        $barcode = $this->sanitize($row['barcode']);
        $humanCode = $this->sanitize($this->truncate($row['human_barcode'], 42));
        $vialType = $this->sanitize($this->truncate($row['vial_type'], 24));
        $testNames = $this->sanitize($this->truncate($row['test_names'], 36));
        $vialVolume = $this->sanitize($this->truncate($row['vial_volume'], 16));

        $marginX = 8;
        $topY = 6;
        $barX = 20;
        $barY = (int) round(4.2 * $dotsPerMm);
        $barHeight = (int) min(56, max(42, $h * $dotsPerMm * 0.28));
        $humanY = $barY + $barHeight + 6;
        $metaY = $humanY + 24;
        $testsY = $metaY + 18;
        $labNoX = max($marginX, (int) round($w * $dotsPerMm) - 120);
        $volumeX = max($marginX, (int) round($w * $dotsPerMm) - 90);

        return <<<TSPL
SIZE {$w} mm, {$h} mm
GAP 2 mm, 0 mm
DIRECTION 1
REFERENCE 0,0
OFFSET 0 mm
SET PEEL OFF
SET CUTTER OFF
SET PARTIAL_CUTTER OFF
CLS
TEXT {$marginX},{$topY},"2",0,1,1,"{$name}"
TEXT {$labNoX},{$topY},"2",0,1,1,"{$labNo}"
BARCODE {$barX},{$barY},"128",{$barHeight},0,0,2,4,"{$barcode}"
TEXT {$marginX},{$humanY},"2",0,1,1,"{$humanCode}"
TEXT {$marginX},{$metaY},"1",0,1,1,"{$vialType}"
TEXT {$volumeX},{$metaY},"1",0,1,1,"{$vialVolume}"
TEXT {$marginX},{$testsY},"1",0,1,1,"{$testNames}"
PRINT 1,1

TSPL;
    }

    private function sanitize(string $value): string
    {
        $value = preg_replace('/[\x00-\x1F\x7F]/', ' ', $value) ?? $value;

        return str_replace(['"', '\\', '^', '~'], '', trim($value));
    }

    private function truncate(string $value, int $max): string
    {
        $value = trim($value);

        return mb_strlen($value) > $max ? mb_substr($value, 0, $max - 1) . '…' : $value;
    }
}
