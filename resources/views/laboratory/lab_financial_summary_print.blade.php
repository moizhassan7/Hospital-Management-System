<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        @if(($tab ?? '') === 'rate_list')
            Lab Rate List — {{ config('hospital.short_name') }}
        @else
            Lab Financial Summary — {{ config('hospital.short_name') }}
        @endif
    </title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #333; margin: 16px; }
        .header { border-bottom: 2px solid #004a99; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { color: #004a99; margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 9px; color: #666; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { border: 1px solid #ddd; padding: 6px 8px; text-align: center; }
        .summary .label { font-size: 7px; text-transform: uppercase; color: #666; display: block; }
        .summary .value { font-size: 13px; font-weight: bold; color: #004a99; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 4px 5px; vertical-align: top; }
        table.data th { background: #004a99; color: #fff; font-size: 7px; text-transform: uppercase; }
        table.data tr:nth-child(even) { background: #f8fafc; }
        .section-title { font-size: 11px; font-weight: bold; color: #004a99; margin: 16px 0 8px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .status-paid { color: #0d9488; font-weight: bold; }
        .status-due { color: #d97706; font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        $money = fn ($val) => number_format((float) $val, 2);
        $fmt = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d-M-Y H:i') : '—';
        $tabName = $tab ?? 'patients';
    @endphp

    <div class="no-print" style="margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 6px 14px; background: #004a99; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
    </div>

    <div class="header">
        <h1>{{ config('hospital.name') }}</h1>
        @if($tabName === 'rate_list')
            <p>Lab Rate List — Desktop pathology test rates</p>
        @else
            <p>Lab Financial Summary — {{ $date_from->format('d M Y') }} to {{ $date_to->format('d M Y') }}</p>
        @endif
        <p>Generated: {{ now()->format('d M Y h:i A') }}</p>
    </div>

    @if($tabName === 'rate_list')
        <table class="summary">
            <tr>
                <td><span class="label">Tests</span><span class="value">{{ $summary['tests'] }}</span></td>
                <td><span class="label">With Rate</span><span class="value">{{ $summary['with_price'] }}</span></td>
                <td><span class="label">Test Heads</span><span class="value">{{ $summary['heads'] }}</span></td>
            </tr>
        </table>

        <div class="section-title">Rate List — All Tests</div>
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Desktop ID</th>
                    <th>Test</th>
                    <th>Test Head</th>
                    <th>Type</th>
                    <th class="text-right">Rate</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rate_rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['desktop_test_id'] ?: '—' }}</td>
                        <td>{{ $row['test_name'] }}</td>
                        <td>{{ $row['test_head'] }}</td>
                        <td>{{ $row['type'] }}</td>
                        <td class="text-right">{{ $money($row['price']) }}</td>
                        <td>{{ $row['is_active'] ? 'Active' : 'Inactive' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <table class="summary">
            <tr>
                <td><span class="label">Patients</span><span class="value">{{ $summary['patients'] }}</span></td>
                <td><span class="label">Tests</span><span class="value">{{ $summary['tests'] }}</span></td>
                <td><span class="label">Subtotal</span><span class="value">{{ $money($summary['sub_total'] ?? 0) }}</span></td>
                <td><span class="label">Discount</span><span class="value">{{ $money($summary['discount'] ?? 0) }}</span></td>
                <td><span class="label">Grand Total</span><span class="value">{{ $money($summary['grand_total']) }}</span></td>
                <td><span class="label">Paid</span><span class="value">{{ $money($summary['paid_amount']) }}</span></td>
                <td><span class="label">Due</span><span class="value">{{ $money($summary['due_amount']) }}</span></td>
            </tr>
        </table>

        @if($tabName === 'patients')
            <div class="section-title">Patients — Billing Summary</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reg Date</th>
                        <th>Lab Reg</th>
                        <th>Patient</th>
                        <th>Tests</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">Discount</th>
                        <th>Disc. Type</th>
                        <th class="text-right">Grand Total</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Due</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient_rows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $fmt($row['registration_date']) }}</td>
                            <td>{{ $row['lab_registration_no'] ?? '—' }}</td>
                            <td>{{ $row['patient_name'] }}</td>
                            <td>{{ $row['test_count'] }}</td>
                            <td class="text-right">{{ $money($row['sub_total']) }}</td>
                            <td class="text-right">{{ $money($row['discount']) }}</td>
                            <td>
                                @if($row['discount'] > 0)
                                    {{ ($row['discount_type'] ?? '') === 'percentage' ? 'Percentage ('.$row['discount_value'].'%)' : 'Flat (PKR)' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right">{{ $money($row['grand_total']) }}</td>
                            <td class="text-right">{{ $money($row['paid_amount']) }}</td>
                            <td class="text-right">{{ $money($row['due_amount']) }}</td>
                            <td class="{{ $row['payment_status'] === 'paid' ? 'status-paid' : 'status-due' }}">
                                {{ ucfirst($row['payment_status']) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="section-title">Revenue By Test</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Test</th>
                        <th class="text-right">Count</th>
                        <th class="text-right">Revenue</th>
                        <th class="text-right">Avg Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($test_rows as $index => $row)
                        @php
                            $avg = $row['test_count'] > 0 ? $row['revenue'] / $row['test_count'] : 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row['test_name'] }}</td>
                            <td class="text-right">{{ number_format($row['test_count']) }}</td>
                            <td class="text-right">{{ $money($row['revenue']) }}</td>
                            <td class="text-right">{{ $money($avg) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</body>
</html>
