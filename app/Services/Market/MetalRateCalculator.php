<?php

namespace App\Services\Market;

use InvalidArgumentException;

/**
 * Pure formula, no I/O: spot price (INR per troy ounce) → per-gram price for
 * a given purity, plus city premium. See docs/BUILD_SPEC.md §6.
 */
class MetalRateCalculator
{
    private const GRAMS_PER_TROY_OUNCE = 31.1035;

    private const PURITY_FACTORS = [
        '24k' => 1.0,
        '22k' => 0.916,
        '18k' => 0.75,
        '999' => 1.0,
    ];

    public function perGramPaise(float $spotInrPerOz, string $purity, int $cityPremiumPaise = 0): int
    {
        if (! isset(self::PURITY_FACTORS[$purity])) {
            throw new InvalidArgumentException("Unknown purity: {$purity}");
        }

        $perGramRupees = ($spotInrPerOz / self::GRAMS_PER_TROY_OUNCE) * self::PURITY_FACTORS[$purity];

        return (int) round($perGramRupees * 100) + $cityPremiumPaise;
    }

    /**
     * @return list<string> the purities sold for this metal — gold comes in
     *                      three, silver is quoted at 999 fineness only.
     */
    public function puritiesFor(string $metal): array
    {
        return $metal === 'gold' ? ['24k', '22k', '18k'] : ['999'];
    }
}
