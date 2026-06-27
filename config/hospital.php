<?php

return [

    'name' => env('HOSPITAL_NAME', 'Madina Medical Complex, Sargodha'),

    'short_name' => env('HOSPITAL_SHORT_NAME', 'Madina Medical Complex'),

    'city' => env('HOSPITAL_CITY', 'Sargodha'),

    'tagline' => env('HOSPITAL_TAGLINE', 'Quality Healthcare & Diagnostics'),

    'logo' => env('HOSPITAL_LOGO', 'images/madina-medical-logo.png'),

    /*
    | When true, only Pathology Lab is shown in navigation (desktop sync workflow).
    */
    'pathology_only' => env('PATHOLOGY_ONLY', true),

];
