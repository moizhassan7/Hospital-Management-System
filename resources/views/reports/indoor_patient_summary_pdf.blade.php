<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Indoor Patient Summary Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #1f2937;
        }
        .header h2 {
            font-size: 18px;
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
            margin-top: 20px;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f7fafc;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
            color: #4a5568;
        }
        tr.total-row td {
            background-color: #e2e8f0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rai Foundation Teaching Hospital, Sargodha</h1>
        <h2>Indoor Patient Summary</h2>
        <p>From {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sr. No</th>
                <th>Ward</th>
                <th>General</th>
                <th>Welfare</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach($reportData as $wardName => $data)
                <tr>
                    <td>{{ $counter++ }}</td>
                    <td>{{ $wardName }}</td>
                    <td>{{ $data['general'] }}</td>
                    <td>{{ $data['welfare'] }}</td>
                    <td>{{ $data['total'] }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2">Grand Total:</td>
                <td>{{ $grandTotalGeneral }}</td>
                <td>{{ $grandTotalWelfare }}</td>
                <td>{{ $grandTotal }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>