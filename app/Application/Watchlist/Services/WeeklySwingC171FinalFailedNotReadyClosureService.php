<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingC171FinalFailedNotReadyClosureService
{
    public const RUN_CODE = 'C171_FINAL_FAILED_NOT_READY_CLOSURE_AND_EVIDENCE_SEAL';
    public const APPROVAL_REFERENCE = 'C171_OPERATOR_APPROVED_FINAL_FAILED_NOT_READY_CLOSURE_ONLY';
    public const SUCCESS_STATUS = 'C171_FINAL_FAILED_NOT_READY_CLOSURE_SEALED';
    public const FINAL_DECISION = 'C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION';
    public const REASON_CODE = 'C171_NO_FINAL_CANDIDATE_PASSED_CANONICAL_IS_GATES';
    public const PIPELINE_VERSION = 'WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3';
    public const PIPELINE_HASH = '9e9933b363026623b7ab5629f3281fa680a53a2e';
    public const CANONICAL_IS_FROM = '2023-01-02';
    public const CANONICAL_IS_TO = '2025-05-21';
    public const FINAL_CATALOG_CODE = 'WS_BT_GRID_FINAL_BOUNDED_REMEDIATION_C01_2026_07';
    public const FINAL_CATALOG_VERSION = 'FINAL-C01';
    public const FINAL_CATALOG_HASH = '5bc6dce5a8a96665435bdae8a30857bc75b108b0';
    public const SUMMARY_FILE_SHA1 = '53356ca429cf7aa47efc45acfb5511f9dc92ed50';
    public const DECISION_ARTIFACT_HASH = 'b7fcc8d7aae089cd6fb518ddb390de0d3122318d';
    public const DECISION_FILE_SHA1 = 'cdaaa8271ebdf6711b6a3cfbd6732e1a6a70992b';

    public const ANCHOR = [
        'param_set_id' => 11,
        'eval_id' => 204,
        'bt_param_id' => 166,
        'row_code' => 'C171_C01_E_SCORE_RISK_FORWARD_RECALIBRATION',
        'catalog_code' => 'WS_BT_GRID_LOW_PRICE_EXECUTION_QUALITY_C01_2026_07',
        'catalog_version' => 'C01',
        'catalog_hash' => 'bad53b5880f183a55163565fb1e073420c29a080',
        'params_hash' => 'c93bae2b761028d6b236f368d5b19bb4f498715a',
        'artifact_hash' => 'bc81941261f0af27f8043b4033da1d2a9b2f331a',
        'file_sha1' => 'd811d80cbe3677835cb3bbb6f3f87462a1744eaf',
        'evidence_manifest_hash' => '604bfbe9698fbb8ec3c74e3fa6e10f9335f66d1d',
    ];

    public const FINAL_EVIDENCE = [
        12 => [
            'eval_id' => 205, 'bt_param_id' => 167,
            'row_code' => 'C171_FINAL_A_RISK_FORWARD_INTERPOLATED',
            'params_hash' => '1d8bfec8d6519526ce0582fa5ce17287b41d27d2',
            'artifact_hash' => '003f793b16117bcdd8d8ebd5cd7181919cc39c1d',
            'file_sha1' => 'f251d73175400dd36920d68d4e02e5afc59dae90',
            'evidence_manifest_hash' => '269c3fe498abd6b5cc1e76181266b73e6a771a41',
        ],
        13 => [
            'eval_id' => 206, 'bt_param_id' => 168,
            'row_code' => 'C171_FINAL_B_RISK_FORWARD_ATR_055',
            'params_hash' => '5c0aa7ef161e6dad96b77e9d4f978c696d785993',
            'artifact_hash' => '3c4860a655eaa107ff1c1fa7c61c43ad3501d89e',
            'file_sha1' => '5c764255457820f1321cb7fadc5ddf21556895e5',
            'evidence_manifest_hash' => '2ed3b26d0b6c81def7b8f096ccc6ee4045ac0dea',
        ],
        14 => [
            'eval_id' => 207, 'bt_param_id' => 169,
            'row_code' => 'C171_FINAL_C_RISK_FORWARD_STOP_125',
            'params_hash' => 'a3e84b2657c1ecd3ece807439621a25743aa06eb',
            'artifact_hash' => 'b8c89c197d5e78e31adb1f08a21e37b17f30b122',
            'file_sha1' => '7fedd1436305b351fa9e3e846fc3b4abca42c26e',
            'evidence_manifest_hash' => '9639d87d580b6782321b0c78ea5e03fd10be03fb',
        ],
    ];

    private WeeklySwingBacktestEvidenceIdentityService $identity;

    public function __construct(WeeklySwingBacktestEvidenceIdentityService $identity = null)
    {
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
    }

    public function execute(
        string $anchorArtifactPath,
        string $finalArtifactDirectory,
        string $summaryCsvPath,
        string $approvalReference,
        bool $operatorApproved,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('C171_FINAL_CLOSURE_OPERATOR_APPROVAL_MISSING');
        }
        foreach (['watchlist_param_sets', 'watchlist_bt_eval', 'watchlist_plan_runs'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('C171_FINAL_CLOSURE_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }

        $decisionContract = $this->loadAndVerifyDecisionContract();
        if (! ($decisionContract['valid'] ?? false)) {
            return $this->blocked((string) ($decisionContract['reason_code'] ?? 'C171_FINAL_CLOSURE_DECISION_CONTRACT_INVALID'), $decisionContract);
        }
        $evidence = $this->loadAndVerifyEvidenceBundle($anchorArtifactPath, $finalArtifactDirectory);
        if (! ($evidence['valid'] ?? false)) {
            return $this->blocked((string) ($evidence['reason_code'] ?? 'C171_FINAL_CLOSURE_EVIDENCE_INVALID'), $evidence);
        }
        $summary = $this->loadAndVerifySummary($summaryCsvPath, $evidence['final_candidates']);
        if (! ($summary['valid'] ?? false)) {
            return $this->blocked((string) ($summary['reason_code'] ?? 'C171_FINAL_CLOSURE_SUMMARY_INVALID'), $summary);
        }
        $database = $this->verifyDatabaseIdentity($evidence);
        if (! ($database['valid'] ?? false)) {
            return $this->blocked((string) ($database['reason_code'] ?? 'C171_FINAL_CLOSURE_DATABASE_IDENTITY_INVALID'), $database);
        }

        $comparison = $this->buildClosureDecision($evidence['anchor'], $evidence['final_candidates']);
        if (! ($comparison['closure_allowed'] ?? false)) {
            return $this->blocked('C171_FINAL_CLOSURE_DECISION_NOT_PROVEN', $comparison);
        }

        $evalCountBefore = DB::table('watchlist_bt_eval')->count();
        $activeCountBefore = DB::table('watchlist_param_sets')->where('policy_code', 'WS')->where('status', 'ACTIVE')->count();
        $planCountBefore = DB::table('watchlist_plan_runs')->count();

        $result = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => self::SUCCESS_STATUS,
            'reason_code' => self::REASON_CODE,
            'approval_reference' => $approvalReference,
            'final_decision' => self::FINAL_DECISION,
            'c171_topic_closed' => true,
            'strategy_quality_result' => 'FAILED_CANONICAL_IS_NOT_READY',
            'research_information_result' => 'POSITIVE_ACTIONABLE_EVIDENCE',
            'pipeline_version' => self::PIPELINE_VERSION,
            'pipeline_hash' => self::PIPELINE_HASH,
            'canonical_is_from' => self::CANONICAL_IS_FROM,
            'canonical_is_to' => self::CANONICAL_IS_TO,
            'anchor' => $evidence['anchor'],
            'final_candidates' => $evidence['final_candidates'],
            'final_candidate_count' => count($evidence['final_candidates']),
            'final_passing_candidate_count' => 0,
            'comparison' => $comparison,
            'summary_csv_file_sha1' => self::SUMMARY_FILE_SHA1,
            'decision_contract_artifact_hash' => self::DECISION_ARTIFACT_HASH,
            'decision_contract_file_sha1' => self::DECISION_FILE_SHA1,
            'database_identity_verified' => true,
            'database_eval_ids_verified' => [204, 205, 206, 207],
            'database_param_set_ids_verified' => [11, 12, 13, 14],
            'additional_c171_candidate_catalog_allowed' => false,
            'oos_allowed' => false,
            'c172_allowed' => false,
            'promotion_allowed' => false,
            'plan_allowed' => false,
            'production_ready' => false,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'recommendation_persisted' => false,
            'confirm_mutated' => false,
            'production_activation_executed' => false,
            'eval_count_before' => $evalCountBefore,
            'active_paramset_count_before' => $activeCountBefore,
            'plan_run_count_before' => $planCountBefore,
            'next_recommendation' => 'C171_CLOSED_BEGIN_SEPARATE_STRATEGY_RESEARCH_ONLY_UNDER_NEW_APPROVED_SCOPE',
        ];

        $evalCountAfter = DB::table('watchlist_bt_eval')->count();
        $activeCountAfter = DB::table('watchlist_param_sets')->where('policy_code', 'WS')->where('status', 'ACTIVE')->count();
        $planCountAfter = DB::table('watchlist_plan_runs')->count();
        $result['eval_count_after'] = $evalCountAfter;
        $result['active_paramset_count_after'] = $activeCountAfter;
        $result['plan_run_count_after'] = $planCountAfter;
        $result['database_mutated'] = $evalCountBefore !== $evalCountAfter
            || $activeCountBefore !== $activeCountAfter
            || $planCountBefore !== $planCountAfter;
        if ($result['database_mutated']) {
            return $this->blocked('C171_FINAL_CLOSURE_DATABASE_MUTATION_DETECTED', $result);
        }

        $result['artifact_hash'] = $this->identity->stableHash($result);
        $result['write'] = $this->writeArtifact($outputPath, $result, (bool) ($options['overwrite'] ?? false));

        return $result;
    }

    public function buildClosureDecision(array $anchor, array $finalCandidates): array
    {
        $passing = [];
        $comparisons = [];
        $anchorMetrics = $anchor['metrics'] ?? [];
        foreach ($finalCandidates as $candidate) {
            if (($candidate['canonical_is_gates_pass'] ?? false) === true) {
                $passing[] = (int) ($candidate['param_set_id'] ?? 0);
            }
            $metrics = $candidate['metrics'] ?? [];
            $comparisons[] = [
                'param_set_id' => (int) ($candidate['param_set_id'] ?? 0),
                'eval_id' => (int) ($candidate['eval_id'] ?? 0),
                'avg_ret_net_delta_vs_anchor' => (float) ($metrics['avg_ret_net_top'] ?? 0) - (float) ($anchorMetrics['avg_ret_net_top'] ?? 0),
                'win_rate_delta_vs_anchor' => (float) ($metrics['win_rate_top'] ?? 0) - (float) ($anchorMetrics['win_rate_top'] ?? 0),
                'median_ret_net_delta_vs_anchor' => (float) ($metrics['median_ret_net_top'] ?? 0) - (float) ($anchorMetrics['median_ret_net_top'] ?? 0),
                'p25_ret_net_delta_vs_anchor' => (float) ($metrics['p25_ret_net_top'] ?? 0) - (float) ($anchorMetrics['p25_ret_net_top'] ?? 0),
                'month_win_rate_min_delta_vs_anchor' => (float) ($metrics['month_win_rate_min'] ?? 0) - (float) ($anchorMetrics['month_win_rate_min'] ?? 0),
                'month_avg_ret_net_min_delta_vs_anchor' => (float) ($metrics['month_avg_ret_net_min'] ?? 0) - (float) ($anchorMetrics['month_avg_ret_net_min'] ?? 0),
                'period_fail_count_delta_vs_anchor' => (int) ($metrics['period_fail_count'] ?? 0) - (int) ($anchorMetrics['period_fail_count'] ?? 0),
            ];
        }

        $anchorRemainsBest = true;
        foreach ($finalCandidates as $candidate) {
            $metrics = $candidate['metrics'] ?? [];
            if ((float) ($metrics['avg_ret_net_top'] ?? 0) > (float) ($anchorMetrics['avg_ret_net_top'] ?? 0)
                && (float) ($metrics['win_rate_top'] ?? 0) >= (float) ($anchorMetrics['win_rate_top'] ?? 0)
                && (int) ($metrics['period_fail_count'] ?? PHP_INT_MAX) <= (int) ($anchorMetrics['period_fail_count'] ?? 0)) {
                $anchorRemainsBest = false;
            }
        }

        return [
            'passing_param_set_ids' => $passing,
            'passing_candidate_count' => count($passing),
            'anchor_param_set_id' => (int) ($anchor['param_set_id'] ?? 0),
            'anchor_eval_id' => (int) ($anchor['eval_id'] ?? 0),
            'anchor_remains_best_overall' => $anchorRemainsBest,
            'candidate_deltas_vs_anchor' => $comparisons,
            'closure_allowed' => count($passing) === 0 && count($finalCandidates) === 3 && $anchorRemainsBest,
            'final_decision' => count($passing) === 0
                ? self::FINAL_DECISION
                : 'C171_FINAL_REVIEW_REQUIRED_BEFORE_C172',
        ];
    }

    private function loadAndVerifyEvidenceBundle(string $anchorPath, string $directory): array
    {
        $anchor = $this->loadAndVerifyArtifact($anchorPath, self::ANCHOR, 11, false);
        if (! ($anchor['valid'] ?? false)) {
            return $anchor;
        }
        $finals = [];
        foreach (self::FINAL_EVIDENCE as $paramSetId => $expected) {
            $path = rtrim($directory, '/\\').DIRECTORY_SEPARATOR.'c171-final-official-is-paramset-'.$paramSetId.'.json';
            $candidate = $this->loadAndVerifyArtifact($path, $expected, $paramSetId, true);
            if (! ($candidate['valid'] ?? false)) {
                return $candidate;
            }
            $finals[] = $candidate['evidence'];
        }

        return [
            'valid' => true,
            'reason_code' => 'C171_FINAL_CLOSURE_EVIDENCE_BUNDLE_VALID',
            'anchor' => $anchor['evidence'],
            'final_candidates' => $finals,
        ];
    }

    private function loadAndVerifyArtifact(string $path, array $expected, int $paramSetId, bool $finalCatalog): array
    {
        if (! is_file($path)) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_EVIDENCE_FILE_MISSING', 'path' => $path];
        }
        if (strtolower((string) sha1_file($path)) !== (string) $expected['file_sha1']) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_EVIDENCE_FILE_SHA1_MISMATCH', 'param_set_id' => $paramSetId];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        $evaluation = $artifact['is_calibration']['evaluations'][0] ?? null;
        if (! is_array($artifact) || ! is_array($evaluation)) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_EVIDENCE_JSON_INVALID', 'param_set_id' => $paramSetId];
        }
        $manifest = $evaluation['official_evidence_manifest'] ?? [];
        $gates = $evaluation['gates'] ?? [];
        $catalogCode = $finalCatalog ? self::FINAL_CATALOG_CODE : self::ANCHOR['catalog_code'];
        $catalogVersion = $finalCatalog ? self::FINAL_CATALOG_VERSION : self::ANCHOR['catalog_version'];
        $catalogHash = $finalCatalog ? self::FINAL_CATALOG_HASH : self::ANCHOR['catalog_hash'];
        if ((int) ($artifact['param_set_id'] ?? 0) !== $paramSetId
            || (int) ($evaluation['eval_id'] ?? 0) !== (int) $expected['eval_id']
            || (int) ($evaluation['param_id'] ?? 0) !== (int) $expected['bt_param_id']
            || (string) ($evaluation['row_code'] ?? '') !== (string) $expected['row_code']
            || (string) ($artifact['params_hash'] ?? '') !== (string) $expected['params_hash']
            || (string) ($artifact['artifact_hash'] ?? '') !== (string) $expected['artifact_hash']
            || (string) ($manifest['evidence_manifest_hash'] ?? '') !== (string) $expected['evidence_manifest_hash']
            || (string) ($artifact['evidence_pipeline_version'] ?? '') !== self::PIPELINE_VERSION
            || (string) ($artifact['evidence_pipeline_hash'] ?? '') !== self::PIPELINE_HASH
            || (string) ($artifact['is_from'] ?? '') !== self::CANONICAL_IS_FROM
            || (string) ($artifact['is_to'] ?? '') !== self::CANONICAL_IS_TO
            || (string) ($artifact['bt_binding']['catalog_code'] ?? '') !== $catalogCode
            || (string) ($artifact['bt_binding']['catalog_version'] ?? '') !== $catalogVersion
            || (string) ($artifact['bt_binding']['catalog_hash'] ?? '') !== $catalogHash
            || ($artifact['status'] ?? '') !== 'C171_VERSIONED_OFFICIAL_IS_EVIDENCE_PERSISTED_CANONICAL_GATES_FAILED_OOS_NOT_RUN'
            || ($artifact['canonical_is_gates_pass'] ?? true) !== false
            || ($artifact['strict_is_boundary'] ?? false) !== true
            || ($artifact['oos_runtime_invoked'] ?? true) !== false
            || ($artifact['paramset_promoted'] ?? true) !== false
            || ($artifact['plan_run_created'] ?? true) !== false
            || ($artifact['production_ready'] ?? true) !== false
            || ($evaluation['official_evidence_persistence_status'] ?? '') !== 'INSERTED'
            || ($artifact['execution_route_proof']['pass'] ?? false) !== true) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_EVIDENCE_CONTRACT_MISMATCH', 'param_set_id' => $paramSetId];
        }
        foreach (['minimum_trade_count', 'minimum_coverage', 'average_return_positive'] as $gate) {
            if (($gates[$gate] ?? false) !== true) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_POSITIVE_GATE_IDENTITY_MISMATCH', 'param_set_id' => $paramSetId];
            }
        }
        foreach (['median_return_non_negative', 'p25_downside_bound', 'monthly_win_rate_floor', 'monthly_average_floor'] as $gate) {
            if (($gates[$gate] ?? true) !== false) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_FAILED_GATE_IDENTITY_MISMATCH', 'param_set_id' => $paramSetId];
            }
        }

        return [
            'valid' => true,
            'reason_code' => 'C171_FINAL_CLOSURE_EVIDENCE_VALID',
            'evidence' => [
                'param_set_id' => $paramSetId,
                'eval_id' => (int) $evaluation['eval_id'],
                'bt_param_id' => (int) $evaluation['param_id'],
                'row_code' => (string) $evaluation['row_code'],
                'catalog_code' => $catalogCode,
                'catalog_version' => $catalogVersion,
                'catalog_hash' => $catalogHash,
                'params_hash' => (string) $artifact['params_hash'],
                'artifact_hash' => (string) $artifact['artifact_hash'],
                'file_sha1' => strtolower((string) sha1_file($path)),
                'evidence_manifest_hash' => (string) $manifest['evidence_manifest_hash'],
                'canonical_is_gates_pass' => false,
                'metrics' => $evaluation['metrics'],
                'gates' => $gates,
            ],
        ];
    }

    private function loadAndVerifySummary(string $path, array $finalCandidates): array
    {
        if (! is_file($path)) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_FILE_MISSING'];
        }
        if (strtolower((string) sha1_file($path)) !== self::SUMMARY_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_FILE_SHA1_MISMATCH'];
        }
        $parsed = $this->readSummaryCsv($path);
        if (! ($parsed['valid'] ?? false)) {
            return $parsed;
        }
        $rows = $parsed['rows'];
        if (count($rows) !== 3) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_ROW_COUNT_MISMATCH'];
        }
        $byId = [];
        foreach ($finalCandidates as $candidate) {
            $byId[(int) $candidate['param_set_id']] = $candidate;
        }
        foreach ($rows as $row) {
            $paramSetId = (int) ($row['param_set_id'] ?? 0);
            $candidate = $byId[$paramSetId] ?? null;
            if (! is_array($candidate)
                || (int) ($row['eval_id'] ?? 0) !== (int) $candidate['eval_id']
                || (string) ($row['params_hash'] ?? '') !== (string) $candidate['params_hash']
                || strtolower((string) ($row['artifact_hash'] ?? '')) !== strtolower((string) $candidate['artifact_hash'])
                || strtolower((string) ($row['file_sha1'] ?? '')) !== strtolower((string) $candidate['file_sha1'])
                || strtolower((string) ($row['canonical_is_gates_pass'] ?? '')) !== 'false'
                || (string) ($row['pipeline_version'] ?? '') !== self::PIPELINE_VERSION
                || (string) ($row['pipeline_hash'] ?? '') !== self::PIPELINE_HASH) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_IDENTITY_MISMATCH', 'param_set_id' => $paramSetId];
            }
        }

        return ['valid' => true, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_VALID', 'row_count' => 3];
    }

    private function readSummaryCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_OPEN_FAILED'];
        }

        $rawHeader = fgets($handle);
        if ($rawHeader === false) {
            fclose($handle);
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_HEADER_INVALID'];
        }

        // PowerShell Export-Csv writes an UTF-8 BOM before the opening quote.
        // Strip it from the raw line before CSV parsing so the first header is
        // parsed as param_set_id rather than the literal "param_set_id".
        $rawHeader = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeader);
        $header = str_getcsv(rtrim((string) $rawHeader, "\r\n"));
        if (! is_array($header) || $header === [] || ($header[0] ?? null) !== 'param_set_id') {
            fclose($handle);
            return [
                'valid' => false,
                'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_HEADER_INVALID',
                'first_header' => $header[0] ?? null,
            ];
        }

        $requiredHeaders = [
            'param_set_id', 'eval_id', 'params_hash', 'canonical_is_gates_pass',
            'pipeline_version', 'pipeline_hash', 'artifact_hash', 'file_sha1',
        ];
        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $header, true)) {
                fclose($handle);
                return [
                    'valid' => false,
                    'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_HEADER_INVALID',
                    'missing_header' => $requiredHeader,
                ];
            }
        }

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== count($header)) {
                fclose($handle);
                return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_ROW_INVALID'];
            }
            $row = array_combine($header, $values);
            if (! is_array($row)) {
                fclose($handle);
                return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_ROW_INVALID'];
            }
            $rows[] = $row;
        }
        fclose($handle);

        return [
            'valid' => true,
            'reason_code' => 'C171_FINAL_CLOSURE_SUMMARY_CSV_PARSED',
            'header' => $header,
            'rows' => $rows,
        ];
    }

    private function verifyDatabaseIdentity(array $evidence): array
    {
        $all = array_merge([$evidence['anchor']], $evidence['final_candidates']);
        foreach ($all as $item) {
            $eval = DB::table('watchlist_bt_eval')->where('eval_id', (int) $item['eval_id'])->first();
            $paramset = DB::table('watchlist_param_sets')->where('param_set_id', (int) $item['param_set_id'])->first();
            if (! $eval || ! $paramset) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_DATABASE_ROW_MISSING', 'eval_id' => $item['eval_id']];
            }
            if ((string) $eval->policy_code !== 'WS'
                || (int) $eval->param_id !== (int) $item['bt_param_id']
                || (string) $eval->catalog_code !== (string) $item['catalog_code']
                || (string) $eval->catalog_version !== (string) $item['catalog_version']
                || (string) $eval->catalog_hash !== (string) $item['catalog_hash']
                || (string) $eval->paramset_hash !== (string) $item['params_hash']
                || (string) $eval->evidence_pipeline_version !== self::PIPELINE_VERSION
                || (string) $eval->evidence_pipeline_hash !== self::PIPELINE_HASH
                || (string) $eval->from_date !== self::CANONICAL_IS_FROM
                || (string) $eval->to_date !== self::CANONICAL_IS_TO
                || (string) $eval->evidence_manifest_hash !== (string) $item['evidence_manifest_hash']
                || (int) $eval->picks_count !== (int) $item['metrics']['picks_count']
                || (int) $eval->days_covered !== (int) $item['metrics']['days_covered']
                || ! $this->decimalMatches($eval->avg_ret_net_top, $item['metrics']['avg_ret_net_top'])
                || ! $this->decimalMatches($eval->median_ret_net_top, $item['metrics']['median_ret_net_top'])
                || ! $this->decimalMatches($eval->p25_ret_net_top, $item['metrics']['p25_ret_net_top'])
                || ! $this->decimalMatches($eval->win_rate_top, $item['metrics']['win_rate_top'])
                || ! $this->decimalMatches($eval->month_win_rate_min, $item['metrics']['month_win_rate_min'])
                || ! $this->decimalMatches($eval->month_avg_ret_net_min, $item['metrics']['month_avg_ret_net_min'])
                || (int) $eval->period_fail_count !== (int) $item['metrics']['period_fail_count']) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_DATABASE_EVAL_IDENTITY_MISMATCH', 'eval_id' => $item['eval_id']];
            }
            if ((string) $paramset->policy_code !== 'WS'
                || (string) $paramset->status !== 'DRAFT'
                || (string) $paramset->params_hash !== (string) $item['params_hash']) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_DATABASE_PARAMSET_IDENTITY_MISMATCH', 'param_set_id' => $item['param_set_id']];
            }
        }

        return ['valid' => true, 'reason_code' => 'C171_FINAL_CLOSURE_DATABASE_IDENTITY_VALID'];
    }

    private function decimalMatches($databaseValue, $artifactValue): bool
    {
        return abs((float) $databaseValue - (float) $artifactValue) <= 0.0000011;
    }

    private function loadAndVerifyDecisionContract(): array
    {
        $path = base_path('docs/watchlist/audit/_artifacts/c171-final-failed-not-ready-closure-decision.json');
        if (! is_file($path) || strtolower((string) sha1_file($path)) !== self::DECISION_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_DECISION_FILE_IDENTITY_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_DECISION_JSON_INVALID'];
        }
        $hash = (string) ($artifact['artifact_hash'] ?? '');
        unset($artifact['artifact_hash']);
        if ($hash !== self::DECISION_ARTIFACT_HASH || $this->identity->stableHash($artifact) !== self::DECISION_ARTIFACT_HASH) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_CLOSURE_DECISION_ARTIFACT_HASH_MISMATCH'];
        }

        return ['valid' => true, 'reason_code' => 'C171_FINAL_CLOSURE_DECISION_CONTRACT_VALID'];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        $json = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('C171_FINAL_CLOSURE_ARTIFACT_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('C171_FINAL_CLOSURE_OUTPUT_DIRECTORY_CREATE_FAILED: '.$directory);
        }
        if (is_file($path)) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return ['status' => 'IDEMPOTENT', 'path' => $path, 'file_sha1' => sha1($existing)];
            }
            if (! $overwrite) {
                throw new \RuntimeException('C171_FINAL_CLOSURE_ARTIFACT_EXISTS_DIFFERENT: '.$path);
            }
        }
        if (file_put_contents($path, $json, LOCK_EX) === false) {
            throw new \RuntimeException('C171_FINAL_CLOSURE_ARTIFACT_WRITE_FAILED: '.$path);
        }

        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1($json)];
    }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'final_decision' => 'NOT_SEALED',
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
