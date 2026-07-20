@php $layout = $layout ?? 'separate'; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Lab Reports - {{ $labPatient->patient_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 0 15mm 15mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, 'Segoe UI', sans-serif;
            font-size: 11px;
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

        @media print {
            .no-print { display: none; }
            body { margin: 0; }

            /* In combined mode the footer prints once at the end, not pinned per page */
            .combined-report .report-doctors-footer {
                position: static !important;
                bottom: auto !important;
                left: auto !important;
                right: auto !important;
                width: auto !important;
            }
        }

        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #004a99;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .layout-toggle {
            position: fixed;
            top: 20px;
            right: 200px;
            background: #fff;
            color: #004a99;
            padding: 10px 18px;
            border: 2px solid #004a99;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        .print-all-summary {
            padding: 16px 20px;
            background: #f0f7ff;
            border-bottom: 2px solid #004a99;
            margin-bottom: 8px;
        }
        .print-all-summary h1 {
            margin: 0 0 4px;
            font-size: 16px;
            color: #004a99;
        }
        .print-all-summary p {
            margin: 0;
            font-size: 12px;
            color: #333;
        }
    </style>
</head>
<body>
    <a href="javascript:window.print()" class="btn-print no-print">Print All Reports ({{ count($reports) }})</a>

    @if($layout === 'combined')
        <a href="{{ request()->fullUrlWithQuery(['layout' => 'separate']) }}" class="layout-toggle no-print">Separate pages</a>
    @else
        <a href="{{ request()->fullUrlWithQuery(['layout' => 'combined']) }}" class="layout-toggle no-print">Combine on one page</a>
    @endif

    <div class="print-all-summary no-print">
        <h1>{{ $labPatient->patient_name }}</h1>
        <p>
            Lab Reg: {{ $labPatient->lab_registration_no ?? 'N/A' }} &mdash; {{ count($reports) }} report(s) with entered results
            &mdash; {{ $layout === 'combined' ? 'Combined on one page' : 'One test per page' }}
        </p>
    </div>

    @if($layout === 'combined')
        <div class="combined-report">
            @include('partials.pathology-report-header', [
                'labPatient' => $labPatient,
                'test' => null,
                'qrCodeDataUri' => null,
                'reportEnteredBy' => $reports[0]['reportEnteredBy'] ?? null,
                'collectedByLabel' => $reports[0]['collectedByLabel'] ?? '—',
                'receivedByLabel' => $reports[0]['receivedByLabel'] ?? '—',
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
                        @include('partials.pathology-results-table', ['testComment' => $testComment])
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
                                            <div>{!! $val !!}</div>
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
                ])
            @endif
        </div>
    @else
        @foreach($reports as $report)
            <div class="report-bundle">
                @include('partials.pathology-report-single', $report)
            </div>
        @endforeach
    @endif
</body>
</html>
