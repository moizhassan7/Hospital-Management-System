<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Critical Test Report — {{ config('hospital.short_name') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        .header {
            border-bottom: 2px solid #c53030;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            color: #c53030;
            margin: 0 0 4px;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            color: #666;
            font-size: 10px;
        }
        .summary {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .summary td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            width: 33.33%;
            vertical-align: top;
        }
        .summary .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #718096;
            display: block;
            margin-bottom: 4px;
        }
        .summary .value {
            font-size: 18px;
            font-weight: bold;
            color: #c53030;
        }
        table.records {
            width: 100%;
            border-collapse: collapse;
        }
        table.records th,
        table.records td {
            border: 1px solid #ddd;
            padding: 5px 6px;
            font-size: 9px;
            vertical-align: top;
        }
        table.records th {
            background: #fef2f2;
            text-transform: uppercase;
            color: #742a2a;
        }
        .flag-high {
            color: #c53030;
            font-weight: bold;
        }
        .flag-low {
            color: #c05621;
            font-weight: bold;
        }
        .result-value {
            font-weight: bold;
            color: #c53030;
        }
        .no-data {
            padding: 20px;
            text-align: center;
            color: #276749;
            background: #f0fff4;
            border: 1px solid #9ae6b4;
        }
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #c53030; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Report</button>
    </div>

    <div class="header">
        <h1>{{ config('hospital.name') }}</h1>
        <p>{{ config('hospital.tagline') }}</p>
        <p class="hms-detail-item"><strong>Critical Test Report</strong> — {{ $date_from->format('d M Y') }} to {{ $date_to->format('d M Y') }}</p>
        <p>Generated: {{ now()->format('d M Y h:i A') }}</p>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="label">Patients with Critical Results</span>
                <span class="value">{{ $patient_count }}</span>
            </td>
            <td>
                <span class="label">Critical Test Records</span>
                <span class="value">{{ $critical_count }}</span>
            </td>
            <td>
                <span class="label">Report Period</span>
                <span class="value" style="font-size: 14px;">{{ $date_from->format('d-M-Y') }} — {{ $date_to->format('d-M-Y') }}</span>
            </td>
        </tr>
    </table>

    @if($records->isEmpty())
        <div class="no-data">No critical test results found for this date range.</div>
    @else
        <table class="records">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date/Time</th>
                    <th>Lab Reg</th>
                    <th>Patient</th>
                    <th>Age/Sex</th>
                    <th>Phone</th>
                    <th>Test</th>
                    <th>Parameter</th>
                    <th>Result</th>
                    <th>Reference</th>
                    <th>Flag</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['result_date']->format('d-M-Y h:i A') }}</td>
                        <td>{{ $row['lab_registration_no'] ?? '—' }}</td>
                        <td>{{ $row['patient_name'] }}</td>
                        <td>{{ $row['age'] }} / {{ $row['gender'] }}</td>
                        <td>{{ $row['contact_no'] ?? '—' }}</td>
                        <td>{{ $row['test_name'] }}</td>
                        <td>{{ $row['parameter'] }}</td>
                        <td class="result-value">{{ $row['result_value'] }} {{ $row['unit'] }}</td>
                        <td>{{ $row['reference_range'] }}</td>
                        <td class="{{ $row['flag'] === 'HIGH' ? 'flag-high' : 'flag-low' }}">
                            {{ $row['flag'] === 'HIGH' ? '▲ HIGH' : '▼ LOW' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
