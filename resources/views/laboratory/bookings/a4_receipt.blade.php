<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A4 Receipt - {{ $patient->lab_registration_no }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .page {
            width: 297mm;
            height: 210mm;
            display: flex;
            padding: 10mm;
            margin: 0 auto;
        }
        .half-page {
            width: 50%;
            height: 100%;
            padding: 0 15mm;
            position: relative;
        }
        .half-page:first-child {
            border-right: 1px dashed #ccc;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .hospital-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .tagline {
            font-size: 11px;
            margin-top: 2px;
        }
        .copy-type {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
            background: #eee;
            padding: 5px;
            border-radius: 4px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 2px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 25%;
        }
        .tests-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .tests-table th, .tests-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        .tests-table th {
            background: #f9f9f9;
            font-weight: bold;
        }
        .tests-table .text-right {
            text-align: right;
        }
        .totals-box {
            width: 60%;
            float: right;
            border: 1px solid #000;
            padding: 10px;
            border-radius: 4px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 4px 0;
        }
        .totals-table .text-right {
            text-align: right;
        }
        .totals-table .font-bold {
            font-weight: bold;
        }
        .footer {
            position: absolute;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            text-align: center;
            font-size: 11px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        @media print {
            body {
                width: 297mm;
                height: 210mm;
            }
            .page {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        .no-print-bar {
            background-color: #f1f5f9;
            padding: 15px;
            text-align: center;
        }
        .print-btn {
            background-color: #0f172a;
            color: #fff;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="no-print no-print-bar">
        <button class="print-btn" onclick="window.print()">Print A4 Landscape Receipt</button>
    </div>
    
    <div class="page">
        <!-- Patient Copy -->
        <div class="half-page">
            <div class="header">
                <div class="hospital-name">{{ config('hospital.name', 'Malik Labs') }}</div>
                <div class="tagline">{{ config('hospital.tagline', 'Premium Diagnostics & Pathology') }} - {{ config('hospital.city', 'Sargodha') }}</div>
            </div>
            
            <div class="copy-type">PATIENT COPY</div>
            
            <table class="info-table">
                <tr>
                    <td class="label">Reg No:</td><td>{{ $patient->lab_registration_no }}</td>
                    <td class="label">Date:</td><td>{{ $patient->created_at->format('d-M-Y h:i A') }}</td>
                </tr>
                <tr>
                    <td class="label">Patient Name:</td><td><strong>{{ $patient->patient_name }}</strong></td>
                    <td class="label">Age / Sex:</td><td>{{ $patient->age }} Y / {{ $patient->gender }}</td>
                </tr>
                <tr>
                    <td class="label">Contact:</td><td>{{ $patient->contact_no ?? '—' }}</td>
                    <td class="label">Referred By:</td><td>{{ $patient->self_referred ? 'Self Referred' : ($patient->refer_by_doctor_name ?? '—') }}</td>
                </tr>
            </table>

            <table class="tests-table">
                <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Test Description</th>
                        <th class="text-right">Price (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->getSelectedTestsArray() as $index => $test)
                        <tr>
                            <td style="width: 10%;">{{ $index + 1 }}</td>
                            <td>{{ $test['name'] }}</td>
                            <td class="text-right" style="width: 30%;">{{ number_format($test['price']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td>Sub-Total:</td><td class="text-right">{{ number_format($patient->sub_total) }}</td>
                    </tr>
                    @if($patient->discount > 0)
                    <tr>
                        <td>Discount:</td><td class="text-right">-{{ number_format($patient->discount) }}</td>
                    </tr>
                    @endif
                    <tr class="font-bold" style="border-top: 1px solid #000; border-bottom: 1px solid #000;">
                        <td>Grand Total:</td><td class="text-right">{{ number_format($patient->grand_total) }}</td>
                    </tr>
                    <tr>
                        <td>Paid Amount:</td><td class="text-right font-bold text-green-700">{{ number_format($patient->paid_amount) }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td>Due Amount:</td><td class="text-right" style="color: red;">{{ number_format($patient->due_amount) }}</td>
                    </tr>
                </table>
                @if($patient->due_amount <= 0)
                    <div style="text-align: center; margin-top: 10px; font-weight: bold; border: 2px solid #000; padding: 4px;">*** BILL PAID ***</div>
                @endif
            </div>

            <div class="footer">
                <p>Software By Switch2itech Ph#03007844301 | Printed: {{ now()->format('d-M-Y h:i A') }}</p>
            </div>
        </div>

        <!-- Lab Copy -->
        <div class="half-page">
            <div class="header">
                <div class="hospital-name">{{ config('hospital.name', 'Malik Labs') }}</div>
                <div class="tagline">{{ config('hospital.tagline', 'Premium Diagnostics & Pathology') }} - {{ config('hospital.city', 'Sargodha') }}</div>
            </div>
            
            <div class="copy-type">LAB COPY</div>
            
            <table class="info-table">
                <tr>
                    <td class="label">Reg No:</td><td>{{ $patient->lab_registration_no }}</td>
                    <td class="label">Date:</td><td>{{ $patient->created_at->format('d-M-Y h:i A') }}</td>
                </tr>
                <tr>
                    <td class="label">Patient Name:</td><td><strong>{{ $patient->patient_name }}</strong></td>
                    <td class="label">Age / Sex:</td><td>{{ $patient->age }} Y / {{ $patient->gender }}</td>
                </tr>
                <tr>
                    <td class="label">Contact:</td><td>{{ $patient->contact_no ?? '—' }}</td>
                    <td class="label">Referred By:</td><td>{{ $patient->self_referred ? 'Self Referred' : ($patient->refer_by_doctor_name ?? '—') }}</td>
                </tr>
            </table>

            <table class="tests-table">
                <thead>
                    <tr>
                        <th>Sr.</th>
                        <th>Test Description</th>
                        <th class="text-right">Price (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->getSelectedTestsArray() as $index => $test)
                        <tr>
                            <td style="width: 10%;">{{ $index + 1 }}</td>
                            <td>{{ $test['name'] }}</td>
                            <td class="text-right" style="width: 30%;">{{ number_format($test['price']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td>Sub-Total:</td><td class="text-right">{{ number_format($patient->sub_total) }}</td>
                    </tr>
                    @if($patient->discount > 0)
                    <tr>
                        <td>Discount:</td><td class="text-right">-{{ number_format($patient->discount) }}</td>
                    </tr>
                    @endif
                    <tr class="font-bold" style="border-top: 1px solid #000; border-bottom: 1px solid #000;">
                        <td>Grand Total:</td><td class="text-right">{{ number_format($patient->grand_total) }}</td>
                    </tr>
                    <tr>
                        <td>Paid Amount:</td><td class="text-right font-bold text-green-700">{{ number_format($patient->paid_amount) }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td>Due Amount:</td><td class="text-right" style="color: red;">{{ number_format($patient->due_amount) }}</td>
                    </tr>
                </table>
                @if($patient->due_amount <= 0)
                    <div style="text-align: center; margin-top: 10px; font-weight: bold; border: 2px solid #000; padding: 4px;">*** BILL PAID ***</div>
                @endif
            </div>

            <div class="footer">
                <p>Software By Switch2itech Ph#03007844301 | Printed: {{ now()->format('d-M-Y h:i A') }}</p>
            </div>
        </div>
    </div>
    
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
