@props(['title' => null, 'description' => null, 'icon' => null, 'flush' => false, 'bodyClass' => null, 'headingLevel' => 'h2'])

<section {{ $attributes->class(['panel']) }}>
    @if ($title || isset($actions))
        <header class="panel-header">
            <div class="min-w-0">
                @if ($title)
                    <{{ $headingLevel }} class="panel-title">
                        @if ($icon)<x-ui.icon :name="$icon" />@endif
                        <span>{{ $title }}</span>
                    </{{ $headingLevel }}>
                @endif
                @if ($description)
                    <p class="panel-description">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="panel-actions">{{ $actions }}</div>
            @endisset
        </header>
    @endif

    @if ($flush)
        {{ $slot }}
    @else
        <div @class(['panel-body', $bodyClass])>{{ $slot }}</div>
    @endif

    @isset($footer)
        <footer {{ $footer->attributes->class(['panel-footer']) }}>{{ $footer }}</footer>
    @endisset
</section>
