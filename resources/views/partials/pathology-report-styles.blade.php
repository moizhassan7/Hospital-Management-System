    /* Main results table */
    .pathology-report-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16px;
        table-layout: fixed;
    }
    .pathology-report-table th,
    .pathology-report-table td {
        border: 1px solid #c5d0de;
        padding: 9px 10px;
        vertical-align: middle;
    }
    .pathology-report-table thead th {
        background: #004a99;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-align: center;
    }
    .pathology-report-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }
    .pathology-report-table tbody tr:hover {
        background: #f1f5f9;
    }
    .pathology-report-table .col-sno {
        width: 36px;
        text-align: center;
        color: #64748b;
        font-weight: 600;
    }
    .pathology-report-table .col-test {
        width: 32%;
        text-align: left;
    }
    .pathology-report-table .col-result {
        width: 18%;
        text-align: center;
        font-weight: 700;
        font-size: 13px;
    }
    .pathology-report-table .col-unit {
        width: 14%;
        text-align: center;
        color: #475569;
    }
    .pathology-report-table .col-ref {
        width: 22%;
        text-align: center;
        color: #475569;
        font-size: 11px;
    }
    .pathology-report-table .col-date {
        display: block;
        font-size: 8px;
        font-weight: 400;
        opacity: 0.9;
        margin-top: 2px;
    }
    .pathology-report-table .abnormal {
        color: #dc2626;
        background: #fef2f2 !important;
    }
    .pathology-report-table .abnormal .result-value {
        color: #dc2626;
    }
    .flag-icon { font-size: 10px; margin-right: 3px; }
    .flag-high { color: #dc2626; }
    .flag-low { color: #d97706; }
    .flag-normal { color: #16a34a; font-size: 8px; }

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
        border-bottom: 2px solid #004a99;
        margin-bottom: 18px;
        padding-bottom: 10px;
    }
    .remarks-page-header h3 {
        margin: 0 0 4px;
        color: #004a99;
        font-size: 16px;
        text-transform: uppercase;
    }
    .remarks-subtitle {
        margin: 0;
        font-size: 11px;
        color: #64748b;
    }
    .remark-block {
        border: 1px solid #e2e8f0;
        border-left: 4px solid #004a99;
        border-radius: 6px;
        padding: 12px 14px;
        margin-bottom: 14px;
        background: #fafbfc;
        page-break-inside: avoid;
    }
    .remark-title {
        margin: 0 0 8px;
        color: #004a99;
        font-size: 12px;
        font-weight: 700;
    }
    .remark-result {
        margin: 0 0 8px;
        font-size: 11px;
        color: #334155;
    }
    .remark-body {
        font-size: 11px;
        line-height: 1.55;
        color: #475569;
        white-space: pre-line;
    }
    .test-level-comment {
        border-left-color: #7c3aed;
    }
    .inline-page-footer {
        margin-top: 24px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        color: #94a3b8;
    }