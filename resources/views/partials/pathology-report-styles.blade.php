    /* Compact lab report header */
    .lab-report-header {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2px;
    }
    .lab-report-header > tbody > tr > td {
        vertical-align: top;
        padding: 0;
        border: none;
    }
    .lab-header-brand { width: 42%; }
    .lab-header-patient { width: 38%; }
    .lab-header-qr { width: 20%; text-align: right; vertical-align: top; }
    .lab-header-brand-inner {
        border-collapse: collapse;
    }
    .lab-header-brand-inner td {
        border: none;
        padding: 0;
        vertical-align: middle;
    }
    .lab-header-logo img {
        height: 42px;
        width: 42px;
        object-fit: contain;
        display: block;
    }
    .lab-header-logo {
        padding-right: 6px !important;
        width: 48px;
    }
    .lab-header-name {
        font-size: 16px;
        font-weight: 700;
        line-height: 1.1;
        color: #000;
        letter-spacing: 0.02em;
    }
    .lab-header-tagline {
        display: inline-block;
        margin-top: 2px;
        padding: 2px 8px;
        background: #004a99;
        color: #fff;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 0.04em;
        line-height: 1.2;
    }
    .lab-header-patient-inner {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
        line-height: 1.3;
    }
    .lab-header-patient-inner td {
        border: none;
        padding: 0 0 1px;
        vertical-align: top;
    }
    .lab-header-patient-inner .lbl {
        white-space: nowrap;
        padding-right: 4px;
        color: #000;
    }
    .lab-header-patient-inner .val {
        text-align: left;
    }
    .lab-header-patient-inner .patient-name-lbl {
        font-size: 10px;
        padding: 1px 0 2px;
    }
    .lab-qr-img {
        width: 58px;
        height: 58px;
        display: block;
        margin-left: auto;
    }
    .lab-header-address {
        text-align: center;
        font-size: 7.5px;
        line-height: 1.25;
        color: #333;
        margin: 2px 0 6px;
        padding-bottom: 4px;
        border-bottom: 1px solid #000;
    }

    /* IDC-style compact pathology results */
    .pathology-report-section {
        margin-bottom: 10px;
        font-family: Arial, Helvetica, DejaVu Sans, sans-serif;
    }
    .pathology-report-meta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4px;
        font-size: 10px;
    }
    .pathology-report-meta td {
        padding: 0 0 4px;
        vertical-align: bottom;
        border: none;
    }
    .pathology-report-meta .meta-left { text-align: left; }
    .pathology-report-meta .meta-center { text-align: center; font-weight: 700; }
    .pathology-report-meta .meta-right { text-align: right; }

    .pathology-report-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
        table-layout: fixed;
    }
    .pathology-report-table th,
    .pathology-report-table td {
        border: none;
        padding: 2px 4px;
        vertical-align: top;
        font-size: 11px;
        line-height: 1.25;
        text-align: left;
    }
    .pathology-report-table thead th {
        font-weight: 700;
        font-size: 11px;
        padding-bottom: 3px;
        border-bottom: 1px solid #000;
        background: transparent;
        color: #000;
        text-transform: none;
        letter-spacing: 0;
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
        width: 42%;
    }
    .pathology-report-table .col-result {
        width: 14%;
        font-weight: 400;
    }
    .pathology-report-table .col-ref {
        width: 44%;
    }
    .pathology-report-table .abnormal,
    .pathology-report-table .abnormal .result-value {
        color: #000;
        font-weight: 700;
    }
    .ref-inner {
        width: 100%;
        border-collapse: collapse;
    }
    .ref-inner td {
        border: none;
        padding: 0;
        vertical-align: top;
        font-size: 11px;
        line-height: 1.25;
    }
    .ref-inner .ref-text {
        text-align: left;
        padding-right: 6px;
    }
    .ref-inner .ref-unit {
        text-align: right;
        white-space: nowrap;
        font-style: italic;
        width: 1%;
    }
    .flag-icon { font-size: 9px; margin-right: 2px; }
    .flag-high { color: #c00; }
    .flag-low { color: #c60; }
    .flag-normal { display: none; }

    .test-head-bar {
        width: 100%;
        background: #e0e0e0;
        border-collapse: collapse;
        margin-bottom: 2px;
    }
    .test-head-bar td {
        padding: 3px 6px;
        font-size: 11px;
        vertical-align: middle;
    }
    .test-head-bar .bar-name {
        font-weight: 700;
        text-align: left;
    }
    .test-head-bar .bar-meta {
        text-align: right;
        font-size: 10px;
        line-height: 1.2;
        white-space: nowrap;
    }
    .test-name-line {
        font-size: 11px;
        font-weight: 700;
        color: #000;
        margin: 0;
        padding: 2px 6px 1px;
    }
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
        padding-bottom: 40px;
    }
    .report-page-main.has-remarks-page {
        page-break-after: always;
    }
    .report-remarks-page {
        page-break-before: always;
        padding-top: 10px;
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
        margin-top: 16px;
        padding-top: 6px;
        border-top: 1px solid #ccc;
        display: flex;
        justify-content: space-between;
        font-size: 9px;
        color: #333;
    }
