<?php

return [

    'name' => env('HOSPITAL_NAME', 'Madina Medical Complex, Sargodha'),

    'short_name' => env('HOSPITAL_SHORT_NAME', 'Madina Medical Complex'),

    'city' => env('HOSPITAL_CITY', 'Sargodha'),

    'tagline' => env('HOSPITAL_TAGLINE', 'Quality Healthcare & Diagnostics'),

    'logo' => env('HOSPITAL_LOGO', 'images/madina-medical-logo.png'),

    'address' => env('HOSPITAL_ADDRESS', ''),

    'phone' => env('HOSPITAL_PHONE', ''),

    'email' => env('HOSPITAL_EMAIL', ''),

    /*
    | Disclaimer printed in pathology report footer (between / beside doctors).
    */
    'report_footer_disclaimer' => env(
        'HOSPITAL_REPORT_FOOTER_DISCLAIMER',
        'For diagnostic purposes, lab results should be correlate with clinical findings of patient. System Generated and Verified Report. No Signature needed. (Not Valid for the Court of Law)'
    ),

    'report_footer_disclaimer_bold' => env(
        'HOSPITAL_REPORT_FOOTER_DISCLAIMER_BOLD',
        'System Generated and Verified Report. No Signature needed. (Not Valid for the Court of Law)'
    ),

    /*
    | When true, only Pathology Lab is shown in navigation (desktop sync workflow).
    */
    'pathology_only' => env('PATHOLOGY_ONLY', true),

    /*
    | LAN access — dusre computer se same Wi‑Fi/LAN par site kholne ke liye.
    | Host PC ka IPv4 (ipconfig se) yahan likhein.
    */
    'lan_host' => env('LAN_HOST'),
    'lan_port' => (int) env('LAN_PORT', 8080),

    /*
    | Barcode label printer — 2" wide × 1" tall (thermal).
    */
    'label' => [
        'width_in' => (float) env('LABEL_WIDTH_IN', 2),
        'height_in' => (float) env('LABEL_HEIGHT_IN', 1),
        'width_mm' => (float) env('LABEL_WIDTH_MM', 50.8),
        'height_mm' => (float) env('LABEL_HEIGHT_MM', 25.4),
        'dpi' => (int) env('LABEL_DPI', 203),
    ],

];
