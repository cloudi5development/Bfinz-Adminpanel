{{--
    Settings — Market Rates.

    Today's gold/silver spot price (INR per troy ounce), read by
    App\Providers\Data\AdminManualMetalRateProvider — the placeholder metal
    rate source until a real provider (Metals-API / GoldAPI.io) is chosen
    (docs/BUILD_SPEC.md §13 Q2). App\Jobs\SyncMetalRates runs on its normal
    schedule and derives 24k/22k/18k (gold) and 999 (silver) per-gram rates,
    plus any per-city premiums, from whatever is saved here.
--}}
@extends('backend.template.layouts.template-base')

@section('content')

    <form method="POST" action="{{ route('backend.settings.market-rates.update') }}">
        @csrf

        <x-admin.page-header
            title="Market Rates"
            description="Today's gold/silver spot price, used to derive per-gram rates for the app.">
            <button type="submit" class="btn btn--primary">
                <x-admin.icon name="save" /> Save rates
            </button>
        </x-admin.page-header>

        <div class="card">
            <div class="card__head">
                <div>
                    <h2 class="card__title">Spot price (INR per troy ounce)</h2>
                    <p class="card__desc">Picked up by the next scheduled sync (see Sync Runs) — no manual re-run needed.</p>
                </div>
            </div>

            <div class="card__body">
                <div class="field-row">
                    <div class="field">
                        <label class="field__label" for="gold_spot">Gold (XAU/INR per oz)</label>
                        <input class="input" type="number" step="0.01" min="0" id="gold_spot"
                               name="market[gold_spot_inr_per_oz]"
                               value="{{ old('market.gold_spot_inr_per_oz', App\Models\Setting::get('market.gold_spot_inr_per_oz')) }}">
                        @error('market.gold_spot_inr_per_oz') <p class="field__hint" style="color:#c0392b">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label class="field__label" for="silver_spot">Silver (XAG/INR per oz)</label>
                        <input class="input" type="number" step="0.01" min="0" id="silver_spot"
                               name="market[silver_spot_inr_per_oz]"
                               value="{{ old('market.silver_spot_inr_per_oz', App\Models\Setting::get('market.silver_spot_inr_per_oz')) }}">
                        @error('market.silver_spot_inr_per_oz') <p class="field__hint" style="color:#c0392b">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flash flash--warning" style="margin-bottom:0">
                    <x-admin.icon name="alert" />
                    <div>
                        The app never shows this number directly — it derives per-gram rates (24k/22k/18k
                        for gold, 999 for silver) plus each city's premium from it on the next sync.
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection
