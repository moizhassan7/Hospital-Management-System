<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Labels — {{ $patientRecord->patient_name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    @php
        $labelW = $label['width_mm'] ?? 50.8;
        $labelH = $label['height_mm'] ?? 25.4;
        $labelWIn = $label['width_in'] ?? 2;
        $labelHIn = $label['height_in'] ?? 1;
    @endphp
    <style>
        @page {
            size: {{ $labelWIn }}in {{ $labelHIn }}in;
            margin: 0;
        }
        @page {
            size: {{ $labelW }}mm {{ $labelH }}mm;
            margin: 0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .labels-sheet { width: {{ $labelW }}mm; }

        /* Fixed grid — no flex-grow, zero wasted vertical space */
        .label {
            position: relative;
            width: {{ $labelW }}mm;
            height: {{ $labelH }}mm;
            overflow: hidden;
            background: #fff;
            border: 0.2mm solid #000;
            border-radius: 0.35mm;
            page-break-after: always;
            break-after: page;
        }

        .row-name {
            position: absolute;
            top: 0.25mm;
            left: 0.45mm;
            right: 0.45mm;
            height: 1.9mm;
            font-size: 6.5pt;
            font-weight: 700;
            line-height: 1.9mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .row-id {
            position: absolute;
            top: 2.05mm;
            left: 0.45mm;
            right: 0.45mm;
            height: 1.7mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 5.5pt;
            line-height: 1;
        }

        .row-id .id-left {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
            min-width: 0;
        }

        .row-id .id-right {
            white-space: nowrap;
            flex-shrink: 0;
            font-weight: 600;
            margin-left: 0.5mm;
        }

        .row-barcode {
            position: absolute;
            top: 3.65mm;
            left: 0.3mm;
            right: 0.3mm;
            height: 12.2mm;
            line-height: 0;
            overflow: hidden;
        }

        .row-barcode svg {
            display: block;
            width: 100% !important;
            height: 12.2mm !important;
            max-height: 12.2mm;
        }

        .row-human {
            position: absolute;
            top: 15.95mm;
            left: 0.45mm;
            right: 0.45mm;
            height: 1.6mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 5pt;
            line-height: 1.6mm;
            text-align: center;
            letter-spacing: 0.1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .row-footer {
            position: absolute;
            bottom: 0.2mm;
            left: 0.45mm;
            right: 0.45mm;
            height: 3.2mm;
            padding-top: 0.55mm;
            border-top: 0.15mm solid #000;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 5pt;
            line-height: 1;
        }

        .row-footer .col-left {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
            min-width: 0;
        }

        .row-footer .col-right {
            white-space: nowrap;
            flex-shrink: 0;
            font-weight: 700;
            margin-left: 0.5mm;
        }

        .no-print {
            padding: 16px;
            text-align: center;
            font-family: system-ui, sans-serif;
            background: #f3f4f6;
        }

        .no-print p { color: #555; font-size: 14px; margin-top: 8px; }

        .no-print button {
            margin-top: 12px;
            background: #7c3aed;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        @media print {
            .no-print { display: none !important; }
            html, body { width: {{ $labelW }}mm; margin: 0 !important; padding: 0 !important; }
            .labels-sheet { width: {{ $labelW }}mm; }
            .label:last-child { page-break-after: auto; break-after: auto; }
        }

        @media screen {
            body { background: #e5e7eb; padding: 12px; }
            .labels-sheet { margin: 0 auto; }
            .label { margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <strong>Printing {{ $vials->count() }} label(s)…</strong>
        <p>Tube label {{ $labelWIn }}" × {{ $labelHIn }}" · Scale <strong>100%</strong> · Margins <strong>None</strong></p>
        <button type="button" onclick="window.print()">Print again</button>
    </div>

    <div class="labels-sheet">
        @foreach($vials as $vial)
            @php
                $gender = strtoupper(substr((string) $patientRecord->gender, 0, 1));
                $collected = $vial->collected_at ?? now();
            @endphp
            <div class="label">
                <div class="row-name">{{ $patientRecord->patient_name }}</div>
                <div class="row-id">
                    <span class="id-left">{{ $patientRecord->lab_registration_no ?? $patientRecord->mr_no ?? 'N/A' }}</span>
                    <span class="id-right">{{ $patientRecord->age }}y {{ $gender }}</span>
                </div>
                <div class="row-barcode">
                    <svg class="barcode" data-barcode="{{ $vial->barcode }}"></svg>
                </div>
                <div class="row-human">{{ $vial->barcode }}</div>
                <div class="row-footer">
                    <span class="col-left">Col: {{ $collected->format('d-M-y H:i') }}</span>
                    <span class="col-right">Exp: {{ $vial->expires_at?->format('d-M-y H:i') ?? 'N/A' }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('.barcode').forEach(function(el) {
            JsBarcode(el, el.dataset.barcode, {
                format: 'CODE128',
                width: 1.55,
                height: 42,
                displayValue: false,
                margin: 0,
                marginTop: 0,
                marginBottom: 0
            });
        });

        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 500);
        });
    </script>
</body>
</html>
