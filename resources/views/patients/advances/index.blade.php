@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-3xl font-bold text-gray-800">Indoor Patient Advances</h2>
</div>

@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">Please fix the following errors:</span>
        <ul class="mt-3 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Active Indoor Patients Table -->
<div class="bg-white shadow-lg rounded-xl overflow-hidden mb-8">
    <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-700">Currently Admitted Patients</h3>
        <div>
            <input type="text" id="searchInput" placeholder="Search MR No or Name..." class="shadow appearance-none border rounded-lg py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal" id="patientsTable">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">MR No / Patient</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Admission Date</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Room/Ward</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-white text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Advances</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-white text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($indoorPatients as $ip)
                    <tr class="patient-row">
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap font-bold patient-mr">{{ $ip->patient->mr_number }}</p>
                            <p class="text-gray-600 whitespace-no-wrap text-xs patient-name">{{ $ip->patient->name }}</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ \Carbon\Carbon::parse($ip->registration_date)->format('d M Y') }}</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-gray-900 whitespace-no-wrap">{{ $ip->room_id ? 'Room '.$ip->room_id : 'Ward '.$ip->ward_id }}</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                            <p class="text-blue-600 font-semibold whitespace-no-wrap">{{ get_setting('currency_symbol', 'Rs') }}{{ number_format($ip->advances->sum('amount'), 2) }}</p>
                            <p class="text-gray-500 text-xs">{{ $ip->advances->count() }} installments</p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                            <button onclick="openAdvancesModal({{ $ip->id }})" class="bg-blue-100 text-blue-700 hover:bg-blue-200 font-semibold py-1 px-3 rounded-lg transition-colors">
                                Manage Advances
                            </button>
                            
                            <!-- Hidden Data -->
                            <div id="data-{{ $ip->id }}" class="hidden">
                                {!! json_encode([
                                    'mr_no' => $ip->patient->mr_number,
                                    'name' => $ip->patient->name,
                                    'advances' => $ip->advances->map(function($a) use($ip) {
                                        return [
                                            'id' => $a->id,
                                            'date' => \Carbon\Carbon::parse($a->date)->format('d M Y'),
                                            'amount' => $a->amount,
                                            'receipt_no' => $a->receipt_no,
                                            'remarks' => $a->remarks,
                                            'added_by' => $a->createdBy ? $a->createdBy->name : '-',
                                            'raw_amount' => $a->amount,
                                            'mr_no' => $ip->patient->mr_number,
                                            'name' => $ip->patient->name
                                        ];
                                    })->toArray()
                                ]) !!}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                            No active indoor patients found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Manage Advances Modal -->
<div id="manageAdvancesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <h3 class="text-2xl font-bold text-gray-800">Advances History</h3>
            <button onclick="document.getElementById('manageAdvancesModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="mb-4 bg-blue-50 p-4 rounded-lg flex justify-between items-center">
            <div>
                <span class="text-gray-600 text-sm">Patient:</span>
                <span id="modalPatientName" class="font-bold text-lg text-blue-900 ml-2"></span>
            </div>
            <div>
                <span class="text-gray-600 text-sm">MR No:</span>
                <span id="modalMrNo" class="font-bold text-lg text-blue-900 ml-2"></span>
            </div>
            <div>
                <span class="text-gray-600 text-sm">Total Advances:</span>
                <span id="modalTotalAdvances" class="font-bold text-lg text-green-700 ml-2"></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Side: Add New Advance -->
            <div class="lg:col-span-1 bg-gray-50 p-4 rounded-lg border border-gray-200 h-fit">
                <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Add New Installment</h4>
                <form action="{{ route('patients.advances.store') }}" method="POST">
                    @csrf
                    <input type="hidden" id="form_mr_no" name="mr_no">
                    
                    <div class="mb-3">
                        <label for="amount" class="block text-gray-700 text-xs font-bold mb-1">Amount ({{ get_setting('currency_symbol', 'Rs') }}):</label>
                        <input type="number" id="amount" name="amount" min="1" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date" class="block text-gray-700 text-xs font-bold mb-1">Date:</label>
                        <input type="date" id="date" name="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div class="mb-3">
                        <label for="receipt_no" class="block text-gray-700 text-xs font-bold mb-1">Receipt No (Optional):</label>
                        <input type="text" id="receipt_no" name="receipt_no" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="remarks" class="block text-gray-700 text-xs font-bold mb-1">Remarks:</label>
                        <textarea id="remarks" name="remarks" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition-colors">
                        Save Advance
                    </button>
                </form>
            </div>

            <!-- Right Side: History -->
            <div class="lg:col-span-2">
                <h4 class="font-bold text-gray-700 mb-3">Installment History</h4>
                <div class="overflow-y-auto max-h-[400px] border border-gray-200 rounded-lg">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b">Date</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b">Amount</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b">Receipt No</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-600 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="advancesHistoryTable" class="bg-white divide-y divide-gray-200">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll('.patient-row');
        
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            rows.forEach(row => {
                const mr = row.querySelector('.patient-mr').textContent.toLowerCase();
                const name = row.querySelector('.patient-name').textContent.toLowerCase();
                if (mr.includes(term) || name.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    const currencySymbol = "{{ get_setting('currency_symbol', 'Rs') }}";
    const deleteRouteBase = "{{ url('patients/advances') }}";
    const csrfToken = "{{ csrf_token() }}";

    function openAdvancesModal(patientId) {
        const dataDiv = document.getElementById(`data-${patientId}`);
        const data = JSON.parse(dataDiv.innerHTML);
        
        document.getElementById('modalPatientName').textContent = data.name;
        document.getElementById('modalMrNo').textContent = data.mr_no;
        document.getElementById('form_mr_no').value = data.mr_no;
        
        const historyTbody = document.getElementById('advancesHistoryTable');
        historyTbody.innerHTML = '';
        
        let total = 0;
        
        if (data.advances && data.advances.length > 0) {
            data.advances.forEach(adv => {
                total += parseFloat(adv.raw_amount);
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap text-gray-800">${adv.date}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-blue-700 font-bold">${currencySymbol}${parseFloat(adv.raw_amount).toFixed(2)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">${adv.receipt_no || '-'}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex space-x-2">
                            <button type="button" onclick='printSpecificReceipt(${JSON.stringify(adv).replace(/'/g, "\\'")})' class="text-gray-500 hover:text-gray-800" title="Print">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </button>
                            <form action="${deleteRouteBase}/${adv.id}" method="POST" onsubmit="return confirm('Are you sure?');" class="inline">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="text-red-500 hover:text-red-700" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                `;
                historyTbody.appendChild(tr);
            });
        } else {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="4" class="px-4 py-6 text-center text-gray-500">No advance history found.</td>`;
            historyTbody.appendChild(tr);
        }
        
        document.getElementById('modalTotalAdvances').textContent = `${currencySymbol}${total.toFixed(2)}`;
        
        document.getElementById('manageAdvancesModal').classList.remove('hidden');
    }

    function printSpecificReceipt(adv) {
        const html = `
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ get_setting('hospital_name', 'Hospital') }}</h1>
                <h2 class="text-xl font-semibold text-gray-700">Advance Payment Receipt</h2>
            </div>
            <div class="mb-6">
                <div class="flex justify-between mb-2 pb-2 border-b">
                    <span class="font-semibold text-gray-700">Date:</span>
                    <span class="text-gray-900">${adv.date}</span>
                </div>
                <div class="flex justify-between mb-2 pb-2 border-b">
                    <span class="font-semibold text-gray-700">Receipt No:</span>
                    <span class="text-gray-900">${adv.receipt_no || '-'}</span>
                </div>
                <div class="flex justify-between mb-2 pb-2 border-b">
                    <span class="font-semibold text-gray-700">Patient Name:</span>
                    <span class="text-gray-900">${adv.name}</span>
                </div>
                <div class="flex justify-between mb-2 pb-2 border-b">
                    <span class="font-semibold text-gray-700">MR Number:</span>
                    <span class="text-gray-900">${adv.mr_no}</span>
                </div>
                <div class="flex justify-between mb-2 pb-2 border-b">
                    <span class="font-semibold text-gray-700">Amount Received:</span>
                    <span class="text-gray-900 font-bold text-lg">${currencySymbol}${parseFloat(adv.raw_amount).toFixed(2)}</span>
                </div>
                <div class="flex justify-between mt-4">
                    <span class="font-semibold text-gray-700">Remarks:</span>
                    <span class="text-gray-900">${adv.remarks || '-'}</span>
                </div>
                <div class="flex justify-between mt-2">
                    <span class="font-semibold text-gray-700">Received By:</span>
                    <span class="text-gray-900">${adv.added_by}</span>
                </div>
            </div>
            <div class="mt-16 flex justify-between">
                <div class="border-t border-gray-400 pt-2 w-48 text-center text-sm font-semibold">
                    Received By Signature
                </div>
                <div class="border-t border-gray-400 pt-2 w-48 text-center text-sm font-semibold">
                    Authorized Signature
                </div>
            </div>
        `;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <title>Advance Receipt - ${adv.mr_no}</title>
                <style>
                    body { font-family: 'Inter', sans-serif; margin: 0; padding: 20mm; font-size: 14px; }
                    h1, h2 { color: #1f2937; margin: 0; }
                    .text-center { text-align: center; }
                    .flex { display: flex; }
                    .justify-between { justify-content: space-between; }
                    .mb-1 { margin-bottom: 0.25rem; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mb-6 { margin-bottom: 1.5rem; }
                    .mb-8 { margin-bottom: 2rem; }
                    .mt-2 { margin-top: 0.5rem; }
                    .mt-4 { margin-top: 1rem; }
                    .mt-16 { margin-top: 4rem; }
                    .pb-2 { padding-bottom: 0.5rem; }
                    .pt-2 { padding-top: 0.5rem; }
                    .border-b { border-bottom: 1px dashed #ccc; }
                    .border-t { border-top: 1px solid #1f2937; }
                    .w-48 { width: 12rem; }
                    .font-bold { font-weight: 700; }
                    .font-semibold { font-weight: 600; }
                    .text-lg { font-size: 1.125rem; }
                    .text-xl { font-size: 1.25rem; }
                    .text-2xl { font-size: 1.5rem; }
                </style>
            </head>
            <body>
                <div style="max-width: 80mm; margin: 0 auto;">
                    ${html}
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
</script>
@endsection
