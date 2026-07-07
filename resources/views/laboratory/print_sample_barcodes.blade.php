<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Labels — {{ $patientRecord->patient_name }}</title>

    @vite(['resources/js/jsbarcode.js'])

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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .labels-sheet {
            width: {{ $labelW }}mm;
        }

        .label {
            width: {{ $labelW }}mm;
            height: {{ $labelH }}mm;
            background: #fff;
            overflow: hidden;
            page-break-after: always;
            break-after: page;

            display: flex;
            flex-direction: column;

            padding: 2mm 2.2mm 1.3mm 2.2mm;
        }

        .top-row {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 2mm;
            height: 5.2mm;
        }

        .patient-name {
            flex: 1;
            min-width: 0;
            font-size: 9pt;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.05;
            letter-spacing: 0.1mm;

            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .lab-no {
            flex-shrink: 0;
            font-size: 9.5pt;
            font-weight: 900;
            line-height: 1;
            text-align: right;
            letter-spacing: 0.1mm;
        }

        .barcode-area {
            width: 100%;
            height: 10.7mm;
            margin-top: 0.4mm;
            padding: 0 4mm;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .barcode {
            width: 100%;
            max-width: 100%;
            height: 10.7mm;
        }

        .human-code {
            width: 100%;
            text-align: center;
            font-family: 'Courier New', Courier, monospace;
            font-size: 6.8pt;
            font-weight: 700;
            line-height: 1.1;
            margin-top: 0.15mm;
            letter-spacing: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
        }

        .footer {
            width: 100%;
            margin-top: 0.45mm;
            overflow: hidden;
        }

        .footer-meta {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1mm;
            font-size: 6.4pt;
            line-height: 1.05;
            font-weight: 700;
        }

        .footer-meta .vial-type {
            flex: 1;
            min-width: 0;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .footer-meta .vial-volume {
            flex-shrink: 0;
            text-align: right;
            white-space: nowrap;
        }

        .footer-tests {
            width: 100%;
            text-align: center;
            font-size: 6.4pt;
            line-height: 1.08;
            margin-top: 0.2mm;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            word-break: break-word;
        }

        .no-print {
            padding: 16px;
            text-align: center;
            font-family: system-ui, sans-serif;
            background: #f3f4f6;
        }

        .no-print .actions {
            margin-top: 12px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .no-print button,
        .no-print .btn-secondary {
            background: #7c3aed;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .no-print .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            html, body {
                width: {{ $labelW }}mm;
                height: {{ $labelH }}mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .labels-sheet {
                width: {{ $labelW }}mm;
            }

            .label:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }

        @media screen {
            body {
                background: #e5e7eb;
                padding: 12px;
            }

            .labels-sheet {
                margin: 0 auto;
            }

            .label {
                margin-bottom: 10px;
                box-shadow: 0 1px 3px rgba(0,0,0,.16);
                border-radius: 2mm;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <strong>Printing {{ count($labelRows) }} label(s)…</strong>

        <div class="actions">
            <button type="button" onclick="window.print()">Print again</button>

            <a class="btn-secondary"
               href="{{ route('pathology.sample_portal', ['lab_reg_no' => $patientRecord->lab_registration_no]) }}">
                Back to portal
            </a>
        </div>
    </div>

    <div class="labels-sheet">
        @foreach($labelRows as $row)
            <div class="label">
                <div class="top-row">
                    <div class="patient-name">
                        {{ $row['patient_name'] }}
                    </div>

                    <div class="lab-no">
                        {{ $row['lab_no'] }}
                    </div>
                </div>

                <div class="barcode-area">
                    <svg
                        class="barcode"
                        id="barcode-{{ $row['vial_id'] }}"
                        data-barcode="{{ $row['barcode'] }}"
                        role="img"
                        aria-label="Barcode {{ $row['barcode'] }}"
                    ></svg>
                </div>

                <div class="human-code">
                    {{ $row['human_barcode'] }}
                </div>

                <div class="footer">
                    <div class="footer-meta">
                        <span class="vial-type">{{ $row['vial_type'] }}</span>
                        @if(!empty($row['vial_volume']))
                            <span class="vial-volume">{{ $row['vial_volume'] }}</span>
                        @endif
                    </div>
                    @if(!empty($row['test_names']))
                        <div class="footer-tests">{{ $row['test_names'] }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.barcode').forEach(function (el) {
                const value = (el.getAttribute('data-barcode') || '').trim();

                if (!value) {
                    return;
                }

                const barWidth = value.length > 14 ? 1.1 : (value.length > 11 ? 1.25 : 1.45);

                JsBarcode(el, value, {
                    format: 'CODE128',
                    displayValue: false,
                    width: barWidth,
                    height: 42,
                    margin: 0,
                    background: '#ffffff',
                    lineColor: '#000000'
                });
            });

            document.querySelectorAll('.footer-tests').forEach(function (el) {
                let size = 6.4;

                el.style.fontSize = size + 'pt';

                while (el.scrollWidth > el.clientWidth && size > 5) {
                    size -= 0.3;
                    el.style.fontSize = size + 'pt';
                }
            });

            document.querySelectorAll('.human-code').forEach(function (el) {
                let size = 6.8;

                el.style.fontSize = size + 'pt';

                while (el.scrollWidth > el.clientWidth && size > 5) {
                    size -= 0.3;
                    el.style.fontSize = size + 'pt';
                }
            });

            setTimeout(function () {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>