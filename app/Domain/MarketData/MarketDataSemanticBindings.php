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
    public const RAW_PRODUCT_VERSION = 'raw_eod_v1';
    public const STRUCTURAL_ADJUSTED_PRODUCT_VERSION = 'structural_adjusted_v2';
    public const TOTAL_RETURN_PRODUCT_VERSION = 'total_return_v1';
    public const PRICE_PRODUCT_VERSION = self::STRUCTURAL_ADJUSTED_PRODUCT_VERSION;

    public const FACTOR_FORMULA_VERSION = 'structural_factor_product_v2';

    /**
     * Liquidity metric identity.
     *
     * The rolling liquidity metrics are versioned separately from the price product because they
     * are defined on the raw series regardless of which analytical product the rest of the vector
     * uses. `SOURCE_REPORTED_LIQUIDITY_VERSION` covers the per-bar facts the source supplies
     * directly, which have no platform formula but still require a stated version to resolve their
     * label.
     */
    public const LIQUIDITY_FORMULA_VERSION = 'liquidity_metric_v1';

    public const SOURCE_REPORTED_LIQUIDITY_VERSION = 'source_reported_v1';

    /**
     * The only volume unit this platform stores. Lot-based units belong to downstream order and
     * position sizing; market-data must not multiply share volume by lot size.
     */
    public const CANONICAL_VOLUME_UNIT_CODE = 'SHARES';

    /** Optional MD-B20 capability; no runtime activation key is registered by current authority. */
    public const SESSION_SNAPSHOT_FEATURE_STATE = 'DISABLED';

    public static function snapshot(): array
    {
        return [
            'factor_formula_version' => self::FACTOR_FORMULA_VERSION,
            'price_product_version' => self::PRICE_PRODUCT_VERSION,
            'raw_product_version' => self::RAW_PRODUCT_VERSION,
            'structural_adjusted_product_version' => self::STRUCTURAL_ADJUSTED_PRODUCT_VERSION,
            'total_return_product_version' => self::TOTAL_RETURN_PRODUCT_VERSION,
            'liquidity_formula_version' => self::LIQUIDITY_FORMULA_VERSION,
            'source_reported_liquidity_version' => self::SOURCE_REPORTED_LIQUIDITY_VERSION,
            'canonical_volume_unit_code' => self::CANONICAL_VOLUME_UNIT_CODE,
            'session_snapshot_feature_state' => self::SESSION_SNAPSHOT_FEATURE_STATE,
        ];
    }
}
