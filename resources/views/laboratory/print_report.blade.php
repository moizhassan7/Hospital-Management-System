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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #004a99;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .logo-area h1 {
            color: #004a99;
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .logo-area p {
            margin: 0;
            font-size: 10px;
            color: #666;
        }
        .accreditation {
            text-align: right;
        }
        .accreditation img {
            height: 40px;
        }

        .patient-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-item b {
            color: #555;
            display: block;
            font-size: 10px;
            text-transform: uppercase;
        }
        .info-item span {
            font-size: 13px;
            font-weight: 600;
        }

        .report-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title h2 {
            margin: 0;
            color: #004a99;
            border-bottom: 1px solid #eee;
            display: inline-block;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: 600;
            text-align: left;
            padding: 8px;
            border-bottom: 2px solid #ddd;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        @include('partials.pathology-report-styles')

        .descriptive-content {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            background: #fff;
        }
        .descriptive-item {
            margin-bottom: 15px;
        }
        .descriptive-item b {
            display: block;
            color: #004a99;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 11px;
        }

        .footer {
            display: none;
        }

        .qr-placeholder {
            width: 60px;
            height: 60px;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #aaa;
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
            }
            .header {
                position: running(header);
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
    <div class="header">
            @include('partials.hospital-brand', ['variant' => 'print-html', 'subtitle' => 'Pathology Laboratory Report'])
        <div class="accreditation">
            @if(!empty($qrCodeDataUri))
                <img src="{{ $qrCodeDataUri }}" alt="Scan for online report" style="width: 90px; height: 90px;">
                <p style="font-size: 9px; text-align: center; margin-top: 4px; color: #666;">Scan for online report</p>
            @endif
        </div>
    </div>

    <div class="patient-info">
        <div class="info-item">
            <b>Patient Name</b>
            <span>{{ $labPatient->patient_name }}</span>
        </div>
        <div class="info-item">
            <b>Lab Reg No</b>
            <span>{{ $labPatient->lab_registration_no ?? 'N/A' }}</span>
        </div>
        <div class="info-item">
            <b>MR Number</b>
            <span>{{ $labPatient->mr_no }}</span>
        </div>
        <div class="info-item">
            <b>Age / Gender</b>
            <span>{{ $labPatient->age }} / {{ $labPatient->gender }}</span>
        </div>
        <div class="info-item">
            <b>Registration Date</b>
            <span>{{ $labPatient->created_at->format('d-M-Y H:i') }}</span>
        </div>
        <div class="info-item">
            <b>Referrer</b>
            <span>{{ $labPatient->refer_by_doctor_name ?? 'Self Referred' }}</span>
        </div>
        <div class="info-item">
            <b>Contact</b>
            <span>{{ $labPatient->contact_no }}</span>
        </div>
        @php
            $reportTestMeta = collect($labPatient->getSelectedTestsArray())->firstWhere('id', $test->id);
        @endphp
        @if($reportTestMeta)
        <div class="info-item">
            <b>Sample Collected</b>
            <span>{{ !empty($reportTestMeta['sample_collected_at']) ? \Carbon\Carbon::parse($reportTestMeta['sample_collected_at'])->format('d-M-Y H:i') : '—' }}</span>
        </div>
        <div class="info-item">
            <b>Received in Lab</b>
            <span>{{ !empty($reportTestMeta['sample_received_in_lab_at']) ? \Carbon\Carbon::parse($reportTestMeta['sample_received_in_lab_at'])->format('d-M-Y H:i') : '—' }}</span>
        </div>
        <div class="info-item">
            <b>Reported At</b>
            <span>{{ !empty($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'] ?? null) ? \Carbon\Carbon::parse($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'])->format('d-M-Y H:i') : '—' }}</span>
        </div>
        @endif
    </div>

    <div class="report-title">
        <h2>{{ $test->name }}</h2>
    </div>

    @if($test->report_format === 'Quantitative' || !$test->report_format)
        @include('partials.pathology-results-table', ['testComment' => null])
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
