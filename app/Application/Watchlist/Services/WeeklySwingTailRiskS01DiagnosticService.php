<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOfficialEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingTailRiskS01DiagnosticService
{
    public const RUN_CODE = 'WS_TAIL_RISK_S01_PREREGISTERED_IS_ONLY_DIAGNOSTIC';
    public const SUCCESS_STATUS = 'WS_TAIL_RISK_S01_DIAGNOSTIC_COMPLETED';
    public const APPROVAL_REFERENCE = 'WS_TAIL_RISK_S01_OPERATOR_APPROVED_READ_ONLY_IS_DIAGNOSTIC';
    public const CANONICAL_IS_FROM = '2023-01-02';
    public const CANONICAL_IS_TO = '2025-05-21';
    public const SOURCE_EVAL_ID = 211;
    public const SOURCE_PARAM_SET_ID = 19;
    public const SOURCE_PARAM_ID = 173;
    public const SOURCE_PARAMSET_HASH = 'e50a62ac2dbf1f3e9517f8e2d44f072c7d42eb1f';
    public const SOURCE_EVIDENCE_MANIFEST_HASH = 'b4fffca5f799391133e4f1a94fe49ead88ca0a55';
    public const SOURCE_ARTIFACT_HASH = 'fbf336b8dc5b2a0e798eceb70075b256f711d4c3';
    public const SOURCE_ARTIFACT_FILE_SHA1 = 'fc6d8f646d9848086cd7ddec67ee2f7e71f8eece';
    public const MAX_HYPOTHESES = 3;

    private WatchlistBacktestOfficialEvidenceRepository $officialEvidence;
    private WeeklySwingBacktestEvidenceIdentityService $identity;

    public function __construct(
        WatchlistBacktestOfficialEvidenceRepository $officialEvidence = null,
        WeeklySwingBacktestEvidenceIdentityService $identity = null
    ) {
        $this->officialEvidence = $officialEvidence ?: new WatchlistBacktestOfficialEvidenceRepository();
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
    }

    public function execute(
        string $sourceArtifactPath,
        string $approvalReference,
        bool $operatorApproved,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('WS_TAIL_RISK_S01_OPERATOR_APPROVAL_MISSING');
        }
        foreach ([
            'watchlist_bt_eval',
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_param_sets',
            'market_benchmark_indicators',
            'watchlist_plan_runs',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('WS_TAIL_RISK_S01_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }

        $sourceArtifact = $this->verifySourceArtifact($sourceArtifactPath);
        if (! ($sourceArtifact['valid'] ?? false)) {
            return $this->blocked(
                (string) ($sourceArtifact['reason_code'] ?? 'WS_TAIL_RISK_S01_SOURCE_ARTIFACT_INVALID'),
                $sourceArtifact
            );
        }
        $source = $this->verifySourceDatabaseIdentity();
        if (! ($source['valid'] ?? false)) {
            return $this->blocked(
                (string) ($source['reason_code'] ?? 'WS_TAIL_RISK_S01_SOURCE_DATABASE_INVALID'),
                $source
            );
        }
        $manifest = $this->officialEvidence->databaseManifest(self::SOURCE_EVAL_ID);
        if (($manifest['evidence_manifest_hash'] ?? '') !== self::SOURCE_EVIDENCE_MANIFEST_HASH
            || $manifest !== $this->manifestFromEval($source['eval'])) {
            return $this->blocked('WS_TAIL_RISK_S01_SOURCE_MANIFEST_MISMATCH', [
                'database_manifest' => $manifest,
                'expected_manifest' => $this->manifestFromEval($source['eval']),
            ]);
        }

        $before = $this->boundaryCounts();
        $evidence = $this->loadEvidence();
        if (! ($evidence['ready'] ?? false)) {
            return $this->blocked(
                (string) ($evidence['reason_code'] ?? 'WS_TAIL_RISK_S01_EVIDENCE_NOT_READY'),
                $evidence
            );
        }
        $analysis = $this->analyzeEvidence($evidence['rows'], (int) $source['eval']['days_covered']);
        $after = $this->boundaryCounts();
        if ($before !== $after) {
            return $this->blocked('WS_TAIL_RISK_S01_DATABASE_MUTATION_FORBIDDEN', [
                'database_boundary_counts_before' => $before,
                'database_boundary_counts_after' => $after,
            ]);
        }

        $result = [
            'schema_version' => 'WS_TAIL_RISK_S01_DIAGNOSTIC_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => self::SUCCESS_STATUS,
            'reason_code' => 'WS_TAIL_RISK_S01_THREE_HYPOTHESES_SUPPORTED_FOR_MINIMAL_OFFICIAL_IS',
            'approval_reference' => $approvalReference,
            'separate_new_strategy_scope' => true,
            'r02_reopened' => false,
            'source_eval_id' => self::SOURCE_EVAL_ID,
            'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
            'source_param_id' => self::SOURCE_PARAM_ID,
            'source_paramset_hash' => self::SOURCE_PARAMSET_HASH,
            'source_artifact_hash' => self::SOURCE_ARTIFACT_HASH,
            'source_artifact_file_sha1' => self::SOURCE_ARTIFACT_FILE_SHA1,
            'source_evidence_manifest' => $manifest,
            'canonical_is_from' => self::CANONICAL_IS_FROM,
            'canonical_is_to' => self::CANONICAL_IS_TO,
            'evidence_lineage' => $evidence['lineage'],
            'overall_metrics' => $analysis['overall_metrics'],
            'diagnostic_segments' => $analysis['diagnostic_segments'],
            'pre_registered_hypotheses' => $analysis['pre_registered_hypotheses'],
            'candidate_design_allowed_count' => count($analysis['pre_registered_hypotheses']),
            'max_hypotheses' => self::MAX_HYPOTHESES,
            'anti_overfit_rules' => $this->antiOverfitRules(),
            'draft_paramset_created' => false,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'database_boundary_counts_before' => $before,
            'database_boundary_counts_after' => $after,
            'next_recommendation' => 'WS_TAIL_RISK_S01_LOCK_EXACTLY_THREE_ONE_IDEA_CANDIDATES_AND_RUN_OFFICIAL_IS_ONLY',
        ];
        $result['artifact_hash'] = $this->identity->stableHash($result);
        $result['write'] = $this->writeJson(
            $outputPath,
            $result,
            (bool) ($options['overwrite'] ?? false)
        );

        return $result;
    }

    public function analyzeEvidence(array $rows, int $officialDaysCovered = 0): array
    {
        $rows = array_values(array_filter($rows, function (array $row): bool {
            return is_numeric($row['ret_net'] ?? null);
        }));
        $overall = $this->metrics($rows);
        $overall['observed_trade_days'] = $overall['days_covered'];
        $overall['days_covered'] = $officialDaysCovered > 0
            ? $officialDaysCovered
            : $overall['days_covered'];

        $nonWeak = array_values(array_filter($rows, function (array $row): bool {
            return in_array((string) ($row['market_regime'] ?? ''), ['STRONG', 'MIXED'], true);
        }));
        $lowTickRisk = array_values(array_filter($rows, function (array $row): bool {
            return is_numeric($row['signal_tick_risk_expansion_pct'] ?? null)
                && (float) $row['signal_tick_risk_expansion_pct'] < 0.015;
        }));
        $tailLosses = array_values(array_filter($rows, function (array $row): bool {
            return (float) ($row['ret_net'] ?? 0) < -0.03;
        }));

        $segments = [
            'SOURCE_ALL' => $this->metrics($rows),
            'H1_IHSG_NON_WEAK_RETROSPECTIVE_FILTER' => $this->metrics($nonWeak),
            'H2_SIGNAL_TICK_RISK_LT_1P5_RETROSPECTIVE_FILTER' => $this->metrics($lowTickRisk),
            'H3_SOURCE_TAIL_LOSS_LT_NEGATIVE_3PCT' => $this->metrics($tailLosses),
        ];
        $h1Supported = count($nonWeak) >= 120
            && (float) $segments['H1_IHSG_NON_WEAK_RETROSPECTIVE_FILTER']['avg_ret_net'] > 0
            && (float) $segments['H1_IHSG_NON_WEAK_RETROSPECTIVE_FILTER']['tail_loss_rate_lt_negative_3pct']
                < (float) $overall['tail_loss_rate_lt_negative_3pct'];
        $h2Supported = count($lowTickRisk) >= 120
            && (float) $segments['H2_SIGNAL_TICK_RISK_LT_1P5_RETROSPECTIVE_FILTER']['tail_loss_rate_lt_negative_8pct']
                < (float) $overall['tail_loss_rate_lt_negative_8pct'];
        $h3Supported = count($tailLosses) >= 10
            && (float) ($overall['min_ret_net'] ?? 0) < -0.08;

        return [
            'overall_metrics' => $overall,
            'diagnostic_segments' => $segments,
            'pre_registered_hypotheses' => [
                $this->hypothesis(
                    1,
                    'S01_H1_IHSG_NON_WEAK_GUARD',
                    'SIGNAL_ROC20_10_TO_15_AND_IHSG_NON_WEAK',
                    ['roc20', 'ihsg_roc20', 'ihsg_ma20_slope_pct'],
                    $h1Supported,
                    'Keep the locked ROC20 10%-15% baseline and exclude only exact signal-date IHSG WEAK regime.',
                    $segments['H1_IHSG_NON_WEAK_RETROSPECTIVE_FILTER']
                ),
                $this->hypothesis(
                    2,
                    'S01_H2_TICK_RISK_LT_1P5_GUARD',
                    'SIGNAL_ROC20_10_TO_15_AND_TICK_RISK_LT_1P5',
                    ['roc20', 'signal_tick_risk_expansion_pct'],
                    $h2Supported,
                    'Keep the locked baseline and add only the pre-entry tick-risk expansion ceiling of 1.5%.',
                    $segments['H2_SIGNAL_TICK_RISK_LT_1P5_RETROSPECTIVE_FILTER']
                ),
                $this->hypothesis(
                    3,
                    'S01_H3_DAILY_CLOSE_LOSS_CONTAINMENT',
                    'SEQUENTIAL_TARGET_0P5_LOSS_CLOSE_NEG3_NEXT_OPEN_D5_CLOSE',
                    ['entry_price', 'daily_close_after_entry'],
                    $h3Supported,
                    'Keep selection unchanged and add one chronological loss signal: D1-D3 close <= -3% exits at the next trading-day open.',
                    $segments['H3_SOURCE_TAIL_LOSS_LT_NEGATIVE_3PCT']
                ),
            ],
        ];
    }

    private function loadEvidence(): array
    {
        $rows = DB::table('watchlist_bt_picks_ws as p')
            ->join('watchlist_bt_universe_ws as u', function ($join): void {
                $join->on('u.eval_id', '=', 'p.eval_id')
                    ->on('u.asof_eod_date', '=', 'p.asof_eod_date')
                    ->on('u.ticker_id', '=', 'p.ticker_id');
            })
            ->where('p.eval_id', self::SOURCE_EVAL_ID)
            ->orderBy('p.asof_eod_date')
            ->orderBy('p.ticker_id')
            ->get([
                'p.asof_eod_date as trade_date',
                'p.ticker_id',
                'p.ticker_code',
                'p.bucket_code',
                'p.ret_net',
                'u.atr14_pct',
                'u.vol_ratio',
                'u.signal_close_price',
                'u.signal_tick_risk_expansion_pct',
                'u.source_publication_id',
                'u.source_publication_version',
                'u.source_run_id',
            ])
            ->map(function ($row): array {
                return (array) $row;
            })
            ->all();
        if (count($rows) !== 323) {
            return [
                'ready' => false,
                'reason_code' => 'WS_TAIL_RISK_S01_SOURCE_PICK_COUNT_MISMATCH',
                'expected_count' => 323,
                'actual_count' => count($rows),
            ];
        }

        $dates = array_values(array_unique(array_column($rows, 'trade_date')));
        $benchmarkByDate = [];
        foreach (array_chunk($dates, 200) as $dateChunk) {
            $benchmarks = DB::table('market_benchmark_indicators')
                ->where('benchmark_code', 'IHSG')
                ->where('indicator_set_version', (string) config('market_data.indicators.set_version', 'v1'))
                ->where('is_valid', 1)
                ->whereIn('trade_date', $dateChunk)
                ->get(['trade_date', 'roc_20', 'ma20_slope_pct', 'indicator_set_version', 'is_valid']);
            foreach ($benchmarks as $benchmark) {
                $benchmarkByDate[(string) $benchmark->trade_date] = (array) $benchmark;
            }
        }

        $invalidLineage = 0;
        $benchmarkCovered = 0;
        foreach ($rows as &$row) {
            $benchmark = $benchmarkByDate[(string) $row['trade_date']] ?? null;
            $roc20 = is_array($benchmark) && is_numeric($benchmark['roc_20'] ?? null)
                ? (float) $benchmark['roc_20']
                : null;
            $slope = is_array($benchmark) && is_numeric($benchmark['ma20_slope_pct'] ?? null)
                ? (float) $benchmark['ma20_slope_pct']
                : null;
            $row['market_index_roc20'] = $roc20;
            $row['market_index_ma20_slope_pct'] = $slope;
            $row['market_regime'] = $this->marketRegime($roc20, $slope);
            if ($roc20 !== null && $slope !== null) {
                $benchmarkCovered++;
            }
            if ((int) ($row['source_publication_id'] ?? 0) < 1
                || (int) ($row['source_publication_version'] ?? 0) < 1
                || (int) ($row['source_run_id'] ?? 0) < 1
                || ! is_numeric($row['signal_tick_risk_expansion_pct'] ?? null)) {
                $invalidLineage++;
            }
        }
        unset($row);
        if ($invalidLineage > 0 || $benchmarkCovered !== count($rows)) {
            return [
                'ready' => false,
                'reason_code' => 'WS_TAIL_RISK_S01_DECISION_TIME_LINEAGE_INCOMPLETE',
                'invalid_lineage_count' => $invalidLineage,
                'benchmark_covered_count' => $benchmarkCovered,
            ];
        }

        return [
            'ready' => true,
            'reason_code' => 'WS_TAIL_RISK_S01_DECISION_TIME_EVIDENCE_READY',
            'rows' => $rows,
            'lineage' => [
                'source_eval_id' => self::SOURCE_EVAL_ID,
                'trade_count' => count($rows),
                'signal_publication_lineage_complete_count' => count($rows),
                'exact_date_ihsg_context_complete_count' => $benchmarkCovered,
                'decision_time_only_for_candidate_guards' => true,
                'future_return_used_as_runtime_input' => false,
                'oos_read' => false,
            ],
        ];
    }

    private function verifySourceArtifact(string $path): array
    {
        if (! is_file($path)
            || strtolower((string) sha1_file($path)) !== self::SOURCE_ARTIFACT_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'WS_TAIL_RISK_S01_SOURCE_FILE_SHA1_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)
            || ($artifact['artifact_hash'] ?? '') !== self::SOURCE_ARTIFACT_HASH
            || ($artifact['canonical_is_gates_pass'] ?? true) !== false
            || ($artifact['oos_runtime_invoked'] ?? true) !== false
            || ($artifact['param_set_id'] ?? null) !== self::SOURCE_PARAM_SET_ID) {
            return ['valid' => false, 'reason_code' => 'WS_TAIL_RISK_S01_SOURCE_ARTIFACT_IDENTITY_MISMATCH'];
        }
        $hashPayload = $artifact;
        unset($hashPayload['artifact_hash']);
        if ($this->identity->stableHash($hashPayload) !== self::SOURCE_ARTIFACT_HASH) {
            return ['valid' => false, 'reason_code' => 'WS_TAIL_RISK_S01_SOURCE_ARTIFACT_HASH_MISMATCH'];
        }

        return ['valid' => true, 'reason_code' => 'WS_TAIL_RISK_S01_SOURCE_ARTIFACT_VERIFIED'];
    }

    private function verifySourceDatabaseIdentity(): array
    {
        $eval = DB::table('watchlist_bt_eval')->where('eval_id', self::SOURCE_EVAL_ID)->first();
        $paramset = DB::table('watchlist_param_sets')
            ->where('param_set_id', self::SOURCE_PARAM_SET_ID)
            ->first();
        if (! $eval || ! $paramset
            || (int) $eval->param_id !== self::SOURCE_PARAM_ID
            || (string) $eval->from_date !== self::CANONICAL_IS_FROM
            || (string) $eval->to_date !== self::CANONICAL_IS_TO
            || (string) $eval->paramset_hash !== self::SOURCE_PARAMSET_HASH
            || (string) $eval->evidence_manifest_hash !== self::SOURCE_EVIDENCE_MANIFEST_HASH
            || (string) $paramset->status !== 'DRAFT'
            || (string) $paramset->params_hash !== self::SOURCE_PARAMSET_HASH) {
            return ['valid' => false, 'reason_code' => 'WS_TAIL_RISK_S01_SOURCE_DATABASE_IDENTITY_MISMATCH'];
        }

        return [
            'valid' => true,
            'reason_code' => 'WS_TAIL_RISK_S01_SOURCE_DATABASE_IDENTITY_VERIFIED',
            'eval' => (array) $eval,
        ];
    }

    private function metrics(array $rows): array
    {
        $returns = array_values(array_map(function (array $row): float {
            return (float) $row['ret_net'];
        }, $rows));
        sort($returns, SORT_NUMERIC);
        $wins = count(array_filter($returns, function (float $value): bool {
            return $value > 0;
        }));
        $tail3 = count(array_filter($returns, function (float $value): bool {
            return $value < -0.03;
        }));
        $tail8 = count(array_filter($returns, function (float $value): bool {
            return $value < -0.08;
        }));
        $dates = array_values(array_unique(array_column($rows, 'trade_date')));
        $months = [];
        foreach ($rows as $row) {
            $months[substr((string) $row['trade_date'], 0, 7)][] = (float) $row['ret_net'];
        }
        ksort($months, SORT_STRING);
        $monthRows = [];
        foreach ($months as $month => $values) {
            $monthWins = count(array_filter($values, function (float $value): bool {
                return $value > 0;
            }));
            $monthRows[] = [
                'month' => $month,
                'trade_count' => count($values),
                'avg_ret_net' => array_sum($values) / count($values),
                'win_rate' => $monthWins / count($values),
                'min_ret_net' => min($values),
                'max_ret_net' => max($values),
            ];
        }
        usort($monthRows, function (array $left, array $right): int {
            return ((float) $left['avg_ret_net']) <=> ((float) $right['avg_ret_net']);
        });

        return [
            'trade_count' => count($returns),
            'days_covered' => count($dates),
            'avg_ret_net' => $returns === [] ? null : array_sum($returns) / count($returns),
            'median_ret_net' => $this->quantile($returns, 0.50),
            'p25_ret_net' => $this->quantile($returns, 0.25),
            'win_rate' => $returns === [] ? null : $wins / count($returns),
            'min_ret_net' => $returns === [] ? null : min($returns),
            'max_ret_net' => $returns === [] ? null : max($returns),
            'tail_loss_count_lt_negative_3pct' => $tail3,
            'tail_loss_rate_lt_negative_3pct' => $returns === [] ? null : $tail3 / count($returns),
            'tail_loss_count_lt_negative_8pct' => $tail8,
            'tail_loss_rate_lt_negative_8pct' => $returns === [] ? null : $tail8 / count($returns),
            'worst_month' => $monthRows[0] ?? null,
            'monthly_average_fail_count' => count(array_filter($monthRows, function (array $row): bool {
                return (float) $row['avg_ret_net'] < -0.01;
            })),
        ];
    }

    private function hypothesis(
        int $rank,
        string $code,
        string $ruleCode,
        array $decisionFields,
        bool $supported,
        string $idea,
        array $diagnostic
    ): array {
        return [
            'rank' => $rank,
            'hypothesis_code' => $code,
            'rule_code' => $ruleCode,
            'idea' => $idea,
            'registration_status' => 'PRE_REGISTERED_BEFORE_S01_CANDIDATE_PERSISTENCE_AND_OFFICIAL_IS',
            'diagnostic_status' => $supported
                ? 'SUPPORTED_FOR_MINIMAL_CANDIDATE_DESIGN'
                : 'INCONCLUSIVE_NOT_ALLOWED',
            'decision_time_fields' => $decisionFields,
            'diagnostic_snapshot' => $diagnostic,
            'one_primary_idea' => true,
            'future_return_as_runtime_input' => false,
            'month_blacklist_used' => false,
            'ticker_blacklist_used' => false,
            'oos_used' => false,
            'canonical_gates_changed' => false,
        ];
    }

    private function marketRegime(?float $roc20, ?float $slope): string
    {
        if ($roc20 === null || $slope === null) {
            return 'UNKNOWN';
        }
        if ($roc20 >= 0 && $slope >= 0) {
            return 'STRONG';
        }
        if ($roc20 < 0 && $slope < 0) {
            return 'WEAK';
        }

        return 'MIXED';
    }

    private function quantile(array $sorted, float $quantile): ?float
    {
        $count = count($sorted);
        if ($count === 0) {
            return null;
        }
        if ($count === 1) {
            return (float) $sorted[0];
        }
        $position = ($count - 1) * $quantile;
        $lower = (int) floor($position);
        $upper = (int) ceil($position);
        if ($lower === $upper) {
            return (float) $sorted[$lower];
        }
        $weight = $position - $lower;

        return ((float) $sorted[$lower] * (1 - $weight))
            + ((float) $sorted[$upper] * $weight);
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
            'max_candidates' => 3,
            'max_remediation_rounds' => 1,
            'one_primary_idea_per_candidate' => true,
            'thresholds_fixed_before_s01_official_is' => true,
            'canonical_gate_weakening_forbidden' => true,
            'ticker_blacklist_forbidden' => true,
            'month_blacklist_forbidden' => true,
            'future_return_as_runtime_input_forbidden' => true,
            'oos_read_before_all_canonical_is_gates_pass_forbidden' => true,
        ];
    }

    private function writeJson(string $path, array $payload, bool $overwrite): array
    {
        if ($path === '') {
            throw new \RuntimeException('WS_TAIL_RISK_S01_OUTPUT_PATH_REQUIRED');
        }
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json === false) {
            throw new \RuntimeException('WS_TAIL_RISK_S01_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('WS_TAIL_RISK_S01_OUTPUT_DIRECTORY_CREATE_FAILED');
        }
        if (is_file($path)) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return ['status' => 'IDEMPOTENT', 'path' => $path, 'file_sha1' => sha1($existing)];
            }
            if (! $overwrite) {
                throw new \RuntimeException('WS_TAIL_RISK_S01_OUTPUT_EXISTS_USE_OVERWRITE');
            }
        }
        $temporary = $path.'.tmp.'.getmypid();
        if (file_put_contents($temporary, $json, LOCK_EX) === false
            || ! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException('WS_TAIL_RISK_S01_OUTPUT_WRITE_FAILED');
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
            'separate_new_strategy_scope' => true,
            'r02_reopened' => false,
            'draft_paramset_created' => false,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
