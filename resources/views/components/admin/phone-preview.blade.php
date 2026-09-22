@props([
    'title' => 'Bfinz',
    'subtitle' => null,
    'heroLabel' => null,
    'heroValue' => null,
    'rows' => [],
    'cta' => null,
    'note' => 'Approximate rendering in the Bfinz mobile app.',
])

{{--
    Mobile app preview.

    The admin panel manages a mobile product, so content screens show roughly
    how the record will look to an app user. It is a static approximation, not a
    live render — enough to catch a truncated title or an empty field before
    publishing.
--}}

<div class="preview">
    <div class="phone" role="img" aria-label="Preview of how this appears in the Bfinz mobile app">
        <div class="phone__screen">

            <div class="phone__bar">
                <div class="phone__notch" aria-hidden="true"><span></span></div>
                <div class="phone__bar-title">{{ $title }}</div>
                @if ($subtitle)
                    <div class="phone__bar-sub">{{ $subtitle }}</div>
                @endif
            </div>

            <div class="phone__body">
                @if ($heroValue)
                    <div class="phone__hero">
                        @if ($heroLabel)
                            <div class="phone__hero-label">{{ $heroLabel }}</div>
                        @endif
                        <div class="phone__hero-value">{{ $heroValue }}</div>
                    </div>
                @endif

                @if (count($rows))
                    <div class="phone__list">
                        @foreach ($rows as $row)
                            <div class="phone__row">
                                <span class="phone__row-k">{{ $row[0] }}</span>
                                <span class="phone__row-v">{{ $row[1] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{ $slot }}

                @if ($cta)
                    <div class="phone__cta">{{ $cta }}</div>
                @endif
            </div>

        </div>
    </div>

    @if ($note)
        <p class="phone__note">{{ $note }}</p>
    @endif
</div>
