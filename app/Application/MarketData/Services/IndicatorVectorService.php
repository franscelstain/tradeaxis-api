<?php

namespace App\Application\MarketData\Services;

use App\Domain\MarketData\MarketDataSemanticBindings;

use App\Domain\MarketData\MarketDataScope;

class IndicatorVectorService
{
    /**
     * Baseline indicators whose NULL forces is_valid=0.
     *
     * Owner contract: docs/market_data/registry/Indicator_Registry_Baseline_LOCKED.md
     * ma20/ma50 and the sector-rotation fields are intentionally absent: they are not
     * mandatory baseline indicators, so contaminating them alone does not invalidate a row.
     */
    private const MANDATORY_BASELINE_INDICATORS = [
        'dv20_idr',
        'atr14_pct',
        'vol_ratio',
        'roc5',
        'roc10',
        'roc20',
        'hh20',
        'll20',
        'close_to_hh20_pct',
        'close_to_ll20_pct',
        'range_20_pct',
        'range_position_20_pct',
    ];

    private $priceProducts;

    public function __construct(AnalyticalPriceProductService $priceProducts = null)
    {
        $this->priceProducts = $priceProducts ?: new AnalyticalPriceProductService();
    }

    public function buildRow($tickerId, array $bars, $requestedDate, $publicationId, $runId, $createdAt, array $config, array $atrSeries = null)
    {
        usort($bars, function ($a, $b) {
            return strcmp($a['trade_date'], $b['trade_date']);
        });

        $rawBars = $bars;
        $adjustment = $this->applyPriceAdjustment($bars, $config);
        $bars = $adjustment['bars'];
        $priceProductCode = $adjustment['price_product_code'];

        $index = null;
        foreach ($bars as $i => $bar) {
            if ($bar['trade_date'] === $requestedDate) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return null;
        }

        $invalidReason = $this->resolveInvalidReason($bars, $index, $config);
        $sectorCode = $this->normalizeSectorCode($config['sector_code'] ?? null);
        $values = [
            'dv20_idr' => null,
            'adv20_close_volume_proxy_idr' => null,
            'adv20_traded_value_idr_actual' => null,
            'atr14' => null,
            'atr14_pct' => null,
            'vol_ratio' => null,
            'sector_code' => $sectorCode,
            'roc5' => null,
            'roc10' => null,
            'roc20' => null,
            'hh20' => null,
            'll20' => null,
            'ma20' => null,
            'ma50' => null,
            'close_to_hh20_pct' => null,
            'close_to_ll20_pct' => null,
            'range_20_pct' => null,
            'range_position_20_pct' => null,
            'close_vs_ma20_pct' => null,
            'close_vs_ma50_pct' => null,
            'ma20_slope_pct' => null,
            'rs_20_vs_ihsg' => null,
            'sector_roc20' => null,
            'rs_20_vs_sector' => null,
            'sector_rs_20_vs_ihsg' => null,
        ] + $this->eventRiskValues($config);

        $values = $this->calculateIndicators($bars, $index, $config, $rawBars, $atrSeries);

        $quarantine = $this->applyCorporateActionQuarantine($values, $config);
        $values = $quarantine['values'];
        $nullReasons = $this->fieldNullReasons($values, $bars, $index, $config, $quarantine['field_reasons']);

        // Contamination is a known, explainable cause. Reporting it as insufficient history
        // would misdescribe it and strip the operator's ability to find affected tickers by
        // reason code. HARD structural codes are preserved: a missing dependency bar is a
        // genuine data hole and stays more actionable than the quarantine annotation.
        if ($quarantine['mandatory_contaminated']
            && ($invalidReason === null || $invalidReason === 'IND_INSUFFICIENT_HISTORY')) {
            // A corporate action names the cause; an unexplained price-scale break only says
            // the series jumped. When both apply, the named cause is the more actionable one.
            $invalidReason = $quarantine['price_scale_contaminated'] && ! $quarantine['corporate_action_contaminated']
                ? 'IND_PRICE_SCALE_DISCONTINUITY'
                : 'IND_CORPORATE_ACTION_DISCONTINUITY';
        }

        return [
            'trade_date' => $requestedDate,
            'ticker_id' => $tickerId,
            'is_valid' => $invalidReason ? 0 : 1,
            'invalid_reason_code' => $invalidReason,
            'indicator_set_version' => $config['set_version'],
            'listing_id' => isset($config['listing_id']) ? (int) $config['listing_id'] : null,
            'formula_version' => (string) ($config['formula_version'] ?? $config['set_version']),
            'config_snapshot_id' => isset($config['config_snapshot_id']) ? (int) $config['config_snapshot_id'] : null,
            'factor_set_id' => isset($config['factor_set_id']) ? (int) $config['factor_set_id'] : null,
            'factor_set_hash' => $config['factor_set_hash'] ?? null,
            'price_product_version' => (string) ($config['price_product_version'] ?? MarketDataSemanticBindings::PRICE_PRODUCT_VERSION),
            /*
             * The liquidity metrics carry their own formula identity, separate from the operator's
             * indicator set version. Their label resolves on this, so keying it to a configurable
             * set version would make the actual-versus-proxy marker resolvable only by coincidence.
             */
            'liquidity_formula_version' => MarketDataSemanticBindings::LIQUIDITY_FORMULA_VERSION,
            /*
             * The vector must state which price product it was computed on. Without it a consumer
             * cannot tell an adjusted series from an unadjusted one, and the two are not
             * comparable: a split-affected window differs by the split ratio, not by a few
             * percent. Leaving it null let RAW-based and STRUCTURAL_ADJUSTED-based rows sit in one
             * column indistinguishably.
             */
            'price_product_code' => $priceProductCode,
            'sector_code' => $values['sector_code'],
            'sector_membership_id' => isset($config['sector_membership_id']) ? (int) $config['sector_membership_id'] : null,
            'dv20_idr' => $values['dv20_idr'],
            'adv20_close_volume_proxy_idr' => $values['adv20_close_volume_proxy_idr'],
            'adv20_traded_value_idr_actual' => $values['adv20_traded_value_idr_actual'],
            'atr14' => $values['atr14'],
            'atr14_pct' => $values['atr14_pct'],
            'vol_ratio' => $values['vol_ratio'],
            'roc5' => $values['roc5'],
            'roc10' => $values['roc10'],
            'roc20' => $values['roc20'],
            'hh20' => $values['hh20'],
            'll20' => $values['ll20'],
            'ma20' => $values['ma20'],
            'ma50' => $values['ma50'],
            'close_to_hh20_pct' => $values['close_to_hh20_pct'],
            'close_to_ll20_pct' => $values['close_to_ll20_pct'],
            'range_20_pct' => $values['range_20_pct'],
            'range_position_20_pct' => $values['range_position_20_pct'],
            'close_vs_ma20_pct' => $values['close_vs_ma20_pct'],
            'close_vs_ma50_pct' => $values['close_vs_ma50_pct'],
            'ma20_slope_pct' => $values['ma20_slope_pct'],
            'rs_20_vs_ihsg' => $values['rs_20_vs_ihsg'],
            'sector_roc20' => $values['sector_roc20'],
            'rs_20_vs_sector' => $values['rs_20_vs_sector'],
            'sector_rs_20_vs_ihsg' => $values['sector_rs_20_vs_ihsg'],
            'corporate_action_flag' => $values['corporate_action_flag'],
            'corporate_action_types' => $values['corporate_action_types'],
            'trading_status_code' => $values['trading_status_code'],
            'is_suspended' => $values['is_suspended'],
            'is_uma' => $values['is_uma'],
            'event_risk_flag' => $values['event_risk_flag'],
            'event_risk_reasons' => $values['event_risk_reasons'],
            'corporate_action_window_reasons' => $quarantine['tokens'],
            /*
             * Retained even when `invalid_reason_code` is set. The primary reason is a
             * compatibility projection of this set and must never replace it.
             */
            'null_reasons_json' => $nullReasons === [] ? null : json_encode($nullReasons, JSON_UNESCAPED_SLASHES),
            'run_id' => $runId,
            'publication_id' => $publicationId,
            'created_at' => $createdAt,
        ];
    }

    /**
     * Field-level null reason sets, retained alongside the compatibility primary reason.
     *
     * The row already carried `invalid_reason_code`, which the nullability contract calls the
     * compatibility primary reason, and nothing else. That is the exact state the contract
     * forbids: a single primary reason standing in for the field-level sets. `null_reasons_json`
     * existed as a column, travelled through the pipeline column list and was selected by the read
     * repository, but no code ever wrote a value into it.
     *
     * The four causes the contract requires to stay distinct map to the four registered INDICATOR
     * codes: warm-up not yet met, a required trading-date dependency absent, an unresolved
     * corporate action, and an unexplained price-scale break. A field is described by every cause
     * that applies to it, not by the first one found, because a contaminated window inside a short
     * history is both.
     *
     * Fields whose only reason for being NULL is an absent optional source fact — the sector and
     * benchmark relatives, and the actual traded value the provider does not supply — carry no
     * code, because the registered vocabulary has none for that state and inventing one here would
     * be a reason-code registry change made by the implementation. See `F-MD-B14-A001-001`.
     *
     * @param  array<string,mixed>  $values
     * @param  array<int,array<string,mixed>>  $bars
     * @param  array<string,array<int,string>>  $quarantineReasons
     * @return array<string,array<int,string>>
     */
    private function fieldNullReasons(array $values, array $bars, $index, array $config, array $quarantineReasons)
    {
        $reasons = [];

        foreach ($this->contaminationHorizons($config) as $field => $horizon) {
            if (! array_key_exists($field, $values) || $values[$field] !== null) {
                continue;
            }

            $codes = isset($quarantineReasons[$field]) ? $quarantineReasons[$field] : [];
            $windowDays = (int) $horizon[0];

            if ($windowDays > 0) {
                if (($index + 1) < $windowDays) {
                    $codes[] = 'IND_INSUFFICIENT_HISTORY';
                } elseif ($this->windowHasMissingDependency($bars, $index, $windowDays)) {
                    $codes[] = 'IND_MISSING_DEPENDENCY_BAR';
                }
            }

            if ($codes === []) {
                continue;
            }

            $codes = array_values(array_unique($codes));
            sort($codes);
            $reasons[$field] = $codes;
        }

        ksort($reasons);

        return $reasons;
    }

    /**
     * A fixed window requires its exact trading-date dependencies, so an absent bar or an absent
     * required input inside the span is a missing dependency rather than a shorter window.
     *
     * Like every other windowed function here it refuses to answer before its own history
     * exists: whether a dependency is missing from a window that has not been reached yet is not
     * a question with a true or false answer, it is the insufficient-history case, and the caller
     * resolves that first.
     *
     * @param  array<int,array<string,mixed>>  $bars
     * @return bool|null
     */
    private function windowHasMissingDependency(array $bars, $index, $windowDays)
    {
        if ($index < 0 || ($index + 1) < $windowDays) {
            return null;
        }

        for ($i = max(0, $index - $windowDays + 1); $i <= $index; $i++) {
            if (! isset($bars[$i])) {
                return true;
            }
            foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
                if (! isset($bars[$i][$field]) || $bars[$i][$field] === null) {
                    return true;
                }
            }
        }

        return false;
    }

    public function resolveInvalidReason(array $bars, $index, array $config)
    {
        $requiredHistory = max(
            (int) $config['dv_window_days'],
            (int) $config['vol_ratio_lookback_days'] + 1,
            (int) $config['roc_lookback_days'] + 1,
            (int) $config['atr_window_days'] + 1,
            (int) $config['hh_window_days']
        );

        if (($index + 1) < $requiredHistory) {
            return 'IND_INSUFFICIENT_HISTORY';
        }

        for ($i = max(0, $index - $requiredHistory); $i <= $index; $i++) {
            if (! isset($bars[$i])) {
                return 'IND_MISSING_DEPENDENCY_BAR';
            }

            foreach (['open', 'high', 'low', 'close', 'volume'] as $field) {
                if (! isset($bars[$i][$field]) || $bars[$i][$field] === null) {
                    return 'IND_MISSING_DEPENDENCY_BAR';
                }
            }
        }

        return null;
    }

    public function calculateIndicators(array $bars, $index, array $config, array $rawBars = null, array $atrSeries = null)
    {
        // The turnover proxy is defined on the as-traded series, so it is computed from the raw
        // bars even when every other indicator runs on the adjusted ones.
        $rawBars = $rawBars === null ? $bars : $rawBars;
        $dvWindow = (int) $config['dv_window_days'];
        $atrWindow = (int) $config['atr_window_days'];
        $volLookback = (int) $config['vol_ratio_lookback_days'];
        $rocLookback = (int) $config['roc_lookback_days'];
        $hhWindow = (int) $config['hh_window_days'];

        $currentBar = $bars[$index];
        $priceBasisCurrent = $this->priceBasis($currentBar, $config);
        $dv20Idr = $this->averageTurnover($rawBars, $index, $dvWindow, $config);
        $atr = $this->wilderAtr($bars, $index, $atrWindow, $config, $atrSeries);
        $priorVolAverage = $this->priorVolumeAverage($bars, $index, $volLookback);
        $hh20 = $this->windowExtreme($bars, $index, $hhWindow, 'high', 'max');
        $ll20 = $this->windowExtreme($bars, $index, $hhWindow, 'low', 'min');
        $ma20 = $this->movingAverage($bars, $index, 20, $config);
        $ma50 = $this->movingAverage($bars, $index, 50, $config);
        $ma20Past = $this->movingAverage($bars, $index - 5, 20, $config);
        $roc20 = $this->roc($bars, $index, $rocLookback, $config);
        $benchmarkRoc20Pct = array_key_exists('benchmark_roc20_pct', $config) && $config['benchmark_roc20_pct'] !== null
            ? (float) $config['benchmark_roc20_pct']
            : null;
        $sectorRoc20Pct = array_key_exists('sector_roc20_pct', $config) && $config['sector_roc20_pct'] !== null
            ? (float) $config['sector_roc20_pct']
            : null;
        $equityRoc20Pct = $roc20 !== null ? $roc20 * 100 : null;

        return [
            /*
             * `dv20_idr` is the legacy alias for the proxy and carries the same value. The two
             * explicitly named fields exist so a consumer never has to guess which one it holds:
             * the actual is source-backed traded value, the proxy is RAW close x RAW volume.
             *
             * The actual stays NULL because the provider does not supply traded value at all —
             * the adapter declares `provides_actual_traded_value => false`. NULL is the contract's
             * required value when the actual is unavailable, and it is the only honest one: a
             * proxy written into the actual field would be a misstatement, not an approximation.
             */
            'dv20_idr' => $dv20Idr,
            'adv20_close_volume_proxy_idr' => $dv20Idr,
            'adv20_traded_value_idr_actual' => null,
            /*
             * `atr14` is a required baseline field under the indicator registry. The recursion
             * already produced it; only the ratio was ever published, so the registered level
             * was permanently NULL in a column the artifact hash already knew the scale of.
             */
            'atr14' => $atr !== null ? round($atr, 10) : null,
            'atr14_pct' => $atr !== null && $priceBasisCurrent > 0 ? round($atr / $priceBasisCurrent, 10) : null,
            'vol_ratio' => $priorVolAverage !== null
                && $priorVolAverage > 0
                && array_key_exists('volume', $currentBar)
                && $currentBar['volume'] !== null
                    ? round(((float) $currentBar['volume']) / $priorVolAverage, 10)
                    : null,
            'sector_code' => $this->normalizeSectorCode($config['sector_code'] ?? null),
            'roc5' => $this->roc($bars, $index, 5, $config),
            'roc10' => $this->roc($bars, $index, 10, $config),
            'roc20' => $roc20,
            'hh20' => $hh20,
            'll20' => $ll20,
            'ma20' => $ma20,
            'ma50' => $ma50,
            'close_to_hh20_pct' => $this->pctDifference($priceBasisCurrent, $hh20),
            'close_to_ll20_pct' => $this->pctDifference($priceBasisCurrent, $ll20),
            'range_20_pct' => $this->pctDifference($hh20, $ll20),
            'range_position_20_pct' => $this->rangePositionPct($priceBasisCurrent, $ll20, $hh20),
            'close_vs_ma20_pct' => $this->pctDifference($priceBasisCurrent, $ma20),
            'close_vs_ma50_pct' => $this->pctDifference($priceBasisCurrent, $ma50),
            'ma20_slope_pct' => $ma20 !== null && $ma20Past !== null ? $this->pctDifference($ma20, $ma20Past) : null,
            'rs_20_vs_ihsg' => $equityRoc20Pct !== null && $benchmarkRoc20Pct !== null ? round($equityRoc20Pct - $benchmarkRoc20Pct, 10) : null,
            'sector_roc20' => $sectorRoc20Pct,
            'rs_20_vs_sector' => $equityRoc20Pct !== null && $sectorRoc20Pct !== null ? round($equityRoc20Pct - $sectorRoc20Pct, 10) : null,
            'sector_rs_20_vs_ihsg' => $sectorRoc20Pct !== null && $benchmarkRoc20Pct !== null ? round($sectorRoc20Pct - $benchmarkRoc20Pct, 10) : null,
        ] + $this->eventRiskValues($config);
    }

    /**
     * Express every bar in the window on the scale in force at the requested date.
     *
     * Owner contract: docs/market_data/registry/Price_Adjustment_Contract_LOCKED.md
     *
     * Canonical bars stay raw in storage. Adjustment happens here, in memory, so the
     * as-traded series is never destroyed and no historical row is rewritten.
     *
     * A bar dated B is multiplied by the product of every factor whose ex_date falls after
     * B, so two splits inside one window compound correctly. Bars at or after the last
     * ex_date are already on the current scale and are left alone.
     */
    private function applyPriceAdjustment(array $bars, array $config)
    {
        $selectedProduct = strtoupper(trim((string) ($config['selected_price_product_code'] ?? MarketDataScope::STRUCTURAL_ADJUSTED_PRODUCT)));

        return $this->priceProducts->build($bars, $selectedProduct, [
            'requested_date' => $config['analytical_as_of_date'] ?? $config['requested_date'] ?? null,
            'price_adjustment_factors' => $config['price_adjustment_factors'] ?? [],
            'factor_set_id' => $config['factor_set_id'] ?? null,
            'factor_set_hash' => $config['factor_set_hash'] ?? null,
            'formula_version' => $config['formula_version'] ?? $config['set_version'] ?? null,
            'config_snapshot_id' => $config['config_snapshot_id'] ?? null,
            'require_persisted_identity' => ! empty($config['require_analytical_identity']),
            'require_factor_lineage' => ! empty($config['require_analytical_identity']),
        ]);
    }

    /**
     * Quarantine indicators whose dependency window spans a corporate action that breaks
     * price or volume continuity.
     *
     * Owner contract: docs/market_data/registry/Indicator_Registry_Baseline_LOCKED.md
     * (Amendment 2026-07-29 - Corporate action window contamination)
     *
     * An indicator with horizon W is contaminated when an entry sits at depth < W, where
     * depth counts trading days back from the requested date. At depth == W the action lands
     * exactly on the window start, so every bar in the window already sits on the post-action
     * scale and the window is clean.
     */
    private function applyCorporateActionQuarantine(array $values, array $config)
    {
        $entries = isset($config['corporate_action_contamination']) && is_array($config['corporate_action_contamination'])
            ? $config['corporate_action_contamination']
            : [];

        // A detected price-scale break contaminates identically to a SCALED corporate action.
        // Splits are routinely absent from the source feed, so the price series itself is the
        // only reliable signal for them.
        $priceScaleEntries = isset($config['price_scale_break_contamination']) && is_array($config['price_scale_break_contamination'])
            ? $config['price_scale_break_contamination']
            : [];

        foreach ($priceScaleEntries as $entry) {
            $entries[] = [
                // Name the cause when the break has one. An operator reading
                // STOCK_SPLIT@2026-07-15 knows what happened and on which day; reading
                // PRICE_SCALE_SCALE_SHIFT@2026-07-15 only learns that the price jumped.
                //
                // The date is always the detected break date, never the action's recorded date:
                // the series is what says when the scale actually changed.
                'action_type_code' => empty($entry['matched_action_type'])
                    ? 'PRICE_SCALE_'.$entry['break_type']
                    : strtoupper(trim((string) $entry['matched_action_type'])),
                'action_date' => $entry['trade_date'],
                'depth' => $entry['depth'],
                'breaks_price_continuity' => true,
                'breaks_volume_continuity' => true,
                'is_price_scale_break' => true,
            ];
        }

        if (empty($entries)) {
            return [
                'values' => $values,
                'tokens' => null,
                'field_reasons' => [],
                'mandatory_contaminated' => false,
                'price_scale_contaminated' => false,
                'corporate_action_contaminated' => false,
            ];
        }

        $tokens = [];
        $fieldReasons = [];
        $mandatoryContaminated = false;
        $priceScaleContaminated = false;
        $corporateActionContaminated = false;

        foreach ($this->contaminationHorizons($config) as $field => $horizon) {
            list($horizonDays, $sensitiveToPrice, $sensitiveToVolume) = $horizon;

            if ($horizonDays <= 0) {
                continue;
            }

            foreach ($entries as $entry) {
                if ((int) $entry['depth'] >= $horizonDays) {
                    continue;
                }

                $applies = ($sensitiveToPrice && ! empty($entry['breaks_price_continuity']))
                    || ($sensitiveToVolume && ! empty($entry['breaks_volume_continuity']));

                if (! $applies) {
                    continue;
                }

                $values[$field] = null;
                $fieldReasons[$field][] = empty($entry['is_price_scale_break'])
                    ? 'IND_CORPORATE_ACTION_DISCONTINUITY'
                    : 'IND_PRICE_SCALE_DISCONTINUITY';
                $tokens[$entry['action_type_code'].'@'.$entry['action_date']] = true;

                if (in_array($field, self::MANDATORY_BASELINE_INDICATORS, true)) {
                    $mandatoryContaminated = true;

                    if (empty($entry['is_price_scale_break'])) {
                        $corporateActionContaminated = true;
                    } else {
                        $priceScaleContaminated = true;
                    }
                }
            }
        }

        $tokenList = array_keys($tokens);
        sort($tokenList);

        return [
            'values' => $values,
            'tokens' => empty($tokenList) ? null : $this->joinContaminationTokens($tokenList),
            'field_reasons' => $fieldReasons,
            'mandatory_contaminated' => $mandatoryContaminated,
            'price_scale_contaminated' => $priceScaleContaminated,
            'corporate_action_contaminated' => $corporateActionContaminated,
        ];
    }

    /**
     * Contamination horizon per indicator, in trading days inclusive of the requested date.
     *
     * Horizons are derived from the same window config the indicators themselves use, so a
     * change to a window size cannot leave the quarantine describing a horizon that no longer
     * matches the computation.
     *
     * Tuple shape: [horizon_days, price_scale_sensitive, volume_scale_sensitive]
     */
    /**
     * The three horizon roles locked by Terminology_and_Scope.md, measured against the declared
     * Weekly Swing decision horizon of 5 IDX trading days.
     *
     * MD-S056-R0022 and MD-S056-R0129 forbid a dependency window entering the baseline field set
     * without a declared role, because spanning beyond the horizon is legitimate for a context
     * window and illegitimate for a decision window. Deriving the role from the span is not the
     * same as declaring it, so the manifest declares it.
     */
    public const HORIZON_ROLE_DECISION = 'decision_window';

    public const HORIZON_ROLE_CONTEXT = 'context_window';

    public const HORIZON_ROLE_STATE = 'state_window';

    public const HORIZON_ROLES = [
        self::HORIZON_ROLE_DECISION,
        self::HORIZON_ROLE_CONTEXT,
        self::HORIZON_ROLE_STATE,
    ];

    /** Weekly Swing decision horizon, in IDX trading days, per Terminology_and_Scope.md. */
    public const DECISION_HORIZON_TRADING_DAYS = 5;

    /**
     * Declared horizon role for every window in the published field set.
     *
     * `roc5` spans exactly the horizon and is consumed for the decision itself. Every other fixed
     * window deliberately spans beyond it and supplies regime background. ATR has no fixed span
     * because it carries recursive state, which the contract names as its own example.
     */
    private static function horizonRoles(): array
    {
        return [
            'roc5' => self::HORIZON_ROLE_DECISION,
            'atr14_pct' => self::HORIZON_ROLE_STATE,
            'atr14' => self::HORIZON_ROLE_STATE,
            'dv20_idr' => self::HORIZON_ROLE_CONTEXT,
            'adv20_close_volume_proxy_idr' => self::HORIZON_ROLE_CONTEXT,
            'vol_ratio' => self::HORIZON_ROLE_CONTEXT,
            'roc10' => self::HORIZON_ROLE_CONTEXT,
            'roc20' => self::HORIZON_ROLE_CONTEXT,
            'hh20' => self::HORIZON_ROLE_CONTEXT,
            'll20' => self::HORIZON_ROLE_CONTEXT,
            'ma20' => self::HORIZON_ROLE_CONTEXT,
            'ma50' => self::HORIZON_ROLE_CONTEXT,
            'close_to_hh20_pct' => self::HORIZON_ROLE_CONTEXT,
            'close_to_ll20_pct' => self::HORIZON_ROLE_CONTEXT,
            'range_20_pct' => self::HORIZON_ROLE_CONTEXT,
            'range_position_20_pct' => self::HORIZON_ROLE_CONTEXT,
            'close_vs_ma20_pct' => self::HORIZON_ROLE_CONTEXT,
            'close_vs_ma50_pct' => self::HORIZON_ROLE_CONTEXT,
            'ma20_slope_pct' => self::HORIZON_ROLE_CONTEXT,
            'rs_20_vs_ihsg' => self::HORIZON_ROLE_CONTEXT,
            'rs_20_vs_sector' => self::HORIZON_ROLE_CONTEXT,
        ];
    }

    /** Registered null reason codes, all owned by `Reason_Codes_Registry.md`. */
    public const NULL_REASON_INSUFFICIENT_HISTORY = 'IND_INSUFFICIENT_HISTORY';

    public const NULL_REASON_MISSING_DEPENDENCY = 'IND_MISSING_DEPENDENCY_BAR';

    public const NULL_REASON_CORPORATE_ACTION = 'IND_CORPORATE_ACTION_DISCONTINUITY';

    public const NULL_REASON_PRICE_SCALE = 'IND_PRICE_SCALE_DISCONTINUITY';

    /** The declarations `Indicator_Registry_Baseline_LOCKED.md` requires of every registry entry. */
    public const REGISTRY_ENTRY_KEYS = [
        'status',
        'dependency_fields',
        'window_rule',
        'basis',
        'unit',
        'warm_up_rule',
        'precision',
        'null_reason_codes',
        'formula_version',
        'registry_version',
    ];

    /**
     * The versioned per-field registry entry the indicator registry baseline requires.
     *
     * The registry names the declarations every registered field must carry, and the
     * implementation published none of them: the dependency manifest carried a window length and
     * a horizon role, and precision lived only in the schema, the hash serializer and the
     * migration, agreeing with each other by inspection rather than by any check.
     *
     * The window rule is read from the same horizon map the quarantine and the manifest use, so a
     * changed window cannot leave this entry describing the old one. Precision is declared here
     * and proven equal to both the deployed schema and the hash serializer, which is the
     * disagreement that would otherwise round one way on write and another way into the hash.
     *
     * @return array<string,array<string,mixed>>
     */
    public function fieldRegistry(array $config): array
    {
        $windows = $this->contaminationHorizons($config);
        $roles = self::horizonRoles();
        $formulaVersion = (string) (isset($config['formula_version']) && $config['formula_version'] !== null
            ? $config['formula_version']
            : (isset($config['set_version']) ? $config['set_version'] : ''));
        if ($formulaVersion === '') {
            throw new \RuntimeException('INDICATOR_REGISTRY_FORMULA_VERSION_UNDECLARED');
        }
        $registryVersion = (string) (isset($config['set_version']) ? $config['set_version'] : '');
        if ($registryVersion === '') {
            throw new \RuntimeException('INDICATOR_REGISTRY_VERSION_UNDECLARED');
        }

        $numeric = [
            'dv20_idr' => ['optional', 'close,volume', 'RAW', 'IDR', 2, 'dv_window_days sessions of RAW close and volume', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'adv20_close_volume_proxy_idr' => ['required', 'close,volume', 'RAW', 'IDR', 2, 'dv_window_days sessions of RAW close and volume', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'adv20_traded_value_idr_actual' => ['optional', 'source_traded_value', 'SOURCE_FACT', 'IDR', 2, 'dv_window_days sessions of source-backed traded value', []],
            'atr14' => ['required', 'high,low,close', 'STRUCTURAL_ADJUSTED', 'IDR', 10, 'stable Wilder seed from the first atr_window_days valid true ranges after the later of dataset start and listing', [self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'atr14_pct' => ['required', 'high,low,close', 'STRUCTURAL_ADJUSTED', 'RATIO', 10, 'stable Wilder seed from the first atr_window_days valid true ranges after the later of dataset start and listing', [self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'vol_ratio' => ['required', 'volume', 'RAW', 'RATIO', 10, 'vol_ratio_lookback_days prior sessions plus the requested date', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'roc5' => ['required', 'close', 'STRUCTURAL_ADJUSTED', 'RATIO', 10, '5 prior sessions plus the requested date', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'roc10' => ['required', 'close', 'STRUCTURAL_ADJUSTED', 'RATIO', 10, '10 prior sessions plus the requested date', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'roc20' => ['required', 'close', 'STRUCTURAL_ADJUSTED', 'RATIO', 10, 'roc_lookback_days prior sessions plus the requested date', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'hh20' => ['required', 'high', 'STRUCTURAL_ADJUSTED', 'IDR', 4, 'hh_window_days sessions', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'll20' => ['required', 'low', 'STRUCTURAL_ADJUSTED', 'IDR', 4, 'hh_window_days sessions', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'ma20' => ['required', 'close', 'STRUCTURAL_ADJUSTED', 'IDR', 4, '20 valid closes', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'ma50' => ['required', 'close', 'STRUCTURAL_ADJUSTED', 'IDR', 4, '50 valid closes', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'close_to_hh20_pct' => ['required', 'close,high', 'STRUCTURAL_ADJUSTED', 'PCT', 10, 'hh_window_days sessions', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'close_to_ll20_pct' => ['required', 'close,low', 'STRUCTURAL_ADJUSTED', 'PCT', 10, 'hh_window_days sessions', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'range_20_pct' => ['required', 'high,low', 'STRUCTURAL_ADJUSTED', 'PCT', 10, 'hh_window_days sessions', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'range_position_20_pct' => ['required', 'close,high,low', 'STRUCTURAL_ADJUSTED', 'PCT', 10, 'hh_window_days sessions', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'close_vs_ma20_pct' => ['required', 'close', 'STRUCTURAL_ADJUSTED', 'PCT', 10, '20 valid closes', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'close_vs_ma50_pct' => ['required', 'close', 'STRUCTURAL_ADJUSTED', 'PCT', 10, '50 valid closes', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'ma20_slope_pct' => ['required', 'close', 'STRUCTURAL_ADJUSTED', 'PCT', 10, '20 valid closes at the requested date and at D[-5], spanning 25 sessions', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'rs_20_vs_ihsg' => ['optional', 'close,benchmark_roc20_pct', 'STRUCTURAL_ADJUSTED', 'PCT', 10, 'roc_lookback_days sessions plus a resolved benchmark return', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'sector_roc20' => ['optional', 'sector_roc20_pct', 'SOURCE_FACT', 'PCT', 10, 'a resolved sector benchmark return for the requested date', []],
            'rs_20_vs_sector' => ['optional', 'close,sector_roc20_pct', 'STRUCTURAL_ADJUSTED', 'PCT', 10, 'roc_lookback_days sessions plus a resolved sector return', [self::NULL_REASON_INSUFFICIENT_HISTORY, self::NULL_REASON_MISSING_DEPENDENCY, self::NULL_REASON_CORPORATE_ACTION, self::NULL_REASON_PRICE_SCALE]],
            'sector_rs_20_vs_ihsg' => ['optional', 'sector_roc20_pct,benchmark_roc20_pct', 'SOURCE_FACT', 'PCT', 10, 'a resolved sector return and benchmark return for the requested date', []],
        ];

        $entries = [];
        foreach ($numeric as $field => $d) {
            $entries[$field] = [
                'status' => $d[0],
                'dependency_fields' => explode(',', $d[1]),
                'window_rule' => isset($windows[$field])
                    ? ($roles[$field] === self::HORIZON_ROLE_STATE
                        ? 'recursive state, no fixed span'
                        : 'exact trading-date window of '.((int) $windows[$field][0]).' sessions')
                    : 'point-in-time source fact, no window',
                'basis' => $d[2],
                'unit' => $d[3],
                'warm_up_rule' => $d[5],
                'precision' => [
                    'scale' => $d[4],
                    'rounding' => 'at the storage boundary only, never on an intermediate step',
                    'serialization' => 'fixed-point decimal text, no exponent, no separator',
                ],
                'null_reason_codes' => $d[6],
                'formula_version' => $formulaVersion,
                'registry_version' => $registryVersion,
            ];
        }

        $context = [
            'sector_code' => ['optional', 'sector membership revision', 'CODE'],
            'corporate_action_flag' => ['optional', 'verified event revision', 'FLAG'],
            'corporate_action_types' => ['optional', 'verified event revision', 'CODE_SET'],
            'trading_status_code' => ['optional', 'trading status event revision', 'CODE'],
            'is_suspended' => ['optional', 'trading status event revision', 'FLAG'],
            'is_uma' => ['optional', 'trading status event revision', 'FLAG'],
            'event_risk_flag' => ['optional', 'event risk source revision', 'FLAG'],
            'event_risk_reasons' => ['optional', 'event risk source revision', 'CODE_SET'],
        ];
        foreach ($context as $field => $d) {
            $entries[$field] = [
                'status' => $d[0],
                'dependency_fields' => [$d[1]],
                'window_rule' => 'as-of the governed knowledge cutoff, no window',
                'basis' => 'SOURCE_FACT',
                'unit' => $d[2],
                'warm_up_rule' => 'none; the fact is present or the field is null',
                'precision' => [
                    'scale' => null,
                    'rounding' => 'not applicable',
                    'serialization' => 'verbatim source token',
                ],
                'null_reason_codes' => [],
                'formula_version' => $formulaVersion,
                'registry_version' => $registryVersion,
            ];
        }

        foreach ($entries as $field => $entry) {
            $missing = array_values(array_diff(self::REGISTRY_ENTRY_KEYS, array_keys($entry)));
            if ($missing !== []) {
                throw new \RuntimeException('INDICATOR_REGISTRY_ENTRY_INCOMPLETE: '.$field
                    .' is missing '.implode(', ', $missing));
            }
        }

        $unregistered = array_keys(array_diff_key($windows, $entries));
        if ($unregistered !== []) {
            throw new \RuntimeException('INDICATOR_REGISTRY_FIELD_UNREGISTERED: '.implode(', ', $unregistered));
        }

        ksort($entries);

        return $entries;
    }

    /**
     * The dependency manifest MD-S081-R0026 requires the registry and implementation to publish,
     * now carrying the horizon role MD-S056-R0129 requires before a window may enter the set.
     *
     * A field present in the horizon map but absent from the window map, or the reverse, is a
     * defect rather than a default: the manifest fails closed instead of inventing a role.
     *
     * @return array<string,array{window_days:int,price_sensitive:bool,volume_sensitive:bool,horizon_role:string}>
     */
    public function dependencyManifest(array $config): array
    {
        $windows = $this->contaminationHorizons($config);
        $roles = self::horizonRoles();

        $undeclared = array_keys(array_diff_key($windows, $roles));
        if ($undeclared !== []) {
            throw new \RuntimeException('INDICATOR_HORIZON_ROLE_UNDECLARED: '.implode(', ', $undeclared));
        }
        $orphaned = array_keys(array_diff_key($roles, $windows));
        if ($orphaned !== []) {
            throw new \RuntimeException('INDICATOR_HORIZON_ROLE_ORPHANED: '.implode(', ', $orphaned));
        }

        $manifest = [];
        foreach ($windows as $field => $window) {
            $role = $roles[$field];
            if (! in_array($role, self::HORIZON_ROLES, true)) {
                throw new \RuntimeException('INDICATOR_HORIZON_ROLE_INVALID: '.$field.' => '.$role);
            }
            $manifest[$field] = [
                'window_days' => (int) $window[0],
                'price_sensitive' => (bool) $window[1],
                'volume_sensitive' => (bool) $window[2],
                'horizon_role' => $role,
            ];
        }

        return $manifest;
    }

    /**
     * MD-S056-R0024 requires the contamination radius to be published as a number: it equals the
     * longest dependency window in the published field set. ATR is excluded from the fixed-window
     * radius because its state window has no fixed span and contaminates the entire remaining
     * chain, which MD-S081-R0034 states separately.
     */
    public function fixedWindowContaminationRadius(array $config): int
    {
        $radius = 0;
        foreach ($this->dependencyManifest($config) as $entry) {
            if ($entry['horizon_role'] === self::HORIZON_ROLE_STATE) {
                continue;
            }
            $radius = max($radius, $entry['window_days']);
        }

        return $radius;
    }

    private function contaminationHorizons(array $config)
    {
        $dvWindow = (int) $config['dv_window_days'];
        $volLookback = (int) $config['vol_ratio_lookback_days'];
        $rocLookback = (int) $config['roc_lookback_days'];
        $hhWindow = (int) $config['hh_window_days'];

        // ATR is seeded cumulatively across the whole loaded bar window rather than the 15
        // days the registry declares, so its contamination horizon is supplied by the caller
        // from the actual load window. See the ATR note in the registry amendment.
        $atrHorizon = (int) (isset($config['atr_contamination_horizon_days']) ? $config['atr_contamination_horizon_days'] : 0);

        return [
            'dv20_idr' => [$dvWindow, true, true],
            // The explicitly named proxy carries the same value over the same window, so it is
            // contaminated by exactly the same events. Omitting it here would leave a quarantined
            // window readable through its clearer name.
            'adv20_close_volume_proxy_idr' => [$dvWindow, true, true],
            'atr14_pct' => [$atrHorizon, true, false],
            // The level and the ratio come from the same recursive state, so a window that
            // contaminates one contaminates both. Omitting the level here would leave a
            // quarantined ATR readable in absolute IDR.
            'atr14' => [$atrHorizon, true, false],
            'vol_ratio' => [$volLookback + 1, false, true],
            'roc5' => [6, true, false],
            'roc10' => [11, true, false],
            'roc20' => [$rocLookback + 1, true, false],
            'hh20' => [$hhWindow, true, false],
            'll20' => [$hhWindow, true, false],
            'ma20' => [20, true, false],
            'ma50' => [50, true, false],
            'close_to_hh20_pct' => [$hhWindow, true, false],
            'close_to_ll20_pct' => [$hhWindow, true, false],
            'range_20_pct' => [$hhWindow, true, false],
            'range_position_20_pct' => [$hhWindow, true, false],
            'close_vs_ma20_pct' => [20, true, false],
            'close_vs_ma50_pct' => [50, true, false],
            // ma20(D) and ma20(D[-5]) together span D[-24]..D.
            'ma20_slope_pct' => [25, true, false],
            'rs_20_vs_ihsg' => [$rocLookback + 1, true, false],
            'rs_20_vs_sector' => [$rocLookback + 1, true, false],
        ];
    }

    /**
     * Join tokens without splitting one mid-way when the persisted column runs out of room.
     */
    private function joinContaminationTokens(array $tokenList)
    {
        $joined = '';

        foreach ($tokenList as $token) {
            $candidate = $joined === '' ? $token : $joined.','.$token;

            if (strlen($candidate) > 255) {
                break;
            }

            $joined = $candidate;
        }

        return $joined === '' ? null : $joined;
    }

    /**
     * Average traded value in IDR over the window.
     *
     * Provider volume for IDX equities is reported in shares, not lots, so turnover is
     * price times share volume with no lot multiplier. Verified against the market total:
     * close * volume across all tickers gives roughly 9.6 trillion IDR for a normal
     * session, which matches published IDX turnover, while applying a lot size of 100
     * produces roughly 959 trillion IDR, which exceeds any real exchange.
     */
    private function averageTurnover(array $bars, $index, $window, array $config)
    {
        if ($index < 0 || ($index + 1) < $window) {
            return null;
        }

        $slice = array_slice($bars, $index - $window + 1, $window);
        if (count($slice) !== $window) {
            return null;
        }

        $turnovers = [];
        foreach ($slice as $bar) {
            if (! array_key_exists('volume', $bar) || $bar['volume'] === null) {
                return null;
            }

            $price = $this->priceBasis($bar, $config);
            if ($price <= 0) {
                return null;
            }

            // RAW close x RAW volume — Volume_and_Turnover_Normalization_LOCKED.md:27. Both terms
            // come from the same as-traded bar, so a corporate action that scales price without
            // scaling volume cannot skew the product.
            $turnovers[] = $price * (float) $bar['volume'];
        }

        return round(array_sum($turnovers) / $window, 2);
    }

    /**
     * Wilder ATR seeded at the dataset/listing boundary rather than at the start of the load
     * window. `$atrSeries` carries the full as-traded series when the caller can supply it; the
     * loaded window is used only as a fallback, and that fallback is an approximation, not the
     * contract value.
     *
     * Owner: Market_Data_Strategy_Implementation_Blueprint_LOCKED.md stage 15 — "Wilder ATR
     * memakai stable seed dan recursive state dari dataset/listing boundary, bukan sliding-window
     * reseed."
     */
    private function wilderAtr(array $bars, $index, $window, array $config, array $atrSeries = null)
    {
        if ($atrSeries !== null && ! empty($atrSeries)) {
            $anchor = (string) $bars[$index]['trade_date'];
            $boundarySeries = [];
            foreach ($atrSeries as $entry) {
                if ((string) $entry['trade_date'] <= $anchor) {
                    $boundarySeries[] = $entry;
                }
            }

            if (count($boundarySeries) > $index + 1) {
                // Boundary state is part of the selected analytical product too. Feeding the RAW
                // series here would mix RAW ATR with structurally adjusted MA/ROC values.
                $bars = $this->applyPriceAdjustment($boundarySeries, $config)['bars'];
                $index = count($boundarySeries) - 1;
            }
        }

        if ($index < $window) {
            return null;
        }

        $trValues = [];
        for ($i = 1; $i <= $index; $i++) {
            if (! isset($bars[$i], $bars[$i - 1])) {
                return null;
            }

            $bar = $bars[$i];
            $previousClose = $this->priceBasis($bars[$i - 1], $config);
            foreach (['high', 'low'] as $field) {
                if (! array_key_exists($field, $bar) || $bar[$field] === null) {
                    return null;
                }
            }

            $high = (float) $bar['high'];
            $low = (float) $bar['low'];
            if ($high <= 0 || $low <= 0 || $previousClose <= 0) {
                return null;
            }

            $trValues[$i] = max(
                $high - $low,
                abs($high - $previousClose),
                abs($low - $previousClose)
            );
        }

        $atr = array_sum(array_slice($trValues, 0, $window, true)) / $window;
        for ($i = $window + 1; $i <= $index; $i++) {
            $atr = (($atr * ($window - 1)) + $trValues[$i]) / $window;
        }

        return $atr;
    }

    private function priorVolumeAverage(array $bars, $index, $lookback)
    {
        if ($index < $lookback) {
            return null;
        }

        $slice = array_slice($bars, $index - $lookback, $lookback);
        if (count($slice) !== $lookback) {
            return null;
        }

        $volumes = [];
        foreach ($slice as $bar) {
            if (! array_key_exists('volume', $bar) || $bar['volume'] === null) {
                return null;
            }
            $volumes[] = (float) $bar['volume'];
        }

        return array_sum($volumes) / $lookback;
    }

    private function windowExtreme(array $bars, $index, $window, $field, $mode)
    {
        if ($index < 0 || ($index + 1) < $window) {
            return null;
        }

        $slice = array_slice($bars, $index - $window + 1, $window);
        if (count($slice) !== $window) {
            return null;
        }

        $values = [];
        foreach ($slice as $bar) {
            if (! array_key_exists($field, $bar) || $bar[$field] === null || (float) $bar[$field] <= 0) {
                return null;
            }
            $values[] = (float) $bar[$field];
        }

        return round($mode === 'min' ? min($values) : max($values), 4);
    }

    private function movingAverage(array $bars, $index, $window, array $config)
    {
        if ($index < 0 || ($index + 1) < $window) {
            return null;
        }

        $slice = array_slice($bars, $index - $window + 1, $window);
        if (count($slice) !== $window) {
            return null;
        }
        $values = [];

        foreach ($slice as $bar) {
            $price = $this->priceBasis($bar, $config);
            if ($price <= 0) {
                return null;
            }
            $values[] = $price;
        }

        return round(array_sum($values) / $window, 4);
    }

    private function roc(array $bars, $index, $lookback, array $config)
    {
        if ($index < $lookback || ! isset($bars[$index - $lookback])) {
            return null;
        }

        $current = $this->priceBasis($bars[$index], $config);
        $base = $this->priceBasis($bars[$index - $lookback], $config);

        if ($base <= 0) {
            return null;
        }

        return round(($current / $base) - 1, 10);
    }

    private function pctDifference($current, $base)
    {
        if ($current === null || $base === null || (float) $base <= 0) {
            return null;
        }

        return round((((float) $current - (float) $base) / (float) $base) * 100, 10);
    }

    private function rangePositionPct($current, $low, $high)
    {
        if ($current === null || $low === null || $high === null) {
            return null;
        }

        $range = (float) $high - (float) $low;
        if ($range <= 0) {
            return null;
        }

        return round((((float) $current - (float) $low) / $range) * 100, 10);
    }

    private function priceBasis(array $bar, array $config)
    {
        // applyPriceAdjustment has already built the selected coherent product in the canonical
        // OHLC fields. Provider adjusted-close observations never participate in this selector.
        return (float) $bar['close'];
    }

    private function normalizeSectorCode($sectorCode)
    {
        $sectorCode = strtoupper(trim((string) $sectorCode));

        return $sectorCode === '' ? null : $sectorCode;
    }

    private function eventRiskValues(array $config)
    {
        $context = $config['event_risk_context'] ?? [];
        if (! is_array($context)) {
            $context = [];
        }

        return [
            'corporate_action_flag' => $this->nullableFlag($context['corporate_action_flag'] ?? null),
            'corporate_action_types' => $this->nullableContextString($context['corporate_action_types'] ?? null),
            'trading_status_code' => $this->nullableContextString($context['trading_status_code'] ?? null),
            'is_suspended' => $this->nullableFlag($context['is_suspended'] ?? null),
            'is_uma' => $this->nullableFlag($context['is_uma'] ?? null),
            'event_risk_flag' => $this->nullableFlag($context['event_risk_flag'] ?? null),
            'event_risk_reasons' => $this->nullableContextString($context['event_risk_reasons'] ?? null),
        ];
    }

    private function nullableFlag($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value === 1 ? 1 : 0;
    }

    private function nullableContextString($value)
    {
        $value = strtoupper(trim((string) $value));

        return $value === '' ? null : substr($value, 0, 255);
    }
}
