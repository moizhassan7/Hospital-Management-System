<!-- Collect Due Amount Modal -->
<div id="hms-collect-due-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="hms-due-modal-title">
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-100 transition-all transform">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold leading-tight" id="hms-due-modal-title">Collect Remaining Due</h3>
                    <p class="text-xs text-amber-100">Patient has pending balance</p>
                </div>
            </div>
            <button type="button" id="hms-due-modal-close" class="text-white/80 hover:text-white p-1 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            <!-- Patient Banner -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-1">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500 text-xs font-semibold uppercase tracking-wider">Patient Name</span>
                    <span class="font-bold text-slate-900" id="hms-due-patient-name">—</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-600">
                    <span>Lab Reg #: <strong class="text-slate-800" id="hms-due-lab-reg">—</strong></span>
                    <span>MR #: <strong class="text-slate-800" id="hms-due-mr-no">—</strong></span>
                </div>
            </div>

            <!-- Financial Details -->
            <div class="space-y-2 text-sm border-t border-b border-slate-100 py-3">
                <div class="flex justify-between text-slate-600">
                    <span>Grand Total:</span>
                    <span class="font-semibold text-slate-800" id="hms-due-grand-total">PKR 0.00</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Already Paid:</span>
                    <span class="font-semibold text-teal-600" id="hms-due-paid-amount">PKR 0.00</span>
                </div>
                <div class="flex justify-between text-base font-extrabold text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                    <span>Remaining Due:</span>
                    <span id="hms-due-remaining-amount">PKR 0.00</span>
                </div>
            </div>

            <!-- Collection Input -->
            <div>
                <label for="hms-due-collect-amount" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Amount to Collect (PKR)
                </label>
                <div class="relative rounded-lg shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-semibold text-sm">
                        PKR
                    </div>
                    <input type="number" step="0.01" min="0.01" id="hms-due-collect-amount"
                        class="w-full pl-12 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 font-bold text-slate-900 text-lg text-right"
                        placeholder="0.00">
                </div>
                <p id="hms-due-error-msg" class="text-xs text-red-600 mt-1 hidden font-medium"></p>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex flex-col gap-2">
            <button type="button" id="hms-due-btn-collect-print"
                class="w-full bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-bold py-2.5 px-4 rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Collect &amp; Print Report</span>
            </button>
            <button type="button" id="hms-due-btn-cancel"
                class="w-full bg-white border border-slate-300 hover:bg-slate-100 text-slate-600 font-semibold py-2 px-3 rounded-xl text-xs transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const modal = document.getElementById('hms-collect-due-modal');
    if (!modal) return;

    const closeBtn = document.getElementById('hms-due-modal-close');
    const cancelBtn = document.getElementById('hms-due-btn-cancel');
    const collectPrintBtn = document.getElementById('hms-due-btn-collect-print');
    const skipPrintBtn = document.getElementById('hms-due-btn-skip-print');
    
    const nameEl = document.getElementById('hms-due-patient-name');
    const labRegEl = document.getElementById('hms-due-lab-reg');
    const mrNoEl = document.getElementById('hms-due-mr-no');
    const grandTotalEl = document.getElementById('hms-due-grand-total');
    const paidAmountEl = document.getElementById('hms-due-paid-amount');
    const remainingEl = document.getElementById('hms-due-remaining-amount');
    const collectInput = document.getElementById('hms-due-collect-amount');
    const errorMsg = document.getElementById('hms-due-error-msg');

    let currentPatientId = null;
    let pendingPrintUrl = null;
    let pendingPrintCallback = null;

    function formatMoney(amount) {
        return 'PKR ' + (parseFloat(amount) || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showModal(data, printUrl, callback) {
        currentPatientId = data.patientId;
        pendingPrintUrl = printUrl;
        pendingPrintCallback = callback;

        nameEl.textContent = data.patientName || '—';
        labRegEl.textContent = data.labReg || '—';
        mrNoEl.textContent = data.mrNo || '—';
        grandTotalEl.textContent = formatMoney(data.grandTotal || 0);
        paidAmountEl.textContent = formatMoney(data.paidAmount || 0);
        remainingEl.textContent = formatMoney(data.dueAmount || 0);

        collectInput.value = (parseFloat(data.dueAmount) || 0).toFixed(2);
        collectInput.max = data.dueAmount;
        errorMsg.classList.add('hidden');

        modal.classList.remove('hidden');
    }

    function hideModal() {
        modal.classList.add('hidden');
        currentPatientId = null;
        pendingPrintUrl = null;
        pendingPrintCallback = null;
    }

    function executePrint() {
        if (typeof pendingPrintCallback === 'function') {
            pendingPrintCallback();
        } else if (pendingPrintUrl) {
            if (window.hmsInlinePrint) {
                window.hmsInlinePrint(pendingPrintUrl);
            } else {
                window.open(pendingPrintUrl, '_blank');
            }
        }
        hideModal();
    }

    closeBtn?.addEventListener('click', hideModal);
    cancelBtn?.addEventListener('click', hideModal);

    collectPrintBtn?.addEventListener('click', function () {
        const amount = parseFloat(collectInput.value);
        if (isNaN(amount) || amount <= 0) {
            errorMsg.textContent = 'Please enter a valid collection amount greater than 0.';
            errorMsg.classList.remove('hidden');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        collectPrintBtn.disabled = true;
        collectPrintBtn.style.opacity = '0.7';

        fetch(`/pathology/bookings/${currentPatientId}/collect-due`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ amount: amount })
        })
        .then(res => res.json())
        .then(data => {
            collectPrintBtn.disabled = false;
            collectPrintBtn.style.opacity = '1';
            if (data.success) {
                executePrint();
            } else {
                errorMsg.textContent = data.message || 'Failed to collect due amount.';
                errorMsg.classList.remove('hidden');
            }
        })
        .catch(err => {
            collectPrintBtn.disabled = false;
            collectPrintBtn.style.opacity = '1';
            errorMsg.textContent = 'Network or server error occurred.';
            errorMsg.classList.remove('hidden');
        });
    });

    // Public trigger function
    window.hmsCheckDueAndPrint = function (data, printUrl, callback) {
        const due = parseFloat(data.dueAmount) || 0;
        if (due > 0.009) {
            showModal(data, printUrl, callback);
        } else {
            if (typeof callback === 'function') {
                callback();
            } else if (printUrl) {
                if (window.hmsInlinePrint) {
                    window.hmsInlinePrint(printUrl);
                } else {
                    window.open(printUrl, '_blank');
                }
            }
        }
    };

    // Attach delegated click listener for elements with data-collect-due
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-collect-due]');
        if (!trigger) return;

        e.preventDefault();

        const data = {
            patientId: trigger.getAttribute('data-due-patient-id'),
            patientName: trigger.getAttribute('data-due-patient-name'),
            labReg: trigger.getAttribute('data-due-lab-reg'),
            mrNo: trigger.getAttribute('data-due-mr-no'),
            grandTotal: trigger.getAttribute('data-due-grand-total'),
            paidAmount: trigger.getAttribute('data-due-paid-amount'),
            dueAmount: trigger.getAttribute('data-due-amount'),
        };

        const printUrl = trigger.getAttribute('href') || trigger.getAttribute('data-hms-print') || trigger.getAttribute('data-print-url');

        window.hmsCheckDueAndPrint(data, printUrl);
    });
})();
</script>
@endpush
