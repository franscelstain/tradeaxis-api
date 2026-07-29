<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingC171RemediationDraftCatalogService
{
    public const RUN_CODE = 'C171_IMPLEMENT_AND_PERSIST_IMMUTABLE_REAL_IS_REMEDIATION_DRAFT_CATALOG';
    public const APPROVAL_REFERENCE = 'C171_OPERATOR_APPROVED_IMMUTABLE_DRAFT_CATALOG_PERSISTENCE_ONLY';
    public const SOURCE_EVAL_ID = 188;
    public const SOURCE_PARAM_SET_ID = 1;
    public const SOURCE_PARAMS_HASH = 'b7f3c207b989c55c93f8f61b1fcceea2c343a151';
    public const DIAGNOSTIC_ARTIFACT_HASH = '768b4e47d4a9e497fda29ca6541be9a8f3a63c9d';
    public const DIAGNOSTIC_FILE_SHA1 = '1d2edc8ba5e1da342437e8c91465db03858b068d';
    public const DESIGN_ARTIFACT_HASH = '2a1345857e2ecf62b2d64fcaa46ed06f6015e9a6';
    public const DESIGN_FILE_SHA1 = 'ff838ac3c91f8681d4659094843b9ddd3c0973f3';

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
        string $diagnosticArtifactPath,
        string $approvalReference,
        bool $operatorApproved,
        string $outputDirectory,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_OPERATOR_APPROVAL_MISSING');
        }
        if ($sourceEvalId !== self::SOURCE_EVAL_ID || $sourceParamSetId !== self::SOURCE_PARAM_SET_ID) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_SOURCE_IDENTITY_INVALID');
        }
        foreach (['watchlist_param_sets', 'watchlist_bt_eval', 'watchlist_bt_param_grid', 'watchlist_bt_oos_eval_ws'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }
        foreach (['max_dv20_idr', 'max_vol_ratio', 'top_max_score_total'] as $column) {
            if (! Schema::hasColumn('watchlist_bt_param_grid', $column)) {
                return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_SCHEMA_NOT_READY', ['missing_column' => $column]);
            }
        }

        $sourceDraft = DB::table('watchlist_param_sets')->where('param_set_id', $sourceParamSetId)->first();
        $sourceEval = DB::table('watchlist_bt_eval')->where('eval_id', $sourceEvalId)->first();
        if (! $sourceDraft || ! $sourceEval) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_SOURCE_NOT_FOUND');
        }
        if ((string) $sourceDraft->status !== 'DRAFT'
            || (string) $sourceDraft->policy_code !== 'WS'
            || (string) $sourceDraft->params_hash !== self::SOURCE_PARAMS_HASH
            || (int) $sourceEval->param_id !== 1
            || (string) $sourceEval->paramset_hash !== self::SOURCE_PARAMS_HASH
            || (string) $sourceEval->from_date !== WeeklySwingC171TradeEvidenceDiagnosticService::CANONICAL_IS_FROM
            || (string) $sourceEval->to_date !== WeeklySwingC171TradeEvidenceDiagnosticService::CANONICAL_IS_TO) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_SOURCE_BASELINE_MISMATCH');
        }

        $sourcePayload = json_decode((string) $sourceDraft->params_json, true);
        if (! is_array($sourcePayload)) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_SOURCE_PARAMSET_INVALID');
        }
        $sourceValidation = $this->validator->validate($sourcePayload);
        if (! ($sourceValidation['valid'] ?? false)
            || (string) ($sourceValidation['canonical_hash'] ?? '') !== self::SOURCE_PARAMS_HASH) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_SOURCE_PARAMSET_INVALID', ['validation' => $sourceValidation]);
        }

        $diagnostic = $this->loadAndVerifyDiagnostic($diagnosticArtifactPath);
        if (! ($diagnostic['valid'] ?? false)) {
            return $this->blocked((string) ($diagnostic['reason_code'] ?? 'C171_REMEDIATION_DIAGNOSTIC_INVALID'), $diagnostic);
        }
        $design = $this->loadAndVerifyDesignArtifact();
        if (! ($design['valid'] ?? false)) {
            return $this->blocked((string) ($design['reason_code'] ?? 'C171_REMEDIATION_DESIGN_INVALID'), $design);
        }

        try {
            $expectedParamsetHashes = $this->deriveExpectedCandidateHashes(
                $sourceValidation['canonical_payload'],
                WatchlistBacktestC171RemediationParamGridCatalog::rows()
            );
        } catch (\Throwable $exception) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CANDIDATE_HASH_PREFLIGHT_FAILED', [
                'error' => $exception->getMessage(),
            ]);
        }
        $candidateHashManifest = [];
        foreach ($expectedParamsetHashes as $rowCode => $paramsHash) {
            $candidateHashManifest[] = [
                'row_code' => $rowCode,
                'params_hash' => $paramsHash,
            ];
        }
        $candidateHashManifestHash = $this->identity->stableHash($candidateHashManifest);

        $activeBefore = DB::table('watchlist_param_sets')->where('policy_code', 'WS')->where('status', 'ACTIVE')->count();
        $oosBefore = DB::table('watchlist_bt_oos_eval_ws')->count();
        $planBefore = Schema::hasTable('watchlist_plan_runs') ? DB::table('watchlist_plan_runs')->count() : 0;

        try {
            $catalogSeed = $this->gridRepository->seedCatalog(WatchlistBacktestC171RemediationParamGridCatalog::rows());
            $catalogRows = $this->gridRepository->allForCatalog(
                WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE,
                'WS'
            );
            if (count($catalogRows) !== WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_COUNT) {
                throw new \RuntimeException('C171_REMEDIATION_DRAFT_CATALOG_PERSISTED_ROW_COUNT_MISMATCH');
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
                    'diagnostic_artifact_hash' => self::DIAGNOSTIC_ARTIFACT_HASH,
                    'diagnostic_file_sha1' => self::DIAGNOSTIC_FILE_SHA1,
                    'candidate_design_artifact_hash' => self::DESIGN_ARTIFACT_HASH,
                    'candidate_design_file_sha1' => self::DESIGN_FILE_SHA1,
                    'catalog_code' => WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE,
                    'catalog_version' => WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_VERSION,
                    'catalog_hash' => WatchlistBacktestC171RemediationParamGridCatalog::hash(),
                    'catalog_row_code' => (string) $row['row_code'],
                    'catalog_row_hash' => (string) $row['row_hash'],
                    'decision_time_fields_only' => true,
                    'oos_used' => false,
                    'canonical_gates_changed' => false,
                ];
                $import = $this->draftImport->execute(
                    $payload,
                    (int) $row['param_id'],
                    WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE,
                    $source
                );
                if (($import['status'] ?? '') !== 'DRAFT_PERSISTED'
                    || ($import['paramset_status'] ?? '') !== 'DRAFT') {
                    throw new \RuntimeException(
                        'C171_REMEDIATION_DRAFT_PERSISTENCE_FAILED: '.(string) ($import['reason_code'] ?? 'UNKNOWN')
                    );
                }

                $rowCode = (string) $row['row_code'];
                $expectedParamsetHash = $expectedParamsetHashes[$rowCode] ?? null;
                if ($expectedParamsetHash === null
                    || (string) ($import['validation']['canonical_hash'] ?? '') !== $expectedParamsetHash
                    || (string) ($import['persistence']['params_hash'] ?? '') !== $expectedParamsetHash) {
                    throw new \RuntimeException('C171_REMEDIATION_DRAFT_PARAMSET_HASH_MISMATCH: '.$rowCode);
                }

                $canonicalPath = rtrim($outputDirectory, '/\\').DIRECTORY_SEPARATOR.strtolower((string) $row['row_code']).'.json';
                $write = $this->writeCanonicalJson(
                    $canonicalPath,
                    $import['validation']['canonical_payload'],
                    (bool) ($options['overwrite'] ?? false)
                );
                $drafts[] = [
                    'row_code' => (string) $row['row_code'],
                    'row_hash' => (string) $row['row_hash'],
                    'bt_param_id' => (int) $row['param_id'],
                    'param_set_id' => (int) $import['param_set_id'],
                    'paramset_status' => (string) $import['paramset_status'],
                    'params_hash' => (string) $import['validation']['canonical_hash'],
                    'expected_params_hash' => $expectedParamsetHash,
                    'eval_model_hash' => (string) ($import['persistence']['eval_model_hash'] ?? ''),
                    'implementation_hash' => (string) ($import['persistence']['implementation_hash'] ?? ''),
                    'persistence_status' => (string) ($import['persistence']['status'] ?? ''),
                    'canonical_file' => $write,
                    'official_is_run' => false,
                    'canonical_is_gates_pass' => null,
                    'oos_allowed' => false,
                ];
            }
        } catch (\Throwable $exception) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_PERSISTENCE_FAILED', [
                'error' => $exception->getMessage(),
            ]);
        }

        $activeAfter = DB::table('watchlist_param_sets')->where('policy_code', 'WS')->where('status', 'ACTIVE')->count();
        $oosAfter = DB::table('watchlist_bt_oos_eval_ws')->count();
        $planAfter = Schema::hasTable('watchlist_plan_runs') ? DB::table('watchlist_plan_runs')->count() : 0;
        if ($activeAfter !== $activeBefore || $oosAfter !== $oosBefore || $planAfter !== $planBefore) {
            return $this->blocked('C171_REMEDIATION_DRAFT_CATALOG_FORBIDDEN_MUTATION_DETECTED', [
                'active_before' => $activeBefore,
                'active_after' => $activeAfter,
                'oos_before' => $oosBefore,
                'oos_after' => $oosAfter,
                'plan_before' => $planBefore,
                'plan_after' => $planAfter,
            ]);
        }

        usort($drafts, function (array $a, array $b): int {
            return strcmp($a['row_code'], $b['row_code']);
        });
        $result = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'C171_IMMUTABLE_REMEDIATION_DRAFT_CATALOG_PERSISTED',
            'reason_code' => 'C171_FIVE_NEW_IMMUTABLE_DRAFT_PARAMSETS_PERSISTED_OFFICIAL_IS_NOT_RUN',
            'approval_reference' => $approvalReference,
            'source_eval_id' => $sourceEvalId,
            'source_param_set_id' => $sourceParamSetId,
            'source_params_hash' => self::SOURCE_PARAMS_HASH,
            'diagnostic_artifact_hash' => self::DIAGNOSTIC_ARTIFACT_HASH,
            'candidate_design_artifact_hash' => self::DESIGN_ARTIFACT_HASH,
            'catalog_seed' => $catalogSeed,
            'catalog_code' => WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE,
            'catalog_version' => WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_VERSION,
            'catalog_hash' => WatchlistBacktestC171RemediationParamGridCatalog::hash(),
            'catalog_row_count' => count($drafts),
            'candidate_hash_contract' => 'DERIVED_FROM_IMMUTABLE_SOURCE_CANONICAL_PAYLOAD_AND_CATALOG_ROW',
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
            'oos_repository_invoked' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'recommendation_persisted' => false,
            'confirm_mutated' => false,
            'production_activation_executed' => false,
            'controlled_rollout_executed' => false,
            'production_ready' => false,
            'next_recommendation' => 'C171_RUN_VERSIONED_OFFICIAL_IS_FOR_EACH_IMMUTABLE_REMEDIATION_DRAFT',
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
                throw new \RuntimeException('C171_REMEDIATION_DRAFT_CANDIDATE_ROW_IDENTITY_INVALID');
            }
            $payload = $this->buildCandidatePayload($sourceCanonicalPayload, $row);
            $validation = $this->validator->validate($payload);
            if (! ($validation['valid'] ?? false)) {
                throw new \RuntimeException(
                    'C171_REMEDIATION_DRAFT_CANDIDATE_VALIDATION_FAILED: '.$rowCode.'|'.json_encode($validation['errors'] ?? [])
                );
            }
            $paramsHash = (string) ($validation['canonical_hash'] ?? '');
            if ($paramsHash === '' || $paramsHash === self::SOURCE_PARAMS_HASH || isset($seenHashes[$paramsHash])) {
                throw new \RuntimeException('C171_REMEDIATION_DRAFT_CANDIDATE_HASH_IDENTITY_INVALID: '.$rowCode);
            }
            $hashes[$rowCode] = $paramsHash;
            $seenHashes[$paramsHash] = true;
        }
        if (count($hashes) !== WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_COUNT) {
            throw new \RuntimeException('C171_REMEDIATION_DRAFT_CANDIDATE_HASH_COUNT_MISMATCH');
        }

        return $hashes;
    }

    public function buildCandidatePayload(array $sourcePayload, array $row): array
    {
        $payload = $sourcePayload;
        $payload['paramset_code'] = (string) $row['row_code'];
        $rationale = 'C171 real-IS remediation candidate '.(string) $row['row_code'].' derived from immutable eval_id 188 diagnostic evidence.';
        $triggers = [
            'C171_STRATEGY_QUALITY_FAILURE_CONFIRMED',
            'C171_DIAGNOSTIC_ARTIFACT_'.self::DIAGNOSTIC_ARTIFACT_HASH,
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

    private function loadAndVerifyDiagnostic(string $path): array
    {
        if (! is_file($path)) {
            return ['valid' => false, 'reason_code' => 'C171_REMEDIATION_DIAGNOSTIC_FILE_MISSING'];
        }
        if (strtolower((string) sha1_file($path)) !== self::DIAGNOSTIC_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'C171_REMEDIATION_DIAGNOSTIC_FILE_SHA1_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)) {
            return ['valid' => false, 'reason_code' => 'C171_REMEDIATION_DIAGNOSTIC_JSON_INVALID'];
        }
        $expectedHash = (string) ($artifact['artifact_hash'] ?? '');
        $hashPayload = $artifact;
        unset($hashPayload['artifact_hash'], $hashPayload['write']);
        if ($expectedHash !== self::DIAGNOSTIC_ARTIFACT_HASH
            || $this->identity->stableHash($hashPayload) !== $expectedHash
            || ($artifact['status'] ?? '') !== 'C171_TRADE_EVIDENCE_DIAGNOSTIC_COMPLETED'
            || ($artifact['remediation_classification'] ?? '') !== 'STRATEGY_QUALITY_FAILURE_CONFIRMED'
            || ($artifact['official_pick_parity']['pass'] ?? false) !== true
            || ($artifact['new_draft_design_allowed'] ?? false) !== true
            || (int) ($artifact['eval_id'] ?? 0) !== self::SOURCE_EVAL_ID
            || (int) ($artifact['param_set_id'] ?? 0) !== self::SOURCE_PARAM_SET_ID
            || (string) ($artifact['params_hash'] ?? '') !== self::SOURCE_PARAMS_HASH) {
            return ['valid' => false, 'reason_code' => 'C171_REMEDIATION_DIAGNOSTIC_CONTRACT_MISMATCH'];
        }

        return ['valid' => true, 'reason_code' => 'C171_REMEDIATION_DIAGNOSTIC_VALID'];
    }

    private function loadAndVerifyDesignArtifact(): array
    {
        $path = base_path('docs/watchlist/audit/_artifacts/c171-immutable-draft-paramset-candidate-design.json');
        if (! is_file($path) || strtolower((string) sha1_file($path)) !== self::DESIGN_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'C171_REMEDIATION_DESIGN_FILE_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)) {
            return ['valid' => false, 'reason_code' => 'C171_REMEDIATION_DESIGN_JSON_INVALID'];
        }
        $expectedHash = (string) ($artifact['artifact_hash'] ?? '');
        $hashPayload = $artifact;
        unset($hashPayload['artifact_hash']);
        $codes = array_values(array_map(function (array $candidate): string {
            return (string) ($candidate['candidate_code'] ?? '');
        }, is_array($artifact['candidates'] ?? null) ? $artifact['candidates'] : []));
        $catalogCodes = array_values(array_map(function (array $row): string {
            return (string) $row['row_code'];
        }, WatchlistBacktestC171RemediationParamGridCatalog::rows()));
        if ($expectedHash !== self::DESIGN_ARTIFACT_HASH
            || $this->designArtifactHash($hashPayload) !== $expectedHash
            || ($artifact['status'] ?? '') !== 'C171_IMMUTABLE_DRAFT_CANDIDATE_DESIGN_COMPLETED'
            || ($artifact['source_evidence']['diagnostic_artifact_hash'] ?? '') !== self::DIAGNOSTIC_ARTIFACT_HASH
            || ($artifact['required_next_implementation_contract']['new_catalog_code'] ?? '') !== WatchlistBacktestC171RemediationParamGridCatalog::CATALOG_CODE
            || $codes !== $catalogCodes) {
            return ['valid' => false, 'reason_code' => 'C171_REMEDIATION_DESIGN_CONTRACT_MISMATCH'];
        }

        return ['valid' => true, 'reason_code' => 'C171_REMEDIATION_DESIGN_VALID'];
    }

    private function designArtifactHash(array $payload): string
    {
        $json = json_encode(
            $this->normalizeDesignArtifact($payload),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
        );
        if ($json === false) {
            throw new \RuntimeException('C171_REMEDIATION_DESIGN_HASH_ENCODING_FAILED: '.json_last_error_msg());
        }

        return sha1($json);
    }

    private function normalizeDesignArtifact($value)
    {
        if (! is_array($value)) {
            return $value;
        }
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return array_map(function ($item) {
                return $this->normalizeDesignArtifact($item);
            }, $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->normalizeDesignArtifact($item);
        }

        return $value;
    }

    private function writeCanonicalJson(string $path, array $payload, bool $overwrite): array
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('C171_REMEDIATION_CANONICAL_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $this->ensureDirectory(dirname($path));
        if (is_file($path)) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return ['status' => 'IDEMPOTENT', 'path' => $path, 'file_sha1' => sha1($existing)];
            }
            if (! $overwrite) {
                throw new \RuntimeException('C171_REMEDIATION_CANONICAL_FILE_EXISTS_DIFFERENT: '.$path);
            }
        }
        if (file_put_contents($path, $json, LOCK_EX) === false) {
            throw new \RuntimeException('C171_REMEDIATION_CANONICAL_FILE_WRITE_FAILED: '.$path);
        }

        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1($json)];
    }

    private function writeArtifact(string $path, array $artifact, bool $overwrite): array
    {
        $json = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \RuntimeException('C171_REMEDIATION_ARTIFACT_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $this->ensureDirectory(dirname($path));
        if (is_file($path) && ! $overwrite) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return ['status' => 'IDEMPOTENT', 'path' => $path, 'file_sha1' => sha1($existing)];
            }
            throw new \RuntimeException('C171_REMEDIATION_ARTIFACT_EXISTS_DIFFERENT: '.$path);
        }
        if (file_put_contents($path, $json, LOCK_EX) === false) {
            throw new \RuntimeException('C171_REMEDIATION_ARTIFACT_WRITE_FAILED: '.$path);
        }

        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1($json)];
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('C171_REMEDIATION_OUTPUT_DIRECTORY_CREATE_FAILED: '.$directory);
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
