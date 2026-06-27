<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Report - {{ $labPatient->patient_name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #004a99;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            color: #004a99;
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0;
            font-size: 10px;
            color: #666;
        }
        .patient-info {
            width: 100%;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-collapse: collapse;
        }
        .patient-info td {
            padding: 6px 8px;
            vertical-align: top;
        }
        .patient-info b {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #555;
        }
        .report-title {
            text-align: center;
            margin: 15px 0;
        }
        .report-title h2 {
            margin: 0;
            color: #004a99;
            font-size: 16px;
        }
        @include('partials.pathology-report-styles')
        .descriptive-item {
            margin-bottom: 12px;
            padding: 8px;
            border: 1px solid #eee;
        }
        .descriptive-item b {
            display: block;
            margin-bottom: 4px;
            color: #004a99;
        }
    </style>
</head>
<body>
    @php $hasRemarksPage = $hasRemarksPage ?? app(\App\Services\PathologyReportService::class)->hasRemarksPage($test, $historyResults, $testComment ?? null); @endphp

    <div class="report-page-main {{ $hasRemarksPage ? 'has-remarks-page' : '' }}">
        @include('partials.hospital-brand', ['variant' => 'print-pdf', 'subtitle' => 'Pathology Laboratory Report'])

        @if(!empty($qrCodeDataUri))
            <table style="width:100%; margin-bottom: 8px;">
                <tr>
                    <td style="text-align: right;">
                        <img src="{{ $qrCodeDataUri }}" alt="QR" style="width: 80px; height: 80px;">
                        <div style="font-size: 8px; color: #666; text-align: right;">Scan for online report</div>
                    </td>
                </tr>
            </table>
        @endif

        <table class="patient-info">
            <tr>
                <td><b>Patient Name</b>{{ $labPatient->patient_name }}</td>
                <td><b>Lab Reg No</b>{{ $labPatient->lab_registration_no ?? 'N/A' }}</td>
                <td><b>MR Number</b>{{ $labPatient->mr_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><b>Age / Gender</b>{{ $labPatient->age }} / {{ $labPatient->gender }}</td>
                <td><b>Registration Date</b>{{ $labPatient->created_at->format('d-M-Y H:i') }}</td>
                <td><b>Referrer</b>{{ $labPatient->refer_by_doctor_name ?? 'Self Referred' }}</td>
            </tr>
            <tr>
                <td colspan="2"><b>Contact</b>{{ $labPatient->contact_no ?? 'N/A' }}</td>
                <td></td>
            </tr>
            @php
                $currentTestResults = collect($labPatient->getSelectedTestsArray())->firstWhere('id', $test->id);
            @endphp
            @if($currentTestResults)
            <tr>
                <td><b>Sample Collected</b>{{ !empty($currentTestResults['sample_collected_at']) ? \Carbon\Carbon::parse($currentTestResults['sample_collected_at'])->format('d-M-Y H:i') : '—' }}</td>
                <td><b>Received in Lab</b>{{ !empty($currentTestResults['sample_received_in_lab_at']) ? \Carbon\Carbon::parse($currentTestResults['sample_received_in_lab_at'])->format('d-M-Y H:i') : '—' }}</td>
                <td><b>Reported At</b>{{ !empty($currentTestResults['result_reported_at'] ?? $currentTestResults['result_completed_at'] ?? null) ? \Carbon\Carbon::parse($currentTestResults['result_reported_at'] ?? $currentTestResults['result_completed_at'])->format('d-M-Y H:i') : '—' }}</td>
            </tr>
            @endif
        </table>

        <div class="report-title">
            <h2>{{ $test->name }}</h2>
        </div>

        @if($test->report_format === 'Quantitative' || !$test->report_format)
            @include('partials.pathology-results-table', ['testComment' => null, 'pdf' => true])
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
