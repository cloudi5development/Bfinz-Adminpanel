@props(['title' => 'Unable to load data', 'body' => 'Something went wrong while fetching this section.'])

{{--
    Reusable failure state. Static for now — it is the block the real error
    handler will render once these screens are wired to the API.
--}}
<div class="state">
    <div class="state__icon state__icon--danger" aria-hidden="true"><x-admin.icon name="alert" /></div>
    <div class="state__title">{{ $title }}</div>
    <p class="state__body">{{ $body }}</p>
    <div class="state__actions">
        <button type="button" class="btn btn--ghost"
                data-toast="Retrying" data-toast-body="Reconnecting to the data source." data-toast-tone="info">
            <x-admin.icon name="refresh" /> Try Again
        </button>
    </div>
</div>
