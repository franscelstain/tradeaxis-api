<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataPublishedEodSeriesReadService;
use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOfficialEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingC171ComparativeOfficialIsFailureDiagnosticService
{
    public const RUN_CODE = 'C171_COMPARATIVE_OFFICIAL_IS_FAILURE_DIAGNOSTIC_AND_R2_HYPOTHESIS_LOCK';
    public const CANONICAL_IS_FROM = '2023-01-02';
    public const CANONICAL_IS_TO = '2025-05-21';
    public const BASELINE_EVAL_ID = 188;
    public const ANCHOR_EXPECTED_EVAL_ID = 192;

    private const EVAL_ARTIFACTS = [
        188 => 'c171-versioned-official-is-evidence.json',
        189 => 'c171-official-is-paramset-2.json',
        190 => 'c171-official-is-paramset-3.json',
        191 => 'c171-official-is-paramset-4.json',
        192 => 'c171-official-is-paramset-5.json',
        193 => 'c171-official-is-paramset-6.json',
    ];

    private const EXPECTED_PARAM_SET_IDS = [188 => 1, 189 => 2, 190 => 3, 191 => 4, 192 => 5, 193 => 6];
    private const EXPECTED_ARTIFACT_FILE_SHA1 = [
        188 => 'B9A3E74466F05FB7A1504CAFF4C7B06F86DD3F62',
        189 => '894EE0BED787C130A28A51B5D6D7FCD14CB8D26C',
        190 => 'CBA34F0942DD6B79E26418DA91A3B787EDC1B091',
        191 => '6A7A55D8B491C4A637BB8DD529A02B44AA54C119',
        192 => '590889CEA60A31A92B7B5262D7996AF012E7276A',
        193 => '99A77BD0AFB502C524A731CFF42EC332ED71936A',
    ];
    private const MIN_TRADES = 120;
    private const MIN_DAYS_COVERED = 390;
    private const MIN_P25 = -0.03;
    private const MIN_MONTH_WIN_RATE = 0.45;
    private const MIN_MONTH_AVG = -0.01;
    private const HOLDING_DAYS = 5;

    private MarketDataTradingCalendarReadService $calendar;
    private MarketDataPublishedEodSeriesReadService $prices;
    private WatchlistBacktestMetricsService $metrics;
    private WatchlistBacktestOfficialEvidenceRepository $officialEvidence;
    private WeeklySwingBacktestEvidenceIdentityService $identity;

    public function __construct(
        MarketDataTradingCalendarReadService $calendar = null,
        MarketDataPublishedEodSeriesReadService $prices = null,
        WatchlistBacktestMetricsService $metrics = null,
        WatchlistBacktestOfficialEvidenceRepository $officialEvidence = null,
        WeeklySwingBacktestEvidenceIdentityService $identity = null
    ) {
        $this->calendar = $calendar ?: new MarketDataTradingCalendarReadService();
        $this->prices = $prices ?: new MarketDataPublishedEodSeriesReadService();
        $this->metrics = $metrics ?: new WatchlistBacktestMetricsService();
        $this->officialEvidence = $officialEvidence ?: new WatchlistBacktestOfficialEvidenceRepository();
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
    }

    public function execute(
        string $approvalReference,
        bool $operatorApproved,
        string $artifactDirectory,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || trim($approvalReference) === '') {
            return $this->blocked('C171_COMPARATIVE_DIAGNOSTIC_OPERATOR_APPROVAL_MISSING');
        }
        foreach (['watchlist_bt_eval', 'watchlist_bt_picks_ws', 'watchlist_bt_universe_ws', 'watchlist_bt_cutoffs_ws', 'watchlist_param_sets'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('C171_COMPARATIVE_DIAGNOSTIC_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }
        foreach (['market_calendar', 'eod_bars', 'market_benchmark_indicators'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('C171_COMPARATIVE_DIAGNOSTIC_MARKET_DATA_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }

        $before = $this->boundaryCounts();
        $artifacts = $this->loadAndVerifyArtifacts($artifactDirectory);
        if (($artifacts['ready'] ?? false) !== true) {
            return $this->blocked((string) ($artifacts['reason_code'] ?? 'C171_COMPARATIVE_DIAGNOSTIC_ARTIFACT_INVALID'), $artifacts);
        }

        $evidence = $this->loadOfficialEvidence($artifacts['artifacts']);
        if (($evidence['ready'] ?? false) !== true) {
            return $this->blocked((string) ($evidence['reason_code'] ?? 'C171_COMPARATIVE_DIAGNOSTIC_EVIDENCE_INVALID'), $evidence);
        }

        $calendar = $this->calendar->resolveTradingDates(self::CANONICAL_IS_FROM, self::CANONICAL_IS_TO);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked('C171_COMPARATIVE_DIAGNOSTIC_CALENDAR_NOT_READY', ['calendar' => $calendar]);
        }
        $calendarDates = array_values($calendar['calendar_dates'] ?? []);
        $priceMap = $this->requiredPriceTickerMap($evidence['pick_rows_by_eval'], $calendarDates);
        if ($priceMap === []) {
            return $this->blocked('C171_COMPARATIVE_DIAGNOSTIC_PRICE_MAP_EMPTY');
        }
        $priceDates = array_keys($priceMap);
        $priceRead = $this->prices->readPublishedSeriesForDateTickerMap(
            $priceDates[0],
            $priceDates[count($priceDates) - 1],
            $priceMap
        );
        if (! ($priceRead['is_ready'] ?? false)) {
            return $this->blocked('C171_COMPARATIVE_DIAGNOSTIC_PUBLISHED_PRICE_READ_NOT_READY', ['price_read' => $priceRead]);
        }

        $regimes = $this->marketRegimes(array_values(array_unique($this->allPickDates($evidence['pick_rows_by_eval']))));
        $replays = $this->replayOfficialPicks(
            $evidence['pick_rows_by_eval'],
            $artifacts['artifacts'],
            $priceRead['series_by_ticker'] ?? [],
            $calendarDates,
            $regimes
        );
        if (($replays['parity']['pass'] ?? false) !== true) {
            return $this->blocked('C171_COMPARATIVE_DIAGNOSTIC_OFFICIAL_PICK_REPLAY_PARITY_FAILED', $replays['parity']);
        }

        $analysis = $this->analyzeComparativeEvidence(
            $evidence['pick_rows_by_eval'],
            $replays['rows_by_eval'],
            $artifacts['artifacts'],
            $regimes
        );
        if (($analysis['ready'] ?? false) !== true) {
            return $this->blocked((string) ($analysis['reason_code'] ?? 'C171_COMPARATIVE_DIAGNOSTIC_ANALYSIS_FAILED'), $analysis);
        }

        $after = $this->boundaryCounts();
        if ($before !== $after) {
            return $this->blocked('C171_COMPARATIVE_DIAGNOSTIC_DATABASE_MUTATION_FORBIDDEN', [
                'before' => $before,
                'after' => $after,
            ]);
        }

        $paths = $this->derivedPaths($outputPath);
        $overwrite = (bool) ($options['overwrite'] ?? false);
        $outputs = [
            'trade_overlap_csv' => $this->writeCsv($paths['trade_overlap_csv'], $analysis['trade_overlap_rows'], $overwrite),
            'added_removed_trades_csv' => $this->writeCsv($paths['added_removed_trades_csv'], $analysis['added_removed_rows'], $overwrite),
            'price_risk_segments_csv' => $this->writeCsv($paths['price_risk_segments_csv'], $analysis['price_risk_rows'], $overwrite),
            'monthly_stability_csv' => $this->writeCsv($paths['monthly_stability_csv'], $analysis['monthly_rows'], $overwrite),
            'score_deciles_csv' => $this->writeCsv($paths['score_deciles_csv'], $analysis['score_decile_rows'], $overwrite),
            'market_regime_csv' => $this->writeCsv($paths['market_regime_csv'], $analysis['market_regime_rows'], $overwrite),
            'exit_distribution_csv' => $this->writeCsv($paths['exit_distribution_csv'], $analysis['exit_distribution_rows'], $overwrite),
            'population_reconciliation_csv' => $this->writeCsv($paths['population_reconciliation_csv'], $analysis['population_reconciliation_rows'], $overwrite),
        ];

        $hypothesisArtifact = [
            'schema_version' => 'C171_COMPARATIVE_R2_HYPOTHESIS_LOCK_V1',
            'source_eval_ids' => array_keys(self::EXPECTED_PARAM_SET_IDS),
            'baseline_eval_id' => self::BASELINE_EVAL_ID,
            'anchor_eval_id' => $analysis['anchor_eval_id'],
            'anchor_param_set_id' => self::EXPECTED_PARAM_SET_IDS[$analysis['anchor_eval_id']],
            'hypothesis_lock_status' => $analysis['hypothesis_lock_status'],
            'primary_focus' => $analysis['primary_focus'],
            'next_semantic_catalog_code' => $analysis['next_semantic_catalog_code'],
            'locked_hypotheses' => $analysis['locked_hypotheses'],
            'rejected_hypotheses' => $analysis['rejected_hypotheses'],
            'anti_overfit_rules' => [
                'max_locked_hypotheses' => 3,
                'ticker_blacklist_forbidden' => true,
                'month_blacklist_forbidden' => true,
                'return_field_as_selection_input_forbidden' => true,
                'oos_read_forbidden' => true,
                'canonical_gate_weakening_forbidden' => true,
                'decision_time_fields_only' => true,
            ],
            'official_pick_replay_parity' => $replays['parity'],
            'created_draft_paramsets' => 0,
            'official_is_runtime_invoked' => false,
            'diagnostic_trade_replay_invoked' => true,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ];
        $hypothesisArtifact['artifact_hash'] = $this->identity->stableHash($hypothesisArtifact);
        $outputs['r2_hypothesis_lock_json'] = $this->writeJson($paths['r2_hypothesis_lock_json'], $hypothesisArtifact, $overwrite);

        $result = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'C171_COMPARATIVE_OFFICIAL_IS_FAILURE_DIAGNOSTIC_COMPLETED',
            'reason_code' => 'C171_R2_HYPOTHESIS_LOCKED_FROM_COMPARATIVE_OFFICIAL_IS_FAILURE_EVIDENCE',
            'approval_reference' => trim($approvalReference),
            'is_from' => self::CANONICAL_IS_FROM,
            'is_to' => self::CANONICAL_IS_TO,
            'source_eval_ids' => array_keys(self::EXPECTED_PARAM_SET_IDS),
            'source_param_set_ids' => array_values(self::EXPECTED_PARAM_SET_IDS),
            'artifact_identity_verification' => $artifacts['identity_summary'],
            'database_manifest_verification' => $evidence['manifest_summary'],
            'official_pick_replay_parity' => $replays['parity'],
            'comparison_summary' => $analysis['comparison_summary'],
            'anchor_eval_id' => $analysis['anchor_eval_id'],
            'anchor_param_set_id' => self::EXPECTED_PARAM_SET_IDS[$analysis['anchor_eval_id']],
            'anchor_reason' => $analysis['anchor_reason'],
            'population_reconciliation' => $analysis['population_reconciliation_summary'],
            'hypothesis_lock_status' => $analysis['hypothesis_lock_status'],
            'primary_focus' => $analysis['primary_focus'],
            'next_semantic_catalog_code' => $analysis['next_semantic_catalog_code'],
            'locked_hypotheses' => $analysis['locked_hypotheses'],
            'rejected_hypotheses' => $analysis['rejected_hypotheses'],
            'r2_hypothesis_locked' => true,
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
            'controlled_rollout_executed' => false,
            'production_ready' => false,
            'database_boundary_counts_before' => $before,
            'database_boundary_counts_after' => $after,
            'outputs' => $outputs,
            'next_recommendation' => 'C171_IMPLEMENT_AND_PERSIST_IMMUTABLE_'.$analysis['primary_focus'].'_C01_DRAFT_CATALOG',
        ];
        $result['artifact_hash'] = $this->identity->stableHash($result);
        $result['write'] = $this->writeJson($outputPath, $result, $overwrite);

        return $result;
    }

    public function analyzeComparativeEvidence(
        array $pickRowsByEval,
        array $replayRowsByEval,
        array $artifacts,
        array $regimes = []
    ): array {
        foreach (array_keys(self::EXPECTED_PARAM_SET_IDS) as $evalId) {
            if (! isset($pickRowsByEval[$evalId], $replayRowsByEval[$evalId], $artifacts[$evalId])) {
                return ['ready' => false, 'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_EVAL_SET_INCOMPLETE', 'missing_eval_id' => $evalId];
            }
        }

        $comparisonSummary = [];
        foreach ($artifacts as $evalId => $artifact) {
            $evaluation = $artifact['is_calibration']['evaluations'][0] ?? [];
            $metrics = is_array($evaluation['metrics'] ?? null) ? $evaluation['metrics'] : [];
            $comparisonSummary[] = [
                'eval_id' => (int) $evalId,
                'param_set_id' => self::EXPECTED_PARAM_SET_IDS[(int) $evalId],
                'row_code' => (string) ($evaluation['row_code'] ?? ((int) $evalId === self::BASELINE_EVAL_ID ? '01_BASELINE' : '')),
                'picks_count' => (int) ($metrics['picks_count'] ?? 0),
                'days_covered' => (int) ($metrics['days_covered'] ?? 0),
                'avg_ret_net' => $this->floatOrNull($metrics['avg_ret_net_top'] ?? null),
                'median_ret_net' => $this->floatOrNull($metrics['median_ret_net_top'] ?? null),
                'p25_ret_net' => $this->floatOrNull($metrics['p25_ret_net_top'] ?? null),
                'win_rate' => $this->floatOrNull($metrics['win_rate_top'] ?? null),
                'month_win_rate_min' => $this->floatOrNull($metrics['month_win_rate_min'] ?? null),
                'month_avg_ret_net_min' => $this->floatOrNull($metrics['month_avg_ret_net_min'] ?? null),
                'period_fail_count' => (int) ($metrics['period_fail_count'] ?? 0),
                'canonical_is_gates_pass' => (bool) ($artifact['canonical_is_gates_pass'] ?? false),
            ];
        }
        usort($comparisonSummary, function (array $a, array $b): int { return $a['eval_id'] <=> $b['eval_id']; });
        $anchor = $this->selectAnchor($comparisonSummary);

        $tradeOverlapRows = [];
        $addedRemovedRows = [];
        $indexedPicks = [];
        foreach ($pickRowsByEval as $evalId => $rows) {
            $indexedPicks[(int) $evalId] = $this->indexRows($rows);
        }
        $evalIds = array_keys($indexedPicks);
        sort($evalIds, SORT_NUMERIC);
        for ($leftIndex = 0; $leftIndex < count($evalIds); $leftIndex++) {
            for ($rightIndex = $leftIndex + 1; $rightIndex < count($evalIds); $rightIndex++) {
                $leftEvalId = (int) $evalIds[$leftIndex];
                $rightEvalId = (int) $evalIds[$rightIndex];
                $left = $indexedPicks[$leftEvalId];
                $right = $indexedPicks[$rightEvalId];
                $retained = array_intersect_key($left, $right);
                $leftOnly = array_diff_key($left, $right);
                $rightOnly = array_diff_key($right, $left);
                $tradeOverlapRows[] = [
                    'comparison_scope' => $leftEvalId === self::BASELINE_EVAL_ID ? 'BASELINE_TO_CANDIDATE' : 'PAIRWISE_CANDIDATE',
                    'left_eval_id' => $leftEvalId,
                    'left_param_set_id' => self::EXPECTED_PARAM_SET_IDS[$leftEvalId],
                    'right_eval_id' => $rightEvalId,
                    'right_param_set_id' => self::EXPECTED_PARAM_SET_IDS[$rightEvalId],
                    'left_count' => count($left),
                    'right_count' => count($right),
                    'retained_count' => count($retained),
                    'left_only_count' => count($leftOnly),
                    'right_only_count' => count($rightOnly),
                    'jaccard_ratio' => $this->ratio(count($retained), count($left) + count($right) - count($retained)),
                    'left_only_avg_ret_net' => $this->avgField(array_values($leftOnly), 'ret_net'),
                    'right_only_avg_ret_net' => $this->avgField(array_values($rightOnly), 'ret_net'),
                    'left_only_total_ret_net' => $this->sumField(array_values($leftOnly), 'ret_net'),
                    'right_only_total_ret_net' => $this->sumField(array_values($rightOnly), 'ret_net'),
                    'retained_return_parity' => $this->returnsEqual($retained, array_intersect_key($right, $left)),
                ];
            }
        }

        $baseline = $indexedPicks[self::BASELINE_EVAL_ID];
        foreach ($indexedPicks as $evalId => $candidate) {
            if ((int) $evalId === self::BASELINE_EVAL_ID) {
                continue;
            }
            $removed = array_diff_key($baseline, $candidate);
            $added = array_diff_key($candidate, $baseline);
            foreach ($removed as $row) {
                $addedRemovedRows[] = $this->changeRow((int) $evalId, 'REMOVED_VS_BASELINE', $row, null);
            }
            foreach ($added as $row) {
                $addedRemovedRows[] = $this->changeRow((int) $evalId, 'ADDED_VS_BASELINE', null, $row);
            }
        }

        $monthlyRows = [];
        $scoreDecileRows = [];
        $priceRiskRows = [];
        $marketRegimeRows = [];
        $exitDistributionRows = [];
        $populationRows = [];
        foreach ($replayRowsByEval as $evalId => $rows) {
            foreach ($this->monthlySegments((int) $evalId, $rows) as $row) $monthlyRows[] = $row;
            foreach ($this->scoreDeciles((int) $evalId, $rows) as $row) $scoreDecileRows[] = $row;
            foreach ($this->priceRiskSegments((int) $evalId, $rows) as $row) $priceRiskRows[] = $row;
            foreach ($this->regimeSegments((int) $evalId, $rows) as $row) $marketRegimeRows[] = $row;
            foreach ($this->exitDistribution((int) $evalId, $rows) as $row) $exitDistributionRows[] = $row;

            $artifact = $artifacts[$evalId];
            $evaluation = $artifact['is_calibration']['evaluations'][0] ?? [];
            $officialCount = count($pickRowsByEval[$evalId]);
            $metricsCount = (int) ($evaluation['metrics']['picks_count'] ?? 0);
            $allEvaluatedCount = (int) ($evaluation['trade_evidence']['evaluated_trade_count'] ?? 0);
            $populationRows[] = [
                'eval_id' => (int) $evalId,
                'param_set_id' => self::EXPECTED_PARAM_SET_IDS[(int) $evalId],
                'official_picks_db_count' => $officialCount,
                'metrics_picks_count' => $metricsCount,
                'trade_evidence_evaluated_trade_count' => $allEvaluatedCount,
                'official_picks_match_metrics' => $officialCount === $metricsCount,
                'population_difference' => $allEvaluatedCount - $officialCount,
                'explanation_code' => 'OFFICIAL_PICKS_EQUALS_METRICS_READY_TOP_ONLY;TRADE_EVIDENCE_INCLUDES_ALL_EVALUATED_BUCKETS',
            ];
        }

        $hypothesis = $this->lockHypotheses(
            $anchor['eval_id'],
            $priceRiskRows,
            $marketRegimeRows,
            $scoreDecileRows,
            $exitDistributionRows,
            $tradeOverlapRows,
            $pickRowsByEval
        );
        if (($hypothesis['ready'] ?? false) !== true) {
            return $hypothesis;
        }

        return [
            'ready' => true,
            'comparison_summary' => $comparisonSummary,
            'anchor_eval_id' => $anchor['eval_id'],
            'anchor_reason' => $anchor['reason'],
            'trade_overlap_rows' => $tradeOverlapRows,
            'added_removed_rows' => $addedRemovedRows,
            'monthly_rows' => $monthlyRows,
            'score_decile_rows' => $scoreDecileRows,
            'price_risk_rows' => $priceRiskRows,
            'market_regime_rows' => $marketRegimeRows,
            'exit_distribution_rows' => $exitDistributionRows,
            'population_reconciliation_rows' => $populationRows,
            'population_reconciliation_summary' => [
                'all_official_picks_match_metrics_picks_count' => count(array_filter($populationRows, function (array $row): bool { return ! $row['official_picks_match_metrics']; })) === 0,
                'population_contract' => 'OFFICIAL_PICKS_EQUALS_METRICS_READY_TOP_ONLY;TRADE_EVIDENCE_INCLUDES_ALL_EVALUATED_BUCKETS',
            ],
            'hypothesis_lock_status' => 'LOCKED',
            'primary_focus' => $hypothesis['primary_focus'],
            'next_semantic_catalog_code' => $hypothesis['next_semantic_catalog_code'],
            'locked_hypotheses' => $hypothesis['locked_hypotheses'],
            'rejected_hypotheses' => $hypothesis['rejected_hypotheses'],
        ];
    }

    private function loadAndVerifyArtifacts(string $directory): array
    {
        $directory = rtrim($directory, '/\\');
        $artifacts = [];
        $summary = [];
        foreach (self::EVAL_ARTIFACTS as $evalId => $filename) {
            $path = $directory.DIRECTORY_SEPARATOR.$filename;
            if (! is_file($path) || ! is_readable($path)) {
                return ['ready' => false, 'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_ARTIFACT_MISSING', 'path' => $path];
            }
            $fileSha1 = strtoupper((string) sha1_file($path));
            if ($fileSha1 !== self::EXPECTED_ARTIFACT_FILE_SHA1[$evalId]) {
                return [
                    'ready' => false,
                    'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_ARTIFACT_FILE_SHA1_MISMATCH',
                    'path' => $path,
                    'eval_id' => $evalId,
                    'expected_file_sha1' => self::EXPECTED_ARTIFACT_FILE_SHA1[$evalId],
                    'actual_file_sha1' => $fileSha1,
                ];
            }
            $decoded = json_decode((string) file_get_contents($path), true);
            if (! is_array($decoded)) {
                return ['ready' => false, 'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_ARTIFACT_JSON_INVALID', 'path' => $path];
            }
            $evaluation = $decoded['is_calibration']['evaluations'][0] ?? [];
            $expectedArtifactHash = (string) ($decoded['artifact_hash'] ?? '');
            $artifactHashPayload = $decoded;
            unset($artifactHashPayload['artifact_hash'], $artifactHashPayload['write']);
            $actualArtifactHash = $this->identity->stableHash($artifactHashPayload);
            if ($expectedArtifactHash === '' || $expectedArtifactHash !== $actualArtifactHash) {
                return [
                    'ready' => false,
                    'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_ARTIFACT_HASH_MISMATCH',
                    'path' => $path,
                    'eval_id' => $evalId,
                    'expected_artifact_hash' => $expectedArtifactHash,
                    'actual_artifact_hash' => $actualArtifactHash,
                ];
            }
            if ((int) ($decoded['param_set_id'] ?? 0) !== self::EXPECTED_PARAM_SET_IDS[$evalId]
                || (int) ($evaluation['eval_id'] ?? 0) !== $evalId
                || (string) ($decoded['is_from'] ?? '') !== self::CANONICAL_IS_FROM
                || (string) ($decoded['is_to'] ?? '') !== self::CANONICAL_IS_TO
                || ($decoded['strict_is_boundary'] ?? false) !== true
                || ($decoded['canonical_is_gates_pass'] ?? true) !== false
                || ($decoded['future_derived_route_used'] ?? true) !== false
                || ($decoded['execution_route_proof']['pass'] ?? false) !== true
                || ($decoded['oos_runtime_invoked'] ?? true) !== false
                || ($decoded['paramset_promoted'] ?? true) !== false
                || ($decoded['plan_run_created'] ?? true) !== false
                || ($decoded['production_ready'] ?? true) !== false
                || (string) ($decoded['status'] ?? '') !== 'C171_VERSIONED_OFFICIAL_IS_EVIDENCE_PERSISTED_CANONICAL_GATES_FAILED_OOS_NOT_RUN') {
                return ['ready' => false, 'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_ARTIFACT_IDENTITY_MISMATCH', 'path' => $path, 'eval_id' => $evalId];
            }
            $artifacts[$evalId] = $decoded;
            $summary[] = [
                'eval_id' => $evalId,
                'param_set_id' => self::EXPECTED_PARAM_SET_IDS[$evalId],
                'params_hash' => (string) ($decoded['params_hash'] ?? ''),
                'artifact_hash' => (string) ($decoded['artifact_hash'] ?? ''),
                'file_sha1' => $fileSha1,
                'path' => $path,
            ];
        }
        return ['ready' => true, 'artifacts' => $artifacts, 'identity_summary' => $summary];
    }

    private function loadOfficialEvidence(array $artifacts): array
    {
        $rowsByEval = [];
        $manifestSummary = [];
        foreach (array_keys(self::EXPECTED_PARAM_SET_IDS) as $evalId) {
            $eval = DB::table('watchlist_bt_eval')->where('eval_id', $evalId)->first();
            if ($eval === null) {
                return ['ready' => false, 'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_EVAL_NOT_FOUND', 'eval_id' => $evalId];
            }
            $evalRow = (array) $eval;
            $artifact = $artifacts[$evalId];
            $evaluation = $artifact['is_calibration']['evaluations'][0] ?? [];
            foreach (['paramset_hash', 'eval_model_hash', 'implementation_hash', 'evidence_manifest_hash'] as $field) {
                $artifactValue = $field === 'evidence_manifest_hash'
                    ? ($evaluation['official_evidence_manifest'][$field] ?? null)
                    : ($evaluation[$field] ?? ($artifact[$field] ?? null));
                if ((string) ($evalRow[$field] ?? '') !== (string) $artifactValue) {
                    return ['ready' => false, 'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_EVAL_IDENTITY_MISMATCH', 'eval_id' => $evalId, 'field' => $field];
                }
            }
            $manifest = $this->officialEvidence->databaseManifest($evalId);
            $artifactManifest = $evaluation['official_evidence_manifest'] ?? [];
            if ($this->identity->stableHash($manifest) !== $this->identity->stableHash($artifactManifest)) {
                return ['ready' => false, 'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_DATABASE_MANIFEST_MISMATCH', 'eval_id' => $evalId, 'database_manifest' => $manifest, 'artifact_manifest' => $artifactManifest];
            }

            $rows = DB::table('watchlist_bt_picks_ws as p')
                ->leftJoin('watchlist_bt_universe_ws as u', function ($join): void {
                    $join->on('u.eval_id', '=', 'p.eval_id')
                        ->on('u.asof_eod_date', '=', 'p.asof_eod_date')
                        ->on('u.ticker_id', '=', 'p.ticker_id');
                })
                ->where('p.eval_id', $evalId)
                ->orderBy('p.asof_eod_date')
                ->orderBy('p.ticker_id')
                ->get([
                    'p.eval_id', 'p.param_id', 'p.asof_eod_date', 'p.ticker_id', 'p.ticker_code', 'p.bucket_code',
                    'p.ret_net', 'p.score_total', 'p.source_publication_id', 'p.source_publication_version', 'p.source_run_id',
                    'u.atr14_pct', 'u.dv20_idr', 'u.vol_ratio', 'u.reason_code as universe_reason_code',
                ])
                ->map(function ($row): array { return (array) $row; })
                ->all();
            if (count($rows) !== (int) ($manifest['picks_count'] ?? -1)) {
                return ['ready' => false, 'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_PICK_COUNT_MISMATCH', 'eval_id' => $evalId, 'database_count' => count($rows), 'manifest_count' => $manifest['picks_count'] ?? null];
            }
            foreach ($rows as &$row) {
                $row['eval_id'] = (int) $row['eval_id'];
                $row['param_id'] = (int) $row['param_id'];
                $row['ticker_id'] = (int) $row['ticker_id'];
                foreach (['ret_net', 'score_total', 'atr14_pct', 'vol_ratio'] as $field) $row[$field] = $this->floatOrNull($row[$field] ?? null);
                $row['dv20_idr'] = $row['dv20_idr'] === null ? null : (int) $row['dv20_idr'];
            }
            unset($row);
            $rowsByEval[$evalId] = $rows;
            $manifestSummary[] = ['eval_id' => $evalId, 'manifest' => $manifest, 'pass' => true];
        }
        return ['ready' => true, 'pick_rows_by_eval' => $rowsByEval, 'manifest_summary' => $manifestSummary];
    }

    private function replayOfficialPicks(array $pickRowsByEval, array $artifacts, array $series, array $calendarDates, array $regimes): array
    {
        $rowsByEval = [];
        $mismatches = [];
        $checked = 0;
        foreach ($pickRowsByEval as $evalId => $rows) {
            $trades = [];
            foreach ($rows as $row) {
                $trades[] = [
                    'trade_date' => (string) $row['asof_eod_date'],
                    'ticker_id' => (int) $row['ticker_id'],
                    'ticker' => (string) $row['ticker_code'],
                    'ticker_code' => (string) $row['ticker_code'],
                    'bucket_code' => (string) $row['bucket_code'],
                    'score_total' => $row['score_total'],
                    'atr14_pct' => $row['atr14_pct'],
                ];
            }
            $snapshot = $artifacts[$evalId]['is_calibration']['evaluations'][0]['paramset_snapshot'] ?? [];
            $metrics = $this->metrics->buildMetrics([
                'trades' => $trades,
                'replay_window' => ['trade_dates' => array_values(array_unique(array_column($trades, 'trade_date')))],
                'paramset_snapshot' => $snapshot,
                'diagnostics' => [],
            ], $series, $calendarDates);
            $evaluated = is_array($metrics['evaluated_trades'] ?? null) ? $metrics['evaluated_trades'] : [];
            $evalIndex = [];
            foreach ($evaluated as $item) {
                if (! is_array($item)) continue;
                $evalIndex[$this->pickKey($item)] = $item;
            }
            $detailRows = [];
            foreach ($rows as $row) {
                $key = $this->pickKey($row);
                $item = $evalIndex[$key] ?? null;
                $checked++;
                if (! is_array($item) || ($item['metrics_ready'] ?? false) !== true || ! is_numeric($item['ret_net'] ?? null)) {
                    $mismatches[] = ['eval_id' => (int) $evalId, 'key' => $key, 'reason' => 'REPLAY_ROW_NOT_METRICS_READY'];
                    continue;
                }
                $officialRet = round((float) $row['ret_net'], 6);
                $replayRet = round((float) $item['ret_net'], 6);
                if (abs($officialRet - $replayRet) > 0.000001) {
                    $mismatches[] = ['eval_id' => (int) $evalId, 'key' => $key, 'reason' => 'RET_NET_MISMATCH', 'official_ret_net' => $officialRet, 'replay_ret_net' => $replayRet];
                }
                foreach ([
                    'source_publication_id' => 'entry_publication_id',
                    'source_publication_version' => 'entry_publication_version',
                    'source_run_id' => 'entry_run_id',
                ] as $officialField => $replayField) {
                    if ((int) ($row[$officialField] ?? 0) !== (int) ($item[$replayField] ?? 0)) {
                        $mismatches[] = [
                            'eval_id' => (int) $evalId,
                            'key' => $key,
                            'reason' => 'ENTRY_LINEAGE_MISMATCH',
                            'field' => $officialField,
                            'official_value' => $row[$officialField] ?? null,
                            'replay_value' => $item[$replayField] ?? null,
                        ];
                    }
                }
                $detailRows[] = array_merge($row, $item, [
                    'official_ret_net' => $officialRet,
                    'replay_ret_net' => $replayRet,
                    'ret_net_parity' => abs($officialRet - $replayRet) <= 0.000001,
                    'market_regime' => $regimes[(string) $row['asof_eod_date']]['regime'] ?? 'UNKNOWN',
                    'market_index_roc20' => $regimes[(string) $row['asof_eod_date']]['roc_20'] ?? null,
                    'market_index_ma20_slope_pct' => $regimes[(string) $row['asof_eod_date']]['ma20_slope_pct'] ?? null,
                ]);
            }
            $rowsByEval[(int) $evalId] = $detailRows;
        }
        return [
            'rows_by_eval' => $rowsByEval,
            'parity' => [
                'pass' => $mismatches === [],
                'checked_count' => $checked,
                'mismatch_count' => count($mismatches),
                'mismatch_sample' => array_slice($mismatches, 0, 20),
                'contract' => 'CURRENT_PUBLISHED_PRICE_DIAGNOSTIC_REPLAY_MUST_MATCH_IMMUTABLE_OFFICIAL_PICK_RET_NET_TO_6DP_AND_ENTRY_PUBLICATION_LINEAGE',
            ],
        ];
    }

    private function requiredPriceTickerMap(array $rowsByEval, array $calendarDates): array
    {
        $dateIndex = array_flip($calendarDates);
        $map = [];
        foreach ($rowsByEval as $rows) {
            foreach ($rows as $row) {
                $signal = (string) $row['asof_eod_date'];
                if (! isset($dateIndex[$signal])) continue;
                $start = (int) $dateIndex[$signal] + 1;
                for ($i = $start; $i < min(count($calendarDates), $start + self::HOLDING_DAYS); $i++) {
                    $date = $calendarDates[$i];
                    $ticker = strtoupper((string) $row['ticker_code']);
                    $map[$date][$ticker] = $ticker;
                }
            }
        }
        ksort($map, SORT_STRING);
        foreach ($map as &$codes) {
            ksort($codes, SORT_STRING);
            $codes = array_values($codes);
        }
        unset($codes);
        return $map;
    }

    private function marketRegimes(array $dates): array
    {
        if (! Schema::hasColumn('market_benchmark_indicators', 'benchmark_code')
            || ! Schema::hasColumn('market_benchmark_indicators', 'trade_date')
            || ! Schema::hasColumn('market_benchmark_indicators', 'roc_20')
            || ! Schema::hasColumn('market_benchmark_indicators', 'ma20_slope_pct')
            || ! Schema::hasColumn('market_benchmark_indicators', 'indicator_set_version')
            || ! Schema::hasColumn('market_benchmark_indicators', 'is_valid')) {
            return [];
        }
        $indicatorSetVersion = (string) config('market_data.indicators.set_version', 'v1');
        $map = [];
        foreach (array_chunk($dates, 400) as $chunk) {
            $rows = DB::table('market_benchmark_indicators')
                ->where('benchmark_code', 'IHSG')
                ->where('indicator_set_version', $indicatorSetVersion)
                ->where('is_valid', 1)
                ->whereIn('trade_date', $chunk)
                ->orderBy('trade_date')
                ->get(['trade_date', 'roc_20', 'ma20_slope_pct', 'indicator_set_version']);
            foreach ($rows as $row) {
                $roc = $this->floatOrNull($row->roc_20 ?? null);
                $slope = $this->floatOrNull($row->ma20_slope_pct ?? null);
                $regime = 'UNKNOWN';
                if ($roc !== null && $slope !== null) {
                    if ($roc >= 0 && $slope >= 0) $regime = 'STRONG';
                    elseif ($roc < 0 && $slope < 0) $regime = 'WEAK';
                    else $regime = 'MIXED';
                }
                $map[(string) $row->trade_date] = ['regime' => $regime, 'roc_20' => $roc, 'ma20_slope_pct' => $slope];
            }
        }
        return $map;
    }

    private function selectAnchor(array $summary): array
    {
        $eligible = array_values(array_filter($summary, function (array $row): bool {
            return $row['eval_id'] !== self::BASELINE_EVAL_ID
                && $row['picks_count'] >= self::MIN_TRADES
                && $row['days_covered'] >= self::MIN_DAYS_COVERED;
        }));
        usort($eligible, function (array $a, array $b): int {
            foreach (['avg_ret_net', 'median_ret_net', 'p25_ret_net', 'month_avg_ret_net_min', 'win_rate'] as $field) {
                $cmp = ((float) $b[$field]) <=> ((float) $a[$field]);
                if ($cmp !== 0) return $cmp;
            }
            return $a['eval_id'] <=> $b['eval_id'];
        });
        $winner = $eligible[0] ?? ['eval_id' => self::ANCHOR_EXPECTED_EVAL_ID];
        return [
            'eval_id' => (int) $winner['eval_id'],
            'reason' => 'HIGHEST_POSITIVE_AVERAGE_RETURN_THEN_ROBUST_RETURN_AND_STABILITY_TIEBREAK_AMONG_COVERAGE_VALID_FAILED_IS_CANDIDATES',
        ];
    }

    private function lockHypotheses(int $anchorEvalId, array $priceRows, array $regimeRows, array $scoreRows, array $exitRows, array $overlapRows, array $pickRowsByEval): array
    {
        $locked = [];
        $rejected = [];
        $anchorPrice = array_values(array_filter($priceRows, function (array $row) use ($anchorEvalId): bool { return $row['eval_id'] === $anchorEvalId; }));
        $low = $this->findSegment($anchorPrice, 'ENTRY_PRICE_BAND', 'LT_200');
        $high = $this->findSegment($anchorPrice, 'ENTRY_PRICE_BAND', 'GE_200');
        if ($low !== null && $high !== null && ((float) $low['p25_ret_net'] < (float) $high['p25_ret_net'] - 0.01 || (float) $low['avg_tick_risk_expansion_pct'] > 0.01)) {
            $locked[] = [
                'rank' => count($locked) + 1,
                'hypothesis_code' => 'LOW_PRICE_TICK_RISK_DECISION_TIME_GUARD',
                'focus' => 'LOW_PRICE_EXECUTION_QUALITY',
                'evidence' => ['low_price_segment' => $low, 'comparison_segment' => $high],
                'allowed_design_direction' => 'Use signal-date close/price-band or decision-time tick-risk proxy; never use future entry return or ticker blacklist.',
            ];
        } else {
            $rejected[] = ['hypothesis_code' => 'LOW_PRICE_TICK_RISK_DECISION_TIME_GUARD', 'reason' => 'MATERIAL_LOW_PRICE_DISADVANTAGE_NOT_PROVEN'];
        }

        $anchorRegimes = array_values(array_filter($regimeRows, function (array $row) use ($anchorEvalId): bool { return $row['eval_id'] === $anchorEvalId; }));
        $weak = $this->findSegment($anchorRegimes, 'MARKET_REGIME', 'WEAK');
        $strong = $this->findSegment($anchorRegimes, 'MARKET_REGIME', 'STRONG');
        if (count($locked) < 3 && $weak !== null && $strong !== null && (int) $weak['trade_count'] >= 40 && (float) $weak['avg_ret_net'] < (float) $strong['avg_ret_net'] - 0.01) {
            $locked[] = [
                'rank' => count($locked) + 1,
                'hypothesis_code' => 'IHSG_DECISION_TIME_REGIME_GUARD',
                'focus' => 'MARKET_REGIME_QUALITY',
                'evidence' => ['weak_regime' => $weak, 'strong_regime' => $strong],
                'allowed_design_direction' => 'Use exact signal-date IHSG roc_20 and ma20_slope_pct only; no future regime lookup.',
            ];
        } else {
            $rejected[] = ['hypothesis_code' => 'IHSG_DECISION_TIME_REGIME_GUARD', 'reason' => 'MATERIAL_WEAK_VS_STRONG_REGIME_GAP_NOT_PROVEN_OR_SAMPLE_TOO_SMALL'];
        }

        $anchorScores = array_values(array_filter($scoreRows, function (array $row) use ($anchorEvalId): bool { return $row['eval_id'] === $anchorEvalId; }));
        $monotonic = $this->scoreMonotonicity($anchorScores);
        if (count($locked) < 3 && ! $monotonic['pass']) {
            $locked[] = [
                'rank' => count($locked) + 1,
                'hypothesis_code' => 'SCORE_RANKING_RECALIBRATION',
                'focus' => 'SCORE_RANKING_QUALITY',
                'evidence' => $monotonic,
                'allowed_design_direction' => 'Reweight or reshape decision-time breakout/momentum/risk/volume scores in isolated candidates; preserve canonical gates.',
            ];
        } else {
            $rejected[] = ['hypothesis_code' => 'SCORE_RANKING_RECALIBRATION', 'reason' => 'SCORE_DECILES_SHOW_ACCEPTABLE_MONOTONICITY_OR_LOCK_LIMIT_REACHED'];
        }

        $anchorExit = array_values(array_filter($exitRows, function (array $row) use ($anchorEvalId): bool { return $row['eval_id'] === $anchorEvalId; }));
        $stop = $this->findSegment($anchorExit, 'EXIT_REASON', 'WATCHLIST_BACKTEST_EXIT_STOP');
        if (count($locked) < 3 && $stop !== null && (float) $stop['share'] > 0.50) {
            $locked[] = [
                'rank' => count($locked) + 1,
                'hypothesis_code' => 'EXIT_DOWNSIDE_CONTROL_REVIEW',
                'focus' => 'EXIT_DOWNSIDE_CONTROL',
                'evidence' => $stop,
                'allowed_design_direction' => 'Test isolated execution-model candidates with new implementation identity; do not reinterpret existing eval_model evidence.',
            ];
        } else {
            $rejected[] = ['hypothesis_code' => 'EXIT_DOWNSIDE_CONTROL_REVIEW', 'reason' => 'STOP_SHARE_NOT_DOMINANT_OR_LOCK_LIMIT_REACHED'];
        }

        $a = $this->indexRows($pickRowsByEval[189] ?? []);
        $e = $this->indexRows($pickRowsByEval[193] ?? []);
        $redundant = array_keys($a) === array_keys($e) && $this->returnsEqual($a, $e);
        $rejected[] = [
            'hypothesis_code' => 'MAX_VOL_RATIO_5_TO_3_AS_STANDALONE_FOCUS',
            'reason' => $redundant ? 'EVAL_189_AND_193_OFFICIAL_PICK_POPULATIONS_AND_RETURNS_ARE_IDENTICAL' : 'NO_STANDALONE_INCREMENTAL_BENEFIT_PROVEN',
        ];

        if ($locked === []) {
            return [
                'ready' => false,
                'reason_code' => 'C171_COMPARATIVE_DIAGNOSTIC_NO_MATERIAL_R2_HYPOTHESIS',
                'rejected_hypotheses' => $rejected,
            ];
        }
        $primaryFocus = (string) $locked[0]['focus'];
        return [
            'ready' => true,
            'primary_focus' => $primaryFocus,
            'next_semantic_catalog_code' => 'WS_BT_GRID_'.$primaryFocus.'_C01_2026_07',
            'locked_hypotheses' => array_slice($locked, 0, 3),
            'rejected_hypotheses' => $rejected,
        ];
    }

    private function monthlySegments(int $evalId, array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) $groups[substr((string) $row['asof_eod_date'], 0, 7)][] = $row;
        ksort($groups, SORT_STRING);
        $out = [];
        foreach ($groups as $month => $items) {
            $metrics = $this->segmentMetrics($items);
            $out[] = array_merge(['eval_id' => $evalId, 'param_set_id' => self::EXPECTED_PARAM_SET_IDS[$evalId], 'month' => $month], $metrics, [
                'monthly_win_rate_gate_pass' => $metrics['win_rate'] >= self::MIN_MONTH_WIN_RATE,
                'monthly_average_gate_pass' => $metrics['avg_ret_net'] >= self::MIN_MONTH_AVG,
                'period_pass' => $metrics['win_rate'] >= self::MIN_MONTH_WIN_RATE && $metrics['avg_ret_net'] >= self::MIN_MONTH_AVG,
            ]);
        }
        return $out;
    }

    private function scoreDeciles(int $evalId, array $rows): array
    {
        usort($rows, function (array $a, array $b): int {
            $cmp = ((float) $b['score_total']) <=> ((float) $a['score_total']);
            if ($cmp !== 0) return $cmp;
            $cmp = strcmp((string) $a['asof_eod_date'], (string) $b['asof_eod_date']);
            return $cmp !== 0 ? $cmp : ((int) $a['ticker_id'] <=> (int) $b['ticker_id']);
        });
        $count = count($rows);
        $groups = [];
        foreach ($rows as $index => $row) {
            $decile = min(10, (int) floor(($index * 10) / max(1, $count)) + 1);
            $groups[$decile][] = $row;
        }
        $out = [];
        foreach ($groups as $decile => $items) {
            $scores = array_column($items, 'score_total');
            $out[] = array_merge([
                'eval_id' => $evalId,
                'param_set_id' => self::EXPECTED_PARAM_SET_IDS[$evalId],
                'segment_type' => 'SCORE_DECILE_DESC',
                'segment' => 'D'.str_pad((string) $decile, 2, '0', STR_PAD_LEFT),
                'score_min' => min($scores),
                'score_max' => max($scores),
            ], $this->segmentMetrics($items));
        }
        return $out;
    }

    private function priceRiskSegments(int $evalId, array $rows): array
    {
        $aggregateGroups = [];
        $detailGroups = [];
        foreach ($rows as $row) {
            $entry = $this->floatOrNull($row['entry_price'] ?? null);
            $aggregateBand = $entry === null ? 'UNKNOWN' : ($entry < 200 ? 'LT_200' : 'GE_200');
            $detailBand = $this->entryPriceDetailBand($entry);
            $aggregateGroups[$aggregateBand][] = $row;
            $detailGroups[$detailBand][] = $row;
        }

        $out = [];
        foreach ([
            'ENTRY_PRICE_BAND' => $aggregateGroups,
            'ENTRY_PRICE_BAND_DETAIL' => $detailGroups,
        ] as $segmentType => $groups) {
            ksort($groups, SORT_STRING);
            foreach ($groups as $band => $items) {
                $theoreticalRisks = [];
                $normalizedRisks = [];
                $expansions = [];
                foreach ($items as $item) {
                    $entry = $this->floatOrNull($item['entry_price'] ?? null);
                    $theoretical = $this->floatOrNull($item['stop_price'] ?? null);
                    $trigger = $this->floatOrNull($item['stop_trigger_price'] ?? null);
                    if ($entry !== null && $entry > 0 && $theoretical !== null && $trigger !== null) {
                        $theoreticalRisk = max(0.0, ($entry - $theoretical) / $entry);
                        $normalizedRisk = max(0.0, ($entry - $trigger) / $entry);
                        $theoreticalRisks[] = $theoreticalRisk;
                        $normalizedRisks[] = $normalizedRisk;
                        $expansions[] = max(0.0, $normalizedRisk - $theoreticalRisk);
                    }
                }
                $out[] = array_merge([
                    'eval_id' => $evalId,
                    'param_set_id' => self::EXPECTED_PARAM_SET_IDS[$evalId],
                    'segment_type' => $segmentType,
                    'segment' => $band,
                ], $this->segmentMetrics($items), [
                    'avg_theoretical_stop_risk_pct' => $this->avgNumbers($theoreticalRisks),
                    'avg_normalized_stop_risk_pct' => $this->avgNumbers($normalizedRisks),
                    'avg_tick_risk_expansion_pct' => $this->avgNumbers($expansions),
                    'max_tick_risk_expansion_pct' => $expansions === [] ? null : max($expansions),
                    'stop_exit_share' => $this->shareReason($items, 'WATCHLIST_BACKTEST_EXIT_STOP'),
                    'gap_through_stop_share' => $this->shareFillRule($items, 'GAP_THROUGH_STOP_AT_OPEN'),
                ]);
            }
        }
        return $out;
    }

    private function entryPriceDetailBand(?float $entry): string
    {
        if ($entry === null) return 'UNKNOWN';
        if ($entry < 50) return 'LT_50';
        if ($entry < 100) return '50_TO_99';
        if ($entry < 200) return '100_TO_199';
        if ($entry < 500) return '200_TO_499';
        if ($entry < 1000) return '500_TO_999';
        return 'GE_1000';
    }

    private function regimeSegments(int $evalId, array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) $groups[(string) ($row['market_regime'] ?? 'UNKNOWN')][] = $row;
        $out = [];
        foreach ($groups as $regime => $items) {
            $out[] = array_merge([
                'eval_id' => $evalId,
                'param_set_id' => self::EXPECTED_PARAM_SET_IDS[$evalId],
                'segment_type' => 'MARKET_REGIME',
                'segment' => $regime,
            ], $this->segmentMetrics($items));
        }
        return $out;
    }

    private function exitDistribution(int $evalId, array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) $groups[(string) ($row['exit_reason_code'] ?? 'UNKNOWN')][] = $row;
        $total = count($rows);
        $out = [];
        foreach ($groups as $reason => $items) {
            $out[] = array_merge([
                'eval_id' => $evalId,
                'param_set_id' => self::EXPECTED_PARAM_SET_IDS[$evalId],
                'segment_type' => 'EXIT_REASON',
                'segment' => $reason,
                'share' => $this->ratio(count($items), $total),
            ], $this->segmentMetrics($items));
        }
        return $out;
    }

    private function segmentMetrics(array $items): array
    {
        $returns = array_values(array_map(function (array $row): float { return (float) ($row['official_ret_net'] ?? $row['ret_net'] ?? 0.0); }, $items));
        sort($returns, SORT_NUMERIC);
        $wins = count(array_filter($returns, function (float $value): bool { return $value > 0; }));
        $dates = array_values(array_unique(array_map(function (array $row): string { return (string) ($row['asof_eod_date'] ?? ''); }, $items)));
        return [
            'trade_count' => count($items),
            'days_covered' => count(array_filter($dates)),
            'avg_ret_net' => $returns === [] ? null : array_sum($returns) / count($returns),
            'median_ret_net' => $this->quantile($returns, 0.50),
            'p25_ret_net' => $this->quantile($returns, 0.25),
            'p75_ret_net' => $this->quantile($returns, 0.75),
            'win_rate' => $this->ratio($wins, count($returns)),
            'min_ret_net' => $returns === [] ? null : min($returns),
            'max_ret_net' => $returns === [] ? null : max($returns),
        ];
    }

    private function scoreMonotonicity(array $rows): array
    {
        usort($rows, function (array $a, array $b): int { return strcmp((string) $a['segment'], (string) $b['segment']); });
        $averages = array_values(array_map(function (array $row): float { return (float) $row['avg_ret_net']; }, $rows));
        $violations = 0;
        for ($i = 1; $i < count($averages); $i++) {
            if ($averages[$i] > $averages[$i - 1] + 0.0025) $violations++;
        }
        return [
            'pass' => $violations <= 2,
            'ordered_decile_average_returns_high_to_low_score' => $averages,
            'material_monotonicity_violation_count' => $violations,
            'rule' => 'Higher-score deciles should not materially underperform lower-score deciles repeatedly.',
        ];
    }

    private function changeRow(int $candidateEvalId, string $changeType, ?array $baseline, ?array $candidate): array
    {
        $row = $candidate ?: $baseline ?: [];
        return [
            'baseline_eval_id' => self::BASELINE_EVAL_ID,
            'candidate_eval_id' => $candidateEvalId,
            'candidate_param_set_id' => self::EXPECTED_PARAM_SET_IDS[$candidateEvalId],
            'change_type' => $changeType,
            'asof_eod_date' => $row['asof_eod_date'] ?? null,
            'ticker_id' => $row['ticker_id'] ?? null,
            'ticker_code' => $row['ticker_code'] ?? null,
            'baseline_ret_net' => $baseline['ret_net'] ?? null,
            'candidate_ret_net' => $candidate['ret_net'] ?? null,
            'baseline_score_total' => $baseline['score_total'] ?? null,
            'candidate_score_total' => $candidate['score_total'] ?? null,
            'atr14_pct' => $row['atr14_pct'] ?? null,
            'dv20_idr' => $row['dv20_idr'] ?? null,
            'vol_ratio' => $row['vol_ratio'] ?? null,
        ];
    }

    private function indexRows(array $rows): array
    {
        $index = [];
        foreach ($rows as $row) $index[$this->pickKey($row)] = $row;
        ksort($index, SORT_STRING);
        return $index;
    }

    private function pickKey(array $row): string
    {
        return (string) ($row['asof_eod_date'] ?? $row['trade_date'] ?? '').'|'.(int) ($row['ticker_id'] ?? 0);
    }

    private function allPickDates(array $rowsByEval): array
    {
        $dates = [];
        foreach ($rowsByEval as $rows) foreach ($rows as $row) $dates[] = (string) $row['asof_eod_date'];
        return $dates;
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
            'watchlist_plan_items',
            'watchlist_recommendations',
            'watchlist_confirm_checks',
            'watchlist_confirm_items',
            'watchlist_confirm_snapshots',
            'watchlist_confirm_snapshot_items',
        ] as $table) {
            $counts[$table] = Schema::hasTable($table) ? DB::table($table)->count() : null;
        }
        return $counts;
    }

    private function derivedPaths(string $outputPath): array
    {
        $base = preg_replace('/\.json$/i', '', $outputPath);
        return [
            'trade_overlap_csv' => $base.'-trade-overlap.csv',
            'added_removed_trades_csv' => $base.'-added-removed-trades.csv',
            'price_risk_segments_csv' => $base.'-price-risk-segments.csv',
            'monthly_stability_csv' => $base.'-monthly-stability.csv',
            'score_deciles_csv' => $base.'-score-deciles.csv',
            'market_regime_csv' => $base.'-market-regime.csv',
            'exit_distribution_csv' => $base.'-exit-distribution.csv',
            'population_reconciliation_csv' => $base.'-population-reconciliation.csv',
            'r2_hypothesis_lock_json' => $base.'-r2-hypothesis-lock.json',
        ];
    }

    private function writeCsv(string $path, array $rows, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) throw new \RuntimeException('C171_COMPARATIVE_DIAGNOSTIC_OUTPUT_EXISTS_USE_OVERWRITE: '.$path);
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) throw new \RuntimeException('C171_COMPARATIVE_DIAGNOSTIC_OUTPUT_DIRECTORY_CREATE_FAILED');
        $temp = $path.'.tmp.'.getmypid();
        $handle = fopen($temp, 'wb');
        if ($handle === false) throw new \RuntimeException('C171_COMPARATIVE_DIAGNOSTIC_CSV_OPEN_FAILED');
        if ($rows !== []) {
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                $flat = [];
                foreach ($row as $value) {
                    if (is_bool($value)) $flat[] = $value ? 1 : 0;
                    elseif (is_array($value)) $flat[] = json_encode($value, JSON_UNESCAPED_SLASHES);
                    else $flat[] = $value;
                }
                fputcsv($handle, $flat);
            }
        }
        fclose($handle);
        if (! rename($temp, $path)) { @unlink($temp); throw new \RuntimeException('C171_COMPARATIVE_DIAGNOSTIC_CSV_RENAME_FAILED'); }
        return ['status' => 'WRITTEN', 'path' => $path, 'row_count' => count($rows), 'file_sha1' => sha1_file($path)];
    }

    private function writeJson(string $path, array $payload, bool $overwrite): array
    {
        if (is_file($path) && ! $overwrite) throw new \RuntimeException('C171_COMPARATIVE_DIAGNOSTIC_OUTPUT_EXISTS_USE_OVERWRITE: '.$path);
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) throw new \RuntimeException('C171_COMPARATIVE_DIAGNOSTIC_OUTPUT_DIRECTORY_CREATE_FAILED');
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) throw new \RuntimeException('C171_COMPARATIVE_DIAGNOSTIC_JSON_ENCODING_FAILED: '.json_last_error_msg());
        $temp = $path.'.tmp.'.getmypid();
        if (file_put_contents($temp, $json.PHP_EOL, LOCK_EX) === false || ! rename($temp, $path)) {
            @unlink($temp); throw new \RuntimeException('C171_COMPARATIVE_DIAGNOSTIC_ARTIFACT_WRITE_FAILED');
        }
        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1_file($path)];
    }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'r2_hypothesis_locked' => false,
            'draft_paramset_created' => false,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }

    private function findSegment(array $rows, string $type, string $segment): ?array
    {
        foreach ($rows as $row) if (($row['segment_type'] ?? null) === $type && ($row['segment'] ?? null) === $segment) return $row;
        return null;
    }

    private function returnsEqual(array $left, array $right): bool
    {
        if (array_keys($left) !== array_keys($right)) return false;
        foreach ($left as $key => $row) if (round((float) $row['ret_net'], 6) !== round((float) $right[$key]['ret_net'], 6)) return false;
        return true;
    }

    private function shareReason(array $rows, string $reason): ?float
    {
        return $this->ratio(count(array_filter($rows, function (array $row) use ($reason): bool { return ($row['exit_reason_code'] ?? null) === $reason; })), count($rows));
    }

    private function shareFillRule(array $rows, string $rule): ?float
    {
        return $this->ratio(count(array_filter($rows, function (array $row) use ($rule): bool { return ($row['fill_rule'] ?? null) === $rule; })), count($rows));
    }

    private function avgNumbers(array $values): ?float
    {
        return $values === [] ? null : array_sum($values) / count($values);
    }

    private function avgField(array $rows, string $field): ?float
    {
        $values = array_values(array_filter(array_map(function (array $row) use ($field) { return $this->floatOrNull($row[$field] ?? null); }, $rows), function ($value): bool { return $value !== null; }));
        return $values === [] ? null : array_sum($values) / count($values);
    }

    private function sumField(array $rows, string $field): float
    {
        return (float) array_sum(array_map(function (array $row) use ($field): float { return (float) ($row[$field] ?? 0.0); }, $rows));
    }

    private function ratio(int $numerator, int $denominator): ?float
    {
        return $denominator > 0 ? $numerator / $denominator : null;
    }

    private function quantile(array $sorted, float $q): ?float
    {
        $count = count($sorted);
        if ($count === 0) return null;
        $position = ($count - 1) * $q;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        if ($lower === $upper) return (float) $sorted[$lower];
        $weight = $position - $lower;
        return (float) $sorted[$lower] * (1 - $weight) + (float) $sorted[$upper] * $weight;
    }

    private function floatOrNull($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
