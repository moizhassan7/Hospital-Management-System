@props([
    'title',
    'description' => '',
    'href' => null,
    'buttonText' => null,
    'iconColor' => 'text-blue-600',
    'buttonClass' => 'bg-blue-500 hover:bg-blue-600',
    'linkable' => false,
])

@php
    $tag = ($linkable && $href) ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($tag === 'a') href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'hms-action-row' . ($linkable ? ' is-link' : '')]) }}
>
    <div class="hms-action-icon {{ $iconColor }}">
        {{ $icon }}
    </div>
    <div class="hms-action-body">
        <h3 class="hms-action-title">{{ $title }}</h3>
        @if($description)
            <p class="hms-action-desc">{{ $description }}</p>
        @endif
    </div>
    @if($href && $buttonText && !$linkable)
        <a href="{{ $href }}" class="hms-action-btn {{ $buttonClass }}">{{ $buttonText }}</a>
    @elseif($linkable)
        <svg class="hms-action-chevron w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    @endif
</{{ $tag }}>
