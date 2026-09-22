@props(['data' => [], 'suffix' => ''])

{{-- Horizontal bar chart. Plain divs rather than a charting library — it is
     lighter, themable with CSS variables and prints correctly. --}}
@php
    $max = max(1, max(array_map(fn ($d) => $d['value'], $data ?: [['value' => 1]])));
@endphp

<div class="chart-bars">
    @foreach ($data as $item)
        @php $pct = round(($item['value'] / $max) * 100, 1); @endphp
        <div class="bar-row">
            <span class="bar-row__label" title="{{ $item['label'] }}">{{ $item['label'] }}</span>
            <span class="bar-row__track">
                <span class="bar-row__fill"
                      style="width:{{ $pct }}%;animation-delay:{{ $loop->index * 60 }}ms"
                      role="img"
                      aria-label="{{ $item['label'] }}: {{ number_format($item['value']) }}{{ $suffix }}"></span>
            </span>
            <span class="bar-row__value">{{ number_format($item['value']) }}{{ $suffix }}</span>
        </div>
    @endforeach
</div>
