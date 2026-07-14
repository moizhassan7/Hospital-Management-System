<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Report - {{ $labPatient->patient_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 0 15mm 15mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, 'Segoe UI', sans-serif;
            font-size: 12.5px;
            color: #000;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        @include('partials.pathology-report-styles')

        .descriptive-content {
            padding: 4px 0;
        }
        .descriptive-item {
            margin-bottom: 8px;
            padding: 4px 0;
            border-bottom: 1px dotted #b5b5b5;
        }
        .descriptive-item b {
            display: block;
            margin-bottom: 2px;
            font-size: 12.5px;
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
            }
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #004a99;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }
    </style>
</head>
<body>
    @unless(!empty($isOnlineView))
    <a href="javascript:window.print()" class="btn-print no-print">Print Report</a>
    @endunless

    @include('partials.pathology-report-single', compact(
        'labPatient', 'test', 'historyResults', 'testImages', 'qrCodeDataUri', 'hasRemarksPage', 'hasTroponinInterpretation', 'hormoneReferenceType', 'testComment', 'reportEnteredBy'
    ))

    <script>
        // Auto print window
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
