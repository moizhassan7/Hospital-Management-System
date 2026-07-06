<iframe id="hms-inline-print-frame" title="Print" tabindex="-1" aria-hidden="true"
    style="position:fixed;width:0;height:0;border:0;opacity:0;pointer-events:none;left:-9999px;"></iframe>

@push('scripts')
<script>
(function () {
    const frame = document.getElementById('hms-inline-print-frame');
    if (!frame) return;

    window.hmsInlinePrint = function (url) {
        if (!url) return;

        frame.onload = function () {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (e) {
                window.open(url, '_blank');
            }
            frame.onload = null;
        };

        frame.src = url;
    };

    document.querySelectorAll('[data-hms-print]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            hmsInlinePrint(btn.getAttribute('data-hms-print'));
        });
    });
})();
</script>
@endpush
