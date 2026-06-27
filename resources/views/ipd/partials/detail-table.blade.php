@php
    $fmtDate = fn ($val) => $val ? \Carbon\Carbon::parse($val)->format('d-M-Y') : '—';
@endphp

<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reg Date</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Slip ID</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">MR No</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ward/Bed</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Discharge</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($records as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-gray-500">{{ $records->firstItem() + $loop->index }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmtDate($row->Reg_date) }}</td>
                    <td class="px-3 py-2 font-medium">{{ $row->Slip_ID ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->MR_No ?? '—' }}</td>
                    <td class="px-3 py-2 font-medium text-gray-900">{{ $row->PatientName ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->Ward_No ?? '—' }} / {{ $row->Bed_No ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->Dr_Name ?? '—' }}</td>
                    <td class="px-3 py-2">{{ $row->Dr_Dept ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $row->Total ?? '—' }}</td>
                    <td class="px-3 py-2 text-right">{{ $row->Remaining ?? '—' }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ $fmtDate($row->Disharge_date) }}</td>
                    <td class="px-3 py-2 text-center">
                        @if($row->Active == 1)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Yes</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">No</span>
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <a href="{{ route('ipd.detail.show', $row->Slip_ID) }}" class="text-blue-600 hover:text-blue-800 font-medium">View</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
