@props(['page' => 1, 'pages' => 5, 'total' => null, 'shown' => null])

{{--
    Pagination UI. The page numbers are presentational while the panel serves
    static rows; swapping this for {{ $records->links() }} is the only change
    needed once the lists are paginated by Eloquent.
--}}
<div class="u-between u-wrap" style="width:100%">
    <span class="table-foot-note">
        Showing <strong data-row-count>{{ $shown ?? 0 }}</strong>
        @if ($total) of <strong>{{ number_format($total) }}</strong> @endif
        records
    </span>

    <nav class="pagination" aria-label="Pagination">
        <button type="button" disabled aria-label="Previous page"><x-admin.icon name="chevron-left" /></button>
        @for ($i = 1; $i <= min(3, $pages); $i++)
            <button type="button" class="{{ $i === (int) $page ? 'is-current' : '' }}"
                    @if ($i === (int) $page) aria-current="page" @endif>{{ $i }}</button>
        @endfor
        @if ($pages > 4)
            <span class="page is-gap">…</span>
            <button type="button">{{ $pages }}</button>
        @endif
        <button type="button" aria-label="Next page"><x-admin.icon name="chevron-right" /></button>
    </nav>
</div>
