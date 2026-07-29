<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingNewStrategyR02RemediationDraftService
{
    public const RUN_CODE = 'WS_NEW_STRATEGY_R02_SINGLE_ALLOWED_REMEDIATION_DRAFT';
    public const APPROVAL_REFERENCE = 'WS_NEW_STRATEGY_R02_OPERATOR_APPROVED_SINGLE_REMEDIATION_DRAFT';
    public const SOURCE_PARAM_SET_ID = 16;
    public const SOURCE_EVAL_ID = 209;
    public const SOURCE_BT_PARAM_ID = 171;
    public const SOURCE_PARAMS_HASH = 'd50497b951107ae8de9f559d3fccf13e7b2182c6';
    public const SOURCE_OFFICIAL_IS_ARTIFACT_HASH = 'd4992cb12859fe74ab287139e1023173ad6a2566';
    public const SOURCE_OFFICIAL_IS_FILE_SHA1 = 'ce58974787485d3bc78a02b1b6dabb08b9ce24fa';

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
        string $sourceOfficialIsPath,
        string $approvalReference,
        bool $operatorApproved,
        string $canonicalOutputPath,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_OPERATOR_APPROVAL_MISSING');
        }
        foreach (['watchlist_param_sets', 'watchlist_bt_eval', 'watchlist_bt_param_grid'] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_SCHEMA_NOT_READY', [
                    'missing_table' => $table,
                ]);
            }
        }

        $sourceArtifact = $this->verifySourceArtifact($sourceOfficialIsPath);
        if (! ($sourceArtifact['valid'] ?? false)) {
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_SOURCE_IS_INVALID', $sourceArtifact);
        }
        $artifact = $sourceArtifact['artifact'];
        $evaluation = $artifact['is_calibration']['evaluations'][0] ?? [];
        if (($artifact['status'] ?? '') !== 'WS_NEW_STRATEGY_R02_OFFICIAL_IS_FAILED_OOS_NOT_RUN'
            || ($artifact['canonical_is_gates_pass'] ?? true) !== false
            || (int) ($artifact['param_set_id'] ?? 0) !== self::SOURCE_PARAM_SET_ID
            || (string) ($artifact['params_hash'] ?? '') !== self::SOURCE_PARAMS_HASH
            || (string) ($artifact['hypothesis_code'] ?? '') !== 'H2_MOMENTUM_PERSISTENCE'
            || (string) ($artifact['research_rule_code'] ?? '') !== 'SIGNAL_ROC20_10_TO_15_PCT'
            || (int) ($evaluation['eval_id'] ?? 0) !== self::SOURCE_EVAL_ID
            || (int) ($evaluation['param_id'] ?? 0) !== self::SOURCE_BT_PARAM_ID
            || ($artifact['oos_runtime_invoked'] ?? true) !== false
            || ($artifact['future_derived_route_used'] ?? true) !== false) {
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_SOURCE_IS_IDENTITY_MISMATCH');
        }

        $sourceDraft = DB::table('watchlist_param_sets')
            ->where('param_set_id', self::SOURCE_PARAM_SET_ID)
            ->first();
        $sourceEval = DB::table('watchlist_bt_eval')
            ->where('eval_id', self::SOURCE_EVAL_ID)
            ->first();
        if (! $sourceDraft || ! $sourceEval
            || (string) $sourceDraft->status !== 'DRAFT'
            || (string) $sourceDraft->params_hash !== self::SOURCE_PARAMS_HASH
            || (string) $sourceEval->paramset_hash !== self::SOURCE_PARAMS_HASH
            || (int) $sourceEval->param_id !== self::SOURCE_BT_PARAM_ID) {
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_SOURCE_DB_IDENTITY_MISMATCH');
        }
        $sourcePayload = json_decode((string) $sourceDraft->params_json, true);
        if (! is_array($sourcePayload)) {
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_SOURCE_PARAMSET_INVALID');
        }
        $sourceValidation = $this->validator->validate($sourcePayload);
        if (! ($sourceValidation['valid'] ?? false)
            || (string) ($sourceValidation['canonical_hash'] ?? '') !== self::SOURCE_PARAMS_HASH
            || $this->identity->stableHash(
                $sourceValidation['canonical_payload']['research_selection'] ?? null
            ) !== $this->identity->stableHash(
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchSelection()
            )
            || array_key_exists('research_execution', $sourceValidation['canonical_payload'])) {
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_SOURCE_PARAMSET_INVALID', [
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
            $seed = $this->gridRepository->seedCatalog(
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::rows()
            );
            $rows = $this->gridRepository->allForCatalog(
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::CATALOG_CODE
            );
            if (count($rows) !== 1) {
                throw new \RuntimeException('WS_NEW_STRATEGY_R02_REMEDIATION_CATALOG_COUNT_MISMATCH');
            }
            $row = $rows[0];
            $payload = $sourceValidation['canonical_payload'];
            $payload['paramset_code'] =
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::ROW_CODE;
            $payload['research_execution'] =
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution();
            $source = [
                'stage' => self::RUN_CODE,
                'approval_reference' => $approvalReference,
                'separate_new_strategy_scope' => true,
                'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
                'source_eval_id' => self::SOURCE_EVAL_ID,
                'source_bt_param_id' => self::SOURCE_BT_PARAM_ID,
                'source_params_hash' => self::SOURCE_PARAMS_HASH,
                'source_official_is_artifact_hash' => self::SOURCE_OFFICIAL_IS_ARTIFACT_HASH,
                'source_official_is_file_sha1' => self::SOURCE_OFFICIAL_IS_FILE_SHA1,
                'r01_artifact_hash' => WeeklySwingNewStrategyR02DraftCatalogService::R01_ARTIFACT_HASH,
                'catalog_code' =>
                    WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::CATALOG_CODE,
                'catalog_version' =>
                    WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::CATALOG_VERSION,
                'catalog_hash' =>
                    WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::hash(),
                'catalog_row_code' => (string) $row['row_code'],
                'catalog_row_hash' => (string) $row['row_hash'],
                'hypothesis_code' => 'H2_MOMENTUM_PERSISTENCE',
                'research_rule_code' => 'SIGNAL_ROC20_10_TO_15_PCT',
                'research_execution_rule_code' =>
                    $payload['research_execution']['rule_code'],
                'single_allowed_remediation' => true,
                'one_primary_idea' => true,
                'decision_time_selection_unchanged' => true,
                'fixed_execution_before_entry' => true,
                'future_derived_route_used' => false,
                'oos_used' => false,
                'canonical_gates_changed' => false,
            ];
            $import = $this->draftImport->execute(
                $payload,
                (int) $row['param_id'],
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::CATALOG_CODE,
                $source
            );
            if (($import['status'] ?? '') !== 'DRAFT_PERSISTED'
                || ($import['paramset_status'] ?? '') !== 'DRAFT') {
                throw new \RuntimeException(
                    'WS_NEW_STRATEGY_R02_REMEDIATION_DRAFT_PERSISTENCE_FAILED: '
                    .(string) ($import['reason_code'] ?? 'UNKNOWN')
                );
            }
            $canonicalWrite = $this->writeJson(
                $canonicalOutputPath,
                $import['validation']['canonical_payload'],
                (bool) ($options['overwrite'] ?? false)
            );
        } catch (\Throwable $exception) {
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_PERSISTENCE_FAILED', [
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
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_FORBIDDEN_MUTATION_DETECTED');
        }

        $result = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'WS_NEW_STRATEGY_R02_SINGLE_REMEDIATION_DRAFT_PERSISTED',
            'reason_code' => 'WS_NEW_STRATEGY_R02_M1_LOCKED_OFFICIAL_IS_NOT_RUN',
            'approval_reference' => $approvalReference,
            'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
            'source_eval_id' => self::SOURCE_EVAL_ID,
            'source_params_hash' => self::SOURCE_PARAMS_HASH,
            'source_official_is_artifact_hash' => self::SOURCE_OFFICIAL_IS_ARTIFACT_HASH,
            'catalog_code' =>
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::CATALOG_CODE,
            'catalog_version' =>
                WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::CATALOG_VERSION,
            'catalog_hash' => WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::hash(),
            'catalog_seed' => $seed,
            'catalog_row_count' => 1,
            'remediation_count' => 1,
            'max_remediation_count' => 1,
            'selection_unchanged_from_h2' => true,
            'one_primary_exit_idea' => true,
            'fixed_execution_before_entry' => true,
            'future_derived_route_used' => false,
            'canonical_gates_changed' => false,
            'param_set_id' => (int) $import['param_set_id'],
            'bt_param_id' => (int) $row['param_id'],
            'params_hash' => (string) $import['validation']['canonical_hash'],
            'paramset_status' => (string) $import['paramset_status'],
            'persistence_status' => (string) ($import['persistence']['status'] ?? ''),
            'canonical_file' => $canonicalWrite,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' => 'WS_NEW_STRATEGY_R02_RUN_OFFICIAL_IS_FOR_SINGLE_REMEDIATION',
        ];
        $result['artifact_hash'] = $this->identity->stableHash($result);
        $result['write'] = $this->writeJson(
            $outputPath,
            $result,
            (bool) ($options['overwrite'] ?? false)
        );

        return $result;
    }

    private function verifySourceArtifact(string $path): array
    {
        if (! is_file($path)
            || strtolower((string) sha1_file($path)) !== self::SOURCE_OFFICIAL_IS_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'SOURCE_FILE_SHA1_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)
            || (string) ($artifact['artifact_hash'] ?? '') !== self::SOURCE_OFFICIAL_IS_ARTIFACT_HASH) {
            return ['valid' => false, 'reason_code' => 'SOURCE_ARTIFACT_HASH_MISMATCH'];
        }
        $payload = $artifact;
        unset($payload['artifact_hash']);
        if ($this->identity->stableHash($payload) !== self::SOURCE_OFFICIAL_IS_ARTIFACT_HASH) {
            return ['valid' => false, 'reason_code' => 'SOURCE_ARTIFACT_HASH_RECOMPUTE_MISMATCH'];
        }

        return ['valid' => true, 'artifact' => $artifact];
    }

    private function writeJson(string $path, array $payload, bool $overwrite): array
    {
        if ($path === '') {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_REMEDIATION_OUTPUT_PATH_REQUIRED');
        }
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        if ($json === false) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_REMEDIATION_JSON_ENCODING_FAILED');
        }
        $json .= PHP_EOL;
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_REMEDIATION_OUTPUT_DIRECTORY_FAILED');
        }
        if (is_file($path)) {
            $existing = (string) file_get_contents($path);
            if ($existing === $json) {
                return ['status' => 'IDEMPOTENT', 'path' => $path, 'file_sha1' => sha1($existing)];
            }
            if (! $overwrite) {
                throw new \RuntimeException('WS_NEW_STRATEGY_R02_REMEDIATION_OUTPUT_EXISTS_USE_OVERWRITE');
            }
        }
        $temp = $path.'.tmp.'.getmypid();
        if (file_put_contents($temp, $json, LOCK_EX) === false || ! rename($temp, $path)) {
            @unlink($temp);
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_REMEDIATION_OUTPUT_WRITE_FAILED');
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
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
