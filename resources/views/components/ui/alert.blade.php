{{-- tone: success | danger | warning | info --}}
@props(['tone' => 'info', 'title' => null, 'dismissible' => false, 'icon' => null])

@php
    $glyph = $icon ?? ['success' => 'check-circle', 'danger' => 'alert-circle', 'warning' => 'alert-triangle'][$tone] ?? 'info';
@endphp

<div {{ $attributes->class(['alert', 'alert-' . $tone]) }} role="{{ in_array($tone, ['danger', 'warning']) ? 'alert' : 'status' }}">
    <x-ui.icon :name="$glyph" class="alert-icon" />
    <div class="alert-body">
        @if ($title)
            <p class="alert-title">{{ $title }}</p>
        @endif
        <div class="alert-text">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" class="alert-close" data-dismiss="alert" aria-label="Dismiss message">
            <x-ui.icon name="x" />
        </button>
    @endif
</div>
