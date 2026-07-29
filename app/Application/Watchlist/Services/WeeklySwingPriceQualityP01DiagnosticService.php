<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOfficialEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingPriceQualityP01DiagnosticService
{
    public const RUN_CODE = 'WS_PRICE_QUALITY_P01_PREREGISTERED_IS_ONLY_DIAGNOSTIC';
    public const SUCCESS_STATUS = 'WS_PRICE_QUALITY_P01_DIAGNOSTIC_COMPLETED';
    public const APPROVAL_REFERENCE = 'WS_PRICE_QUALITY_P01_OPERATOR_APPROVED_READ_ONLY_DIAGNOSTIC';
    public const CANONICAL_IS_FROM = '2023-01-02';
    public const CANONICAL_IS_TO = '2025-05-21';
    public const SOURCE_EVAL_ID = 212;
    public const SOURCE_PARAM_SET_ID = 20;
    public const SOURCE_PARAM_ID = 174;
    public const SOURCE_PARAMSET_HASH = 'dde1467e62dd6c586ad7234479d5ae23794a759a';
    public const SOURCE_EVIDENCE_MANIFEST_HASH = 'f9cd54843f3f34f946e7df6ff03246c10529fb3a';
    public const SOURCE_ARTIFACT_HASH = '0e1823c01c48f64072f9d07347cad0e7f304c4d1';
    public const SOURCE_ARTIFACT_FILE_SHA1 = '6a501f8851aa2539db2af72db90cfdb2109f4ab8';
    public const SOURCE_PICK_COUNT = 205;
    public const MAX_HYPOTHESES = 1;
    public const MAX_CANDIDATES = 3;
    public const MIN_TRADES = 120;
    public const MIN_MONTH_WIN_RATE = 0.45;
    public const MIN_MONTH_AVG_RET_NET = -0.01;
    public const MIN_P25_RET_NET = -0.03;
    public const CANDIDATE_THRESHOLDS = [50, 100, 200];

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
            return $this->blocked('WS_PRICE_QUALITY_P01_OPERATOR_APPROVAL_MISSING');
        }
        foreach ([
            'watchlist_bt_eval',
            'watchlist_bt_picks_ws',
            'watchlist_bt_universe_ws',
            'watchlist_param_sets',
            'watchlist_plan_runs',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked(
                    'WS_PRICE_QUALITY_P01_SCHEMA_NOT_READY',
                    ['missing_table' => $table]
                );
            }
        }

        $sourceArtifact = $this->verifySourceArtifact($sourceArtifactPath);
        if (! ($sourceArtifact['valid'] ?? false)) {
            return $this->blocked(
                (string) ($sourceArtifact['reason_code']
                    ?? 'WS_PRICE_QUALITY_P01_SOURCE_ARTIFACT_INVALID'),
                $sourceArtifact
            );
        }
        $source = $this->verifySourceDatabaseIdentity();
        if (! ($source['valid'] ?? false)) {
            return $this->blocked(
                (string) ($source['reason_code']
                    ?? 'WS_PRICE_QUALITY_P01_SOURCE_DATABASE_INVALID'),
                $source
            );
        }
        $manifest = $this->officialEvidence->databaseManifest(self::SOURCE_EVAL_ID);
        if (($manifest['evidence_manifest_hash'] ?? '') !== self::SOURCE_EVIDENCE_MANIFEST_HASH
            || $manifest !== $this->manifestFromEval($source['eval'])) {
            return $this->blocked('WS_PRICE_QUALITY_P01_SOURCE_MANIFEST_MISMATCH', [
                'database_manifest' => $manifest,
                'expected_manifest' => $this->manifestFromEval($source['eval']),
            ]);
        }

        $before = $this->boundaryCounts();
        $evidence = $this->loadEvidence();
        if (! ($evidence['ready'] ?? false)) {
            return $this->blocked(
                (string) ($evidence['reason_code']
                    ?? 'WS_PRICE_QUALITY_P01_EVIDENCE_NOT_READY'),
                $evidence
            );
        }
        $analysis = $this->analyzeEvidence(
            $evidence['rows'],
            (int) $source['eval']['days_covered']
        );
        $after = $this->boundaryCounts();
        if ($before !== $after) {
            return $this->blocked('WS_PRICE_QUALITY_P01_DATABASE_MUTATION_FORBIDDEN', [
                'database_boundary_counts_before' => $before,
                'database_boundary_counts_after' => $after,
            ]);
        }

        $allowed = $analysis['candidate_design_allowed'];
        $result = [
            'schema_version' => 'WS_PRICE_QUALITY_P01_DIAGNOSTIC_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => self::SUCCESS_STATUS,
            'reason_code' => $allowed === []
                ? 'WS_PRICE_QUALITY_P01_NO_LOCKED_THRESHOLD_AUTHORIZED_CLOSE_WITHOUT_CATALOG'
                : 'WS_PRICE_QUALITY_P01_LOCKED_PRICE_THRESHOLDS_SUPPORTED_FOR_MINIMAL_OFFICIAL_IS',
            'approval_reference' => $approvalReference,
            'separate_new_strategy_scope' => true,
            'c171_reopened' => false,
            'r02_reopened' => false,
            's01_reopened' => false,
            'source_anchor_is_best_of_failed_binding' => false,
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
            'pre_registered_hypothesis' => $analysis['pre_registered_hypothesis'],
            'pre_registered_candidate_thresholds' => self::CANDIDATE_THRESHOLDS,
            'diagnostic_price_buckets' => $analysis['diagnostic_price_buckets'],
            'source_metrics' => $analysis['source_metrics'],
            'candidate_diagnostics' => $analysis['candidate_diagnostics'],
            'candidate_design_allowed' => $allowed,
            'candidate_design_allowed_count' => count($allowed),
            'max_hypotheses' => self::MAX_HYPOTHESES,
            'max_candidates' => self::MAX_CANDIDATES,
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
            'next_recommendation' => $allowed === []
                ? 'WS_PRICE_QUALITY_P01_CLOSE_WITHOUT_CATALOG'
                : 'WS_PRICE_QUALITY_P01_PERSIST_ONLY_AUTHORIZED_PREDECLARED_ONE_IDEA_DRAFTS',
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
            return is_numeric($row['ret_net'] ?? null)
                && is_numeric($row['signal_close_price'] ?? null)
                && (float) $row['signal_close_price'] > 0;
        }));
        $source = $this->metrics($rows);
        $source['observed_trade_days'] = $source['days_covered'];
        $source['days_covered'] = $officialDaysCovered > 0
            ? $officialDaysCovered
            : $source['days_covered'];

        $buckets = [];
        foreach ([
            'SIGNAL_CLOSE_LT_50' => function (float $price): bool { return $price < 50; },
            'SIGNAL_CLOSE_50_TO_99' => function (float $price): bool {
                return $price >= 50 && $price < 100;
            },
            'SIGNAL_CLOSE_100_TO_199' => function (float $price): bool {
                return $price >= 100 && $price < 200;
            },
            'SIGNAL_CLOSE_GE_200' => function (float $price): bool { return $price >= 200; },
        ] as $code => $predicate) {
            $buckets[$code] = $this->metrics(array_values(array_filter(
                $rows,
                function (array $row) use ($predicate): bool {
                    return $predicate((float) $row['signal_close_price']);
                }
            )));
        }

        $candidates = [];
        $allowed = [];
        foreach (self::CANDIDATE_THRESHOLDS as $threshold) {
            $candidateRows = array_values(array_filter(
                $rows,
                function (array $row) use ($threshold): bool {
                    return (float) $row['signal_close_price'] >= $threshold;
                }
            ));
            $metrics = $this->metrics($candidateRows);
            $metrics['observed_trade_days'] = $metrics['days_covered'];
            $metrics['days_covered'] = $officialDaysCovered > 0
                ? $officialDaysCovered
                : $metrics['days_covered'];
            $gates = $this->authorizationGates($metrics, $source);
            $code = 'P01_C'.(array_search($threshold, self::CANDIDATE_THRESHOLDS, true) + 1)
                .'_MIN_SIGNAL_PRICE_'.$threshold;
            $candidate = [
                'candidate_code' => $code,
                'threshold' => $threshold,
                'rule_code' => 'EXACT_SIGNAL_DATE_CLOSE_GTE_'.$threshold,
                'metrics' => $metrics,
                'authorization_gates' => $gates,
                'candidate_design_authorized' => ! in_array(false, $gates, true),
                'one_primary_idea' => true,
                'future_return_as_runtime_input' => false,
                'entry_gap_as_runtime_input' => false,
                'ticker_blacklist_used' => false,
                'month_blacklist_used' => false,
                'oos_used' => false,
                'canonical_gates_changed' => false,
            ];
            $candidates[] = $candidate;
            if ($candidate['candidate_design_authorized']) {
                $allowed[] = [
                    'candidate_code' => $code,
                    'threshold' => $threshold,
                    'rule_code' => $candidate['rule_code'],
                ];
            }
        }

        return [
            'source_metrics' => $source,
            'diagnostic_price_buckets' => $buckets,
            'pre_registered_hypothesis' => [
                'hypothesis_code' => 'P01_H1_LOW_PRICE_MICROSTRUCTURE_RISK',
                'idea' => 'An exact signal-date minimum price floor may remove unstable low-price microstructure exposure while retaining the non-weak-IHSG core.',
                'registration_status' => 'PRE_REGISTERED_BEFORE_P01_DIAGNOSTIC_RUNTIME',
                'diagnostic_status' => $allowed === []
                    ? 'INCONCLUSIVE_NOT_ALLOWED'
                    : 'SUPPORTED_FOR_MINIMAL_CANDIDATE_DESIGN',
                'decision_time_fields' => ['signal_close_price'],
                'candidate_thresholds_locked_before_diagnostic' => self::CANDIDATE_THRESHOLDS,
                'candidate_design_allowed_count' => count($allowed),
            ],
            'candidate_diagnostics' => $candidates,
            'candidate_design_allowed' => $allowed,
        ];
    }

    private function authorizationGates(array $metrics, array $source): array
    {
        return [
            'minimum_trade_count' => (int) $metrics['trade_count'] >= self::MIN_TRADES,
            'average_return_positive' => (float) $metrics['avg_ret_net'] > 0,
            'median_return_non_negative' => (float) $metrics['median_ret_net'] >= 0,
            'p25_downside_bound' => (float) $metrics['p25_ret_net'] >= self::MIN_P25_RET_NET,
            'monthly_win_rate_floor' => (float) $metrics['month_win_rate_min']
                >= self::MIN_MONTH_WIN_RATE,
            'monthly_average_floor' => (float) $metrics['month_avg_ret_net_min']
                >= self::MIN_MONTH_AVG_RET_NET,
            'source_worst_month_average_improved' =>
                (float) $metrics['month_avg_ret_net_min']
                    > (float) $source['month_avg_ret_net_min'],
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
        if (count($rows) !== self::SOURCE_PICK_COUNT) {
            return [
                'ready' => false,
                'reason_code' => 'WS_PRICE_QUALITY_P01_SOURCE_PICK_COUNT_MISMATCH',
                'expected_count' => self::SOURCE_PICK_COUNT,
                'actual_count' => count($rows),
            ];
        }

        $invalid = array_values(array_filter($rows, function (array $row): bool {
            return (int) ($row['source_publication_id'] ?? 0) < 1
                || (int) ($row['source_publication_version'] ?? 0) < 1
                || (int) ($row['source_run_id'] ?? 0) < 1
                || ! is_numeric($row['signal_close_price'] ?? null)
                || (float) $row['signal_close_price'] <= 0;
        }));
        if ($invalid !== []) {
            return [
                'ready' => false,
                'reason_code' => 'WS_PRICE_QUALITY_P01_DECISION_TIME_LINEAGE_INCOMPLETE',
                'invalid_count' => count($invalid),
                'invalid_sample' => array_slice($invalid, 0, 10),
            ];
        }

        return [
            'ready' => true,
            'reason_code' => 'WS_PRICE_QUALITY_P01_DECISION_TIME_EVIDENCE_READY',
            'rows' => $rows,
            'lineage' => [
                'source_eval_id' => self::SOURCE_EVAL_ID,
                'trade_count' => count($rows),
                'signal_publication_lineage_complete_count' => count($rows),
                'signal_close_price_complete_count' => count($rows),
                'exact_signal_date_field_only' => 'signal_close_price',
                'future_return_used_as_runtime_input' => false,
                'entry_gap_used_as_runtime_input' => false,
                'oos_read' => false,
            ],
        ];
    }

    private function verifySourceArtifact(string $path): array
    {
        if (! is_file($path)
            || strtolower((string) sha1_file($path)) !== self::SOURCE_ARTIFACT_FILE_SHA1) {
            return [
                'valid' => false,
                'reason_code' => 'WS_PRICE_QUALITY_P01_SOURCE_FILE_SHA1_MISMATCH',
            ];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)
            || ($artifact['artifact_hash'] ?? '') !== self::SOURCE_ARTIFACT_HASH
            || ($artifact['canonical_is_gates_pass'] ?? true) !== false
            || ($artifact['oos_runtime_invoked'] ?? true) !== false
            || ($artifact['param_set_id'] ?? null) !== self::SOURCE_PARAM_SET_ID) {
            return [
                'valid' => false,
                'reason_code' => 'WS_PRICE_QUALITY_P01_SOURCE_ARTIFACT_IDENTITY_MISMATCH',
            ];
        }
        $hashPayload = $artifact;
        unset($hashPayload['artifact_hash']);
        if ($this->identity->stableHash($hashPayload) !== self::SOURCE_ARTIFACT_HASH) {
            return [
                'valid' => false,
                'reason_code' => 'WS_PRICE_QUALITY_P01_SOURCE_ARTIFACT_HASH_MISMATCH',
            ];
        }

        return [
            'valid' => true,
            'reason_code' => 'WS_PRICE_QUALITY_P01_SOURCE_ARTIFACT_VERIFIED',
        ];
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
            return [
                'valid' => false,
                'reason_code' => 'WS_PRICE_QUALITY_P01_SOURCE_DATABASE_IDENTITY_MISMATCH',
            ];
        }

        return [
            'valid' => true,
            'reason_code' => 'WS_PRICE_QUALITY_P01_SOURCE_DATABASE_IDENTITY_VERIFIED',
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
        $dates = array_values(array_unique(array_column($rows, 'trade_date')));
        $months = [];
        foreach ($rows as $row) {
            $months[substr((string) $row['trade_date'], 0, 7)][] = (float) $row['ret_net'];
        }
        ksort($months, SORT_STRING);
        $monthly = [];
        foreach ($months as $month => $values) {
            $monthWins = count(array_filter($values, function (float $value): bool {
                return $value > 0;
            }));
            $monthly[] = [
                'month' => $month,
                'trade_count' => count($values),
                'avg_ret_net' => array_sum($values) / count($values),
                'win_rate' => $monthWins / count($values),
                'min_ret_net' => min($values),
                'max_ret_net' => max($values),
            ];
        }
        $monthWinRates = array_column($monthly, 'win_rate');
        $monthAverages = array_column($monthly, 'avg_ret_net');

        return [
            'trade_count' => count($returns),
            'days_covered' => count($dates),
            'avg_ret_net' => $returns === [] ? null : array_sum($returns) / count($returns),
            'median_ret_net' => $this->quantile($returns, 0.50),
            'p25_ret_net' => $this->quantile($returns, 0.25),
            'win_rate' => $returns === [] ? null : $wins / count($returns),
            'min_ret_net' => $returns === [] ? null : min($returns),
            'max_ret_net' => $returns === [] ? null : max($returns),
            'month_win_rate_min' => $monthWinRates === [] ? null : min($monthWinRates),
            'month_avg_ret_net_min' => $monthAverages === [] ? null : min($monthAverages),
            'period_fail_count' => count(array_filter($monthly, function (array $row): bool {
                return (float) $row['win_rate'] < self::MIN_MONTH_WIN_RATE
                    || (float) $row['avg_ret_net'] < self::MIN_MONTH_AVG_RET_NET;
            })),
            'monthly_metrics' => $monthly,
        ];
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
            'market_data_lineage_hash' =>
                (string) ($eval['market_data_lineage_hash'] ?? ''),
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
            'max_candidates' => self::MAX_CANDIDATES,
            'max_remediation_rounds' => 1,
            'one_primary_idea' => 'EXACT_SIGNAL_DATE_MINIMUM_PRICE_QUALITY_FLOOR',
            'candidate_thresholds_locked_before_diagnostic' => self::CANDIDATE_THRESHOLDS,
            'new_threshold_after_diagnostic_forbidden' => true,
            'canonical_gate_weakening_forbidden' => true,
            'ticker_blacklist_forbidden' => true,
            'month_blacklist_forbidden' => true,
            'sector_whitelist_forbidden' => true,
            'entry_gap_as_runtime_input_forbidden' => true,
            'future_return_as_runtime_input_forbidden' => true,
            'oos_read_before_all_canonical_is_gates_pass_forbidden' => true,
        ];
    }

    private function writeJson(string $path, array $payload, bool $overwrite): array
    {
        if ($path === '') {
            throw new \RuntimeException('WS_PRICE_QUALITY_P01_OUTPUT_PATH_REQUIRED');
        }
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json === false) {
            throw new \RuntimeException('WS_PRICE_QUALITY_P01_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_OUTPUT_DIRECTORY_CREATE_FAILED'
            );
        }
        if (is_file($path)) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return [
                    'status' => 'IDEMPOTENT',
                    'path' => $path,
                    'file_sha1' => sha1($existing),
                ];
            }
            if (! $overwrite) {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_OUTPUT_EXISTS_USE_OVERWRITE'
                );
            }
        }
        $temporary = $path.'.tmp.'.getmypid();
        if (file_put_contents($temporary, $json, LOCK_EX) === false
            || ! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException('WS_PRICE_QUALITY_P01_OUTPUT_WRITE_FAILED');
        }

        return [
            'status' => 'WRITTEN',
            'path' => $path,
            'file_sha1' => sha1_file($path),
        ];
    }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'separate_new_strategy_scope' => true,
            'c171_reopened' => false,
            'r02_reopened' => false,
            's01_reopened' => false,
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
