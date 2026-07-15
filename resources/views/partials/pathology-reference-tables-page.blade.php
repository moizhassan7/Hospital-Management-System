@php
    /**
     * Renders reference / normal-value tables for a report.
     *
     * Expected variables:
     *   $referenceGroups : list<array{label: ?string, tables: list<array{title,columns,rows}>}>
     *   $labPatient      : LaboratoryPatient
     *   $mode            : 'page' (own trailing page) | 'inline' (flows after results)
     *   $hasTrailingPage : bool (optional, page mode only) — force a page break after
     */
    $mode = $mode ?? 'page';
    $referenceGroups = collect($referenceGroups ?? [])
        ->filter(fn ($group) => ! empty($group['tables']))
        ->values();
    $showGroupLabels = $referenceGroups->count() > 1;
    $wrapperClass = $mode === 'inline'
        ? 'report-reference-inline'
        : ('report-reference-page' . (!empty($hasTrailingPage) ? ' has-trailing-page' : ''));
@endphp

@if($referenceGroups->isNotEmpty())
    <div class="{{ $wrapperClass }}">
        <div class="reference-page-title">Reference Ranges</div>

        @foreach($referenceGroups as $group)
            @if($showGroupLabels && !empty($group['label']))
                <div class="reference-group-title">{{ $group['label'] }}</div>
            @endif

            @foreach($group['tables'] as $table)
                <div class="report-reference-table-block">
                    @if(!empty($table['title']))
                        <div class="reference-table-title">{{ $table['title'] }}</div>
                    @endif
                    <table class="reference-values-table">
                        <thead>
                            <tr>
                                @foreach($table['columns'] as $column)
                                    <th>{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($table['rows'] as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td>{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endforeach
    </div>
@endif
