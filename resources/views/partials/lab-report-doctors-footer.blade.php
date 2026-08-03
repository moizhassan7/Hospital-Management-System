@php
    $doctors = $labReportDoctors ?? app(\App\Services\HospitalBrandingService::class)->activeReportDoctors();
    $disclaimer = ''; // Disabled per user request
    $doctorCount = $doctors->count();
    $withHeader = request('with_header', 1);
@endphp

@if($withHeader && ($doctorCount > 0 || $disclaimer !== ''))
    <div class="report-doctors-footer">
        @if($doctorCount >= 3)
            <table class="report-footer-table">
                <tr>
                    @foreach($doctors as $doctor)
                        <td class="report-footer-doctor report-footer-doctor-right" style="width: {{ floor(100 / max($doctorCount, 1)) }}%;">
                            @include('partials.lab-report-doctor-block', ['doctor' => $doctor])
                        </td>
                    @endforeach
                </tr>
            </table>
            @if($disclaimer !== '')
                <div class="report-footer-disclaimer-bottom">@include('partials.lab-report-disclaimer-text', ['disclaimer' => $disclaimer])</div>
            @endif
        @elseif($doctorCount === 2)
            <table class="report-footer-table">
                <tr>
                    <td class="report-footer-disclaimer" style="width: 42%;">
                        @include('partials.lab-report-disclaimer-text', ['disclaimer' => $disclaimer])
                    </td>
                    <td class="report-footer-divider-col" style="width: 1%;"></td>
                    <td class="report-footer-doctor report-footer-doctor-right" style="width: 28%;">
                        @include('partials.lab-report-doctor-block', ['doctor' => $doctors->first()])
                    </td>
                    <td class="report-footer-divider-col" style="width: 1%;"></td>
                    <td class="report-footer-doctor report-footer-doctor-right" style="width: 28%;">
                        @include('partials.lab-report-doctor-block', ['doctor' => $doctors->last()])
                    </td>
                </tr>
            </table>
        @elseif($doctorCount === 1)
            <table class="report-footer-table">
                <tr>
                    <td class="report-footer-disclaimer report-footer-disclaimer-single" style="width: 69%;">
                        @include('partials.lab-report-disclaimer-text', ['disclaimer' => $disclaimer])
                    </td>
                    <td class="report-footer-divider-col" style="width: 1%;"></td>
                    <td class="report-footer-doctor report-footer-doctor-right" style="width: 30%;">
                        @include('partials.lab-report-doctor-block', ['doctor' => $doctors->first()])
                    </td>
                </tr>
            </table>
        @elseif($disclaimer !== '')
            <div class="report-footer-disclaimer-bottom">@include('partials.lab-report-disclaimer-text', ['disclaimer' => $disclaimer])</div>
        @endif
    </div>
@endif

@php
    $branding = app(\App\Services\HospitalBrandingService::class);
    $footerImg = $branding->get('footer_image');
    $isPdf = $pdf ?? false;
    
    $footerPath = null;
    if ($footerImg) {
        $relativePath = str_replace('storage/', '', $footerImg);
        $footerPath = storage_path('app/public/' . $relativePath);
        if (!file_exists($footerPath)) {
            $footerPath = public_path($footerImg);
        }
    }
@endphp
@if($isPdf && $footerPath)
    <div style="position: absolute; bottom: 0; left: 0; right: 0; text-align: center;">
        <img src="{{ $footerPath }}" alt="Footer" style="width: 100%; height: auto; object-fit: contain;">
    </div>
@endif
