<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestEvaluationRepository;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestIsCalibrationService
{
    private WatchlistBacktestPublishedPriceRuntimeService $runtime;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestEvaluationRepository $evaluations;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;

    public function __construct(
        WatchlistBacktestPublishedPriceRuntimeService $runtime = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestEvaluationRepository $evaluations = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null
    ) {
        $this->runtime = $runtime ?: new WatchlistBacktestPublishedPriceRuntimeService();
        $this->paramGrid = $paramGrid ?: new WatchlistBacktestParamGridRepository();
        $this->evaluations = $evaluations ?: new WatchlistBacktestEvaluationRepository();
        $this->paramsetFactory = $paramsetFactory ?: new WatchlistBacktestParamGridParamsetFactory();
    }

    public function calibrate(array $isDates, array $options = []): array
    {
        $dates = $this->normalizeDates($isDates);
        if ($dates === []) {
            return $this->blocked('WS_BT_OOS_WINDOW_INSUFFICIENT', 'In-sample trading-date window is empty.');
        }

        $policyCode = (string) ($options['policy_code'] ?? 'WS');
        $catalogCode = trim((string) ($options['catalog_code'] ?? WatchlistBacktestParamGridCatalog::CATALOG_CODE));
        if ($catalogCode === '') {
            return $this->blocked('WS_BT_R2_CATALOG_MISSING', 'Explicit catalog_code is required for IS calibration.');
        }
        $gridRows = $catalogCode === WatchlistBacktestParamGridCatalog::CATALOG_CODE
            ? $this->paramGrid->allForPolicy($policyCode)
            : $this->paramGrid->allForCatalog($catalogCode, $policyCode);
        if ($gridRows === []) {
            $isLegacyR1 = $catalogCode === WatchlistBacktestParamGridCatalog::CATALOG_CODE;
            return $this->blocked(
                $isLegacyR1 ? 'WS_BT_OOS_PROOF_MISSING' : 'WS_BT_R2_CATALOG_MISSING',
                $isLegacyR1
                    ? 'Official watchlist_bt_param_grid contains no row for the requested policy.'
                    : 'Official watchlist_bt_param_grid contains no row for the requested explicit catalog.',
                ['param_grid_count' => 0]
            );
        }

        $isLegacyR1 = $catalogCode === WatchlistBacktestParamGridCatalog::CATALOG_CODE;
        usort($gridRows, function (array $left, array $right) use ($isLegacyR1): int {
            if ($isLegacyR1) {
                return ((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0));
            }
            $comparison = strcmp((string) ($left['row_code'] ?? ''), (string) ($right['row_code'] ?? ''));
            return $comparison !== 0
                ? $comparison
                : (((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0)));
        });

        $catalogVersions = array_values(array_unique(array_map(function (array $row) use ($isLegacyR1): string {
            return (string) ($row['catalog_version'] ?? ($isLegacyR1 ? WatchlistBacktestParamGridCatalog::CATALOG_VERSION : ''));
        }, $gridRows)));
        $catalogHashes = array_values(array_unique(array_map(function (array $row) use ($isLegacyR1): string {
            return (string) ($row['catalog_hash'] ?? ($isLegacyR1 ? WatchlistBacktestParamGridCatalog::hash() : ''));
        }, $gridRows)));
        if (count($catalogVersions) !== 1 || count($catalogHashes) !== 1
            || $catalogVersions[0] === '' || $catalogHashes[0] === '') {
            throw new \RuntimeException('WS_BT_R2_CATALOG_IDENTITY_CONFLICT: persisted catalog identity is inconsistent.');
        }
        $catalogVersion = $catalogVersions[0];
        $catalogHash = $catalogHashes[0];

        $from = $dates[0];
        $to = $dates[count($dates) - 1];
        $expectedDateHash = $this->stableHash($dates);
        $references = [];
        $valid = [];

        foreach ($gridRows as $gridRow) {
            $paramId = (int) ($gridRow['param_id'] ?? 0);
            $rowCode = (string) ($gridRow['row_code'] ?? ('PARAM_'.$paramId));
            $paramset = $this->paramsetFromGridRow($gridRow);
            $runtimeOptions = [
                'paramset' => $paramset,
                'executed_at' => $options['executed_at'] ?? null,
            ];
            if ($catalogCode !== WatchlistBacktestParamGridCatalog::CATALOG_CODE) {
                $runtimeOptions['hard_market_data_to_date'] = $to;
            }
            $result = $this->runtime->evaluateWindow($from, $to, $runtimeOptions);

            if (! ($result['is_ready'] ?? false)) {
                $reference = [
                    'param_id' => $paramId,
                    'status' => 'RUNTIME_FAILED',
                    'reason_code' => $result['reason_code'] ?? 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_NOT_READY',
                    'eval_id' => null,
                    'calibration_valid' => false,
                    'artifact_hash' => null,
                    'diagnostics' => $result['diagnostics'] ?? [],
                ];
                if (! $isLegacyR1) {
                    $reference = array_merge([
                        'param_id' => $paramId,
                        'row_code' => $rowCode,
                        'catalog_code' => $catalogCode,
                    ], array_diff_key($reference, ['param_id' => true]));
                }
                $references[] = $reference;
                unset($result, $paramset);
                $this->releaseIterationMemory();
                continue;
            }

            $resolvedDates = $this->normalizeDates($result['calendar']['trade_dates'] ?? []);
            if ($this->stableHash($resolvedDates) !== $expectedDateHash) {
                throw new \RuntimeException('WS_BT_OOS_PROOF_MISSING: Runtime calendar dates differ from the frozen in-sample prefix.');
            }

            $artifact = $result['artifact'] ?? [];
            $metrics = $artifact['metrics']['canonical_eval_metrics'] ?? [];
            $sufficiency = $artifact['metrics']['metric_sufficiency'] ?? [];
            if (! ($sufficiency['required_fields_available'] ?? false)
                || ! ($sufficiency['thresholds_resolved'] ?? false)) {
                $reference = [
                    'param_id' => $paramId,
                    'status' => 'METRICS_INSUFFICIENT',
                    'reason_code' => 'WS_BT_EVAL_METRICS_MISSING',
                    'eval_id' => null,
                    'calibration_valid' => false,
                    'missing_fields' => $sufficiency['missing_required_fields'] ?? [],
                    'artifact_hash' => $result['artifact_hash'] ?? null,
                    'trade_evidence' => $this->extremeTradeEvidence($artifact['metrics']['evaluated_trades'] ?? []),
                ];
                if (! $isLegacyR1) {
                    $reference = array_merge([
                        'param_id' => $paramId,
                        'row_code' => $rowCode,
                        'catalog_code' => $catalogCode,
                    ], array_diff_key($reference, ['param_id' => true]));
                }
                $references[] = $reference;
                unset($result, $artifact, $metrics, $sufficiency, $paramset);
                $this->releaseIterationMemory();
                continue;
            }

            $evalModel = $this->evalModel($paramset);
            $paramsetHash = $this->stableHash($paramset);
            $persisted = $this->evaluations->persist($this->evaluationRow(
                $policyCode,
                $catalogCode,
                $catalogVersion,
                $catalogHash,
                $isLegacyR1,
                $paramId,
                $evalModel,
                $paramsetHash,
                $from,
                $to,
                $metrics
            ));
            $sufficiencyReasonCodes = $this->sufficiencyReasonCodes($sufficiency);
            $reference = [
                'param_id' => $paramId,
                'status' => ($sufficiency['calibration_valid'] ?? false) ? 'VALID' : 'GATES_FAILED',
                'reason_code' => $sufficiencyReasonCodes[0] ?? null,
                'reason_codes' => $sufficiencyReasonCodes,
                'eval_id' => $persisted['eval_id'],
                'persistence_status' => $persisted['status'],
                'calibration_valid' => (bool) ($sufficiency['calibration_valid'] ?? false),
                'metrics' => $metrics,
                'gates' => $sufficiency['gates'] ?? [],
                'effective_thresholds' => $sufficiency['effective_thresholds'] ?? [],
                'artifact_hash' => $result['artifact_hash'] ?? null,
                'calendar_hash' => $result['calendar']['calendar_hash'] ?? null,
                'price_payload_hash' => $result['price_read']['price_series_manifest']['source_payload_hash'] ?? null,
                'publication_manifest_hash' => $this->stableHash($result['price_read']['publication_manifest'] ?? []),
                'paramset_snapshot' => $paramset,
                'paramset_hash' => $paramsetHash,
                'eval_model' => $evalModel,
                'price_read_mode' => $result['price_read']['price_series_manifest']['read_mode']
                    ?? $result['price_read']['price_series_manifest']['targeted_date_ticker_read']
                    ?? null,
                'requested_ticker_date_pair_count' => (int) ($result['price_read']['price_series_manifest']['requested_ticker_date_pair_count'] ?? 0),
                'trade_evidence' => $this->extremeTradeEvidence($artifact['metrics']['evaluated_trades'] ?? []),
            ];
            if (! $isLegacyR1) {
                $reference = array_merge([
                    'param_id' => $paramId,
                    'row_code' => $rowCode,
                    'catalog_code' => $catalogCode,
                    'catalog_version' => $catalogVersion,
                    'catalog_hash' => $catalogHash,
                ], array_diff_key($reference, ['param_id' => true]));
                $reference['requested_ticker_date_pair_count'] = (int) ($result['artifact']['runtime_execution']['requested_ticker_date_pair_count']
                    ?? $result['price_read']['price_series_manifest']['requested_ticker_date_pair_count']
                    ?? 0);
                $reference['strict_is_boundary'] = (bool) ($result['artifact']['runtime_execution']['strict_is_boundary'] ?? false);
                $reference['hard_market_data_to_date'] = $result['artifact']['runtime_execution']['hard_market_data_to_date'] ?? null;
                $reference['max_requested_market_data_date'] = $result['artifact']['runtime_execution']['max_requested_market_data_date'] ?? null;
                $reference['strategy_trade_date_count'] = (int) ($result['artifact']['runtime_execution']['strategy_trade_date_count'] ?? count($dates));
                $reference['boundary_censored_trade_date_count'] = (int) ($result['artifact']['runtime_execution']['boundary_censored_trade_date_count'] ?? 0);
            }
            $references[] = $reference;
            if ($reference['calibration_valid']) {
                $valid[] = $reference;
            }
            unset($result, $artifact, $metrics, $sufficiency, $paramset, $persisted, $reference, $evalModel, $paramsetHash);
            $this->releaseIterationMemory();
        }

        usort($valid, function (array $left, array $right): int {
            foreach (['avg_ret_net_top', 'median_ret_net_top', 'month_win_rate_min', 'p25_ret_net_top'] as $metric) {
                $comparison = ((float) ($right['metrics'][$metric] ?? 0.0)) <=> ((float) ($left['metrics'][$metric] ?? 0.0));
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return ((int) $left['param_id']) <=> ((int) $right['param_id']);
        });

        $best = $valid[0] ?? null;
        $binding = $best === null ? null : [
            'param_id_best_is' => $best['param_id'],
            'is_eval_id' => $best['eval_id'],
            'paramset_snapshot' => $best['paramset_snapshot'],
            'paramset_hash' => $best['paramset_hash'],
            'is_metrics' => $best['metrics'],
            'is_metrics_hash' => $this->stableHash($best['metrics']),
            'is_artifact_hash' => $best['artifact_hash'],
            'eval_model' => $best['eval_model'],
            'is_from' => $from,
            'is_to' => $to,
            'is_trading_date_hash' => $expectedDateHash,
            'calendar_hash' => $best['calendar_hash'],
            'price_payload_hash' => $best['price_payload_hash'],
            'publication_manifest_hash' => $best['publication_manifest_hash'],
            'ranking_policy' => [
                'avg_ret_net_top_desc',
                'median_ret_net_top_desc',
                'month_win_rate_min_desc',
                'p25_ret_net_top_desc',
                'param_id_asc',
            ],
        ];
        if ($binding !== null) {
            if (! $isLegacyR1) {
                $binding = array_merge([
                    'policy_code' => $policyCode,
                    'catalog_code' => $catalogCode,
                    'catalog_version' => $catalogVersion,
                    'catalog_hash' => $catalogHash,
                    'row_code' => $best['row_code'],
                ], $binding);
            }
            $binding['binding_hash'] = $this->stableHash($binding);
        }

        $noValidReasonCode = $this->noValidReasonCode($catalogCode);

        $paramGridHashRows = $isLegacyR1
            ? array_map([WatchlistBacktestParamGridCatalog::class, 'legacyRuntimeRow'], $gridRows)
            : $gridRows;
        $response = [
            'ready' => $binding !== null,
            'is_ready' => $binding !== null,
            'status' => $binding !== null ? 'READY' : 'BLOCKED',
            'reason_code' => $binding !== null ? null : $noValidReasonCode,
            'policy_code' => $policyCode,
            'is_from' => $from,
            'is_to' => $to,
            'is_trading_date_count' => count($dates),
            'is_trading_date_hash' => $expectedDateHash,
            'param_grid_count' => count($gridRows),
            'param_grid_hash' => $this->stableHash($paramGridHashRows),
            'is_valid_param_count' => count($valid),
            'is_failed_param_count' => count($references) - count($valid),
            'is_failure_reason_codes' => $this->evaluationFailureReasonCodes($references),
            'is_max_picks_count' => $this->maxMetric($references, 'picks_count'),
            'is_max_days_covered' => $this->maxMetric($references, 'days_covered'),
            'evaluations' => $references,
            'best_is_binding' => $binding,
            'diagnostics' => $binding !== null ? [] : [[
                'reason_code' => $noValidReasonCode,
                'message' => 'No in-sample parameter passed every canonical metric sufficiency gate; no best-IS binding was created.',
                'fatal' => true,
            ]],
            'production_ready' => false,
        ];
        if (! $isLegacyR1) {
            $response = array_merge([
                'ready' => $response['ready'],
                'is_ready' => $response['is_ready'],
                'status' => $response['status'],
                'reason_code' => $response['reason_code'],
                'policy_code' => $policyCode,
                'catalog_code' => $catalogCode,
                'catalog_version' => $catalogVersion,
                'catalog_hash' => $catalogHash,
                'ordered_param_hashes' => array_values(array_map(function (array $reference): ?string {
                    return $reference['paramset_hash'] ?? null;
                }, $references)),
            ], array_diff_key($response, [
                'ready' => true, 'is_ready' => true, 'status' => true, 'reason_code' => true, 'policy_code' => true,
            ]));
        }

        return $response;
    }

    private function sufficiencyReasonCodes(array $sufficiency): array
    {
        if (! ($sufficiency['required_fields_available'] ?? false)
            || ! ($sufficiency['thresholds_resolved'] ?? false)) {
            return ['WS_BT_EVAL_METRICS_MISSING'];
        }

        $gates = is_array($sufficiency['gates'] ?? null) ? $sufficiency['gates'] : [];
        $codes = [];
        if (($gates['minimum_trade_count'] ?? null) === false) {
            $codes[] = 'WS_BT_EVAL_MIN_TRADES_FAIL';
        }
        if (($gates['minimum_coverage'] ?? null) === false) {
            $codes[] = 'WS_BT_EVAL_MIN_DAYS_FAIL';
        }
        if (($gates['average_return_positive'] ?? null) === false
            || ($gates['median_return_non_negative'] ?? null) === false) {
            $codes[] = 'WS_BT_EVAL_ROBUST_RETURN_FAIL';
        }
        if (($gates['p25_downside_bound'] ?? null) === false) {
            $codes[] = 'WS_BT_EVAL_DOWNSIDE_FAIL';
        }
        if (($gates['monthly_win_rate_floor'] ?? null) === false
            || ($gates['monthly_average_floor'] ?? null) === false) {
            $codes[] = 'WS_BT_EVAL_STABILITY_FAIL';
        }

        return array_values(array_unique($codes));
    }

    private function noValidReasonCode(string $catalogCode): string
    {
        if ($catalogCode === WatchlistBacktestParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_OOS_PROOF_MISSING';
        }
        if ($catalogCode === WatchlistBacktestC01ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C01_NO_VALID_IS_CANDIDATE';
        }
        if ($catalogCode === WatchlistBacktestC03ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C03_NO_VALID_IS_CANDIDATE';
        }
        if ($catalogCode === WatchlistBacktestC04ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C04_NO_VALID_IS_CANDIDATE';
        }
        if ($catalogCode === WatchlistBacktestC05ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C05_NO_VALID_IS_CANDIDATE';
        }
        if ($catalogCode === WatchlistBacktestC06ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C06_NO_VALID_IS_CANDIDATE';
        }
        if ($catalogCode === WatchlistBacktestC07ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C07_NO_VALID_IS_CANDIDATE';
        }
        if ($catalogCode === WatchlistBacktestC14ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C14_NO_VALID_IS_CANDIDATE';
        }
        if ($catalogCode === WatchlistBacktestC15ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C15_NO_VALID_IS_CANDIDATE';
        }
        if ($catalogCode === WatchlistBacktestC16ParamGridCatalog::CATALOG_CODE) {
            return 'WS_BT_C16_NO_VALID_IS_CANDIDATE';
        }

        return 'WS_BT_R2_NO_VALID_IS_CANDIDATE';
    }

    private function paramsetFromGridRow(array $row): array
    {
        return $this->paramsetFactory->make($row);
    }

    private function evaluationRow(
        string $policyCode,
        string $catalogCode,
        string $catalogVersion,
        string $catalogHash,
        bool $isLegacyR1,
        int $paramId,
        string $evalModel,
        string $paramsetHash,
        string $from,
        string $to,
        array $metrics
    ): array
    {
        $row = [
            'policy_code' => $policyCode,
            'param_id' => $paramId,
            'eval_model' => $evalModel,
            'paramset_hash' => $paramsetHash,
            'from_date' => $from,
            'to_date' => $to,
            'days_covered' => $metrics['days_covered'],
            'picks_count' => $metrics['picks_count'],
            'avg_ret_net_top' => $metrics['avg_ret_net_top'],
            'win_rate_top' => $metrics['win_rate_top'],
            'median_ret_net_top' => $metrics['median_ret_net_top'],
            'p25_ret_net_top' => $metrics['p25_ret_net_top'],
            'p75_ret_net_top' => $metrics['p75_ret_net_top'],
            'min_ret_net_top' => $metrics['min_ret_net_top'],
            'max_ret_net_top' => $metrics['max_ret_net_top'],
            'periods_count' => $metrics['periods_count'],
            'period_fail_count' => $metrics['period_fail_count'],
            'month_win_rate_min' => $metrics['month_win_rate_min'],
            'month_avg_ret_net_min' => $metrics['month_avg_ret_net_min'],
            'avg_ret_net_all' => null,
            'win_rate_all' => null,
            'median_ret_net_all' => null,
            'p25_ret_net_all' => null,
            'p75_ret_net_all' => null,
            'min_ret_net_all' => null,
            'max_ret_net_all' => null,
        ];
        if (! $isLegacyR1) {
            $row = array_merge([
                'policy_code' => $policyCode,
                'catalog_code' => $catalogCode,
                'catalog_version' => $catalogVersion,
                'catalog_hash' => $catalogHash,
            ], array_diff_key($row, ['policy_code' => true]));
        }

        return $row;
    }

    private function evalModel(array $paramset): string
    {
        return WatchlistBacktestStrategyService::canonicalEvalModel($paramset);
    }

    private function extremeTradeEvidence(array $evaluatedTrades, int $limit = 10): array
    {
        $ready = [];
        foreach ($evaluatedTrades as $trade) {
            if (! is_array($trade) || ! ($trade['metrics_ready'] ?? false) || ! is_numeric($trade['ret_net'] ?? null)) {
                continue;
            }
            $ready[] = $this->tradeEvidenceRow($trade);
        }

        usort($ready, function (array $left, array $right): int {
            $comparison = ((float) ($left['ret_net'] ?? 0.0)) <=> ((float) ($right['ret_net'] ?? 0.0));
            if ($comparison !== 0) {
                return $comparison;
            }
            foreach (['trade_date', 'ticker', 'entry_trade_date', 'exit_trade_date'] as $key) {
                $comparison = strcmp((string) ($left[$key] ?? ''), (string) ($right[$key] ?? ''));
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return ((int) ($left['ticker_id'] ?? 0)) <=> ((int) ($right['ticker_id'] ?? 0));
        });

        $limit = max(1, min(25, $limit));
        $worst = array_slice($ready, 0, $limit);
        $best = array_reverse(array_slice($ready, -$limit));

        return [
            'evaluated_trade_count' => count($ready),
            'limit_per_side' => $limit,
            'worst_trades' => $worst,
            'best_trades' => $best,
        ];
    }

    private function tradeEvidenceRow(array $trade): array
    {
        $keys = [
            'trade_date',
            'ticker_id',
            'ticker',
            'bucket_code',
            'entry_trade_date',
            'exit_trade_date',
            'exit_reason_code',
            'entry_price',
            'exit_price',
            'executed_price',
            'trigger_price',
            'fill_rule',
            'gap_detected',
            'gap_fill_rule',
            'source_price_mode',
            'price_fraction_rule',
            'price_fraction_reference',
            'price_normalization_rule',
            'stop_price',
            'target_price',
            'stop_trigger_price',
            'target_trigger_price',
            'target_stop_source',
            'atr14_pct',
            'stop_atr_mult',
            'min_rr',
            'entry_volume',
            'exit_volume',
            'ret_gross',
            'ret_net',
            'is_win',
            'entry_publication_id',
            'entry_publication_version',
            'entry_run_id',
            'exit_publication_id',
            'exit_publication_version',
            'exit_run_id',
        ];
        $row = [];
        foreach ($keys as $key) {
            $row[$key] = $trade[$key] ?? null;
        }

        return $row;
    }

    private function evaluationFailureReasonCodes(array $references): array
    {
        $codes = [];
        foreach ($references as $reference) {
            if (($reference['calibration_valid'] ?? false) === true) {
                continue;
            }
            $reasonCodes = is_array($reference['reason_codes'] ?? null)
                ? $reference['reason_codes']
                : [$reference['reason_code'] ?? null];
            foreach ($reasonCodes as $reasonCode) {
                if (is_string($reasonCode) && $reasonCode !== '') {
                    $codes[$reasonCode] = true;
                }
            }
        }
        $values = array_keys($codes);
        sort($values, SORT_STRING);

        return $values;
    }

    private function maxMetric(array $references, string $metric): int
    {
        $max = 0;
        foreach ($references as $reference) {
            $max = max($max, (int) ($reference['metrics'][$metric] ?? 0));
        }

        return $max;
    }

    private function releaseIterationMemory(): void
    {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_merge([
            'ready' => false,
            'is_ready' => false,
            'reason_code' => $reasonCode,
            'diagnostics' => [[
                'reason_code' => $reasonCode,
                'message' => $message,
                'fatal' => true,
            ]],
            'production_ready' => false,
        ], $extra);
    }

    private function normalizeDates(array $dates): array
    {
        $normalized = [];
        foreach ($dates as $date) {
            if (! is_scalar($date)) {
                continue;
            }
            $value = trim((string) $date);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $normalized[$value] = $value;
            }
        }
        ksort($normalized, SORT_STRING);

        return array_values($normalized);
    }

    private function stableHash(array $payload): string
    {
        return sha1(json_encode($this->normalizeForHash($payload), JSON_UNESCAPED_SLASHES));
    }

    private function normalizeForHash($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) {
                return $this->normalizeForHash($item);
            }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeForHash($item);
        }

        return $value;
    }
}
