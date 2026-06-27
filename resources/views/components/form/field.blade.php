@props(['label' => null, 'for' => null, 'required' => false, 'hint' => null])

<div {{ $attributes->merge(['class' => 'hms-field']) }}>
    @if($label)
        <label @if($for) for="{{ $for }}" @endif class="hms-label">
            {{ $label }}@if($required)<span class="hms-required">*</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if($hint)
        <p class="hms-field-hint">{{ $hint }}</p>
    @endif
</div>
