@props(['title' => null, 'description' => null, 'icon' => null])

<section {{ $attributes->class(['form-section']) }}>
    @if ($title)
        <div class="form-section-head">
            <div class="min-w-0">
                <h3 class="form-section-title">
                    @if ($icon)<x-ui.icon :name="$icon" />@endif
                    <span>{{ $title }}</span>
                </h3>
                @if ($description)
                    <p class="form-section-desc">{{ $description }}</p>
                @endif
            </div>
            @isset($aside)
                <div class="flex flex-wrap items-center gap-2">{{ $aside }}</div>
            @endisset
        </div>
    @endif
    {{ $slot }}
</section>
