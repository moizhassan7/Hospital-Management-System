<!-- Print/PDF Header & Footer Prompt Modal -->
<div id="hms-header-footer-prompt-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="hms-hf-prompt-title">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-auto transform transition-all scale-100 p-6 flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold leading-tight text-gray-800" id="hms-hf-prompt-title">Print Options</h3>
            <button type="button" id="hms-hf-prompt-close" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <p class="text-gray-600 mb-6 text-sm">
            How would you like to generate this report? You can include the hospital letterhead and doctors' footer, or exclude them if you are printing on a pre-printed page.
        </p>

        <div class="flex flex-col gap-3">
            <button type="button" id="hms-hf-prompt-with" class="w-full flex items-center justify-center px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors shadow-sm focus:ring-4 focus:ring-blue-100">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                With Header & Footer
            </button>
            <button type="button" id="hms-hf-prompt-without" class="w-full flex items-center justify-center px-4 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium transition-colors border border-gray-200 focus:ring-4 focus:ring-gray-50">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                Without Header & Footer (Blank Space)
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const modal = document.getElementById('hms-header-footer-prompt-modal');
    if (!modal) return;
    
    const closeBtn = document.getElementById('hms-hf-prompt-close');
    const withBtn = document.getElementById('hms-hf-prompt-with');
    const withoutBtn = document.getElementById('hms-hf-prompt-without');
    
    let currentCallback = null;

    window.promptWithHeaderFooter = function(callback) {
        currentCallback = callback;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentCallback = null;
    }

    closeBtn.addEventListener('click', hideModal);

    withBtn.addEventListener('click', function() {
        if (currentCallback) currentCallback(1);
        hideModal();
    });

    withoutBtn.addEventListener('click', function() {
        if (currentCallback) currentCallback(0);
        hideModal();
    });

    // Close on click outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) hideModal();
    });
})();

window.appendWithHeaderParam = function(url, withHeader) {
    if (!url) return url;
    const separator = url.indexOf('?') !== -1 ? '&' : '?';
    return url + separator + 'with_header=' + withHeader;
};
</script>
@endpush
