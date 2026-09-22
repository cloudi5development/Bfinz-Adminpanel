@props([
    'label' => '',
    'value' => '',
    'change' => null,
    'direction' => 'up',
    'previous' => null,
    'route' => null,
    'accent' => false,
    'spark' => null,
    // Kept so the older call sites (calculators, goals, settings) keep working.
    'icon' => null,
    'tone' => null,
    'caption' => null,
])

{{--
    Headline counter.

    Layout: label and a jump-through control on the top row, the figure with its
    change pill beneath, then either the month-on-month comparison or a
    sparkline. One card per row of four is the accent card, filled in brand blue
    so the eye lands on the primary metric first.
--}}

@php
    $tag = $route ? 'a' : 'div';
    $foot = $previous ?? $caption;
@endphp

<{{ $tag }}
    @if ($route) href="{{ route($route) }}" @endif
    {{ $attributes->merge(['class' => 'stat' . ($accent ? ' stat--accent' : '') . ($route ? ' stat--link' : '')]) }}>

    <div class="stat__head">
        <span class="stat__label">{{ $label }}</span>

        @if ($route)
            <span class="stat__jump" aria-hidden="true"><x-admin.icon name="arrow-up-right" /></span>
        @elseif ($icon)
            <span class="stat__jump" aria-hidden="true"><x-admin.icon :name="$icon" /></span>
        @endif
    </div>

    <div class="stat__row">
        <span class="stat__value">{{ $value }}</span>

        @if ($change)
            <span class="stat__pill stat__pill--{{ $direction }}">{{ $change }}</span>
        @endif
    </div>

    @if ($spark)
        @php
            // Build the sparkline path in PHP so it renders without JavaScript.
            $w = 220; $h = 46;
            $max = max($spark); $min = min($spark);
            $span = max(1, $max - $min);
            $n = count($spark);

            $pts = [];
            foreach (array_values($spark) as $i => $v) {
                $x = $n > 1 ? round($i * $w / ($n - 1), 2) : $w / 2;
                $y = round($h - 4 - (($v - $min) / $span) * ($h - 10), 2);
                $pts[] = $x . ',' . $y;
            }

            $line = 'M' . implode(' L', $pts);
            $area = $line . ' L' . $w . ',' . $h . ' L0,' . $h . ' Z';
            $uid  = 'sp' . substr(md5($label), 0, 6);
        @endphp

        <svg class="stat__spark" viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none"
             role="img" aria-label="{{ $label }} trend over the last 12 months">
            <defs>
                <linearGradient id="{{ $uid }}" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="currentColor" stop-opacity=".28"/>
                    <stop offset="100%" stop-color="currentColor" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <path d="{{ $area }}" fill="url(#{{ $uid }})"/>
            <path d="{{ $line }}" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
        </svg>
    @elseif ($foot)
        <div class="stat__prev">{{ $foot }}</div>
    @endif

</{{ $tag }}>
