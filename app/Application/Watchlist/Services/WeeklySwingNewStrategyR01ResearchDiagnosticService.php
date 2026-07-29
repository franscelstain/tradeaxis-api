<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOfficialEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingNewStrategyR01ResearchDiagnosticService
{
    public const RUN_CODE = 'WS_NEW_STRATEGY_R01_RESEARCH_HYPOTHESIS_AND_DIAGNOSTIC_EVIDENCE';
    public const SUCCESS_STATUS = 'WS_NEW_STRATEGY_R01_DIAGNOSTIC_COMPLETED';
    public const APPROVAL_REFERENCE = 'WS_NEW_STRATEGY_R01_OPERATOR_APPROVED_READ_ONLY_RESEARCH';
    public const CANONICAL_IS_FROM = '2023-01-02';
    public const CANONICAL_IS_TO = '2025-05-21';
    public const SOURCE_EVAL_ID = 204;
    public const SOURCE_PARAM_SET_ID = 11;
    public const SOURCE_PARAM_ID = 166;
    public const SOURCE_PARAMSET_HASH = 'c93bae2b761028d6b236f368d5b19bb4f498715a';
    public const SOURCE_EVIDENCE_MANIFEST_HASH = '604bfbe9698fbb8ec3c74e3fa6e10f9335f66d1d';
    public const MAX_HYPOTHESES = 3;
    private const HOLDING_DAYS = 5;

    private MarketDataTradingCalendarReadService $calendar;
    private MarketDataPublishedEodSeriesReadService $prices;
    private WatchlistBacktestMetricsService $metrics;
    private WatchlistBacktestOfficialEvidenceRepository $officialEvidence;
    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetRuntimeAdapter $runtimeAdapter;
    private WeeklySwingBacktestEvidenceIdentityService $identity;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        MarketDataPublishedEodSeriesReadService $prices = null,
        WatchlistBacktestMetricsService $metrics = null,
        WatchlistBacktestOfficialEvidenceRepository $officialEvidence = null,
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetRuntimeAdapter $runtimeAdapter = null,
        WeeklySwingBacktestEvidenceIdentityService $identity = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->prices = $prices ?: new MarketDataPublishedEodSeriesReadService();
        $this->metrics = $metrics ?: new WatchlistBacktestMetricsService();
        $this->officialEvidence = $officialEvidence ?: new WatchlistBacktestOfficialEvidenceRepository();
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->runtimeAdapter = $runtimeAdapter ?: new WeeklySwingParamsetRuntimeAdapter();
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
    }

    public function execute(
        string $approvalReference,
        bool $operatorApproved,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('WS_NEW_STRATEGY_R01_OPERATOR_APPROVAL_MISSING');
        }

        foreach ([
            'watchlist_bt_eval',
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_bt_cutoffs_ws',
            'watchlist_param_sets',
            'watchlist_plan_runs',
            'eod_indicators_history',
            'market_benchmark_indicators',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('WS_NEW_STRATEGY_R01_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }

        $closure = $this->verifyC171Closure();
        if (! ($closure['valid'] ?? false)) {
            return $this->blocked((string) ($closure['reason_code'] ?? 'WS_NEW_STRATEGY_R01_C171_CLOSURE_INVALID'), $closure);
        }

        $source = $this->loadSourceIdentity();
        if (! ($source['valid'] ?? false)) {
            return $this->blocked((string) ($source['reason_code'] ?? 'WS_NEW_STRATEGY_R01_SOURCE_IDENTITY_INVALID'), $source);
        }

        $databaseManifest = $this->officialEvidence->databaseManifest(self::SOURCE_EVAL_ID);
        if ($databaseManifest !== $this->manifestFromEval($source['eval'])) {
            return $this->blocked('WS_NEW_STRATEGY_R01_SOURCE_MANIFEST_MISMATCH', [
                'database_manifest' => $databaseManifest,
                'expected_manifest' => $this->manifestFromEval($source['eval']),
            ]);
        }

        $before = $this->boundaryCounts();
        $signal = $this->loadSignalEvidence();
        if (! ($signal['ready'] ?? false)) {
            return $this->blocked((string) ($signal['reason_code'] ?? 'WS_NEW_STRATEGY_R01_SIGNAL_EVIDENCE_NOT_READY'), $signal);
        }

        $calendar = $this->calendar->resolveTradingDates(self::CANONICAL_IS_FROM, self::CANONICAL_IS_TO);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked('WS_NEW_STRATEGY_R01_CALENDAR_NOT_READY', ['calendar' => $calendar]);
        }
        $calendarDates = array_values($calendar['calendar_dates'] ?? $calendar['trade_dates'] ?? []);
        $priceMap = $this->requiredPriceTickerMap($signal['rows'], $calendarDates);
        if ($priceMap === []) {
            return $this->blocked('WS_NEW_STRATEGY_R01_PRICE_MAP_EMPTY');
        }
        $priceDates = array_keys($priceMap);
        $priceRead = $this->prices->readPublishedSeriesForDateTickerMap(
            $priceDates[0],
            $priceDates[count($priceDates) - 1],
            $priceMap
        );
        if (! ($priceRead['is_ready'] ?? false)) {
            return $this->blocked('WS_NEW_STRATEGY_R01_PUBLISHED_PRICE_READ_NOT_READY', [
                'price_series_manifest' => $priceRead['price_series_manifest'] ?? [],
                'diagnostics' => $priceRead['diagnostics'] ?? [],
            ]);
        }

        $replay = $this->replayOfficialPicks(
            $signal['rows'],
            $source['runtime_paramset'],
            $priceRead['series_by_ticker'] ?? [],
            $calendarDates
        );
        if (! ($replay['parity']['pass'] ?? false)) {
            return $this->blocked('WS_NEW_STRATEGY_R01_OFFICIAL_PICK_REPLAY_PARITY_FAILED', $replay['parity']);
        }

        $analysis = $this->analyzeEvidence(
            $replay['rows'],
            (int) ($source['eval']['days_covered'] ?? 0)
        );
        $after = $this->boundaryCounts();
        if ($before !== $after) {
            return $this->blocked('WS_NEW_STRATEGY_R01_DATABASE_MUTATION_FORBIDDEN', [
                'database_boundary_counts_before' => $before,
                'database_boundary_counts_after' => $after,
            ]);
        }

        $overwrite = (bool) ($options['overwrite'] ?? false);
        $paths = $this->derivedPaths($outputPath);
        $outputs = [
            'detailed_trades_csv' => $this->writeCsv($paths['detailed_trades_csv'], $replay['rows'], $overwrite),
            'segments_csv' => $this->writeCsv($paths['segments_csv'], $analysis['segment_rows'], $overwrite),
            'winner_loser_csv' => $this->writeCsv($paths['winner_loser_csv'], $analysis['winner_loser_rows'], $overwrite),
            'monthly_yearly_csv' => $this->writeCsv($paths['monthly_yearly_csv'], $analysis['monthly_yearly_rows'], $overwrite),
        ];

        $hypothesisArtifact = [
            'schema_version' => 'WS_NEW_STRATEGY_R01_HYPOTHESIS_LOCK_V1',
            'run_code' => self::RUN_CODE,
            'source_eval_id' => self::SOURCE_EVAL_ID,
            'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
            'source_paramset_hash' => self::SOURCE_PARAMSET_HASH,
            'source_evidence_manifest_hash' => self::SOURCE_EVIDENCE_MANIFEST_HASH,
            'canonical_is_from' => self::CANONICAL_IS_FROM,
            'canonical_is_to' => self::CANONICAL_IS_TO,
            'max_hypotheses' => self::MAX_HYPOTHESES,
            'research_hypotheses' => $analysis['research_hypotheses'],
            'candidate_design_allowed_hypotheses' => $analysis['candidate_design_allowed_hypotheses'],
            'candidate_design_allowed_count' => count($analysis['candidate_design_allowed_hypotheses']),
            'anti_overfit_rules' => $this->antiOverfitRules(),
            'draft_paramset_created' => false,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ];
        $hypothesisArtifact['artifact_hash'] = $this->identity->stableHash($hypothesisArtifact);
        $outputs['hypothesis_lock_json'] = $this->writeJson(
            $paths['hypothesis_lock_json'],
            $hypothesisArtifact,
            $overwrite
        );

        $result = [
            'schema_version' => 'WS_NEW_STRATEGY_R01_DIAGNOSTIC_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => self::SUCCESS_STATUS,
            'reason_code' => $analysis['diagnostic_reason_code'],
            'approval_reference' => $approvalReference,
            'c171_status' => 'CLOSED',
            'c171_result' => 'FAILED_NOT_READY',
            'c171_more_remediation_allowed' => false,
            'separate_new_strategy_scope' => true,
            'source_eval_id' => self::SOURCE_EVAL_ID,
            'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
            'source_param_id' => self::SOURCE_PARAM_ID,
            'source_paramset_hash' => self::SOURCE_PARAMSET_HASH,
            'source_evidence_manifest' => $databaseManifest,
            'canonical_is_from' => self::CANONICAL_IS_FROM,
            'canonical_is_to' => self::CANONICAL_IS_TO,
            'official_pick_replay_parity' => $replay['parity'],
            'signal_feature_lineage' => $signal['lineage'],
            'overall_metrics' => $analysis['overall_metrics'],
            'canonical_gate_snapshot' => $analysis['canonical_gate_snapshot'],
            'research_hypotheses' => $analysis['research_hypotheses'],
            'candidate_design_allowed_hypotheses' => $analysis['candidate_design_allowed_hypotheses'],
            'candidate_design_allowed_count' => count($analysis['candidate_design_allowed_hypotheses']),
            'diagnostic_highlights' => $analysis['diagnostic_highlights'],
            'anti_overfit_rules' => $this->antiOverfitRules(),
            'draft_paramset_created' => false,
            'draft_paramset_mutated' => false,
            'official_is_runtime_invoked' => false,
            'diagnostic_trade_replay_invoked' => true,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'recommendation_persisted' => false,
            'confirm_mutated' => false,
            'production_activation_executed' => false,
            'production_ready' => false,
            'database_boundary_counts_before' => $before,
            'database_boundary_counts_after' => $after,
            'outputs' => $outputs,
            'next_recommendation' => count($analysis['candidate_design_allowed_hypotheses']) > 0
                ? 'WS_NEW_STRATEGY_R02_IMPLEMENT_MINIMAL_ONE_IDEA_CANDIDATES_FROM_SUPPORTED_R01_HYPOTHESES'
                : 'WS_NEW_STRATEGY_R02_EXPAND_DECISION_TIME_DIAGNOSTIC_EVIDENCE_WITHOUT_PARAMSET',
        ];
        $result['artifact_hash'] = $this->identity->stableHash($result);
        $result['write'] = $this->writeJson($outputPath, $result, $overwrite);

        return $result;
    }

    public function analyzeEvidence(array $rows, ?int $officialDaysCovered = null): array
    {
        $rows = array_values(array_filter($rows, function (array $row): bool {
            return is_numeric($row['ret_net'] ?? null);
        }));
        $overall = $this->metricsForRows($rows);
        $overall['observed_trade_days'] = $overall['days_covered'];
        if ($officialDaysCovered !== null && $officialDaysCovered > 0) {
            $overall['days_covered'] = $officialDaysCovered;
            $overall['coverage_semantics'] = 'OFFICIAL_EVAL_COVERAGE_INCLUDES_METRICS_READY_AND_EXPLICIT_VALID_EMPTY_RECOMMENDATION_DATES';
        } else {
            $overall['coverage_semantics'] = 'OBSERVED_TRADE_DATES_ONLY';
        }
        $segmentRows = [];
        foreach ($this->segmentDefinitions() as $axis => $resolver) {
            $groups = [];
            foreach ($rows as $row) {
                $groups[$resolver($row)][] = $row;
            }
            ksort($groups, SORT_STRING);
            foreach ($groups as $segment => $items) {
                $segmentRows[] = array_merge([
                    'axis' => $axis,
                    'segment' => $segment,
                ], $this->metricsForRows($items));
            }
        }

        $winnerLoserRows = [];
        foreach ([
            'score_total',
            'signal_close_price',
            'signal_tick_risk_expansion_pct',
            'dv20_idr',
            'atr14_pct',
            'vol_ratio',
            'roc5',
            'roc10',
            'roc20',
            'close_to_hh20_pct',
            'range_position_20_pct',
            'ma20_slope_pct',
            'rs_20_vs_ihsg',
            'market_index_roc20',
            'market_index_ma20_slope_pct',
            'entry_gap_pct',
        ] as $field) {
            foreach (['WIN', 'LOSS_OR_FLAT'] as $population) {
                $items = array_values(array_filter($rows, function (array $row) use ($population): bool {
                    return $population === 'WIN'
                        ? (float) $row['ret_net'] > 0
                        : (float) $row['ret_net'] <= 0;
                }));
                $values = array_values(array_filter(array_map(function (array $row) use ($field) {
                    return $this->floatOrNull($row[$field] ?? null);
                }, $items), function ($value): bool {
                    return $value !== null;
                }));
                sort($values, SORT_NUMERIC);
                $winnerLoserRows[] = [
                    'field' => $field,
                    'population' => $population,
                    'trade_count' => count($items),
                    'feature_value_count' => count($values),
                    'feature_average' => $values === [] ? null : array_sum($values) / count($values),
                    'feature_p25' => $this->quantile($values, 0.25),
                    'feature_median' => $this->quantile($values, 0.50),
                    'feature_p75' => $this->quantile($values, 0.75),
                    'avg_ret_net' => $this->average(array_column($items, 'ret_net')),
                ];
            }
        }

        $monthlyYearly = [];
        foreach (['MONTH' => 7, 'YEAR' => 4] as $periodType => $length) {
            $groups = [];
            foreach ($rows as $row) {
                $groups[substr((string) ($row['trade_date'] ?? ''), 0, $length)][] = $row;
            }
            ksort($groups, SORT_STRING);
            foreach ($groups as $period => $items) {
                $metrics = $this->metricsForRows($items);
                $monthlyYearly[] = array_merge([
                    'period_type' => $periodType,
                    'period' => $period,
                ], $metrics, [
                    'win_rate_gate_pass' => $metrics['win_rate'] >= 0.45,
                    'average_gate_pass' => $metrics['avg_ret_net'] >= -0.01,
                    'period_pass' => $metrics['win_rate'] >= 0.45 && $metrics['avg_ret_net'] >= -0.01,
                ]);
            }
        }

        $hypotheses = $this->evaluatePreRegisteredHypotheses($segmentRows);
        $allowed = array_values(array_filter($hypotheses, function (array $hypothesis): bool {
            return ($hypothesis['diagnostic_status'] ?? '') === 'SUPPORTED_FOR_MINIMAL_CANDIDATE_DESIGN';
        }));
        $allowed = array_slice($allowed, 0, self::MAX_HYPOTHESES);

        return [
            'diagnostic_reason_code' => $allowed === []
                ? 'WS_NEW_STRATEGY_R01_HYPOTHESES_INCONCLUSIVE'
                : 'WS_NEW_STRATEGY_R01_SUPPORTED_HYPOTHESES_FOUND',
            'overall_metrics' => $overall,
            'canonical_gate_snapshot' => $this->canonicalGateSnapshot($overall),
            'segment_rows' => $segmentRows,
            'winner_loser_rows' => $winnerLoserRows,
            'monthly_yearly_rows' => $monthlyYearly,
            'research_hypotheses' => $hypotheses,
            'candidate_design_allowed_hypotheses' => $allowed,
            'diagnostic_highlights' => [
                'worst_month' => $this->worstPeriod($monthlyYearly, 'MONTH'),
                'worst_year' => $this->worstPeriod($monthlyYearly, 'YEAR'),
                'largest_average_return_contrasts' => $this->largestContrasts($segmentRows, 'avg_ret_net'),
                'largest_p25_contrasts' => $this->largestContrasts($segmentRows, 'p25_ret_net'),
            ],
        ];
    }

    private function loadSourceIdentity(): array
    {
        $eval = DB::table('watchlist_bt_eval')->where('eval_id', self::SOURCE_EVAL_ID)->first();
        $paramset = DB::table('watchlist_param_sets')->where('param_set_id', self::SOURCE_PARAM_SET_ID)->first();
        if (! $eval || ! $paramset) {
            return ['valid' => false, 'reason_code' => 'WS_NEW_STRATEGY_R01_SOURCE_ROW_MISSING'];
        }
        if ((string) $eval->policy_code !== 'WS'
            || (int) $eval->param_id !== self::SOURCE_PARAM_ID
            || (string) $eval->from_date !== self::CANONICAL_IS_FROM
            || (string) $eval->to_date !== self::CANONICAL_IS_TO
            || (string) $eval->paramset_hash !== self::SOURCE_PARAMSET_HASH
            || (string) $eval->evidence_manifest_hash !== self::SOURCE_EVIDENCE_MANIFEST_HASH
            || (string) $paramset->policy_code !== 'WS'
            || (string) $paramset->status !== 'DRAFT'
            || (string) $paramset->params_hash !== self::SOURCE_PARAMSET_HASH) {
            return ['valid' => false, 'reason_code' => 'WS_NEW_STRATEGY_R01_SOURCE_ROW_IDENTITY_MISMATCH'];
        }
        $payload = json_decode((string) $paramset->params_json, true);
        if (! is_array($payload)) {
            return ['valid' => false, 'reason_code' => 'WS_NEW_STRATEGY_R01_SOURCE_PARAMSET_JSON_INVALID'];
        }
        $validation = $this->validator->validate($payload);
        if (! ($validation['valid'] ?? false)
            || (string) ($validation['canonical_hash'] ?? '') !== self::SOURCE_PARAMSET_HASH) {
            return ['valid' => false, 'reason_code' => 'WS_NEW_STRATEGY_R01_SOURCE_PARAMSET_VALIDATION_FAILED'];
        }

        return [
            'valid' => true,
            'reason_code' => 'WS_NEW_STRATEGY_R01_SOURCE_IDENTITY_VALID',
            'eval' => (array) $eval,
            'runtime_paramset' => $this->runtimeAdapter->adapt($validation['canonical_payload']),
        ];
    }

    private function verifyC171Closure(): array
    {
        $path = base_path('docs/watchlist/audit/_artifacts/c171-final-failed-not-ready-closure-decision.json');
        if (! is_file($path)
            || strtolower((string) sha1_file($path)) !== WeeklySwingC171FinalFailedNotReadyClosureService::DECISION_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'WS_NEW_STRATEGY_R01_C171_CLOSURE_FILE_IDENTITY_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)
            || ($artifact['final_decision'] ?? '') !== WeeklySwingC171FinalFailedNotReadyClosureService::FINAL_DECISION
            || ($artifact['additional_c171_candidate_catalog_allowed'] ?? true) !== false
            || ($artifact['oos_allowed'] ?? true) !== false
            || (int) ($artifact['anchor']['eval_id'] ?? 0) !== self::SOURCE_EVAL_ID
            || (int) ($artifact['anchor']['param_set_id'] ?? 0) !== self::SOURCE_PARAM_SET_ID
            || (string) ($artifact['anchor']['params_hash'] ?? '') !== self::SOURCE_PARAMSET_HASH) {
            return ['valid' => false, 'reason_code' => 'WS_NEW_STRATEGY_R01_C171_CLOSURE_CONTRACT_MISMATCH'];
        }
        $hash = (string) ($artifact['artifact_hash'] ?? '');
        unset($artifact['artifact_hash']);
        if ($hash !== WeeklySwingC171FinalFailedNotReadyClosureService::DECISION_ARTIFACT_HASH
            || $this->identity->stableHash($artifact) !== $hash) {
            return ['valid' => false, 'reason_code' => 'WS_NEW_STRATEGY_R01_C171_CLOSURE_HASH_MISMATCH'];
        }

        return ['valid' => true, 'reason_code' => 'WS_NEW_STRATEGY_R01_C171_CLOSURE_VERIFIED'];
    }

    private function loadSignalEvidence(): array
    {
        $rows = DB::table('watchlist_bt_picks_ws as p')
            ->join('watchlist_bt_universe_ws as u', function ($join): void {
                $join->on('u.eval_id', '=', 'p.eval_id')
                    ->on('u.asof_eod_date', '=', 'p.asof_eod_date')
                    ->on('u.ticker_id', '=', 'p.ticker_id');
            })
            ->leftJoin('eod_indicators_history as i', function ($join): void {
                $join->on('i.publication_id', '=', 'u.source_publication_id')
                    ->on('i.run_id', '=', 'u.source_run_id')
                    ->on('i.trade_date', '=', 'u.asof_eod_date')
                    ->on('i.ticker_id', '=', 'u.ticker_id');
            })
            ->leftJoin('market_data_sectors as s', 's.sector_code', '=', 'i.sector_code')
            ->where('p.eval_id', self::SOURCE_EVAL_ID)
            ->orderBy('p.asof_eod_date')
            ->orderBy('p.ticker_id')
            ->get([
                'p.asof_eod_date as trade_date',
                'p.ticker_id',
                'p.ticker_code',
                'p.bucket_code',
                'p.ret_net as official_ret_net',
                'p.score_total',
                'p.source_publication_id as entry_publication_id',
                'p.source_publication_version as entry_publication_version',
                'p.source_run_id as entry_run_id',
                'u.dv20_idr',
                'u.atr14_pct',
                'u.vol_ratio',
                'u.signal_close_price',
                'u.signal_tick_risk_expansion_pct',
                'u.source_publication_id as signal_publication_id',
                'u.source_publication_version as signal_publication_version',
                'u.source_run_id as signal_run_id',
                'i.is_valid as indicator_is_valid',
                'i.indicator_set_version',
                'i.roc5',
                'i.roc10',
                'i.roc20',
                'i.hh20',
                'i.close_to_hh20_pct',
                'i.range_position_20_pct',
                'i.ma20_slope_pct',
                'i.rs_20_vs_ihsg',
                'i.sector_roc20',
                'i.rs_20_vs_sector',
                'i.sector_code',
                's.sector_name',
                'i.event_risk_flag',
            ])
            ->map(function ($row): array {
                return (array) $row;
            })
            ->all();

        if (count($rows) !== 1308) {
            return [
                'ready' => false,
                'reason_code' => 'WS_NEW_STRATEGY_R01_SOURCE_PICK_COUNT_MISMATCH',
                'expected_count' => 1308,
                'actual_count' => count($rows),
            ];
        }
        $invalid = array_values(array_filter($rows, function (array $row): bool {
            return (int) ($row['indicator_is_valid'] ?? 0) !== 1
                || (int) ($row['signal_publication_id'] ?? 0) < 1
                || (int) ($row['signal_run_id'] ?? 0) < 1
                || ($row['roc5'] ?? null) === null
                || ($row['roc10'] ?? null) === null
                || ($row['roc20'] ?? null) === null
                || ($row['close_to_hh20_pct'] ?? null) === null;
        }));
        if ($invalid !== []) {
            return [
                'ready' => false,
                'reason_code' => 'WS_NEW_STRATEGY_R01_SIGNAL_FEATURE_LINEAGE_INCOMPLETE',
                'invalid_count' => count($invalid),
                'invalid_sample' => array_slice($invalid, 0, 10),
            ];
        }

        $dates = array_values(array_unique(array_column($rows, 'trade_date')));
        $benchmarkByDate = [];
        $indicatorSetVersion = (string) config('market_data.indicators.set_version', 'v1');
        foreach (array_chunk($dates, 200) as $chunk) {
            $benchmarks = DB::table('market_benchmark_indicators')
                ->where('benchmark_code', 'IHSG')
                ->where('indicator_set_version', $indicatorSetVersion)
                ->where('is_valid', 1)
                ->whereIn('trade_date', $chunk)
                ->orderBy('trade_date')
                ->get(['trade_date', 'roc_20', 'ma20_slope_pct', 'indicator_set_version', 'is_valid']);
            foreach ($benchmarks as $benchmark) {
                $benchmarkByDate[(string) $benchmark->trade_date] = [
                    'market_index_roc20' => $benchmark->roc_20,
                    'market_index_ma20_slope_pct' => $benchmark->ma20_slope_pct,
                    'market_indicator_set_version' => $benchmark->indicator_set_version,
                    'market_indicator_is_valid' => (bool) $benchmark->is_valid,
                ];
            }
        }
        foreach ($rows as &$row) {
            $row = array_merge($row, $benchmarkByDate[(string) $row['trade_date']] ?? [
                'market_index_roc20' => null,
                'market_index_ma20_slope_pct' => null,
                'market_indicator_set_version' => null,
                'market_indicator_is_valid' => false,
            ]);
        }
        unset($row);

        $benchmarkCovered = count(array_filter($rows, function (array $row): bool {
            return ($row['market_indicator_is_valid'] ?? false) === true
                && is_numeric($row['market_index_roc20'] ?? null)
                && is_numeric($row['market_index_ma20_slope_pct'] ?? null);
        }));

        return [
            'ready' => true,
            'reason_code' => 'WS_NEW_STRATEGY_R01_SIGNAL_EVIDENCE_READY',
            'rows' => $rows,
            'lineage' => [
                'source_mode' => 'IMMUTABLE_SIGNAL_PUBLICATION_INDICATOR_HISTORY_JOIN',
                'trade_count' => count($rows),
                'equity_feature_complete_count' => count($rows),
                'benchmark_feature_complete_count' => $benchmarkCovered,
                'equity_feature_coverage' => count($rows) > 0 ? 1.0 : 0.0,
                'benchmark_feature_coverage' => count($rows) > 0 ? $benchmarkCovered / count($rows) : 0.0,
                'decision_time_only' => true,
                'future_return_used_as_feature' => false,
            ],
        ];
    }

    private function replayOfficialPicks(
        array $rows,
        array $runtimeParamset,
        array $series,
        array $calendarDates
    ): array {
        $trades = [];
        foreach ($rows as $row) {
            $trades[] = [
                'trade_date' => (string) $row['trade_date'],
                'ticker_id' => (int) $row['ticker_id'],
                'ticker' => (string) $row['ticker_code'],
                'ticker_code' => (string) $row['ticker_code'],
                'bucket_code' => (string) $row['bucket_code'],
                'score_total' => (float) $row['score_total'],
                'atr14_pct' => (float) $row['atr14_pct'],
            ];
        }
        $metrics = $this->metrics->buildMetrics([
            'trades' => $trades,
            'replay_window' => ['trade_dates' => array_values(array_unique(array_column($trades, 'trade_date')))],
            'paramset_snapshot' => $runtimeParamset,
            'diagnostics' => [],
        ], $series, $calendarDates);
        $evaluated = is_array($metrics['evaluated_trades'] ?? null) ? $metrics['evaluated_trades'] : [];
        $index = [];
        foreach ($evaluated as $item) {
            if (is_array($item)) {
                $index[$this->key($item['trade_date'] ?? '', $item['ticker_id'] ?? 0)] = $item;
            }
        }

        $mismatches = [];
        $detail = [];
        foreach ($rows as $row) {
            $key = $this->key($row['trade_date'], $row['ticker_id']);
            $item = $index[$key] ?? null;
            if (! is_array($item)
                || ($item['metrics_ready'] ?? false) !== true
                || ! is_numeric($item['ret_net'] ?? null)) {
                $mismatches[] = ['key' => $key, 'reason_code' => 'REPLAY_ROW_NOT_METRICS_READY'];
                continue;
            }
            $officialRet = round((float) $row['official_ret_net'], 6);
            $replayRet = round((float) $item['ret_net'], 6);
            if (abs($officialRet - $replayRet) > 0.000001) {
                $mismatches[] = [
                    'key' => $key,
                    'reason_code' => 'RET_NET_MISMATCH',
                    'official_ret_net' => $officialRet,
                    'replay_ret_net' => $replayRet,
                ];
            }
            foreach ([
                'entry_publication_id' => 'entry_publication_id',
                'entry_publication_version' => 'entry_publication_version',
                'entry_run_id' => 'entry_run_id',
            ] as $sourceField => $replayField) {
                if ((int) ($row[$sourceField] ?? 0) !== (int) ($item[$replayField] ?? 0)) {
                    $mismatches[] = [
                        'key' => $key,
                        'reason_code' => 'ENTRY_LINEAGE_MISMATCH',
                        'field' => $sourceField,
                        'expected' => $row[$sourceField] ?? null,
                        'actual' => $item[$replayField] ?? null,
                    ];
                }
            }
            $signalClose = $this->floatOrNull($row['signal_close_price'] ?? null);
            $entryPrice = $this->floatOrNull($item['entry_price'] ?? null);
            $marketRoc = $this->floatOrNull($row['market_index_roc20'] ?? null);
            $marketSlope = $this->floatOrNull($row['market_index_ma20_slope_pct'] ?? null);
            $detail[] = array_merge($row, $item, [
                'official_ret_net' => $officialRet,
                'replay_ret_net' => $replayRet,
                'ret_net' => $replayRet,
                'ret_net_parity' => abs($officialRet - $replayRet) <= 0.000001,
                'entry_gap_pct' => $signalClose !== null && $signalClose > 0 && $entryPrice !== null
                    ? ($entryPrice / $signalClose) - 1
                    : null,
                'market_regime' => $this->marketRegime($marketRoc, $marketSlope),
                'momentum_persistence' => $this->momentumPersistence($row),
            ]);
        }

        return [
            'rows' => $detail,
            'parity' => [
                'pass' => $mismatches === [] && count($detail) === count($rows),
                'checked_count' => count($rows),
                'replayed_count' => count($detail),
                'mismatch_count' => count($mismatches),
                'mismatch_sample' => array_slice($mismatches, 0, 20),
                'contract' => 'IMMUTABLE_OFFICIAL_RET_NET_6DP_AND_ENTRY_PUBLICATION_LINEAGE_PARITY',
            ],
        ];
    }

    private function evaluatePreRegisteredHypotheses(array $segments): array
    {
        $definitions = [
            [
                'rank' => 1,
                'hypothesis_code' => 'H1_BREAKOUT_QUALITY_CONFIRMATION',
                'idea' => 'Breakout dekat HH20 dengan kualitas volume yang wajar lebih stabil daripada breakout jauh atau terlalu extended.',
                'primary_axis' => 'breakout_extension',
                'supporting_axis' => 'volume_ratio',
                'decision_time_fields' => ['close_to_hh20_pct', 'range_position_20_pct', 'vol_ratio'],
                'minimal_candidate_rule' => 'Uji satu filter breakout-quality saja; threshold final harus ditetapkan sebelum official IS.',
            ],
            [
                'rank' => 2,
                'hypothesis_code' => 'H2_MOMENTUM_PERSISTENCE',
                'idea' => 'Momentum yang selaras pada ROC5/ROC10/ROC20 lebih tahan dibanding lonjakan satu titik atau momentum yang sedang mendingin.',
                'primary_axis' => 'momentum_persistence',
                'supporting_axis' => 'roc20',
                'decision_time_fields' => ['roc5', 'roc10', 'roc20', 'ma20_slope_pct', 'rs_20_vs_ihsg'],
                'minimal_candidate_rule' => 'Uji satu konfirmasi persistensi momentum saja; return dan exit path tidak boleh menjadi router.',
            ],
            [
                'rank' => 3,
                'hypothesis_code' => 'H3_MARKET_REGIME_COMPATIBILITY',
                'idea' => 'Kualitas Weekly Swing berbeda secara material menurut kondisi IHSG yang tersedia pada signal date.',
                'primary_axis' => 'market_regime',
                'supporting_axis' => 'market_index_roc20',
                'decision_time_fields' => ['market_index_roc20', 'market_index_ma20_slope_pct'],
                'minimal_candidate_rule' => 'Uji satu aturan regime decision-time saja tanpa blacklist bulan dan tanpa membaca OOS.',
            ],
        ];

        $result = [];
        foreach ($definitions as $definition) {
            $primary = $this->axisContrast($segments, $definition['primary_axis']);
            $supporting = $this->axisContrast($segments, $definition['supporting_axis']);
            $supported = ($primary['supported'] ?? false) || ($supporting['supported'] ?? false);
            $result[] = array_merge($definition, [
                'registration_status' => 'PRE_REGISTERED_BEFORE_CANDIDATE_DESIGN',
                'diagnostic_status' => $supported
                    ? 'SUPPORTED_FOR_MINIMAL_CANDIDATE_DESIGN'
                    : 'INCONCLUSIVE_REQUIRES_EVIDENCE_EXPANSION',
                'primary_contrast' => $primary,
                'supporting_contrast' => $supporting,
                'future_return_as_selection_input' => false,
                'oos_used' => false,
                'canonical_gates_changed' => false,
            ]);
        }

        return array_slice($result, 0, self::MAX_HYPOTHESES);
    }

    private function axisContrast(array $segments, string $axis): array
    {
        $rows = array_values(array_filter($segments, function (array $row) use ($axis): bool {
            return ($row['axis'] ?? '') === $axis && (int) ($row['trade_count'] ?? 0) >= 20;
        }));
        if (count($rows) < 2) {
            return [
                'axis' => $axis,
                'supported' => false,
                'reason_code' => 'INSUFFICIENT_COMPARABLE_SEGMENTS',
                'comparable_segment_count' => count($rows),
            ];
        }
        usort($rows, function (array $left, array $right): int {
            foreach (['median_ret_net', 'p25_ret_net', 'avg_ret_net', 'win_rate'] as $field) {
                $cmp = ((float) ($right[$field] ?? -INF)) <=> ((float) ($left[$field] ?? -INF));
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            return strcmp((string) $left['segment'], (string) $right['segment']);
        });
        $best = $rows[0];
        $worst = $rows[count($rows) - 1];
        $medianSpread = (float) $best['median_ret_net'] - (float) $worst['median_ret_net'];
        $p25Spread = (float) $best['p25_ret_net'] - (float) $worst['p25_ret_net'];
        $averageSpread = (float) $best['avg_ret_net'] - (float) $worst['avg_ret_net'];
        $winRateSpread = (float) $best['win_rate'] - (float) $worst['win_rate'];
        $supported = ($medianSpread >= 0.005 && ($winRateSpread >= 0.05 || $p25Spread >= 0))
            || ($p25Spread >= 0.01 && $medianSpread >= 0)
            || ($medianSpread >= 0 && $p25Spread >= 0 && $averageSpread >= 0.005);

        return [
            'axis' => $axis,
            'supported' => $supported,
            'reason_code' => $supported ? 'MATERIAL_SEGMENT_CONTRAST_FOUND' : 'MATERIAL_SEGMENT_CONTRAST_NOT_FOUND',
            'comparable_segment_count' => count($rows),
            'best_segment' => $best,
            'worst_segment' => $worst,
            'median_ret_net_spread' => $medianSpread,
            'p25_ret_net_spread' => $p25Spread,
            'avg_ret_net_spread' => $averageSpread,
            'win_rate_spread' => $winRateSpread,
            'p25_regression_warning' => $p25Spread < 0,
            'support_rule' => '(median>=0.005 AND (win_rate>=0.05 OR p25>=0)) OR (p25>=0.01 AND median>=0) OR (median>=0 AND p25>=0 AND average>=0.005); each compared segment >=20 trades',
        ];
    }

    private function segmentDefinitions(): array
    {
        return [
            'outcome' => function (array $row): string {
                return (float) $row['ret_net'] > 0 ? 'WIN' : 'LOSS_OR_FLAT';
            },
            'month' => function (array $row): string {
                return substr((string) $row['trade_date'], 0, 7);
            },
            'year' => function (array $row): string {
                return substr((string) $row['trade_date'], 0, 4);
            },
            'entry_gap' => function (array $row): string {
                $value = $this->floatOrNull($row['entry_gap_pct'] ?? null);
                if ($value === null) return 'UNKNOWN';
                if ($value <= -0.02) return 'GAP_DOWN_LE_-2%';
                if ($value < 0) return 'GAP_DOWN_-2_TO_0%';
                if ($value < 0.02) return 'FLAT_TO_GAP_UP_2%';
                return 'GAP_UP_GE_2%';
            },
            'entry_price' => function (array $row): string {
                $value = $this->floatOrNull($row['entry_price'] ?? null);
                if ($value === null) return 'UNKNOWN';
                if ($value < 200) return 'LT_200';
                if ($value < 500) return '200_TO_499';
                if ($value < 1000) return '500_TO_999';
                if ($value < 5000) return '1000_TO_4999';
                return 'GE_5000';
            },
            'tick_risk' => function (array $row): string {
                return $this->numericBand($row['signal_tick_risk_expansion_pct'] ?? null, [0.005, 0.01, 0.015], [
                    'LT_0_5%', '0_5_TO_1%', '1_TO_1_5%', 'GE_1_5%',
                ]);
            },
            'breakout_extension' => function (array $row): string {
                return $this->numericBand($row['close_to_hh20_pct'] ?? null, [-0.02, 0, 0.02, 0.05], [
                    'FAR_BELOW_LT_-2%', 'NEAR_BELOW_-2_TO_0%', 'BREAKOUT_0_TO_2%', 'EXTENDED_2_TO_5%', 'HIGHLY_EXTENDED_GE_5%',
                ]);
            },
            'roc20' => function (array $row): string {
                return $this->numericBand($row['roc20'] ?? null, [0, 0.05, 0.10, 0.15], [
                    'NEGATIVE', '0_TO_5%', '5_TO_10%', '10_TO_15%', 'GE_15%',
                ]);
            },
            'momentum_persistence' => function (array $row): string {
                return (string) ($row['momentum_persistence'] ?? 'UNKNOWN');
            },
            'volume_ratio' => function (array $row): string {
                return $this->numericBand($row['vol_ratio'] ?? null, [1.2, 1.5, 2, 3, 5], [
                    'LT_1_2', '1_2_TO_1_5', '1_5_TO_2', '2_TO_3', '3_TO_5', 'GE_5',
                ]);
            },
            'atr14' => function (array $row): string {
                return $this->numericBand($row['atr14_pct'] ?? null, [0.02, 0.035, 0.05, 0.06, 0.08], [
                    'LT_2%', '2_TO_3_5%', '3_5_TO_5%', '5_TO_6%', '6_TO_8%', 'GE_8%',
                ]);
            },
            'market_regime' => function (array $row): string {
                return (string) ($row['market_regime'] ?? 'UNKNOWN');
            },
            'market_index_roc20' => function (array $row): string {
                return $this->numericBand($row['market_index_roc20'] ?? null, [-0.05, 0, 0.05, 0.10], [
                    'LT_-5%', '-5_TO_0%', '0_TO_5%', '5_TO_10%', 'GE_10%',
                ]);
            },
            'exit_reason' => function (array $row): string {
                return (string) ($row['exit_reason_code'] ?? 'UNKNOWN');
            },
            'gap_detected' => function (array $row): string {
                return ($row['gap_detected'] ?? false) ? 'GAP_DETECTED' : 'NO_TRIGGER_GAP';
            },
            'sector' => function (array $row): string {
                return (string) ($row['sector_code'] ?? 'UNKNOWN');
            },
        ];
    }

    private function metricsForRows(array $rows): array
    {
        $returns = array_values(array_filter(array_map(function (array $row) {
            return $this->floatOrNull($row['ret_net'] ?? null);
        }, $rows), function ($value): bool {
            return $value !== null;
        }));
        sort($returns, SORT_NUMERIC);
        $wins = count(array_filter($returns, function (float $value): bool {
            return $value > 0;
        }));
        $dates = array_values(array_unique(array_filter(array_map(function (array $row): string {
            return (string) ($row['trade_date'] ?? '');
        }, $rows))));
        $months = [];
        foreach ($rows as $row) {
            $months[substr((string) ($row['trade_date'] ?? ''), 0, 7)][] = (float) ($row['ret_net'] ?? 0);
        }
        $monthWinRates = [];
        $monthAverages = [];
        foreach ($months as $monthReturns) {
            $monthWins = count(array_filter($monthReturns, function (float $value): bool {
                return $value > 0;
            }));
            $monthWinRates[] = count($monthReturns) > 0 ? $monthWins / count($monthReturns) : 0;
            $monthAverages[] = count($monthReturns) > 0 ? array_sum($monthReturns) / count($monthReturns) : 0;
        }

        return [
            'trade_count' => count($returns),
            'days_covered' => count($dates),
            'months_covered' => count($months),
            'avg_ret_net' => $returns === [] ? null : array_sum($returns) / count($returns),
            'median_ret_net' => $this->quantile($returns, 0.50),
            'p25_ret_net' => $this->quantile($returns, 0.25),
            'p75_ret_net' => $this->quantile($returns, 0.75),
            'win_rate' => $returns === [] ? null : $wins / count($returns),
            'min_ret_net' => $returns === [] ? null : min($returns),
            'max_ret_net' => $returns === [] ? null : max($returns),
            'month_win_rate_min' => $monthWinRates === [] ? null : min($monthWinRates),
            'month_avg_ret_net_min' => $monthAverages === [] ? null : min($monthAverages),
        ];
    }

    private function canonicalGateSnapshot(array $metrics): array
    {
        $gates = [
            'minimum_trade_count' => (int) ($metrics['trade_count'] ?? 0) >= 120,
            'minimum_coverage' => (int) ($metrics['days_covered'] ?? 0) >= 390,
            'average_return_positive' => ($metrics['avg_ret_net'] ?? null) !== null && $metrics['avg_ret_net'] > 0,
            'median_return_non_negative' => ($metrics['median_ret_net'] ?? null) !== null && $metrics['median_ret_net'] >= 0,
            'p25_downside_bound' => ($metrics['p25_ret_net'] ?? null) !== null && $metrics['p25_ret_net'] >= -0.03,
            'monthly_win_rate_floor' => ($metrics['month_win_rate_min'] ?? null) !== null && $metrics['month_win_rate_min'] >= 0.45,
            'monthly_average_floor' => ($metrics['month_avg_ret_net_min'] ?? null) !== null && $metrics['month_avg_ret_net_min'] >= -0.01,
        ];

        return [
            'pass' => ! in_array(false, $gates, true),
            'gates' => $gates,
            'thresholds' => [
                'min_trades' => 120,
                'min_days_covered' => 390,
                'min_p25_ret_net' => -0.03,
                'min_month_win_rate' => 0.45,
                'min_month_avg_ret_net' => -0.01,
            ],
        ];
    }

    private function worstPeriod(array $rows, string $periodType): ?array
    {
        $filtered = array_values(array_filter($rows, function (array $row) use ($periodType): bool {
            return ($row['period_type'] ?? '') === $periodType;
        }));
        usort($filtered, function (array $left, array $right): int {
            $cmp = ((float) $left['avg_ret_net']) <=> ((float) $right['avg_ret_net']);
            return $cmp !== 0 ? $cmp : strcmp((string) $left['period'], (string) $right['period']);
        });

        return $filtered[0] ?? null;
    }

    private function largestContrasts(array $segments, string $field): array
    {
        $byAxis = [];
        foreach ($segments as $row) {
            if ((int) ($row['trade_count'] ?? 0) >= 20 && is_numeric($row[$field] ?? null)) {
                $byAxis[(string) $row['axis']][] = $row;
            }
        }
        $contrasts = [];
        foreach ($byAxis as $axis => $rows) {
            usort($rows, function (array $left, array $right) use ($field): int {
                return ((float) $left[$field]) <=> ((float) $right[$field]);
            });
            if (count($rows) < 2) {
                continue;
            }
            $worst = $rows[0];
            $best = $rows[count($rows) - 1];
            $contrasts[] = [
                'axis' => $axis,
                'field' => $field,
                'best_segment' => $best['segment'],
                'best_value' => $best[$field],
                'worst_segment' => $worst['segment'],
                'worst_value' => $worst[$field],
                'spread' => (float) $best[$field] - (float) $worst[$field],
            ];
        }
        usort($contrasts, function (array $left, array $right): int {
            return ((float) $right['spread']) <=> ((float) $left['spread']);
        });

        return array_slice($contrasts, 0, 5);
    }

    private function requiredPriceTickerMap(array $rows, array $calendarDates): array
    {
        $dateIndex = array_flip($calendarDates);
        $map = [];
        foreach ($rows as $row) {
            $signalDate = (string) $row['trade_date'];
            if (! isset($dateIndex[$signalDate])) {
                continue;
            }
            $start = (int) $dateIndex[$signalDate] + 1;
            for ($index = $start; $index < min(count($calendarDates), $start + self::HOLDING_DAYS); $index++) {
                $date = $calendarDates[$index];
                $ticker = strtoupper((string) $row['ticker_code']);
                $map[$date][$ticker] = $ticker;
            }
        }
        ksort($map, SORT_STRING);
        foreach ($map as &$tickers) {
            ksort($tickers, SORT_STRING);
            $tickers = array_values($tickers);
        }
        unset($tickers);

        return $map;
    }

    private function manifestFromEval(array $eval): array
    {
        return [
            'schema_version' => 'WS_OFFICIAL_IS_EVIDENCE_C171_V1',
            'picks_count' => (int) ($eval['picks_count'] ?? 0),
            'picks_hash' => (string) ($eval['picks_hash'] ?? ''),
            'universe_count' => (int) ($eval['universe_count'] ?? 0),
            'universe_hash' => (string) ($eval['universe_hash'] ?? ''),
            'cutoff_count' => (int) ($eval['cutoff_count'] ?? 0),
            'cutoffs_hash' => (string) ($eval['cutoffs_hash'] ?? ''),
            'market_data_lineage_hash' => (string) ($eval['market_data_lineage_hash'] ?? ''),
            'evidence_manifest_hash' => (string) ($eval['evidence_manifest_hash'] ?? ''),
        ];
    }

    private function boundaryCounts(): array
    {
        $counts = [];
        foreach ([
            'watchlist_bt_eval',
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_bt_cutoffs_ws',
            'watchlist_param_sets',
            'watchlist_plan_runs',
        ] as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        return $counts;
    }

    private function antiOverfitRules(): array
    {
        return [
            'max_hypotheses' => self::MAX_HYPOTHESES,
            'max_future_candidates' => 3,
            'max_remediation_rounds' => 1,
            'ticker_blacklist_forbidden' => true,
            'month_blacklist_forbidden' => true,
            'future_return_as_selection_input_forbidden' => true,
            'entry_gap_as_selection_input_forbidden' => true,
            'exit_reason_as_selection_input_forbidden' => true,
            'oos_read_before_canonical_is_pass_forbidden' => true,
            'canonical_gate_weakening_forbidden' => true,
            'one_primary_idea_per_candidate' => true,
            'decision_time_fields_only' => true,
        ];
    }

    private function derivedPaths(string $outputPath): array
    {
        $base = preg_replace('/\.json$/i', '', $outputPath) ?: $outputPath;

        return [
            'detailed_trades_csv' => $base.'-trades.csv',
            'segments_csv' => $base.'-segments.csv',
            'winner_loser_csv' => $base.'-winner-loser.csv',
            'monthly_yearly_csv' => $base.'-monthly-yearly.csv',
            'hypothesis_lock_json' => $base.'-hypothesis-lock.json',
        ];
    }

    private function writeCsv(string $path, array $rows, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R01_OUTPUT_EXISTS_USE_OVERWRITE: '.$path);
        }
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R01_OUTPUT_DIRECTORY_CREATE_FAILED: '.$directory);
        }
        $temp = $path.'.tmp.'.getmypid();
        $handle = fopen($temp, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R01_CSV_OPEN_FAILED: '.$path);
        }
        try {
            if ($rows !== []) {
                fputcsv($handle, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($handle, array_map(function ($value) {
                        if (is_bool($value)) return $value ? 1 : 0;
                        if (is_array($value)) return json_encode($value, JSON_UNESCAPED_SLASHES);
                        return $value;
                    }, $row));
                }
            }
        } finally {
            fclose($handle);
        }
        if (! rename($temp, $path)) {
            @unlink($temp);
            throw new \RuntimeException('WS_NEW_STRATEGY_R01_CSV_RENAME_FAILED: '.$path);
        }

        return [
            'status' => 'WRITTEN',
            'path' => $path,
            'row_count' => count($rows),
            'file_sha1' => sha1_file($path),
        ];
    }

    private function writeJson(string $path, array $payload, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R01_OUTPUT_EXISTS_USE_OVERWRITE: '.$path);
        }
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R01_OUTPUT_DIRECTORY_CREATE_FAILED: '.$directory);
        }
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R01_JSON_ENCODING_FAILED: '.json_last_error_msg());
        }
        $temp = $path.'.tmp.'.getmypid();
        if (file_put_contents($temp, $json.PHP_EOL, LOCK_EX) === false || ! rename($temp, $path)) {
            @unlink($temp);
            throw new \RuntimeException('WS_NEW_STRATEGY_R01_JSON_WRITE_FAILED: '.$path);
        }

        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1_file($path)];
    }

    private function momentumPersistence(array $row): string
    {
        $roc5 = $this->floatOrNull($row['roc5'] ?? null);
        $roc10 = $this->floatOrNull($row['roc10'] ?? null);
        $roc20 = $this->floatOrNull($row['roc20'] ?? null);
        if ($roc5 === null || $roc10 === null || $roc20 === null) return 'UNKNOWN';
        if ($roc5 > 0 && $roc10 > 0 && $roc20 > 0) return 'PERSISTENT_POSITIVE';
        if ($roc5 > 0 && $roc10 > 0 && $roc20 <= 0) return 'RECENT_RECOVERY';
        if ($roc20 > 0 && ($roc5 <= 0 || $roc10 <= 0)) return 'MOMENTUM_COOLING';
        return 'NON_PERSISTENT';
    }

    private function marketRegime(?float $roc20, ?float $slope): string
    {
        if ($roc20 === null || $slope === null) return 'UNKNOWN';
        if ($roc20 >= 0 && $slope >= 0) return 'STRONG';
        if ($roc20 < 0 && $slope < 0) return 'WEAK';
        return 'MIXED';
    }

    private function numericBand($value, array $bounds, array $labels): string
    {
        if (! is_numeric($value)) return 'UNKNOWN';
        $number = (float) $value;
        foreach ($bounds as $index => $bound) {
            if ($number < $bound) return $labels[$index];
        }

        return $labels[count($labels) - 1];
    }

    private function average(array $values): ?float
    {
        $values = array_values(array_filter($values, 'is_numeric'));
        return $values === [] ? null : array_sum($values) / count($values);
    }

    private function quantile(array $sorted, float $quantile): ?float
    {
        $count = count($sorted);
        if ($count === 0) return null;
        if ($count === 1) return (float) $sorted[0];
        $position = ($count - 1) * $quantile;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        if ($lower === $upper) return (float) $sorted[$lower];
        $weight = $position - $lower;
        return ((float) $sorted[$lower] * (1 - $weight)) + ((float) $sorted[$upper] * $weight);
    }

    private function key($date, $tickerId): string
    {
        return (string) $date.'|'.(int) $tickerId;
    }

    private function floatOrNull($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'separate_new_strategy_scope' => true,
            'draft_paramset_created' => false,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
