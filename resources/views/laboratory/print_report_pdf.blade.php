<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Report - {{ $labPatient->patient_name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.25;
        }
        @include('partials.pathology-report-styles')
        .descriptive-item {
            margin-bottom: 8px;
            padding: 4px 0;
            border-bottom: 1px dotted #b5b5b5;
        }
        .descriptive-item b {
            display: block;
            margin-bottom: 2px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    @php $hasRemarksPage = $hasRemarksPage ?? app(\App\Services\PathologyReportService::class)->hasRemarksPage($test, $historyResults, $testComment ?? null); @endphp

    <div class="report-page-main {{ $hasRemarksPage ? 'has-remarks-page' : '' }}">
        @include('partials.pathology-report-header', ['labPatient' => $labPatient, 'qrCodeDataUri' => $qrCodeDataUri ?? null, 'pdf' => true])

        @if($test->report_format === 'Quantitative' || !$test->report_format)
            @include('partials.pathology-results-table', ['testComment' => $testComment ?? null, 'pdf' => true])
        @else
            @php
                $currentResults = $historyResults[$labPatient->id] ?? collect();
            @endphp
            @foreach($test->testParticulars as $particular)
                @php
                    $result = $currentResults->where('test_particular_id', $particular->id)->first();
                    $val = $result ? $result->result_value : '';
                @endphp
                @if($val)
                    <div class="descriptive-item">
                        <b>{{ $particular->name }}</b>
                        <div>{!! strip_tags($val, '<p><br><b><i><ul><ol><li>') !!}</div>
                    </div>
                @endif
            @endforeach
        @endif

        @include('partials.lab-report-entered-by', ['pdf' => true])
        @include('partials.lab-report-doctors-footer', ['pdf' => true])

        <div class="inline-page-footer">
            <div>{{ config('hospital.name') }}</div>
            <div>Report Generated: {{ now()->format('d-M-Y H:i') }}</div>
            <div>Page 1{{ $hasRemarksPage ? ' of 2' : '' }}</div>
        </div>
    </div>

    @if($hasRemarksPage && ($test->report_format === 'Quantitative' || !$test->report_format))
        @include('partials.pathology-results-remarks-page', [
            'testComment' => $testComment ?? null,
            'labPatient' => $labPatient,
            'test' => $test,
            'historyResults' => $historyResults,
        ])
        <div class="inline-page-footer">
            <div>{{ config('hospital.name') }}</div>
            <div>Interpretation Notes</div>
            <div>Page 2 of 2</div>
        </div>
    @endif
</body>
</html>
