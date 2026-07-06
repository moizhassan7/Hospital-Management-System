@php
    use App\Services\PathologyFormulaService;
    $formulaService = app(PathologyFormulaService::class);

    $testHeadName = $test->testHead->name ?? null;
    $testName = $test->name;
    $showSubSection = $testHeadName && strcasecmp($testHeadName, $testName) !== 0;
    if (! $testHeadName) {
        $testHeadName = $testName;
    }

    $reportTestMeta = collect($labPatient->getSelectedTestsArray())->firstWhere('id', $test->id);
    $resultDateTime = ! empty($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'] ?? null)
        ? \Carbon\Carbon::parse($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'])->format('d-m-y H:i')
        : now()->format('d-m-y H:i');

    $currentResults = $historyResults[$labPatient->id] ?? $historyResults->last() ?? collect();
    $inlineComment = trim((string) ($testComment ?? ''));
@endphp

<div class="pathology-report-section">
    <div class="section-title">{{ strtoupper($testHeadName) }}</div>

    <table class="pathology-report-table">
        <thead>
            <tr>
                <th class="col-test">Test</th>
                <th class="col-normal">Normal Value</th>
                <th class="col-unit">Unit</th>
                <th class="col-result">
                    Result
                    <span class="result-datetime">{{ $resultDateTime }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            @if($showSubSection)
                <tr class="sub-section-row">
                    <td colspan="4">{{ $testName }}</td>
                </tr>
            @endif

            @foreach($test->testParticulars as $particular)
                @php
                    $hasSavedResult = false;
                    foreach ($historyResults as $results) {
                        $saved = $results->where('test_particular_id', $particular->id)->first();
                        if ($saved && $saved->result_value !== '' && $saved->result_value !== null) {
                            $hasSavedResult = true;
                        }
                    }
                    if ($particular->is_calculated && ! $hasSavedResult) {
                        continue;
                    }

                    if (! $hasSavedResult) {
                        continue;
                    }

                    $result = $currentResults->where('test_particular_id', $particular->id)->first();
                    $val = $result ? $result->result_value : '—';
                    $flag = null;
                    if ($result && is_numeric($val)) {
                        $flag = $formulaService->isAbnormal(
                            (float) $val,
                            $particular->normal_range_min !== null ? (float) $particular->normal_range_min : null,
                            $particular->normal_range_max !== null ? (float) $particular->normal_range_max : null
                        );
                    }

                    $normalValue = '—';
                    if ($particular->normal_range_min !== null || $particular->normal_range_max !== null) {
                        $normalValue = ($particular->normal_range_min ?? '—') . ' - ' . ($particular->normal_range_max ?? '—');
                    } elseif ($particular->reference_text) {
                        $lines = preg_split('/\r\n|\r|\n/', trim($particular->reference_text));
                        $normalValue = $lines[0] ?? '—';
                    }
                @endphp
                <tr>
                    <td class="col-test test-name-cell">{{ $particular->name }}</td>
                    <td class="col-normal">{{ $normalValue }}</td>
                    <td class="col-unit">{{ $particular->unit ?: '—' }}</td>
                    <td class="col-result {{ $flag === 'high' || $flag === 'low' ? 'abnormal' : '' }}">
                        <div class="result-cell">
                            <span class="flag-slot" aria-hidden="true">
                                @if($flag === 'high')
                                    <span class="flag-icon flag-high">▲</span>
                                @elseif($flag === 'low')
                                    <span class="flag-icon flag-low">▼</span>
                                @endif
                            </span>
                            <span class="result-value">{{ $val }}</span>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($inlineComment !== '')
        <div class="report-comments">
            <strong>Comments:</strong>
            <div class="report-comments-body">{!! nl2br(e($inlineComment)) !!}</div>
        </div>
    @endif
</div>
