<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use RuntimeException;

class WatchlistBacktestIsFailureDrilldownService
{
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c01-is-failure-drilldown.json';
    private const FIELD_NOT_AVAILABLE = 'FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE';
    private const NOT_DERIVED = 'NOT_DERIVED';
    private const NOT_USED_FOR_NEXT_CATALOG_DECISION = 'NOT_USED_FOR_NEXT_CATALOG_DECISION';

    private WatchlistBacktestPublishedPriceRuntimeService $runtime;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;

    public function __construct(
        WatchlistBacktestPublishedPriceRuntimeService $runtime = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null
    ) {
        $this->runtime = $runtime ?: new WatchlistBacktestPublishedPriceRuntimeService();
        $this->paramGrid = $paramGrid ?: new WatchlistBacktestParamGridRepository();
        $this->paramsetFactory = $paramsetFactory ?: new WatchlistBacktestParamGridParamsetFactory();
    }

    public function execute(string $catalogCode, string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $catalogCode = trim($catalogCode);
        $fromDate = trim($fromDate);
        $toDate = trim($toDate);
        $outputPath = trim($outputPath);

        if ($catalogCode === '') {
            return $this->blocked('WS_BT_C01_CATALOG_MISSING', 'Explicit catalog_code is required for IS drilldown.');
        }
        if (! $this->validDate($fromDate) || ! $this->validDate($toDate) || strcmp($fromDate, $toDate) > 0) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Explicit from/to date window is invalid.');
        }
        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked(
                strcmp($toDate, WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) > 0
                    ? 'WS_BT_C01_IS_BOUNDARY_VIOLATION'
                    : 'WS_BT_C01_IS_WINDOW_MISMATCH',
                'C01 IS failure drilldown requires the exact frozen IS window.'
            );
        }
        if ($outputPath === '' || is_dir($outputPath)) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Explicit non-directory output path is required.');
        }
        if (is_file($outputPath) && ! ($options['overwrite'] ?? false)) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID', 'Output file already exists. Use overwrite explicitly.');
        }

        $policyCode = (string) ($options['policy_code'] ?? 'WS');
        $rows = $this->paramGrid->allForCatalog($catalogCode, $policyCode);
        if ($rows === []) {
            return $this->blocked('WS_BT_C01_CATALOG_MISSING', 'No rows found for explicit catalog_code.', [
                'catalog_code' => $catalogCode,
                'policy_code' => $policyCode,
            ]);
        }

        $rowFilter = $this->rowFilter($options);
        if ($rowFilter !== null) {
            $rows = array_values(array_filter($rows, function (array $row) use ($rowFilter): bool {
                if ($rowFilter['param_id'] !== null && (int) ($row['param_id'] ?? 0) !== $rowFilter['param_id']) {
                    return false;
                }
                if ($rowFilter['row_code'] !== null && (string) ($row['row_code'] ?? '') !== $rowFilter['row_code']) {
                    return false;
                }

                return true;
            }));
            if ($rows === []) {
                return $this->blocked('WS_BT_IS_DRILLDOWN_ROW_FILTER_NO_MATCH', 'No catalog rows matched the explicit drilldown row filter.', [
                    'catalog_code' => $catalogCode,
                    'policy_code' => $policyCode,
                    'row_filter' => $rowFilter,
                ]);
            }
        }

        usort($rows, function (array $left, array $right): int {
            $comparison = strcmp((string) ($left['row_code'] ?? ''), (string) ($right['row_code'] ?? ''));
            if ($comparison !== 0) {
                return $comparison;
            }

            return ((int) ($left['param_id'] ?? 0)) <=> ((int) ($right['param_id'] ?? 0));
        });

        $paramDiagnostics = [];
        $allEvaluatedTrades = [];
        $allRuntimeDiagnostics = [];
        $catalogHashes = [];
        $catalogVersions = [];
        $isTradingDateHashes = [];
        $isTradingDateCounts = [];
        $maxRequestedDate = null;
        $strictBoundaryAll = true;
        $oosBoundaryOk = true;
        $runtimeReadyAll = true;

        foreach ($rows as $row) {
            $catalogHashes[(string) ($row['catalog_hash'] ?? '')] = true;
            $catalogVersions[(string) ($row['catalog_version'] ?? '')] = true;
            $paramset = $this->paramsetFactory->make($row);
            $runtimeResult = $this->runtime->evaluateWindow($fromDate, $toDate, [
                'paramset' => $paramset,
                'hard_market_data_to_date' => $toDate,
                'executed_at' => $options['executed_at'] ?? null,
            ]);
            $runtimeCalendarDates = $this->normalizeDateList($runtimeResult['calendar']['trade_dates'] ?? []);
            if ($runtimeCalendarDates !== []) {
                $isTradingDateHashes[$this->stableHash($runtimeCalendarDates)] = true;
                $isTradingDateCounts[(string) count($runtimeCalendarDates)] = true;
            }

            $runtimeReady = (bool) ($runtimeResult['is_ready'] ?? false);
            $runtimeReadyAll = $runtimeReadyAll && $runtimeReady;
            $artifact = is_array($runtimeResult['artifact'] ?? null) ? $runtimeResult['artifact'] : [];
            $metrics = is_array($artifact['metrics'] ?? null) ? $artifact['metrics'] : [];
            $sufficiency = is_array($metrics['metric_sufficiency'] ?? null) ? $metrics['metric_sufficiency'] : [];
            $canonicalMetrics = is_array($metrics['canonical_eval_metrics'] ?? null) ? $metrics['canonical_eval_metrics'] : [];
            $evaluatedTrades = is_array($metrics['evaluated_trades'] ?? null)
                ? $metrics['evaluated_trades']
                : (is_array($artifact['evaluations'] ?? null) ? $artifact['evaluations'] : []);
            $strategyTrades = is_array($artifact['trades'] ?? null) ? $artifact['trades'] : [];
            $enrichedTrades = $this->enrichEvaluatedTrades($evaluatedTrades, $strategyTrades, $row);
            $runtimeDiagnostics = array_merge(
                is_array($runtimeResult['diagnostics'] ?? null) ? $runtimeResult['diagnostics'] : [],
                is_array($artifact['diagnostics'] ?? null) ? $artifact['diagnostics'] : []
            );
            foreach ($runtimeDiagnostics as $diagnostic) {
                if (is_array($diagnostic)) {
                    $diagnostic['param_id'] = (int) ($row['param_id'] ?? 0);
                    $diagnostic['row_code'] = (string) ($row['row_code'] ?? '');
                    $allRuntimeDiagnostics[] = $diagnostic;
                }
            }

            foreach ($enrichedTrades as $trade) {
                $allEvaluatedTrades[] = $trade;
            }

            $runtimeExecution = is_array($artifact['runtime_execution'] ?? null) ? $artifact['runtime_execution'] : [];
            $paramMaxRequestedDate = $runtimeExecution['max_requested_market_data_date']
                ?? $runtimeResult['price_read']['price_series_manifest']['requested_to_date']
                ?? null;
            if (is_scalar($paramMaxRequestedDate)) {
                $paramMaxRequestedDate = (string) $paramMaxRequestedDate;
                if ($maxRequestedDate === null || strcmp($paramMaxRequestedDate, $maxRequestedDate) > 0) {
                    $maxRequestedDate = $paramMaxRequestedDate;
                }
                if (strcmp($paramMaxRequestedDate, $toDate) > 0) {
                    $oosBoundaryOk = false;
                }
            }
            $strictBoundaryAll = $strictBoundaryAll && (bool) ($runtimeExecution['strict_is_boundary'] ?? true);

            $reasonCodes = $this->reasonCodesFromSufficiency($sufficiency, $runtimeResult);
            $gates = is_array($sufficiency['gates'] ?? null) ? $sufficiency['gates'] : [];
            $thresholds = is_array($sufficiency['effective_thresholds'] ?? null)
                ? $sufficiency['effective_thresholds']
                : [];

            $paramDiagnostics[] = [
                'param_id' => (int) ($row['param_id'] ?? 0),
                'row_code' => (string) ($row['row_code'] ?? ''),
                'status' => $runtimeReady
                    ? (($sufficiency['calibration_valid'] ?? false) ? 'VALID' : 'GATES_FAILED')
                    : 'RUNTIME_FAILED',
                'runtime_ready' => $runtimeReady,
                'calibration_valid' => (bool) ($sufficiency['calibration_valid'] ?? false),
                'reason_codes' => $reasonCodes,
                'metrics' => $canonicalMetrics,
                'gates' => $gates,
                'effective_thresholds' => $thresholds,
                'nearest_gate_gap' => $this->nearestGateGap($canonicalMetrics, $thresholds),
                'worst_gate_gap' => $this->worstGateGap($canonicalMetrics, $thresholds),
                'trade_count' => count(array_filter($enrichedTrades, function (array $trade): bool {
                    return (bool) ($trade['metrics_ready'] ?? false);
                })),
                'diagnostic_count' => count($runtimeDiagnostics),
                'artifact_hash' => $runtimeResult['artifact_hash'] ?? ($artifact['meta']['artifact_hash'] ?? null),
                'paramset_hash' => $this->stableHash($paramset),
                'catalog_hash' => (string) ($row['catalog_hash'] ?? ''),
                'eval_model' => 'ENTRY=NEXT_OPEN;EXIT=STOP_TP_OR_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS',
            ];
        }

        $catalogHash = count($catalogHashes) === 1 ? (string) array_key_first($catalogHashes) : null;
        $catalogVersion = count($catalogVersions) === 1 ? (string) array_key_first($catalogVersions) : null;
        $isTradingDateHash = count($isTradingDateHashes) === 1 ? (string) array_key_first($isTradingDateHashes) : null;
        $isTradingDateCount = count($isTradingDateCounts) === 1 ? (int) array_key_first($isTradingDateCounts) : null;
        $runtimeFieldAvailability = $this->runtimeFieldAvailability($allEvaluatedTrades);
        $runtimeConsumedParameterSummary = $this->runtimeConsumedParameterSummary($rows);
        $artifact = [
            'meta' => [
                'artifact_version' => 'WATCHLIST_BT_IS_FAILURE_DRILLDOWN_V1',
                'generated_at' => $options['generated_at'] ?? null,
                'scope' => $catalogVersion === 'C01' ? 'IS_ONLY_C01_FAILURE_DRILLDOWN' : 'IS_ONLY_CATALOG_FAILURE_DRILLDOWN',
                'production_ready' => false,
                'oos_executed' => false,
                'paramset_promoted' => false,
            ],
            'catalog_code' => $catalogCode,
            'catalog_version' => $catalogVersion,
            'catalog_hash' => $catalogHash,
            'catalog_count' => count($rows),
            'row_filter' => $rowFilter,
            'is_from' => $fromDate,
            'is_to' => $toDate,
            'is_trading_date_count' => $isTradingDateCount,
            'is_trading_date_hash' => $isTradingDateHash,
            'is_window' => [
                'explicit_from' => $fromDate,
                'explicit_to' => $toDate,
                'no_current_date_default' => true,
                'no_max_trade_date_default' => true,
                'no_latest_catalog_fallback' => true,
                'no_active_catalog_fallback' => true,
            ],
            'per_param_status' => $this->perParamStatus($paramDiagnostics),
            'per_param_failure_codes' => $this->perParamFailureCodes($paramDiagnostics),
            'per_param_key_metrics' => $this->perParamKeyMetrics($paramDiagnostics),
            'nearest_gate_gap' => $this->bestNearestGateGap($paramDiagnostics),
            'worst_gate_gap' => $this->globalWorstGateGap($paramDiagnostics),
            'candidate_count_summary' => $this->metricSummary($paramDiagnostics, 'picks_count'),
            'days_covered_summary' => $this->metricSummary($paramDiagnostics, 'days_covered'),
            'month_win_rate_min_summary' => $this->metricSummary($paramDiagnostics, 'month_win_rate_min'),
            'month_avg_ret_min_summary' => $this->metricSummary($paramDiagnostics, 'month_avg_ret_net_min'),
            'downside_metric_summary' => $this->metricSummary($paramDiagnostics, 'p25_ret_net_top'),
            'robust_return_metric_summary' => [
                'avg_ret_net_top' => $this->metricSummary($paramDiagnostics, 'avg_ret_net_top'),
                'median_ret_net_top' => $this->metricSummary($paramDiagnostics, 'median_ret_net_top'),
            ],
            'stability_metric_summary' => [
                'month_win_rate_min' => $this->metricSummary($paramDiagnostics, 'month_win_rate_min'),
                'month_avg_ret_net_min' => $this->metricSummary($paramDiagnostics, 'month_avg_ret_net_min'),
                'period_fail_count' => $this->metricSummary($paramDiagnostics, 'period_fail_count'),
            ],
            'ticker_loss_cluster_summary' => $this->clusterBy($allEvaluatedTrades, 'ticker', false, 20),
            'ticker_profit_cluster_summary' => $this->clusterBy($allEvaluatedTrades, 'ticker', true, 20),
            'month_failure_cluster_summary' => $this->clusterBy($this->withMonth($allEvaluatedTrades), 'month', false, 20),
            'month_profit_cluster_summary' => $this->clusterBy($this->withMonth($allEvaluatedTrades), 'month', true, 20),
            'trade_date_failure_cluster_summary' => $this->clusterBy($allEvaluatedTrades, 'trade_date', false, 20),
            'setup_bucket_summary' => $this->clusterBy($allEvaluatedTrades, 'bucket_code', null, 20),
            'breakout_extension_bucket_summary' => $this->breakoutExtensionBucketSummary($allEvaluatedTrades),
            'momentum_roc_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'roc20', [-0.05, 0.0, 0.02, 0.05, 0.10, 0.15, 0.25]),
            'short_term_momentum_roc5_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'roc5', [-0.05, -0.02, 0.0, 0.02, 0.05, 0.10, 0.15]),
            'short_term_momentum_roc10_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'roc10', [-0.08, -0.03, 0.0, 0.02, 0.05, 0.10, 0.18]),
            'volume_ratio_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'vol_ratio', [1.0, 1.2, 1.5, 2.0, 2.5, 3.0]),
            'liquidity_dv20_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'dv20_idr', [1000000000, 2500000000, 5000000000, 7500000000, 10000000000, 15000000000, 20000000000]),
            'atr_bucket_summary' => $this->numericBucketSummary($allEvaluatedTrades, 'atr14_pct', [0.02, 0.03, 0.04, 0.05, 0.08, 0.12]),
            'close_to_ll20_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'close_to_ll20_pct', [0.0, 0.02, 0.04, 0.08, 0.12, 0.20]),
            'range_position_20_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'range_position_20_pct', [0.20, 0.35, 0.50, 0.65, 0.80, 0.95, 1.10]),
            'sector_roc20_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'sector_roc20', [-0.08, -0.04, -0.02, 0.0, 0.03, 0.06, 0.12]),
            'rs_20_vs_sector_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'rs_20_vs_sector', [-0.08, -0.04, -0.02, 0.0, 0.03, 0.06, 0.12]),
            'sector_rs_20_vs_ihsg_bucket_summary' => $this->numericBucketSummaryFromRuntime($allEvaluatedTrades, 'sector_rs_20_vs_ihsg', [-0.08, -0.04, -0.02, 0.0, 0.03, 0.06, 0.12]),
            'event_risk_flag_summary' => $this->eventRiskFlagSummary($allEvaluatedTrades),
            'score_bucket_summary' => $this->numericBucketSummary($allEvaluatedTrades, 'recommendation_score', [0.60, 0.70, 0.80, 0.90, 1.00]),
            'sector_bucket_summary' => $this->categoricalBucketSummaryFromRuntime($allEvaluatedTrades, 'sector_code'),
            'score_component_effectiveness_summary' => $this->scoreComponentEffectivenessSummary($allEvaluatedTrades),
            'grouping_quantile_summary' => $this->groupingQuantileSummary($rows, $paramDiagnostics),
            'param_axis_effectiveness_summary' => $this->paramAxisEffectiveness($rows, $paramDiagnostics),
            'runtime_consumed_parameter_summary' => $runtimeConsumedParameterSummary,
            'dead_parameter_or_silent_default_summary' => $this->deadParameterSummary($runtimeConsumedParameterSummary, $runtimeFieldAvailability),
            'runtime_field_availability_summary' => $runtimeFieldAvailability,
            'data_quality_diagnostic_summary' => $this->dataQualitySummary($allRuntimeDiagnostics, $allEvaluatedTrades),
            'no_oos_leakage_summary' => [
                'explicit_from' => $fromDate,
                'explicit_to' => $toDate,
                'max_requested_market_data_date' => $maxRequestedDate,
                'max_allowed_market_data_date' => $toDate,
                'max_date_within_boundary' => $maxRequestedDate === null || strcmp($maxRequestedDate, $toDate) <= 0,
                'strict_is_boundary_all_evaluations' => $strictBoundaryAll,
                'oos_service_invoked' => false,
                'oos_repository_invoked' => false,
                'oos_table_unchanged' => true,
                'oos_executed' => false,
                'production_ready' => false,
            ],
            'diagnostic_reason_summary' => $this->diagnosticReasonSummary($paramDiagnostics, $allRuntimeDiagnostics),
            'next_focus_recommendation' => $this->nextFocusRecommendation($paramDiagnostics, $allEvaluatedTrades, $runtimeFieldAvailability, $catalogVersion),
            'validation' => [
                'runtime_ready_all_params' => $runtimeReadyAll,
                'catalog_count_matches' => count($rows) === count($paramDiagnostics),
                'row_filter_applied' => $rowFilter !== null,
                'catalog_hash_single_value' => $catalogHash !== null,
                'is_trading_date_hash_single_value' => $isTradingDateHash !== null,
                'no_oos_market_data_read' => $oosBoundaryOk,
                'no_oos_table_mutation' => true,
                'best_of_failed_forbidden' => true,
                'best_is_binding' => null,
                'production_ready' => false,
            ],
        ];

        $artifact['canonical_artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['artifact_hash'] = $artifact['canonical_artifact_hash'];
        $artifact['meta']['canonical_artifact_hash'] = $artifact['canonical_artifact_hash'];
        $artifact['meta']['artifact_hash'] = $artifact['artifact_hash'];

        $write = $this->writeArtifact($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked($write['reason_code'], 'Diagnostic artifact write failed.', ['write' => $write]);
        }

        return [
            'is_ready' => true,
            'status' => 'DONE',
            'reason_code' => $catalogVersion === 'C01'
                ? 'WS_BT_C01_IS_FAILURE_DRILLDOWN_READY'
                : 'WS_BT_IS_FAILURE_DRILLDOWN_READY',
            'artifact_hash' => $artifact['artifact_hash'],
            'artifact' => $artifact,
            'write' => $write,
            'oos_executed' => false,
            'production_ready' => false,
        ];
    }

    private function enrichEvaluatedTrades(array $evaluatedTrades, array $strategyTrades, array $row): array
    {
        $tradeIndex = [];
        foreach ($strategyTrades as $trade) {
            if (! is_array($trade)) {
                continue;
            }
            $key = $this->tradeKey($trade);
            if ($key !== '') {
                $tradeIndex[$key] = $trade;
            }
        }

        $enriched = [];
        foreach ($evaluatedTrades as $trade) {
            if (! is_array($trade)) {
                continue;
            }
            $matching = $tradeIndex[$this->tradeKey($trade)] ?? [];
            $trade = array_merge($matching, $trade);
            $trade['param_id'] = (int) ($row['param_id'] ?? 0);
            $trade['row_code'] = (string) ($row['row_code'] ?? '');
            $trade['catalog_code'] = (string) ($row['catalog_code'] ?? '');
            $trade['ret_net'] = $this->floatOrNull($trade['ret_net'] ?? null);
            $trade['atr14_pct'] = $this->floatOrNull($trade['atr14_pct'] ?? null);
            $trade['recommendation_score'] = $this->floatOrNull($trade['recommendation_score'] ?? null);
            $trade['close_to_hh20_pct'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['close_to_hh20_pct'],
                ['score_metrics', 'close_to_hh20_pct'],
                ['factor_breakdown', 'breakout', 'close_to_hh20_pct'],
            ]));
            $trade['roc20'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['roc20'],
                ['score_metrics', 'roc20'],
                ['factor_breakdown', 'momentum', 'roc20'],
            ]));
            $trade['roc5'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['roc5'],
                ['roc_5'],
                ['score_metrics', 'roc5'],
                ['score_metrics', 'roc_5'],
            ]));
            $trade['roc10'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['roc10'],
                ['roc_10'],
                ['score_metrics', 'roc10'],
                ['score_metrics', 'roc_10'],
            ]));
            $trade['ll20'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['ll20'],
                ['score_metrics', 'll20'],
            ]));
            $trade['close_to_ll20_pct'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['close_to_ll20_pct'],
                ['score_metrics', 'close_to_ll20_pct'],
            ]));
            $trade['range_20_pct'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['range_20_pct'],
                ['score_metrics', 'range_20_pct'],
            ]));
            $trade['range_position_20_pct'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['range_position_20_pct'],
                ['score_metrics', 'range_position_20_pct'],
            ]));
            $trade['sector_roc20'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['sector_roc20'],
                ['score_metrics', 'sector_roc20'],
            ]));
            $trade['rs_20_vs_sector'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['rs_20_vs_sector'],
                ['score_metrics', 'rs_20_vs_sector'],
            ]));
            $trade['sector_rs_20_vs_ihsg'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['sector_rs_20_vs_ihsg'],
                ['score_metrics', 'sector_rs_20_vs_ihsg'],
            ]));
            $corporateActionTypes = $this->stringOrNull($this->firstAvailable($trade, [
                ['corporate_action_types'],
                ['score_metrics', 'corporate_action_types'],
            ]));
            $trade['corporate_action_types'] = $corporateActionTypes;
            $trade['trading_status_code'] = $this->stringOrNull($this->firstAvailable($trade, [
                ['trading_status_code'],
                ['score_metrics', 'trading_status_code'],
            ]));
            $trade['event_risk_reasons'] = $this->stringOrNull($this->firstAvailable($trade, [
                ['event_risk_reasons'],
                ['score_metrics', 'event_risk_reasons'],
            ]));
            $trade['corporate_action_flag'] = $this->corporateActionFlagOrNull($this->firstAvailable($trade, [
                ['corporate_action_flag'],
                ['score_metrics', 'corporate_action_flag'],
            ]), $corporateActionTypes);
            foreach (['is_suspended', 'is_uma', 'event_risk_flag'] as $flagField) {
                $trade[$flagField] = $this->flagOrNull($this->firstAvailable($trade, [
                    [$flagField],
                    ['score_metrics', $flagField],
                ]));
            }
            $trade['vol_ratio'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['vol_ratio'],
                ['score_metrics', 'vol_ratio'],
                ['factor_breakdown', 'volume', 'vol_ratio'],
            ]));
            $trade['dv20_idr'] = $this->floatOrNull($this->firstAvailable($trade, [
                ['dv20_idr'],
                ['score_metrics', 'dv20_idr'],
                ['factor_breakdown', 'volume', 'dv20_idr'],
            ]));
            $sector = $this->firstAvailable($trade, [
                ['sector_code'],
                ['sector'],
                ['score_metrics', 'sector_code'],
            ]);
            $trade['sector_code'] = is_scalar($sector) && trim((string) $sector) !== ''
                ? strtoupper(trim((string) $sector))
                : null;
            $scoreComponents = is_array($trade['score_components'] ?? null) ? $trade['score_components'] : [];
            foreach (['score_momentum', 'score_breakout', 'score_volume', 'score_risk'] as $component) {
                $trade[$component] = $this->floatOrNull($trade[$component] ?? ($scoreComponents[$component] ?? null));
            }
            if ($trade['close_to_hh20_pct'] !== null) {
                $nearBelowPct = $this->floatOrNull($row['bo_near_below_pct'] ?? null);
                $maxExtensionPct = $this->floatOrNull($row['bo_max_ext_pct'] ?? null);
                $trade['bo_near_below_pct'] = $nearBelowPct;
                $trade['bo_max_ext_pct'] = $maxExtensionPct;
                $trade['bo_near_below_hit'] = $nearBelowPct === null ? null : $trade['close_to_hh20_pct'] >= -$nearBelowPct;
                $trade['bo_max_ext_hit'] = $maxExtensionPct === null ? null : $trade['close_to_hh20_pct'] <= $maxExtensionPct;
            }
            $trade['is_win'] = ($trade['ret_net'] ?? null) === null ? null : ((float) $trade['ret_net'] > 0.0);
            $trade['metrics_ready'] = (bool) ($trade['metrics_ready'] ?? (($trade['ret_net'] ?? null) !== null));
            $enriched[] = $trade;
        }

        return $enriched;
    }

    private function tradeKey(array $trade): string
    {
        $date = (string) ($trade['trade_date'] ?? '');
        $ticker = (string) ($trade['ticker'] ?? $trade['ticker_code'] ?? $trade['ticker_id'] ?? '');

        return $date === '' || $ticker === '' ? '' : $date.'|'.strtoupper($ticker);
    }

    private function reasonCodesFromSufficiency(array $sufficiency, array $runtimeResult): array
    {
        if (! ($runtimeResult['is_ready'] ?? false)) {
            return $this->uniqueReasonCodes([$runtimeResult['reason_code'] ?? 'WATCHLIST_BACKTEST_RUNTIME_ARTIFACT_NOT_READY']);
        }

        $codes = [];
        $gates = is_array($sufficiency['gates'] ?? null) ? $sufficiency['gates'] : [];
        if (($gates['average_return_positive'] ?? true) === false || ($gates['median_return_non_negative'] ?? true) === false) {
            $codes[] = 'WS_BT_EVAL_ROBUST_RETURN_FAIL';
        }
        if (($gates['p25_downside_bound'] ?? true) === false) {
            $codes[] = 'WS_BT_EVAL_DOWNSIDE_FAIL';
        }
        if (($gates['monthly_win_rate_floor'] ?? true) === false || ($gates['monthly_average_floor'] ?? true) === false) {
            $codes[] = 'WS_BT_EVAL_STABILITY_FAIL';
        }
        if ($codes === [] && ($sufficiency['calibration_valid'] ?? false) !== true) {
            $codes[] = 'WS_BT_EVAL_METRICS_MISSING';
        }

        return $this->uniqueReasonCodes($codes);
    }

    private function nearestGateGap(array $metrics, array $thresholds): ?array
    {
        $gaps = $this->gateGaps($metrics, $thresholds);
        $negative = array_values(array_filter($gaps, function (array $gap): bool {
            return $gap['gap'] < 0;
        }));
        if ($negative === []) {
            return null;
        }
        usort($negative, function (array $left, array $right): int {
            return abs($left['gap']) <=> abs($right['gap']);
        });

        return $negative[0];
    }

    private function worstGateGap(array $metrics, array $thresholds): ?array
    {
        $gaps = $this->gateGaps($metrics, $thresholds);
        $negative = array_values(array_filter($gaps, function (array $gap): bool {
            return $gap['gap'] < 0;
        }));
        if ($negative === []) {
            return null;
        }
        usort($negative, function (array $left, array $right): int {
            return $left['gap'] <=> $right['gap'];
        });

        return $negative[0];
    }

    private function gateGaps(array $metrics, array $thresholds): array
    {
        $specs = [
            ['metric' => 'avg_ret_net_top', 'threshold' => 0.0, 'direction' => 'gte'],
            ['metric' => 'median_ret_net_top', 'threshold' => 0.0, 'direction' => 'gte'],
            ['metric' => 'p25_ret_net_top', 'threshold' => $thresholds['min_p25_ret_net_top'] ?? -0.03, 'direction' => 'gte'],
            ['metric' => 'month_win_rate_min', 'threshold' => $thresholds['min_month_win_rate_min'] ?? 0.45, 'direction' => 'gte'],
            ['metric' => 'month_avg_ret_net_min', 'threshold' => $thresholds['min_month_avg_ret_net_min'] ?? -0.01, 'direction' => 'gte'],
        ];
        $gaps = [];
        foreach ($specs as $spec) {
            $value = $this->floatOrNull($metrics[$spec['metric']] ?? null);
            if ($value === null) {
                continue;
            }
            $threshold = (float) $spec['threshold'];
            $gaps[] = [
                'metric' => $spec['metric'],
                'value' => $value,
                'threshold' => $threshold,
                'gap' => $value - $threshold,
            ];
        }

        return $gaps;
    }

    private function perParamStatus(array $paramDiagnostics): array
    {
        return array_map(function (array $param): array {
            return [
                'param_id' => $param['param_id'],
                'row_code' => $param['row_code'],
                'status' => $param['status'],
                'calibration_valid' => $param['calibration_valid'],
            ];
        }, $paramDiagnostics);
    }

    private function perParamFailureCodes(array $paramDiagnostics): array
    {
        return array_map(function (array $param): array {
            return [
                'param_id' => $param['param_id'],
                'row_code' => $param['row_code'],
                'reason_codes' => $param['reason_codes'],
            ];
        }, $paramDiagnostics);
    }

    private function perParamKeyMetrics(array $paramDiagnostics): array
    {
        return array_map(function (array $param): array {
            $metrics = $param['metrics'];

            return [
                'param_id' => $param['param_id'],
                'row_code' => $param['row_code'],
                'picks_count' => $metrics['picks_count'] ?? null,
                'days_covered' => $metrics['days_covered'] ?? null,
                'avg_ret_net_top' => $metrics['avg_ret_net_top'] ?? null,
                'median_ret_net_top' => $metrics['median_ret_net_top'] ?? null,
                'p25_ret_net_top' => $metrics['p25_ret_net_top'] ?? null,
                'month_win_rate_min' => $metrics['month_win_rate_min'] ?? null,
                'month_avg_ret_net_min' => $metrics['month_avg_ret_net_min'] ?? null,
            ];
        }, $paramDiagnostics);
    }

    private function bestNearestGateGap(array $paramDiagnostics): ?array
    {
        $values = array_values(array_filter(array_map(function (array $param) {
            return $param['nearest_gate_gap'];
        }, $paramDiagnostics)));
        if ($values === []) {
            return null;
        }
        usort($values, function (array $left, array $right): int {
            return abs($left['gap']) <=> abs($right['gap']);
        });

        return $values[0];
    }

    private function globalWorstGateGap(array $paramDiagnostics): ?array
    {
        $values = array_values(array_filter(array_map(function (array $param) {
            return $param['worst_gate_gap'];
        }, $paramDiagnostics)));
        if ($values === []) {
            return null;
        }
        usort($values, function (array $left, array $right): int {
            return $left['gap'] <=> $right['gap'];
        });

        return $values[0];
    }

    private function metricSummary(array $paramDiagnostics, string $metric): array
    {
        $values = [];
        foreach ($paramDiagnostics as $param) {
            $value = $this->floatOrNull($param['metrics'][$metric] ?? null);
            if ($value !== null) {
                $values[] = [
                    'param_id' => $param['param_id'],
                    'row_code' => $param['row_code'],
                    'value' => $value,
                ];
            }
        }
        usort($values, function (array $left, array $right): int {
            if ($left['value'] == $right['value']) {
                return $left['param_id'] <=> $right['param_id'];
            }

            return $left['value'] < $right['value'] ? -1 : 1;
        });

        $onlyValues = array_map(function (array $item): float {
            return (float) $item['value'];
        }, $values);

        return [
            'count' => count($values),
            'min' => $onlyValues === [] ? null : min($onlyValues),
            'max' => $onlyValues === [] ? null : max($onlyValues),
            'avg' => $onlyValues === [] ? null : array_sum($onlyValues) / count($onlyValues),
            'best' => $values === [] ? null : $values[count($values) - 1],
            'worst' => $values[0] ?? null,
        ];
    }

    private function clusterBy(array $trades, string $field, ?bool $profitOnly, int $limit): array
    {
        $clusters = [];
        foreach ($trades as $trade) {
            if (! ($trade['metrics_ready'] ?? false)) {
                continue;
            }
            $ret = $this->floatOrNull($trade['ret_net'] ?? null);
            if ($ret === null) {
                continue;
            }
            if ($profitOnly === true && $ret <= 0) {
                continue;
            }
            if ($profitOnly === false && $ret >= 0) {
                continue;
            }
            $key = (string) ($trade[$field] ?? 'UNKNOWN');
            if ($key === '') {
                $key = 'UNKNOWN';
            }
            if (! isset($clusters[$key])) {
                $clusters[$key] = [
                    'bucket' => $key,
                    'trade_count' => 0,
                    'loss_count' => 0,
                    'win_count' => 0,
                    'ret_sum' => 0.0,
                    'min_ret_net' => null,
                    'max_ret_net' => null,
                ];
            }
            $clusters[$key]['trade_count']++;
            $clusters[$key]['ret_sum'] += $ret;
            if ($ret > 0) {
                $clusters[$key]['win_count']++;
            } else {
                $clusters[$key]['loss_count']++;
            }
            $clusters[$key]['min_ret_net'] = $clusters[$key]['min_ret_net'] === null ? $ret : min($clusters[$key]['min_ret_net'], $ret);
            $clusters[$key]['max_ret_net'] = $clusters[$key]['max_ret_net'] === null ? $ret : max($clusters[$key]['max_ret_net'], $ret);
        }

        $rows = array_values(array_map(function (array $cluster): array {
            $cluster['avg_ret_net'] = $cluster['trade_count'] > 0 ? $cluster['ret_sum'] / $cluster['trade_count'] : null;
            $cluster['win_rate'] = $cluster['trade_count'] > 0 ? $cluster['win_count'] / $cluster['trade_count'] : null;
            unset($cluster['ret_sum']);

            return $cluster;
        }, $clusters));

        usort($rows, function (array $left, array $right) use ($profitOnly): int {
            if ($profitOnly === true) {
                if ($left['avg_ret_net'] == $right['avg_ret_net']) {
                    return strcmp($left['bucket'], $right['bucket']);
                }

                return $left['avg_ret_net'] < $right['avg_ret_net'] ? 1 : -1;
            }
            if ($left['loss_count'] === $right['loss_count']) {
                if ($left['avg_ret_net'] == $right['avg_ret_net']) {
                    return strcmp($left['bucket'], $right['bucket']);
                }

                return $left['avg_ret_net'] < $right['avg_ret_net'] ? -1 : 1;
            }

            return $left['loss_count'] < $right['loss_count'] ? 1 : -1;
        });

        return array_slice($rows, 0, $limit);
    }

    private function withMonth(array $trades): array
    {
        return array_map(function (array $trade): array {
            $date = (string) ($trade['trade_date'] ?? '');
            $trade['month'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? substr($date, 0, 7) : 'UNKNOWN';

            return $trade;
        }, $trades);
    }

    private function numericBucketSummary(array $trades, string $field, array $breaks): array
    {
        $bucketed = [];
        foreach ($trades as $trade) {
            $value = $this->floatOrNull($trade[$field] ?? null);
            if ($value === null) {
                $trade[$field.'_bucket'] = 'UNAVAILABLE';
            } else {
                $trade[$field.'_bucket'] = $this->bucketLabel($value, $breaks);
            }
            $bucketed[] = $trade;
        }

        return [
            'field' => $field,
            'breaks' => $breaks,
            'summary' => $this->clusterBy($bucketed, $field.'_bucket', null, 50),
        ];
    }

    private function numericBucketSummaryFromRuntime(array $trades, string $field, array $breaks): array
    {
        if (! $this->hasNumericRuntimeEvidence($trades, $field)) {
            return $this->unavailableBucket(
                $field.' is not exported in current backtest trade/evaluation payload.',
                [$field]
            );
        }

        return array_merge($this->numericBucketSummary($trades, $field, $breaks), [
            'status' => 'DERIVED_FROM_RUNTIME_EVIDENCE',
            'source_field' => $field,
            'derivation_status' => 'DERIVED',
            'next_catalog_decision_usage' => 'DIAGNOSTIC_ONLY_REQUIRES_SEPARATE_REVIEW',
        ]);
    }

    private function breakoutExtensionBucketSummary(array $trades): array
    {
        if (! $this->hasNumericRuntimeEvidence($trades, 'close_to_hh20_pct')) {
            return $this->unavailableBucket(
                'close_to_hh20_pct is not exported in current backtest trade/evaluation payload.',
                ['close_to_hh20_pct', 'bo_near_below_pct', 'bo_max_ext_pct']
            );
        }

        $summary = $this->numericBucketSummaryFromRuntime(
            $trades,
            'close_to_hh20_pct',
            [-0.05, -0.02, 0.0, 0.02, 0.05, 0.10]
        );
        $summary['bo_near_below_pct_hit_miss'] = $this->booleanBucketSummary($trades, 'bo_near_below_hit');
        $summary['bo_max_ext_pct_hit_miss'] = $this->booleanBucketSummary($trades, 'bo_max_ext_hit');

        return $summary;
    }

    private function categoricalBucketSummaryFromRuntime(array $trades, string $field): array
    {
        if (! $this->hasScalarRuntimeEvidence($trades, $field)) {
            return $this->unavailableBucket(
                $field.' is not exported in current backtest trade/evaluation payload.',
                [$field]
            );
        }

        return [
            'status' => 'DERIVED_FROM_RUNTIME_EVIDENCE',
            'field' => $field,
            'derivation_status' => 'DERIVED',
            'next_catalog_decision_usage' => 'DIAGNOSTIC_ONLY_REQUIRES_SEPARATE_REVIEW',
            'summary' => $this->clusterBy($trades, $field, null, 50),
        ];
    }

    private function eventRiskFlagSummary(array $trades): array
    {
        $fields = [
            'corporate_action_flag',
            'corporate_action_types',
            'trading_status_code',
            'is_suspended',
            'is_uma',
            'event_risk_flag',
            'event_risk_reasons',
        ];
        $flagFields = ['corporate_action_flag', 'is_suspended', 'is_uma', 'event_risk_flag'];
        $fieldSummaries = [];
        $hasAnyField = false;
        foreach ($fields as $field) {
            $fieldSummaries[$field] = $this->categoricalBucketSummaryFromRuntime($trades, $field);
            $hasAnyField = $hasAnyField || (($fieldSummaries[$field]['status'] ?? null) === 'DERIVED_FROM_RUNTIME_EVIDENCE');
        }

        if (! $hasAnyField) {
            return $this->unavailableBucket(
                'event-risk flags are not exported in current backtest trade/evaluation payload.',
                $fields
            );
        }

        $bucketed = [];
        foreach ($trades as $trade) {
            $any = null;
            foreach ($flagFields as $field) {
                $flag = $this->flagOrNull($trade[$field] ?? null);
                if ($flag === null) {
                    continue;
                }
                $any = $any === null ? ($flag === 1) : ($any || $flag === 1);
            }
            foreach (['corporate_action_types', 'event_risk_reasons'] as $field) {
                if ($this->stringOrNull($trade[$field] ?? null) !== null) {
                    $any = true;
                }
            }
            $trade['any_event_risk_flag'] = $any;
            $bucketed[] = $trade;
        }

        return [
            'status' => 'DERIVED_FROM_RUNTIME_EVIDENCE',
            'derivation_status' => 'DERIVED',
            'next_catalog_decision_usage' => 'DIAGNOSTIC_ONLY_REQUIRES_SEPARATE_REVIEW',
            'fields' => $fieldSummaries,
            'any_event_risk_flag_hit_miss' => $this->booleanBucketSummary($bucketed, 'any_event_risk_flag'),
        ];
    }

    private function booleanBucketSummary(array $trades, string $field): array
    {
        $bucketed = [];
        foreach ($trades as $trade) {
            $value = $trade[$field] ?? null;
            if ($value === true) {
                $trade[$field.'_bucket'] = 'HIT';
            } elseif ($value === false) {
                $trade[$field.'_bucket'] = 'MISS';
            } else {
                $trade[$field.'_bucket'] = self::FIELD_NOT_AVAILABLE;
            }
            $bucketed[] = $trade;
        }

        return $this->clusterBy($bucketed, $field.'_bucket', null, 50);
    }

    private function scoreComponentEffectivenessSummary(array $trades): array
    {
        $components = ['score_momentum', 'score_breakout', 'score_volume', 'score_risk'];
        $available = array_values(array_filter($components, function (string $component) use ($trades): bool {
            return $this->hasNumericRuntimeEvidence($trades, $component);
        }));

        if ($available === []) {
            return $this->unavailableBucket(
                'score_components is not exported in current backtest trade/evaluation payload.',
                ['score_components', 'score_momentum', 'score_breakout', 'score_volume', 'score_risk']
            );
        }

        $summary = [
            'status' => 'DERIVED_FROM_RUNTIME_EVIDENCE',
            'derivation_status' => 'DERIVED',
            'next_catalog_decision_usage' => 'DIAGNOSTIC_ONLY_REQUIRES_SEPARATE_REVIEW',
            'components' => [],
        ];
        foreach ($components as $component) {
            if (! in_array($component, $available, true)) {
                $summary['components'][$component] = $this->unavailableBucket(
                    $component.' is not exported in current backtest trade/evaluation payload.',
                    [$component]
                );
                continue;
            }
            $summary['components'][$component] = array_merge(
                $this->numericBucketSummary($trades, $component, [0.25, 0.50, 0.65, 0.80, 0.90, 1.00]),
                [
                    'status' => 'DERIVED_FROM_RUNTIME_EVIDENCE',
                    'directional_finding' => $this->directionalFinding($trades, $component),
                ]
            );
        }

        return $summary;
    }

    private function directionalFinding(array $trades, string $field): array
    {
        $rows = [];
        foreach ($trades as $trade) {
            if (! ($trade['metrics_ready'] ?? false)) {
                continue;
            }
            $value = $this->floatOrNull($trade[$field] ?? null);
            $ret = $this->floatOrNull($trade['ret_net'] ?? null);
            if ($value === null || $ret === null) {
                continue;
            }
            $rows[] = ['value' => $value, 'ret_net' => $ret];
        }
        if (count($rows) < 4) {
            return [
                'method' => 'TOP_QUARTILE_AVG_RET_MINUS_BOTTOM_QUARTILE_AVG_RET',
                'status' => 'INSUFFICIENT_RUNTIME_EVIDENCE_FOR_DIRECTIONAL_FINDING',
                'sample_count' => count($rows),
            ];
        }

        usort($rows, function (array $left, array $right): int {
            if ($left['value'] == $right['value']) {
                return $left['ret_net'] <=> $right['ret_net'];
            }

            return $left['value'] < $right['value'] ? -1 : 1;
        });
        $quartileCount = max(1, (int) ceil(count($rows) / 4));
        $bottom = array_slice($rows, 0, $quartileCount);
        $top = array_slice($rows, -$quartileCount);
        $bottomAvg = $this->average(array_column($bottom, 'ret_net'));
        $topAvg = $this->average(array_column($top, 'ret_net'));
        $delta = $topAvg - $bottomAvg;

        return [
            'method' => 'TOP_QUARTILE_AVG_RET_MINUS_BOTTOM_QUARTILE_AVG_RET',
            'sample_count' => count($rows),
            'bottom_quartile_avg_ret_net' => $bottomAvg,
            'top_quartile_avg_ret_net' => $topAvg,
            'delta_avg_ret_net' => $delta,
            'finding' => $delta > 0
                ? 'HIGHER_COMPONENT_ASSOCIATED_WITH_HIGHER_AVG_RET_NET'
                : ($delta < 0 ? 'HIGHER_COMPONENT_ASSOCIATED_WITH_LOWER_AVG_RET_NET' : 'NO_DIRECTIONAL_DIFFERENCE_OBSERVED'),
        ];
    }

    private function bucketLabel(float $value, array $breaks): string
    {
        $previous = null;
        foreach ($breaks as $break) {
            $break = (float) $break;
            if ($value <= $break) {
                return ($previous === null ? '<=' : $previous.'..').$break;
            }
            $previous = $break;
        }

        return '>'.$previous;
    }

    private function groupingQuantileSummary(array $rows, array $paramDiagnostics): array
    {
        $byParam = [];
        foreach ($paramDiagnostics as $param) {
            $byParam[$param['param_id']] = $param;
        }
        $summary = [];
        foreach ($rows as $row) {
            $paramId = (int) ($row['param_id'] ?? 0);
            $metrics = $byParam[$paramId]['metrics'] ?? [];
            $summary[] = [
                'param_id' => $paramId,
                'row_code' => (string) ($row['row_code'] ?? ''),
                'top_min_score_q' => $this->floatOrNull($row['top_min_score_q'] ?? null),
                'secondary_min_score_q' => $this->floatOrNull($row['secondary_min_score_q'] ?? null),
                'picks_count' => $metrics['picks_count'] ?? null,
                'avg_ret_net_top' => $metrics['avg_ret_net_top'] ?? null,
                'p25_ret_net_top' => $metrics['p25_ret_net_top'] ?? null,
                'month_win_rate_min' => $metrics['month_win_rate_min'] ?? null,
            ];
        }

        return $summary;
    }

    private function paramAxisEffectiveness(array $rows, array $paramDiagnostics): array
    {
        $metricsByParam = [];
        foreach ($paramDiagnostics as $param) {
            $metricsByParam[$param['param_id']] = $param['metrics'];
        }
        $axes = [
            'min_dv20_idr', 'dv20_strong_idr', 'min_vol_ratio', 'strong_vol_ratio',
            'min_atr14_pct', 'max_atr14_pct', 'atr_ideal_low', 'atr_ideal_high',
            'roc_lo', 'roc_hi', 'mom_roc20_soft_min', 'bo_near_below_pct', 'bo_max_ext_pct',
            'w_momentum', 'w_volume', 'w_breakout', 'w_risk',
            'top_min_score_q', 'secondary_min_score_q',
        ];
        $summary = [];
        foreach ($axes as $axis) {
            $values = [];
            foreach ($rows as $row) {
                $paramId = (int) ($row['param_id'] ?? 0);
                $axisValue = $this->floatOrNull($row[$axis] ?? null);
                $metricValue = $this->floatOrNull($metricsByParam[$paramId]['avg_ret_net_top'] ?? null);
                $downsideValue = $this->floatOrNull($metricsByParam[$paramId]['p25_ret_net_top'] ?? null);
                if ($axisValue !== null && $metricValue !== null) {
                    $values[] = [
                        'param_id' => $paramId,
                        'axis_value' => $axisValue,
                        'avg_ret_net_top' => $metricValue,
                        'p25_ret_net_top' => $downsideValue,
                    ];
                }
            }
            $unique = array_values(array_unique(array_map(function (array $row): string {
                return (string) $row['axis_value'];
            }, $values)));
            $summary[$axis] = [
                'distinct_values' => count($unique),
                'runtime_consumed_by_contract' => true,
                'observable_metric_spread' => $this->metricSpread($values, 'avg_ret_net_top'),
                'observable_downside_spread' => $this->metricSpread($values, 'p25_ret_net_top'),
                'finding' => count($unique) <= 1
                    ? 'STATIC_AXIS_IN_THIS_CATALOG'
                    : 'VARIED_AXIS_WITH_OBSERVABLE_OUTCOME_SPREAD',
            ];
        }

        return $summary;
    }

    private function metricSpread(array $values, string $metric): ?float
    {
        $nums = [];
        foreach ($values as $value) {
            $num = $this->floatOrNull($value[$metric] ?? null);
            if ($num !== null) {
                $nums[] = $num;
            }
        }
        if ($nums === []) {
            return null;
        }

        return max($nums) - min($nums);
    }

    private function runtimeConsumedParameterSummary(array $rows): array
    {
        $map = [
            'risk.min_atr14_pct' => ['column' => 'min_atr14_pct', 'consumer' => 'WatchlistCandidateUniverseService + WatchlistScoringService'],
            'risk.max_atr14_pct' => ['column' => 'max_atr14_pct', 'consumer' => 'WatchlistCandidateUniverseService + WatchlistScoringService'],
            'risk.atr_ideal_low' => ['column' => 'atr_ideal_low', 'consumer' => 'WatchlistScoringService'],
            'risk.atr_ideal_high' => ['column' => 'atr_ideal_high', 'consumer' => 'WatchlistScoringService'],
            'liquidity.min_dv20_idr' => ['column' => 'min_dv20_idr', 'consumer' => 'WatchlistCandidateUniverseService + WatchlistScoringService'],
            'liquidity.dv20_strong_idr' => ['column' => 'dv20_strong_idr', 'consumer' => 'WatchlistCandidateUniverseService + WatchlistScoringService'],
            'volume.min_vol_ratio' => ['column' => 'min_vol_ratio', 'consumer' => 'WatchlistCandidateUniverseService + WatchlistScoringService'],
            'volume.strong_vol_ratio' => ['column' => 'strong_vol_ratio', 'consumer' => 'WatchlistScoringService'],
            'setup.roc_lo' => ['column' => 'roc_lo', 'consumer' => 'WatchlistScoringService'],
            'setup.roc_hi' => ['column' => 'roc_hi', 'consumer' => 'WatchlistScoringService'],
            'setup.mom_roc20_soft_min' => ['column' => 'mom_roc20_soft_min', 'consumer' => 'WatchlistScoringService'],
            'setup.bo_near_below_pct' => ['column' => 'bo_near_below_pct', 'consumer' => 'WatchlistScoringService'],
            'setup.bo_max_ext_pct' => ['column' => 'bo_max_ext_pct', 'consumer' => 'WatchlistScoringService'],
            'scoring.weights.value.momentum' => ['column' => 'w_momentum', 'consumer' => 'WatchlistScoringService'],
            'scoring.weights.value.breakout' => ['column' => 'w_breakout', 'consumer' => 'WatchlistScoringService'],
            'scoring.weights.value.volume' => ['column' => 'w_volume', 'consumer' => 'WatchlistScoringService'],
            'scoring.weights.value.risk' => ['column' => 'w_risk', 'consumer' => 'WatchlistScoringService'],
            'grouping.top_min_score_q' => ['column' => 'top_min_score_q', 'consumer' => 'WatchlistPlanGroupingService'],
            'grouping.secondary_min_score_q' => ['column' => 'secondary_min_score_q', 'consumer' => 'WatchlistPlanGroupingService'],
        ];

        $parameters = [];
        foreach ($map as $parameter => $definition) {
            $column = $definition['column'];
            $values = [];
            $missing = 0;
            foreach ($rows as $row) {
                if (! array_key_exists($column, $row) || $row[$column] === null || $row[$column] === '') {
                    $missing++;
                    continue;
                }
                $values[(string) $row[$column]] = true;
            }
            $parameters[$parameter] = [
                'catalog_column' => $column,
                'runtime_consumer' => $definition['consumer'],
                'catalog_column_present_for_all_rows' => $missing === 0,
                'catalog_distinct_value_count' => count($values),
                'status' => $missing === 0
                    ? 'RUNTIME_CONSUMED_BY_PARAMSET_FACTORY_AND_WS_SERVICES'
                    : 'FIELD_NOT_AVAILABLE_IN_RUNTIME_EVIDENCE',
                'silent_default_allowed' => false,
            ];
        }

        return [
            'status' => 'RUNTIME_CONSUMPTION_CONTRACT_RECORDED',
            'source' => [
                'WatchlistBacktestParamGridParamsetFactory',
                'WatchlistCandidateUniverseService',
                'WatchlistScoringService',
                'WatchlistPlanGroupingService',
            ],
            'parameters' => $parameters,
        ];
    }

    private function deadParameterSummary(array $runtimeConsumedParameterSummary, array $runtimeFieldAvailability): array
    {
        $dead = [];
        foreach (($runtimeConsumedParameterSummary['parameters'] ?? []) as $parameter => $summary) {
            if (($summary['catalog_column_present_for_all_rows'] ?? false) !== true) {
                $dead[] = $parameter;
            }
        }
        $fieldExportGaps = [];
        foreach ($runtimeFieldAvailability as $field => $summary) {
            if (($summary['status'] ?? null) === self::FIELD_NOT_AVAILABLE) {
                $fieldExportGaps[] = $field;
            }
        }

        return [
            'status' => $dead === [] ? 'NO_DEAD_PARAMETER_OR_SILENT_DEFAULT_DETECTED_FOR_ALLOWED_C01_AXES' : 'DEAD_PARAMETER_OR_SILENT_DEFAULT_REVIEW_REQUIRED',
            'dead_parameter_candidates' => $dead,
            'confirmed_runtime_consumed_axes' => array_keys($runtimeConsumedParameterSummary['parameters'] ?? []),
            'diagnostic_payload_gap' => $fieldExportGaps,
            'not_derived_marker' => self::NOT_DERIVED,
            'next_catalog_decision_usage_for_gaps' => self::NOT_USED_FOR_NEXT_CATALOG_DECISION,
            'finding' => $fieldExportGaps === []
                ? 'Diagnostic feature fields are available in runtime evidence; use them only as diagnostic evidence, not as promotion proof.'
                : 'Allowed C01 parameter axes are mapped into the runtime paramset, but feature-level false momentum/volume/liquidity/breakout/sector conclusions remain unavailable until those fields are exported into the backtest trade/evaluation payload.',
        ];
    }

    private function dataQualitySummary(array $diagnostics, array $trades): array
    {
        $reasonDistribution = [];
        foreach ($diagnostics as $diagnostic) {
            $code = (string) ($diagnostic['reason_code'] ?? 'UNKNOWN');
            $reasonDistribution[$code] = ($reasonDistribution[$code] ?? 0) + 1;
        }
        ksort($reasonDistribution, SORT_STRING);

        $skipDistribution = [];
        foreach ($trades as $trade) {
            if (($trade['metrics_ready'] ?? false) === true) {
                continue;
            }
            $code = (string) ($trade['reason_code'] ?? 'UNKNOWN');
            $skipDistribution[$code] = ($skipDistribution[$code] ?? 0) + 1;
        }
        ksort($skipDistribution, SORT_STRING);

        return [
            'runtime_diagnostic_count' => count($diagnostics),
            'runtime_reason_distribution' => $reasonDistribution,
            'skipped_trade_distribution' => $skipDistribution,
            'missing_publication_or_ohlc_supported' => $reasonDistribution !== [] || $skipDistribution !== [],
        ];
    }

    private function diagnosticReasonSummary(array $paramDiagnostics, array $runtimeDiagnostics): array
    {
        $failureDistribution = [];
        foreach ($paramDiagnostics as $param) {
            foreach ($param['reason_codes'] as $code) {
                $failureDistribution[$code] = ($failureDistribution[$code] ?? 0) + 1;
            }
        }
        ksort($failureDistribution, SORT_STRING);

        return [
            'per_param_failure_distribution' => $failureDistribution,
            'runtime_diagnostic_count' => count($runtimeDiagnostics),
            'all_params_valid' => count(array_filter($paramDiagnostics, function (array $param): bool {
                return (bool) $param['calibration_valid'];
            })) === count($paramDiagnostics),
            'best_is_binding' => null,
            'best_of_failed_forbidden' => true,
        ];
    }

    private function nextFocusRecommendation(array $paramDiagnostics, array $trades, array $runtimeFieldAvailability, ?string $catalogVersion = null): array
    {
        $failureDistribution = [];
        foreach ($paramDiagnostics as $param) {
            foreach ($param['reason_codes'] as $code) {
                $failureDistribution[$code] = ($failureDistribution[$code] ?? 0) + 1;
            }
        }
        $validCount = count(array_filter($paramDiagnostics, function (array $param): bool {
            return (bool) $param['calibration_valid'];
        }));

        $availableTradeCount = count(array_filter($trades, function (array $trade): bool {
            return (bool) ($trade['metrics_ready'] ?? false);
        }));
        $missingRuntimeFields = [];
        foreach ($runtimeFieldAvailability as $field => $summary) {
            if (($summary['status'] ?? null) === self::FIELD_NOT_AVAILABLE) {
                $missingRuntimeFields[] = $field;
            }
        }

        $isC01 = $catalogVersion === null || $catalogVersion === '' || $catalogVersion === 'C01';

        return [
            'decision' => $validCount > 0 ? 'VALID_IS_BINDING_AVAILABLE' : 'NEXT_CATALOG_NOT_DESIGNED',
            'recommended_status' => $validCount > 0
                ? 'ELIGIBLE_FOR_SEPARATE_OOS_PROOF_SESSION'
                : 'NOT_ELIGIBLE_FOR_OOS_PROOF_NO_VALID_IS_PARAMETER',
            'focus' => $isC01
                ? ($missingRuntimeFields === [] ? 'DIAGNOSTIC_REVIEW_BEFORE_C02' : 'DIAGNOSTIC_PAYLOAD_ENRICHMENT_BEFORE_C02')
                : ($missingRuntimeFields === [] ? 'STRATEGY_QUALITY_DIAGNOSTIC_BEFORE_NEXT_CATALOG' : 'RUNTIME_PAYLOAD_ENRICHMENT_BEFORE_NEXT_CATALOG'),
            'rationale' => $availableTradeCount > 0
                ? ($isC01
                    ? ($missingRuntimeFields === []
                    ? 'C01 still fails canonical robust return/downside/stability gates. Runtime feature fields are present for diagnostic review, but no next catalog is created in this session.'
                    : 'C01 still fails canonical robust return/downside/stability gates. Runtime trade-level output supports ticker/month/date/ATR/score drilldown, but feature-level setup/ROC/volume/liquidity/breakout/sector root-cause fields are not all exported yet, so C02 should not be designed only from current evidence.')
                    : ($missingRuntimeFields === []
                        ? 'The requested catalog still fails canonical robust return/downside/stability gates. Runtime feature fields are present for diagnostic review, but no next catalog is created by this diagnostic command.'
                        : 'The requested catalog still fails canonical robust return/downside/stability gates. Runtime trade-level output is missing fields required for a safe next catalog decision.'))
                : ($isC01 ? 'No evaluated trade payload was available; do not design C02.' : 'No evaluated trade payload was available; do not design a next catalog.'),
            'failure_distribution' => $failureDistribution,
            'missing_runtime_evidence_fields' => $missingRuntimeFields,
            'missing_field_decision_usage' => self::NOT_USED_FOR_NEXT_CATALOG_DECISION,
        ];
    }

    private function runtimeFieldAvailability(array $trades): array
    {
        $fields = [
            'close_to_hh20_pct' => 'numeric',
            'roc20' => 'numeric',
            'vol_ratio' => 'numeric',
            'dv20_idr' => 'numeric',
            'sector_code' => 'scalar',
            'score_components' => 'score_components',
            'roc5' => 'numeric',
            'roc10' => 'numeric',
            'close_to_ll20_pct' => 'numeric',
            'range_20_pct' => 'numeric',
            'range_position_20_pct' => 'numeric',
            'sector_roc20' => 'numeric',
            'rs_20_vs_sector' => 'numeric',
            'sector_rs_20_vs_ihsg' => 'numeric',
            'corporate_action_flag' => 'scalar',
            'corporate_action_types' => 'scalar',
            'trading_status_code' => 'scalar',
            'is_suspended' => 'scalar',
            'is_uma' => 'scalar',
            'event_risk_flag' => 'scalar',
            'event_risk_reasons' => 'scalar',
        ];
        $summary = [];
        foreach ($fields as $field => $type) {
            if ($type === 'numeric') {
                $available = $this->hasNumericRuntimeEvidence($trades, $field);
            } elseif ($type === 'scalar') {
                $available = $this->hasScalarRuntimeEvidence($trades, $field);
            } else {
                $available = false;
                foreach (['score_momentum', 'score_breakout', 'score_volume', 'score_risk'] as $component) {
                    $available = $available || $this->hasNumericRuntimeEvidence($trades, $component);
                }
            }
            $summary[$field] = [
                'status' => $available ? 'AVAILABLE_IN_RUNTIME_EVIDENCE' : self::FIELD_NOT_AVAILABLE,
                'derivation_status' => $available ? 'DERIVABLE' : self::NOT_DERIVED,
                'next_catalog_decision_usage' => $available
                    ? 'DIAGNOSTIC_ONLY_REQUIRES_SEPARATE_REVIEW'
                    : self::NOT_USED_FOR_NEXT_CATALOG_DECISION,
            ];
        }

        return $summary;
    }

    private function unavailableBucket(string $reason, array $missingFields = []): array
    {
        return [
            'status' => self::FIELD_NOT_AVAILABLE,
            'derivation_status' => self::NOT_DERIVED,
            'next_catalog_decision_usage' => self::NOT_USED_FOR_NEXT_CATALOG_DECISION,
            'reason' => $reason,
            'missing_fields' => $missingFields,
            'required_for_next_diagnostic' => true,
        ];
    }

    private function hasNumericRuntimeEvidence(array $trades, string $field): bool
    {
        foreach ($trades as $trade) {
            if (! ($trade['metrics_ready'] ?? false)) {
                continue;
            }
            if ($this->floatOrNull($trade[$field] ?? null) !== null) {
                return true;
            }
        }

        return false;
    }

    private function hasScalarRuntimeEvidence(array $trades, string $field): bool
    {
        foreach ($trades as $trade) {
            if (! ($trade['metrics_ready'] ?? false)) {
                continue;
            }
            $value = $trade[$field] ?? null;
            if (is_scalar($value) && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function firstAvailable(array $source, array $paths)
    {
        foreach ($paths as $path) {
            $value = $this->valueAtPath($source, $path);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function valueAtPath(array $source, array $path)
    {
        $cursor = $source;
        foreach ($path as $segment) {
            if (! is_array($cursor) || ! array_key_exists($segment, $cursor)) {
                return null;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    private function average(array $values): ?float
    {
        $numeric = array_values(array_filter($values, function ($value): bool {
            return is_numeric($value);
        }));
        if ($numeric === []) {
            return null;
        }

        return array_sum($numeric) / count($numeric);
    }

    private function normalizeDateList($dates): array
    {
        if (! is_array($dates)) {
            return [];
        }
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

    private function rowFilter(array $options): ?array
    {
        $paramId = null;
        $rawParamId = $options['param_id'] ?? null;
        if (is_scalar($rawParamId) && trim((string) $rawParamId) !== '') {
            $paramId = ctype_digit(trim((string) $rawParamId)) ? (int) trim((string) $rawParamId) : -1;
        }

        $rowCode = null;
        $rawRowCode = $options['row_code'] ?? null;
        if (is_scalar($rawRowCode) && trim((string) $rawRowCode) !== '') {
            $rowCode = trim((string) $rawRowCode);
        }

        if ($paramId === null && $rowCode === null) {
            return null;
        }

        return [
            'param_id' => $paramId,
            'row_code' => $rowCode,
        ];
    }

    private function blocked(string $reasonCode, string $message, array $extra = []): array
    {
        return array_merge([
            'is_ready' => false,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'oos_executed' => false,
            'production_ready' => false,
        ], $extra);
    }

    private function writeArtifact(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath) && ! $overwrite) {
            return [
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID',
                'path' => $outputPath,
            ];
        }
        $directory = dirname($outputPath);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return [
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_DIRECTORY_UNAVAILABLE',
                'path' => $outputPath,
            ];
        }
        $encoded = json_encode($this->normalizeForHash($artifact), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
        $tmp = $outputPath.'.tmp';
        if (file_put_contents($tmp, $encoded, LOCK_EX) === false) {
            return [
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'path' => $outputPath,
            ];
        }
        if (! rename($tmp, $outputPath)) {
            @unlink($tmp);

            return [
                'is_ready' => false,
                'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_RENAME_FAILED',
                'path' => $outputPath,
            ];
        }

        return [
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITTEN',
            'path' => $outputPath,
            'bytes' => strlen($encoded),
            'sha1' => sha1($encoded),
        ];
    }

    private function validDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $errors = \DateTimeImmutable::getLastErrors();

        return $date !== false
            && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
            && $date->format('Y-m-d') === $value;
    }

    private function floatOrNull($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function flagOrNull($value): ?int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_numeric($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }

        return null;
    }

    private function corporateActionFlagOrNull($value, ?string $corporateActionTypes): ?int
    {
        $explicit = $this->flagOrNull($value);
        if ($explicit !== null) {
            return $explicit;
        }

        return $corporateActionTypes === null ? null : 1;
    }

    private function stringOrNull($value): ?string
    {
        if ($value === null || ! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function uniqueReasonCodes(array $reasonCodes): array
    {
        $unique = [];
        foreach ($reasonCodes as $reasonCode) {
            if (! is_scalar($reasonCode)) {
                continue;
            }
            $value = trim((string) $reasonCode);
            if ($value !== '') {
                $unique[$value] = $value;
            }
        }

        return array_values($unique);
    }

    private function artifactForHash(array $artifact): array
    {
        unset(
            $artifact['artifact_hash'],
            $artifact['canonical_artifact_hash'],
            $artifact['meta']['artifact_hash'],
            $artifact['meta']['canonical_artifact_hash'],
            $artifact['meta']['generated_at']
        );

        return $artifact;
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
        if ($this->isList($value)) {
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

    private function isList(array $value): bool
    {
        $index = 0;
        foreach (array_keys($value) as $key) {
            if ($key !== $index) {
                return false;
            }
            $index++;
        }

        return true;
    }
}
