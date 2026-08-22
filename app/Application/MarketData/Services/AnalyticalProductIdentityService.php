<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataSemanticBindings;
use App\Domain\MarketData\MarketDataScope;

/**
 * Canonical identity for the one analytical product selected by an indicator run.
 */
class AnalyticalProductIdentityService
{
    public function selectedProductCode()
    {
        $selected = strtoupper(trim((string) config(
            'market_data.indicators.price_product_default',
            MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT
        )));

        if ($selected !== MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT) {
            throw new \RuntimeException(
                'ANALYTICAL_PRICE_PRODUCT_INVALID: indicator runs must select STRUCTURAL_ADJUSTED.'
            );
        }

        return $selected;
    }

    public function productVersion()
    {
        return MarketDataSemanticBindings::PRICE_PRODUCT_VERSION;
    }

    public function factorFormulaVersion()
    {
        return MarketDataSemanticBindings::FACTOR_FORMULA_VERSION;
    }

    /**
     * Hash exactly the factor revisions selected for the run, including the empty set.
     */
    public function factorSetHash(array $factorsByTicker, $windowStart, $windowEnd)
    {
        $canonicalFactors = [];
        ksort($factorsByTicker, SORT_NUMERIC);

        foreach ($factorsByTicker as $tickerId => $factors) {
            usort($factors, function ($left, $right) {
                $leftKey = ($left['ex_date'] ?? '').'|'.($left['factor_revision_ref'] ?? '');
                $rightKey = ($right['ex_date'] ?? '').'|'.($right['factor_revision_ref'] ?? '');

                return strcmp($leftKey, $rightKey);
            });

            foreach ($factors as $factor) {
                $canonicalFactors[] = [
                    'ticker_id' => (int) $tickerId,
                    'listing_id' => isset($factor['listing_id']) ? (int) $factor['listing_id'] : null,
                    'factor_revision_ref' => (string) ($factor['factor_revision_ref'] ?? ''),
                    'ex_date' => (string) ($factor['ex_date'] ?? ''),
                    'price_factor' => $this->canonicalDecimal($factor['price_factor'] ?? null),
                    'volume_factor' => $this->canonicalDecimal($factor['volume_factor'] ?? null),
                ];
            }
        }

        $payload = [
            'factor_set_schema_version' => 'analytical_factor_set_v1',
            'price_product_code' => $this->selectedProductCode(),
            'price_product_version' => $this->productVersion(),
            'factor_formula_version' => $this->factorFormulaVersion(),
            'window_start' => (string) $windowStart,
            'window_end' => (string) $windowEnd,
            'factors' => $canonicalFactors,
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function canonicalDecimal($value)
    {
        return $value === null ? null : number_format((float) $value, 12, '.', '');
    }
}
