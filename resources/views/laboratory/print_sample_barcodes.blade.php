<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Barcode Labels - {{ $patientRecord->patient_name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: 50mm 40mm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            width: 50mm;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .labels-container {
            width: 50mm;
        }
        .label {
            width: 50mm;
            height: 40mm;
            padding: 1.5mm 2mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            page-break-after: always;
            page-break-inside: avoid;
            border: 1px dashed #ccc;
        }
        .label-header {
            font-weight: bold;
            font-size: 6.5pt;
            text-align: center;
            letter-spacing: 0.3px;
            border-bottom: 0.4pt solid #000;
            padding-bottom: 0.8mm;
            margin-bottom: 0.8mm;
            line-height: 1;
            flex-shrink: 0;
        }
        .patient-name {
            font-size: 7pt;
            font-weight: bold;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
        }
        .patient-meta {
            font-size: 6pt;
            line-height: 1.15;
            margin-bottom: 0.6mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
        }
        .barcode-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 0;
            margin: 0.4mm 0;
        }
        .barcode-wrap svg {
            display: block;
            max-width: 46mm;
            max-height: 11mm;
            width: 100%;
            height: auto;
        }
        .barcode-text {
            font-family: 'Courier New', monospace;
            font-size: 6pt;
            text-align: center;
            letter-spacing: 0.2px;
            line-height: 1;
            flex-shrink: 0;
        }
        .vial-line {
            font-size: 6.5pt;
            font-weight: bold;
            text-align: center;
            line-height: 1.15;
            margin-top: 0.5mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
        }
        .expiry-line {
            font-size: 5.5pt;
            text-align: center;
            line-height: 1.15;
            margin-top: 0.3mm;
            flex-shrink: 0;
        }
        .no-print {
            padding: 20px;
            text-align: center;
            background: #f3f4f6;
            width: auto;
        }
        .no-print p.hint {
            font-size: 13px;
            color: #555;
            margin-bottom: 12px;
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
            .label:last-child {
                page-break-after: auto;
            }
        }
        @media screen {
            body {
                width: auto;
                background: #e5e7eb;
                padding: 16px;
            }
            .labels-container {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <h2 style="margin-bottom: 8px;">Sample Barcode Labels — {{ $patientRecord->patient_name }}</h2>
        <p style="margin-bottom: 8px; color: #666;">{{ $vials->count() }} label(s) ready to print</p>
        <p class="hint">Label size: <strong>50mm × 40mm</strong>. In print dialog set Scale to <strong>100%</strong> and Margins to <strong>None / Minimum</strong>.</p>
        <button onclick="window.print()">Print Barcodes</button>
        <button class="secondary" onclick="window.close()">Close</button>
        <button class="secondary" onclick="window.location.href='{{ route('pathology.sample_portal', ['mr_no' => $patientRecord->mr_no]) }}'">Back to Portal</button>
    </div>

    <div class="labels-container">
        @foreach($vials as $vial)
            <div class="label">
                <div class="label-header">{{ config('hospital.short_name') }} &middot; PATHOLOGY</div>
                <div class="patient-name">{{ $patientRecord->patient_name }}</div>
                <div class="patient-meta">
                    MR: {{ $patientRecord->mr_no ?? 'N/A' }} |
                    {{ $patientRecord->age }}/{{ substr($patientRecord->gender, 0, 1) }} |
                    {{ $patientRecord->priority }}
                </div>
                <div class="barcode-wrap">
                    <svg class="barcode" data-barcode="{{ $vial->barcode }}"></svg>
                </div>
                <div class="barcode-text">{{ $vial->barcode }}</div>
                <div class="vial-line">{{ $vial->vial_type }} — Vial {{ $vial->vial_number }}</div>
                <div class="expiry-line">Exp: {{ $vial->expires_at?->format('d/m/Y H:i') ?? 'N/A' }}</div>
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('.barcode').forEach(function(el) {
            JsBarcode(el, el.dataset.barcode, {
                format: 'CODE128',
                width: 1.1,
                height: 24,
                displayValue: false,
                margin: 0
            });
        });

        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
