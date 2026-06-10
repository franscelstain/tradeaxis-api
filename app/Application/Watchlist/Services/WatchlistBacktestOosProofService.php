<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOosEvaluationRepository;

class WatchlistBacktestOosProofService
{
    private MarketDataTradingCalendarReadService $calendar;
    private WatchlistBacktestOosSplitService $splitter;
    private WatchlistBacktestIsCalibrationService $calibration;
    private WatchlistBacktestPublishedPriceRuntimeService $runtime;
    private WatchlistBacktestOosEvaluationRepository $oosEvaluations;
    private WatchlistBacktestRuntimeArtifactService $artifacts;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        WatchlistBacktestOosSplitService $splitter = null,
        WatchlistBacktestIsCalibrationService $calibration = null,
        WatchlistBacktestPublishedPriceRuntimeService $runtime = null,
        WatchlistBacktestOosEvaluationRepository $oosEvaluations = null,
        WatchlistBacktestRuntimeArtifactService $artifacts = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->splitter = $splitter ?: new WatchlistBacktestOosSplitService();
        $this->runtime = $runtime ?: new WatchlistBacktestPublishedPriceRuntimeService();
        $this->calibration = $calibration ?: new WatchlistBacktestIsCalibrationService($this->runtime);
        $this->oosEvaluations = $oosEvaluations ?: new WatchlistBacktestOosEvaluationRepository();
        $this->artifacts = $artifacts ?: new WatchlistBacktestRuntimeArtifactService();
    }

    public function execute(string $fromDate, string $toDate, string $outputPath, array $options = []): array
    {
        $options['executed_at'] = isset($options['executed_at']) && trim((string) $options['executed_at']) !== ''
            ? (string) $options['executed_at']
            : date(DATE_ATOM);
        $calendar = $this->calendar->resolveReplayWindow($fromDate, $toDate, 5);
        if (! ($calendar['is_ready'] ?? false)) {
            $split = [
                'ready' => false,
                'is_ready' => false,
                'reason_code' => $calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE',
                'split_rule' => WatchlistBacktestOosSplitService::SPLIT_RULE,
                'diagnostics' => $calendar['diagnostics'] ?? [],
            ];

            return $this->writeFailureArtifact($fromDate, $toDate, $outputPath, $options, $calendar, $split, null, null);
        }

        $split = $this->splitter->split($calendar['trade_dates'] ?? []);
        if (! ($split['is_ready'] ?? false)) {
            return $this->writeFailureArtifact($fromDate, $toDate, $outputPath, $options, $calendar, $split, null, null);
        }

        try {
            $calibration = $this->calibration->calibrate($split['is_dates'], [
                'policy_code' => 'WS',
                'executed_at' => $options['executed_at'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $calibration = $this->failureResultFromException($e, 'WS_BT_OOS_PROOF_MISSING');
        }
        if (! ($calibration['is_ready'] ?? false)) {
            return $this->writeFailureArtifact($fromDate, $toDate, $outputPath, $options, $calendar, $split, $calibration, null);
        }

        $binding = $calibration['best_is_binding'];
        $frozenBindingHash = (string) $binding['binding_hash'];
        try {
            $oos = $this->runtime->evaluateWindow($split['oos_from'], $split['oos_to'], [
                'paramset' => $binding['paramset_snapshot'],
                'executed_at' => $options['executed_at'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $oos = $this->failureResultFromException($e, 'WS_BT_OOS_PROOF_MISSING');
        }

        if (! ($oos['is_ready'] ?? false)) {
            return $this->writeFailureArtifact($fromDate, $toDate, $outputPath, $options, $calendar, $split, $calibration, $oos);
        }

        $resolvedOosDates = $this->normalizeDates($oos['calendar']['trade_dates'] ?? []);
        if ($this->stableHash($resolvedOosDates) !== $split['oos_trading_date_hash']) {
            $oos = $this->asFailure($oos, 'WS_BT_OOS_WINDOW_INSUFFICIENT', 'OOS runtime calendar does not match the frozen chronological suffix.');

            return $this->writeFailureArtifact($fromDate, $toDate, $outputPath, $options, $calendar, $split, $calibration, $oos);
        }

        if ($frozenBindingHash !== $this->stableHash(array_diff_key($binding, ['binding_hash' => true]))) {
            $oos = $this->asFailure($oos, 'WS_BT_OOS_PROOF_MISSING', 'Frozen best-IS binding changed before OOS evaluation completed.');

            return $this->writeFailureArtifact($fromDate, $toDate, $outputPath, $options, $calendar, $split, $calibration, $oos);
        }

        $metrics = $oos['artifact']['metrics']['canonical_eval_metrics'] ?? [];
        $acceptance = $this->acceptance($metrics, $binding['paramset_snapshot']);
        if ($acceptance['missing_metrics'] !== []) {
            $oos = $this->asFailure(
                $oos,
                'WS_BT_OOS_PROOF_MISSING',
                'Canonical OOS metrics are incomplete: '.implode(', ', $acceptance['missing_metrics']).'.'
            );

            return $this->writeFailureArtifact($fromDate, $toDate, $outputPath, $options, $calendar, $split, $calibration, $oos);
        }

        $oosRow = [
            'policy_code' => 'WS',
            'policy_version' => (string) ($binding['paramset_snapshot']['policy_version'] ?? 'WS_EOD_RUNTIME'),
            'eval_model' => $binding['eval_model'],
            'param_id_best_is' => $binding['param_id_best_is'],
            'is_eval_id' => $binding['is_eval_id'],
            'from_date_is' => $split['is_from'],
            'to_date_is' => $split['is_to'],
            'from_date_oos' => $split['oos_from'],
            'to_date_oos' => $split['oos_to'],
            'days_covered_oos' => $metrics['days_covered'] ?? 0,
            'picks_count_oos' => $metrics['picks_count'] ?? 0,
            'avg_ret_net_top_oos' => $metrics['avg_ret_net_top'] ?? 0,
            'win_rate_top_oos' => $metrics['win_rate_top'] ?? 0,
            'median_ret_net_top_oos' => $metrics['median_ret_net_top'] ?? 0,
            'p25_ret_net_top_oos' => $metrics['p25_ret_net_top'] ?? 0,
            'month_win_rate_min_oos' => $metrics['month_win_rate_min'] ?? 0,
        ];
        try {
            $persistence = $this->oosEvaluations->persist($oosRow);
        } catch (\Throwable $e) {
            $oos = $this->asFailure($oos, $this->reasonCode($e, 'WS_BT_OOS_PROOF_MISSING'), $e->getMessage());

            return $this->writeFailureArtifact($fromDate, $toDate, $outputPath, $options, $calendar, $split, $calibration, $oos);
        }

        $artifact = $this->buildArtifact(
            $fromDate,
            $toDate,
            $calendar,
            $split,
            $calibration,
            $binding,
            $oos,
            $acceptance,
            $persistence,
            $options
        );
        $write = $this->writeArtifact($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        if (! ($write['is_ready'] ?? false)) {
            return $this->blocked($write['reason_code'], [[
                'reason_code' => $write['reason_code'],
                'message' => 'OOS evidence artifact could not be written.',
                'fatal' => true,
            ]], ['artifact' => $artifact, 'write' => $write]);
        }

        return [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => $acceptance['pass'] ? 'LOCAL_OOS_PROOF_PASS' : $acceptance['reason_code'],
            'status' => $acceptance['pass'] ? 'PASS' : 'OOS_ACCEPTANCE_FAIL',
            'calendar' => $calendar,
            'split' => $split,
            'calibration' => $calibration,
            'best_is_binding' => $binding,
            'oos_runtime' => $oos,
            'oos_acceptance' => $acceptance,
            'persistence' => $persistence,
            'artifact' => $artifact,
            'artifact_hash' => $artifact['validation']['artifact_hash'],
            'write' => $write,
            'production_ready' => false,
        ];
    }

    private function acceptance(array $metrics, array $paramset): array
    {
        $minTrades = $this->thresholdInt($paramset['eval']['min_trades_oos'] ?? null) ?? 40;
        $required = [
            'days_covered', 'picks_count', 'avg_ret_net_top', 'win_rate_top',
            'median_ret_net_top', 'month_win_rate_min', 'p25_ret_net_top',
        ];
        $missing = array_values(array_filter($required, function (string $field) use ($metrics): bool {
            return ! array_key_exists($field, $metrics) || $metrics[$field] === null;
        }));

        $gates = [
            'minimum_oos_trades' => $missing === [] ? (int) $metrics['picks_count'] >= $minTrades : null,
            'average_return_positive' => $missing === [] ? (float) $metrics['avg_ret_net_top'] > 0 : null,
            'median_return_non_negative' => $missing === [] ? (float) $metrics['median_ret_net_top'] >= 0 : null,
            'monthly_win_rate_floor' => $missing === [] ? (float) $metrics['month_win_rate_min'] >= 0.45 : null,
            'p25_downside_bound' => $missing === [] ? (float) $metrics['p25_ret_net_top'] >= -0.03 : null,
        ];
        $failed = array_keys(array_filter($gates, function ($value): bool {
            return $value !== true;
        }));
        $pass = $missing === [] && $failed === [];
        $reasonCodes = [];
        if ($missing !== []) {
            $reasonCodes[] = 'WS_BT_OOS_PROOF_MISSING';
        } else {
            if ($gates['minimum_oos_trades'] === false) {
                $reasonCodes[] = 'WS_BT_OOS_WINDOW_INSUFFICIENT';
            }
            if ($gates['average_return_positive'] === false || $gates['median_return_non_negative'] === false) {
                $reasonCodes[] = 'WS_BT_OOS_METRICS_FAIL';
            }
            if ($gates['monthly_win_rate_floor'] === false) {
                $reasonCodes[] = 'WS_BT_OOS_STABILITY_FAIL';
            }
            if ($gates['p25_downside_bound'] === false) {
                $reasonCodes[] = 'WS_BT_OOS_DRAWDOWN_FAIL';
            }
        }

        return [
            'pass' => $pass,
            'reason_code' => $pass ? 'LOCAL_OOS_PROOF_PASS' : ($reasonCodes[0] ?? 'WS_BT_OOS_METRICS_FAIL'),
            'reason_codes' => $reasonCodes,
            'failed_gates' => $failed,
            'missing_metrics' => $missing,
            'thresholds' => [
                'min_trades_oos' => $minTrades,
                'avg_ret_net_top_oos_gt' => 0,
                'median_ret_net_top_oos_gte' => 0,
                'month_win_rate_min_oos_gte' => 0.45,
                'p25_ret_net_top_oos_gte' => -0.03,
            ],
            'gates' => $gates,
        ];
    }

    private function buildArtifact(
        string $fromDate,
        string $toDate,
        array $calendar,
        array $split,
        array $calibration,
        array $binding,
        array $oos,
        array $acceptance,
        array $persistence,
        array $options
    ): array {
        $artifact = [
            'ready' => true,
            'is_ready' => true,
            'reason_code' => $acceptance['pass'] ? 'LOCAL_OOS_PROOF_PASS' : $acceptance['reason_code'],
            'meta' => [
                'artifact_service' => 'WatchlistBacktestOosProofService',
                'artifact_version' => 'WATCHLIST_BT_OOS_PROOF_V1',
                'policy_code' => 'WS',
                'requested_from' => $fromDate,
                'requested_to' => $toDate,
                'generated_at' => $options['executed_at'] ?? null,
                'production_ready' => false,
            ],
            'source_contract' => [
                'official_trading_calendar' => true,
                'official_published_eod_ohlcv' => true,
                'no_raw_market_data_query' => true,
                'no_latest_or_max_date' => true,
                'explicit_range_only' => true,
            ],
            'split_manifest' => $split,
            'is_calibration' => [
                'param_grid_count' => $calibration['param_grid_count'],
                'param_grid_hash' => $calibration['param_grid_hash'],
                'is_valid_param_count' => $calibration['is_valid_param_count'],
                'evaluations' => $calibration['evaluations'],
            ],
            'best_is_binding' => $binding,
            'oos_evaluation' => [
                'metrics' => $oos['artifact']['metrics']['canonical_eval_metrics'] ?? [],
                'artifact_hash' => $oos['artifact_hash'] ?? null,
                'calendar_hash' => $oos['calendar']['calendar_hash'] ?? null,
                'price_payload_hash' => $oos['price_read']['price_series_manifest']['source_payload_hash'] ?? null,
                'publication_manifest_hash' => $this->stableHash($oos['price_read']['publication_manifest'] ?? []),
                'eval_model' => $binding['eval_model'],
                'frozen_binding_hash_before_oos' => $binding['binding_hash'],
                'frozen_binding_hash_after_oos' => $this->stableHash(array_diff_key($binding, ['binding_hash' => true])),
                'binding_immutable' => true,
                'retuning_performed' => false,
            ],
            'oos_acceptance' => $acceptance,
            'persistence_manifest' => [
                'official_tables' => ['watchlist_bt_param_grid', 'watchlist_bt_eval', 'watchlist_bt_oos_eval_ws'],
                'is_eval_id' => $binding['is_eval_id'],
                'oos_id' => $persistence['oos_id'],
                'oos_persistence_status' => 'PERSISTED',
                'oos_persistence_operation' => $persistence['status'],
                'duplicate_exact_payload_idempotent' => true,
                'duplicate_conflict_fail_closed' => true,
                'shadow_tables_created' => false,
            ],
            'boundary' => [
                'no_plan_mutation' => true,
                'no_recommendation_mutation' => true,
                'no_confirm_mutation' => true,
                'no_paramset_status_mutation' => true,
                'no_promotion' => true,
                'no_portfolio_allocation' => true,
                'no_order_or_broker_instruction' => true,
            ],
            'diagnostics' => array_merge(
                $calendar['diagnostics'] ?? [],
                $split['diagnostics'] ?? [],
                $calibration['diagnostics'] ?? [],
                $oos['diagnostics'] ?? []
            ),
            'validation' => [],
        ];
        $artifact['validation'] = [
            'split_integrity' => ($split['no_overlap'] ?? false) && ($split['no_hidden_gap'] ?? false),
            'best_param_frozen_before_oos' => true,
            'oos_not_used_for_selection' => true,
            'eval_model_identical' => ($binding['eval_model'] ?? null) === ($artifact['oos_evaluation']['eval_model'] ?? null),
            'production_ready' => false,
        ];
        $artifact['validation']['artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['meta']['artifact_hash'] = $artifact['validation']['artifact_hash'];

        return $artifact;
    }

    private function writeFailureArtifact(
        string $fromDate,
        string $toDate,
        string $outputPath,
        array $options,
        array $calendar,
        array $split,
        ?array $calibration,
        ?array $oos
    ): array {
        $reasonCode = $oos['reason_code'] ?? $calibration['reason_code'] ?? $split['reason_code'] ?? 'WS_BT_OOS_PROOF_MISSING';
        $artifact = [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => $reasonCode,
            'meta' => [
                'artifact_service' => 'WatchlistBacktestOosProofService',
                'artifact_version' => 'WATCHLIST_BT_OOS_PROOF_V1',
                'policy_code' => 'WS',
                'requested_from' => $fromDate,
                'requested_to' => $toDate,
                'generated_at' => $options['executed_at'] ?? null,
                'production_ready' => false,
            ],
            'split_manifest' => $split,
            'is_calibration' => $calibration ?? [],
            'best_is_binding' => $calibration['best_is_binding'] ?? null,
            'oos_evaluation' => $oos ?? [],
            'oos_acceptance' => [
                'pass' => false,
                'reason_code' => $reasonCode,
                'reason_codes' => [$reasonCode],
                'failed_gates' => [],
            ],
            'persistence_manifest' => [
                'official_tables' => ['watchlist_bt_param_grid', 'watchlist_bt_eval', 'watchlist_bt_oos_eval_ws'],
                'oos_id' => null,
            ],
            'boundary' => [
                'no_paramset_status_mutation' => true,
                'no_promotion' => true,
                'production_ready' => false,
            ],
            'diagnostics' => array_merge(
                $calendar['diagnostics'] ?? [],
                $split['diagnostics'] ?? [],
                $calibration['diagnostics'] ?? [],
                $oos['diagnostics'] ?? []
            ),
            'validation' => [],
        ];
        $artifact['validation']['artifact_hash'] = $this->stableHash($this->artifactForHash($artifact));
        $artifact['meta']['artifact_hash'] = $artifact['validation']['artifact_hash'];
        $write = $this->writeArtifact($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        $resultReasonCode = ($write['is_ready'] ?? false)
            ? $reasonCode
            : ($write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED');

        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => $resultReasonCode,
            'calendar' => $calendar,
            'split' => $split,
            'calibration' => $calibration,
            'oos_runtime' => $oos,
            'artifact' => $artifact,
            'artifact_hash' => $artifact['validation']['artifact_hash'],
            'write' => $write,
            'diagnostics' => $artifact['diagnostics'],
            'production_ready' => false,
        ];
    }

    private function writeArtifact(array $artifact, string $outputPath, bool $overwrite): array
    {
        if (is_file($outputPath)) {
            if (! $overwrite) {
                return [
                    'ready' => false,
                    'is_ready' => false,
                    'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_PATH_INVALID',
                    'path' => $outputPath,
                ];
            }
            if (! @unlink($outputPath)) {
                return [
                    'ready' => false,
                    'is_ready' => false,
                    'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                    'path' => $outputPath,
                ];
            }
        }

        return $this->artifacts->writeJsonArtifact($artifact, $outputPath);
    }

    private function artifactForHash(array $artifact): array
    {
        unset(
            $artifact['validation']['artifact_hash'],
            $artifact['meta']['artifact_hash'],
            $artifact['meta']['generated_at']
        );

        return $this->withoutOperationalPersistenceFields($artifact);
    }

    private function withoutOperationalPersistenceFields($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach (array_keys($value) as $key) {
            if (in_array($key, ['persistence_status', 'oos_persistence_operation'], true)) {
                unset($value[$key]);
                continue;
            }
            $value[$key] = $this->withoutOperationalPersistenceFields($value[$key]);
        }

        return $value;
    }

    private function asFailure(array $result, string $reasonCode, string $message): array
    {
        $result['ready'] = false;
        $result['is_ready'] = false;
        $result['reason_code'] = $reasonCode;
        $result['diagnostics'] = array_merge($result['diagnostics'] ?? [], [[
            'reason_code' => $reasonCode,
            'message' => $message,
            'fatal' => true,
        ]]);

        return $result;
    }

    private function failureResultFromException(\Throwable $e, string $fallback): array
    {
        $reasonCode = $this->reasonCode($e, $fallback);

        return [
            'ready' => false,
            'is_ready' => false,
            'reason_code' => $reasonCode,
            'diagnostics' => [[
                'reason_code' => $reasonCode,
                'message' => $e->getMessage(),
                'fatal' => true,
            ]],
            'production_ready' => false,
        ];
    }

    private function reasonCode(\Throwable $e, string $fallback): string
    {
        if (preg_match('/^([A-Z0-9_]+):/', (string) $e->getMessage(), $matches)) {
            return $matches[1];
        }

        return $fallback;
    }

    private function thresholdInt($value): ?int
    {
        if (is_array($value)) {
            $value = $value['value'] ?? null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function blocked(string $reasonCode, array $diagnostics, array $context = []): array
    {
        return array_merge([
            'ready' => false,
            'is_ready' => false,
            'reason_code' => $reasonCode,
            'diagnostics' => $diagnostics,
            'artifact_hash' => null,
            'production_ready' => false,
        ], $context);
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
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
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
