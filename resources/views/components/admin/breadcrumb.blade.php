@props(['items' => []])

{{-- Breadcrumb trail, e.g. Dashboard / Rates & Market / Gold Rate --}}
@if (count($items))
    <nav class="crumbs" aria-label="Breadcrumb">
        @foreach ($items as $item)
            @if (! $loop->first)
                <x-admin.icon name="chevron-right" />
            @endif

            @if (! empty($item['url']) && ! $loop->last)
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @else
                <span class="{{ $loop->last ? 'crumbs__current' : '' }}"
                      @if ($loop->last) aria-current="page" @endif>{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
