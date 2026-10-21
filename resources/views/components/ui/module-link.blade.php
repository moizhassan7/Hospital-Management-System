@props(['href', 'icon' => 'arrow-right', 'title', 'description' => null, 'primary' => false])

<a href="{{ $href }}" {{ $attributes->class(['module-link', 'is-primary' => $primary]) }}>
    <span class="module-link-icon"><x-ui.icon :name="$icon" /></span>
    <span class="module-link-body">
        <span class="module-link-title">{{ $title }}</span>
        @if ($description)
            <span class="module-link-desc">{{ $description }}</span>
        @endif
    </span>
    <x-ui.icon name="chevron-right" class="module-link-arrow" />
</a>
