<?php

namespace App\Providers\Data;

use App\Contracts\Providers\ForexRateProvider;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Free, keyless ECB reference rates (https://frankfurter.dev). ECB's native
 * base is EUR, so one request fetches EUR→{every requested code, plus INR}
 * and every other rate is derived by cross-division — cheaper and more
 * consistent than one request per currency.
 *
 * Known gap: ECB doesn't publish some pegged Gulf currencies (AED, SAR,
 * QAR — see docs/BUILD_SPEC.md §6, which names ExchangeRate-API as the
 * fallback for those). Not implemented yet; those codes are simply skipped
 * with a warning rather than failing the whole sync.
 */
class FrankfurterForexRateProvider implements ForexRateProvider
{
    private const BASE_URL = 'https://api.frankfurter.dev/v1/latest';

    public function fetchRates(array $baseCurrencyCodes): array
    {
        $symbols = array_values(array_unique(array_merge($baseCurrencyCodes, ['INR'])));

        $response = Http::timeout(10)->get(self::BASE_URL, [
            'base' => 'EUR',
            'symbols' => implode(',', $symbols),
        ]);

        if (! $response->successful()) {
            throw new ApiException('Forex provider (Frankfurter) request failed.', null, 503);
        }

        $eurRates = $response->json('rates') ?? [];
        $eurToInr = $eurRates['INR'] ?? null;

        if ($eurToInr === null) {
            throw new ApiException('Forex provider (Frankfurter) did not return an INR rate.', null, 503);
        }

        $result = [];

        foreach ($baseCurrencyCodes as $code) {
            if ($code === 'EUR') {
                $result['EUR'] = ['rate' => (float) $eurToInr, 'source' => 'frankfurter'];

                continue;
            }

            $eurToCode = $eurRates[$code] ?? null;

            if ($eurToCode === null || (float) $eurToCode === 0.0) {
                Log::warning('Frankfurter has no rate for currency, skipping', ['code' => $code]);

                continue;
            }

            $result[$code] = [
                'rate' => (float) $eurToInr / (float) $eurToCode,
                'source' => 'frankfurter',
            ];
        }

        return $result;
    }
}
