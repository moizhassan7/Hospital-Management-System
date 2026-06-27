@php
    $fmtDate = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d-M-Y') : '—';
    $fmtDateTime = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d-M-Y h:i A') : '—';
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reg Date</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">MR No</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Slip</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Age/Sex</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mobile</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ward/Bed</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Consultant</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Discharge</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($records as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-gray-500">{{ $records->firstItem() + $loop->index }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmtDate($row->RegistrationDate) }}</td>
                    <td class="px-3 py-2 font-medium">{{ $row->Mr_No ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->SlipNo ?? '—' }}</td>
                    <td class="px-3 py-2 font-medium text-gray-900">{{ $row->PatientName ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->Age ?? '—' }} / {{ $row->Gender ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->MobileNo ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->WardNo ?? '—' }} / {{ $row->BedNo ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->Consultent ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $row->Total ?? '—' }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmtDate($row->Discharge_Date) }}</td>
                    <td class="px-3 py-2 text-center">
                        @if($row->Discharge_Date)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700">Discharged</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Admitted</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <a href="{{ route('ipd.history.show', $row->Ipd_Id) }}" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
