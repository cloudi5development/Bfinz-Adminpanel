@props(['title' => '', 'description' => null])

<div class="section-head">
    <div>
        <h2 class="section-head__title">{{ $title }}</h2>
        @if ($description)
            <p class="section-head__desc">{{ $description }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="u-row u-row--sm">{{ $slot }}</div>
    @endif
</div>
