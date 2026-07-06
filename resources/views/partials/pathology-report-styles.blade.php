    /* Pre-printed letterhead zone — 2.25 inch top area, QR centered */
    .letterhead-zone {
        height: 2.25in;
        width: 100%;
        position: relative;
        margin: 0;
        padding: 0;
    }
    .letterhead-qr {
        position: absolute;
        left: 47%;
        top: 42%;
        transform: translate(-50%, -50%);
        width: 72px;
        height: 72px;
        display: block;
    }

    /* Patient info header — IDC style two columns */
    .patient-info-header {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 4px;
        font-size: 10px;
        line-height: 1.45;
    }
    .patient-info-header td {
        vertical-align: top;
        padding: 0;
        border: none;
    }
    .patient-info-left {
        width: 50%;
        text-align: left;
    }
    .patient-info-right {
        width: 50%;
        text-align: left;
        padding-left: 12px;
    }
    .info-line {
        margin-bottom: 1px;
    }
    .info-label {
        font-weight: 700;
        color: #000;
    }
    .info-value {
        font-weight: 400;
        color: #000;
    }
    .patient-name-value {
        font-weight: 700;
        text-transform: uppercase;
    }
    .info-inline {
        margin-left: 10px;
    }
    .patient-info-divider {
        border-bottom: 1px solid #000;
        margin: 4px 0 8px;
    }

    /* IDC-style pathology results */
    .pathology-report-section {
        margin-bottom: 10px;
        font-family: Arial, Helvetica, DejaVu Sans, sans-serif;
    }
    .section-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        margin: 6px 0 4px;
        color: #000;
    }

    .pathology-report-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
        table-layout: fixed;
    }
    .pathology-report-table th,
    .pathology-report-table td {
        border: none;
        padding: 2px 6px;
        vertical-align: top;
        font-size: 11px;
        line-height: 1.3;
        text-align: left;
    }
    .pathology-report-table thead th {
        font-weight: 700;
        font-size: 11px;
        padding: 4px 6px;
        border-top: 1px solid #000;
        border-bottom: 1px solid #000;
        background: #e0e0e0;
        color: #000;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .pathology-report-table thead th .result-datetime {
        display: block;
        font-weight: 400;
        font-size: 9px;
        text-transform: none;
        letter-spacing: 0;
        margin-top: 1px;
    }
    .pathology-report-table tbody tr td {
        border-bottom: 1px dotted #b5b5b5;
        padding-top: 3px;
        padding-bottom: 3px;
    }
    .pathology-report-table tbody tr:last-child td {
        border-bottom: none;
    }
    .pathology-report-table .col-test {
        width: 38%;
    }
    .pathology-report-table .col-normal {
        width: 22%;
    }
    .pathology-report-table .col-unit {
        width: 14%;
    }
    .pathology-report-table .col-result {
        width: 26%;
        font-weight: 400;
    }
    .pathology-report-table .test-name-cell {
        font-weight: 700;
        color: #000;
    }
    .pathology-report-table .abnormal,
    .pathology-report-table .abnormal .result-value {
        color: #000;
        font-weight: 700;
    }

    .sub-section-row td {
        background: #e8e8e8;
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        padding: 4px 6px !important;
        font-weight: 700;
        font-size: 11px;
        color: #004a99;
    }
    .ref-notes-row td {
        border-bottom: 1px dotted #b5b5b5 !important;
        padding: 2px 6px 4px !important;
    }
    .ref-notes-text {
        font-family: 'Times New Roman', Times, DejaVu Serif, serif;
        font-size: 10px;
        line-height: 1.35;
        color: #000;
        white-space: pre-line;
    }

    .result-layout {
        width: 100%;
        border-collapse: collapse;
        border: none;
        margin: 0;
        table-layout: fixed;
    }
    .pathology-report-table .result-layout td {
        border: none !important;
        padding: 0 !important;
        vertical-align: top;
        font-size: inherit;
        line-height: inherit;
        background: transparent;
    }
    .pathology-report-table .result-flag-cell {
        width: 12px;
        padding-right: 3px !important;
        text-align: center;
    }
    .pathology-report-table .result-value-cell {
        text-align: left;
    }
    .flag-icon { font-size: 9px; line-height: 1.3; }
    .flag-high { color: #c00; }
    .flag-low { color: #c60; }

    .report-comments {
        margin-top: 8px;
        font-size: 11px;
        line-height: 1.35;
        page-break-inside: avoid;
    }
    .report-comments strong {
        font-weight: 700;
    }
    .report-comments-body {
        margin-top: 2px;
        white-space: pre-line;
    }

    /* Page layout */
    .report-page-main {
        position: relative;
        padding-bottom: 24px;
    }
    .report-page-main.has-remarks-page {
        page-break-after: always;
    }
    .report-remarks-page {
        page-break-before: always;
        padding-top: 2.25in;
    }
    .remarks-page-header {
        border-bottom: 1px solid #000;
        margin-bottom: 12px;
        padding-bottom: 6px;
    }
    .remarks-page-header h3 {
        margin: 0 0 2px;
        color: #000;
        font-size: 13px;
        font-weight: 700;
        text-transform: none;
    }
    .remarks-subtitle {
        margin: 0;
        font-size: 10px;
        color: #333;
    }
    .remark-block {
        border: none;
        border-bottom: 1px dotted #b5b5b5;
        border-radius: 0;
        padding: 6px 0;
        margin-bottom: 6px;
        background: transparent;
        page-break-inside: avoid;
    }
    .remark-title {
        margin: 0 0 4px;
        color: #000;
        font-size: 11px;
        font-weight: 700;
    }
    .remark-result {
        margin: 0 0 4px;
        font-size: 11px;
        color: #000;
    }
    .remark-body {
        font-size: 11px;
        line-height: 1.35;
        color: #000;
        white-space: pre-line;
    }
    .inline-page-footer {
        margin-top: 12px;
        padding-top: 4px;
        border-top: 1px solid #ccc;
        display: flex;
        justify-content: flex-end;
        font-size: 9px;
        color: #333;
    }

    /* Report footer — doctors + disclaimer */
    .report-doctors-footer {
        margin-top: 16px;
        padding-top: 10px;
        border-top: 1px solid #000;
        page-break-inside: avoid;
    }

    @media print {
        .report-page-main {
            padding-bottom: 1in;
        }
        .report-doctors-footer {
            position: fixed;
            bottom: 1in;
            left: 15mm;
            right: 15mm;
            width: auto;
            margin-top: 0;
            padding-top: 8px;
            background: #fff;
            z-index: 10;
        }
        .inline-page-footer {
            position: fixed;
            bottom: 0.35in;
            right: 15mm;
            margin-top: 0;
            padding-top: 0;
            border-top: none;
            z-index: 11;
        }
    }
    .report-footer-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-footer-table td {
        vertical-align: top;
        padding: 6px 12px;
        border: none;
    }
    .report-footer-doctor {
        text-align: left;
    }
    .report-footer-doctor-right {
        text-align: right;
    }
    .report-footer-doctor-name {
        font-weight: 700;
        color: #004a99;
        font-size: 13px;
        line-height: 1.35;
    }
    .report-footer-doctor-meta {
        font-size: 11px;
        font-weight: 700;
        color: #000;
        line-height: 1.4;
    }
    .report-footer-disclaimer {
        font-size: 11px;
        line-height: 1.45;
        text-align: left;
        color: #000;
        vertical-align: middle;
    }
    .report-footer-disclaimer strong {
        font-weight: 700;
    }
    .report-footer-disclaimer-single {
        text-align: left;
    }
    .report-footer-disclaimer-bottom strong {
        font-weight: 700;
    }
    .report-footer-divider-col {
        padding: 0 !important;
        width: 1px;
        border-left: 1px solid #888;
    }
    .report-footer-disclaimer-bottom {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #888;
        font-size: 11px;
        line-height: 1.45;
        text-align: left;
        color: #000;
    }
