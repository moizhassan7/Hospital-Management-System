@props(['label', 'value', 'icon' => null, 'hint' => null, 'prefix' => null, 'meter' => null])

<div {{ $attributes->class(['metric']) }}>
    <p class="metric-label">
        @if ($icon)<x-ui.icon :name="$icon" />@endif
        <span>{{ $label }}</span>
    </p>
    <p class="metric-value">@if ($prefix)<small>{{ $prefix }}</small>@endif{{ $value }}</p>
    @if ($meter !== null)
        <div class="meter" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $meter }}" aria-label="{{ $label }}">
            <span style="width: {{ max(0, min(100, $meter)) }}%"></span>
        </div>
    @endif
    @if ($hint)
        <p class="metric-hint">{{ $hint }}</p>
    @endif
    {{ $slot }}
</div>
