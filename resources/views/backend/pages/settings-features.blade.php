{{--
    Settings — Feature Controls.

    Per-feature kill switches for the mobile app. Each toggle maps to a module
    the app can hide without a release, which is how a degraded data source is
    handled in production.

    Cards are built by grouping on the toggle's own `group` key, so adding or
    removing a feature never shifts a positional slice.
--}}
@extends('backend.template.layouts.template-base')

@php
    use App\Support\MockData;

    $features = MockData::featureToggles();
    $groups   = collect($features)->groupBy('group');
    $enabled  = count(array_filter($features, fn ($f) => $f['on']));
    $disabled = array_values(array_filter($features, fn ($f) => ! $f['on']));
@endphp

@section('content')

    <x-admin.page-header
        title="Feature Controls"
        description="Turn app features on or off without shipping a release. Disabled features are hidden from every user.">
        <button type="button" class="btn btn--primary"
                data-toast="Feature controls saved"
                data-toast-body="Toggles take effect app-wide once the module is connected."
                data-toast-tone="success">
            <x-admin.icon name="save" /> Save changes
        </button>
    </x-admin.page-header>

    <div class="grid grid--4 u-mb-3">
        <x-admin.stat-card label="Total features" :value="count($features)" icon="sliders" tone="primary" />
        <x-admin.stat-card label="Enabled" :value="$enabled" icon="check" tone="success" />
        <x-admin.stat-card label="Disabled" :value="count($features) - $enabled" icon="x" tone="warning" />
        <x-admin.stat-card label="Last changed" value="20 Sep 2026" icon="clock" tone="royal" />
    </div>

    <div class="flash flash--info" role="status">
        <x-admin.icon name="info" />
        <div>
            Disabling a feature hides it in the app but keeps its data intact — records stay in the
            panel and reappear the moment the feature is switched back on.
        </div>
    </div>

    <div class="grid grid--2">
        @foreach ($groups as $groupName => $items)
            <div class="card">
                <div class="card__head">
                    <div>
                        <h2 class="card__title">{{ $groupName }}</h2>
                        <p class="card__desc">
                            {{ $items->where('on', true)->count() }} of {{ $items->count() }} enabled
                        </p>
                    </div>
                </div>
                <div class="card__body">
                    @foreach ($items as $feature)
                        <x-admin.toggle
                            :label="$feature['label']"
                            :description="$feature['desc']"
                            :checked="$feature['on']" />
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @if (count($disabled))
        <div class="flash flash--warning u-mt-3" style="margin-bottom:0">
            <x-admin.icon name="alert" />
            <div>
                <strong>{{ count($disabled) }} {{ Str::plural('feature', count($disabled)) }} currently disabled:</strong>
                {{ collect($disabled)->pluck('label')->join(', ', ' and ') }}.
                Users will not see {{ count($disabled) === 1 ? 'it' : 'them' }} in the app.
            </div>
        </div>
    @endif

@endsection
