@php
    /** @var \App\Models\TestParticular $particular */
    $showPatientType = $showPatientType ?? true;
    $showCritical = $showCritical ?? true;
    $showInterpretation = $showInterpretation ?? true;
@endphp

<div class="particular-reference-display text-sm text-gray-600 whitespace-pre-line">
    @if($showPatientType && $particular->patient_type)
        <div class="text-xs font-medium text-indigo-700 mb-1">Patient type: {{ $particular->patient_type }}</div>
    @endif

    @if($particular->hasNumericRange())
        <div>{{ $particular->formattedNumericRange() }}</div>
    @endif

    @if($particular->referenceRangeText())
        <div class="{{ $particular->hasNumericRange() ? 'mt-1 text-gray-500 italic' : '' }}">{{ $particular->referenceRangeText() }}</div>
    @endif

    @if(! $particular->hasNumericRange() && ! $particular->referenceRangeText())
        <div>—</div>
    @endif

    @if($showCritical && $particular->hasCriticalRange())
        <div class="mt-1 text-xs text-amber-700">Critical: {{ $particular->formattedCriticalRange() }}</div>
    @endif

    @if($showInterpretation && $particular->interpretationLabel())
        <div class="mt-1 text-xs text-slate-700">Interpretation: {{ $particular->interpretationLabel() }}</div>
    @endif
</div>
