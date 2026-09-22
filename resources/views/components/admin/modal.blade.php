@props([
    'id' => 'modal',
    'title' => '',
    'description' => null,
    'icon' => null,
    'tone' => 'primary',
    'size' => null,
])

{{--
    Reusable dialog. Opened with [data-modal-open="<id>"] from anywhere on the
    page and closed with [data-modal-close], the overlay, or Escape (app.js).
--}}
<div class="modal" id="{{ $id }}" role="dialog" aria-modal="true"
     aria-labelledby="{{ $id }}Title" aria-hidden="true">

    <div class="modal__overlay" data-modal-close></div>

    <div class="modal__dialog {{ $size === 'lg' ? 'modal__dialog--lg' : '' }}">

        <div class="modal__head">
            @if ($icon)
                <span class="modal__icon modal__icon--{{ $tone }}" aria-hidden="true">
                    <x-admin.icon :name="$icon" />
                </span>
            @endif

            <div class="u-grow">
                <h2 class="modal__title" id="{{ $id }}Title">{{ $title }}</h2>
                @if ($description)
                    <p class="modal__desc">{{ $description }}</p>
                @endif
            </div>

            <button type="button" class="icon-btn modal__close" data-modal-close aria-label="Close dialog">
                <x-admin.icon name="x" />
            </button>
        </div>

        <div class="modal__body">{{ $slot }}</div>

        @isset($footer)
            <div class="modal__foot">{{ $footer }}</div>
        @endisset

    </div>
</div>
