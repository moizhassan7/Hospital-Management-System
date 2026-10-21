@props(['name' => 'search', 'value' => null, 'placeholder' => 'Search', 'label' => null])

<div class="input-group has-start {{ $attributes->get('class') }}">
    <span class="input-affix is-start"><x-ui.icon name="search" /></span>
    <input type="search" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" aria-label="{{ $label ?? $placeholder }}" {{ $attributes->except('class')->merge(['class' => 'input']) }}>
</div>
