{{-- tone: neutral | brand | success | warning | danger | info | ink --}}
@props(['tone' => 'neutral', 'dot' => false])

<span {{ $attributes->class(['badge', 'badge-' . $tone]) }}>
    @if ($dot)<span class="badge-dot" aria-hidden="true"></span>@endif
    {{ $slot }}
</span>
