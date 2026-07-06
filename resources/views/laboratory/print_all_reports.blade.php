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

        @media print {
            .no-print { display: none; }
            body { margin: 0; }
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

    <div class="print-all-summary no-print">
        <h1>{{ $labPatient->patient_name }}</h1>
        <p>Lab Reg: {{ $labPatient->lab_registration_no ?? 'N/A' }} &mdash; {{ count($reports) }} report(s) with entered results</p>
    </div>

    @foreach($reports as $report)
        <div class="report-bundle">
            @include('partials.pathology-report-single', $report)
        </div>
    @endforeach
</body>
</html>
