<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;

class WatchlistBacktestC27CatalogCandidateRawOhlcValidationService
{
    public const ARTIFACT_TYPE = 'C27_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION';
    public const DEFAULT_C26_INPUT_PATH = 'storage/app/watchlist/backtest/c26-catalog-candidate-diagnostic-all-param.json';
    public const DEFAULT_C21_INPUT_PATH = 'storage/app/watchlist/backtest/c21-entry-exit-behavior-diagnostic-all-param.json';
    public const DEFAULT_OUTPUT_PATH = 'storage/app/watchlist/backtest/c27-catalog-candidate-raw-ohlc-validation-all-param.json';
    public const DEFAULT_SOURCE_CATALOG_CODE = WatchlistBacktestC17ParamGridCatalog::CATALOG_CODE;

    public const C26_G21 = 'C26_G03_C25_G21_PRIMARY_BALANCED_CANDIDATE';
    public const C25_G21 = WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G21;
    public const C25_G13 = WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G13;
    public const C25_G16 = WatchlistBacktestC26CatalogCandidateDiagnosticService::C25_G16;

    private MarketDataTradingCalendarReadService $calendar;
    private MarketDataPublishedEodSeriesReadService $priceSeries;
    private WatchlistBacktestParamGridRepository $paramGrid;
    private WatchlistBacktestParamGridParamsetFactory $paramsetFactory;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        MarketDataPublishedEodSeriesReadService $priceSeries = null,
        WatchlistBacktestParamGridRepository $paramGrid = null,
        WatchlistBacktestParamGridParamsetFactory $paramsetFactory = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->priceSeries = $priceSeries ?: new MarketDataPublishedEodSeriesReadService();
        $this->paramGrid = $paramGrid ?: new WatchlistBacktestParamGridRepository();
        $this->paramsetFactory = $paramsetFactory ?: new WatchlistBacktestParamGridParamsetFactory();
    }

    public static function validationProfiles(): array
    {
        return [
            'C27_G00_RAW_CANONICAL_BASELINE' => ['family' => 'baseline', 'candidate_role' => 'raw_canonical_baseline', 'source' => 'canonical'],
            'C27_G01_RAW_C22_S06_SHADOW' => ['family' => 'shadow_benchmark', 'candidate_role' => 'raw_c22_s06_shadow', 'source' => 'c22_s06'],
            'C27_G02_RAW_C23_R09_NEXT_OPEN_RULE' => ['family' => 'r09_bridge', 'candidate_role' => 'raw_r09_next_open_rule', 'source' => 'r09'],
            'C27_G03_RAW_C25_G13_TARGET_0_50PCT' => ['family' => 'defensive_comparator', 'candidate_role' => 'raw_g13_defensive_target_0_50pct', 'source' => 'g13'],
            'C27_G04_RAW_C25_G16_TARGET_1_50PCT' => ['family' => 'next_open_delay_comparator', 'candidate_role' => 'raw_g16_target_1_50pct', 'source' => 'g16'],
            'C27_G05_RAW_C25_G21_PRIMARY_COMBO' => ['family' => 'primary_candidate', 'candidate_role' => 'raw_g21_target_1pct_or_no_signal_d3_or_r09', 'source' => 'g21'],
            'C27_G06_G21_VS_C25_RECONCILIATION_GATE' => ['family' => 'readiness_gate', 'candidate_role' => 'raw_g21_vs_c25_reconciliation_gate', 'source' => 'g21'],
            'C27_G07_INTRADAY_SEQUENCE_CONSERVATIVE_GATE' => ['family' => 'readiness_gate', 'candidate_role' => 'intraday_sequence_conservative_gate', 'source' => 'g21'],
            'C27_G08_CATALOG_CANDIDATE_READINESS_SCORE' => ['family' => 'readiness_score', 'candidate_role' => 'catalog_candidate_readiness_score', 'source' => 'g21'],
        ];
    }

    public function execute(string $c26InputPath = '', string $outputPath = '', array $options = []): array
    {
        $catalogCode = (string) ($options['catalog_code'] ?? self::DEFAULT_SOURCE_CATALOG_CODE);
        $fromDate = (string) ($options['from'] ?? WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $toDate = (string) ($options['to'] ?? WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $c26InputPath = trim($c26InputPath) !== '' ? trim($c26InputPath) : self::DEFAULT_C26_INPUT_PATH;
        $c21InputPath = trim((string) ($options['input_c21_artifact'] ?? self::DEFAULT_C21_INPUT_PATH));
        $outputPath = trim($outputPath) !== '' ? trim($outputPath) : self::DEFAULT_OUTPUT_PATH;

        if ($fromDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE
            || $toDate !== WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE) {
            return $this->blocked('WS_BT_C27_IS_ONLY_WINDOW_MISMATCH', 'C27 raw OHLC validation requires the frozen IS window only.', $outputPath, $options, [
                'catalog_code' => $catalogCode,
                'from' => $fromDate,
                'to' => $toDate,
            ]);
        }
        if (is_file($outputPath) && empty($options['overwrite'])) {
            return $this->blocked('WATCHLIST_BACKTEST_ARTIFACT_EXISTS', 'Output artifact already exists. Pass --overwrite to replace it.', '', $options);
        }

        $c26 = $this->readJson($c26InputPath);
        if ($c26 === null) {
            return $this->blocked('WS_BT_C27_C26_ARTIFACT_UNREADABLE', 'C27 requires a readable C26 catalog-candidate diagnostic artifact.', $outputPath, $options, [
                'c26_all_param_artifact_path' => $c26InputPath,
            ]);
        }
        if (($c26['artifact_type'] ?? null) !== 'C26_CATALOG_CANDIDATE_DIAGNOSTIC' || ($c26['status'] ?? null) !== 'PASS') {
            return $this->blocked('WS_BT_C27_C26_ARTIFACT_INVALID', 'C27 requires a PASS C26 catalog-candidate diagnostic artifact.', $outputPath, $options, [
                'c26_artifact_type' => $c26['artifact_type'] ?? null,
                'c26_status' => $c26['status'] ?? null,
            ]);
        }
        if (($c26['candidate_readiness_summary']['c27_catalog_candidate_implementation_recommended'] ?? false) !== true) {
            return $this->blocked('WS_BT_C27_C26_DID_NOT_RECOMMEND_C27', 'C27 may only run after C26 recommends raw-OHLC-first catalog-candidate implementation.', $outputPath, $options);
        }

        $paramIds = $this->paramIds($options['param_ids'] ?? null);
        if ($paramIds === false) {
            return $this->blocked('WS_BT_C27_PARAM_IDS_INVALID', 'param-ids must be a comma-separated list of positive integers.', $outputPath, $options);
        }
        $profileCodes = $this->profileCodes($options['validation_profile_codes'] ?? ($options['profile_codes'] ?? null));
        if ($profileCodes === false) {
            return $this->blocked('WS_BT_C27_PROFILE_INVALID', 'validation-profile-codes/profile-codes must contain known C27 validation profile codes only.', $outputPath, $options);
        }
        $profileScope = 'EXPLICIT';
        if ($profileCodes === []) {
            $profileCodes = array_keys(self::validationProfiles());
            $profileScope = 'ALL_DEFAULT';
        }
        $maxProfiles = $this->positiveIntOrNull($options['max_validation_profiles'] ?? ($options['max_profiles'] ?? null));
        if ($maxProfiles !== null) {
            $profileCodes = array_slice($profileCodes, 0, $maxProfiles);
            $profileScope .= '_MAX_'.$maxProfiles;
        }

        $c21 = $this->readJson($c21InputPath);
        if ($c21 === null || ($c21['artifact_type'] ?? null) !== 'C21_ENTRY_EXIT_BEHAVIOR_DIAGNOSTIC') {
            return $this->blocked('WS_BT_C27_C21_ARTIFACT_UNREADABLE', 'C27 requires C21 canonical path levels for raw stop/target validation.', $outputPath, $options, [
                'c21_path_artifact_path' => $c21InputPath,
            ]);
        }
        $c21Rows = $this->c21RowsByPickKey($c21);

        $baseRows = $this->c26G21Rows($c26, $paramIds);
        $maxParams = $this->positiveIntOrNull($options['max_params'] ?? null);
        if ($maxParams !== null && $paramIds === []) {
            $allowed = $this->firstParamIds($baseRows, $maxParams);
            $baseRows = array_values(array_filter($baseRows, function (array $row) use ($allowed): bool {
                return isset($allowed[(int) ($row['param_id'] ?? 0)]);
            }));
            $profileScope .= '_MAX_PARAMS_'.$maxParams;
        }
        $maxPicks = $this->positiveIntOrNull($options['max_picks'] ?? null);
        if ($maxPicks !== null) {
            $baseRows = array_slice($baseRows, 0, $maxPicks);
            $profileScope .= '_MAX_PICKS_'.$maxPicks;
        }
        if ($baseRows === []) {
            return $this->blocked('WS_BT_C27_C26_G21_ROWS_EMPTY', 'C27 requires C26 G21 pick rows after filtering.', $outputPath, $options);
        }

        $calendar = $this->calendar->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked($calendar['reason_code'] ?? 'WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar is unavailable for C27 raw OHLC validation.', $outputPath, $options, [
                'calendar' => $calendar,
            ]);
        }
        $calendarDates = $this->normalizeDateList($calendar['calendar_dates'] ?? $calendar['trade_dates'] ?? []);
        if ($calendarDates === []) {
            return $this->blocked('WATCHLIST_BACKTEST_CALENDAR_UNAVAILABLE', 'Trading calendar returned no usable dates.', $outputPath, $options);
        }

        try {
            $gridRows = $this->paramGrid->allForCatalog($catalogCode, 'WS');
        } catch (\Throwable $e) {
            return $this->blocked('WS_BT_C27_SOURCE_CATALOG_UNAVAILABLE', $e->getMessage(), $outputPath, $options);
        }
        $gridByParam = [];
        foreach ($gridRows as $row) {
            $gridByParam[(int) ($row['param_id'] ?? 0)] = $row;
        }

        $requiredMap = $this->requiredPriceTickerMap($baseRows, $calendarDates, 5, true);
        $requiredDates = array_keys($requiredMap);
        $priceFromDate = $requiredDates[0] ?? $fromDate;
        $priceToDate = $requiredDates === [] ? $toDate : $requiredDates[count($requiredDates) - 1];
        $priceRead = $requiredMap === []
            ? $this->emptyPriceRead($priceFromDate, $priceToDate)
            : $this->priceSeries->readPublishedSeriesForDateTickerMap($priceFromDate, $priceToDate, $requiredMap);
        $series = is_array($priceRead['series_by_ticker'] ?? null) ? $priceRead['series_by_ticker'] : [];

        $rawPickRows = [];
        $profileRows = [];
        $total = count($baseRows);
        foreach ($baseRows as $index => $c26Row) {
            if (! empty($options['progress_callback']) && is_callable($options['progress_callback']) && ($index % 250 === 0)) {
                ($options['progress_callback'])('[C27] raw pick '.($index + 1).'/'.$total.' profiles='.count($profileCodes));
            }
            $paramId = (int) ($c26Row['param_id'] ?? 0);
            if (! isset($gridByParam[$paramId])) {
                $raw = $this->missingRawPick($c26Row, 'WS_BT_C27_PARAM_GRID_ROW_MISSING');
            } else {
                try {
                    $paramset = $this->paramsetFactory->make($gridByParam[$paramId]);
                    $raw = $this->evaluateRawPick($c26Row, $c21Rows[$this->pickKey($c26Row)] ?? null, $paramset, $series, $calendarDates);
                } catch (\Throwable $e) {
                    $raw = $this->missingRawPick($c26Row, 'WS_BT_C27_PARAMSET_INVALID', $e->getMessage());
                }
            }
            $rawPickRows[] = $raw;
            foreach ($profileCodes as $profileCode) {
                $profileRows[] = $this->profileRow($profileCode, $raw);
            }
        }

        $profileSummary = $this->profileSummary($profileRows);
        $validationSummary = $this->rawValidationSummary($rawPickRows, $priceRead);
        $paramConsistency = $this->groupStabilitySummary($rawPickRows, 'param_id', 'param_consistency');
        $monthStability = $this->groupStabilitySummary($rawPickRows, 'trade_month', 'month_stability');
        $bucketStability = $this->groupStabilitySummary($rawPickRows, 'bucket_code', 'bucket_stability');
        $lookaheadSafety = $this->lookaheadSafetySummary($rawPickRows);
        $candidateSummary = $this->candidateSummary($profileSummary, $validationSummary);
        $readiness = $this->readinessSummary($candidateSummary, $validationSummary, $paramConsistency, $monthStability, $bucketStability, $lookaheadSafety);
        $decision = $this->decision($readiness);

        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C27_RAW_OHLC_VALIDATION_READY',
            'scope' => 'IS_ONLY_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION',
            'generated_at' => (string) ($options['executed_at'] ?? gmdate('c')),
            'source_catalog' => [
                'catalog_code' => $catalogCode,
                'policy_code' => 'WS',
                'mutated' => false,
            ],
            'source_evidence' => $this->sourceEvidence($c26, $c21, $c26InputPath, $c21InputPath),
            'is_window' => ['from' => $fromDate, 'to' => $toDate],
            'price_evaluation_model' => $this->canonicalEvaluationModel(),
            'validation_profiles' => $this->profileDefinitions($profileCodes),
            'profile_scope' => $profileScope,
            'evaluated_picks_count' => count($baseRows),
            'raw_pick_rows' => $rawPickRows,
            'pick_validation_rows' => $profileRows,
            'profile_summary' => $profileSummary,
            'raw_ohlc_validation_summary' => $validationSummary,
            'param_consistency_summary' => $paramConsistency,
            'month_stability_summary' => $monthStability,
            'bucket_stability_summary' => $bucketStability,
            'lookahead_safety_summary' => $lookaheadSafety,
            'candidate_summary' => $candidateSummary,
            'candidate_readiness_summary' => $readiness,
            'decision' => $decision,
            'safety_boundaries' => $this->safetyBoundaries(),
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);

        $write = $this->writeArtifact($outputPath, $artifact);
        if (! ($write['ok'] ?? false)) {
            return $this->blocked($write['reason_code'], $write['message'], '', $options);
        }

        $g21 = $candidateSummary['raw_g21_summary'] ?? [];
        $r09 = $candidateSummary['raw_r09_summary'] ?? [];
        $g13 = $candidateSummary['raw_g13_summary'] ?? [];
        $g16 = $candidateSummary['raw_g16_summary'] ?? [];

        return [
            'status' => 'PASS',
            'reason_code' => 'WS_BT_C27_RAW_OHLC_VALIDATION_READY',
            'scope' => 'IS_ONLY_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION',
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'validation_profile_count' => count($profileCodes),
            'profile_scope' => $profileScope,
            'evaluated_picks_count' => count($baseRows),
            'raw_ohlc_validated_count' => (int) ($validationSummary['raw_ohlc_validated_count'] ?? 0),
            'raw_ohlc_missing_count' => (int) ($validationSummary['raw_ohlc_missing_count'] ?? 0),
            'raw_ohlc_validation_pass' => $readiness['raw_ohlc_validation_pass'] ? 1 : 0,
            'c21_input_artifact_hash' => $c21['artifact_hash'] ?? null,
            'c26_input_artifact_hash' => $c26['artifact_hash'] ?? null,
            'raw_r09_avg_ret_net' => $r09['avg_ret_net'] ?? null,
            'raw_r09_median_ret_net' => $r09['median_ret_net'] ?? null,
            'raw_r09_p25_ret_net' => $r09['p25_ret_net'] ?? null,
            'raw_g21_avg_ret_net' => $g21['avg_ret_net'] ?? null,
            'raw_g21_median_ret_net' => $g21['median_ret_net'] ?? null,
            'raw_g21_p25_ret_net' => $g21['p25_ret_net'] ?? null,
            'raw_g13_avg_ret_net' => $g13['avg_ret_net'] ?? null,
            'raw_g16_avg_ret_net' => $g16['avg_ret_net'] ?? null,
            'g21_raw_beats_r09' => $readiness['g21_raw_beats_r09'] ? 1 : 0,
            'g21_raw_catalog_candidate_ready' => $readiness['g21_raw_catalog_candidate_ready'] ? 1 : 0,
            'c28_oos_proof_recommended' => $readiness['c28_oos_proof_recommended'] ? 1 : 0,
            'c27_catalog_implementation_deferred' => 1,
            'c27_catalog_code' => 'NOT_CREATED',
            'derived_mfe_mae_used_for_execution' => 0,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }

    private function evaluateRawPick(array $c26Row, ?array $c21Row, array $paramset, array $series, array $calendarDates): array
    {
        $ticker = strtoupper(trim((string) ($c26Row['ticker'] ?? '')));
        $tradeDate = (string) ($c26Row['trade_date'] ?? '');
        $entryDate = (string) ($c26Row['entry_date'] ?? '');
        if ($entryDate === '') {
            $entryDate = (string) ($this->nextTradingDate($tradeDate, $calendarDates) ?? '');
        }
        $pathDates = $entryDate !== '' ? $this->tradingWindowFrom($entryDate, $calendarDates, 5) : [];
        $signalBar = $this->publishedBar($series, $ticker, $tradeDate);

        $base = $this->rawBase($c26Row);
        if ($ticker === '' || $tradeDate === '' || $entryDate === '' || $signalBar === null || count($pathDates) < 5) {
            return array_merge($base, [
                'missing_path_data_flag' => true,
                'missing_path_reason_code' => 'WS_BT_C27_REQUIRED_SIGNAL_OR_D1_TO_D5_PATH_MISSING',
            ]);
        }

        $pathBars = [];
        foreach ($pathDates as $offset => $date) {
            $bar = $this->publishedBar($series, $ticker, $date);
            if ($bar === null || ! $this->tradableBar($bar, $paramset)) {
                return array_merge($base, [
                    'entry_date' => $entryDate,
                    'missing_path_data_flag' => true,
                    'missing_path_reason_code' => 'WS_BT_C27_D1_TO_D5_RAW_OHLC_PATH_MISSING',
                ]);
            }
            foreach (['open', 'high', 'low', 'close'] as $field) {
                $value = $this->num($bar[$field] ?? null);
                if ($value === null || $value <= 0) {
                    return array_merge($base, [
                        'entry_date' => $entryDate,
                        'missing_path_data_flag' => true,
                        'missing_path_reason_code' => 'WS_BT_C27_RAW_OHLC_NON_EXECUTABLE',
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
            return array_merge($base, [
                'entry_date' => $entryDate,
                'missing_path_data_flag' => true,
                'missing_path_reason_code' => 'WS_BT_C27_SIGNAL_CLOSE_OR_ENTRY_OPEN_MISSING',
            ]);
        }

        $levels = $this->levelsFromC21($c21Row, $entryPrice);
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

        $entryPriceSource = $this->num($c26Row['entry_price'] ?? null);
        $entryDiff = $entryPriceSource !== null ? abs($entryPrice - $entryPriceSource) : null;
        $raw = array_merge($base, [
            'entry_date' => $entryDate,
            'raw_entry_price' => $entryPrice,
            'c26_entry_price' => $entryPriceSource,
            'entry_price_match_flag' => $entryDiff !== null && $entryDiff <= 0.000001,
            'signal_close_price' => $signalClose,
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
            'target_1pct_hit_day' => $target1['exit_day_offset'] ?? null,
            'no_signal_d3_open_flag' => $target1 === null && $this->noProfitByDay($entryPrice, $pathBars, 2),
            'r09_fallback_flag' => $target1 === null && ! $this->noProfitByDay($entryPrice, $pathBars, 2),
            'raw_vs_c25_g21_delta' => $this->delta($g21['ret_net'] ?? null, $c26Row['c25_g21_ret_net'] ?? null),
            'raw_vs_c25_g13_delta' => $this->delta($g13['ret_net'] ?? null, $c26Row['c25_g13_ret_net'] ?? null),
            'raw_vs_c25_g16_delta' => $this->delta($g16['ret_net'] ?? null, $c26Row['c25_g16_ret_net'] ?? null),
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

    private function rawBase(array $c26Row): array
    {
        return [
            'trade_date' => $c26Row['trade_date'] ?? null,
            'trade_month' => substr((string) ($c26Row['trade_date'] ?? ''), 0, 7),
            'ticker_id' => $c26Row['ticker_id'] ?? null,
            'ticker' => $c26Row['ticker'] ?? null,
            'param_id' => $c26Row['param_id'] ?? null,
            'row_code' => $c26Row['row_code'] ?? null,
            'entry_date' => $c26Row['entry_date'] ?? null,
            'bucket_code' => $c26Row['bucket_code'] ?? null,
            'bucket_reason' => $c26Row['bucket_reason'] ?? null,
            'c25_g21_ret_net' => $this->num($c26Row['c25_g21_ret_net'] ?? null),
            'c25_g13_ret_net' => $this->num($c26Row['c25_g13_ret_net'] ?? null),
            'c25_g16_ret_net' => $this->num($c26Row['c25_g16_ret_net'] ?? null),
            'c23_r09_ret_net' => $this->num($c26Row['c23_r09_ret_net'] ?? null),
            'canonical_ret_net_source' => $this->num($c26Row['canonical_ret_net'] ?? null),
            'raw_ohlc_validated_flag' => false,
            'missing_path_data_flag' => true,
            'missing_path_reason_code' => null,
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
        ];
    }

    private function missingRawPick(array $c26Row, string $reasonCode, string $message = ''): array
    {
        return array_merge($this->rawBase($c26Row), [
            'missing_path_data_flag' => true,
            'missing_path_reason_code' => $reasonCode,
            'missing_path_message' => $message,
        ]);
    }

    private function canonicalExit(float $entryPrice, array $pathBars, array $levels, array $paramset): array
    {
        if (($levels['has_target_stop'] ?? false) !== true) {
            return $this->missingExit('WS_BT_C27_CANONICAL_STOP_TARGET_LEVELS_MISSING');
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
                return $this->missingExit('WS_BT_C27_R09_SIGNAL_PATH_MISSING');
            }
            $signalReturn = ($pathBars[$day]['close'] - $entryPrice) / $entryPrice;
            if ($signalReturn > 0) {
                $exitOffset = $day + 1;
                if (! isset($pathBars[$exitOffset])) {
                    return $this->missingExit('WS_BT_C27_R09_NEXT_OPEN_AFTER_SIGNAL_MISSING');
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
            return $this->missingExit('WS_BT_C27_DAMAGE_CONTROL_NEXT_OPEN_MISSING');
        }
        $exit = $this->exitPayload(
            $pathBars[$exitOffset]['date'],
            $pathBars[$exitOffset]['open'],
            $exitOffset,
            'raw_damage_control_no_profit_d'.$noProfitByDay.'_exit_d'.$exitOffset.'_open',
            $noProfitByDay,
            $pathBars[$noProfitByDay]['date'],
            'no_profit_by_close',
            $exitOffset > $noProfitByDay
        );
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
            'lookahead_violation_reason' => $lookaheadSafe ? null : 'WS_BT_C27_RULE_EXIT_NOT_AFTER_SIGNAL_CLOSE',
            'missing_path_data_flag' => false,
            'missing_path_reason_code' => null,
            'intraday_sequence_known' => true,
            'ambiguous_intraday_sequence_flag' => false,
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
            'intraday_sequence_known' => false,
            'ambiguous_intraday_sequence_flag' => false,
            'conservative_fill_policy' => 'OPEN_GAP_THEN_HIGH_LOW_STOP_FIRST_IF_BOTH_SAME_DAILY_CANDLE',
        ];
    }

    private function profileRow(string $profileCode, array $raw): array
    {
        $profiles = self::validationProfiles();
        $profile = $profiles[$profileCode] ?? $profiles['C27_G05_RAW_C25_G21_PRIMARY_COMBO'];
        $source = (string) ($profile['source'] ?? 'g21');
        $exit = is_array($raw[$source] ?? null) ? $raw[$source] : $this->missingExit('WS_BT_C27_PROFILE_SOURCE_MISSING');
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
            'signal_close_price' => $raw['signal_close_price'] ?? null,
            'bucket_code' => $raw['bucket_code'] ?? null,
            'profile_code' => $profileCode,
            'profile_family' => $profile['family'] ?? null,
            'candidate_role' => $profile['candidate_role'] ?? null,
            'profile_exit_date' => $exit['exit_date'] ?? null,
            'profile_exit_price' => $exit['exit_price'] ?? null,
            'profile_exit_day_offset' => $exit['exit_day_offset'] ?? null,
            'profile_exit_reason' => $exit['exit_reason'] ?? null,
            'profile_ret_net' => $ret,
            'raw_canonical_ret_net' => $this->num($raw['canonical']['ret_net'] ?? null),
            'raw_r09_ret_net' => $this->num($raw['r09']['ret_net'] ?? null),
            'raw_g13_ret_net' => $this->num($raw['g13']['ret_net'] ?? null),
            'raw_g16_ret_net' => $this->num($raw['g16']['ret_net'] ?? null),
            'raw_g21_ret_net' => $this->num($raw['g21']['ret_net'] ?? null),
            'c25_g13_ret_net' => $raw['c25_g13_ret_net'] ?? null,
            'c25_g16_ret_net' => $raw['c25_g16_ret_net'] ?? null,
            'c25_g21_ret_net' => $raw['c25_g21_ret_net'] ?? null,
            'delta_vs_raw_canonical' => $this->delta($ret, $raw['canonical']['ret_net'] ?? null),
            'delta_vs_raw_r09' => $this->delta($ret, $raw['r09']['ret_net'] ?? null),
            'delta_vs_raw_g13' => $this->delta($ret, $raw['g13']['ret_net'] ?? null),
            'delta_vs_raw_g16' => $this->delta($ret, $raw['g16']['ret_net'] ?? null),
            'delta_vs_raw_g21' => $this->delta($ret, $raw['g21']['ret_net'] ?? null),
            'delta_vs_c25_g13' => $this->delta($ret, $raw['c25_g13_ret_net'] ?? null),
            'delta_vs_c25_g16' => $this->delta($ret, $raw['c25_g16_ret_net'] ?? null),
            'delta_vs_c25_g21' => $this->delta($ret, $raw['c25_g21_ret_net'] ?? null),
            'target_1pct_hit_flag' => (bool) ($raw['target_1pct_hit_flag'] ?? false),
            'target_1pct_hit_day' => $raw['target_1pct_hit_day'] ?? null,
            'no_signal_d3_open_flag' => (bool) ($raw['no_signal_d3_open_flag'] ?? false),
            'r09_fallback_flag' => (bool) ($raw['r09_fallback_flag'] ?? false),
            'raw_ohlc_validated_flag' => (bool) ($raw['raw_ohlc_validated_flag'] ?? false),
            'lookahead_safe' => (bool) ($exit['lookahead_safe'] ?? false),
            'lookahead_violation_reason' => $exit['lookahead_violation_reason'] ?? null,
            'intraday_sequence_known' => (bool) ($exit['intraday_sequence_known'] ?? false),
            'ambiguous_intraday_sequence_flag' => (bool) ($exit['ambiguous_intraday_sequence_flag'] ?? false),
            'conservative_fill_policy' => $exit['conservative_fill_policy'] ?? null,
            'missing_path_data_flag' => (bool) ($raw['missing_path_data_flag'] ?? true) || (bool) ($exit['missing_path_data_flag'] ?? false),
            'missing_path_reason_code' => $raw['missing_path_reason_code'] ?? ($exit['missing_path_reason_code'] ?? null),
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'oos_executed' => false,
            'production_ready' => 0,
        ];
    }

    private function c26G21Rows(array $c26, array $paramIds): array
    {
        $allowed = $paramIds === [] ? [] : array_fill_keys($paramIds, true);
        $rows = [];
        $seen = [];
        foreach (($c26['pick_diagnostic_rows'] ?? []) as $row) {
            if (! is_array($row) || ($row['profile_code'] ?? null) !== self::C26_G21) {
                continue;
            }
            $paramId = (int) ($row['param_id'] ?? 0);
            if ($allowed !== [] && ! isset($allowed[$paramId])) {
                continue;
            }
            $key = $this->pickKey($row);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $rows[] = $row;
        }
        usort($rows, function (array $left, array $right): int {
            foreach (['trade_date', 'param_id', 'ticker'] as $key) {
                $cmp = strcmp((string) ($left[$key] ?? ''), (string) ($right[$key] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            return 0;
        });
        return $rows;
    }

    private function c21RowsByPickKey(array $c21): array
    {
        $rows = [];
        foreach (($c21['pick_path_rows'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $profile = (string) ($row['diagnostic_profile_code'] ?? 'C21_P00_CANONICAL_PATH_BASELINE');
            if ($profile !== 'C21_P00_CANONICAL_PATH_BASELINE') {
                continue;
            }
            $rows[$this->pickKey($row)] = $row;
        }
        return $rows;
    }

    private function levelsFromC21(?array $row, float $entryPrice): array
    {
        if (! is_array($row)) {
            return ['has_target_stop' => false];
        }
        $stop = $this->num($row['stop_trigger_price'] ?? null);
        $target = $this->num($row['target_trigger_price'] ?? null);
        $has = $stop !== null && $target !== null && $stop > 0 && $target > 0 && $stop < $entryPrice && $target > $entryPrice;
        return [
            'has_target_stop' => $has,
            'stop_trigger_price' => $has ? $stop : null,
            'target_trigger_price' => $has ? $target : null,
            'source' => 'C21_CANONICAL_LEVELS_RAW_OHLC_REVALIDATED_BY_C27',
        ];
    }

    private function rawValidationSummary(array $rows, array $priceRead): array
    {
        $validated = array_values(array_filter($rows, function (array $row): bool {
            return ($row['raw_ohlc_validated_flag'] ?? false) === true;
        }));
        $missing = count($rows) - count($validated);
        $matches = 0;
        $mismatches = 0;
        $targetHits = 0;
        $d3 = 0;
        $r09Fallback = 0;
        $entryMatches = 0;
        foreach ($validated as $row) {
            $delta = $this->num($row['raw_vs_c25_g21_delta'] ?? null);
            if ($delta !== null && abs($delta) <= 0.000001) {
                $matches++;
            } elseif ($delta !== null) {
                $mismatches++;
            }
            if (($row['target_1pct_hit_flag'] ?? false) === true) {
                $targetHits++;
            }
            if (($row['no_signal_d3_open_flag'] ?? false) === true) {
                $d3++;
            }
            if (($row['r09_fallback_flag'] ?? false) === true) {
                $r09Fallback++;
            }
            if (($row['entry_price_match_flag'] ?? false) === true) {
                $entryMatches++;
            }
        }

        return [
            'evaluated_picks_count' => count($rows),
            'raw_ohlc_validated_count' => count($validated),
            'raw_ohlc_missing_count' => $missing,
            'raw_ohlc_available_rate' => count($rows) > 0 ? count($validated) / count($rows) : null,
            'raw_ohlc_validation_pass' => count($rows) > 0 && $missing === 0,
            'entry_price_match_count' => $entryMatches,
            'entry_price_mismatch_count' => count($validated) - $entryMatches,
            'g21_raw_c25_exact_match_count' => $matches,
            'g21_raw_c25_mismatch_count' => $mismatches,
            'g21_raw_c25_exact_match_rate' => count($validated) > 0 ? $matches / count($validated) : null,
            'target_1pct_hit_count' => $targetHits,
            'target_1pct_hit_rate' => count($validated) > 0 ? $targetHits / count($validated) : null,
            'no_signal_d3_open_count' => $d3,
            'r09_fallback_count' => $r09Fallback,
            'derived_mfe_mae_used_for_execution' => false,
            'raw_high_low_used_for_execution' => true,
            'price_readiness' => [
                'is_ready' => (bool) ($priceRead['is_ready'] ?? false),
                'reason_code' => $priceRead['reason_code'] ?? null,
                'required_price_date_count' => $priceRead['price_series_manifest']['required_price_date_count'] ?? null,
                'requested_ticker_date_pair_count' => $priceRead['price_series_manifest']['requested_ticker_date_pair_count'] ?? null,
                'missing_price_dates' => $priceRead['price_series_manifest']['missing_price_dates'] ?? [],
                'missing_price_rows_count' => count($priceRead['price_series_manifest']['missing_price_rows'] ?? []),
                'exact_date_resolution_only' => $priceRead['price_series_manifest']['exact_date_resolution_only'] ?? true,
                'no_latest_fallback' => $priceRead['price_series_manifest']['no_latest_fallback'] ?? true,
            ],
        ];
    }

    private function profileSummary(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $groups[(string) ($row['profile_code'] ?? 'UNKNOWN')][] = $row;
        }
        ksort($groups, SORT_STRING);
        $out = [];
        foreach ($groups as $profileCode => $profileRows) {
            $returns = $this->values($profileRows, 'profile_ret_net');
            $wins = count(array_filter($returns, function (float $value): bool {
                return $value > 0;
            }));
            $lookahead = count(array_filter($profileRows, function (array $row): bool {
                return ($row['lookahead_safe'] ?? true) !== true;
            }));
            $missing = count(array_filter($profileRows, function (array $row): bool {
                return ($row['missing_path_data_flag'] ?? false) === true;
            }));
            $profile = self::validationProfiles()[$profileCode] ?? [];
            $out[$profileCode] = [
                'profile_code' => $profileCode,
                'profile_family' => $profile['family'] ?? null,
                'candidate_role' => $profile['candidate_role'] ?? null,
                'evaluated_picks_count' => count($profileRows),
                'valid_ret_count' => count($returns),
                'missing_path_count' => $missing,
                'avg_ret_net' => $this->avg($returns),
                'median_ret_net' => $this->median($returns),
                'p25_ret_net' => $this->percentile($returns, 0.25),
                'win_rate' => count($returns) > 0 ? $wins / count($returns) : null,
                'avg_delta_vs_raw_r09' => $this->avg($this->values($profileRows, 'delta_vs_raw_r09')),
                'median_delta_vs_raw_r09' => $this->median($this->values($profileRows, 'delta_vs_raw_r09')),
                'p25_delta_vs_raw_r09' => $this->percentile($this->values($profileRows, 'delta_vs_raw_r09'), 0.25),
                'avg_delta_vs_c25_g21' => $this->avg($this->values($profileRows, 'delta_vs_c25_g21')),
                'lookahead_violation_count' => $lookahead,
                'raw_ohlc_validated_count' => count(array_filter($profileRows, function (array $row): bool {
                    return ($row['raw_ohlc_validated_flag'] ?? false) === true;
                })),
                'ambiguous_intraday_sequence_count' => count(array_filter($profileRows, function (array $row): bool {
                    return ($row['ambiguous_intraday_sequence_flag'] ?? false) === true;
                })),
            ];
        }
        return $out;
    }

    private function candidateSummary(array $profiles, array $validation): array
    {
        $g21 = $profiles['C27_G05_RAW_C25_G21_PRIMARY_COMBO'] ?? [];
        $r09 = $profiles['C27_G02_RAW_C23_R09_NEXT_OPEN_RULE'] ?? [];
        $g13 = $profiles['C27_G03_RAW_C25_G13_TARGET_0_50PCT'] ?? [];
        $g16 = $profiles['C27_G04_RAW_C25_G16_TARGET_1_50PCT'] ?? [];

        return [
            'raw_r09_summary' => $r09,
            'raw_g21_summary' => $g21,
            'raw_g13_summary' => $g13,
            'raw_g16_summary' => $g16,
            'g21_avg_delta_vs_raw_r09' => $this->delta($g21['avg_ret_net'] ?? null, $r09['avg_ret_net'] ?? null),
            'g21_median_delta_vs_raw_r09' => $this->delta($g21['median_ret_net'] ?? null, $r09['median_ret_net'] ?? null),
            'g21_p25_delta_vs_raw_r09' => $this->delta($g21['p25_ret_net'] ?? null, $r09['p25_ret_net'] ?? null),
            'g21_win_rate_delta_vs_raw_r09' => $this->delta($g21['win_rate'] ?? null, $r09['win_rate'] ?? null),
            'g21_avg_delta_vs_g13' => $this->delta($g21['avg_ret_net'] ?? null, $g13['avg_ret_net'] ?? null),
            'g21_avg_delta_vs_g16' => $this->delta($g21['avg_ret_net'] ?? null, $g16['avg_ret_net'] ?? null),
            'raw_ohlc_validation_pass' => (bool) ($validation['raw_ohlc_validation_pass'] ?? false),
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function groupStabilitySummary(array $rows, string $key, string $label): array
    {
        $groups = [];
        foreach ($rows as $row) {
            if (($row['raw_ohlc_validated_flag'] ?? false) !== true) {
                continue;
            }
            $groupKey = (string) ($row[$key] ?? 'UNKNOWN');
            $groups[$groupKey][] = $row;
        }
        ksort($groups, SORT_STRING);

        $items = [];
        $pass = 0;
        $fail = 0;
        foreach ($groups as $groupKey => $groupRows) {
            $g21 = $this->valuesFromExit($groupRows, 'g21');
            $r09 = $this->valuesFromExit($groupRows, 'r09');
            $avgDelta = $this->delta($this->avg($g21), $this->avg($r09));
            $p25Delta = $this->delta($this->percentile($g21, 0.25), $this->percentile($r09, 0.25));
            $ok = $avgDelta !== null && $p25Delta !== null && $avgDelta >= -0.000001 && $p25Delta >= -0.000001;
            $ok ? $pass++ : $fail++;
            $items[] = [
                $label.'_key' => $groupKey,
                'count' => count($groupRows),
                'raw_g21_avg_ret_net' => $this->avg($g21),
                'raw_r09_avg_ret_net' => $this->avg($r09),
                'raw_g21_p25_ret_net' => $this->percentile($g21, 0.25),
                'raw_r09_p25_ret_net' => $this->percentile($r09, 0.25),
                'avg_delta_vs_raw_r09' => $avgDelta,
                'p25_delta_vs_raw_r09' => $p25Delta,
                'pass_flag' => $ok,
            ];
        }

        return [
            $label.'_pass_count' => $pass,
            $label.'_fail_count' => $fail,
            $label.'_rows' => $items,
        ];
    }

    private function lookaheadSafetySummary(array $rows): array
    {
        $violations = 0;
        $ambiguous = 0;
        foreach ($rows as $row) {
            if (($row['lookahead_safe'] ?? true) !== true) {
                $violations++;
            }
            foreach (['g13', 'g16', 'g21'] as $source) {
                if (($row[$source]['ambiguous_intraday_sequence_flag'] ?? false) === true) {
                    $ambiguous++;
                }
            }
        }
        return [
            'lookahead_violation_count' => $violations,
            'ambiguous_intraday_sequence_count' => $ambiguous,
            'lookahead_safe' => $violations === 0,
            'preplanned_order_threshold_fixed_before_path_evaluation' => true,
            'close_signal_same_day_exit_allowed' => false,
            'close_signal_next_open_rule_checks' => [
                'd1_close_signal_min_exit_day' => 2,
                'd2_close_signal_min_exit_day' => 3,
                'd3_close_signal_min_exit_day' => 4,
            ],
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'derived_mfe_mae_used_for_execution' => false,
        ];
    }

    private function readinessSummary(array $candidate, array $validation, array $param, array $month, array $bucket, array $lookahead): array
    {
        $avgDelta = $this->num($candidate['g21_avg_delta_vs_raw_r09'] ?? null);
        $p25Delta = $this->num($candidate['g21_p25_delta_vs_raw_r09'] ?? null);
        $medianDelta = $this->num($candidate['g21_median_delta_vs_raw_r09'] ?? null);
        $rawPass = (bool) ($validation['raw_ohlc_validation_pass'] ?? false);
        $lookaheadPass = (int) ($lookahead['lookahead_violation_count'] ?? 0) === 0;
        $beatsR09 = $avgDelta !== null && $p25Delta !== null && $medianDelta !== null
            && $avgDelta >= -0.000001
            && $p25Delta >= -0.000001
            && $medianDelta >= -0.000001;
        $paramOk = (int) ($param['param_consistency_pass_count'] ?? 0) > 0
            && (int) ($param['param_consistency_fail_count'] ?? 0) <= (int) ($param['param_consistency_pass_count'] ?? 0);
        $monthOk = (int) ($month['month_stability_pass_count'] ?? 0) > 0
            && (int) ($month['month_stability_fail_count'] ?? 0) <= (int) ($month['month_stability_pass_count'] ?? 0);
        $bucketOk = (int) ($bucket['bucket_stability_pass_count'] ?? 0) > 0
            && (int) ($bucket['bucket_stability_fail_count'] ?? 0) === 0;
        $ready = $rawPass && $lookaheadPass && $beatsR09 && $paramOk && $monthOk && $bucketOk;

        $failures = [];
        if (! $rawPass) {
            $failures[] = 'RAW_OHLC_VALIDATION_INCOMPLETE';
        }
        if (! $lookaheadPass) {
            $failures[] = 'LOOKAHEAD_SAFETY_FAILED';
        }
        if (! $beatsR09) {
            $failures[] = 'G21_RAW_DISTRIBUTION_DOES_NOT_BEAT_R09';
        }
        if (! $paramOk) {
            $failures[] = 'G21_PARAM_CONSISTENCY_WEAK';
        }
        if (! $monthOk) {
            $failures[] = 'G21_MONTH_STABILITY_WEAK';
        }
        if (! $bucketOk) {
            $failures[] = 'G21_BUCKET_STABILITY_WEAK';
        }

        return [
            'raw_ohlc_validation_pass' => $rawPass,
            'derived_mfe_mae_dependency_removed' => true,
            'g21_raw_beats_r09' => $beatsR09,
            'g21_raw_catalog_candidate_ready' => $ready,
            'g21_failure_reason_codes' => $failures,
            'c28_oos_proof_recommended' => $ready,
            'c28_requires_is_artifact_hash_lock' => $ready,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'production_ready' => 0,
        ];
    }

    private function decision(array $readiness): array
    {
        $ready = (bool) ($readiness['g21_raw_catalog_candidate_ready'] ?? false);
        $rawPass = (bool) ($readiness['raw_ohlc_validation_pass'] ?? false);
        $status = $ready
            ? 'C27_RAW_OHLC_VALIDATED_CATALOG_CANDIDATE_READY_FOR_C28_OOS_PROOF'
            : ($rawPass ? 'C27_RAW_OHLC_VALIDATED_BUT_CANDIDATE_NOT_READY' : 'C27_RAW_OHLC_VALIDATION_INCOMPLETE');

        return [
            'decision_status' => $status,
            'raw_ohlc_validation_pass' => $rawPass,
            'g21_raw_catalog_candidate_ready' => $ready,
            'c28_oos_proof_recommended' => (bool) ($readiness['c28_oos_proof_recommended'] ?? false),
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'production_ready' => 0,
            'next_step' => $ready
                ? 'C28 may run OOS proof against the C27 raw-OHLC-validated IS artifact; C27 itself does not create a production catalog.'
                : 'Keep C27 as IS-only raw OHLC validation evidence and revisit the rule or missing raw path coverage before OOS.',
        ];
    }

    private function sourceEvidence(array $c26, array $c21, string $c26Path, string $c21Path): array
    {
        return [
            'c19_final_status' => 'C19_CATALOG_CANDIDATE_FAILED',
            'c20_final_status' => 'C20_DATE_GATE_NOT_ENOUGH',
            'c21_final_status' => 'C21_EXECUTION_SIGNAL_FOUND',
            'c22_final_status' => 'C22_EXIT_CAPTURE_SIGNAL_FOUND',
            'c23_final_status' => 'C23_NON_LOOKAHEAD_RULE_CANDIDATE_FOUND_BUT_SHADOW_GAP_NOT_ACCEPTABLE',
            'c24_final_status' => 'C24_GAP_BRIDGE_EXPLAINED',
            'c25_final_status' => 'C25_C26_CATALOG_CANDIDATE_DIAGNOSTIC_RECOMMENDED',
            'c26_final_status' => 'C26_RAW_OHLC_VALIDATION_REQUIRED',
            'c21_path_artifact_path' => $c21Path,
            'c21_path_artifact_hash' => $c21['artifact_hash'] ?? null,
            'c26_all_param_artifact_path' => $c26Path,
            'c26_all_param_artifact_hash' => $c26['artifact_hash'] ?? null,
        ];
    }

    private function safetyBoundaries(): array
    {
        return [
            'IS_ONLY' => true,
            'OOS_NOT_RUN' => true,
            'production_ready' => 0,
            'C27_CATALOG_CODE' => 'NOT_CREATED',
            'C27_CATALOG_IMPLEMENTATION_DEFERRED' => true,
            'NO_PROMOTION' => true,
            'NO_OOS' => true,
            'NO_C01_TO_C26_MUTATION' => true,
            'NO_C19_REOPEN' => true,
            'NO_C20_REOPEN' => true,
            'NO_C21_REOPEN' => true,
            'NO_C22_REOPEN' => true,
            'NO_C23_REOPEN' => true,
            'NO_C24_REOPEN' => true,
            'NO_C25_REOPEN' => true,
            'NO_C26_REOPEN' => true,
            'catalog_allowed' => false,
            'oos_allowed' => false,
            'canonical_model_unchanged' => $this->canonicalEvaluationModel(),
            'raw_ohlc_used_for_execution' => true,
            'derived_mfe_mae_used_for_execution' => false,
            'future_path_price_used_for_selection' => false,
            'profile_ret_net_used_for_selection' => false,
            'preplanned_order_threshold_fixed_before_path_evaluation' => true,
            'conservative_fill_policy' => 'OPEN_GAP_THEN_HIGH_LOW_STOP_FIRST_IF_BOTH_SAME_DAILY_CANDLE',
        ];
    }

    private function canonicalEvaluationModel(): array
    {
        return [
            'ENTRY' => 'NEXT_OPEN',
            'EXIT' => 'STOP_TP_OR_TIME',
            'HOLD' => 5,
            'FEE' => 'IDR_FIXED',
            'SLIP' => 0,
            'GAP' => 'OPEN',
            'PX' => 'IDX_BANDS',
            'canonical_model_changed' => false,
            'raw_ohlc_validation_first' => true,
            'catalog_candidate_implementation_only' => true,
        ];
    }

    private function requiredPriceTickerMap(array $rows, array $calendarDates, int $holdingDays, bool $includeSignalDate): array
    {
        $map = [];
        foreach ($rows as $row) {
            $ticker = strtoupper(trim((string) ($row['ticker'] ?? '')));
            $tradeDate = (string) ($row['trade_date'] ?? '');
            $entryDate = (string) ($row['entry_date'] ?? '');
            if ($entryDate === '') {
                $entryDate = (string) ($this->nextTradingDate($tradeDate, $calendarDates) ?? '');
            }
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

    private function normalizeDateList(array $dates): array
    {
        $out = [];
        foreach ($dates as $date) {
            $value = trim((string) $date);
            if ($value !== '') {
                $out[$value] = $value;
            }
        }
        ksort($out, SORT_STRING);
        return array_values($out);
    }

    private function firstParamIds(array $rows, int $max): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['param_id'] ?? 0);
            if ($id > 0) {
                $ids[$id] = true;
            }
            if (count($ids) >= $max) {
                break;
            }
        }
        return $ids;
    }

    private function profileDefinitions(array $profileCodes): array
    {
        $profiles = self::validationProfiles();
        $out = [];
        foreach ($profileCodes as $code) {
            $out[] = array_merge(['profile_code' => $code], $profiles[$code] ?? []);
        }
        return $out;
    }

    private function valuesFromExit(array $rows, string $source): array
    {
        $values = [];
        foreach ($rows as $row) {
            if (is_numeric($row[$source]['ret_net'] ?? null)) {
                $values[] = (float) $row[$source]['ret_net'];
            }
        }
        return $values;
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

    private function delta($left, $right): ?float
    {
        $left = $this->num($left);
        $right = $this->num($right);
        return $left !== null && $right !== null ? $left - $right : null;
    }

    private function num($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }

    private function pickKey(array $row): string
    {
        return implode('|', [
            (string) ($row['trade_date'] ?? ''),
            (string) ($row['ticker'] ?? ''),
            (string) ($row['param_id'] ?? ''),
        ]);
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

    private function profileCodes($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (! is_array($value)) {
            return false;
        }
        $known = self::validationProfiles();
        $codes = [];
        foreach ($value as $item) {
            $code = strtoupper(trim((string) $item));
            if ($code === '' || ! isset($known[$code])) {
                return false;
            }
            $codes[] = $code;
        }
        return array_values(array_unique($codes));
    }

    private function paramIds($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (is_string($value)) {
            $value = preg_split('/\s*,\s*/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }
        if (! is_array($value)) {
            return false;
        }
        $ids = [];
        foreach ($value as $item) {
            if (! is_numeric($item) || (int) $item <= 0) {
                return false;
            }
            $ids[] = (int) $item;
        }
        return array_values(array_unique($ids));
    }

    private function positiveIntOrNull($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value) || (int) $value <= 0) {
            return null;
        }
        return (int) $value;
    }

    private function readJson(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    private function writeArtifact(string $path, array $artifact): array
    {
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to create artifact directory.'];
        }
        $json = json_encode($artifact, JSON_UNESCAPED_SLASHES);
        if ($json === false || file_put_contents($path, $json."\n") === false) {
            return ['ok' => false, 'reason_code' => 'WATCHLIST_BACKTEST_ARTIFACT_WRITE_FAILED', 'message' => 'Unable to write C27 artifact.'];
        }
        return ['ok' => true];
    }

    private function stableHash(array $payload): string
    {
        unset($payload['artifact_hash']);
        return sha1(json_encode($payload, JSON_UNESCAPED_SLASHES));
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

    private function blocked(string $reasonCode, string $message, string $outputPath = '', array $options = [], array $extra = []): array
    {
        $artifact = [
            'artifact_type' => self::ARTIFACT_TYPE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'scope' => 'IS_ONLY_CATALOG_CANDIDATE_RAW_OHLC_VALIDATION',
            'generated_at' => (string) ($options['executed_at'] ?? gmdate('c')),
            'decision' => [
                'decision_status' => 'C27_RAW_OHLC_VALIDATION_BLOCKED',
                'catalog_allowed' => false,
                'oos_allowed' => false,
                'production_ready' => 0,
            ],
            'safety_boundaries' => $this->safetyBoundaries(),
            'extra' => $extra,
        ];
        $artifact['artifact_hash'] = $this->stableHash($artifact);
        if ($outputPath !== '') {
            $this->writeArtifact($outputPath, $artifact);
        }
        return [
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'message' => $message,
            'artifact_path' => $outputPath,
            'artifact_hash' => $artifact['artifact_hash'],
            'c27_catalog_implementation_deferred' => 1,
            'c27_catalog_code' => 'NOT_CREATED',
            'derived_mfe_mae_used_for_execution' => 0,
            'oos_service_invoked' => 0,
            'oos_repository_invoked' => 0,
            'oos_executed' => 0,
            'production_ready' => 0,
        ];
    }
}
