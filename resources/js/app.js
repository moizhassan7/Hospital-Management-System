import './bootstrap';

const SIDEBAR_STORAGE_KEY = 'hms-sidebar-collapsed';

function syncSidebarToggle() {
    const toggle = document.getElementById('hms-sidebar-toggle');
    if (!toggle) {
        return;
    }

    const collapsed = document.body.dataset.sidebarCollapsed === '1';
    toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
}

function initSidebarToggle() {
    const toggle = document.getElementById('hms-sidebar-toggle');
    if (!toggle) {
        return;
    }

    syncSidebarToggle();

    toggle.addEventListener('click', () => {
        const collapsed = document.body.dataset.sidebarCollapsed === '1';
        document.body.dataset.sidebarCollapsed = collapsed ? '0' : '1';

        try {
            localStorage.setItem(SIDEBAR_STORAGE_KEY, collapsed ? '0' : '1');
        } catch (e) {
            // Ignore storage errors (private browsing, etc.)
        }

        syncSidebarToggle();
    });
}

document.addEventListener('DOMContentLoaded', initSidebarToggle);

/**
 * Global submit guard.
 *
 * Prevents double submissions (which cause Laravel 419 "Page Expired" on
 * login/logout and duplicate saves) by disabling a form's submit button as
 * soon as it is submitted, until the page navigates/reloads.
 *
 * Listens on `document` during the bubble phase so that forms which call
 * `preventDefault()` in their own handler (e.g. the result entry modal flow)
 * are respected: if the submit was cancelled, `event.defaultPrevented` is true
 * and we leave the button alone until the real submission happens.
 */
const LOADING_CLASS = 'hms-btn-loading';
const LOADING_TEXT = 'Please wait…';
const REENABLE_FALLBACK_MS = 15000;

function disableSubmitter(btn) {
    if (!btn || btn.disabled || btn.dataset.noDisable !== undefined) {
        return;
    }

    btn.disabled = true;
    btn.setAttribute('aria-busy', 'true');
    btn.classList.add(LOADING_CLASS);

    // Only swap text for plain text buttons (avoid wiping icon-only buttons).
    if (btn.children.length === 0 && btn.textContent.trim() !== '') {
        btn.dataset.originalText = btn.textContent;
        btn.textContent = LOADING_TEXT;
    }

    // Safety net: if navigation never happens (e.g. an AJAX flow that stays on
    // the page), re-enable the button so the user is never permanently stuck.
    window.setTimeout(() => reenableSubmitter(btn), REENABLE_FALLBACK_MS);
}

function reenableSubmitter(btn) {
    if (!btn) {
        return;
    }

    btn.disabled = false;
    btn.removeAttribute('aria-busy');
    btn.classList.remove(LOADING_CLASS);

    if (btn.dataset.originalText !== undefined) {
        btn.textContent = btn.dataset.originalText;
        delete btn.dataset.originalText;
    }
}

function handleFormSubmit(event) {
    // A form-level handler cancelled this submit (e.g. showing a modal first).
    // Do nothing now; the real submission will bubble up here later.
    if (event.defaultPrevented) {
        return;
    }

    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.dataset.noDisable !== undefined) {
        return;
    }

    // Prefer the actual clicked button; fall back to the form's submit buttons.
    let btn = event.submitter;
    if (!btn || btn.type !== 'submit') {
        btn = form.querySelector('button[type="submit"], input[type="submit"]');
    }

    // Disable after the current event so form data (incl. the submitter's
    // name/value) is still serialized into the request.
    window.setTimeout(() => disableSubmitter(btn), 0);
}

function reenableAllSubmitters() {
    document
        .querySelectorAll(`.${LOADING_CLASS}`)
        .forEach((btn) => reenableSubmitter(btn));
}

document.addEventListener('submit', handleFormSubmit);

// Restore buttons when returning via the browser back/forward cache, otherwise
// they would stay disabled after navigating back to a cached page.
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        reenableAllSubmitters();
    }
});
