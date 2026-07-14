@php
    $hormoneReferenceType = $hormoneReferenceType ?? null;
    $rows = match ($hormoneReferenceType) {
        'fsh' => [
            ['Follicular Phase', '2.9 - 12.0'],
            ['Ovulation Peak', '5.8 - 21.0'],
            ['Luteal Phase', '1.5 - 7.0'],
            ['Menopausal', '17.0 - 95.0'],
            ['Male', '1.7 - 12.0'],
        ],
        'lh' => [
            ['Follicular Phase', '1.5 - 8.0'],
            ['Ovulation Peak', '9.6 - 80.0'],
            ['Luteal Phase', '0.2 - 6.5'],
            ['Post Menopause Female', '8.0 - 33.0'],
            ['Male', '1.0 - 7.0'],
        ],
        default => [],
    };
@endphp

@if(count($rows) > 0)
<div class="report-hormone-reference-ranges">
    <div class="hormone-reference-title">Normal Range</div>
    <table class="hormone-reference-table">
        <thead>
            <tr>
                <th>Phase</th>
                <th>Normal Range</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as [$phase, $range])
                <tr>
                    <td>{{ $phase }}</td>
                    <td>{{ $range }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
