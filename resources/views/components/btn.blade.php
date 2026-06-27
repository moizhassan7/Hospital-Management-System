@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
    'size' => 'md',
])

@php
    $classes = 'hms-btn hms-btn-' . $variant;
    if ($size === 'sm') $classes .= ' hms-btn-sm';
    if ($size === 'lg') $classes .= ' hms-btn-lg';
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($tag === 'a') href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>{{ $slot }}</{{ $tag }}>
