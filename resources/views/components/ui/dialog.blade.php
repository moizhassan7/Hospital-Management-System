{{--
    Dialog shell. The outer element keeps the given id and starts with the
    `hidden` class, so page scripts can keep opening/closing it by toggling
    that class. Focus trap, Escape and focus return are handled in ui.js.

    size: sm | md | lg | xl     tone: null | danger | warning (icon tint)
    Mark close buttons with data-dialog-close.
--}}
@props(['id', 'title', 'description' => null, 'size' => 'md', 'icon' => null, 'tone' => null, 'dismissible' => true, 'role' => 'dialog'])

<div id="{{ $id }}" {{ $attributes->class(['dialog-backdrop', 'hidden']) }} role="{{ $role }}" aria-modal="true" aria-labelledby="{{ $id }}-title" @if ($description) aria-describedby="{{ $id }}-description" @endif data-dialog>
    <div class="dialog dialog-{{ $size }}" tabindex="-1">
        <div class="dialog-header">
            @if ($icon)
                <span @class(['dialog-icon', 'dialog-icon-' . $tone => $tone])><x-ui.icon :name="$icon" /></span>
            @endif
            <div class="min-w-0 flex-1">
                <h2 id="{{ $id }}-title" class="dialog-title">{{ $title }}</h2>
                @if ($description)
                    <p id="{{ $id }}-description" class="dialog-description">{{ $description }}</p>
                @endif
            </div>
            @if ($dismissible)
                <button type="button" class="dialog-x" data-dialog-close aria-label="Close">
                    <x-ui.icon name="x" />
                </button>
            @endif
        </div>

        @if (trim($slot) !== '')
            <div class="dialog-body">{{ $slot }}</div>
        @endif

        @isset($footer)
            <div {{ $footer->attributes->class(['dialog-footer']) }}>{{ $footer }}</div>
        @endisset
    </div>
</div>
