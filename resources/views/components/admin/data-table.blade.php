@props([
    'id' => 'table',
    'columns' => [],
    'rows' => [],
    'filters' => [],
    'search' => 'Search records',
    'primary' => null,
    'export' => true,
    'total' => null,
    'empty' => [],
    'actions' => true,
])

{{--
    The panel's one list component: toolbar (search, filters, export, primary
    action), sortable table, row actions, empty state and pagination.

    Every list screen renders through this, which is what keeps 46 modules
    visually identical. Column `type` decides how a cell is drawn — see
    App\Support\MockTables for the vocabulary.

    Sorting and search are client-side (app.js) while the rows are static; both
    become server-side parameters when the data is real, with no markup change.
--}}

@php
    $emptyTitle  = $empty['title'] ?? 'No records found';
    $emptyBody   = $empty['body'] ?? 'Nothing matches the current filters yet.';
    $colCount    = count($columns) + ($actions ? 1 : 0);
    $searchId    = $id . 'Search';
@endphp

<div class="card">

    {{-- Toolbar ------------------------------------------------------- --}}
    <div class="toolbar">
        <div class="toolbar__search">
            <x-admin.icon name="search" />
            <label for="{{ $searchId }}" class="visually-hidden">{{ $search }}</label>
            <input type="search"
                   id="{{ $searchId }}"
                   class="input"
                   placeholder="{{ $search }}"
                   autocomplete="off"
                   data-table-search="#{{ $id }}">
        </div>

        @foreach ($filters as $filter)
            @php $filterId = $id . '-filter-' . $loop->index; @endphp
            <label for="{{ $filterId }}" class="visually-hidden">{{ $filter['label'] }}</label>
            <select class="select" id="{{ $filterId }}" style="width:auto;min-width:142px">
                <option value="">{{ $filter['label'] }}: All</option>
                @foreach ($filter['options'] as $option)
                    <option>{{ $option }}</option>
                @endforeach
            </select>
        @endforeach

        <div class="toolbar__spacer"></div>

        @if ($export)
            <button type="button" class="btn btn--ghost btn--sm"
                    data-toast="Export queued"
                    data-toast-body="CSV export will be generated once the module is connected to the API."
                    data-toast-tone="info">
                <x-admin.icon name="download" /> Export
            </button>
        @endif

        @if ($primary)
            <button type="button" class="btn btn--primary btn--sm" data-modal-open="{{ $id }}AddModal">
                <x-admin.icon name="plus" /> {{ $primary }}
            </button>
        @endif
    </div>

    {{-- Loading placeholder: hidden while rows are static, ready for the
         real fetch state later. --}}
    <div class="u-hide" data-table-loading>
        <x-admin.skeleton type="table" :rows="6" />
    </div>

    {{-- Table --------------------------------------------------------- --}}
    <div class="table-wrap">
        <table class="data" id="{{ $id }}">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        @php $isAmount = ($column['type'] ?? '') === 'amount'; @endphp
                        <th data-sort scope="col"
                            class="is-sortable {{ $isAmount ? 'is-amount' : '' }}">
                            <span class="th-inner">
                                {{ $column['label'] }}
                                <x-admin.icon name="sort" />
                            </span>
                        </th>
                    @endforeach

                    @if ($actions)
                        <th class="is-actions" scope="col">Actions</th>
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
                                'strong' => 'is-strong',
                                'muted'  => 'is-muted',
                                'mono'   => 'is-mono',
                                'amount' => 'is-amount',
                                default  => '',
                            } }}">
                                @switch($type)
                                    @case('entity')
                                        <div class="cell-entity">
                                            <span class="avatar avatar--sm" aria-hidden="true">{{ mb_strtoupper(mb_substr($value, 0, 1)) }}</span>
                                            <span>
                                                <span class="cell-entity__name">{{ $value }}</span>
                                                @if ($sub)
                                                    <br><span class="cell-entity__sub">{{ $sub }}</span>
                                                @endif
                                            </span>
                                        </div>
                                        @break

                                    @case('badge')
                                        <x-admin.status-badge :status="$value" />
                                        @break

                                    @case('chip')
                                        <span class="chip">{{ $value }}</span>
                                        @break

                                    @case('rating')
                                        <span class="stars" aria-label="{{ $value }} out of 5">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <x-admin.icon name="star"
                                                    class="{{ $s <= (int) $value ? '' : 'is-off' }}"
                                                    fill="{{ $s <= (int) $value ? 'currentColor' : 'none' }}" />
                                            @endfor
                                        </span>
                                        @break

                                    @case('delta')
                                        @php
                                            $dir = str_starts_with((string) $value, '-') ? 'down'
                                                 : (str_starts_with((string) $value, '+') ? 'up' : 'flat');
                                        @endphp
                                        <x-admin.delta :value="$value" :direction="$dir" />
                                        @break

                                    @case('muted')
                                        <span class="cell-truncate">{{ $value }}</span>
                                        @break

                                    @default
                                        {{ $value }}
                                @endswitch
                            </td>
                        @endforeach

                        @if ($actions)
                            <td class="is-actions">
                                <div class="row-actions">
                                    <button type="button" class="row-btn" title="View details" aria-label="View details"
                                            data-modal-open="{{ $id }}ViewModal">
                                        <x-admin.icon name="eye" />
                                    </button>
                                    <button type="button" class="row-btn" title="Edit record" aria-label="Edit record"
                                            data-modal-open="{{ $id }}AddModal">
                                        <x-admin.icon name="edit" />
                                    </button>
                                    <button type="button" class="row-btn row-btn--danger" title="Delete record" aria-label="Delete record"
                                            data-modal-open="{{ $id }}DeleteModal">
                                        <x-admin.icon name="trash" />
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $colCount }}" style="padding:0">
                            <x-admin.empty-state :title="$emptyTitle" :body="$emptyBody" :action="$primary" />
                        </td>
                    </tr>
                @endforelse

                {{-- Revealed by app.js when a search filters every row out. --}}
                <tr data-empty-row style="display:none">
                    <td colspan="{{ $colCount }}" style="padding:0">
                        <x-admin.empty-state
                            title="No matching records"
                            body="No rows match your search. Try a different term or clear the filters."
                            icon="search" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Footer -------------------------------------------------------- --}}
    @if (count($rows))
        <div class="card__foot">
            <x-admin.pagination :shown="count($rows)" :total="$total ?? count($rows)" :pages="5" />
        </div>
    @endif

</div>

{{-- Modals shared by every row in this table ------------------------- --}}
<x-admin.modal :id="$id . 'AddModal'" :title="$primary ?? 'Add record'"
               description="Fields are illustrative while the module runs on static data.">
    <div class="field-row">
        <div class="field">
            <label class="field__label" for="{{ $id }}-f1">Name <span class="field__req">*</span></label>
            <input class="input" id="{{ $id }}-f1" placeholder="Enter a value">
        </div>
        <div class="field">
            <label class="field__label" for="{{ $id }}-f2">Status</label>
            <select class="select" id="{{ $id }}-f2">
                <option>Active</option>
                <option>Inactive</option>
                <option>Draft</option>
            </select>
        </div>
    </div>
    <div class="field">
        <label class="field__label" for="{{ $id }}-f3">Description</label>
        <textarea class="textarea" id="{{ $id }}-f3" placeholder="Short description shown in the app"></textarea>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn--ghost" data-modal-close>Cancel</button>
        <button type="button" class="btn btn--primary" data-modal-close
                data-toast="Saved" data-toast-body="Persisting records arrives with the API phase." data-toast-tone="success">
            <x-admin.icon name="save" /> Save record
        </button>
    </x-slot:footer>
</x-admin.modal>

<x-admin.modal :id="$id . 'ViewModal'" title="Record details"
               description="A read-only view of the selected row.">
    <div class="kv">
        @foreach (array_slice($columns, 0, 6) as $column)
            <div class="kv__row">
                <span class="kv__k">{{ $column['label'] }}</span>
                <span class="kv__v">{{ $rows[0][$column['key']] ?? '—' }}</span>
            </div>
        @endforeach
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn--ghost" data-modal-close>Close</button>
        <button type="button" class="btn btn--primary" data-modal-close data-modal-open="{{ $id }}AddModal">
            <x-admin.icon name="edit" /> Edit
        </button>
    </x-slot:footer>
</x-admin.modal>

<x-admin.confirm-dialog :id="$id . 'DeleteModal'" />
