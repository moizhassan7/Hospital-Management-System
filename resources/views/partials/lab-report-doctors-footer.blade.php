@php
    $doctors = $labReportDoctors ?? app(\App\Services\HospitalBrandingService::class)->activeReportDoctors();
@endphp

@if($doctors->isNotEmpty())
    @if(!empty($pdf))
        <table style="width:100%; margin-top:20px; border-top:1px solid #c5d0de; padding-top:12px;">
            <tr>
                @foreach($doctors as $doctor)
                    <td style="width:{{ floor(100 / max($doctors->count(), 1)) }}%; vertical-align:top; padding:8px 10px; text-align:center;">
                        <div style="border-top:1px solid #004a99; margin-top:28px; padding-top:6px; font-size:10px;">
                            <strong style="color:#004a99; font-size:11px;">{{ $doctor->name }}</strong><br>
                            @if($doctor->designation)
                                <span style="color:#475569;">{{ $doctor->designation }}</span><br>
                            @endif
                            @if($doctor->qualifications)
                                <span style="color:#64748b; font-size:9px;">{{ $doctor->qualifications }}</span><br>
                            @endif
                            @if($doctor->phone)
                                <span style="color:#64748b; font-size:9px;">{{ $doctor->phone }}</span>
                            @endif
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    @else
        <div class="lab-report-doctors-footer" style="margin-top:24px; padding-top:16px; border-top:1px solid #c5d0de;">
            <div style="display:flex; flex-wrap:wrap; gap:24px; justify-content:space-around;">
                @foreach($doctors as $doctor)
                    <div style="text-align:center; min-width:160px; flex:1;">
                        <div style="border-top:2px solid #004a99; margin-top:40px; padding-top:8px;">
                            <strong style="color:#004a99; font-size:13px;">{{ $doctor->name }}</strong>
                            @if($doctor->designation)
                                <div style="font-size:11px; color:#475569; margin-top:2px;">{{ $doctor->designation }}</div>
                            @endif
                            @if($doctor->qualifications)
                                <div style="font-size:10px; color:#64748b;">{{ $doctor->qualifications }}</div>
                            @endif
                            @if($doctor->phone)
                                <div style="font-size:10px; color:#64748b;">{{ $doctor->phone }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
