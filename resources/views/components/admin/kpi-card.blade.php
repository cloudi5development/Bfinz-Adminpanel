@props([
    'title' => '',
    'value' => '',
    'icon' => 'chart',
    'variant' => 'plain',
    'route' => null,
    'spark' => null,
])

{{--
    Dashboard KPI card.

    The card's top-right corner is cut away by a concave sweep with a round
    action button nesting in the cut-out:

        ┌────────────────╮      ← top edge eases into the sweep
        │                 ╲___  ← concave arc around the button
        │                     ╲
        │        CARD          │
        └──────────────────────┘

    A radial-gradient mask cannot draw this, because subtracting a disc only
    ever yields concave arcs — the points where it crosses the card's top and
    right edges stay as sharp 90° cusps. So the corner is drawn as a real path
    with a fillet at each end, tangent to both the straight edge and the sweep,
    which is what makes the joins read as smooth.

        .kpi__card    the body, with a rectangular bite masked out top-right
        .kpi__corner  the SVG that fills that bite with the shaped corner
        .kpi__fab     the round button sitting in the cut-out

    The wrapper keeps `overflow: visible` and carries the shadow through
    `filter: drop-shadow()`, so the shadow traces the shaped silhouette rather
    than a plain rectangle.
--}}

@php
    // Corner geometry, in CSS pixels. Keep in step with --kpi-corner-* in the
    // stylesheet: the mask cuts the hole, this draws what fills it.
    $cw = 86;    // corner piece width
    $ch = 48;    // corner piece height
    $r  = 26;    // radius of the concave sweep — nests the 36px button
    $nx = 65;    // sweep centre, measured from the piece's left edge (y = 0)
    $f  = 9;     // fillet radius at each end of the sweep

    // Left fillet: tangent to the top edge and to the sweep, so its centre sits
    // f below the edge and (r + f) from the sweep's centre.
    $f1x = $nx - sqrt(($r + $f) ** 2 - $f ** 2);
    $d1  = sqrt(($nx - $f1x) ** 2 + $f ** 2);
    $p1x = $f1x + $f * ($nx - $f1x) / $d1;
    $p1y = $f   + $f * (0 - $f) / $d1;

    // Right fillet: tangent to the right edge and to the sweep.
    $f2x = $cw - $f;
    $f2y = sqrt(($r + $f) ** 2 - ($f2x - $nx) ** 2);
    $d2  = sqrt(($nx - $f2x) ** 2 + $f2y ** 2);
    $p2x = $f2x + $f * ($nx - $f2x) / $d2;
    $p2y = $f2y + $f * (0 - $f2y) / $d2;

    $n = fn ($v) => round($v, 2);

    $path = implode(' ', [
        'M0,0',
        'L' . $n($f1x) . ',0',                                          // top edge
        'A' . $f . ',' . $f . ' 0 0 1 ' . $n($p1x) . ',' . $n($p1y),    // ease in
        'A' . $r . ',' . $r . ' 0 0 0 ' . $n($p2x) . ',' . $n($p2y),    // the sweep
        'A' . $f . ',' . $f . ' 0 0 1 ' . $cw . ',' . $n($f2y),         // ease out
        'L' . $cw . ',' . $ch,                                          // right edge
        'L0,' . $ch,
        'Z',
    ]);

    $tag = $route ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($route) href="{{ route($route) }}" @endif
    {{ $attributes->merge(['class' => 'kpi kpi--' . $variant . ($route ? ' kpi--link' : '')]) }}>

    {{-- Fills the masked bite with the shaped corner.

         Two constraints fix where this lives. It cannot go inside .kpi__card,
         because a mask clips its descendants and it would land in the very
         hole the mask cuts. And it must paint BEFORE the card, or its solid
         lower half covers the card's own title text. So: wrapper, first. --}}
    <svg class="kpi__corner" viewBox="0 0 {{ $cw }} {{ $ch }}"
         width="{{ $cw }}" height="{{ $ch }}" aria-hidden="true" focusable="false">
        <path d="{{ $path }}" fill="currentColor"/>
    </svg>

    <div class="kpi__card">

        <div class="kpi__head">
            <span class="kpi__icon" aria-hidden="true"><x-admin.icon :name="$icon" /></span>
            <span class="kpi__title">{{ $title }}</span>
        </div>

        <div class="kpi__value">{{ $value }}</div>

        @if ($spark)
            @php
                // Drawn in PHP so the card needs no JavaScript to render.
                $w = 150; $h = 40;
                $vals = array_values($spark);
                $max = max($vals); $min = min($vals);
                $range = max(1, $max - $min);
                $count = count($vals);

                $pts = [];
                foreach ($vals as $i => $v) {
                    $x = $count > 1 ? round($i * $w / ($count - 1), 2) : $w / 2;
                    $y = round($h - 3 - (($v - $min) / $range) * ($h - 8), 2);
                    $pts[] = $x . ',' . $y;
                }

                $line = 'M' . implode(' L', $pts);
                $area = $line . ' L' . $w . ',' . $h . ' L0,' . $h . ' Z';
                $uid  = 'kpi' . substr(md5($title), 0, 6);
            @endphp

            <svg class="kpi__spark" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none"
                 role="img" aria-label="{{ $title }} trend">
                <defs>
                    <linearGradient id="{{ $uid }}" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="currentColor" stop-opacity=".30"/>
                        <stop offset="100%" stop-color="currentColor" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="{{ $area }}" fill="url(#{{ $uid }})"/>
                <path d="{{ $line }}" fill="none" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
            </svg>
        @endif

    </div>

    {{-- The button nests in the cut-out. It lives in the wrapper, not the card,
         so the card's own clipping can never cut it. --}}
    <span class="kpi__fab" aria-hidden="true"><x-admin.icon name="arrow-up-right" /></span>
</{{ $tag }}>
