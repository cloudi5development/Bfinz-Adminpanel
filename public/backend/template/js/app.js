/* ==========================================================================
   Bfinz Admin Panel — behaviour
   --------------------------------------------------------------------------
   Vanilla JS, no framework and no build step. Everything is delegated from
   `document`, so markup rendered later (a modal, an extra table) works without
   re-binding anything.

   Data attributes it reacts to:
     [data-sidebar-toggle]   collapse / expand the desktop sidebar
     [data-drawer-toggle]    open the mobile drawer
     [data-nav-toggle]       expand a sidebar section
     [data-dropdown]         wrapper for a dropdown; [data-dropdown-trigger] opens it
     [data-modal-open="id"]  open the modal with that id
     [data-modal-close]      close the nearest modal
     [data-tabs]             tab group; [data-tab="key"] + [data-panel="key"]
     [data-segment]          segmented control; buttons carry [data-segment-value]
     [data-sort]             sortable table header
     [data-table-search]     filters the rows of [data-table] by text
     [data-toast]            fires a demo toast (UI-phase affordance)
   ========================================================================== */

(function () {
    'use strict';

    var body = document.body;
    var STORAGE_KEY = 'bfinz.sidebar.collapsed';

    /* ------------------------------------------------------- Sidebar -- */

    // Restore the collapsed preference before paint-sensitive work happens.
    try {
        if (localStorage.getItem(STORAGE_KEY) === '1' && window.innerWidth > 991) {
            body.classList.add('is-collapsed');
        }
    } catch (e) { /* private mode — fall back to expanded */ }

    function toggleSidebar() {
        body.classList.toggle('is-collapsed');
        try {
            localStorage.setItem(STORAGE_KEY, body.classList.contains('is-collapsed') ? '1' : '0');
        } catch (e) { /* ignore */ }
    }

    function openDrawer()  { body.classList.add('is-drawer-open'); }
    function closeDrawer() { body.classList.remove('is-drawer-open'); }

    /* ------------------------------------------------------ Dropdowns -- */

    function closeDropdowns(except) {
        document.querySelectorAll('[data-dropdown].is-open').forEach(function (d) {
            if (d !== except) {
                d.classList.remove('is-open');
                var trigger = d.querySelector('[data-dropdown-trigger]');
                if (trigger) { trigger.setAttribute('aria-expanded', 'false'); }
            }
        });
    }

    /* --------------------------------------------------------- Modals -- */

    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) { return; }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        body.style.overflow = 'hidden';

        var focusable = modal.querySelector('[autofocus], button, [href], input, select, textarea');
        if (focusable) { focusable.focus(); }
    }

    function closeModal(modal) {
        if (!modal) { return; }
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        body.style.overflow = '';
    }

    function closeAllModals() {
        document.querySelectorAll('.modal.is-open').forEach(closeModal);
    }

    /* --------------------------------------------------------- Toasts -- */

    var ICONS = {
        success: '<path d="M20 6 9 17l-5-5"/>',
        warning: '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        danger:  '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
        info:    '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>'
    };

    function toast(title, bodyText, tone) {
        tone = tone || 'info';

        var host = document.querySelector('.toasts');
        if (!host) {
            host = document.createElement('div');
            host.className = 'toasts';
            document.body.appendChild(host);
        }

        var el = document.createElement('div');
        el.className = 'toast toast--' + tone;
        el.setAttribute('role', 'status');
        el.innerHTML =
            '<svg class="toast__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
            'stroke-linecap="round" stroke-linejoin="round">' + (ICONS[tone] || ICONS.info) + '</svg>' +
            '<div><div class="toast__title"></div>' + (bodyText ? '<div class="toast__body"></div>' : '') + '</div>' +
            '<button class="toast__close" type="button" aria-label="Dismiss">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">' +
            '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';

        // Assign as text, never as HTML — messages can carry record names.
        el.querySelector('.toast__title').textContent = title;
        if (bodyText) { el.querySelector('.toast__body').textContent = bodyText; }

        host.appendChild(el);

        var timer = setTimeout(dismiss, 4200);

        function dismiss() {
            clearTimeout(timer);
            el.classList.add('is-leaving');
            setTimeout(function () { el.remove(); }, 220);
        }

        el.querySelector('.toast__close').addEventListener('click', dismiss);
    }

    window.bfinzToast = toast;

    /* ---------------------------------------------------- Table sort -- */

    function sortTable(th) {
        var table = th.closest('table');
        var tbody = table.querySelector('tbody');
        if (!tbody) { return; }

        var index = Array.prototype.indexOf.call(th.parentNode.children, th);
        var asc   = th.getAttribute('data-sort-dir') !== 'asc';

        table.querySelectorAll('th[data-sort]').forEach(function (other) {
            if (other !== th) { other.removeAttribute('data-sort-dir'); }
        });
        th.setAttribute('data-sort-dir', asc ? 'asc' : 'desc');

        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

        rows.sort(function (a, b) {
            var x = cellText(a, index);
            var y = cellText(b, index);

            var nx = numeric(x);
            var ny = numeric(y);

            if (nx !== null && ny !== null) { return asc ? nx - ny : ny - nx; }
            return asc ? x.localeCompare(y) : y.localeCompare(x);
        });

        rows.forEach(function (r) { tbody.appendChild(r); });
    }

    function cellText(row, index) {
        var cell = row.children[index];
        return cell ? cell.textContent.trim() : '';
    }

    // "₹6,850" / "12,480" / "8.50%" → a number we can compare; otherwise null.
    function numeric(value) {
        var cleaned = value.replace(/[₹,%\s,]/g, '').replace(/[^0-9.\-]/g, '');
        if (cleaned === '' || cleaned === '-' || cleaned === '.') { return null; }
        var n = parseFloat(cleaned);
        return isNaN(n) ? null : n;
    }

    /* -------------------------------------------------- Table search -- */

    function filterTable(input) {
        var target = document.querySelector(input.getAttribute('data-table-search'));
        if (!target) { return; }

        var term = input.value.trim().toLowerCase();
        var rows = target.querySelectorAll('tbody tr');
        var shown = 0;

        rows.forEach(function (row) {
            if (row.hasAttribute('data-empty-row')) { return; }
            var match = term === '' || row.textContent.toLowerCase().indexOf(term) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) { shown++; }
        });

        // Show the "nothing matched" row when a search empties the table.
        var emptyRow = target.querySelector('[data-empty-row]');
        if (emptyRow) { emptyRow.style.display = shown === 0 ? '' : 'none'; }

        var counter = document.querySelector('[data-row-count]');
        if (counter) { counter.textContent = shown; }
    }

    /* ------------------------------------------------------ Delegation -- */

    document.addEventListener('click', function (event) {
        var el;

        // Sidebar collapse / drawer
        if (event.target.closest('[data-sidebar-toggle]')) { toggleSidebar(); return; }
        if (event.target.closest('[data-drawer-toggle]'))  { openDrawer();    return; }
        if (event.target.closest('[data-drawer-close]'))   { closeDrawer();   return; }

        // Sidebar section expand
        el = event.target.closest('[data-nav-toggle]');
        if (el) {
            event.preventDefault();
            var item = el.closest('.nav-item');
            var wasOpen = item.classList.contains('is-open');

            // Accordion: only one section open at a time keeps a 15-section
            // menu readable without endless scrolling.
            item.parentNode.querySelectorAll('.nav-item.is-open').forEach(function (open) {
                if (open !== item) {
                    open.classList.remove('is-open');
                    var t = open.querySelector('[data-nav-toggle]');
                    if (t) { t.setAttribute('aria-expanded', 'false'); }
                }
            });

            item.classList.toggle('is-open', !wasOpen);
            el.setAttribute('aria-expanded', String(!wasOpen));
            return;
        }

        // Dropdowns
        el = event.target.closest('[data-dropdown-trigger]');
        if (el) {
            event.preventDefault();
            var dd = el.closest('[data-dropdown]');
            var willOpen = !dd.classList.contains('is-open');
            closeDropdowns(dd);
            dd.classList.toggle('is-open', willOpen);
            el.setAttribute('aria-expanded', String(willOpen));
            return;
        }

        if (!event.target.closest('.dropdown__menu')) { closeDropdowns(null); }

        // Modals
        el = event.target.closest('[data-modal-open]');
        if (el) { event.preventDefault(); openModal(el.getAttribute('data-modal-open')); return; }

        if (event.target.closest('[data-modal-close]') || event.target.classList.contains('modal__overlay')) {
            closeModal(event.target.closest('.modal'));
            return;
        }

        // Tabs
        el = event.target.closest('[data-tab]');
        if (el) {
            var group = el.closest('[data-tabs]');
            var key = el.getAttribute('data-tab');

            group.querySelectorAll('[data-tab]').forEach(function (t) {
                var on = t === el;
                t.classList.toggle('is-active', on);
                t.setAttribute('aria-selected', String(on));
            });

            var scope = group.parentNode;
            scope.querySelectorAll('[data-panel]').forEach(function (p) {
                p.classList.toggle('is-active', p.getAttribute('data-panel') === key);
            });
            return;
        }

        // Segmented controls (chart range switches)
        el = event.target.closest('[data-segment-value]');
        if (el) {
            var seg = el.closest('[data-segment]');
            var value = el.getAttribute('data-segment-value');

            seg.querySelectorAll('[data-segment-value]').forEach(function (b) {
                b.classList.toggle('is-active', b === el);
                b.setAttribute('aria-pressed', String(b === el));
            });

            var owner = document.querySelector(seg.getAttribute('data-segment'));
            if (owner) {
                owner.querySelectorAll('[data-series]').forEach(function (s) {
                    s.classList.toggle('u-hide', s.getAttribute('data-series') !== value);
                });
            }
            return;
        }

        // Sorting
        el = event.target.closest('th[data-sort]');
        if (el) { sortTable(el); return; }

        // Demo toast — stands in for the save/export call that will exist later.
        el = event.target.closest('[data-toast]');
        if (el) {
            event.preventDefault();
            toast(el.getAttribute('data-toast'), el.getAttribute('data-toast-body') || '', el.getAttribute('data-toast-tone') || 'info');
        }
    });

    document.addEventListener('input', function (event) {
        if (event.target.matches('[data-table-search]')) { filterTable(event.target); }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAllModals();
            closeDropdowns(null);
            closeDrawer();
        }

        // "/" focuses the global search, the way most admin tools behave.
        if (event.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName)) {
            var search = document.querySelector('[data-global-search]');
            if (search) { event.preventDefault(); search.focus(); }
        }
    });

    // Leaving the drawer breakpoint should not strand the drawer open.
    var wasNarrow = window.innerWidth <= 991;
    window.addEventListener('resize', function () {
        var isNarrow = window.innerWidth <= 991;
        if (isNarrow !== wasNarrow) { closeDrawer(); wasNarrow = isNarrow; }
    });
})();
