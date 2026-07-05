@php
    $city = config('hospital.city', '');
    $reportTestMeta = isset($test)
        ? collect($labPatient->getSelectedTestsArray())->firstWhere('id', $test->id)
        : null;
    $registrationAt = $labPatient->created_at;
    $reportingAt = ! empty($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'] ?? null)
        ? \Carbon\Carbon::parse($reportTestMeta['result_reported_at'] ?? $reportTestMeta['result_completed_at'])
        : now();
    $refNo = $labPatient->lab_registration_no ?? $labPatient->desktop_invoice ?? '';
    $mrNo = $labPatient->mr_no ?? '';
    $ageLabel = trim((string) ($labPatient->age ?? ''));
    if ($ageLabel !== '' && ! str_contains(strtolower($ageLabel), 'year')) {
        $ageLabel .= ' Year(s)';
    }
    $genderLabel = ucfirst(strtolower((string) ($labPatient->gender ?? '')));
    $reference = trim((string) ($labPatient->refer_by_doctor_name ?? ''));
    $reference = $reference !== '' ? strtoupper($reference) : 'SELF';
    $consultant = $reference;
    $printedBy = trim((string) ($reportEnteredBy ?? ''));
    if ($printedBy === '' && auth()->check()) {
        $printedBy = auth()->user()->name ?? '';
    }
    $formatDateTime = fn ($dt) => $dt->format('d-m-Y H:i') . ($city ? ' ' . $city : '');
@endphp

<div class="letterhead-zone">
    @if(!empty($qrCodeDataUri))
        <img src="{{ $qrCodeDataUri }}" alt="QR" class="letterhead-qr">
    @endif
</div>

<table class="patient-info-header">
    <tr>
        <td class="patient-info-left">
            <div class="info-line"><span class="info-label">Patient Name:</span> <span class="info-value patient-name-value">{{ strtoupper($labPatient->patient_name) }}</span></div>
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
            <div class="info-line"><span class="info-label">CNIC :</span></div>
            <div class="info-line">
                <span class="info-label">Phone :</span>
                <span class="info-value">{{ $labPatient->contact_no ?: '—' }}</span>
            </div>
        </td>
        <td class="patient-info-right">
            <div class="info-line"><span class="info-label">Registration :</span> <span class="info-value">{{ $formatDateTime($registrationAt) }}</span></div>
            <div class="info-line"><span class="info-label">Reporting :</span> <span class="info-value">{{ $formatDateTime($reportingAt) }}</span></div>
            <div class="info-line"><span class="info-label">Reference :</span> <span class="info-value">{{ $reference }}</span></div>
            <div class="info-line"><span class="info-label">Consultant :</span> <span class="info-value">{{ $consultant }}</span></div>
            <div class="info-line"><span class="info-label">Printed By :</span> <span class="info-value">{{ $printedBy ?: '—' }}</span></div>
        </td>
    </tr>
</table>
<div class="patient-info-divider"></div>
