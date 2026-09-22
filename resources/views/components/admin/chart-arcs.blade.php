@props(['data' => []])

{{--
    Concentric semicircular arcs — one ring per metric, each sweeping from the
    left in proportion to its percentage.

    Concentric rings rather than a pie because the percentages are independent
    (each is a share of active users, not a slice of one total), so they do not
    and should not sum to 100.

    Geometry is computed in PHP, so it renders without JavaScript.
--}}

@php
    // Drawing surface. cy sits on the baseline, so the arcs occupy the top half.
    $w = 210; $h = 116;
    $cx = 105; $cy = 106;
    $stroke = 11;
    $outer = 92;          // radius of the first ring
    $step = 16;           // ring pitch: stroke + gap

    $rings = [];
    foreach (array_values($data) as $i => $item) {
        $r = $outer - ($i * $step);
        if ($r < $stroke) {
            continue;
        }

        // A full semicircle is 180°; each ring sweeps that fraction of it,
        // centred on the top so the rings read as a balanced fan rather than
        // bunching against the left edge.
        $pct  = max(0, min(100, (float) $item['percent']));
        $half = (M_PI * $pct / 100) / 2;
        $start = (M_PI / 2) + $half;
        $end   = (M_PI / 2) - $half;

        $x1 = $cx + $r * cos($start);
        $y1 = $cy - $r * sin($start);
        $x2 = $cx + $r * cos($end);
        $y2 = $cy - $r * sin($end);

        $rings[] = [
            'colour' => $item['colour'],
            'label'  => $item['label'],
            'pct'    => $pct,
            'path'   => sprintf('M%.2f,%.2f A%d,%d 0 0 1 %.2f,%.2f', $x1, $y1, $r, $r, $x2, $y2),
            // A sweep of 0 would still paint a round cap, so suppress it.
            'draw'   => $pct > 0.5,
        ];
    }
@endphp

<svg class="chart-arcs" viewBox="0 0 {{ $w }} {{ $h }}" role="img"
     aria-label="{{ collect($data)->map(fn ($d) => $d['label'] . ' ' . $d['percent'] . '%')->implode(', ') }}">
    @foreach ($rings as $ring)
        @if ($ring['draw'])
            <path d="{{ $ring['path'] }}" fill="none" stroke="{{ $ring['colour'] }}"
                  stroke-width="{{ $stroke }}" stroke-linecap="round">
                <title>{{ $ring['label'] }}: {{ $ring['pct'] }}%</title>
            </path>
        @endif
    @endforeach
</svg>
