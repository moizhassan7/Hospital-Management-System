{{--
    Patient identity band — read-only identity strip shown at the head of
    clinical workspaces when the patient is known server-side.
    Extra cells can be appended through the default slot using
    <div class="band-cell">…</div>.
--}}
@props(['mr' => null, 'name' => null, 'age' => null, 'gender' => null, 'type' => null])

<div {{ $attributes->class(['patient-band']) }} role="group" aria-label="Patient identification">
    <div class="band-cell">
        <span class="band-label">MR No.</span>
        <span @class(['band-value band-mr', 'is-empty' => blank($mr)])>{{ filled($mr) ? $mr : '—' }}</span>
    </div>
    <div class="band-cell band-cell-grow">
        <span class="band-label">Patient</span>
        <span @class(['band-value', 'is-empty' => blank($name)])>{{ filled($name) ? $name : '—' }}</span>
    </div>
    @if (filled($age) || filled($gender))
        <div class="band-cell">
            <span class="band-label">Age / Sex</span>
            <span class="band-value">{{ filled($age) ? $age : '—' }} / {{ filled($gender) ? $gender : '—' }}</span>
        </div>
    @endif
    @if (filled($type))
        <div class="band-cell">
            <span class="band-label">Type</span>
            <span class="band-value">{{ $type }}</span>
        </div>
    @endif
    {{ $slot }}
</div>
