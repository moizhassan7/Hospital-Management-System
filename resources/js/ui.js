/*
 * Life Care Hospital — application shell behaviour.
 *
 * Page scripts open and close dialogs by toggling the `hidden` class on the
 * dialog's backdrop element. Everything here observes that contract instead
 * of replacing it, so existing page logic keeps working unchanged.
 */

const LC = (window.LC = window.LC || {});
const root = document.documentElement;
const desktop = window.matchMedia('(min-width: 1024px)');

const FOCUSABLE = [
    'a[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
    '[contenteditable="true"]',
].join(',');

const ICONS = {
    success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
    danger: '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>',
    warning: '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    close: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
};

function svg(paths, cls = 'lc-icon') {
    return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="${cls}">${paths}</svg>`;
}

function isVisible(el) {
    return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);
}

function focusablesIn(container) {
    return [...container.querySelectorAll(FOCUSABLE)].filter((el) => isVisible(el));
}

/* ------------------------------------------------------------------------
 * Sidebar
 * --------------------------------------------------------------------- */

function initSidebar() {
    const toggles = document.querySelectorAll('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    if (!toggles.length) return;

    const syncAria = () => {
        const expanded = desktop.matches
            ? !root.classList.contains('sidebar-collapsed')
            : root.classList.contains('sidebar-open');
        toggles.forEach((btn) => btn.setAttribute('aria-expanded', String(expanded)));
    };

    const setCollapsed = (collapsed) => {
        root.classList.toggle('sidebar-collapsed', collapsed);
        try {
            localStorage.setItem('lc.sidebar', collapsed ? 'collapsed' : 'expanded');
        } catch (e) {
            /* storage unavailable — keep the in-memory state */
        }
        syncAria();
    };

    const setDrawer = (open) => {
        root.classList.toggle('sidebar-open', open);
        if (backdrop) backdrop.hidden = !open;
        syncAria();
    };

    toggles.forEach((btn) =>
        btn.addEventListener('click', () => {
            if (desktop.matches) setCollapsed(!root.classList.contains('sidebar-collapsed'));
            else setDrawer(!root.classList.contains('sidebar-open'));
        }),
    );

    backdrop?.addEventListener('click', () => setDrawer(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && root.classList.contains('sidebar-open')) setDrawer(false);
    });
    desktop.addEventListener('change', () => setDrawer(false));
    syncAria();

    document.querySelectorAll('[data-nav-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.nav-item');
            const open = !item.classList.contains('is-open');
            item.classList.toggle('is-open', open);
            btn.setAttribute('aria-expanded', String(open));
        });
    });

    initRailFlyout();
}

/* In the collapsed rail, labels and sub-pages are shown in a flyout. */
function initRailFlyout() {
    const nav = document.querySelector('.sidebar-nav');
    if (!nav) return;

    const flyout = document.createElement('div');
    flyout.className = 'nav-flyout';
    flyout.hidden = true;
    document.body.appendChild(flyout);

    let hideTimer = null;
    let current = null;

    const railMode = () => desktop.matches && root.classList.contains('sidebar-collapsed');

    const hide = () => {
        flyout.hidden = true;
        current = null;
    };

    const scheduleHide = () => {
        clearTimeout(hideTimer);
        hideTimer = setTimeout(hide, 140);
    };

    const show = (item) => {
        clearTimeout(hideTimer);
        if (!railMode() || current === item) return;
        current = item;

        const link = item.querySelector(':scope > .nav-link');
        const label = link?.querySelector('.nav-label')?.textContent.trim() ?? '';
        const sub = item.querySelector(':scope > .nav-sub');

        flyout.innerHTML = '';
        const title = document.createElement('div');
        title.className = 'nav-flyout-title';
        title.textContent = label;
        flyout.appendChild(title);

        flyout.classList.toggle('is-tooltip', !sub);
        if (sub) {
            sub.querySelectorAll('a').forEach((a) => flyout.appendChild(a.cloneNode(true)));
        }

        flyout.hidden = false;
        const r = link.getBoundingClientRect();
        const fr = flyout.getBoundingClientRect();
        let top = sub ? r.top - 6 : r.top + r.height / 2 - fr.height / 2;
        top = Math.max(8, Math.min(top, window.innerHeight - fr.height - 8));
        flyout.style.left = `${r.right + 10}px`;
        flyout.style.top = `${top}px`;
    };

    const topLevelItem = (el) => el?.closest('.sidebar-nav .nav-item:not(.nav-sub .nav-item)');

    nav.addEventListener('mouseover', (e) => {
        const item = topLevelItem(e.target);
        if (item) show(item);
    });
    nav.addEventListener('mouseleave', scheduleHide);
    nav.addEventListener('focusin', (e) => {
        const item = topLevelItem(e.target);
        if (item) show(item);
    });
    nav.addEventListener('focusout', (e) => {
        if (!flyout.contains(e.relatedTarget)) scheduleHide();
    });
    nav.addEventListener('scroll', hide, { passive: true });
    nav.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' && !flyout.hidden && !flyout.classList.contains('is-tooltip')) {
            const first = flyout.querySelector('a');
            if (first) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    flyout.addEventListener('mouseenter', () => clearTimeout(hideTimer));
    flyout.addEventListener('mouseleave', scheduleHide);
    flyout.addEventListener('focusout', (e) => {
        if (!flyout.contains(e.relatedTarget)) scheduleHide();
    });
    flyout.addEventListener('keydown', (e) => {
        const links = [...flyout.querySelectorAll('a')];
        const i = links.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            links[(i + 1) % links.length]?.focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            links[(i - 1 + links.length) % links.length]?.focus();
        } else if (e.key === 'Escape' || e.key === 'ArrowLeft') {
            e.preventDefault();
            const owner = current?.querySelector(':scope > .nav-link');
            hide();
            owner?.focus();
        }
    });

    window.addEventListener('resize', hide);
}

/* ------------------------------------------------------------------------
 * Dropdown menus
 * --------------------------------------------------------------------- */

function initMenus() {
    const menus = [];

    document.querySelectorAll('[data-menu-button]').forEach((btn) => {
        const menu = document.getElementById(btn.getAttribute('aria-controls'));
        if (!menu) return;

        const items = () => [...menu.querySelectorAll('.menu-item')].filter(isVisible);
        const close = (restoreFocus) => {
            if (menu.hidden) return;
            menu.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
            if (restoreFocus) btn.focus();
        };
        const open = () => {
            menus.forEach((m) => m.close(false));
            menu.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
        };

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (menu.hidden) {
                open();
                items()[0]?.focus();
            } else {
                close(false);
            }
        });

        btn.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                open();
                items()[0]?.focus();
            }
        });

        menu.addEventListener('keydown', (e) => {
            const list = items();
            const i = list.indexOf(document.activeElement);
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                list[(i + 1) % list.length]?.focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                list[(i - 1 + list.length) % list.length]?.focus();
            } else if (e.key === 'Home') {
                e.preventDefault();
                list[0]?.focus();
            } else if (e.key === 'End') {
                e.preventDefault();
                list[list.length - 1]?.focus();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                close(true);
            } else if (e.key === 'Tab') {
                close(false);
            }
        });

        menus.push({ menu, close });
    });

    document.addEventListener('click', (e) => {
        menus.forEach((m) => {
            if (!m.menu.contains(e.target)) m.close(false);
        });
    });
}

/* ------------------------------------------------------------------------
 * Dialogs — focus management for every [data-dialog]
 * --------------------------------------------------------------------- */

const openStack = [];
const returnFocus = new WeakMap();

const dialogIsOpen = (d) => !d.classList.contains('hidden') && !d.hidden;

function onDialogOpened(d) {
    if (openStack.includes(d)) return;
    openStack.push(d);
    returnFocus.set(d, document.activeElement);
    document.body.classList.add('dialog-open');

    requestAnimationFrame(() => {
        if (d.contains(document.activeElement)) return;
        const list = focusablesIn(d);
        const target =
            d.querySelector('[autofocus]') ||
            list.find((el) => el.matches('input, select, textarea')) ||
            list[0] ||
            d.querySelector('.dialog, .palette');
        target?.focus?.({ preventScroll: true });
    });
}

function onDialogClosed(d) {
    const i = openStack.indexOf(d);
    if (i === -1) return;
    openStack.splice(i, 1);
    if (!openStack.length) document.body.classList.remove('dialog-open');

    const back = returnFocus.get(d);
    const active = document.activeElement;
    const focusLost = !active || active === document.body || d.contains(active);
    if (back && document.contains(back) && focusLost) back.focus?.({ preventScroll: true });
}

function watchDialog(d) {
    if (d.__lcWatched) return;
    d.__lcWatched = true;
    new MutationObserver(() => (dialogIsOpen(d) ? onDialogOpened(d) : onDialogClosed(d))).observe(d, {
        attributes: true,
        attributeFilter: ['class', 'hidden'],
    });
    if (dialogIsOpen(d)) onDialogOpened(d);
}

LC.openDialog = (id) => {
    const d = typeof id === 'string' ? document.getElementById(id) : id;
    if (!d) return;
    watchDialog(d);
    d.hidden = false;
    d.classList.remove('hidden');
};

LC.closeDialog = (id) => {
    const d = typeof id === 'string' ? document.getElementById(id) : id;
    d?.classList.add('hidden');
};

function requestClose(d) {
    const closer = d.querySelector('[data-dialog-close]');
    if (closer) closer.click();
    else d.classList.add('hidden');
}

function initDialogs() {
    document.querySelectorAll('[data-dialog]').forEach(watchDialog);

    document.addEventListener('click', (e) => {
        const opener = e.target.closest('[data-dialog-open]');
        if (opener) {
            e.preventDefault();
            LC.openDialog(opener.getAttribute('data-dialog-open'));
            return;
        }

        const closer = e.target.closest('[data-dialog-close]');
        if (closer) {
            closer.closest('[data-dialog]')?.classList.add('hidden');
            return;
        }

        if (e.target.matches?.('[data-dialog][data-backdrop-close]')) {
            requestClose(e.target);
        }
    });

    document.addEventListener('keydown', (e) => {
        const top = openStack[openStack.length - 1];
        if (!top || !dialogIsOpen(top)) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            requestClose(top);
            return;
        }

        if (e.key === 'Tab') {
            const list = focusablesIn(top);
            if (!list.length) {
                e.preventDefault();
                return;
            }
            const first = list[0];
            const last = list[list.length - 1];
            const inside = top.contains(document.activeElement);
            if (e.shiftKey && (document.activeElement === first || !inside)) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && (document.activeElement === last || !inside)) {
                e.preventDefault();
                first.focus();
            }
        }
    });
}

/* ------------------------------------------------------------------------
 * Confirmation for destructive actions: <form data-confirm="…">
 * --------------------------------------------------------------------- */

LC.confirm = ({ title = 'Are you sure?', message = '', confirmLabel = 'Confirm', tone = 'danger' } = {}) =>
    new Promise((resolve) => {
        const d = document.getElementById('lc-confirm');
        if (!d) {
            resolve(window.confirm(message || title));
            return;
        }

        d.querySelector('[data-confirm-title]').textContent = title;
        d.querySelector('[data-confirm-message]').textContent = message;
        const accept = d.querySelector('[data-confirm-accept]');
        accept.textContent = confirmLabel;
        accept.className = `btn ${tone === 'danger' ? 'btn-danger' : 'btn-primary'}`;

        const finish = (result) => {
            observer.disconnect();
            accept.removeEventListener('click', onAccept);
            d.classList.add('hidden');
            resolve(result);
        };
        const onAccept = () => finish(true);
        const observer = new MutationObserver(() => {
            if (!dialogIsOpen(d)) finish(false);
        });

        accept.addEventListener('click', onAccept);
        LC.openDialog(d);
        observer.observe(d, { attributes: true, attributeFilter: ['class', 'hidden'] });
        requestAnimationFrame(() => d.querySelector('[data-dialog-close]')?.focus());
    });

function initConfirm() {
    document.addEventListener(
        'submit',
        async (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) return;
            if (form.dataset.confirmed === '1') return;

            e.preventDefault();
            const ok = await LC.confirm({
                title: form.dataset.confirmTitle || 'Are you sure?',
                message: form.dataset.confirm,
                confirmLabel: form.dataset.confirmLabel || 'Delete',
                tone: form.dataset.confirmTone || 'danger',
            });
            if (!ok) return;

            form.dataset.confirmed = '1';
            if (form.requestSubmit) form.requestSubmit(e.submitter || undefined);
            else form.submit();
        },
        true,
    );
}

/* ------------------------------------------------------------------------
 * Busy state for submit buttons: <button type="submit" data-busy-label="Saving…">
 * --------------------------------------------------------------------- */

function initBusyButtons() {
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        const btn = e.submitter || e.target.querySelector('[type="submit"][data-busy-label]');
        if (!btn || !btn.hasAttribute('data-busy-label')) return;
        // Defer so the browser captures the submitter before it is disabled.
        setTimeout(() => {
            btn.dataset.idleHtml = btn.innerHTML;
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');
            btn.innerHTML = `<span class="spinner" aria-hidden="true"></span><span>${btn.dataset.busyLabel}</span>`;
        }, 0);
    });

    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        document.querySelectorAll('[data-busy-label][aria-busy="true"]').forEach((btn) => {
            btn.disabled = false;
            btn.removeAttribute('aria-busy');
            if (btn.dataset.idleHtml) btn.innerHTML = btn.dataset.idleHtml;
        });
    });
}

/* ------------------------------------------------------------------------
 * Page jump palette (Ctrl/Cmd + K) — lists pages the user may open
 * --------------------------------------------------------------------- */

function initPalette() {
    const d = document.getElementById('lc-palette');
    if (!d) return;

    const input = d.querySelector('[data-palette-input]');
    const items = [...d.querySelectorAll('[data-palette-item]')];
    const empty = d.querySelector('[data-palette-empty]');
    let visible = items;
    let index = 0;

    const select = (i) => {
        visible.forEach((el) => el.setAttribute('aria-selected', 'false'));
        index = Math.max(0, Math.min(i, visible.length - 1));
        const el = visible[index];
        if (!el) {
            input.removeAttribute('aria-activedescendant');
            return;
        }
        el.setAttribute('aria-selected', 'true');
        input.setAttribute('aria-activedescendant', el.id);
        el.scrollIntoView({ block: 'nearest' });
    };

    const filter = () => {
        const terms = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
        visible = items.filter((el) => {
            const hay = el.dataset.search;
            const match = terms.every((t) => hay.includes(t));
            el.hidden = !match;
            return match;
        });
        if (empty) {
            empty.hidden = visible.length > 0;
            const echo = empty.querySelector('span');
            if (echo) echo.textContent = input.value.trim();
        }
        select(0);
    };

    const open = () => {
        input.value = '';
        filter();
        LC.openDialog(d);
        requestAnimationFrame(() => input.focus());
    };

    document.querySelectorAll('[data-palette-open]').forEach((btn) => btn.addEventListener('click', open));
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && !e.shiftKey && !e.altKey && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (dialogIsOpen(d)) LC.closeDialog(d);
            else open();
        }
    });

    input.addEventListener('input', filter);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            select(index + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            select(index - 1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const el = visible[index];
            if (el) window.location.href = el.href;
        }
    });
    items.forEach((el) =>
        el.addEventListener('mousemove', () => {
            const i = visible.indexOf(el);
            if (i !== index) select(i);
        }),
    );
}

/* ------------------------------------------------------------------------
 * Alerts & toasts
 * --------------------------------------------------------------------- */

LC.toast = (message, tone = 'info', { timeout = 6000 } = {}) => {
    let region = document.getElementById('lc-toasts');
    if (!region) {
        region = document.createElement('div');
        region.id = 'lc-toasts';
        region.className = 'toast-region';
        document.body.appendChild(region);
    }

    const el = document.createElement('div');
    el.className = `alert alert-${tone} toast`;
    el.setAttribute('role', tone === 'danger' || tone === 'warning' ? 'alert' : 'status');
    el.innerHTML = `${svg(ICONS[tone] || ICONS.info, 'lc-icon alert-icon')}<div class="alert-body"><div class="alert-text"></div></div><button type="button" class="alert-close" data-dismiss="alert" aria-label="Dismiss">${svg(ICONS.close)}</button>`;
    el.querySelector('.alert-text').textContent = message;
    region.appendChild(el);

    if (timeout) setTimeout(() => el.remove(), timeout);
    return el;
};

function initAlerts() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-dismiss="alert"]');
        if (btn) btn.closest('.alert')?.remove();
    });
}

/* ------------------------------------------------------------------------
 * Client-side row filter: <input data-table-filter="#table-id">
 * Rows marked data-empty-row are ignored. An optional element with
 * data-filter-count="#table-id" receives "n of total".
 * --------------------------------------------------------------------- */

function initTableFilters() {
    document.querySelectorAll('[data-table-filter]').forEach((input) => {
        const selector = input.dataset.tableFilter;
        const table = document.querySelector(selector);
        const body = table?.tBodies[0];
        if (!body) return;

        const rows = [...body.rows].filter((row) => !row.hasAttribute('data-empty-row'));
        const counter = document.querySelector(`[data-filter-count="${selector}"]`);
        const columns = table.tHead?.rows[0]?.cells.length || 1;
        let emptyRow = null;

        const update = () => {
            const terms = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
            let shown = 0;
            rows.forEach((row) => {
                const match = terms.every((t) => row.textContent.toLowerCase().includes(t));
                row.hidden = !match;
                if (match) shown += 1;
            });

            if (rows.length) {
                if (!emptyRow) {
                    emptyRow = body.insertRow();
                    emptyRow.setAttribute('data-empty-row', '');
                    const cell = emptyRow.insertCell();
                    cell.colSpan = columns;
                    cell.className = 'table-empty';
                }
                emptyRow.cells[0].textContent = `Nothing matches “${input.value.trim()}”. Try a different name or number.`;
                emptyRow.hidden = shown > 0;
            }

            if (counter) counter.textContent = terms.length ? `${shown} of ${rows.length}` : `${rows.length}`;
        };

        input.addEventListener('input', update);
        if (counter) counter.textContent = `${rows.length}`;
    });
}

/* ------------------------------------------------------------------------
 * Tabs: <div role="tablist" data-tabs> with <button role="tab" aria-controls="panel-id">.
 * Panels are toggled with the hidden attribute; arrow keys move between tabs.
 * --------------------------------------------------------------------- */

function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach((list) => {
        const tabs = [...list.querySelectorAll('[role="tab"]')];
        if (!tabs.length) return;

        const select = (tab, focus = false) => {
            tabs.forEach((t) => {
                const selected = t === tab;
                t.setAttribute('aria-selected', selected ? 'true' : 'false');
                t.tabIndex = selected ? 0 : -1;
                const panel = document.getElementById(t.getAttribute('aria-controls'));
                if (panel) panel.hidden = !selected;
            });
            if (focus) tab.focus();
        };

        list.addEventListener('click', (e) => {
            const tab = e.target.closest('[role="tab"]');
            if (tab && list.contains(tab)) select(tab);
        });

        list.addEventListener('keydown', (e) => {
            const index = tabs.indexOf(document.activeElement);
            if (index < 0) return;
            const next = { ArrowRight: index + 1, ArrowLeft: index - 1, Home: 0, End: tabs.length - 1 }[e.key];
            if (next === undefined) return;
            e.preventDefault();
            select(tabs[(next + tabs.length) % tabs.length], true);
        });

        select(tabs.find((t) => t.getAttribute('aria-selected') === 'true') || tabs[0]);
    });
}

/* ------------------------------------------------------------------------
 * JSON requests and search pickers for page scripts.
 * --------------------------------------------------------------------- */

LC.escape = (value) =>
    String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

/**
 * fetch() with the page's CSRF token that always asks for JSON, so Laravel
 * answers validation failures with 422 instead of a redirect. On failure the
 * thrown Error carries `status` and `messages` (every validation message).
 */
LC.json = async (url, { method = 'GET', body } = {}) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(body !== undefined ? { 'Content-Type': 'application/json' } : {}),
            ...(token ? { 'X-CSRF-TOKEN': token } : {}),
        },
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });
    const data = await response.json().catch(() => null);

    if (!response.ok) {
        const fallback = {
            401: 'You have been signed out. Sign in again, then retry.',
            419: 'This page expired. Reload it and try again.',
        }[response.status];
        const messages = data?.errors ? Object.values(data.errors).flat() : [];
        const error = new Error(messages[0] || fallback || data?.message || `Request failed (${response.status}).`);
        error.status = response.status;
        error.messages = messages.length ? messages : [error.message];
        throw error;
    }
    return data;
};

/**
 * Search dialog fed by an async load(term). Columns render cells as HTML
 * (escape values with LC.escape). Rows for which disabledReason(row) returns
 * text cannot be picked. ↑/↓ move, Enter picks.
 */
LC.searchPicker = ({ dialog, input, results, load, columns, disabledReason = () => null, onPick, emptyText = 'Nothing found.', delay = 250 }) => {
    const d = document.getElementById(dialog);
    const box = document.getElementById(input);
    const list = document.getElementById(results);
    let rows = [];
    let active = -1;
    let timer = null;
    let request = 0;

    const render = () => {
        if (!rows.length) {
            list.innerHTML = `<div class="result-message">${LC.escape(emptyText)}</div>`;
            return;
        }
        const head = columns.map((c) => `<th class="${c.className || ''}">${LC.escape(c.label)}</th>`).join('');
        const body = rows
            .map((row, i) => {
                const reason = disabledReason(row);
                const cells = columns.map((c) => `<td class="${c.className || ''}">${c.render(row)}</td>`).join('');
                const state = `${reason ? ' is-disabled' : ''}${i === active ? ' is-active' : ''}`;
                return `<tr class="search-result-row${state}" data-index="${i}"${reason ? ` aria-disabled="true" title="${LC.escape(reason)}"` : ''}>${cells}</tr>`;
            })
            .join('');
        list.innerHTML = `<table class="table"><thead><tr>${head}</tr></thead><tbody>${body}</tbody></table>`;
    };

    const pick = (i) => {
        const row = rows[i];
        if (!row || disabledReason(row)) return;
        LC.closeDialog(d);
        onPick(row);
    };

    const search = () => {
        clearTimeout(timer);
        timer = setTimeout(async () => {
            const id = ++request;
            list.innerHTML = '<div class="result-message">Searching…</div>';
            try {
                const data = await load(box.value.trim());
                if (id !== request) return;
                rows = Array.isArray(data) ? data : [];
                active = rows.findIndex((r) => !disabledReason(r));
                render();
            } catch (error) {
                if (id !== request) return;
                rows = [];
                list.innerHTML = `<div class="result-message is-error">${LC.escape(error.message || 'Could not load results.')}</div>`;
            }
        }, delay);
    };

    box.addEventListener('input', search);
    box.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            const step = e.key === 'ArrowDown' ? 1 : -1;
            for (let i = active + step; i >= 0 && i < rows.length; i += step) {
                if (!disabledReason(rows[i])) {
                    active = i;
                    break;
                }
            }
            render();
            list.querySelector('.is-active')?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            e.preventDefault();
            pick(active);
        }
    });
    list.addEventListener('click', (e) => {
        const tr = e.target.closest('.search-result-row');
        if (tr) pick(Number(tr.dataset.index));
    });

    return {
        open(term = '') {
            box.value = term;
            rows = [];
            active = -1;
            list.innerHTML = '';
            LC.openDialog(d);
            requestAnimationFrame(() => box.focus());
            search();
        },
    };
};

/* ------------------------------------------------------------------------ */

function boot() {
    initSidebar();
    initMenus();
    initDialogs();
    initConfirm();
    initBusyButtons();
    initPalette();
    initAlerts();
    initTableFilters();
    initTabs();
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
