<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Indoor Discharge Patient Payment Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            margin: 0;
            color: #1f2937;
        }
        .header h2 {
            font-size: 16px;
            margin: 0;
            color: #4a5568;
        }
        .header p {
            margin: 5px 0 0;
            color: #718096;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f7fafc;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            color: #4a5568;
        }
        td.num, th.num {
            text-align: right;
        }
        tfoot td {
            font-weight: bold;
            background-color: #e2e8f0;
        }
    </style>
</head>
<body>
    @php $currency = get_setting('currency_symbol', 'Rs'); @endphp
    <div class="header">
        <h1>{{ get_setting('hospital_name', 'Hospital Management System') }}</h1>
        <h2>Indoor Discharge Patient Payment</h2>
        <p>From {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sr. No</th>
                <th>Adm. Date</th>
                <th>Patient (MR No)</th>
                <th>Doctor</th>
                <th>Dis. Date</th>
                <th class="num">Dr. Share</th>
                <th class="num">Hop. Share</th>
                <th class="num">Total Bill</th>
                <th class="num">Advance</th>
                <th class="num">Amount Adj.</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach($payments as $record)
                @php
                    $admission = $record->admission_date
                        ?? optional($record->indoorPatient)->registration_date
                        ?? optional($record->patient)->created_at;
                @endphp
                <tr>
                    <td>{{ $counter++ }}</td>
                    <td>{{ $admission ? \Carbon\Carbon::parse($admission)->format('d-M-Y') : 'N/A' }}</td>
                    <td>{{ $record->patient->name ?? 'N/A' }} ({{ $record->patient->mr_number ?? 'N/A' }})</td>
                    <td>{{ $record->certifyingDoctor->name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->discharge_date)->format('d-M-Y') }}</td>
                    <td class="num">{{ $currency }} {{ number_format($record->dr_share, 2) }}</td>
                    <td class="num">{{ $currency }} {{ number_format($record->hop_share, 2) }}</td>
                    <td class="num">{{ $currency }} {{ number_format($record->total_bill, 2) }}</td>
                    <td class="num">{{ $currency }} {{ number_format($record->advance_fee, 2) }}</td>
                    <td class="num">{{ $currency }} {{ number_format($record->amount_adj, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Grand total</td>
                <td class="num">{{ $currency }} {{ number_format($payments->sum('dr_share'), 2) }}</td>
                <td class="num">{{ $currency }} {{ number_format($payments->sum('hop_share'), 2) }}</td>
                <td class="num">{{ $currency }} {{ number_format($payments->sum('total_bill'), 2) }}</td>
                <td class="num">{{ $currency }} {{ number_format($payments->sum('advance_fee'), 2) }}</td>
                <td class="num">{{ $currency }} {{ number_format($payments->sum('amount_adj'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
