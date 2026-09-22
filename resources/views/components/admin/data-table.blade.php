@props([
    'id' => 'dataTable',
    'columns' => [],
    'rows' => [],
    'filters' => [],
    'search' => 'Search records',
    'primary' => null,
    'export' => true,
    'total' => null,
    'empty' => [],
    'actions' => true,
    'perPage' => 10,
])

{{--
    Data table — one self-contained component.

    Everything the table needs is in this single file: the toolbar, the table
    itself, the row actions, the empty state, the pagination and all three
    modals. No other Blade component is pulled in, so you can read the whole
    screen here without jumping between files.

    Icons are Font Awesome <i> tags. Styles live in one place:
    public/backend/template/css/style.css, under "24. Data table" — every class
    below is prefixed `dt-` so it cannot collide with the rest of the theme.

    Columns arrive as [['key' => 'city', 'label' => 'City', 'type' => 'strong']].
    `type` decides how the cell is drawn; the switch further down is the whole
    vocabulary:

        strong  entity  mono  amount  muted  chip  badge  rating  delta  text
--}}

@php
    // ---------------------------------------------------------------------
    // Everything below is derived here so the markup stays readable.
    // ---------------------------------------------------------------------

    $emptyTitle = $empty['title'] ?? 'No records found';
    $emptyBody  = $empty['body']  ?? 'Nothing matches the current filters yet.';
    $colSpan    = count($columns) + ($actions ? 1 : 0);

    $shown = count($rows);
    $totalRows = $total ?: $shown;
    $pageCount = max(1, (int) ceil($totalRows / max(1, $perPage)));

    // Status word -> badge colour. One mapping, so the same word is never
    // green on one screen and amber on another.
    $badgeClass = function ($value) {
        return match (mb_strtolower(trim((string) $value))) {
            'active', 'published', 'connected', 'success', 'resolved', 'sent',
            'completed', 'enabled'                       => 'dt-badge--green',
            'warning', 'pending', 'scheduled', 'open',
            'in progress', 'review', 'reviewed'          => 'dt-badge--amber',
            'offline', 'failed', 'error', 'rejected',
            'expired', 'high'                            => 'dt-badge--red',
            'draft', 'inactive', 'archived', 'disabled',
            'low'                                        => 'dt-badge--grey',
            'medium'                                     => 'dt-badge--blue',
            default                                      => 'dt-badge--navy',
        };
    };
@endphp

<div class="dt-card">

    {{-- ================= Toolbar ================= --}}
    <div class="dt-toolbar">

        <div class="dt-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <label for="{{ $id }}Search" class="dt-sr-only">{{ $search }}</label>
            <input type="search" id="{{ $id }}Search" placeholder="{{ $search }}"
                   autocomplete="off" data-dt-search="{{ $id }}">
        </div>

        @foreach ($filters as $i => $filter)
            <label for="{{ $id }}Filter{{ $i }}" class="dt-sr-only">{{ $filter['label'] }}</label>
            <select class="dt-select" id="{{ $id }}Filter{{ $i }}">
                <option value="">{{ $filter['label'] }}: All</option>
                @foreach ($filter['options'] as $option)
                    <option>{{ $option }}</option>
                @endforeach
            </select>
        @endforeach

        <span class="dt-spacer"></span>

        @if ($export)
            <button type="button" class="dt-btn dt-btn--ghost" data-dt-toast="Export queued">
                <i class="fa-solid fa-download"></i> Export
            </button>
        @endif

        @if ($primary)
            <button type="button" class="dt-btn dt-btn--primary" data-dt-open="{{ $id }}FormModal">
                <i class="fa-solid fa-plus"></i> {{ $primary }}
            </button>
        @endif
    </div>

    {{-- ================= Table ================= --}}
    <div class="dt-scroll">
        <table class="dt-table" id="{{ $id }}">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col"
                            class="dt-sortable {{ ($column['type'] ?? '') === 'amount' ? 'dt-right' : '' }}"
                            data-dt-sort>{{ $column['label'] }}</th>
                    @endforeach

                    @if ($actions)
                        <th scope="col" class="dt-right">Actions</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($columns as $column)
                            @php
                                $key   = $column['key'];
                                $type  = $column['type'] ?? 'text';
                                $value = $row[$key] ?? '—';
                                $sub   = $row[$key . '_sub'] ?? null;
                            @endphp

                            <td class="{{ match ($type) {
                                'strong' => 'dt-strong',
                                'muted'  => 'dt-muted',
                                'mono'   => 'dt-mono',
                                'amount' => 'dt-right dt-amount',
                                default  => '',
                            } }}">

                                @if ($type === 'entity')
                                    <span class="dt-entity">
                                        <span class="dt-avatar">{{ mb_strtoupper(mb_substr($value, 0, 1)) }}</span>
                                        <span>
                                            <span class="dt-entity-name">{{ $value }}</span>
                                            @if ($sub)
                                                <span class="dt-entity-sub">{{ $sub }}</span>
                                            @endif
                                        </span>
                                    </span>

                                @elseif ($type === 'badge')
                                    <span class="dt-badge {{ $badgeClass($value) }}">{{ $value }}</span>

                                @elseif ($type === 'chip')
                                    <span class="dt-chip">{{ $value }}</span>

                                @elseif ($type === 'rating')
                                    <span class="dt-stars" aria-label="{{ $value }} out of 5">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="fa-{{ $s <= (int) $value ? 'solid' : 'regular' }} fa-star"></i>
                                        @endfor
                                    </span>

                                @elseif ($type === 'delta')
                                    @php
                                        $down = str_starts_with((string) $value, '-');
                                        $up   = str_starts_with((string) $value, '+');
                                    @endphp
                                    <span class="dt-delta {{ $down ? 'dt-delta--down' : ($up ? 'dt-delta--up' : '') }}">
                                        @if ($up)   <i class="fa-solid fa-arrow-up"></i>   @endif
                                        @if ($down) <i class="fa-solid fa-arrow-down"></i> @endif
                                        {{ $value }}
                                    </span>

                                @elseif ($type === 'muted')
                                    <span class="dt-truncate">{{ $value }}</span>

                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach

                        @if ($actions)
                            <td class="dt-right">
                                <span class="dt-actions">
                                    <button type="button" class="dt-icon-btn" title="View details"
                                            aria-label="View details" data-dt-open="{{ $id }}ViewModal">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    <button type="button" class="dt-icon-btn" title="Edit record"
                                            aria-label="Edit record" data-dt-open="{{ $id }}FormModal">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button type="button" class="dt-icon-btn dt-icon-btn--danger" title="Delete record"
                                            aria-label="Delete record" data-dt-open="{{ $id }}DeleteModal">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </span>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $colSpan }}">
                            <div class="dt-empty">
                                <i class="fa-regular fa-folder-open"></i>
                                <p class="dt-empty-title">{{ $emptyTitle }}</p>
                                <p class="dt-empty-body">{{ $emptyBody }}</p>
                                @if ($primary)
                                    <button type="button" class="dt-btn dt-btn--primary"
                                            data-dt-open="{{ $id }}FormModal">
                                        <i class="fa-solid fa-plus"></i> {{ $primary }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse

                {{-- Shown by the script when a search matches nothing. --}}
                <tr class="dt-no-match" hidden>
                    <td colspan="{{ $colSpan }}">
                        <div class="dt-empty">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <p class="dt-empty-title">No matching records</p>
                            <p class="dt-empty-body">Try a different search term or clear the filters.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ================= Pagination ================= --}}
    @if ($shown)
        <div class="dt-foot">
            <p class="dt-count">
                Showing <strong data-dt-count="{{ $id }}">{{ $shown }}</strong>
                of <strong>{{ number_format($totalRows) }}</strong> records
            </p>

            <nav class="dt-pager" aria-label="Pagination">
                <button type="button" class="dt-page" disabled aria-label="Previous page">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                @for ($p = 1; $p <= min(3, $pageCount); $p++)
                    <button type="button" class="dt-page {{ $p === 1 ? 'is-current' : '' }}"
                            @if ($p === 1) aria-current="page" @endif>{{ $p }}</button>
                @endfor

                @if ($pageCount > 4)
                    <span class="dt-page dt-gap">&hellip;</span>
                    <button type="button" class="dt-page">{{ $pageCount }}</button>
                @elseif ($pageCount === 4)
                    <button type="button" class="dt-page">4</button>
                @endif

                <button type="button" class="dt-page" aria-label="Next page"
                        @disabled($pageCount < 2)>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </nav>
        </div>
    @endif
</div>

{{-- ================= Modals — plain HTML, no component ================= --}}

{{-- Add / edit --}}
<div class="dt-modal" id="{{ $id }}FormModal" role="dialog" aria-modal="true"
     aria-labelledby="{{ $id }}FormTitle" hidden>
    <div class="dt-modal-backdrop" data-dt-close></div>

    <div class="dt-modal-box">
        <div class="dt-modal-head">
            <h2 class="dt-modal-title" id="{{ $id }}FormTitle">{{ $primary ?? 'Add record' }}</h2>
            <button type="button" class="dt-icon-btn" data-dt-close aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="dt-modal-body">
            <p class="dt-modal-note">
                Fields are illustrative while this module runs on static data.
            </p>

            <div class="dt-field-row">
                <div class="dt-field">
                    <label for="{{ $id }}Name">Name <span class="dt-req">*</span></label>
                    <input type="text" id="{{ $id }}Name" placeholder="Enter a value">
                </div>
                <div class="dt-field">
                    <label for="{{ $id }}Status">Status</label>
                    <select id="{{ $id }}Status">
                        <option>Active</option>
                        <option>Inactive</option>
                        <option>Draft</option>
                    </select>
                </div>
            </div>

            <div class="dt-field">
                <label for="{{ $id }}Desc">Description</label>
                <textarea id="{{ $id }}Desc" rows="3"
                          placeholder="Short description shown in the app"></textarea>
            </div>
        </div>

        <div class="dt-modal-foot">
            <button type="button" class="dt-btn dt-btn--ghost" data-dt-close>Cancel</button>
            <button type="button" class="dt-btn dt-btn--primary" data-dt-close
                    data-dt-toast="Record saved">
                <i class="fa-regular fa-floppy-disk"></i> Save record
            </button>
        </div>
    </div>
</div>

{{-- View details --}}
<div class="dt-modal" id="{{ $id }}ViewModal" role="dialog" aria-modal="true"
     aria-labelledby="{{ $id }}ViewTitle" hidden>
    <div class="dt-modal-backdrop" data-dt-close></div>

    <div class="dt-modal-box">
        <div class="dt-modal-head">
            <h2 class="dt-modal-title" id="{{ $id }}ViewTitle">Record details</h2>
            <button type="button" class="dt-icon-btn" data-dt-close aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="dt-modal-body">
            <dl class="dt-kv">
                @foreach (array_slice($columns, 0, 6) as $column)
                    <div class="dt-kv-row">
                        <dt>{{ $column['label'] }}</dt>
                        <dd>{{ $rows[0][$column['key']] ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="dt-modal-foot">
            <button type="button" class="dt-btn dt-btn--ghost" data-dt-close>Close</button>
            <button type="button" class="dt-btn dt-btn--primary" data-dt-close
                    data-dt-open="{{ $id }}FormModal">
                <i class="fa-solid fa-pen"></i> Edit
            </button>
        </div>
    </div>
</div>

{{-- Delete confirmation --}}
<div class="dt-modal" id="{{ $id }}DeleteModal" role="dialog" aria-modal="true"
     aria-labelledby="{{ $id }}DeleteTitle" hidden>
    <div class="dt-modal-backdrop" data-dt-close></div>

    <div class="dt-modal-box dt-modal-box--sm">
        <div class="dt-modal-head">
            <span class="dt-modal-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
            <h2 class="dt-modal-title" id="{{ $id }}DeleteTitle">Delete this record?</h2>
            <button type="button" class="dt-icon-btn" data-dt-close aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="dt-modal-body">
            <p class="dt-modal-text">
                Are you sure you want to delete this record? This action cannot be undone.
            </p>
            <p class="dt-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                Records removed here also disappear from the Bfinz mobile app.
            </p>
        </div>

        <div class="dt-modal-foot">
            <button type="button" class="dt-btn dt-btn--ghost" data-dt-close>Cancel</button>
            <button type="button" class="dt-btn dt-btn--danger" data-dt-close
                    data-dt-toast="Record deleted">
                <i class="fa-regular fa-trash-can"></i> Delete
            </button>
        </div>
    </div>
</div>

{{-- ================= Behaviour =================
     Search, sort, modals and the toast live here rather than in app.js, so the
     table is one file end to end. Guarded so it only ever binds once, however
     many tables a page renders. --}}
@once
    @push('scripts')
    <script>
    (function () {
        'use strict';

        function openModal(id) {
            var m = document.getElementById(id);
            if (!m) { return; }
            m.hidden = false;
            document.body.style.overflow = 'hidden';
            var f = m.querySelector('input, select, textarea, button');
            if (f) { f.focus(); }
        }

        function closeModal(m) {
            if (!m) { return; }
            m.hidden = true;
            document.body.style.overflow = '';
        }

        function toast(message) {
            var host = document.querySelector('.dt-toasts');
            if (!host) {
                host = document.createElement('div');
                host.className = 'dt-toasts';
                document.body.appendChild(host);
            }

            var el = document.createElement('div');
            el.className = 'dt-toast';
            el.setAttribute('role', 'status');
            el.innerHTML = '<i class="fa-solid fa-circle-check"></i><span></span>';
            // Assigned as text: record names must never be parsed as markup.
            el.querySelector('span').textContent = message;
            host.appendChild(el);

            setTimeout(function () { el.remove(); }, 3200);
        }

        function search(input) {
            var table = document.getElementById(input.getAttribute('data-dt-search'));
            if (!table) { return; }

            var term = input.value.trim().toLowerCase();
            var rows = table.querySelectorAll('tbody tr');
            var hits = 0;

            rows.forEach(function (row) {
                if (row.classList.contains('dt-no-match')) { return; }
                var match = term === '' || row.textContent.toLowerCase().indexOf(term) !== -1;
                row.hidden = !match;
                if (match) { hits++; }
            });

            var none = table.querySelector('.dt-no-match');
            if (none) { none.hidden = hits !== 0; }

            var counter = document.querySelector('[data-dt-count="' + table.id + '"]');
            if (counter) { counter.textContent = hits; }
        }

        // "₹6,850" / "12,480" / "8.50%" -> a number, so those columns sort
        // numerically instead of alphabetically.
        function asNumber(text) {
            var cleaned = text.replace(/[^0-9.\-]/g, '');
            if (cleaned === '' || cleaned === '-' || cleaned === '.') { return null; }
            var n = parseFloat(cleaned);
            return isNaN(n) ? null : n;
        }

        function sort(th) {
            var table = th.closest('table');
            var body = table.querySelector('tbody');
            if (!body) { return; }

            var index = Array.prototype.indexOf.call(th.parentNode.children, th);
            var asc = th.getAttribute('data-dir') !== 'asc';

            table.querySelectorAll('th[data-dt-sort]').forEach(function (other) {
                if (other !== th) { other.removeAttribute('data-dir'); }
            });
            th.setAttribute('data-dir', asc ? 'asc' : 'desc');

            var rows = Array.prototype.slice.call(body.querySelectorAll('tr:not(.dt-no-match)'));

            rows.sort(function (a, b) {
                var x = (a.children[index] ? a.children[index].textContent : '').trim();
                var y = (b.children[index] ? b.children[index].textContent : '').trim();
                var nx = asNumber(x), ny = asNumber(y);
                if (nx !== null && ny !== null) { return asc ? nx - ny : ny - nx; }
                return asc ? x.localeCompare(y) : y.localeCompare(x);
            });

            rows.forEach(function (r) { body.appendChild(r); });

            // Re-appending the data rows would otherwise strand the
            // "no matching records" row at the top of the body.
            var noMatch = body.querySelector(".dt-no-match");
            if (noMatch) { body.appendChild(noMatch); }
        }

        document.addEventListener('click', function (e) {
            var el;

            el = e.target.closest('[data-dt-open]');
            if (el) {
                var current = e.target.closest('.dt-modal');
                if (current) { closeModal(current); }
                openModal(el.getAttribute('data-dt-open'));
            }

            if (e.target.closest('[data-dt-close]')) {
                closeModal(e.target.closest('.dt-modal'));
            }

            el = e.target.closest('[data-dt-toast]');
            if (el) { toast(el.getAttribute('data-dt-toast')); }

            el = e.target.closest('th[data-dt-sort]');
            if (el) { sort(el); }
        });

        document.addEventListener('input', function (e) {
            if (e.target.matches('[data-dt-search]')) { search(e.target); }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.dt-modal:not([hidden])').forEach(closeModal);
            }
        });
    })();
    </script>
    @endpush
@endonce
