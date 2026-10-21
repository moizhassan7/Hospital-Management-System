@props(['title', 'subtitle' => null])

<header {{ $attributes->class(['page-header']) }}>
    <div class="page-header-main">
        @isset($meta)
            <div class="page-meta">{{ $meta }}</div>
        @endisset
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div {{ $actions->attributes->class(['page-actions']) }}>{{ $actions }}</div>
    @endisset
</header>
