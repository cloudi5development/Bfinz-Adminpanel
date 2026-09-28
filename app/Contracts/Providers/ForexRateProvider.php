<?php

namespace App\Contracts\Providers;

use App\Exceptions\ApiException;

interface ForexRateProvider
{
    /**
     * @param  list<string>  $baseCurrencyCodes  e.g. ['USD', 'EUR', 'AED']
     * @return array<string, array{rate: float, source: string}> keyed by base code, rate is 1 base = N INR
     *
     * @throws ApiException when the provider can't be reached
     */
    public function fetchRates(array $baseCurrencyCodes): array;
}
