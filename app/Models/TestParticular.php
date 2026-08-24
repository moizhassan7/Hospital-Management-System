<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestParticular extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'test_id',
        'name',
        'patient_type',
        'result_key',
        'unit',
        'normal_range_min',
        'normal_range_max',
        'critical_range_min',
        'critical_range_max',
        'reference_text',
        'reference_range_text',
        'interpretation_name',
        'remarks',
        'formula',
        'is_calculated',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_calculated' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function hasNumericRange(): bool
    {
        return $this->filledRangeValue($this->normal_range_min)
            || $this->filledRangeValue($this->normal_range_max);
    }

    public function hasCriticalRange(): bool
    {
        return $this->filledRangeValue($this->critical_range_min)
            || $this->filledRangeValue($this->critical_range_max);
    }

    public function formattedNumericRange(): ?string
    {
        if (! $this->hasNumericRange()) {
            return null;
        }

        return ($this->displayRangeValue($this->normal_range_min) ?? '—')
            .' – '
            .($this->displayRangeValue($this->normal_range_max) ?? '—');
    }

    public function formattedCriticalRange(): ?string
    {
        if (! $this->hasCriticalRange()) {
            return null;
        }

        return ($this->displayRangeValue($this->critical_range_min) ?? '—')
            .' – '
            .($this->displayRangeValue($this->critical_range_max) ?? '—');
    }

    public function referenceRangeText(): ?string
    {
        $text = trim((string) ($this->reference_range_text ?: $this->reference_text ?: ''));

        return $text !== '' ? $text : null;
    }

    public function interpretationLabel(): ?string
    {
        $text = trim((string) ($this->interpretation_name ?: ''));

        return $text !== '' ? $text : null;
    }

    /** @return list<string> */
    public function referenceDisplayParts(): array
    {
        $parts = [];

        if ($numeric = $this->formattedNumericRange()) {
            $parts[] = $numeric;
        }

        if ($words = $this->referenceRangeText()) {
            $parts[] = $words;
        }

        return $parts;
    }

    public function referenceDisplay(): string
    {
        $parts = $this->referenceDisplayParts();

        return $parts !== [] ? implode("\n", $parts) : '—';
    }

    private function filledRangeValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        return trim((string) $value) !== '';
    }

    /** @return list<string>|null */
    public function getDropdownOptions(): ?array
    {
        return \App\Support\LabParameterOptions::getOptionsFor(
            $this->name,
            $this->test?->name ?? ''
        );
    }

    private function displayRangeValue(mixed $value): ?string
    {
        if (! $this->filledRangeValue($value)) {
            return null;
        }

        return trim((string) $value);
    }
}

