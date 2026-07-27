<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt - {{ $patient->lab_registration_no }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.3;
            color: #000;
            width: 80mm;
            padding: 10px;
            margin: 0 auto;
            font-weight: 500;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .header {
            margin-bottom: 15px;
        }

        .title {
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 12px;
            margin-bottom: 4px;
        }

        .info-block {
            font-size: 13px;
            margin-bottom: 10px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .details-table {
            width: 100%;
            font-size: 13px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .details-table td:first-child {
            width: 35%;
        }

        .items-table {
            width: 100%;
            font-size: 13px;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .items-table th,
        .items-table td {
            padding: 5px 0;
            text-align: left;
        }

        .items-table th {
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
        }

        .totals-table {
            width: 100%;
            font-size: 14px;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .totals-table td {
            padding: 4px 0;
        }

        .totals-table td:first-child {
            width: 50%;
        }

        .footer {
            margin-top: 15px;
            font-size: 12px;
        }

        @media print {
            body {
                width: 80mm;
                padding: 5px;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        .no-print-bar {
            background-color: #f1f5f9;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .print-btn {
            background-color: #0f172a;
            color: #fff;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 4px;
            font-family: inherit;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="no-print no-print-bar">
        <span>Thermal Receipt View</span>
        <button class="print-btn" onclick="window.print()">Print Receipt</button>
    </div>

    @foreach(['LAB COPY', 'PATIENT COPY'] as $index => $copyType)
        @if($index > 0)
            <div style="page-break-before: always; height: 5px;"></div>
        @endif

        @php
            $center = $patient->limsBooking->collectionCenter ?? null;
            $printName = $center->name ?? config('hospital.name', 'Malik Labs');
            $printAddress = $center->address ?? config('hospital.city', 'Sargodha');
            $printPhone = $center->phone ?? config('hospital.phone');
        @endphp
        <div class="text-center header">
            <h1 class="title font-bold">{{ $printName }}</h1>
            <p class="subtitle">{{ config('hospital.tagline', 'Premium Diagnostics & Pathology') }}</p>
            <p class="subtitle">{{ $printAddress }}</p>
            @if($printPhone)
                <p class="subtitle">Ph: {{ $printPhone }}</p>
            @endif
        </div>

        <div style="text-align: center; font-weight: bold; padding: 4px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; margin-bottom: 10px;">
            {{ $copyType }}
        </div>

        <table class="details-table">
            <tr>
                <td class="font-bold">Reg No:</td>
                <td>{{ $patient->lab_registration_no }}</td>
            </tr>
            <tr>
                <td class="font-bold">MR No:</td>
                <td>{{ $patient->mr_no ?? '—' }}</td>
            </tr>
            <tr>
                <td class="font-bold">Date:</td>
                <td>{{ $patient->created_at->format('d-M-Y h:i A') }}</td>
            </tr>
            <tr>
                <td class="font-bold">Patient:</td>
                <td class="font-bold">{{ $patient->patient_name }}</td>
            </tr>
            <tr>
                <td class="font-bold">Age/Sex:</td>
                <td>{{ $patient->age }} Y / {{ $patient->gender }}</td>
            </tr>
            <tr>
                <td class="font-bold">Contact:</td>
                <td>{{ $patient->contact_no ?? '—' }}</td>
            </tr>
            <tr>
                <td class="font-bold">Referred:</td>
                <td>{{ $patient->self_referred ? 'Self Referred' : ($patient->refer_by_doctor_name ?? '—') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="font-bold">Test Description</th>
                    <th class="font-bold text-right w-24">Price (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patient->getSelectedTestsArray() as $test)
                    <tr>
                        <td>{{ $test['name'] }}</td>
                        <td class="text-right">{{ number_format($test['price']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table class="totals-table">
            <tr>
                <td>Sub-Total:</td>
                <td class="text-right">{{ number_format($patient->sub_total) }}</td>
            </tr>
            @if($patient->discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-{{ number_format($patient->discount) }}</td>
                </tr>
            @endif
            <tr class="font-bold">
                <td>Grand Total:</td>
                <td class="text-right">{{ number_format($patient->grand_total) }}</td>
            </tr>
            <tr>
                <td>Paid Amount:</td>
                <td class="text-right font-bold text-green-700">{{ number_format($patient->paid_amount) }}</td>
            </tr>
            @if($patient->due_amount > 0)
                <tr class="font-bold">
                    <td>Due Amount:</td>
                    <td class="text-right" style="color: red;">{{ number_format($patient->due_amount) }}</td>
                </tr>
            @else
                <tr>
                    <td colspan="2" class="text-center">
                        <div style="font-size: 16px; font-weight: bold; border: 2px solid #000; padding: 4px; display: inline-block; margin-top: 8px;">
                            *** BILL PAID ***
                        </div>
                    </td>
                </tr>
            @endif
        </table>

        <div class="divider"></div>

        <div class="text-center footer">
            <p class="font-bold">Thank You for choosing us!</p>
            <p>This is a system generated receipt.</p>
            <p>Software By Switch2itech Ph#03007844301</p>
        </div>
    @endforeach

    <script>
        window.addEventListener('DOMContentLoaded', function () {
            // Auto trigger print
            setTimeout(function () {
                window.print();
                // Close tab if opened in a popup window
                if (window.opener) {
                    window.close();
                }
            }, 800);
        });
    </script>
</body>

</html>