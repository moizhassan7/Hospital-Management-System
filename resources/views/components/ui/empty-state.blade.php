@props(['icon' => 'inbox', 'title', 'description' => null])

<div {{ $attributes->class(['empty-state']) }}>
    <div class="empty-state-icon"><x-ui.icon :name="$icon" /></div>
    <p class="empty-state-title">{{ $title }}</p>
    @if ($description)
        <p class="empty-state-desc">{{ $description }}</p>
    @endif
    @isset($actions)
        <div class="empty-state-actions">{{ $actions }}</div>
    @endisset
</div>
