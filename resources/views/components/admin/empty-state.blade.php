@props([
    'title' => 'No records found',
    'body' => null,
    'icon' => 'inbox',
    'action' => null,
])

{{-- Shown instead of a blank screen when a list has nothing in it. --}}
<div class="state">
    <div class="state__icon" aria-hidden="true"><x-admin.icon :name="$icon" /></div>
    <div class="state__title">{{ $title }}</div>
    @if ($body)
        <p class="state__body">{{ $body }}</p>
    @endif
    @if ($action)
        <div class="state__actions">
            <button type="button" class="btn btn--primary"
                    data-toast="{{ $action }}"
                    data-toast-body="This form is part of the next phase — the panel is in its UI stage."
                    data-toast-tone="info">
                <x-admin.icon name="plus" /> {{ $action }}
            </button>
        </div>
    @endif
    {{ $slot }}
</div>
