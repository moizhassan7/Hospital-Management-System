<?php

namespace App\Services;

use App\Models\LaboratoryPatient;
use App\Models\LabSampleVial;
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

    private function singleZpl(LaboratoryPatient $patient, LabSampleVial $vial): string
    {
        $pw = $this->labelWidthDots();
        $ll = $this->labelHeightDots();

        $name = $this->sanitize($this->truncate($patient->patient_name, 24));
        $meta = $this->sanitize($this->truncate(
            ($patient->mr_no ?? 'N/A') . ' ' . $patient->age . '/' . substr((string) $patient->gender, 0, 1),
            16
        ));
        $barcode = $this->sanitize($vial->barcode);
        $footer = $this->sanitize($this->truncate(
            'Exp ' . ($vial->expires_at?->format('d/m/y H:i') ?? 'N/A'),
            28
        ));

        return <<<ZPL
^XA
^PW{$pw}
^LL{$ll}
^LH0,0
^CI28
^FO6,4^A0N,18,16^FD{$name}^FS
^FO{$this->rightColumnX($pw, 120)},4^A0N,15,13^FD{$meta}^FS
^FO24,24^BY2,2,62^BCN,62,N,N,N^FD{$barcode}^FS
^FO6,98^A0N,16,14^FD{$barcode}^FS
^FO{$this->rightColumnX($pw, 160)},98^A0N,14,12^FD{$footer}^FS
^XZ
ZPL;
    }

    private function singleTspl(LaboratoryPatient $patient, LabSampleVial $vial): string
    {
        $w = config('hospital.label.width_mm', 50.8);
        $h = config('hospital.label.height_mm', 25.4);

        $name = $this->sanitize($this->truncate($patient->patient_name, 24));
        $meta = $this->sanitize($this->truncate(
            ($patient->mr_no ?? 'N/A') . ' ' . $patient->age . '/' . substr((string) $patient->gender, 0, 1),
            16
        ));
        $barcode = $this->sanitize($vial->barcode);
        $footer = $this->sanitize($this->truncate(
            'Exp ' . ($vial->expires_at?->format('d/m/y H:i') ?? 'N/A'),
            28
        ));

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
TEXT 6,4,"2",0,1,1,"{$name}"
TEXT 190,4,"1",0,1,1,"{$meta}"
BARCODE 20,22,"128",56,1,0,2,4,"{$barcode}"
TEXT 6,92,"2",0,1,1,"{$barcode}"
TEXT 150,92,"1",0,1,1,"{$footer}"
PRINT 1,1

TSPL;
    }

    private function rightColumnX(int $pageWidth, int $fieldWidth): int
    {
        return max(6, $pageWidth - $fieldWidth - 6);
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
