@php
    $city = config('hospital.city', '');
    $reportTestMeta = isset($test)
        ? collect($labPatient->getSelectedTestsArray())->firstWhere('id', $test->id)
        : null;
    $registrationAt = $labPatient->created_at;
    $reportingAt = !empty($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'] ?? null)
        ? \Carbon\Carbon::parse($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'])
        : now();
    $refNo = $labPatient->lab_registration_no ?? $labPatient->desktop_invoice ?? '';
    $mrNo = $labPatient->mr_no ?? '';
    $ageLabel = trim((string) ($labPatient->age ?? ''));
    if ($ageLabel !== '' && !str_contains(strtolower($ageLabel), 'year')) {
        $ageLabel .= ' Year(s)';
    }
    $genderLabel = ucfirst(strtolower((string) ($labPatient->gender ?? '')));
    $consultant = $labPatient->getConsultantLabel();
    $printedBy = trim((string) ($reportEnteredBy ?? ''));
    if ($printedBy === '' && auth()->check()) {
        $printedBy = auth()->user()->name ?? '';
    }
    $formatDateTime = fn($dt) => $dt->format('d-m-Y H:i');

    $branding = app(\App\Services\HospitalBrandingService::class);
    $isPdf = $pdf ?? false;

    $mainLabName = strtoupper(trim((string) ($branding->get('name') ?? config('hospital.name', 'Hospital'))));
    $headerSubtitle = $labPatient->resolveReportHeaderSubtitle($mainLabName);
    $labName = $labPatient->resolveReportSiteLabel();
    $logoPath = $branding->resolveImagePath($branding->get('logo'));
    $phcLogoPath = public_path('images/punjab-healthcare-commission-phc-logo-2F34F17F99-seeklogo.com.png');

    $headerPhone = '';
    if (auth()->check()) {
        $authUser = auth()->user();
        $authUser->loadMissing('collectionCenter');
        if ($authUser->collectionCenter?->phone) {
            $headerPhone = trim((string) $authUser->collectionCenter->phone);
        } elseif ($authUser->isMainLabScope()) {
            $headerPhone = trim((string) ($branding->get('phone') ?? ''));
        }
    }
    if ($headerPhone === '') {
        $headerPhone = trim((string) ($branding->get('phone') ?? ''));
    }

    $withHeader = request('with_header', 1);

    // Helper for base64 images so they render correctly in both Browser and PDF
    $logoBase64 = '';
    if ($logoPath && file_exists($logoPath)) {
        $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath));
    }

    $phcLogoBase64 = '';
    if ($phcLogoPath && file_exists($phcLogoPath)) {
        $phcLogoBase64 = 'data:image/' . pathinfo($phcLogoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($phcLogoPath));
    }
@endphp

<div class="letterhead-zone"
    style="margin-bottom: 0; padding-bottom: 5px; @if($withHeader) height: auto; display: block; margin-top: 20px; @else height: 1.5in; display: block; @endif">
    @if($withHeader)
        <table class="letterhead-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            <tr>
                <td style="width: 15%; vertical-align: middle; text-align: left; padding: 0;">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Lab Logo" style="width: 80px; height: 80px; object-fit: contain;">
                    @endif
                </td>
                <td style="width: 2%; vertical-align: middle; text-align: center;">
                    <div style="border-left: 2px solid #000; height: 75px; margin: 0 auto;"></div>
                </td>
                <td style="width: 65%; vertical-align: middle; text-align: left; padding-left: 10px;">
                    <div
                        style="font-family: 'Times New Roman', Times, DejaVu Serif, serif; font-size: 34px; font-weight: bold; letter-spacing: 0.02em; color: #000; line-height: 1.05; text-transform: uppercase;">
                        {{ $mainLabName }}
                    </div>
                    <div
                        style="font-family: DejaVu Sans, Arial, sans-serif; font-size: 14px; font-weight: bold; letter-spacing: 0.02em; color: #000; text-transform: uppercase; margin-top: 5px;">
                        {{ $labName }}
                    </div>
                    @if($headerPhone !== '')
                        <div
                            style="font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #000; margin-top: 5px; letter-spacing: 0.02em; font-weight: bold;">
                            Ph: {{ $headerPhone }}
                        </div>
                    @endif
                </td>
                <td style="width: 18%; vertical-align: middle; text-align: right; padding: 0;">
                    @if($phcLogoBase64)
                        <img src="{{ $phcLogoBase64 }}" alt="Punjab Healthcare Commission"
                            style="width: 80px; height: 80px; object-fit: contain;">
                    @endif
                </td>
            </tr>
        </table>
    @endif
</div>

<table style="width: 100%; border-collapse: collapse; margin: 0 0 4px; font-size: 11.5px; line-height: 1.45;">
    <tr>
        <td style="vertical-align: top; text-align: left; width: 40%; padding: 0;">
            <div class="info-line"><span class="info-label">Patient Name:</span> <span
                    class="info-value patient-name-value">{{ strtoupper($labPatient->patient_name) }}</span></div>
            <div class="info-line">
                <span class="info-label">Age/Sex:</span>
                <span class="info-value">{{ $ageLabel ?: '—' }} / {{ $genderLabel ?: '—' }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">MR. No. :</span>
                <span class="info-value">{{ $mrNo ?: '—' }}</span>
                <span class="info-label info-inline">Ref #:</span>
                <span class="info-value">{{ $refNo ?: '—' }}</span>
            </div>
            <div class="info-line">
                <span class="info-label">Phone :</span>
                <span class="info-value">{{ $labPatient->contact_no ?: '—' }}</span>
            </div>
            <div class="info-line"><span class="info-label">Consultant :</span> <span
                    class="info-value">{{ $consultant }}</span></div>
        </td>
        <td style="vertical-align: top; text-align: center; width: 40%; padding: 0 12px;">
            <div class="info-line" style="margin-bottom: 4px;">
                <span class="info-label">Registration :</span> <span
                    class="info-value">{{ $formatDateTime($registrationAt) }}</span>
                <div class="info-value" style="margin-top: 1px; font-size: 11px;">({{ $labName }})</div>
            </div>
            <div class="info-line" style="margin-bottom: 4px;">
                <span class="info-label">Reporting :</span> <span
                    class="info-value">{{ $formatDateTime($reportingAt) }}</span>
                <div class="info-value" style="margin-top: 1px; font-size: 11px;">({{ $labName }})</div>
            </div>
            <div class="info-line"><span class="info-label">Printed By :</span> <span
                    class="info-value">{{ $printedBy ?: '—' }}</span></div>
        </td>
        <td style="vertical-align: top; text-align: right; width: 20%; padding: 0;">
            @if(!empty($qrCodeDataUri))
                <img src="{{ $qrCodeDataUri }}" alt="QR" class="patient-qr"
                    style="width: 115px; height: 115px; display: inline-block;">
            @endif
        </td>
    </tr>
</table>
<div class="patient-info-divider"></div>