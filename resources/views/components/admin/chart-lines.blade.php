@props([
    'labels' => [],
    'series' => [],
    'highlight' => null,
    'height' => 250,
])

{{--
    Multi-series line chart with smooth curves.

    The curves are Catmull-Rom splines converted to cubic béziers, not straight
    polylines — a polyline through ten weekly points reads as a jagged zig-zag,
    which is exactly what the reference avoids.

    Only series flagged `on` are drawn; the rest stay in the data so the toggles
    above the chart have something to switch to later.
--}}

@php
    // viewBox width sits near the chart's real rendered width, so the uniform
    // scale stays close to 1 and the labels render at their intended size.
    $w = 760; $h = 280;
    // padR clears the last x-axis label, which is centred on the final point.
    $padL = 50; $padR = 44; $padT = 26; $padB = 36;
    $plotW = $w - $padL - $padR;
    $plotH = $h - $padT - $padB;

    $visible = array_values(array_filter($series, fn ($s) => ! empty($s['on'])));
    $all = [];
    foreach ($visible as $s) {
        $all = array_merge($all, $s['values']);
    }

    $max = $all ? max($all) : 1;
    // Round the axis up to a readable step so the gridlines land on whole numbers.
    $step = (int) max(1, pow(10, max(0, strlen((string) (int) $max) - 1)) / 2);
    $top  = (int) (ceil($max / (4 * $step)) * 4 * $step);
    $top  = max($top, 1);

    $count = count($labels);
    $xAt = fn ($i) => $count > 1 ? $padL + ($i * $plotW / ($count - 1)) : $padL + $plotW / 2;
    $yAt = fn ($v) => $padT + $plotH - ($v / $top * $plotH);

    /**
     * Catmull-Rom through the points, emitted as cubic béziers. The /6 is the
     * standard tension: control points sit a sixth of the way along the
     * neighbouring span, which keeps the curve from overshooting.
     */
    $smooth = function (array $pts) {
        if (count($pts) < 2) {
            return '';
        }

        $d = 'M' . round($pts[0][0], 2) . ',' . round($pts[0][1], 2);

        for ($i = 0; $i < count($pts) - 1; $i++) {
            $p0 = $pts[max(0, $i - 1)];
            $p1 = $pts[$i];
            $p2 = $pts[$i + 1];
            $p3 = $pts[min(count($pts) - 1, $i + 2)];

            $c1x = $p1[0] + ($p2[0] - $p0[0]) / 6;
            $c1y = $p1[1] + ($p2[1] - $p0[1]) / 6;
            $c2x = $p2[0] - ($p3[0] - $p1[0]) / 6;
            $c2y = $p2[1] - ($p3[1] - $p1[1]) / 6;

            $d .= sprintf(' C%.2f,%.2f %.2f,%.2f %.2f,%.2f', $c1x, $c1y, $c2x, $c2y, $p2[0], $p2[1]);
        }

        return $d;
    };

    $lines = [];
    foreach ($visible as $s) {
        $pts = [];
        foreach (array_values($s['values']) as $i => $v) {
            $pts[] = [$xAt($i), $yAt($v)];
        }
        $lines[] = ['colour' => $s['colour'], 'name' => $s['name'], 'd' => $smooth($pts), 'pts' => $pts];
    }

    // The callout bubble, pinned to one point on one series.
    $tip = null;
    if ($highlight && isset($lines[$highlight['series']]['pts'][$highlight['index']])) {
        [$hx, $hy] = $lines[$highlight['series']]['pts'][$highlight['index']];
        $tip = [
            'x' => $hx, 'y' => $hy,
            'colour' => $lines[$highlight['series']]['colour'],
            'label' => $highlight['label'], 'sub' => $highlight['sub'] ?? '',
        ];
    }

    $gridLines = 4;
    $fmt = fn ($v) => $v >= 1000 ? rtrim(rtrim(number_format($v / 1000, 1), '0'), '.') . 'k' : (string) (int) $v;
@endphp

{{-- Scaled uniformly, NOT preserveAspectRatio="none": stretching the viewBox
     to fill a fixed height would distort every label and the callout text. --}}
<svg class="chart-lines" viewBox="0 0 {{ $w }} {{ $h }}" role="img"
     aria-label="{{ collect($visible)->pluck('name')->implode(' and ') }} over {{ $count }} weeks">

    {{-- Horizontal grid + y-axis labels --}}
    @for ($g = 0; $g <= $gridLines; $g++)
        @php
            $y = $padT + ($g * $plotH / $gridLines);
            $val = $top - ($g * $top / $gridLines);
        @endphp
        <line x1="{{ $padL }}" y1="{{ round($y, 2) }}" x2="{{ $w - $padR }}" y2="{{ round($y, 2) }}"
              stroke="#EDF2F9" stroke-width="1" vector-effect="non-scaling-stroke"/>
        @if ($val > 0)
            <text x="{{ $padL - 10 }}" y="{{ round($y + 3.5, 2) }}" text-anchor="end"
                  font-size="10" fill="#94A3B8" font-family="inherit">{{ $fmt($val) }}</text>
        @endif
    @endfor

    {{-- The curves --}}
    @foreach ($lines as $line)
        <path d="{{ $line['d'] }}" fill="none" stroke="{{ $line['colour'] }}"
              stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
              vector-effect="non-scaling-stroke"/>
    @endforeach

    {{-- Callout --}}
    @if ($tip)
        <line x1="{{ round($tip['x'], 2) }}" y1="{{ round($tip['y'], 2) }}"
              x2="{{ round($tip['x'], 2) }}" y2="{{ $padT + $plotH }}"
              stroke="{{ $tip['colour'] }}" stroke-width="1" stroke-dasharray="3 3"
              opacity=".45" vector-effect="non-scaling-stroke"/>

        <g transform="translate({{ round($tip['x'], 2) }} {{ round($tip['y'], 2) }})">
            <rect x="-44" y="-52" width="88" height="36" rx="9" fill="#FFFFFF"
                  stroke="#E2E8F0" stroke-width="1" vector-effect="non-scaling-stroke"/>
            <text x="0" y="-37" text-anchor="middle" font-size="12" font-weight="700"
                  fill="#111827" font-family="inherit">{{ $tip['label'] }}</text>
            <text x="0" y="-25" text-anchor="middle" font-size="9"
                  fill="#64748B" font-family="inherit">{{ $tip['sub'] }}</text>
            <circle cx="0" cy="0" r="5.5" fill="{{ $tip['colour'] }}"
                    stroke="#FFFFFF" stroke-width="2.5" vector-effect="non-scaling-stroke"/>
        </g>
    @endif

    {{-- X-axis labels --}}
    @foreach ($labels as $i => $label)
        @php $isHot = $highlight && $i === $highlight['index']; @endphp
        <text x="{{ round($xAt($i), 2) }}" y="{{ $h - 12 }}" text-anchor="middle"
              font-size="10" font-family="inherit"
              fill="{{ $isHot ? '#111827' : '#94A3B8' }}"
              font-weight="{{ $isHot ? '700' : '400' }}">{{ $label }}</text>
    @endforeach
</svg>
