@php
    $isPdf = $pdf ?? false;
    $branding = app(\App\Services\HospitalBrandingService::class);
    $shortName = config('hospital.short_name');
    $tagline = config('hospital.tagline', 'Pathology Laboratory');
    $address = config('hospital.address');
    $phone = config('hospital.phone');
    $logoUrl = $branding->logoUrl();
    $logoPath = $branding->logoPath();
    $hasLogo = $isPdf ? file_exists($logoPath) : true;
    $logoSrc = $isPdf ? $logoPath : $logoUrl;

    $mrnPin = trim(($labPatient->mr_no ?? '') . ' / ' . ($labPatient->lab_registration_no ?? $labPatient->desktop_invoice ?? ''), ' /');
    $addressLine = collect([$address, $phone ? 'UAN: ' . $phone : null])->filter()->implode(', ');
@endphp

<table class="lab-report-header">
    <tr>
        <td class="lab-header-brand">
            <table class="lab-header-brand-inner">
                <tr>
                    @if($hasLogo)
                        <td class="lab-header-logo">
                            <img src="{{ $logoSrc }}" alt="{{ $shortName }}">
                        </td>
                    @endif
                    <td class="lab-header-titles">
                        <div class="lab-header-name">{{ $shortName }}</div>
                        <div class="lab-header-tagline">{{ strtoupper($tagline) }}</div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="lab-header-patient">
            <table class="lab-header-patient-inner">
                <tr>
                    <td class="lbl">MRN/PIN:</td>
                    <td class="val"><strong>{{ $mrnPin ?: 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="lbl patient-name-lbl" colspan="2"><strong>{{ $labPatient->patient_name }}</strong></td>
                </tr>
                <tr>
                    <td class="lbl">Age/Gender:</td>
                    <td class="val"><strong>{{ $labPatient->age }} / {{ $labPatient->gender }}</strong></td>
                </tr>
                @if($labPatient->contact_no)
                <tr>
                    <td class="lbl">Contact:</td>
                    <td class="val"><strong>{{ $labPatient->contact_no }}</strong></td>
                </tr>
                @endif
                <tr>
                    <td class="lbl">Ref.By:</td>
                    <td class="val"><strong>{{ $labPatient->refer_by_doctor_name ?: 'Self' }}</strong></td>
                </tr>
            </table>
        </td>
        <td class="lab-header-qr">
            @if(!empty($qrCodeDataUri))
                <img src="{{ $qrCodeDataUri }}" alt="QR" class="lab-qr-img">
            @endif
        </td>
    </tr>
</table>
@if($addressLine)
    <div class="lab-header-address">{{ $addressLine }}</div>
@endif
