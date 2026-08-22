<?php

namespace App\Domain\MarketData;

/**
 * Semantic identities compiled into the market-data implementation.
 *
 * These are not operator configuration knobs. Changing one changes the implementation contract
 * and therefore requires a new build and snapshot identity. Keeping them here prevents an
 * unregistered environment variable from silently changing analytical meaning.
 */
final class MarketDataSemanticBindings
{
    public const PRICE_PRODUCT_VERSION = 'structural_adjusted_v1';

    public const FACTOR_FORMULA_VERSION = 'structural_factor_product_v1';

    /** Optional MD-B20 capability; no runtime activation key is registered by current authority. */
    public const SESSION_SNAPSHOT_FEATURE_STATE = 'DISABLED';

    public static function snapshot(): array
    {
        return [
            'factor_formula_version' => self::FACTOR_FORMULA_VERSION,
            'price_product_version' => self::PRICE_PRODUCT_VERSION,
            'session_snapshot_feature_state' => self::SESSION_SNAPSHOT_FEATURE_STATE,
        ];
    }
}
