@props([
    'labels' => [],
    'values' => [],
    'id' => 'chart',
    'height' => 260,
    'suffix' => '',
])

{{--
    Area chart drawn as inline SVG.

    Hand-built rather than pulled from a charting library: the series here are
    small, the visual is fixed, and this keeps the panel free of a ~200 KB
    dependency for one shape. The path is generated in PHP, so it renders with
    JavaScript disabled and prints correctly.
--}}

@php
    $values = array_values($values);
    $count  = count($values);

    // Geometry of the drawing surface, in viewBox units.
    $w = 640; $h = 240;
    $padL = 44; $padR = 12; $padT = 14; $padB = 30;

    $plotW = $w - $padL - $padR;
    $plotH = $h - $padT - $padB;

    $max = $count ? max($values) : 1;
    // Round the axis up to something readable rather than the raw maximum.
    $step  = max(1, (int) pow(10, max(0, strlen((string) (int) $max) - 2)));
    $top   = max(1, (int) (ceil($max / (5 * $step)) * 5 * $step));

    $points = [];
    foreach ($values as $i => $value) {
        $x = $count > 1 ? $padL + ($i * $plotW / ($count - 1)) : $padL + $plotW / 2;
        $y = $padT + $plotH - ($value / $top * $plotH);
        $points[] = ['x' => round($x, 2), 'y' => round($y, 2), 'v' => $value];
    }

    $line = '';
    foreach ($points as $i => $p) {
        $line .= ($i === 0 ? 'M' : 'L') . $p['x'] . ',' . $p['y'] . ' ';
    }

    $area = $line . 'L' . end($points)['x'] . ',' . ($padT + $plotH)
          . ' L' . $points[0]['x'] . ',' . ($padT + $plotH) . ' Z';

    $gridLines = 4;
    $uid = $id . '-' . substr(md5(implode(',', $values)), 0, 6);
@endphp

<svg class="chart" viewBox="0 0 {{ $w }} {{ $h }}" style="height:{{ $height }}px"
     preserveAspectRatio="none" role="img"
     aria-label="{{ $slot->isEmpty() ? 'Trend chart' : strip_tags($slot) }}">

    <defs>
        <linearGradient id="fill-{{ $uid }}" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%"   stop-color="#0066FF" stop-opacity=".26"/>
            <stop offset="100%" stop-color="#0066FF" stop-opacity="0"/>
        </linearGradient>
        <linearGradient id="stroke-{{ $uid }}" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%"   stop-color="#0066FF"/>
            <stop offset="100%" stop-color="#1747C8"/>
        </linearGradient>
    </defs>

    {{-- Horizontal grid + y-axis labels --}}
    @for ($g = 0; $g <= $gridLines; $g++)
        @php
            $y   = $padT + ($g * $plotH / $gridLines);
            $val = $top - ($g * $top / $gridLines);
        @endphp
        <line x1="{{ $padL }}" y1="{{ round($y, 2) }}" x2="{{ $w - $padR }}" y2="{{ round($y, 2) }}"
              stroke="#EDF2F9" stroke-width="1" vector-effect="non-scaling-stroke"/>
        <text x="{{ $padL - 8 }}" y="{{ round($y + 3.5, 2) }}" text-anchor="end"
              font-size="10" fill="#94A3B8" font-family="inherit">
            {{ $val >= 1000 ? round($val / 1000, 1) . 'k' : (int) $val }}
        </text>
    @endfor

    {{-- Area + line --}}
    <path d="{{ $area }}" fill="url(#fill-{{ $uid }})"/>
    <path d="{{ trim($line) }}" fill="none" stroke="url(#stroke-{{ $uid }})"
          stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
          vector-effect="non-scaling-stroke"/>

    {{-- Points + x-axis labels --}}
    @foreach ($points as $i => $p)
        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5" fill="#fff"
                stroke="#0066FF" stroke-width="2" vector-effect="non-scaling-stroke">
            <title>{{ $labels[$i] ?? '' }}: {{ number_format($p['v']) }}{{ $suffix }}</title>
        </circle>
        <text x="{{ $p['x'] }}" y="{{ $h - 9 }}" text-anchor="middle"
              font-size="10" fill="#94A3B8" font-family="inherit">{{ $labels[$i] ?? '' }}</text>
    @endforeach
</svg>
