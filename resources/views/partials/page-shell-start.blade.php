{{-- Opens standardized page structure. Pair with page-shell-end. --}}
@php
    $title = $title ?? null;
    $subtitle = $subtitle ?? null;
    $backUrl = $backUrl ?? null;
    $backLabel = $backLabel ?? 'Back';
@endphp

@if($title)
    @include('partials.page-toolbar', compact('title', 'subtitle', 'backUrl', 'backLabel'))
@endif

@include('partials.flash-alerts')
