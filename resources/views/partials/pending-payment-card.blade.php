@if(($patient->due_amount ?? 0) > 0.009)
    <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/10 border-2 border-amber-400/60 rounded-2xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center font-bold shrink-0 shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h4 class="font-bold text-amber-900 text-base">Payment Pending</h4>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-200 text-amber-900 uppercase tracking-wider">Due Amount</span>
                </div>
                <p class="text-xs text-amber-800 mt-0.5">
                    Remaining Balance: <strong class="text-base text-amber-900 font-black">PKR {{ number_format($patient->due_amount, 2) }}</strong>
                    <span class="text-amber-700 ml-2 hidden md:inline">(Total: PKR {{ number_format($patient->grand_total, 2) }} | Paid: PKR {{ number_format($patient->paid_amount, 2) }})</span>
                </p>
            </div>
        </div>
        <button type="button"
            data-collect-due
            data-due-patient-id="{{ $patient->id }}"
            data-due-patient-name="{{ $patient->patient_name }}"
            data-due-lab-reg="{{ $patient->lab_registration_no ?? 'N/A' }}"
            data-due-mr-no="{{ $patient->mr_no ?? 'N/A' }}"
            data-due-grand-total="{{ $patient->grand_total }}"
            data-due-paid-amount="{{ $patient->paid_amount }}"
            data-due-amount="{{ $patient->due_amount }}"
            class="bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-bold py-2.5 px-5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span>Collect Payment Now</span>
        </button>
    </div>
@endif
