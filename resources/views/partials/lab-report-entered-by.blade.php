@if(!empty($reportEnteredBy))
    @if(!empty($pdf))
        <table style="width:100%; margin-top:16px;">
            <tr>
                <td style="text-align:right; font-size:11.5px; color:#475569;">
                    <strong style="color:#004a99;">Report prepared by:</strong> {{ $reportEnteredBy }}
                </td>
            </tr>
        </table>
    @else
        <div style="margin-top:16px; text-align:right; font-size:12.5px; color:#475569;">
            <strong style="color:#004a99;">Report prepared by:</strong> {{ $reportEnteredBy }}
        </div>
    @endif
@endif
