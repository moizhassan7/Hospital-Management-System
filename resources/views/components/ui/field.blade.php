{{--
    Label + control + hint + validation message.
    `error` is the validation key to read from $errors (defaults to nothing).
    `tag` marks how the value gets there, e.g. "Auto" or "Calculated".
--}}
@props(['label' => null, 'for' => null, 'labelId' => null, 'hint' => null, 'error' => null, 'required' => false, 'tag' => null])

<div {{ $attributes->class(['field']) }}>
    @if ($label !== null)
        <label @if ($for) for="{{ $for }}" @endif @if ($labelId) id="{{ $labelId }}" @endif class="field-label">
            <span>{{ $label }}</span>
            @if ($required)<span class="field-required" aria-hidden="true">*</span>@endif
            @if ($tag)<span class="field-tag">{{ $tag }}</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif

    @if ($error)
        @error($error)
            <p class="field-error"><x-ui.icon name="alert-circle" class="mt-px size-3.5" />{{ $message }}</p>
        @enderror
    @endif
</div>
