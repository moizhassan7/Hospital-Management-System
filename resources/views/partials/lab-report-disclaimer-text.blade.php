@php
    $boldPart = trim((string) config(
        'hospital.report_footer_disclaimer_bold',
        'System Generated and Verified Report. No Signature needed. (Not Valid for the Court of Law)'
    ));
    $fullDisclaimer = trim((string) ($disclaimer ?? config('hospital.report_footer_disclaimer', '')));
    $introPart = trim(str_replace($boldPart, '', $fullDisclaimer));
@endphp
@if($introPart !== ''){{ $introPart }} @endif<strong>{{ $boldPart }}</strong>
