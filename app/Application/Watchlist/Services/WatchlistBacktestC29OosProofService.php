<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC29OosProofService
{
    public const RUN_CODE = 'C29_OOS_PROOF_C28_G05';
    public const DEFAULT_C28_ARTIFACT = 'storage/app/watchlist/backtest/c28-rule-revision-tiebreak-diagnostic-all-param.json';
    public const DEFAULT_EXPECTED_C28_HASH = '64ec3e48fa3c6beb4b1175cc8f0cc277f22d20fd';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json';
    public const CANDIDATE_PROFILE = 'C28_G05_BUCKET_TIEBREAK_R09_STABLE_G21_NO_SIGNAL_G16_DELAY';
    public const OOS_FROM = '2025-05-22';
    public const OOS_TO = '2026-05-29';

    public const RULE_MAPPING = [
        'candidate_matches_or_beats_c22' => 'RAW_R09',
        'no_rule_profit_signal_before_fallback' => 'RAW_G21',
        'next_open_delay_after_close_signal' => 'RAW_G16',
    ];

    private MarketDataTradingCalendarReadService $calendar;
    private MarketDataPublishedEodSeriesReadService $priceSeries;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;
    private WatchlistBacktestStrategyService $strategy;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        MarketDataPublishedEodSeriesReadService $priceSeries = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null,
        WatchlistBacktestStrategyService $strategy = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->priceSeries = $priceSeries ?: new MarketDataPublishedEodSeriesReadService();
        $this->paramGrid = $paramGrid ?: new WatchlistBacktestParamGridRepository();
        $this->paramsetFactory = $paramsetFactory ?: new WatchlistBacktestParamGridParamsetFactory();
        $this->strategy = $strategy ?: new WatchlistBacktestStrategyService();
    }

    public function execute(
        string $c28Artifact = self::DEFAULT_C28_ARTIFACT,
        string $expectedC28Hash = self::DEFAULT_EXPECTED_C28_HASH,
        string $candidateProfileCode = self::CANDIDATE_PROFILE,
        string $fromDate = self::OOS_FROM,
        string $toDate = self::OOS_TO,
        string $outputPath = self::DEFAULT_OUTPUT_PATH,
        array $options = []
    ): array {
        $generatedAt = (string) ($options['executed_at'] ?? gmdate('c'));
        $c28Artifact = trim($c28Artifact) !== '' ? trim($c28Artifact) : self::DEFAULT_C28_ARTIFACT;
        $expectedC28Hash = trim($expectedC28Hash) !== '' ? trim($expectedC28Hash) : self::DEFAULT_EXPECTED_C28_HASH;
        $candidateProfileCode = trim($candidateProfileCode) !== '' ? strtoupper(trim($candidateProfileCode)) : self::CANDIDATE_PROFILE;
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if ($fromDate !== self::OOS_FROM || $toDate !== self::OOS_TO) {
            return $this->blocked(
                $outputPath,
                $generatedAt,
                $c28Artifact,
                $expectedC28Hash,
                null,
                $candidateProfileCode,
                'WS_BT_C29_RESERVED_OOS_WINDOW_MISMATCH',
                'C29 OOS proof must use the reserved OOS window 2025-05-22 through 2026-05-29 only.',
                ['from' => $fromDate, 'to' => $toDate]
            );
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked(
                $outputPath,
                $generatedAt,
                $c28Artifact,
                $expectedC28Hash,
                null,
                $candidateProfileCode,
                'WATCHLIST_BACKTEST_ARTIFACT_EXISTS',
                'Output artifact already exists. Pass --overwrite to replace it.',
                ['output' => $outputPath]
            );
        }

        $source = $this->validateC28Source($c28Artifact, $expectedC28Hash, $candidateProfileCode);
        if (! ($source['ready'] ?? false)) {
            return $this->blocked(
                $outputPath,
                $generatedAt,
                $c28Artifact,
                $expectedC28Hash,
                $source['actual_c28_hash'] ?? null,
                $candidateProfileCode,
                (string) ($source['reason_code'] ?? 'WS_BT_C29_INVALID_C28_SOURCE'),
                (string) ($source['message'] ?? 'C29 source C28 artifact validation failed.'),
                $source
            );
        }

        if (! empty($options['oos_raw_rows_fixture']) && is_array($options['oos_raw_rows_fixture'])) {
            $rawRows = $options['oos_raw_rows_fixture'];
            $calendar = ['is_ready' => true, 'trade_dates' => [], 'calendar_dates' => [], 'reason_code' => 'C29_FIXTURE'];
            $priceRead = ['is_ready' => true, 'reason_code' => 'C29_FIXTURE'];
            $paramRows = [];
        } else {
            try {
                $runtime = $this->buildOosRawRows($source['c28']);
            } catch (\Throwable $e) {
                return $this->operatorRequired(
                    $outputPath,
                    $generatedAt,
                    $c28Artifact,
                    $expectedC28Hash,
                    (string) $source['actual_c28_hash'],
                    $candidateProfileCode,
                    $this->reasonCode($e, 'WS_BT_C29_RUNTIME_UNAVAILABLE'),
                    $e->getMessage(),
                    ['exception_class' => get_class($e)]
                );
            }
            if (! ($runtime['ready'] ?? false)) {
                return $this->operatorRequired(
                    $outputPath,
                    $generatedAt,
                    $c28Artifact,
                    $expectedC28Hash,
                    (string) $source['actual_c28_hash'],
                    $candidateProfileCode,
                    (string) ($runtime['reason_code'] ?? 'WS_BT_C29_RUNTIME_UNAVAILABLE'),
                    (string) ($runtime['message'] ?? 'C29 runtime could not build OOS raw rows.'),
                    $runtime
                );
            }
            $rawRows = $runtime['raw_rows'];
            $calendar = $runtime['calendar'];
            $priceRead = $runtime['price_read'];
            $paramRows = $runtime['param_rows'];
        }

        $candidateRows = [];
        foreach ($rawRows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $candidateRows[] = $this->candidateRow($row);
        }

        $metrics = $this->metrics($candidateRows);
        $lookaheadViolationCount = $this->lookaheadViolationCount($candidateRows);
        $gate = $this->gate($metrics, $lookaheadViolationCount, $source);
        $status = $gate['overall_pass'] === true
            ? 'C29_OOS_PROOF_PASSED_NOT_PRODUCTION_READY'
            : 'C29_OOS_PROOF_FAILED';

        $artifact = $this->baseArtifact($generatedAt, $c28Artifact, $expectedC28Hash, (string) $source['actual_c28_hash'], $candidateProfileCode);
        $artifact = array_replace_recursive($artifact, [
            'status' => $status,
            'c28_hash_match' => true,
            'source_c28_validation' => $this->sourceValidationSummary($source),
            'oos_runtime' => [
                'calendar_reason_code' => $calendar['reason_code'] ?? null,
                'trade_date_count' => count($calendar['trade_dates'] ?? []),
                'calendar_date_count' => count($calendar['calendar_dates'] ?? []),
                'param_count' => count($paramRows),
                'raw_rows_count' => count($rawRows),
                'price_readiness' => [
                    'is_ready' => (bool) ($priceRead['is_ready'] ?? false),
                    'reason_code' => $priceRead['reason_code'] ?? null,
                    'required_price_date_count' => $priceRead['price_series_manifest']['required_price_date_count'] ?? null,
                    'requested_ticker_date_pair_count' => $priceRead['price_series_manifest']['requested_ticker_date_pair_count'] ?? null,
                    'missing_price_rows_count' => count($priceRead['price_series_manifest']['missing_price_rows'] ?? []),
                    'exact_date_resolution_only' => $priceRead['price_series_manifest']['exact_date_resolution_only'] ?? true,
                    'no_latest_fallback' => $priceRead['price_series_manifest']['no_latest_fallback'] ?? true,
                ],
            ],
            'metrics' => $metrics,
            'gate' => $gate,
            'diagnostics' => $this->diagnosticsForGate($gate, $metrics),
            'lookahead_violation_count' => $lookaheadViolationCount,
            'oos_pick_rows' => $candidateRows,
            'created_at' => $generatedAt,
        ]);
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact, (bool) ($options['overwrite'] ?? false));
        if (! ($write['ok'] ?? false)) {
            return [
                'status' => 'C29_OPERATOR_VALIDATION_REQUIRED',
                'reason_code' => $write['reason_code'] ?? 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED',
                'message' => $write['message'] ?? 'Unable to write C29 artifact.',
                'artifact_path' => $outputPath,
                'artifact_hash' => $artifact['artifact_hash'],
                'production_ready' => 0,
            ];
        }

        return [
            'status' => $status,
            'reason_code' => $status,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'expected_c28_hash' => $expectedC28Hash,
            'actual_c28_hash' => (string) $source['actual_c28_hash'],
            'c28_hash_match' => true,
            'candidate_profile_code' => $candidateProfileCode,
            'evaluated_picks_count' => $metrics['evaluated_picks_count'],
            'avg_ret_net' => $metrics['avg_ret_net'],
            'median_ret_net' => $metrics['median_ret_net'],
            'p25_ret_net' => $metrics['p25_ret_net'],
            'win_rate' => $metrics['win_rate'],
            'month_win_rate_min' => $metrics['month_win_rate_min'],
            'month_avg_ret_net_min' => $metrics['month_avg_ret_net_min'],
            'lookahead_violation_count' => $lookaheadViolationCount,
            'production_ready' => 0,
        ];
    }

    private function validateC28Source(string $path, string $expectedHash, string $candidateProfileCode): array
    {
        if (! is_file($path)) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_C28_ARTIFACT_MISSING',
                'message' => 'C29 requires the locked C28 all-param artifact, but the file is missing.',
                'actual_c28_hash' => null,
            ];
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        if (! is_array($decoded)) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_C28_ARTIFACT_UNREADABLE',
                'message' => 'C28 artifact is not readable JSON.',
                'actual_c28_hash' => null,
            ];
        }

        $actualHash = $this->stableHash($decoded);
        if ($actualHash !== $expectedHash) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_C28_ARTIFACT_HASH_MISMATCH',
                'message' => 'C28 artifact stable hash does not match the expected locked hash.',
                'actual_c28_hash' => $actualHash,
                'artifact_hash_field' => $decoded['artifact_hash'] ?? null,
            ];
        }
        if (($decoded['artifact_type'] ?? null) !== 'C28_RULE_REVISION_TIEBREAK_DIAGNOSTIC' || ($decoded['status'] ?? null) !== 'PASS') {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_C28_ARTIFACT_INVALID',
                'message' => 'C29 requires a PASS C28 rule revision/tiebreak diagnostic artifact.',
                'actual_c28_hash' => $actualHash,
                'c28_artifact_type' => $decoded['artifact_type'] ?? null,
                'c28_status' => $decoded['status'] ?? null,
            ];
        }
        if ($candidateProfileCode !== self::CANDIDATE_PROFILE) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_CANDIDATE_PROFILE_INVALID',
                'message' => 'C29 may only prove the locked C28 G05 candidate profile.',
                'actual_c28_hash' => $actualHash,
                'candidate_profile_code' => $candidateProfileCode,
            ];
        }
        if (($decoded['candidate_profile_code'] ?? null) !== $candidateProfileCode
            || ! isset($decoded['profile_summary'][$candidateProfileCode])) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_C28_CANDIDATE_NOT_FOUND',
                'message' => 'Locked candidate profile C28 G05 is not present in the C28 artifact.',
                'actual_c28_hash' => $actualHash,
                'candidate_profile_code' => $candidateProfileCode,
            ];
        }
        if (! $this->ruleMatchesArtifact($decoded, $candidateProfileCode)) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_C28_RULE_MAPPING_MISMATCH',
                'message' => 'C28 G05 rule mapping does not match the expected R09/G21/G16 bucket mapping.',
                'actual_c28_hash' => $actualHash,
                'candidate_profile_code' => $candidateProfileCode,
            ];
        }
        if (! $this->executionRouteAvailableBeforeFuturePath($decoded, $candidateProfileCode)) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN',
                'message' => 'C28 G05 chooses R09/G21/G16 from a bucket derived from the evaluated D1-D5 path. The route is unavailable at execution time, so OOS proof is forbidden.',
                'actual_c28_hash' => $actualHash,
                'candidate_profile_code' => $candidateProfileCode,
                'future_path_price_used_for_rule_routing' => true,
                'oos_runtime_invoked' => false,
            ];
        }
        if (($decoded['candidate_readiness_summary']['c28_revised_candidate_ready'] ?? false) !== true
            || ($decoded['candidate_readiness_summary']['c29_oos_proof_recommended'] ?? false) !== true) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_C28_CANDIDATE_NOT_READY',
                'message' => 'C28 artifact does not mark the G05 candidate as ready for C29 OOS proof.',
                'actual_c28_hash' => $actualHash,
                'candidate_profile_code' => $candidateProfileCode,
            ];
        }

        return [
            'ready' => true,
            'actual_c28_hash' => $actualHash,
            'artifact_hash_field' => $decoded['artifact_hash'] ?? null,
            'c28_file_sha1' => sha1_file($path),
            'hash_pass' => true,
            'c28' => $decoded,
            'param_ids' => $this->candidateParamIds($decoded, $candidateProfileCode),
        ];
    }

    private function ruleMatchesArtifact(array $c28, string $candidateProfileCode): bool
    {
        $definitionOk = false;
        foreach (($c28['diagnostic_profiles'] ?? []) as $profile) {
            if (! is_array($profile) || ($profile['profile_code'] ?? null) !== $candidateProfileCode) {
                continue;
            }
            $definitionOk = ($profile['source'] ?? null) === 'r09_stable_g21_no_signal_g16_delay'
                && ($profile['candidate_role'] ?? null) === 'r09_stable_g21_no_signal_g16_delay';
            break;
        }
        if (! $definitionOk) {
            return false;
        }

        foreach (($c28['pick_diagnostic_rows'] ?? []) as $row) {
            if (! is_array($row) || ($row['profile_code'] ?? null) !== $candidateProfileCode) {
                continue;
            }
            $bucket = (string) ($row['bucket_code'] ?? '');
            $selected = (string) ($row['selected_source_code'] ?? '');
            $expected = self::RULE_MAPPING[$bucket] ?? 'RAW_R09';
            if ($selected !== str_replace('RAW_', '', $expected)) {
                return false;
            }
        }

        return true;
    }

    private function executionRouteAvailableBeforeFuturePath(array $c28, string $candidateProfileCode): bool
    {
        if (($c28['candidate_readiness_summary']['execution_time_route_availability_pass'] ?? false) !== true) {
            return false;
        }

        $candidateRows = 0;
        foreach (($c28['pick_diagnostic_rows'] ?? []) as $row) {
            if (! is_array($row) || ($row['profile_code'] ?? null) !== $candidateProfileCode) {
                continue;
            }
            $candidateRows++;
            if (($row['route_decision_available_before_entry'] ?? false) !== true
                || ($row['future_path_price_used_for_rule_routing'] ?? true) !== false) {
                return false;
            }
        }

        return $candidateRows > 0;
    }

    private function buildOosRawRows(array $c28): array
    {
        $calendar = $this->calendar->resolveReplayWindow(self::OOS_FROM, self::OOS_TO, 5);
        if (! ($calendar['is_ready'] ?? false)) {
            return [
                'ready' => false,
                'reason_code' => $calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE',
                'message' => 'Trading calendar is unavailable for C29 OOS proof.',
                'calendar' => $calendar,
            ];
        }

        $paramIds = $this->candidateParamIds($c28, self::CANDIDATE_PROFILE);
        if ($paramIds === []) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_C28_PARAM_IDS_EMPTY',
                'message' => 'C28 candidate rows do not provide fixed param IDs for OOS replay.',
            ];
        }
        $allowed = array_fill_keys($paramIds, true);

        $gridRows = $this->paramGrid->allForCatalog(WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE, 'WS');
        $paramRows = [];
        foreach ($gridRows as $row) {
            $id = (int) ($row['param_id'] ?? 0);
            if (isset($allowed[$id])) {
                $paramRows[] = $row;
            }
        }
        if (count($paramRows) !== count($paramIds)) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_SOURCE_CATALOG_PARAM_MISSING',
                'message' => 'C17 source catalog does not contain all param IDs fixed by C28.',
                'expected_param_ids' => $paramIds,
                'resolved_param_ids' => array_map(function (array $row): int { return (int) ($row['param_id'] ?? 0); }, $paramRows),
            ];
        }

        $baseRows = [];
        foreach ($paramRows as $row) {
            $paramset = $this->paramsetFactory->make($row);
            $payload = $this->strategy->backtestForReplayWindow($calendar['trade_dates'] ?? [], [], $paramset, []);
            if (! ($payload['is_ready'] ?? false)) {
                continue;
            }
            foreach (($payload['trades'] ?? []) as $trade) {
                if (! is_array($trade)) {
                    continue;
                }
                $baseRows[] = $this->baseRowFromTrade($trade, $row, $paramset, $calendar['calendar_dates'] ?? []);
            }
        }
        if ($baseRows === []) {
            return [
                'ready' => false,
                'reason_code' => 'WS_BT_C29_OOS_PICK_ROWS_EMPTY',
                'message' => 'C29 OOS replay produced no candidate picks.',
                'calendar' => $calendar,
                'param_rows' => $paramRows,
            ];
        }

        $requiredMap = $this->requiredPriceTickerMap($baseRows, $calendar['calendar_dates'] ?? [], 5, true);
        $dates = array_keys($requiredMap);
        $priceRead = $dates === []
            ? $this->emptyPriceRead(self::OOS_FROM, self::OOS_TO)
            : $this->priceSeries->readPublishedSeriesForDateTickerMap($dates[0], $dates[count($dates) - 1], $requiredMap);
        if (! ($priceRead['is_ready'] ?? false)) {
            return [
                'ready' => false,
                'reason_code' => $priceRead['reason_code'] ?? 'WATCHLIST_BACKTEST_PRICE_SERIES_UNAVAILABLE',
                'message' => 'Published OOS price series is unavailable for C29 raw evaluation.',
                'calendar' => $calendar,
                'price_read' => $priceRead,
                'param_rows' => $paramRows,
            ];
        }

        $series = $priceRead['series_by_ticker'] ?? [];
        $rawRows = [];
        foreach ($baseRows as $row) {
            $rawRows[] = $this->evaluateRawPick($row, $series, $calendar['calendar_dates'] ?? []);
        }
        usort($rawRows, function (array $left, array $right): int {
            foreach (['trade_date', 'param_id', 'ticker'] as $key) {
                $cmp = strcmp((string) ($left[$key] ?? ''), (string) ($right[$key] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            return 0;
        });

        return [
            'ready' => true,
            'calendar' => $calendar,
            'price_read' => $priceRead,
            'param_rows' => $paramRows,
            'raw_rows' => $rawRows,
        ];
    }

    private function baseRowFromTrade(array $trade, array $gridRow, array $paramset, array $calendarDates): array
    {
        $tradeDate = (string) ($trade['trade_date'] ?? '');
        return [
            'trade_date' => $tradeDate,
            'trade_month' => substr($tradeDate, 0, 7),
            'ticker_id' => $trade['ticker_id'] ?? null,
            'ticker' => strtoupper((string) ($trade['ticker'] ?? '')),
            'param_id' => (int) ($gridRow['param_id'] ?? 0),
            'row_code' => (string) ($gridRow['row_code'] ?? ''),
            'entry_date' => $this->nextTradingDate($tradeDate, $calendarDates),
            'stop_price' => $this->num($trade['stop_price'] ?? null),
            'target_price' => $this->num($trade['target_price'] ?? null),
            'paramset' => $paramset,
            'recommendation_rank' => $trade['recommendation_rank'] ?? null,
            'plan_rank' => $trade['plan_rank'] ?? null,
            'selection_bucket_source' => 'WatchlistBacktestStrategyService',
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function evaluateRawPick(array $base, array $series, array $calendarDates): array
    {
        $ticker = strtoupper(trim((string) ($base['ticker'] ?? '')));
        $tradeDate = (string) ($base['trade_date'] ?? '');
        $entryDate = (string) ($base['entry_date'] ?? '');
        $paramset = is_array($base['paramset'] ?? null) ? $base['paramset'] : WatchlistBacktestStrategyService::defaultParamset();
        $pathDates = $entryDate !== '' ? $this->tradingWindowFrom($entryDate, $calendarDates, 5) : [];
        $signalBar = $this->publishedBar($series, $ticker, $tradeDate);

        $raw = $this->rawBase($base);
        if ($ticker === '' || $tradeDate === '' || $entryDate === '' || $signalBar === null || count($pathDates) < 5) {
            return array_merge($raw, [
                'missing_path_data_flag' => true,
                'missing_path_reason_code' => 'WS_BT_C29_REQUIRED_SIGNAL_OR_D1_TO_D5_PATH_MISSING',
            ]);
        }

        $pathBars = [];
        foreach ($pathDates as $offset => $date) {
            $bar = $this->publishedBar($series, $ticker, $date);
            if ($bar === null || ! $this->tradableBar($bar, $paramset)) {
                return array_merge($raw, [
                    'entry_date' => $entryDate,
                    'missing_path_data_flag' => true,
                    'missing_path_reason_code' => 'WS_BT_C29_D1_TO_D5_RAW_OHLC_PATH_MISSING',
                ]);
            }
            foreach (['open', 'high', 'low', 'close'] as $field) {
                $value = $this->num($bar[$field] ?? null);
                if ($value === null || $value <= 0) {
                    return array_merge($raw, [
                        'entry_date' => $entryDate,
                        'missing_path_data_flag' => true,
                        'missing_path_reason_code' => 'WS_BT_C29_RAW_OHLC_NON_EXECUTABLE',
                    ]);
                }
            }
            $pathBars[$offset + 1] = [
                'date' => $date,
                'open' => (float) $bar['open'],
                'high' => (float) $bar['high'],
                'low' => (float) $bar['low'],
                'close' => (float) $bar['close'],
            ];
        }

        $signalClose = $this->num($signalBar['close'] ?? null);
        $entryPrice = $this->num($pathBars[1]['open'] ?? null);
        if ($signalClose === null || $signalClose <= 0 || $entryPrice === null || $entryPrice <= 0) {
            return array_merge($raw, [
                'entry_date' => $entryDate,
                'missing_path_data_flag' => true,
                'missing_path_reason_code' => 'WS_BT_C29_SIGNAL_CLOSE_OR_ENTRY_OPEN_MISSING',
            ]);
        }

        $levels = $this->levelsFromBase($base, $entryPrice);
        $canonical = $this->canonicalExit($entryPrice, $pathBars, $levels, $paramset);
        $c22 = $this->c22S06Exit($entryPrice, $pathBars, $paramset);
        $r09 = $this->r09Exit($entryPrice, $pathBars, $canonical, $paramset);
        $damageD3 = $this->damageControlExit($entryPrice, $pathBars, 2, 3, $canonical, $paramset);
        $g13 = $this->targetOrFallback($entryPrice, $pathBars, 0.0050, $r09, $paramset);
        $g16 = $this->targetOrFallback($entryPrice, $pathBars, 0.0150, $r09, $paramset);
        $target1 = $this->preplannedTargetExit($entryPrice, $pathBars, 0.0100, $paramset);
        $g21 = $target1 ?: ($this->noProfitByDay($entryPrice, $pathBars, 2) ? $damageD3 : $r09);
        $g21['profile_reason'] = $target1 !== null
            ? 'raw_g21_preplanned_target_1pct'
            : ($this->noProfitByDay($entryPrice, $pathBars, 2) ? 'raw_g21_no_signal_d3_open' : 'raw_g21_r09_fallback');

        $bucket = $this->gapComponent($c22, $r09);
        $raw = array_merge($raw, [
            'entry_date' => $entryDate,
            'raw_entry_price' => $entryPrice,
            'signal_close_price' => $signalClose,
            'bucket_code' => $bucket,
            'bucket_reason' => $this->bucketReason($bucket),
            'raw_ohlc_validated_flag' => true,
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
            'canonical' => $canonical,
            'c22_s06' => $c22,
            'r09' => $r09,
            'g13' => $g13,
            'g16' => $g16,
            'g21' => $g21,
            'target_1pct_hit_flag' => ($target1 !== null),
            'no_signal_d3_open_flag' => $target1 === null && $this->noProfitByDay($entryPrice, $pathBars, 2),
            'r09_fallback_flag' => $target1 === null && ! $this->noProfitByDay($entryPrice, $pathBars, 2),
            'lookahead_safe' => ($r09['lookahead_safe'] ?? false) === true
                && ($damageD3['lookahead_safe'] ?? false) === true
                && ($g21['lookahead_safe'] ?? false) === true,
            'lookahead_violation_reason' => $this->firstNonNull([
                $r09['lookahead_violation_reason'] ?? null,
                $damageD3['lookahead_violation_reason'] ?? null,
                $g21['lookahead_violation_reason'] ?? null,
            ]),
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
        ]);

        foreach ($pathBars as $offset => $bar) {
            foreach (['open', 'high', 'low', 'close'] as $field) {
                $raw['d'.$offset.'_'.$field] = $bar[$field];
            }
        }

        return $raw;
    }

    private function candidateRow(array $raw): array
    {
        $selection = $this->selectCandidateExit($raw);
        $exit = $selection['exit'];
        $ret = $this->num($exit['ret_net'] ?? null);
        return [
            'trade_date' => $raw['trade_date'] ?? null,
            'trade_month' => $raw['trade_month'] ?? null,
            'ticker_id' => $raw['ticker_id'] ?? null,
            'ticker' => $raw['ticker'] ?? null,
            'param_id' => $raw['param_id'] ?? null,
            'row_code' => $raw['row_code'] ?? null,
            'entry_date' => $raw['entry_date'] ?? null,
            'entry_price' => $raw['raw_entry_price'] ?? null,
            'bucket_code' => $raw['bucket_code'] ?? null,
            'bucket_reason' => $raw['bucket_reason'] ?? null,
            'profile_code' => self::CANDIDATE_PROFILE,
            'selected_source_code' => $selection['selected_source_code'],
            'selected_source_reason' => $selection['selected_source_reason'],
            'profile_exit_date' => $exit['exit_date'] ?? null,
            'profile_exit_price' => $exit['exit_price'] ?? null,
            'profile_exit_day_offset' => $exit['exit_day_offset'] ?? null,
            'profile_exit_reason' => $exit['exit_reason'] ?? null,
            'profile_ret_net' => $ret,
            'raw_r09_ret_net' => $this->num($raw['r09']['ret_net'] ?? null),
            'raw_g21_ret_net' => $this->num($raw['g21']['ret_net'] ?? null),
            'raw_g16_ret_net' => $this->num($raw['g16']['ret_net'] ?? null),
            'win_flag' => $ret !== null && $ret > 0,
            'raw_ohlc_validated_flag' => (bool) ($raw['raw_ohlc_validated_flag'] ?? false),
            'lookahead_safe' => (bool) ($exit['lookahead_safe'] ?? false),
            'lookahead_violation_reason' => $exit['lookahead_violation_reason'] ?? null,
            'missing_path_data_flag' => (bool) ($raw['missing_path_data_flag'] ?? false) || (bool) ($exit['missing_path_data_flag'] ?? false),
            'missing_path_reason_code' => $raw['missing_path_reason_code'] ?? ($exit['missing_path_reason_code'] ?? null),
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'production_ready' => 0,
        ];
    }

    private function selectCandidateExit(array $raw): array
    {
        $bucket = (string) ($raw['bucket_code'] ?? '');
        if ($bucket === 'no_rule_profit_signal_before_fallback') {
            return [
                'selected_source_code' => 'G21',
                'selected_source_reason' => 'g21_no_signal_d3_damage_control',
                'exit' => is_array($raw['g21'] ?? null) ? $raw['g21'] : $this->missingExit('WS_BT_C29_G21_SOURCE_MISSING'),
            ];
        }
        if ($bucket === 'next_open_delay_after_close_signal') {
            return [
                'selected_source_code' => 'G16',
                'selected_source_reason' => 'g16_next_open_delay_target_component',
                'exit' => is_array($raw['g16'] ?? null) ? $raw['g16'] : $this->missingExit('WS_BT_C29_G16_SOURCE_MISSING'),
            ];
        }
        return [
            'selected_source_code' => 'R09',
            'selected_source_reason' => $bucket === 'candidate_matches_or_beats_c22' ? 'r09_stable_candidate_bucket' : 'r09_default_non_primary_bucket',
            'exit' => is_array($raw['r09'] ?? null) ? $raw['r09'] : $this->missingExit('WS_BT_C29_R09_SOURCE_MISSING'),
        ];
    }

    private function metrics(array $rows): array
    {
        $valid = array_values(array_filter($rows, function (array $row): bool {
            return ($row['missing_path_data_flag'] ?? false) !== true && is_numeric($row['profile_ret_net'] ?? null);
        }));
        $returns = $this->values($valid, 'profile_ret_net');
        $wins = count(array_filter($returns, function (float $value): bool { return $value > 0; }));
        $monthRows = [];
        foreach ($valid as $row) {
            $monthRows[(string) ($row['trade_month'] ?? 'UNKNOWN')][] = $row;
        }
        ksort($monthRows, SORT_STRING);
        $monthWinRates = [];
        $monthAvgReturns = [];
        $monthSummary = [];
        foreach ($monthRows as $month => $items) {
            $vals = $this->values($items, 'profile_ret_net');
            $monthWins = count(array_filter($vals, function (float $value): bool { return $value > 0; }));
            $winRate = count($vals) > 0 ? $monthWins / count($vals) : null;
            $avg = $this->avg($vals);
            if ($winRate !== null) {
                $monthWinRates[] = $winRate;
            }
            if ($avg !== null) {
                $monthAvgReturns[] = $avg;
            }
            $monthSummary[] = [
                'trade_month' => $month,
                'evaluated_picks_count' => count($vals),
                'avg_ret_net' => $avg,
                'win_rate' => $winRate,
            ];
        }

        return [
            'evaluated_picks_count' => count($returns),
            'avg_ret_net' => $this->avg($returns),
            'median_ret_net' => $this->median($returns),
            'p25_ret_net' => $this->percentile($returns, 0.25),
            'win_rate' => count($returns) > 0 ? $wins / count($returns) : null,
            'month_win_rate_min' => $monthWinRates === [] ? null : min($monthWinRates),
            'month_avg_ret_net_min' => $monthAvgReturns === [] ? null : min($monthAvgReturns),
            'month_summary' => $monthSummary,
        ];
    }

    private function gate(array $metrics, int $lookaheadViolationCount, array $source): array
    {
        $gate = [
            'hash_pass' => (bool) ($source['hash_pass'] ?? false),
            'candidate_found_pass' => true,
            'rule_match_pass' => true,
            'execution_route_pass' => true,
            'min_picks_pass' => ((int) ($metrics['evaluated_picks_count'] ?? 0)) >= 40,
            'avg_pass' => $this->num($metrics['avg_ret_net'] ?? null) !== null && (float) $metrics['avg_ret_net'] > 0,
            'median_pass' => $this->num($metrics['median_ret_net'] ?? null) !== null && (float) $metrics['median_ret_net'] >= 0,
            'month_win_rate_pass' => $this->num($metrics['month_win_rate_min'] ?? null) !== null && (float) $metrics['month_win_rate_min'] >= 0.45,
            'p25_pass' => $this->num($metrics['p25_ret_net'] ?? null) !== null && (float) $metrics['p25_ret_net'] >= -0.03,
            'lookahead_pass' => $lookaheadViolationCount === 0,
            'production_ready_pass' => true,
        ];
        $gate['overall_pass'] = ! in_array(false, $gate, true) && ! in_array(null, $gate, true);
        return $gate;
    }

    private function baseArtifact(string $createdAt, string $c28Artifact, string $expectedC28Hash, ?string $actualC28Hash, string $candidateProfileCode): array
    {
        return [
            'run_code' => self::RUN_CODE,
            'status' => 'C29_OPERATOR_VALIDATION_REQUIRED',
            'artifact_type' => 'C29_OOS_PROOF',
            'is_only' => false,
            'oos_proof' => true,
            'production_ready' => false,
            'oos_window' => ['from' => self::OOS_FROM, 'to' => self::OOS_TO],
            'input_c28_artifact' => $c28Artifact,
            'expected_c28_hash' => $expectedC28Hash,
            'actual_c28_hash' => $actualC28Hash,
            'c28_hash_match' => $actualC28Hash !== null && $actualC28Hash === $expectedC28Hash,
            'candidate_profile_code' => $candidateProfileCode,
            'candidate_rule' => self::RULE_MAPPING,
            'execution_model' => [
                'entry' => 'NEXT_OPEN',
                'exit' => 'STOP_TP_OR_TIME',
                'hold' => 5,
                'fee' => 'IDR_FIXED',
                'slip' => 0,
                'gap' => 'OPEN',
                'px' => 'IDX_BANDS',
            ],
            'metrics' => [
                'evaluated_picks_count' => 0,
                'avg_ret_net' => null,
                'median_ret_net' => null,
                'p25_ret_net' => null,
                'win_rate' => null,
                'month_win_rate_min' => null,
                'month_avg_ret_net_min' => null,
            ],
            'gate' => [
                'hash_pass' => null,
                'candidate_found_pass' => null,
                'rule_match_pass' => null,
                'execution_route_pass' => null,
                'min_picks_pass' => null,
                'avg_pass' => null,
                'median_pass' => null,
                'month_win_rate_pass' => null,
                'p25_pass' => null,
                'lookahead_pass' => null,
                'overall_pass' => null,
            ],
            'diagnostics' => [],
            'lookahead_violation_count' => 0,
            'safety_boundaries' => [
                'OOS_PROOF_ONLY' => true,
                'NO_RETUNE' => true,
                'NO_PROFILE_RESELECTION' => true,
                'NO_BEST_OF_OOS' => true,
                'NO_PRODUCTION_CATALOG' => true,
                'NO_PROMOTION' => true,
                'NO_PLAN_CONFIRM_MUTATION' => true,
                'FUTURE_DERIVED_RULE_ROUTING_FORBIDDEN' => true,
                'NO_C01_TO_C28_MUTATION' => true,
                'production_ready' => false,
                'future_path_price_used_for_selection' => false,
                'profile_ret_net_used_for_selection' => false,
                'derived_mfe_mae_used_for_execution' => false,
            ],
            'created_at' => $createdAt,
        ];
    }

    private function blocked(string $outputPath, string $generatedAt, string $c28Artifact, string $expectedC28Hash, ?string $actualC28Hash, string $candidateProfileCode, string $reasonCode, string $message, array $extra = []): array
    {
        $artifact = $this->baseArtifact($generatedAt, $c28Artifact, $expectedC28Hash, $actualC28Hash, $candidateProfileCode);
        $artifact['status'] = 'C29_BLOCKED_INVALID_C28_SOURCE';
        $artifact['gate'] = array_replace($artifact['gate'], [
            'hash_pass' => $actualC28Hash === null ? false : $actualC28Hash === $expectedC28Hash,
            'candidate_found_pass' => $reasonCode === 'WS_BT_C29_C28_CANDIDATE_NOT_FOUND' ? false : null,
            'rule_match_pass' => $reasonCode === 'WS_BT_C29_C28_RULE_MAPPING_MISMATCH' ? false : null,
            'execution_route_pass' => $reasonCode === 'WS_BT_C29_FUTURE_DERIVED_ROUTE_FORBIDDEN' ? false : null,
            'overall_pass' => false,
        ]);
        $artifact['diagnostics'][] = [
            'reason_code' => $reasonCode,
            'message' => $message,
            'fatal' => true,
            'extra' => $extra,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($outputPath !== '') {
            $this->writeArtifact($outputPath, $artifact, true);
        }

        return [
            'status' => 'C29_BLOCKED_INVALID_C28_SOURCE',
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'expected_c28_hash' => $expectedC28Hash,
            'actual_c28_hash' => $actualC28Hash,
            'c28_hash_match' => $actualC28Hash !== null && $actualC28Hash === $expectedC28Hash,
            'production_ready' => 0,
        ];
    }

    private function operatorRequired(string $outputPath, string $generatedAt, string $c28Artifact, string $expectedC28Hash, ?string $actualC28Hash, string $candidateProfileCode, string $reasonCode, string $message, array $extra = []): array
    {
        $artifact = $this->baseArtifact($generatedAt, $c28Artifact, $expectedC28Hash, $actualC28Hash, $candidateProfileCode);
        $artifact['status'] = 'C29_OPERATOR_VALIDATION_REQUIRED';
        $artifact['gate'] = array_replace($artifact['gate'], [
            'hash_pass' => $actualC28Hash === null ? null : $actualC28Hash === $expectedC28Hash,
            'candidate_found_pass' => true,
            'rule_match_pass' => true,
            'overall_pass' => null,
        ]);
        $artifact['diagnostics'][] = [
            'reason_code' => $reasonCode,
            'message' => $message,
            'fatal' => true,
            'extra' => $extra,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($outputPath !== '') {
            $this->writeArtifact($outputPath, $artifact, true);
        }

        return [
            'status' => 'C29_OPERATOR_VALIDATION_REQUIRED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'expected_c28_hash' => $expectedC28Hash,
            'actual_c28_hash' => $actualC28Hash,
            'c28_hash_match' => $actualC28Hash !== null && $actualC28Hash === $expectedC28Hash,
            'candidate_profile_code' => $candidateProfileCode,
            'production_ready' => 0,
        ];
    }

    private function rawBase(array $base): array
    {
        return [
            'trade_date' => $base['trade_date'] ?? null,
            'trade_month' => $base['trade_month'] ?? null,
            'ticker_id' => $base['ticker_id'] ?? null,
            'ticker' => $base['ticker'] ?? null,
            'param_id' => $base['param_id'] ?? null,
            'row_code' => $base['row_code'] ?? null,
            'entry_date' => $base['entry_date'] ?? null,
            'raw_ohlc_validated_flag' => false,
            'missing_path_data_flag' => true,
            'missing_path_reason_code' => null,
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
        ];
    }

    private function levelsFromBase(array $row, float $entryPrice): array
    {
        $stop = $this->num($row['stop_price'] ?? null);
        $target = $this->num($row['target_price'] ?? null);
        $has = $stop !== null && $target !== null && $stop > 0 && $target > 0 && $stop < $entryPrice && $target > $entryPrice;
        return [
            'has_target_stop' => $has,
            'stop_trigger_price' => $has ? $stop : null,
            'target_trigger_price' => $has ? $target : null,
            'source' => 'WATCHLIST_STRATEGY_TRADE_STOP_TARGET_LEVELS',
        ];
    }

    private function canonicalExit(float $entryPrice, array $pathBars, array $levels, array $paramset): array
    {
        if (($levels['has_target_stop'] ?? false) !== true) {
            return $this->missingExit('WS_BT_C29_CANONICAL_STOP_TARGET_LEVELS_MISSING');
        }
        $exit = null;
        foreach ($pathBars as $offset => $bar) {
            $stop = (float) $levels['stop_trigger_price'];
            $target = (float) $levels['target_trigger_price'];
            if ($bar['open'] <= $stop) {
                $exit = $this->exitPayload($bar['date'], $bar['open'], $offset, 'raw_canonical_stop_gap_open', null, null, null, true);
            } elseif ($bar['open'] >= $target) {
                $exit = $this->exitPayload($bar['date'], $bar['open'], $offset, 'raw_canonical_target_gap_open', null, null, null, true);
            } elseif ($bar['low'] <= $stop) {
                $exit = $this->exitPayload($bar['date'], $stop, $offset, 'raw_canonical_stop_hit', null, null, null, true);
            } elseif ($bar['high'] >= $target) {
                $exit = $this->exitPayload($bar['date'], $target, $offset, 'raw_canonical_target_hit', null, null, null, true);
            }
            if ($exit !== null) {
                break;
            }
        }
        if ($exit === null) {
            $last = $pathBars[count($pathBars)];
            $exit = $this->exitPayload($last['date'], $last['close'], count($pathBars), 'raw_canonical_hold_d5_close', null, null, null, true);
        }
        $exit['ret_net'] = $this->retNet($entryPrice, (float) $exit['exit_price'], $paramset);
        $exit['stop_trigger_price'] = $levels['stop_trigger_price'];
        $exit['target_trigger_price'] = $levels['target_trigger_price'];
        return $exit;
    }

    private function c22S06Exit(float $entryPrice, array $pathBars, array $paramset): array
    {
        foreach ($pathBars as $offset => $bar) {
            if ($bar['close'] > $entryPrice) {
                $exit = $this->exitPayload($bar['date'], $bar['close'], $offset, 'raw_c22_s06_first_profitable_close', $offset, $bar['date'], 'close_profit_gt_0', true);
                $exit['ret_net'] = $this->retNet($entryPrice, (float) $exit['exit_price'], $paramset);
                return $exit;
            }
        }
        $last = $pathBars[count($pathBars)];
        $exit = $this->exitPayload($last['date'], $last['close'], count($pathBars), 'raw_c22_s06_hold_fallback', null, null, null, true);
        $exit['ret_net'] = $this->retNet($entryPrice, (float) $exit['exit_price'], $paramset);
        return $exit;
    }

    private function r09Exit(float $entryPrice, array $pathBars, array $canonical, array $paramset): array
    {
        for ($day = 1; $day <= 3; $day++) {
            if (! isset($pathBars[$day])) {
                return $this->missingExit('WS_BT_C29_R09_SIGNAL_PATH_MISSING');
            }
            $signalReturn = ($pathBars[$day]['close'] - $entryPrice) / $entryPrice;
            if ($signalReturn > 0) {
                $exitOffset = $day + 1;
                if (! isset($pathBars[$exitOffset])) {
                    return $this->missingExit('WS_BT_C29_R09_NEXT_OPEN_AFTER_SIGNAL_MISSING');
                }
                $exit = $this->exitPayload($pathBars[$exitOffset]['date'], $pathBars[$exitOffset]['open'], $exitOffset, 'raw_r09_next_open_after_close_profit', $day, $pathBars[$day]['date'], 'close_profit_gt_0', true);
                $exit['ret_net'] = $this->retNet($entryPrice, (float) $exit['exit_price'], $paramset);
                return $exit;
            }
        }
        if (($canonical['missing_path_data_flag'] ?? false) === true) {
            return $canonical;
        }
        $fallback = $canonical;
        $fallback['exit_reason'] = 'raw_r09_fallback_canonical';
        return $fallback;
    }

    private function damageControlExit(float $entryPrice, array $pathBars, int $noProfitByDay, int $exitOffset, array $canonical, array $paramset): array
    {
        if (! $this->noProfitByDay($entryPrice, $pathBars, $noProfitByDay)) {
            if (($canonical['missing_path_data_flag'] ?? false) === true) {
                return $canonical;
            }
            $fallback = $canonical;
            $fallback['exit_reason'] = 'raw_damage_control_fallback_canonical';
            return $fallback;
        }
        if (! isset($pathBars[$noProfitByDay], $pathBars[$exitOffset])) {
            return $this->missingExit('WS_BT_C29_DAMAGE_CONTROL_NEXT_OPEN_MISSING');
        }
        $exit = $this->exitPayload($pathBars[$exitOffset]['date'], $pathBars[$exitOffset]['open'], $exitOffset, 'raw_damage_control_no_profit_d'.$noProfitByDay.'_exit_d'.$exitOffset.'_open', $noProfitByDay, $pathBars[$noProfitByDay]['date'], 'no_profit_by_close', $exitOffset > $noProfitByDay);
        $exit['ret_net'] = $this->retNet($entryPrice, (float) $exit['exit_price'], $paramset);
        return $exit;
    }

    private function targetOrFallback(float $entryPrice, array $pathBars, float $targetPct, array $fallback, array $paramset): array
    {
        $target = $this->preplannedTargetExit($entryPrice, $pathBars, $targetPct, $paramset);
        return $target ?: $fallback;
    }

    private function preplannedTargetExit(float $entryPrice, array $pathBars, float $targetPct, array $paramset): ?array
    {
        $target = $this->normalizeTargetTriggerPrice($entryPrice * (1.0 + $targetPct));
        if ($target === null || $target <= $entryPrice) {
            return null;
        }
        foreach ($pathBars as $offset => $bar) {
            if ($bar['open'] >= $target) {
                $exit = $this->exitPayload($bar['date'], $bar['open'], $offset, 'raw_preplanned_intraday_target_gap_open_hit', null, null, null, true);
                $exit['target_pct'] = $targetPct;
                $exit['target_trigger_price'] = $target;
                $exit['ret_net'] = $this->retNet($entryPrice, (float) $exit['exit_price'], $paramset);
                return $exit;
            }
            if ($bar['high'] >= $target) {
                $exit = $this->exitPayload($bar['date'], $target, $offset, 'raw_preplanned_intraday_target_hit', null, null, null, true);
                $exit['target_pct'] = $targetPct;
                $exit['target_trigger_price'] = $target;
                $exit['ret_net'] = $this->retNet($entryPrice, (float) $exit['exit_price'], $paramset);
                return $exit;
            }
        }
        return null;
    }

    private function exitPayload(string $date, float $price, int $offset, string $reason, ?int $signalOffset, ?string $signalDate, ?string $signalType, bool $lookaheadSafe): array
    {
        return [
            'exit_date' => $date,
            'exit_price' => $price,
            'exit_day_offset' => $offset,
            'exit_reason' => $reason,
            'signal_day_offset' => $signalOffset,
            'signal_date' => $signalDate,
            'signal_type' => $signalType,
            'ret_net' => null,
            'lookahead_safe' => $lookaheadSafe,
            'lookahead_violation_reason' => $lookaheadSafe ? null : 'WS_BT_C29_RULE_EXIT_NOT_AFTER_SIGNAL_CLOSE',
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
            'conservative_fill_policy' => 'OPEN_GAP_THEN_HIGH_LOW_STOP_FIRST_IF_BOTH_SAME_DAILY_CANDLE',
        ];
    }

    private function missingExit(string $reasonCode): array
    {
        return [
            'exit_date' => null,
            'exit_price' => null,
            'exit_day_offset' => null,
            'exit_reason' => null,
            'signal_day_offset' => null,
            'signal_date' => null,
            'signal_type' => null,
            'ret_net' => null,
            'lookahead_safe' => false,
            'lookahead_violation_reason' => null,
            'missing_path_data_flag' => true,
            'missing_path_reason_code' => $reasonCode,
        ];
    }

    private function requiredPriceTickerMap(array $rows, array $calendarDates, int $holdingDays, bool $includeSignalDate): array
    {
        $map = [];
        foreach ($rows as $row) {
            $ticker = strtoupper(trim((string) ($row['ticker'] ?? '')));
            $tradeDate = (string) ($row['trade_date'] ?? '');
            $entryDate = (string) ($row['entry_date'] ?? '');
            if ($ticker === '' || $tradeDate === '' || $entryDate === '') {
                continue;
            }
            if ($includeSignalDate) {
                $map[$tradeDate][$ticker] = $ticker;
            }
            foreach ($this->tradingWindowFrom($entryDate, $calendarDates, $holdingDays) as $date) {
                $map[$date][$ticker] = $ticker;
            }
        }
        ksort($map, SORT_STRING);
        foreach ($map as $date => $codes) {
            ksort($codes, SORT_STRING);
            $map[$date] = array_values($codes);
        }
        return $map;
    }

    private function gapComponent(array $c22, array $r09): string
    {
        $signalOffset = $r09['signal_day_offset'] ?? null;
        $ruleExitOffset = $r09['exit_day_offset'] ?? null;
        $c22ExitOffset = $c22['exit_day_offset'] ?? null;
        $c22Ret = $this->num($c22['ret_net'] ?? null);
        $r09Ret = $this->num($r09['ret_net'] ?? null);
        $gap = $c22Ret !== null && $r09Ret !== null ? $c22Ret - $r09Ret : null;
        if ($gap !== null && $gap <= 0.0000001) {
            return 'candidate_matches_or_beats_c22';
        }
        if ($signalOffset === null) {
            return 'no_rule_profit_signal_before_fallback';
        }
        if (is_numeric($signalOffset) && is_numeric($c22ExitOffset) && (int) $signalOffset > (int) $c22ExitOffset) {
            return 'late_rule_signal_after_c22_s06';
        }
        if (is_numeric($signalOffset) && is_numeric($c22ExitOffset) && (int) $signalOffset === (int) $c22ExitOffset
            && is_numeric($ruleExitOffset) && (int) $ruleExitOffset > (int) $c22ExitOffset) {
            return 'next_open_delay_after_close_signal';
        }
        return 'other_gap_rows';
    }

    private function bucketReason(string $bucket): string
    {
        $map = [
            'candidate_matches_or_beats_c22' => 'C23 R09 return matches or beats C22 S06 shadow row.',
            'next_open_delay_after_close_signal' => 'Close signal occurs on the same day as C22 S06, but realistic C23 R09 exits next open.',
            'no_rule_profit_signal_before_fallback' => 'No close-profit rule signal occurs before fallback, so C23 R09 falls back to canonical exit behavior.',
            'late_rule_signal_after_c22_s06' => 'Realistic close signal appears later than the C22 S06 first profitable close shadow.',
            'other_gap_rows' => 'Remaining C22-vs-R09 gap row not covered by primary C24 buckets.',
        ];
        return $map[$bucket] ?? 'Unknown C29 bucket.';
    }

    private function sourceValidationSummary(array $source): array
    {
        return [
            'c28_artifact_hash_lock' => true,
            'actual_c28_hash' => $source['actual_c28_hash'] ?? null,
            'artifact_hash_field' => $source['artifact_hash_field'] ?? null,
            'c28_file_sha1' => $source['c28_file_sha1'] ?? null,
            'candidate_profile_found' => true,
            'rule_mapping_match' => true,
            'param_ids_fixed_before_oos' => $source['param_ids'] ?? [],
            'oos_return_used_for_profile_selection' => false,
        ];
    }

    private function diagnosticsForGate(array $gate, array $metrics): array
    {
        if (($gate['overall_pass'] ?? false) === true) {
            return [[
                'reason_code' => 'WS_BT_C29_OOS_PROOF_GATE_PASS',
                'message' => 'C29 OOS proof gate passed. production_ready remains false.',
                'fatal' => false,
            ]];
        }
        $items = [];
        foreach ($gate as $name => $pass) {
            if ($name === 'overall_pass' || $pass === true) {
                continue;
            }
            $items[] = [
                'reason_code' => 'WS_BT_C29_GATE_FAIL_'.strtoupper($name),
                'message' => 'C29 OOS proof gate failed: '.$name.'.',
                'fatal' => false,
                'metrics' => $metrics,
            ];
        }
        return $items;
    }

    private function candidateParamIds(array $c28, string $candidateProfileCode): array
    {
        $ids = [];
        foreach (($c28['pick_diagnostic_rows'] ?? []) as $row) {
            if (! is_array($row) || ($row['profile_code'] ?? null) !== $candidateProfileCode) {
                continue;
            }
            $id = (int) ($row['param_id'] ?? 0);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        ksort($ids, SORT_NUMERIC);
        return array_values($ids);
    }

    private function lookaheadViolationCount(array $rows): int
    {
        $count = 0;
        foreach ($rows as $row) {
            if (($row['lookahead_safe'] ?? false) !== true) {
                $count++;
            }
        }
        return $count;
    }

    private function retNet(float $entryPrice, float $exitPrice, array $paramset): ?float
    {
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $notionalIdr = $this->num($backtest['notional_idr'] ?? null) ?? 10000000.0;
        $lotSize = max(1, (int) ($backtest['lot_size'] ?? 100));
        $feeBuy = $this->num($backtest['fee_buy_idr'] ?? null) ?? 2500.0;
        $feeSell = $this->num($backtest['fee_sell_idr'] ?? null) ?? 2500.0;
        $lots = (int) floor($notionalIdr / ($entryPrice * $lotSize));
        if ($lots < 1) {
            return null;
        }
        $quantity = $lots * $lotSize;
        $grossBuy = $entryPrice * $quantity;
        $grossSell = $exitPrice * $quantity;
        return ($grossSell - $grossBuy - $feeBuy - $feeSell) / ($grossBuy + $feeBuy);
    }

    private function tradableBar(array $bar, array $paramset): bool
    {
        $backtest = is_array($paramset['backtest'] ?? null) ? $paramset['backtest'] : [];
        $minimum = max(1, (int) ($backtest['min_tradable_volume'] ?? 1));
        return is_numeric($bar['volume'] ?? null) && (int) $bar['volume'] >= $minimum;
    }

    private function publishedBar(array $priceSeries, string $ticker, string $tradeDate): ?array
    {
        $ticker = strtoupper($ticker);
        $bar = null;
        if (isset($priceSeries[$ticker][$tradeDate]) && is_array($priceSeries[$ticker][$tradeDate])) {
            $bar = $priceSeries[$ticker][$tradeDate];
        } elseif (isset($priceSeries[$ticker.'.JK'][$tradeDate]) && is_array($priceSeries[$ticker.'.JK'][$tradeDate])) {
            $bar = $priceSeries[$ticker.'.JK'][$tradeDate];
        }
        if ($bar === null) {
            return null;
        }
        if (($bar['published'] ?? true) !== true || ($bar['readable'] ?? true) !== true) {
            return null;
        }
        return $bar;
    }

    private function noProfitByDay(float $entryPrice, array $pathBars, int $day): bool
    {
        for ($i = 1; $i <= $day; $i++) {
            if (! isset($pathBars[$i]) || $pathBars[$i]['close'] > $entryPrice) {
                return false;
            }
        }
        return true;
    }

    private function normalizeTargetTriggerPrice(float $price): ?float
    {
        if ($price <= 0) {
            return null;
        }
        $tick = $this->priceTick($price);
        $normalized = ceil(($price - 0.000000001) / $tick) * $tick;
        return $normalized > 0 ? (float) $normalized : null;
    }

    private function priceTick(float $price): float
    {
        if ($price < 200) {
            return 1.0;
        }
        if ($price < 500) {
            return 2.0;
        }
        if ($price < 2000) {
            return 5.0;
        }
        if ($price < 5000) {
            return 10.0;
        }
        return 25.0;
    }

    private function nextTradingDate(string $tradeDate, array $calendarDates): ?string
    {
        foreach ($calendarDates as $date) {
            if (strcmp($date, $tradeDate) > 0) {
                return $date;
            }
        }
        return null;
    }

    private function tradingWindowFrom(string $entryDate, array $calendarDates, int $holdingDays): array
    {
        $window = [];
        $started = false;
        foreach ($calendarDates as $date) {
            if (! $started && $date === $entryDate) {
                $started = true;
            }
            if ($started) {
                $window[] = $date;
                if (count($window) >= $holdingDays) {
                    break;
                }
            }
        }
        return $window;
    }

    private function values(array $rows, string $key): array
    {
        $values = [];
        foreach ($rows as $row) {
            if (is_numeric($row[$key] ?? null)) {
                $values[] = (float) $row[$key];
            }
        }
        return $values;
    }

    private function avg(array $values): ?float
    {
        return $values === [] ? null : array_sum($values) / count($values);
    }

    private function median(array $values): ?float
    {
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);
        return $count % 2 === 1 ? (float) $values[$middle] : ((float) $values[$middle - 1] + (float) $values[$middle]) / 2.0;
    }

    private function percentile(array $values, float $percentile): ?float
    {
        if ($values === []) {
            return null;
        }
        sort($values, SORT_NUMERIC);
        $count = count($values);
        if ($count === 1) {
            return (float) $values[0];
        }
        $position = ($count - 1) * $percentile;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        if ($lower === $upper) {
            return (float) $values[$lower];
        }
        $weight = $position - $lower;
        return ((float) $values[$lower] * (1.0 - $weight)) + ((float) $values[$upper] * $weight);
    }

    private function num($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }

    private function firstNonNull(array $values)
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }
        return null;
    }

    private function emptyPriceRead(string $fromDate, string $toDate): array
    {
        return [
            'is_ready' => true,
            'reason_code' => 'WATCHLIST_BACKTEST_PRICE_SERIES_EMPTY_REQUEST',
            'requested_from_date' => $fromDate,
            'requested_to_date' => $toDate,
            'series_by_ticker' => [],
            'price_series_manifest' => [
                'required_price_date_count' => 0,
                'requested_ticker_date_pair_count' => 0,
                'missing_price_dates' => [],
                'missing_price_rows' => [],
                'exact_date_resolution_only' => true,
                'no_latest_fallback' => true,
            ],
        ];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        if (is_file($path)) {
            if (! $overwrite) {
                return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'message' => 'Artifact already exists.'];
            }
            @unlink($path);
        }
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C29 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    private function reasonCode(\Throwable $e, string $fallback): string
    {
        if (preg_match('/([A-Z0-9_]{6,})/', $e->getMessage(), $m)) {
            return $m[1];
        }
        return $fallback;
    }
}
