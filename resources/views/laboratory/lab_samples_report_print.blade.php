<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Samples Report — {{ config('hospital.short_name') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #333; margin: 16px; }
        .header { border-bottom: 2px solid #004a99; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { color: #004a99; margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 9px; color: #666; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { border: 1px solid #ddd; padding: 6px 8px; text-align: center; }
        .summary .label { font-size: 7px; text-transform: uppercase; color: #666; display: block; }
        .summary .value { font-size: 14px; font-weight: bold; color: #004a99; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 4px 5px; vertical-align: top; }
        table.data th { background: #004a99; color: #fff; font-size: 7px; text-transform: uppercase; }
        table.data tr:nth-child(even) { background: #f8fafc; }
        .section-title { font-size: 11px; font-weight: bold; color: #004a99; margin: 16px 0 8px; text-transform: uppercase; }
        .status-completed { color: #0d9488; font-weight: bold; }
        .status-pending { color: #d97706; font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        $fmt = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d-M-Y H:i') : '—';
        $showTests = ($tab ?? 'tests') === 'tests';
    @endphp

    <div class="no-print" style="margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 6px 14px; background: #004a99; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
    </div>

    <div class="header">
        <h1>{{ config('hospital.name') }}</h1>
        <p>Lab Samples Report — {{ $date_from->format('d M Y') }} to {{ $date_to->format('d M Y') }}</p>
        <p>Generated: {{ now()->format('d M Y h:i A') }}</p>
    </div>

    <table class="summary">
        <tr>
            <td><span class="label">Patients</span><span class="value">{{ $summary['patients'] }}</span></td>
            <td><span class="label">Tests</span><span class="value">{{ $summary['tests'] }}</span></td>
            <td><span class="label">Vials</span><span class="value">{{ $summary['vials'] }}</span></td>
            <td><span class="label">Not Collected</span><span class="value">{{ $summary['not_collected'] }}</span></td>
            <td><span class="label">In Lab</span><span class="value">{{ $summary['in_lab'] }}</span></td>
            <td><span class="label">Results Done</span><span class="value">{{ $summary['completed'] }}</span></td>
        </tr>
    </table>

    @if($showTests)
        <div class="section-title">Tests — Sample / Receive in Lab / Results</div>
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reg Date</th>
                    <th>Lab Reg</th>
                    <th>Patient</th>
                    <th>Test</th>
                    <th>Sample</th>
                    <th>Collected</th>
                    <th>In Lab</th>
                    <th>Result</th>
                    <th>Result At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($test_rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $fmt($row['registration_date']) }}</td>
                        <td>{{ $row['lab_registration_no'] ?? '—' }}</td>
                        <td>{{ $row['patient_name'] }}</td>
                        <td>{{ $row['test_name'] }}</td>
                        <td>{{ $row['sample_status_label'] }}</td>
                        <td>{{ $fmt($row['sample_collected_at']) }}</td>
                        <td>{{ $fmt($row['sample_received_in_lab_at']) }}</td>
                        <td class="{{ $row['result_status'] === 'completed' ? 'status-completed' : 'status-pending' }}">
                            {{ ucfirst($row['result_status']) }}
                        </td>
                        <td>{{ $fmt($row['result_completed_at']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="section-title">Sample Vials — Collection / Receive / Reported</div>
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Lab Reg</th>
                    <th>Patient</th>
                    <th>Barcode</th>
                    <th>Vial</th>
                    <th>Tests</th>
                    <th>Status</th>
                    <th>Collected</th>
                    <th>In Lab</th>
                    <th>Reported</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vial_rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['lab_registration_no'] ?? '—' }}</td>
                        <td>{{ $row['patient_name'] }}</td>
                        <td>{{ $row['barcode'] }}</td>
                        <td>{{ $row['vial_type'] }} #{{ $row['vial_number'] }}</td>
                        <td>{{ $row['tests'] }}</td>
                        <td>{{ $row['status_label'] }}</td>
                        <td>{{ $fmt($row['collected_at']) }}</td>
                        <td>{{ $fmt($row['received_in_lab_at']) }}</td>
                        <td>{{ $fmt($row['reported_at']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
