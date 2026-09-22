@props(['value' => '', 'direction' => 'flat'])

{{-- A signed change with a matching arrow: +12.5% up, -2.3% down. --}}
<span class="delta delta--{{ $direction }}">
    @if ($direction === 'up')
        <x-admin.icon name="arrow-up" />
    @elseif ($direction === 'down')
        <x-admin.icon name="arrow-down" />
    @endif
    {{ $value }}
</span>
