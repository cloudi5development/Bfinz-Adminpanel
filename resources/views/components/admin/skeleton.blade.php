@props(['type' => 'table', 'rows' => 5])

{{--
    Loading placeholders. Rendered inside a hidden wrapper on list pages so the
    real loading state is a class toggle away once data is fetched.
--}}
@if ($type === 'stats')
    <div class="grid grid--stats">
        @for ($i = 0; $i < 6; $i++)
            <div class="skel skel--stat"></div>
        @endfor
    </div>
@elseif ($type === 'chart')
    <div class="skel skel--chart"></div>
@elseif ($type === 'cards')
    <div class="grid grid--3">
        @for ($i = 0; $i < 3; $i++)
            <div class="skel" style="height:148px;border-radius:var(--radius)"></div>
        @endfor
    </div>
@else
    <div class="skel-rows">
        @for ($i = 0; $i < (int) $rows; $i++)
            <div class="skel-row">
                <div class="skel skel--avatar"></div>
                <div class="skel skel--line"></div>
                <div class="skel skel--line" style="width:70%"></div>
                <div class="skel skel--chip"></div>
            </div>
        @endfor
    </div>
@endif
