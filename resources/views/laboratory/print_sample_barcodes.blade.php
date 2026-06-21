<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Barcode Labels - {{ $patientRecord->patient_name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: 50mm 30mm;
            margin: 2mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000;
        }
        .labels-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
        }
        .label {
            width: 50mm;
            height: 30mm;
            border: 1px dashed #ccc;
            padding: 2mm;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        .label-header {
            font-weight: bold;
            font-size: 7px;
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            margin-bottom: 1mm;
        }
        .patient-info {
            font-size: 7px;
            line-height: 1.3;
        }
        .patient-info strong {
            font-weight: bold;
        }
        .barcode-container {
            text-align: center;
            margin: 1mm 0;
        }
        .barcode-container svg {
            max-width: 100%;
            height: 12mm;
        }
        .barcode-text {
            font-family: monospace;
            font-size: 7px;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .vial-info {
            font-size: 7px;
            text-align: center;
            font-weight: bold;
            margin-top: 1mm;
        }
        .expiry-info {
            font-size: 6px;
            text-align: center;
            color: #333;
        }
        .no-print {
            padding: 20px;
            text-align: center;
            background: #f3f4f6;
        }
        .no-print button {
            background: #7c3aed;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin: 0 8px;
        }
        .no-print button:hover {
            background: #6d28d9;
        }
        .no-print .secondary {
            background: #6b7280;
        }
        .no-print .secondary:hover {
            background: #4b5563;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .label {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <h2 style="margin-bottom: 16px;">Sample Barcode Labels — {{ $patientRecord->patient_name }}</h2>
        <p style="margin-bottom: 16px; color: #666;">{{ $vials->count() }} label(s) ready to print</p>
        <button onclick="window.print()">Print Barcodes</button>
        <button class="secondary" onclick="window.close()">Close</button>
        <button class="secondary" onclick="window.location.href='{{ route('pathology.sample_portal', ['mr_no' => $patientRecord->mr_no]) }}'">Back to Portal</button>
    </div>

    <div class="labels-container">
        @foreach($vials as $vial)
            @php
                $testNames = \App\Models\Test::whereIn('id', $vial->test_ids ?? [])->pluck('name')->implode(', ');
            @endphp
            <div class="label">
                <div class="label-header">PATHOLOGY SAMPLE</div>
                <div class="patient-info">
                    <strong>{{ $patientRecord->patient_name }}</strong><br>
                    MR: {{ $patientRecord->mr_no ?? 'N/A' }} |
                    {{ $patientRecord->age }}/{{ substr($patientRecord->gender, 0, 1) }} |
                    {{ $patientRecord->priority }}
                </div>
                <div class="barcode-container">
                    <svg class="barcode" data-barcode="{{ $vial->barcode }}"></svg>
                </div>
                <div class="barcode-text">{{ $vial->barcode }}</div>
                <div class="vial-info">{{ $vial->vial_type }} — Vial {{ $vial->vial_number }}</div>
                <div class="expiry-info">
                    Tests: {{ \Illuminate\Support\Str::limit($testNames, 40) }}<br>
                    Exp: {{ $vial->expires_at?->format('d/m/Y H:i') ?? 'N/A' }}
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('.barcode').forEach(function(el) {
            JsBarcode(el, el.dataset.barcode, {
                format: 'CODE128',
                width: 1.5,
                height: 35,
                displayValue: false,
                margin: 0
            });
        });

        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
