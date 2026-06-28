<div id="abnormal-alert-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4" role="dialog" aria-modal="true">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 border-t-4 border-amber-500">
        <div class="flex items-start gap-3 mb-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 text-xl font-bold">!</div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Abnormal Value</h3>
                <p class="text-sm text-gray-600 mt-1">This report contains the following abnormal result(s):</p>
                <ul id="abnormal-modal-values" class="text-sm text-gray-700 mt-2 space-y-1 list-disc list-inside max-h-48 overflow-y-auto"></ul>
            </div>
        </div>
        <p class="text-xs text-gray-500 mb-4">This result is outside the normal reference range. Please confirm before saving.</p>
        <div class="flex justify-end gap-3">
            <button type="button" id="abnormal-modal-cancel" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium">Go Back</button>
            <button type="button" id="abnormal-modal-confirm" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold">Yes, I Know</button>
        </div>
    </div>
</div>

<div id="critical-alert-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4" role="dialog" aria-modal="true">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 border-t-4 border-red-600">
        <div class="flex items-start gap-3 mb-4">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 text-xl font-bold">!!</div>
            <div>
                <h3 class="text-lg font-bold text-red-700">Critical Value</h3>
                <p class="text-sm text-gray-600 mt-1">This report contains the following critical result(s):</p>
                <ul id="critical-modal-values" class="text-sm text-gray-700 mt-2 space-y-1 list-disc list-inside max-h-48 overflow-y-auto"></ul>
            </div>
        </div>
        <p class="text-xs text-red-600 font-medium mb-3">Critical result — report to referring doctor immediately.</p>
        <label class="block text-sm font-semibold text-gray-700 mb-1" for="critical-doctor-input">Doctor Reported To <span class="text-red-500">*</span></label>
        <input type="text" id="critical-doctor-input" placeholder="Enter doctor name"
            class="w-full border border-red-300 rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-red-500 mb-4">
        <p id="critical-doctor-error" class="text-xs text-red-600 mb-2 hidden">Doctor name is required.</p>
        <div class="flex justify-end gap-3">
            <button type="button" id="critical-modal-cancel" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium">Go Back</button>
            <button type="button" id="critical-modal-confirm" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold">Confirm &amp; Report</button>
        </div>
    </div>
</div>
