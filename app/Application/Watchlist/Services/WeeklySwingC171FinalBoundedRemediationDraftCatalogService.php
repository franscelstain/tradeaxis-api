<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingC171FinalBoundedRemediationDraftCatalogService
{
    public const RUN_CODE = 'C171_FINAL_BOUNDED_REMEDIATION_CATALOG_AND_CLOSURE_RULE_LOCK';
    public const APPROVAL_REFERENCE = 'C171_OPERATOR_APPROVED_FINAL_BOUNDED_REMEDIATION_CATALOG_PERSISTENCE_ONLY';
    public const SOURCE_EVAL_ID = 204;
    public const SOURCE_PARAM_SET_ID = 11;
    public const SOURCE_PARAMS_HASH = 'c93bae2b761028d6b236f368d5b19bb4f498715a';
    public const SOURCE_PIPELINE_VERSION = 'WS_C171_C01_TICK_RISK_GUARD_ENFORCEMENT_PIPELINE_V3';
    public const SOURCE_PIPELINE_HASH = '9e9933b363026623b7ab5629f3281fa680a53a2e';
    public const SUMMARY_FILE_SHA1 = '613ebb88047ddbe707ef354699ad744f73d2d798';
    public const DESIGN_ARTIFACT_HASH = '81dbe104197cd2e12abb692e1e66668ff94b9725';
    public const DESIGN_FILE_SHA1 = '90b18e7b93f497d7e193c52e18d2cad539015280';
    public const EVIDENCE = [
        5 => ['eval_id' => 199, 'params_hash' => 'e49b47449be1bc59659455d315bb6aaf5f4f9491', 'artifact_hash' => '08346e0edad9218b724ef9ab3b3721c78011a0cb', 'file_sha1' => 'bcab17a6e9c931a3b13dc9ba5043392911527ec2'],
        7 => ['eval_id' => 200, 'params_hash' => 'd13f9bb408d7c08a7d27bbac638f77218846d234', 'artifact_hash' => '742f5097b62465ab32937e809c515ad046f8dd41', 'file_sha1' => '4333202870d94220d9b75075d40412de6694699e'],
        8 => ['eval_id' => 201, 'params_hash' => 'b87fd352dd0fc98242a0ad4a9ae72922f5d7cd66', 'artifact_hash' => 'cc34ba030d790751305eda3a6d9d14e63fb10a27', 'file_sha1' => '0352703b15326793b7eef365bf4cd16c26c5ffbf'],
        9 => ['eval_id' => 202, 'params_hash' => '794d9487c9566aeb611a132f2e8951e9013ed2ce', 'artifact_hash' => '17670dc076c2ed3a8c21b8835f93ff7bf1505ab2', 'file_sha1' => 'f421999d5b5b03d9d5366929d7ae0c1eea62d025'],
        10 => ['eval_id' => 203, 'params_hash' => '873c1d1eb5f7f3dccee0f8619ec30e61eb838078', 'artifact_hash' => '0ae379be0ec80c1aac17d37cd94728b84f41532c', 'file_sha1' => '684f055e7f0a830fceb7043d7696b539ef9b4fd2'],
        11 => ['eval_id' => 204, 'params_hash' => 'c93bae2b761028d6b236f368d5b19bb4f498715a', 'artifact_hash' => 'bc81941261f0af27f8043b4033da1d2a9b2f331a', 'file_sha1' => 'd811d80cbe3677835cb3bbb6f3f87462a1744eaf'],
    ];

    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetDraftImportService $draftImport;
    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WatchlistBacktestParamGridRepository $gridRepository;

    public function __construct(
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetDraftImportService $draftImport = null,
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WatchlistBacktestParamGridRepository $gridRepository = null
    ) {
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->draftImport = $draftImport ?: new WeeklySwingParamsetDraftImportService();
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->gridRepository = $gridRepository ?: new WatchlistBacktestParamGridRepository();
    }

    public function execute(
        int $sourceEvalId,
        int $sourceParamSetId,
        string $artifactDirectory,
        string $summaryCsvPath,
        string $approvalReference,
        bool $operatorApproved,
        string $outputDirectory,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_OPERATOR_APPROVAL_MISSING');
        }
        if ($sourceEvalId !== self::SOURCE_EVAL_ID || $sourceParamSetId !== self::SOURCE_PARAM_SET_ID) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_SOURCE_IDENTITY_INVALID');
        }
        foreach (['watchlist_param_sets', 'watchlist_bt_eval', 'watchlist_bt_param_grid'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }

        $sourceDraft = DB::table('watchlist_param_sets')->where('param_set_id', $sourceParamSetId)->first();
        $sourceEval = DB::table('watchlist_bt_eval')->where('eval_id', $sourceEvalId)->first();
        if (! $sourceDraft || ! $sourceEval) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_SOURCE_NOT_FOUND');
        }
        if ((string) $sourceDraft->status !== 'DRAFT'
            || (string) $sourceDraft->policy_code !== 'WS'
            || (string) $sourceDraft->params_hash !== self::SOURCE_PARAMS_HASH
            || (int) $sourceEval->param_id !== 166
            || (string) $sourceEval->paramset_hash !== self::SOURCE_PARAMS_HASH
            || (string) $sourceEval->evidence_pipeline_version !== self::SOURCE_PIPELINE_VERSION
            || (string) $sourceEval->evidence_pipeline_hash !== self::SOURCE_PIPELINE_HASH
            || (string) $sourceEval->from_date !== WeeklySwingC171TradeEvidenceDiagnosticService::CANONICAL_IS_FROM
            || (string) $sourceEval->to_date !== WeeklySwingC171TradeEvidenceDiagnosticService::CANONICAL_IS_TO) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_SOURCE_MISMATCH');
        }

        $sourcePayload = json_decode((string) $sourceDraft->params_json, true);
        if (! is_array($sourcePayload)) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_SOURCE_PARAMSET_INVALID');
        }
        $sourceValidation = $this->validator->validate($sourcePayload);
        if (! ($sourceValidation['valid'] ?? false)
            || (string) ($sourceValidation['canonical_hash'] ?? '') !== self::SOURCE_PARAMS_HASH) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_SOURCE_PARAMSET_INVALID', ['validation' => $sourceValidation]);
        }

        $evidence = $this->loadAndVerifyEvidenceBundle($artifactDirectory);
        if (! ($evidence['valid'] ?? false)) {
            return $this->blocked((string) ($evidence['reason_code'] ?? 'C171_FINAL_BOUNDED_REMEDIATION_EVIDENCE_INVALID'), $evidence);
        }
        $summary = $this->loadAndVerifySummary($summaryCsvPath);
        if (! ($summary['valid'] ?? false)) {
            return $this->blocked((string) ($summary['reason_code'] ?? 'C171_FINAL_BOUNDED_REMEDIATION_SUMMARY_INVALID'), $summary);
        }
        $design = $this->loadAndVerifyDesignArtifact();
        if (! ($design['valid'] ?? false)) {
            return $this->blocked((string) ($design['reason_code'] ?? 'C171_FINAL_BOUNDED_REMEDIATION_DESIGN_INVALID'), $design);
        }

        try {
            $expectedParamsetHashes = $this->deriveExpectedCandidateHashes(
                $sourceValidation['canonical_payload'],
                WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::rows()
            );
        } catch (\Throwable $exception) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_CANDIDATE_HASH_PREFLIGHT_FAILED', [
                'error' => $exception->getMessage(),
            ]);
        }
        $candidateHashManifest = [];
        foreach ($expectedParamsetHashes as $rowCode => $paramsHash) {
            $candidateHashManifest[] = ['row_code' => $rowCode, 'params_hash' => $paramsHash];
        }
        $candidateHashManifestHash = $this->identity->stableHash($candidateHashManifest);

        $activeBefore = DB::table('watchlist_param_sets')->where('policy_code', 'WS')->where('status', 'ACTIVE')->count();
        $evalBefore = DB::table('watchlist_bt_eval')->count();
        $planBefore = Schema::hasTable('watchlist_plan_runs') ? DB::table('watchlist_plan_runs')->count() : 0;

        try {
            $catalogSeed = $this->gridRepository->seedCatalog(WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::rows());
            $catalogRows = $this->gridRepository->allForCatalog(
                WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE,
                'WS'
            );
            if (count($catalogRows) !== WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_COUNT) {
                throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_CATALOG_ROW_COUNT_MISMATCH');
            }

            $drafts = [];
            foreach ($catalogRows as $row) {
                $payload = $this->buildCandidatePayload($sourceValidation['canonical_payload'], $row);
                $source = [
                    'stage' => self::RUN_CODE,
                    'approval_reference' => $approvalReference,
                    'source_eval_id' => self::SOURCE_EVAL_ID,
                    'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
                    'source_params_hash' => self::SOURCE_PARAMS_HASH,
                    'source_pipeline_version' => self::SOURCE_PIPELINE_VERSION,
                    'source_pipeline_hash' => self::SOURCE_PIPELINE_HASH,
                    'source_evidence_manifest_hash' => (string) $evidence['manifest_hash'],
                    'candidate_design_artifact_hash' => self::DESIGN_ARTIFACT_HASH,
                    'candidate_design_file_sha1' => self::DESIGN_FILE_SHA1,
                    'catalog_code' => WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE,
                    'catalog_version' => WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_VERSION,
                    'catalog_hash' => WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::hash(),
                    'catalog_row_code' => (string) $row['row_code'],
                    'catalog_row_hash' => (string) $row['row_hash'],
                    'decision_time_fields_only' => true,
                    'oos_used' => false,
                    'canonical_gates_changed' => false,
                    'final_remediation' => true,
                    'further_remediation_allowed_if_no_pass' => false,
                ];
                $import = $this->draftImport->execute(
                    $payload,
                    (int) $row['param_id'],
                    WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE,
                    $source
                );
                if (($import['status'] ?? '') !== 'DRAFT_PERSISTED'
                    || ($import['paramset_status'] ?? '') !== 'DRAFT') {
                    throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_DRAFT_PERSISTENCE_FAILED: '.(string) ($import['reason_code'] ?? 'UNKNOWN'));
                }

                $rowCode = (string) $row['row_code'];
                $expectedParamsetHash = $expectedParamsetHashes[$rowCode] ?? null;
                if ($expectedParamsetHash === null
                    || (string) ($import['validation']['canonical_hash'] ?? '') !== $expectedParamsetHash
                    || (string) ($import['persistence']['params_hash'] ?? '') !== $expectedParamsetHash) {
                    throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_DRAFT_HASH_MISMATCH: '.$rowCode);
                }

                $canonicalPath = rtrim($outputDirectory, '/\\').DIRECTORY_SEPARATOR.strtolower($rowCode).'.json';
                $write = $this->writeCanonicalJson(
                    $canonicalPath,
                    $import['validation']['canonical_payload'],
                    (bool) ($options['overwrite'] ?? false)
                );
                $drafts[] = [
                    'row_code' => $rowCode,
                    'row_hash' => (string) $row['row_hash'],
                    'bt_param_id' => (int) $row['param_id'],
                    'param_set_id' => (int) $import['param_set_id'],
                    'paramset_status' => (string) $import['paramset_status'],
                    'params_hash' => (string) $import['validation']['canonical_hash'],
                    'expected_params_hash' => $expectedParamsetHash,
                    'persistence_status' => (string) ($import['persistence']['status'] ?? ''),
                    'canonical_file' => $write,
                    'official_is_run' => false,
                    'oos_allowed' => false,
                ];
            }
        } catch (\Throwable $exception) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_CATALOG_PERSISTENCE_FAILED', [
                'error' => $exception->getMessage(),
            ]);
        }

        $activeAfter = DB::table('watchlist_param_sets')->where('policy_code', 'WS')->where('status', 'ACTIVE')->count();
        $evalAfter = DB::table('watchlist_bt_eval')->count();
        $planAfter = Schema::hasTable('watchlist_plan_runs') ? DB::table('watchlist_plan_runs')->count() : 0;
        if ($activeAfter !== $activeBefore || $evalAfter !== $evalBefore || $planAfter !== $planBefore) {
            return $this->blocked('C171_FINAL_BOUNDED_REMEDIATION_FORBIDDEN_MUTATION_DETECTED');
        }

        usort($drafts, function (array $a, array $b): int {
            return strcmp($a['row_code'], $b['row_code']);
        });
        $result = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'C171_FINAL_BOUNDED_REMEDIATION_CATALOG_PERSISTED_CLOSURE_RULE_LOCKED',
            'reason_code' => 'C171_THREE_FINAL_IMMUTABLE_DRAFTS_PERSISTED_OFFICIAL_IS_NOT_RUN',
            'approval_reference' => $approvalReference,
            'source_eval_id' => $sourceEvalId,
            'source_param_set_id' => $sourceParamSetId,
            'source_params_hash' => self::SOURCE_PARAMS_HASH,
            'source_pipeline_version' => self::SOURCE_PIPELINE_VERSION,
            'source_pipeline_hash' => self::SOURCE_PIPELINE_HASH,
            'source_evidence_manifest_hash' => (string) $evidence['manifest_hash'],
            'final_decision' => 'ONE_FINAL_BOUNDED_REMEDIATION_ALLOWED',
            'closure_rule_if_no_pass' => 'C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION',
            'closure_rule_if_pass' => 'C171_FINAL_REVIEW_REQUIRED_BEFORE_C172',
            'additional_c171_candidate_catalog_allowed' => false,
            'candidate_design_artifact_hash' => self::DESIGN_ARTIFACT_HASH,
            'catalog_seed' => $catalogSeed,
            'catalog_code' => WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE,
            'catalog_version' => WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_VERSION,
            'catalog_hash' => WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::hash(),
            'catalog_row_count' => count($drafts),
            'candidate_hash_contract' => 'DERIVED_FROM_IMMUTABLE_PARAMSET_11_V3_AND_FINAL_CATALOG_ROW',
            'candidate_hash_manifest' => $candidateHashManifest,
            'candidate_hash_manifest_hash' => $candidateHashManifestHash,
            'drafts' => $drafts,
            'draft_paramset_created_count' => count(array_filter($drafts, function (array $draft): bool {
                return $draft['persistence_status'] === 'INSERTED';
            })),
            'draft_paramset_idempotent_count' => count(array_filter($drafts, function (array $draft): bool {
                return $draft['persistence_status'] === 'IDEMPOTENT';
            })),
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'recommendation_persisted' => false,
            'confirm_mutated' => false,
            'production_ready' => false,
            'next_recommendation' => 'C171_RUN_VERSIONED_OFFICIAL_IS_FOR_FINAL_BOUNDED_REMEDIATION_DRAFTS_THEN_CLOSE_OR_ADVANCE',
        ];
        $result['artifact_hash'] = $this->identity->stableHash($result);
        $result['write'] = $this->writeArtifact($outputPath, $result, (bool) ($options['overwrite'] ?? false));

        return $result;
    }

    public function deriveExpectedCandidateHashes(array $sourceCanonicalPayload, array $catalogRows): array
    {
        $hashes = [];
        $seenHashes = [];
        foreach ($catalogRows as $row) {
            $rowCode = trim((string) ($row['row_code'] ?? ''));
            if ($rowCode === '' || isset($hashes[$rowCode])) {
                throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_DRAFT_CANDIDATE_ROW_IDENTITY_INVALID');
            }
            $payload = $this->buildCandidatePayload($sourceCanonicalPayload, $row);
            $validation = $this->validator->validate($payload);
            if (! ($validation['valid'] ?? false)) {
                throw new \RuntimeException(
                    'C171_FINAL_BOUNDED_REMEDIATION_DRAFT_CANDIDATE_VALIDATION_FAILED: '.$rowCode.'|'.json_encode($validation['errors'] ?? [])
                );
            }
            $paramsHash = (string) ($validation['canonical_hash'] ?? '');
            if ($paramsHash === '' || $paramsHash === self::SOURCE_PARAMS_HASH || isset($seenHashes[$paramsHash])) {
                throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_DRAFT_CANDIDATE_HASH_IDENTITY_INVALID: '.$rowCode);
            }
            $hashes[$rowCode] = $paramsHash;
            $seenHashes[$paramsHash] = true;
        }
        if (count($hashes) !== WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_COUNT) {
            throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_DRAFT_CANDIDATE_HASH_COUNT_MISMATCH');
        }

        return $hashes;
    }

    public function buildCandidatePayload(array $sourcePayload, array $row): array
    {
        $payload = $sourcePayload;
        $payload['paramset_code'] = (string) $row['row_code'];
        $rationale = 'C171 final bounded remediation candidate '.(string) $row['row_code'].' derived from immutable V3 anchor eval_id 204; no further remediation is allowed if the final catalog has no canonical IS pass.';
        $triggers = [
            'C171_C01_V3_FINAL_COMPARATIVE_COMPLETED',
            'C171_FINAL_DECISION_ARTIFACT_'.self::DESIGN_ARTIFACT_HASH,
            'C171_CATALOG_ROW_'.(string) $row['row_hash'],
        ];

        $this->setAuditValue($payload, 'liquidity', 'min_dv20_idr', (int) $row['min_dv20_idr'], $rationale, $triggers);
        $this->setAuditValue($payload, 'liquidity', 'max_dv20_idr', (int) $row['max_dv20_idr'], $rationale, $triggers);
        $this->setAuditValue($payload, 'liquidity', 'dv20_strong_idr', (int) $row['dv20_strong_idr'], $rationale, $triggers);
        $this->setAuditValue($payload, 'volume', 'min_vol_ratio', (float) $row['min_vol_ratio'], $rationale, $triggers);
        $this->setAuditValue($payload, 'volume', 'max_vol_ratio', (float) $row['max_vol_ratio'], $rationale, $triggers);
        foreach (['min_atr14_pct','max_atr14_pct','atr_ideal_low','atr_ideal_high','stop_atr_mult','min_rr'] as $key) {
            $this->setAuditValue($payload, 'risk', $key, (float) $row[$key], $rationale, $triggers);
        }
        if ($row['max_signal_tick_risk_expansion_pct'] === null) {
            unset($payload['risk']['max_signal_tick_risk_expansion_pct']);
        } else {
            $this->setAuditValue(
                $payload,
                'risk',
                'max_signal_tick_risk_expansion_pct',
                (float) $row['max_signal_tick_risk_expansion_pct'],
                $rationale,
                $triggers
            );
        }
        foreach (['roc_lo','roc_hi','mom_roc20_soft_min','bo_near_below_pct','bo_max_ext_pct'] as $key) {
            $this->setAuditValue($payload, 'setup', $key, (float) $row[$key], $rationale, $triggers);
        }
        $this->setAuditValue($payload, 'scoring', 'weights', [
            'momentum' => (float) $row['w_momentum'],
            'volume' => (float) $row['w_volume'],
            'breakout' => (float) $row['w_breakout'],
            'risk' => (float) $row['w_risk'],
        ], $rationale, $triggers);
        $this->setAuditValue($payload, 'grouping', 'top_picks_target', (int) $row['top_picks_target'], $rationale, $triggers);
        $this->setAuditValue($payload, 'grouping', 'secondary_target', (int) $row['secondary_target'], $rationale, $triggers);
        $this->setAuditValue($payload, 'grouping', 'top_min_score_q', (float) $row['top_min_score_q'], $rationale, $triggers);
        $this->setAuditValue($payload, 'grouping', 'secondary_min_score_q', (float) $row['secondary_min_score_q'], $rationale, $triggers);
        $this->setAuditValue($payload, 'grouping', 'top_max_score_total', (float) $row['top_max_score_total'], $rationale, $triggers);

        return $payload;
    }

    private function setAuditValue(array &$payload, string $section, string $key, $value, string $rationale, array $triggers): void
    {
        $existing = is_array($payload[$section][$key] ?? null) ? $payload[$section][$key] : [];
        $payload[$section][$key] = array_replace([
            'value' => $value,
            'origin' => 'BT',
            'status' => 'TEMP',
            'bt_target' => true,
            'rationale' => $rationale,
            'change_triggers' => $triggers,
        ], $existing, [
            'value' => $value,
            'origin' => 'BT',
            'status' => 'TEMP',
            'bt_target' => true,
            'rationale' => $rationale,
            'change_triggers' => $triggers,
        ]);
    }

    private function loadAndVerifyEvidenceBundle(string $directory): array
    {
        $manifest = [];
        $metrics = [];
        foreach (self::EVIDENCE as $paramSetId => $expected) {
            $path = rtrim($directory, '/\\').DIRECTORY_SEPARATOR.'c171-c01-v3-official-is-paramset-'.$paramSetId.'.json';
            if (! is_file($path)) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_EVIDENCE_FILE_MISSING', 'param_set_id' => $paramSetId];
            }
            $fileSha1 = strtolower((string) sha1_file($path));
            if ($fileSha1 !== $expected['file_sha1']) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_EVIDENCE_FILE_SHA1_MISMATCH', 'param_set_id' => $paramSetId];
            }
            $artifact = json_decode((string) file_get_contents($path), true);
            if (! is_array($artifact)) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_EVIDENCE_JSON_INVALID', 'param_set_id' => $paramSetId];
            }
            $evaluation = $artifact['is_calibration']['evaluations'][0] ?? null;
            if (! is_array($evaluation)
                || (int) ($artifact['param_set_id'] ?? 0) !== $paramSetId
                || (int) ($evaluation['eval_id'] ?? 0) !== $expected['eval_id']
                || (string) ($artifact['params_hash'] ?? '') !== $expected['params_hash']
                || (string) ($artifact['artifact_hash'] ?? '') !== $expected['artifact_hash']
                || (string) ($artifact['evidence_pipeline_version'] ?? '') !== self::SOURCE_PIPELINE_VERSION
                || (string) ($artifact['evidence_pipeline_hash'] ?? '') !== self::SOURCE_PIPELINE_HASH
                || ($artifact['status'] ?? '') !== 'C171_VERSIONED_OFFICIAL_IS_EVIDENCE_PERSISTED_CANONICAL_GATES_FAILED_OOS_NOT_RUN'
                || ($artifact['canonical_is_gates_pass'] ?? true) !== false
                || ($artifact['strict_is_boundary'] ?? false) !== true
                || ($artifact['oos_runtime_invoked'] ?? true) !== false
                || ($artifact['paramset_promoted'] ?? true) !== false
                || ($artifact['plan_run_created'] ?? true) !== false
                || ($artifact['production_ready'] ?? true) !== false) {
                return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_EVIDENCE_CONTRACT_MISMATCH', 'param_set_id' => $paramSetId];
            }
            if (in_array($paramSetId, [7, 8, 9], true)) {
                $audit = $artifact['tick_risk_guard_audit'] ?? [];
                if (($audit['status'] ?? '') !== 'PASS'
                    || ($audit['pass'] ?? false) !== true
                    || (int) ($audit['above_threshold_without_tick_reason_count'] ?? -1) !== 0
                    || (int) ($audit['eligible_above_threshold_after_guard_count'] ?? -1) !== 0) {
                    return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_TICK_RISK_AUDIT_INVALID', 'param_set_id' => $paramSetId];
                }
            }
            $metric = $evaluation['metrics'] ?? [];
            $metrics[$paramSetId] = [
                'avg_ret_net' => (float) ($metric['avg_ret_net_top'] ?? 0),
                'median_ret_net' => (float) ($metric['median_ret_net_top'] ?? 0),
                'p25_ret_net' => (float) ($metric['p25_ret_net_top'] ?? 0),
                'win_rate' => (float) ($metric['win_rate_top'] ?? 0),
                'month_win_rate_min' => (float) ($metric['month_win_rate_min'] ?? 0),
                'month_avg_ret_net_min' => (float) ($metric['month_avg_ret_net_min'] ?? 0),
                'period_fail_count' => (int) ($metric['period_fail_count'] ?? PHP_INT_MAX),
            ];
            $manifest[] = [
                'param_set_id' => $paramSetId,
                'eval_id' => $expected['eval_id'],
                'params_hash' => $expected['params_hash'],
                'artifact_hash' => $expected['artifact_hash'],
                'file_sha1' => $fileSha1,
            ];
        }

        if (! ($metrics[11]['avg_ret_net'] > $metrics[5]['avg_ret_net']
            && $metrics[11]['win_rate'] > $metrics[5]['win_rate']
            && $metrics[11]['period_fail_count'] < $metrics[5]['period_fail_count'])) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_ANCHOR_NOT_SUPPORTED'];
        }
        if (! ($metrics[7]['avg_ret_net'] > $metrics[8]['avg_ret_net']
            && $metrics[8]['avg_ret_net'] > $metrics[9]['avg_ret_net']
            && $metrics[7]['win_rate'] > $metrics[8]['win_rate']
            && $metrics[8]['win_rate'] > $metrics[9]['win_rate'])) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_TICK_RISK_DIRECTION_NOT_REJECTED'];
        }

        return [
            'valid' => true,
            'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_EVIDENCE_VALID',
            'manifest' => $manifest,
            'manifest_hash' => $this->identity->stableHash($manifest),
            'metrics' => $metrics,
        ];
    }

    private function loadAndVerifySummary(string $path): array
    {
        if (! is_file($path)) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_SUMMARY_FILE_MISSING'];
        }
        if (strtolower((string) sha1_file($path)) !== self::SUMMARY_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_SUMMARY_FILE_SHA1_MISMATCH'];
        }

        return ['valid' => true, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_SUMMARY_VALID'];
    }

    private function loadAndVerifyDesignArtifact(): array
    {
        $path = base_path('docs/watchlist/audit/_artifacts/c171-final-bounded-remediation-catalog-decision.json');
        if (! is_file($path) || strtolower((string) sha1_file($path)) !== self::DESIGN_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_DESIGN_FILE_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_DESIGN_JSON_INVALID'];
        }
        $expectedHash = (string) ($artifact['artifact_hash'] ?? '');
        $hashPayload = $artifact;
        unset($hashPayload['artifact_hash']);
        $codes = array_values(array_map(function (array $candidate): string {
            return (string) ($candidate['row_code'] ?? '');
        }, is_array($artifact['final_catalog']['candidates'] ?? null) ? $artifact['final_catalog']['candidates'] : []));
        $catalogCodes = array_values(array_map(function (array $row): string {
            return (string) $row['row_code'];
        }, WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::rows()));
        if ($expectedHash !== self::DESIGN_ARTIFACT_HASH
            || $this->identity->stableHash($hashPayload) !== $expectedHash
            || ($artifact['status'] ?? '') !== 'C171_ONE_FINAL_BOUNDED_REMEDIATION_ALLOWED_CLOSURE_RULE_LOCKED'
            || ($artifact['decision'] ?? '') !== 'ONE_FINAL_BOUNDED_REMEDIATION_ALLOWED'
            || (int) ($artifact['anchor_eval_id'] ?? 0) !== self::SOURCE_EVAL_ID
            || (int) ($artifact['anchor_param_set_id'] ?? 0) !== self::SOURCE_PARAM_SET_ID
            || ($artifact['anchor_params_hash'] ?? '') !== self::SOURCE_PARAMS_HASH
            || ($artifact['final_catalog']['catalog_code'] ?? '') !== WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_CODE
            || ($artifact['final_catalog']['catalog_hash'] ?? '') !== WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::hash()
            || (int) ($artifact['final_catalog']['candidate_count'] ?? 0) !== WatchlistBacktestC171FinalBoundedRemediationParamGridCatalog::CATALOG_COUNT
            || ($artifact['closure_rule']['if_no_candidate_passes_all_canonical_is_gates'] ?? '') !== 'C171_FAILED_NOT_READY_NO_FURTHER_REMEDIATION'
            || ($artifact['closure_rule']['additional_c171_candidate_catalog_allowed'] ?? true) !== false
            || $codes !== $catalogCodes) {
            return ['valid' => false, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_DESIGN_CONTRACT_MISMATCH'];
        }

        return ['valid' => true, 'reason_code' => 'C171_FINAL_BOUNDED_REMEDIATION_DESIGN_VALID'];
    }

    private function writeCanonicalJson(string $path, array $payload, bool $overwrite): array
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_CANONICAL_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $this->ensureDirectory(dirname($path));
        if (is_file($path)) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return ['status' => 'IDEMPOTENT', 'path' => $path, 'file_sha1' => sha1($existing)];
            }
            if (! $overwrite) {
                throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_CANONICAL_FILE_EXISTS_DIFFERENT: '.$path);
            }
        }
        if (file_put_contents($path, $json, LOCK_EX) === false) {
            throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_CANONICAL_FILE_WRITE_FAILED: '.$path);
        }

        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1($json)];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        $json = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_ARTIFACT_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $this->ensureDirectory(dirname($path));
        if (is_file($path) && ! $overwrite) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return ['status' => 'IDEMPOTENT', 'path' => $path, 'file_sha1' => sha1($existing)];
            }
            throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_ARTIFACT_EXISTS_DIFFERENT: '.$path);
        }
        if (file_put_contents($path, $json, LOCK_EX) === false) {
            throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_ARTIFACT_WRITE_FAILED: '.$path);
        }

        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1($json)];
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('C171_FINAL_BOUNDED_REMEDIATION_OUTPUT_DIRECTORY_CREATE_FAILED: '.$directory);
        }
    }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
