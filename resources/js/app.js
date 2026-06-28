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
