<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPD Slip - {{ $appointment->appointment_number }}</title>
    <style>
        /* Thermal Printer 80mm Settings */
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 10px;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            width: 75mm;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .header { margin-bottom: 15px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .logo { max-width: 60px; max-height: 60px; margin-bottom: 5px; }
        .hospital-name { font-size: 16px; font-weight: bold; margin: 0; text-transform: uppercase; }
        .hospital-address { font-size: 10px; margin: 2px 0 0 0; }
        
        .title { font-size: 14px; font-weight: bold; margin: 10px 0; text-decoration: underline; }
        
        .meta-info { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        
        .token-area { text-align: center; margin: 15px 0; border: 2px solid #000; padding: 10px; border-radius: 5px; }
        .token-title { font-size: 14px; font-weight: bold; margin: 0; }
        .token-number { font-size: 48px; font-weight: 900; margin: 5px 0 0 0; line-height: 1; }
        
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .details-table td { padding: 4px 0; vertical-align: top; font-size: 12px; }
        .details-table td:first-child { font-weight: bold; width: 35%; }
        
        .fee-area { border-top: 1px dashed #000; padding-top: 10px; margin-bottom: 15px; font-size: 14px; }
        .fee-row { display: flex; justify-content: space-between; font-weight: bold; }
        
        .footer { text-align: center; font-size: 9px; color: #555; border-top: 1px solid #ddd; padding-top: 5px; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="setTimeout(function(){ window.print(); window.close(); }, 500);">
    
    <div class="header text-center">
        @if(get_setting('logo'))
            <img src="{{ asset('storage/' . get_setting('logo')) }}" alt="Logo" class="logo">
        @endif
        <h1 class="hospital-name">{{ get_setting('hospital_name', 'Hospital Management System') }}</h1>
        <p class="hospital-address">{{ get_setting('address', '') }}</p>
        <div class="title">OPD Consultation Slip</div>
    </div>
    
    <div class="meta-info">
        <div><strong>Date:</strong> {{ date('d-M-Y', strtotime($appointment->appointment_date)) }}</div>
        <div><strong>Time:</strong> {{ date('h:i A', strtotime($appointment->appointment_time)) }}</div>
    </div>
    
    <div class="token-area">
        <p class="token-title">Token Number</p>
        <p class="token-number">{{ $appointment->token_number ?? '-' }}</p>
    </div>
    
    <table class="details-table">
        <tr>
            <td>Appt No:</td>
            <td>{{ $appointment->appointment_number }}</td>
        </tr>
        <tr>
            <td>Patient:</td>
            <td>{{ $appointment->patient_name }} ({{ $appointment->gender }}, {{ $appointment->age }}y)</td>
        </tr>
        <tr>
            <td>MR No:</td>
            <td>{{ $appointment->mr_number }}</td>
        </tr>
        <tr>
            <td>Doctor:</td>
            <td>{{ $appointment->doctor_name }}</td>
        </tr>
        @if($appointment->referred_by)
        <tr>
            <td>Referred By:</td>
            <td>{{ $appointment->referred_by }}</td>
        </tr>
        @endif
    </table>
    
    <div class="fee-area">
        <div class="fee-row">
            <span>Consultation Fee:</span>
            <span>{{ get_setting('currency_symbol', 'Rs') }} {{ number_format($appointment->total_amount, 2) }}</span>
        </div>
    </div>
    
    <div class="footer">
        <p>Software by Switch2Itech</p>
        <p>Printed: {{ date('d-M-Y H:i:s') }}</p>
    </div>

</body>
</html>
