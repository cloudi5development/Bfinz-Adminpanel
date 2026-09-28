<?php

namespace App\Services\Alerts;

use App\Contracts\Providers\PushSender;
use App\Models\Alert;
use App\Models\ForexRate;
use App\Models\FuelPrice;
use App\Models\MetalRate;
use App\Models\Notification;
use App\Models\UserFcmToken;

/**
 * Dispatched (via App\Jobs\EvaluateAlerts) after every market sync, per
 * docs/BUILD_SPEC.md §6. Rules (§7.6): above/below fire once on crossing
 * into the trigger side, then re-arm only after crossing back; any_change
 * fires at most once per calendar day.
 *
 * fd/rd alert types are accepted by the schema (spec §5) but always skipped
 * here — those products don't exist yet (P5).
 */
class AlertEvaluationService
{
    public function __construct(private readonly PushSender $push) {}

    public function evaluate(string $type): void
    {
        if (! in_array($type, ['gold', 'silver', 'forex', 'fuel'], true)) {
            return;
        }

        Alert::where('type', $type)->where('is_active', true)->each(function (Alert $alert) {
            $value = $this->currentValueFor($alert);

            if ($value === null) {
                return;
            }

            if ($alert->condition === 'any_change') {
                if ($alert->last_triggered_at === null || ! $alert->last_triggered_at->isToday()) {
                    $this->trigger($alert, $value);
                }

                return;
            }

            $crossedIntoTrigger = $alert->condition === 'above'
                ? $value >= (float) $alert->target_value
                : $value <= (float) $alert->target_value;

            if ($crossedIntoTrigger && $alert->armed) {
                $this->trigger($alert, $value);
                $alert->update(['armed' => false]);
            } elseif (! $crossedIntoTrigger && ! $alert->armed) {
                $alert->update(['armed' => true]);
            }
        });
    }

    private function currentValueFor(Alert $alert): ?float
    {
        return match ($alert->type) {
            'gold', 'silver' => $this->latestMetalRate($alert),
            'forex' => $this->latestForexRate($alert),
            'fuel' => $this->latestFuelPrice($alert),
            default => null,
        };
    }

    private function latestMetalRate(Alert $alert): ?float
    {
        $rate = MetalRate::where('metal', $alert->type)
            ->where('purity', $alert->asset)
            ->where('city_id', $alert->city_id)
            ->orderByDesc('rate_date')
            ->first();

        return $rate ? $rate->rate_per_gram_paise / 100 : null;
    }

    private function latestForexRate(Alert $alert): ?float
    {
        $rate = ForexRate::where('base', $alert->asset)->where('quote', 'INR')->orderByDesc('rate_date')->first();

        return $rate ? (float) $rate->rate : null;
    }

    private function latestFuelPrice(Alert $alert): ?float
    {
        $price = FuelPrice::where('fuel', $alert->asset)->where('city_id', $alert->city_id)->orderByDesc('price_date')->first();

        return $price ? $price->price_paise / 100 : null;
    }

    private function trigger(Alert $alert, float $value): void
    {
        $title = $this->titleFor($alert);
        $body = 'Now at '.number_format($value, 2)." (your alert: {$alert->condition} ".number_format((float) $alert->target_value, 2).')';

        if ($alert->condition === 'any_change') {
            $body = 'New rate: '.number_format($value, 2);
        }

        Notification::create([
            'user_id' => $alert->user_id,
            'category' => 'alerts',
            'title' => $title,
            'body' => $body,
            'data' => ['alert_id' => $alert->id, 'type' => $alert->type, 'asset' => $alert->asset, 'value' => $value],
        ]);

        $tokens = UserFcmToken::where('user_id', $alert->user_id)->pluck('fcm_token')->all();

        if ($tokens !== []) {
            $this->push->send($tokens, $title, $body, ['type' => $alert->type, 'asset' => $alert->asset]);
        }

        $alert->update(['last_triggered_at' => now()]);
    }

    private function titleFor(Alert $alert): string
    {
        $label = ucfirst($alert->type);

        return "{$label} alert: {$alert->asset}";
    }
}
