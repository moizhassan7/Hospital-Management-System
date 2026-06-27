@php
    use App\Services\PathologyFormulaService;
    $formulaService = app(PathologyFormulaService::class);
    $isPdf = $pdf ?? false;
    $tableClass = 'pathology-report-table' . ($isPdf ? ' results' : '');
@endphp

@if($test->report_format === 'Quantitative' || !$test->report_format)
    <table class="{{ $tableClass }}">
        <thead>
            <tr>
                <th class="col-sno">#</th>
                <th class="col-test">Investigation</th>
                @foreach($historyResults as $results)
                    <th class="col-result">
                        Result
                        @if($historyResults->count() > 1)
                            <span class="col-date">{{ $results->first()->created_at->format('d-M-Y') }}</span>
                        @endif
                    </th>
                @endforeach
                <th class="col-unit">Unit</th>
                <th class="col-ref">Reference Range</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNum = 0; @endphp
            @foreach($test->testParticulars as $particular)
                @php
                    $hasSavedResult = false;
                    $latestVal = '—';
                    foreach ($historyResults as $results) {
                        $saved = $results->where('test_particular_id', $particular->id)->first();
                        if ($saved && $saved->result_value !== '' && $saved->result_value !== null) {
                            $hasSavedResult = true;
                            $latestVal = $saved->result_value;
                        }
                    }
                    if ($particular->is_calculated && ! $hasSavedResult) {
                        continue;
                    }

                    $rowNum++;

                    $refText = '—';
                    if ($particular->normal_range_min !== null || $particular->normal_range_max !== null) {
                        $refText = ($particular->normal_range_min ?? '—') . ' – ' . ($particular->normal_range_max ?? '—');
                    } elseif ($particular->reference_text) {
                        $refText = $particular->reference_text;
                    }
                @endphp
                <tr>
                    <td class="col-sno">{{ $rowNum }}</td>
                    <td class="col-test"><strong>{{ $particular->name }}</strong></td>
                    @foreach($historyResults as $results)
                        @php
                            $result = $results->where('test_particular_id', $particular->id)->first();
                            $val = $result ? $result->result_value : '—';
                            $flag = null;
                            if ($result && is_numeric($val)) {
                                $flag = $formulaService->isAbnormal(
                                    (float) $val,
                                    $particular->normal_range_min !== null ? (float) $particular->normal_range_min : null,
                                    $particular->normal_range_max !== null ? (float) $particular->normal_range_max : null
                                );
                            }
                        @endphp
                        <td class="col-result {{ $flag === 'high' || $flag === 'low' ? 'abnormal' : '' }}">
                            @if($flag === 'high')
                                <span class="flag-icon flag-high">▲</span>
                            @elseif($flag === 'low')
                                <span class="flag-icon flag-low">▼</span>
                            @elseif($flag === 'normal' && is_numeric($val))
                                <span class="flag-icon flag-normal">■</span>
                            @endif
                            <span class="result-value">{{ $val }}</span>
                        </td>
                    @endforeach
                    <td class="col-unit">{{ $particular->unit ?: '—' }}</td>
                    <td class="col-ref">{{ $refText }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
