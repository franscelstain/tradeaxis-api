<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingNewStrategyR02DraftCatalogService
{
    public const RUN_CODE = 'WS_NEW_STRATEGY_R02_MINIMAL_ONE_IDEA_DRAFT_CATALOG';
    public const APPROVAL_REFERENCE = 'WS_NEW_STRATEGY_R02_OPERATOR_APPROVED_MINIMAL_DRAFT_CATALOG';
    public const SOURCE_EVAL_ID = 204;
    public const SOURCE_PARAM_SET_ID = 11;
    public const SOURCE_PARAMS_HASH = 'c93bae2b761028d6b236f368d5b19bb4f498715a';
    public const R01_ARTIFACT_HASH = 'a38e59f6d1422b7823a428ca4f6b724a3fa1a0e7';
    public const R01_FILE_SHA1 = 'bf76fb76388d6e0c81230b12b1dd4e934bbbe59a';
    public const R01_HYPOTHESIS_LOCK_FILE_SHA1 = '4560bf207af841641885f863fd2219d0c7c1f6d1';
    public const C171_CLOSURE_ARTIFACT_HASH = '71a5614ab5b97f407ec6bd01d7b5f9f13f7d68a2';
    public const C171_CLOSURE_FILE_SHA1 = '3c4537c9014d0f2ba1498ce8a79b5fcc3506fc2e';

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
        string $r01ArtifactPath,
        string $r01HypothesisLockPath,
        string $c171ClosurePath,
        string $approvalReference,
        bool $operatorApproved,
        string $outputDirectory,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('WS_NEW_STRATEGY_R02_OPERATOR_APPROVAL_MISSING');
        }
        foreach (['watchlist_param_sets', 'watchlist_bt_eval', 'watchlist_bt_param_grid'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('WS_NEW_STRATEGY_R02_SCHEMA_NOT_READY', ['missing_table' => $table]);
            }
        }

        $closure = $this->verifyJsonArtifact(
            $c171ClosurePath,
            self::C171_CLOSURE_FILE_SHA1,
            self::C171_CLOSURE_ARTIFACT_HASH
        );
        if (! ($closure['valid'] ?? false)
            || ($closure['artifact']['status'] ?? '') !== 'C171_FINAL_FAILED_NOT_READY_CLOSURE_SEALED'
            || ($closure['artifact']['additional_c171_candidate_catalog_allowed'] ?? true) !== false
            || ($closure['artifact']['oos_allowed'] ?? true) !== false) {
            return $this->blocked('WS_NEW_STRATEGY_R02_C171_CLOSURE_INVALID', $closure);
        }

        $r01 = $this->verifyJsonArtifact($r01ArtifactPath, self::R01_FILE_SHA1, self::R01_ARTIFACT_HASH);
        if (! ($r01['valid'] ?? false)
            || ($r01['artifact']['status'] ?? '') !== 'WS_NEW_STRATEGY_R01_DIAGNOSTIC_COMPLETED'
            || (int) ($r01['artifact']['candidate_design_allowed_count'] ?? 0) !== 3
            || ($r01['artifact']['oos_table_read'] ?? true) !== false
            || ($r01['artifact']['canonical_gate_snapshot']['pass'] ?? true) !== false) {
            return $this->blocked('WS_NEW_STRATEGY_R02_R01_ARTIFACT_INVALID', $r01);
        }
        if (! is_file($r01HypothesisLockPath)
            || strtolower((string) sha1_file($r01HypothesisLockPath)) !== self::R01_HYPOTHESIS_LOCK_FILE_SHA1) {
            return $this->blocked('WS_NEW_STRATEGY_R02_R01_HYPOTHESIS_LOCK_INVALID');
        }
        $hypothesisLock = json_decode((string) file_get_contents($r01HypothesisLockPath), true);
        if (! is_array($hypothesisLock)
            || (int) ($hypothesisLock['candidate_design_allowed_count'] ?? 0) !== 3
            || ($hypothesisLock['oos_table_read'] ?? true) !== false
            || ($hypothesisLock['anti_overfit_rules']['max_future_candidates'] ?? null) !== 3) {
            return $this->blocked('WS_NEW_STRATEGY_R02_R01_HYPOTHESIS_LOCK_INVALID');
        }

        $sourceDraft = DB::table('watchlist_param_sets')
            ->where('param_set_id', self::SOURCE_PARAM_SET_ID)
            ->first();
        $sourceEval = DB::table('watchlist_bt_eval')
            ->where('eval_id', self::SOURCE_EVAL_ID)
            ->first();
        if (! $sourceDraft || ! $sourceEval
            || (string) $sourceDraft->status !== 'DRAFT'
            || (string) $sourceDraft->policy_code !== 'WS'
            || (string) $sourceDraft->params_hash !== self::SOURCE_PARAMS_HASH
            || (int) $sourceEval->param_id !== 166
            || (string) $sourceEval->paramset_hash !== self::SOURCE_PARAMS_HASH) {
            return $this->blocked('WS_NEW_STRATEGY_R02_SOURCE_IDENTITY_INVALID');
        }
        $sourcePayload = json_decode((string) $sourceDraft->params_json, true);
        if (! is_array($sourcePayload)) {
            return $this->blocked('WS_NEW_STRATEGY_R02_SOURCE_PARAMSET_INVALID');
        }
        $sourceValidation = $this->validator->validate($sourcePayload);
        if (! ($sourceValidation['valid'] ?? false)
            || (string) ($sourceValidation['canonical_hash'] ?? '') !== self::SOURCE_PARAMS_HASH) {
            return $this->blocked('WS_NEW_STRATEGY_R02_SOURCE_PARAMSET_INVALID', [
                'validation' => $sourceValidation,
            ]);
        }

        $evalBefore = DB::table('watchlist_bt_eval')->count();
        $oosBefore = Schema::hasTable('watchlist_bt_oos_eval_ws')
            ? DB::table('watchlist_bt_oos_eval_ws')->count()
            : 0;
        $activeBefore = DB::table('watchlist_param_sets')
            ->where('policy_code', 'WS')
            ->where('status', 'ACTIVE')
            ->count();
        $planBefore = Schema::hasTable('watchlist_plan_runs')
            ? DB::table('watchlist_plan_runs')->count()
            : 0;

        try {
            $catalogSeed = $this->gridRepository->seedCatalog(
                WatchlistBacktestNewStrategyR02ParamGridCatalog::rows()
            );
            $catalogRows = $this->gridRepository->allForCatalog(
                WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_CODE
            );
            if (count($catalogRows) !== WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_COUNT) {
                throw new \RuntimeException('WS_NEW_STRATEGY_R02_CATALOG_ROW_COUNT_MISMATCH');
            }

            $drafts = [];
            foreach ($catalogRows as $row) {
                $payload = $this->buildCandidatePayload($sourceValidation['canonical_payload'], $row);
                $selection = $payload['research_selection'];
                $source = [
                    'stage' => self::RUN_CODE,
                    'approval_reference' => $approvalReference,
                    'separate_new_strategy_scope' => true,
                    'source_eval_id' => self::SOURCE_EVAL_ID,
                    'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
                    'source_params_hash' => self::SOURCE_PARAMS_HASH,
                    'r01_artifact_hash' => self::R01_ARTIFACT_HASH,
                    'r01_file_sha1' => self::R01_FILE_SHA1,
                    'r01_hypothesis_lock_file_sha1' => self::R01_HYPOTHESIS_LOCK_FILE_SHA1,
                    'catalog_code' => WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_CODE,
                    'catalog_version' => WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_VERSION,
                    'catalog_hash' => WatchlistBacktestNewStrategyR02ParamGridCatalog::hash(),
                    'catalog_row_code' => (string) $row['row_code'],
                    'catalog_row_hash' => (string) $row['row_hash'],
                    'hypothesis_code' => (string) $selection['hypothesis_code'],
                    'research_rule_code' => (string) $selection['rule_code'],
                    'one_primary_idea' => true,
                    'decision_time_fields_only' => true,
                    'oos_used' => false,
                    'canonical_gates_changed' => false,
                ];
                $import = $this->draftImport->execute(
                    $payload,
                    (int) $row['param_id'],
                    WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_CODE,
                    $source
                );
                if (($import['status'] ?? '') !== 'DRAFT_PERSISTED'
                    || ($import['paramset_status'] ?? '') !== 'DRAFT') {
                    throw new \RuntimeException(
                        'WS_NEW_STRATEGY_R02_DRAFT_PERSISTENCE_FAILED: '.(string) ($import['reason_code'] ?? 'UNKNOWN')
                    );
                }
                $canonicalPath = rtrim($outputDirectory, '/\\')
                    .DIRECTORY_SEPARATOR.strtolower((string) $row['row_code']).'.json';
                $write = $this->writeJson(
                    $canonicalPath,
                    $import['validation']['canonical_payload'],
                    (bool) ($options['overwrite'] ?? false)
                );
                $drafts[] = [
                    'row_code' => (string) $row['row_code'],
                    'row_hash' => (string) $row['row_hash'],
                    'hypothesis_code' => (string) $selection['hypothesis_code'],
                    'research_rule_code' => (string) $selection['rule_code'],
                    'bt_param_id' => (int) $row['param_id'],
                    'param_set_id' => (int) $import['param_set_id'],
                    'paramset_status' => (string) $import['paramset_status'],
                    'params_hash' => (string) $import['validation']['canonical_hash'],
                    'persistence_status' => (string) ($import['persistence']['status'] ?? ''),
                    'canonical_file' => $write,
                    'official_is_run' => false,
                    'oos_allowed' => false,
                ];
            }
        } catch (\Throwable $exception) {
            return $this->blocked('WS_NEW_STRATEGY_R02_CATALOG_PERSISTENCE_FAILED', [
                'error' => $exception->getMessage(),
            ]);
        }

        $evalAfter = DB::table('watchlist_bt_eval')->count();
        $oosAfter = Schema::hasTable('watchlist_bt_oos_eval_ws')
            ? DB::table('watchlist_bt_oos_eval_ws')->count()
            : 0;
        $activeAfter = DB::table('watchlist_param_sets')
            ->where('policy_code', 'WS')
            ->where('status', 'ACTIVE')
            ->count();
        $planAfter = Schema::hasTable('watchlist_plan_runs')
            ? DB::table('watchlist_plan_runs')->count()
            : 0;
        if ($evalAfter !== $evalBefore
            || $oosAfter !== $oosBefore
            || $activeAfter !== $activeBefore
            || $planAfter !== $planBefore) {
            return $this->blocked('WS_NEW_STRATEGY_R02_FORBIDDEN_MUTATION_DETECTED');
        }

        usort($drafts, function (array $left, array $right): int {
            return strcmp($left['row_code'], $right['row_code']);
        });
        $result = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'WS_NEW_STRATEGY_R02_THREE_MINIMAL_DRAFTS_PERSISTED',
            'reason_code' => 'WS_NEW_STRATEGY_R02_ONE_IDEA_CANDIDATES_LOCKED_OFFICIAL_IS_NOT_RUN',
            'approval_reference' => $approvalReference,
            'separate_new_strategy_scope' => true,
            'source_eval_id' => self::SOURCE_EVAL_ID,
            'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
            'source_params_hash' => self::SOURCE_PARAMS_HASH,
            'r01_artifact_hash' => self::R01_ARTIFACT_HASH,
            'catalog_code' => WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_CODE,
            'catalog_version' => WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_VERSION,
            'catalog_hash' => WatchlistBacktestNewStrategyR02ParamGridCatalog::hash(),
            'catalog_row_count' => count($drafts),
            'max_candidate_count' => 3,
            'one_primary_idea_per_candidate' => true,
            'canonical_gates_changed' => false,
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
            'production_ready' => false,
            'next_recommendation' => 'WS_NEW_STRATEGY_R02_RUN_OFFICIAL_IS_FOR_THREE_LOCKED_DRAFTS',
        ];
        $result['artifact_hash'] = $this->identity->stableHash($result);
        $result['write'] = $this->writeJson(
            $outputPath,
            $result,
            (bool) ($options['overwrite'] ?? false)
        );

        return $result;
    }

    public function buildCandidatePayload(array $sourcePayload, array $row): array
    {
        $payload = $sourcePayload;
        $rowCode = (string) $row['row_code'];
        $payload['paramset_code'] = $rowCode;
        $rationale = 'R02 one-idea candidate '.$rowCode.' locked from R01 decision-time evidence before official IS.';
        $triggers = [
            'WS_NEW_STRATEGY_R01_ARTIFACT_'.self::R01_ARTIFACT_HASH,
            'WS_NEW_STRATEGY_R02_CATALOG_ROW_'.(string) $row['row_hash'],
        ];
        if ($rowCode === 'R02_H1_BREAKOUT_QUALITY_0_TO_2') {
            $this->setAuditValue(
                $payload,
                'setup',
                'bo_max_ext_pct',
                (float) $row['bo_max_ext_pct'],
                $rationale,
                $triggers
            );
        } elseif ($rowCode === 'R02_H2_ROC20_PERSISTENCE_10_TO_15') {
            $this->setAuditValue(
                $payload,
                'setup',
                'roc_lo',
                (float) $row['roc_lo'],
                $rationale,
                $triggers
            );
        }
        $payload['research_selection'] =
            WatchlistBacktestNewStrategyR02ParamGridCatalog::researchSelectionForRow($rowCode);

        return $payload;
    }

    private function setAuditValue(
        array &$payload,
        string $section,
        string $key,
        $value,
        string $rationale,
        array $triggers
    ): void {
        $existing = is_array($payload[$section][$key] ?? null) ? $payload[$section][$key] : [];
        $payload[$section][$key] = array_replace($existing, [
            'value' => $value,
            'origin' => 'BT',
            'status' => 'TEMP',
            'bt_target' => true,
            'rationale' => $rationale,
            'change_triggers' => $triggers,
        ]);
    }

    private function verifyJsonArtifact(string $path, string $fileSha1, string $artifactHash): array
    {
        if (! is_file($path) || strtolower((string) sha1_file($path)) !== $fileSha1) {
            return ['valid' => false, 'reason_code' => 'FILE_SHA1_MISMATCH', 'path' => $path];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact) || (string) ($artifact['artifact_hash'] ?? '') !== $artifactHash) {
            return ['valid' => false, 'reason_code' => 'ARTIFACT_HASH_MISMATCH', 'path' => $path];
        }
        $payload = $artifact;
        unset($payload['artifact_hash']);
        if ($this->identity->stableHash($payload) !== $artifactHash) {
            return ['valid' => false, 'reason_code' => 'ARTIFACT_HASH_RECOMPUTE_MISMATCH', 'path' => $path];
        }

        return ['valid' => true, 'artifact' => $artifact];
    }

    private function writeJson(string $path, array $payload, bool $overwrite): array
    {
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json === false) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_OUTPUT_DIRECTORY_CREATE_FAILED');
        }
        if (is_file($path)) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return ['status' => 'IDEMPOTENT', 'path' => $path, 'file_sha1' => sha1($existing)];
            }
            if (! $overwrite) {
                throw new \RuntimeException('WS_NEW_STRATEGY_R02_OUTPUT_EXISTS_USE_OVERWRITE: '.$path);
            }
        }
        $temp = $path.'.tmp.'.getmypid();
        if (file_put_contents($temp, $json, LOCK_EX) === false || ! rename($temp, $path)) {
            @unlink($temp);
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_OUTPUT_WRITE_FAILED: '.$path);
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
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
