<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Report - {{ $labPatient->patient_name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12.5px;
            color: #000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
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
            font-size: 12.5px;
        }
    </style>
</head>
<body>
    @php
        $reportService = app(\App\Services\PathologyReportService::class);
        $hasRemarksPage = $hasRemarksPage ?? $reportService->hasRemarksPage($test, $historyResults, $testComment ?? null);
        $hasTroponinInterpretation = $hasTroponinInterpretation ?? $reportService->hasTroponinHsInterpretationPage($test);
        $showRemarks = $hasRemarksPage && ($test->report_format === 'Quantitative' || ! $test->report_format);
        $totalPages = 1 + ($showRemarks ? 1 : 0);
        $pageOneLabel = $totalPages > 1 ? 'Page 1 of ' . $totalPages : 'Page 1';
        $hasTrailingPages = $showRemarks;
    @endphp

    <div class="report-page-main {{ $hasTrailingPages ? 'has-remarks-page' : '' }}">
        @include('partials.pathology-report-header', [
            'labPatient' => $labPatient,
            'test' => $test,
            'qrCodeDataUri' => $qrCodeDataUri ?? null,
            'reportEnteredBy' => $reportEnteredBy ?? null,
            'pdf' => true,
        ])

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

        @if($hasTroponinInterpretation)
            @include('partials.pathology-troponin-hs-interpretation')
        @endif

        @include('partials.lab-report-doctors-footer', ['pdf' => true])

        <div class="inline-page-footer">
            <div>{{ $pageOneLabel }}</div>
        </div>
    </div>

    @if($showRemarks)
        @include('partials.pathology-results-remarks-page', [
            'testComment' => $testComment ?? null,
            'labPatient' => $labPatient,
            'test' => $test,
            'historyResults' => $historyResults,
            'hasTrailingPage' => $hasTroponinInterpretation,
        ])
        <div class="inline-page-footer">
            <div>Page 2 of {{ $totalPages }}</div>
        </div>
    @endif

</body>
</html>
