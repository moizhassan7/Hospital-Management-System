@props([
    'title',
    'subtitle' => null,
    'backUrl' => null,
    'backLabel' => 'Back',
])

<div class="hms-page-toolbar">
    <div>
        <h2 class="hms-page-heading">{{ $title }}</h2>
        @if($subtitle)
            <p class="hms-page-subheading">{{ $subtitle }}</p>
        @endif
    </div>
    @if($backUrl)
        <a href="{{ $backUrl }}" class="hms-back-btn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $backLabel }}
        </a>
    @endif
</div>
