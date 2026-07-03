@php
    use App\Services\PathologyFormulaService;
    $formulaService = app(PathologyFormulaService::class);
    $isPdf = $pdf ?? false;

    $testHeadName = $test->testHead->name ?? null;
    $testName = $test->name;
    $showTestNameLine = $testHeadName && strcasecmp($testHeadName, $testName) !== 0;
    if (! $testHeadName) {
        $testHeadName = $testName;
    }
    $reportTestMeta = collect($labPatient->getSelectedTestsArray())->firstWhere('id', $test->id);
    $visitDate = $labPatient->created_at->format('d-M-Y h:iA');
    $reportDate = ! empty($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'] ?? null)
        ? \Carbon\Carbon::parse($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'])->format('d-M-Y h:iA')
        : now()->format('d-M-Y h:iA');
    $sampleDate = ! empty($reportTestMeta['sample_collected_at'])
        ? \Carbon\Carbon::parse($reportTestMeta['sample_collected_at'])->format('d-M-y')
        : $labPatient->created_at->format('d-M-y');
    $labRefNo = $labPatient->lab_registration_no ?? $labPatient->desktop_invoice ?? 'N/A';

    $currentResults = $historyResults[$labPatient->id] ?? $historyResults->last() ?? collect();
    $inlineComment = trim((string) ($testComment ?? ''));
@endphp

<div class="pathology-report-section">
    <table class="pathology-report-meta">
        <tr>
            <td class="meta-left">Visit Date: {{ $visitDate }}</td>
            <td class="meta-center">Final Report</td>
            <td class="meta-right">Report Date: {{ $reportDate }}</td>
        </tr>
    </table>

    <table class="pathology-report-table">
        <thead>
            <tr>
                <th class="col-test">Test Name</th>
                <th class="col-result">Results</th>
                <th class="col-ref">Reference Ranges</th>
            </tr>
        </thead>
    </table>

    <table class="test-head-bar">
        <tr>
            <td class="bar-name">{{ $testHeadName }}</td>
            <td class="bar-meta">
                {{ $sampleDate }}<br>{{ $labRefNo }}
            </td>
        </tr>
    </table>

    @if($showTestNameLine)
        <div class="test-name-line">{{ $testName }}</div>
    @endif

    <table class="pathology-report-table">
        <tbody>
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

                    $refText = '—';
                    if ($particular->normal_range_min !== null || $particular->normal_range_max !== null) {
                        $refText = ($particular->normal_range_min ?? '—') . ' - ' . ($particular->normal_range_max ?? '—');
                    } elseif ($particular->reference_text) {
                        $refText = $particular->reference_text;
                    }
                @endphp
                <tr>
                    <td class="col-test">{{ $particular->name }}</td>
                    <td class="col-result {{ $flag === 'high' || $flag === 'low' ? 'abnormal' : '' }}">
                        @if($flag === 'high')
                            <span class="flag-icon flag-high">▲</span>
                        @elseif($flag === 'low')
                            <span class="flag-icon flag-low">▼</span>
                        @endif
                        <span class="result-value">{{ $val }}</span>
                    </td>
                    <td class="col-ref">
                        <table class="ref-inner">
                            <tr>
                                <td class="ref-text">{!! nl2br(e($refText)) !!}</td>
                                @if($particular->unit)
                                    <td class="ref-unit">{{ $particular->unit }}</td>
                                @endif
                            </tr>
                        </table>
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
