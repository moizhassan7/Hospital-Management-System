{{-- Shared confirmation used by <form data-confirm="…"> and window.LC.confirm(). --}}
<div id="lc-confirm" class="dialog-backdrop hidden" role="alertdialog" aria-modal="true" aria-labelledby="lc-confirm-title" aria-describedby="lc-confirm-message" data-dialog>
    <div class="dialog dialog-sm" tabindex="-1">
        <div class="dialog-header border-b-0 pb-1">
            <span class="dialog-icon dialog-icon-danger"><x-ui.icon name="alert-triangle" /></span>
            <div class="min-w-0 flex-1">
                <h2 id="lc-confirm-title" class="dialog-title" data-confirm-title>Are you sure?</h2>
                <p id="lc-confirm-message" class="dialog-description" data-confirm-message></p>
            </div>
        </div>
        <div class="dialog-footer mt-3">
            <button type="button" class="btn btn-secondary" data-dialog-close>Cancel</button>
            <button type="button" class="btn btn-danger" data-confirm-accept>Delete</button>
        </div>
    </div>
</div>
