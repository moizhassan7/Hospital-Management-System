<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Indoor Discharge Patient History Report</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Rai Foundation Teaching Hospital, Sargodha</h1>
        <h2>Indoor Discharge Patient History</h2>
        <p>From {{ \Carbon\Carbon::parse($startDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-M-Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Sr. No</th>
                <th>Adm. Date</th>
                <th>Doctor</th>
                <th>Patient (MR No)</th>
                <th>Dis. Date</th>
                <!-- <th>LOS</th> -->
                <th>Discharge Type & Diagnosis</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach($dischargedPatients as $record)
                <tr>
                    <td>{{ $counter++ }}</td>
                    <td>{{ \Carbon\Carbon::parse($record->indoorPatient->registration_date)->format('d-M-Y') }}</td>
                    <td>{{ $record->certifyingDoctor->name ?? 'N/A' }}</td>
                    <td>{{ $record->patient->name ?? 'N/A' }} ({{ $record->patient->mr_number ?? 'N/A' }})</td>
                    <td>{{ \Carbon\Carbon::parse($record->discharge_date)->format('d-M-Y') }}</td>
                    <!-- <td>{{ \Carbon\Carbon::parse($record->indoorPatient->registration_date)->diffInDays($record->discharge_date) }}</td> -->
                    <td>{{ $record->discharge_status ?? 'N/A' }} {{ $record->diagnoses ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
