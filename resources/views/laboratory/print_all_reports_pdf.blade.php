@php $layout = $layout ?? 'separate'; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Lab Reports - {{ $labPatient->patient_name }}</title>
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

        .descriptive-content { padding: 4px 0; }
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

        .report-bundle {
            page-break-after: always;
        }
        .report-bundle:last-child {
            page-break-after: auto;
        }

        /* Combined layout: one letterhead + one footer, all tests flow together */
        .combined-report .combined-test-section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .combined-report .pathology-report-section {
            margin-bottom: 8px;
        }

    </style>
</head>
<body>

    @if($layout === 'combined')
        <div class="combined-report">
            @include('partials.pathology-report-header', [
                'labPatient' => $labPatient,
                'test' => null,
                'qrCodeDataUri' => $reports[0]['qrCodeDataUri'] ?? null,
                'reportEnteredBy' => $reports[0]['reportEnteredBy'] ?? null,
                'collectedByLabel' => $reports[0]['collectedByLabel'] ?? '—',
                'receivedByLabel' => $reports[0]['receivedByLabel'] ?? '—',
                'pdf' => true,
            ])

            @foreach($reports as $report)
                @php
                    $test = $report['test'];
                    $labPatient = $report['labPatient'];
                    $historyResults = $report['historyResults'];
                    $testComment = $report['testComment'] ?? null;
                    $testImages = $report['testImages'] ?? collect();
                    $isQuantitative = $test->report_format === 'Quantitative' || ! $test->report_format;
                @endphp
                <div class="combined-test-section">
                    @if($isQuantitative)
                        @include('partials.pathology-results-table', ['testComment' => $testComment, 'pdf' => true])
                    @else
                        <div class="pathology-report-section">
                            <div class="section-title">{{ strtoupper($test->testHead->name ?? $test->name) }}</div>
                            <div class="descriptive-content">
                                @php $currentResults = $historyResults[$labPatient->id] ?? collect(); @endphp
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
                            </div>
                            @if($testImages->count() > 0)
                                <div class="descriptive-item">
                                    <b>Attached Images</b>
                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px;">
                                        @foreach($testImages as $img)
                                            <img src="{{ asset('storage/' . $img->image_path) }}" style="width: 100%; border: 1px solid #eee; border-radius: 4px;">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($report['hasTroponinInterpretation'] ?? false)
                        @include('partials.pathology-troponin-hs-interpretation')
                    @endif
                </div>
            @endforeach

            @include('partials.lab-report-doctors-footer', [
                'labReportDoctors' => $reports[0]['labReportDoctors'] ?? null,
                'pdf' => true,
            ])

            @php
                $referenceGroups = [];
                foreach ($reports as $refReport) {
                    $refTables = $refReport['referenceTables'] ?? [];
                    if (! empty($refTables)) {
                        $referenceGroups[] = [
                            'label' => $refReport['test']->name,
                            'tables' => $refTables,
                        ];
                    }
                }
            @endphp

            @if(! empty($referenceGroups))
                @include('partials.pathology-reference-tables-page', [
                    'referenceGroups' => $referenceGroups,
                    'labPatient' => $labPatient,
                    'mode' => 'inline',
                ])
            @endif
        </div>
    @else
        @foreach($reports as $report)
            @php $report['pdf'] = true; @endphp
            <div class="report-bundle">
                @include('partials.pathology-report-single', $report)
            </div>
        @endforeach
    @endif
</body>
</html>
