<?php

namespace App\Domain\MarketData;

/**
 * The declared label for every stored liquidity metric.
 *
 * `Volume_and_Turnover_Normalization_LOCKED.md` requires a consumer to tell an actual value from a
 * proxy "without consulting a document and without parsing a column name". This class is not that
 * consumer surface — the persisted `md_liquidity_metric_labels` rows are. It is the declaration the
 * persisted rows are seeded from and checked against, so a deployed registry that has drifted from
 * the contract is detectable rather than merely different.
 *
 * A metric absent from here is unlabelled. The contract is explicit that unlabelled does not mean
 * proxy: "A metric whose actual-versus-proxy marker is absent is not assumed to be a proxy. It is
 * unlabelled, and an unlabelled liquidity metric may not be published."
 */
final class LiquidityMetricLabelRegistry
{
    public const KIND_ACTUAL = 'ACTUAL';

    public const KIND_PROXY = 'PROXY';

    public const SCOPE_INDICATOR_ROW = 'EOD_INDICATOR_ROW';

    public const SCOPE_BAR_ROW = 'EOD_BAR_ROW';

    public const BASIS_RAW = 'RAW';

    public const BASIS_NOT_APPLICABLE = 'NOT_APPLICABLE';

    /**
     * The retirement condition the contract requires every compatibility alias to carry.
     *
     * Recorded on the alias row itself rather than in prose, because the contract's own diagnosis of
     * `dv20_idr` is that documenting an alias "corrects the reading for whoever reads the
     * documentation; the column keeps asserting the wrong thing to everyone else".
     */
    public const DV20_RETIREMENT_CONDITION = 'Retired through a versioned read-model change once no reader outside this package '
        .'depends on dv20_idr, demonstrated rather than assumed. Until then the alias is preserved and never propagated: '
        .'no new artifact, column, contract or API field may be named dv* or otherwise imply traded value without stating its basis.';

    /**
     * @return array<int,array<string,mixed>>
     */
    public static function declared(): array
    {
        $indicatorVersion = MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION;
        $sourceVersion = MarketDataSemanticBindings::SOURCE_REPORTED_LIQUIDITY_VERSION;

        return [
            /*
             * Source-backed actual traded value. The price basis is NOT_APPLICABLE rather than RAW
             * because this value is not computed from a price at all — recording RAW here would
             * describe it as a price-derived figure, which is the confusion the contract exists to
             * prevent.
             */
            [
                'metric_field' => 'traded_value_idr_actual',
                'metric_scope' => self::SCOPE_BAR_ROW,
                'formula_version' => $sourceVersion,
                'metric_kind' => self::KIND_ACTUAL,
                'price_basis' => self::BASIS_NOT_APPLICABLE,
                'window_sessions' => null,
                'unit_code' => 'IDR',
                'market_scope' => 'IDX_REGULAR',
                'quality_state_field' => 'quality_state',
                'is_compatibility_alias' => false,
                'aliases_metric_field' => null,
                'retirement_condition' => null,
            ],
            [
                'metric_field' => 'trade_count_actual',
                'metric_scope' => self::SCOPE_BAR_ROW,
                'formula_version' => $sourceVersion,
                'metric_kind' => self::KIND_ACTUAL,
                'price_basis' => self::BASIS_NOT_APPLICABLE,
                'window_sessions' => null,
                'unit_code' => 'TRADES',
                'market_scope' => 'IDX_REGULAR',
                'quality_state_field' => 'quality_state',
                'is_compatibility_alias' => false,
                'aliases_metric_field' => null,
                'retirement_condition' => null,
            ],
            [
                'metric_field' => 'adv20_traded_value_idr_actual',
                'metric_scope' => self::SCOPE_INDICATOR_ROW,
                'formula_version' => $indicatorVersion,
                'metric_kind' => self::KIND_ACTUAL,
                'price_basis' => self::BASIS_NOT_APPLICABLE,
                'window_sessions' => 20,
                'unit_code' => 'IDR',
                'market_scope' => 'IDX_REGULAR',
                'quality_state_field' => 'invalid_reason_code',
                'is_compatibility_alias' => false,
                'aliases_metric_field' => null,
                'retirement_condition' => null,
            ],
            /*
             * The proxy carries RAW explicitly. Combining structural-adjusted price with raw volume
             * is forbidden as dimensionally inconsistent, so the basis is part of what makes the
             * stored value readable rather than a decoration on it.
             */
            [
                'metric_field' => 'adv20_close_volume_proxy_idr',
                'metric_scope' => self::SCOPE_INDICATOR_ROW,
                'formula_version' => $indicatorVersion,
                'metric_kind' => self::KIND_PROXY,
                'price_basis' => self::BASIS_RAW,
                'window_sessions' => 20,
                'unit_code' => 'IDR',
                'market_scope' => 'IDX_REGULAR',
                'quality_state_field' => 'invalid_reason_code',
                'is_compatibility_alias' => false,
                'aliases_metric_field' => null,
                'retirement_condition' => null,
            ],
            [
                'metric_field' => 'dv20_idr',
                'metric_scope' => self::SCOPE_INDICATOR_ROW,
                'formula_version' => $indicatorVersion,
                'metric_kind' => self::KIND_PROXY,
                'price_basis' => self::BASIS_RAW,
                'window_sessions' => 20,
                'unit_code' => 'IDR',
                'market_scope' => 'IDX_REGULAR',
                'quality_state_field' => 'invalid_reason_code',
                'is_compatibility_alias' => true,
                'aliases_metric_field' => 'adv20_close_volume_proxy_idr',
                'retirement_condition' => self::DV20_RETIREMENT_CONDITION,
            ],
        ];
    }

    /** Metric fields stored on the indicator row, in declaration order. */
    public static function indicatorMetricFields(): array
    {
        return self::fieldsForScope(self::SCOPE_INDICATOR_ROW);
    }

    /** Metric fields stored on the canonical bar row, in declaration order. */
    public static function barMetricFields(): array
    {
        return self::fieldsForScope(self::SCOPE_BAR_ROW);
    }

    private static function fieldsForScope(string $scope): array
    {
        $fields = [];
        foreach (self::declared() as $label) {
            if ($label['metric_scope'] === $scope) {
                $fields[] = $label['metric_field'];
            }
        }

        return $fields;
    }

    public static function declaredFor(string $metricField): ?array
    {
        foreach (self::declared() as $label) {
            if ($label['metric_field'] === $metricField) {
                return $label;
            }
        }

        return null;
    }
}
