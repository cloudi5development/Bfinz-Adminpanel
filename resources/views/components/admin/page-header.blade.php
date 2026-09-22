@props(['title' => '', 'description' => null])

{{-- Page title, optional description, and an actions slot on the right. --}}
<div class="page-head">
    <div>
        <h1 class="page-head__title">{{ $title }}</h1>
        @if ($description)
            <p class="page-head__desc">{{ $description }}</p>
        @endif
    </div>

    @if (! $slot->isEmpty())
        <div class="page-head__actions">{{ $slot }}</div>
    @endif
</div>
