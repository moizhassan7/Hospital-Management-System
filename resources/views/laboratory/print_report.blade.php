<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Report - {{ $labPatient->patient_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
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

        .descriptive-content {
            padding: 4px 0;
        }
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

        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
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
    </style>
</head>
<body>
    @unless(!empty($isOnlineView))
    <a href="javascript:window.print()" class="btn-print no-print">Print Report</a>
    @endunless

    @php $hasRemarksPage = $hasRemarksPage ?? app(\App\Services\PathologyReportService::class)->hasRemarksPage($test, $historyResults, $testComment ?? null); @endphp

    <div class="report-page-main {{ $hasRemarksPage ? 'has-remarks-page' : '' }}">
        @include('partials.pathology-report-header', ['labPatient' => $labPatient, 'qrCodeDataUri' => $qrCodeDataUri ?? null])

    @if($test->report_format === 'Quantitative' || !$test->report_format)
        @include('partials.pathology-results-table', ['testComment' => $testComment ?? null])
    @else
        <div class="descriptive-content">
            @php
                $currentResults = $historyResults->where('laboratory_patient_id', $labPatient->id)->first();
                // If historyResults is grouped by patient_id, we just get the one for current patient
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
                        <div>{!! $val !!}</div>
                    </div>
                @endif
            @endforeach

            @if(isset($testImages) && $testImages->count() > 0)
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

        {{-- Show previous reports summary if available --}}
        @if($historyResults->count() > 1)
            <div style="margin-top: 30px; page-break-before: always;">
                <h4 style="color: #004a99; border-bottom: 1px solid #eee; padding-bottom: 5px;">PREVIOUS REPORTS HISTORY</h4>
                <table style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            @foreach($test->testParticulars->where('name', '!=', 'Findings') as $p)
                                <th>{{ $p->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historyResults as $pid => $results)
                            @if($pid != $labPatient->id)
                                <tr>
                                    <td>{{ $results->first()->created_at->format('d-M-Y') }}</td>
                                    @foreach($test->testParticulars->where('name', '!=', 'Findings') as $p)
                                        @php $res = $results->where('test_particular_id', $p->id)->first(); @endphp
                                        <td>{{ $res ? $res->result_value : '-' }}</td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    @include('partials.lab-report-entered-by')
    @include('partials.lab-report-doctors-footer')

    <div class="inline-page-footer">
        <div>{{ config('hospital.name') }}</div>
        <div>Report Generated: {{ date('d-M-Y H:i') }}</div>
        <div>Page 1{{ $hasRemarksPage ? ' of 2' : '' }}</div>
    </div>
    </div>{{-- end report-page-main --}}

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

    <script>
        // Auto print window
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
