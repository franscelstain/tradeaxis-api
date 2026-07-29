<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingPriceQualityP01IdentityRepairDraftService
{
    public const RUN_CODE =
        'WS_PRICE_QUALITY_P01_REMEDIATION_EXECUTION_IDENTITY_REPAIR_DRAFT';
    public const APPROVAL_REFERENCE =
        'WS_PRICE_QUALITY_P01_OPERATOR_APPROVED_IDENTITY_REPAIR_DRAFT';
    public const PARAMSET_CODE =
        'P01_M1_C1_MIN_PRICE_50_LOSS_CLOSE_NEG1_NEXT_OPEN_IDENTITY_REPAIR_V1';
    public const SOURCE_PARAM_SET_ID = 27;
    public const SOURCE_BT_PARAM_ID = 180;
    public const SOURCE_EVAL_ID = 218;
    public const SOURCE_PARAMS_HASH =
        'b9ebd3a64c92aa7e09c786f1fce6c1a13ada469a';
    public const SOURCE_ARTIFACT_HASH =
        'd4a834938202c0c39b53fd094088273a561854a2';
    public const SOURCE_ARTIFACT_FILE_SHA1 =
        'a7084384ba41767e594d739a5d464f85f21c7f4d';
    public const SOURCE_EVIDENCE_MANIFEST_HASH =
        '2110f4fec4984446b599f9e3b1fd6c7b5fb40ac1';
    public const EXPECTED_EVAL_MODEL =
        'ENTRY=NEXT_OPEN;EXIT=SEQ_TP05_PCL1NO_TIME;HOLD=5;FEE=IDR_FIXED;SLIP=0;GAP=OPEN;PX=IDX_BANDS';

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
        $this->draftImport = $draftImport
            ?: new WeeklySwingParamsetDraftImportService();
        $this->identity = $identity
            ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->gridRepository = $gridRepository
            ?: new WatchlistBacktestParamGridRepository();
    }

    public function execute(
        string $sourceArtifactPath,
        string $approvalReference,
        bool $operatorApproved,
        string $canonicalOutputPath,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_OPERATOR_APPROVAL_MISSING'
            );
        }
        foreach ([
            'watchlist_param_sets', 'watchlist_bt_eval',
            'watchlist_bt_param_grid',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked(
                    'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_SCHEMA_NOT_READY',
                    ['missing_table' => $table]
                );
            }
        }
        $artifact = $this->verifySourceArtifact($sourceArtifactPath);
        if (! ($artifact['valid'] ?? false)) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_SOURCE_ARTIFACT_INVALID',
                $artifact
            );
        }

        $sourceDraft = DB::table('watchlist_param_sets')
            ->where('param_set_id', self::SOURCE_PARAM_SET_ID)
            ->first();
        $sourceEval = DB::table('watchlist_bt_eval')
            ->where('eval_id', self::SOURCE_EVAL_ID)
            ->first();
        if (! $sourceDraft
            || ! $sourceEval
            || (string) $sourceDraft->status !== 'DRAFT'
            || (string) $sourceDraft->params_hash !== self::SOURCE_PARAMS_HASH
            || (int) $sourceEval->param_id !== self::SOURCE_BT_PARAM_ID
            || (string) $sourceEval->paramset_hash !== self::SOURCE_PARAMS_HASH
            || (string) $sourceEval->evidence_manifest_hash
                !== self::SOURCE_EVIDENCE_MANIFEST_HASH) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_SOURCE_IDENTITY_INVALID'
            );
        }
        $payload = json_decode((string) $sourceDraft->params_json, true);
        $sourceValidation = is_array($payload)
            ? $this->validator->validate($payload)
            : ['valid' => false];
        if (! ($sourceValidation['valid'] ?? false)
            || ($sourceValidation['canonical_hash'] ?? '')
                !== self::SOURCE_PARAMS_HASH) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_SOURCE_PARAMSET_INVALID'
            );
        }

        $before = $this->forbiddenBoundaryCounts();
        try {
            $seed = $this->gridRepository->seedCatalog(
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::rows()
            );
            $rows = $this->gridRepository->allForCatalog(
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::CATALOG_CODE
            );
            if (count($rows) !== 1
                || (int) $rows[0]['param_id'] !== self::SOURCE_BT_PARAM_ID) {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_BINDING_MISMATCH'
                );
            }
            $row = $rows[0];
            $repairedPayload = $sourceValidation['canonical_payload'];
            $repairedPayload['paramset_code'] = self::PARAMSET_CODE;
            $repairedPayload['research_selection'] =
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::researchSelection();
            $repairedPayload['research_execution'] =
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::researchExecution();

            $runtime = (new WeeklySwingParamsetRuntimeAdapter())
                ->adapt($repairedPayload);
            $evalModel =
                WatchlistBacktestStrategyService::canonicalEvalModel($runtime);
            if ($evalModel !== self::EXPECTED_EVAL_MODEL) {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_EVAL_MODEL_NOT_FIXED'
                );
            }
            $provenance = [
                'stage' => self::RUN_CODE,
                'approval_reference' => $approvalReference,
                'separate_new_strategy_scope' => true,
                'c171_reopened' => false,
                'r02_reopened' => false,
                's01_reopened' => false,
                'source_anchor_is_best_of_failed_binding' => false,
                'single_allowed_remediation' => true,
                'remediation_round' => 1,
                'max_remediation_rounds' => 1,
                'identity_repair_only' => true,
                'strategy_semantics_changed' => false,
                'remediation_round_incremented' => false,
                'second_remediation_created' => false,
                'invalidated_eval_id' => self::SOURCE_EVAL_ID,
                'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
                'source_bt_param_id' => self::SOURCE_BT_PARAM_ID,
                'source_eval_id' => self::SOURCE_EVAL_ID,
                'source_params_hash' => self::SOURCE_PARAMS_HASH,
                'source_official_is_artifact_hash' =>
                    self::SOURCE_ARTIFACT_HASH,
                'source_official_is_file_sha1' =>
                    self::SOURCE_ARTIFACT_FILE_SHA1,
                'source_evidence_manifest_hash' =>
                    self::SOURCE_EVIDENCE_MANIFEST_HASH,
                'primary_hypothesis_code' =>
                    WatchlistBacktestPriceQualityP01ParamGridCatalog
                        ::PRIMARY_HYPOTHESIS_CODE,
                'catalog_code' =>
                    WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                        ::CATALOG_CODE,
                'catalog_version' =>
                    WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                        ::CATALOG_VERSION,
                'catalog_hash' =>
                    WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                        ::hash(),
                'catalog_row_code' => (string) $row['row_code'],
                'catalog_row_hash' => (string) $row['row_hash'],
                'one_primary_idea' => true,
                'selection_changed_from_c1' => false,
                'new_signal_price_threshold_introduced' => false,
                'decision_time_fields_only' => true,
                'fixed_execution_before_entry' => true,
                'future_derived_route_used' => false,
                'oos_used' => false,
                'canonical_gates_changed' => false,
                'ticker_blacklist_used' => false,
                'month_blacklist_used' => false,
                'sector_whitelist_used' => false,
                'entry_gap_as_runtime_input_used' => false,
                'expected_eval_model' => self::EXPECTED_EVAL_MODEL,
            ];
            $import = $this->draftImport->execute(
                $repairedPayload,
                (int) $row['param_id'],
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::CATALOG_CODE,
                $provenance
            );
            if (($import['status'] ?? '') !== 'DRAFT_PERSISTED'
                || ($import['paramset_status'] ?? '') !== 'DRAFT') {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_DRAFT_PERSISTENCE_FAILED: '
                    .(string) ($import['reason_code'] ?? 'UNKNOWN')
                );
            }
            $canonicalWrite = $this->writeJson(
                $canonicalOutputPath,
                $import['validation']['canonical_payload'],
                (bool) ($options['overwrite'] ?? false)
            );
        } catch (\Throwable $exception) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_PERSISTENCE_FAILED',
                ['error' => $exception->getMessage()]
            );
        }
        $after = $this->forbiddenBoundaryCounts();
        if ($before !== $after) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_FORBIDDEN_MUTATION'
            );
        }

        $result = [
            'schema_version' => 'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_DRAFT_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => 'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_DRAFT_PERSISTED',
            'reason_code' =>
                'WS_PRICE_QUALITY_P01_EXECUTION_IDENTITY_CORRECTED_WITHOUT_STRATEGY_CHANGE',
            'approval_reference' => $approvalReference,
            'identity_repair_only' => true,
            'strategy_semantics_changed' => false,
            'remediation_rounds_used' => 1,
            'remediation_rounds_remaining' => 0,
            'invalidated_eval_id' => self::SOURCE_EVAL_ID,
            'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
            'catalog_seed' => $seed,
            'catalog_code' =>
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::CATALOG_CODE,
            'row_code' => (string) $row['row_code'],
            'bt_param_id' => (int) $row['param_id'],
            'param_set_id' => (int) $import['param_set_id'],
            'params_hash' => (string) $import['validation']['canonical_hash'],
            'eval_model' => self::EXPECTED_EVAL_MODEL,
            'eval_model_hash' =>
                (string) ($import['persistence']['eval_model_hash'] ?? ''),
            'implementation_hash' =>
                (string) ($import['persistence']['implementation_hash'] ?? ''),
            'persistence_status' =>
                (string) ($import['persistence']['status'] ?? ''),
            'canonical_file' => $canonicalWrite,
            'official_is_runtime_invoked' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' =>
                'WS_PRICE_QUALITY_P01_RERUN_SAME_SINGLE_REMEDIATION_WITH_CORRECTED_IDENTITY',
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
            || strtolower((string) sha1_file($path))
                !== self::SOURCE_ARTIFACT_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'FILE_SHA1_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)
            || ($artifact['artifact_hash'] ?? '') !== self::SOURCE_ARTIFACT_HASH
            || (int) ($artifact['param_set_id'] ?? 0)
                !== self::SOURCE_PARAM_SET_ID
            || (int) ($artifact['is_calibration']['evaluations'][0]['eval_id']
                ?? 0) !== self::SOURCE_EVAL_ID
            || ($artifact['official_evidence_manifest']['evidence_manifest_hash']
                ?? '') !== self::SOURCE_EVIDENCE_MANIFEST_HASH
            || ($artifact['canonical_is_gates_pass'] ?? true) !== false
            || ($artifact['oos_table_read'] ?? true) !== false) {
            return [
                'valid' => false,
                'reason_code' => 'ARTIFACT_IDENTITY_MISMATCH',
            ];
        }
        $payload = $artifact;
        unset($payload['artifact_hash']);

        return [
            'valid' => $this->identity->stableHash($payload)
                === self::SOURCE_ARTIFACT_HASH,
            'reason_code' => 'WS_PRICE_QUALITY_P01_INVALID_IDENTITY_EVAL_VERIFIED',
        ];
    }

    private function forbiddenBoundaryCounts(): array
    {
        return [
            'watchlist_bt_eval' => DB::table('watchlist_bt_eval')->count(),
            'active_paramsets' => DB::table('watchlist_param_sets')
                ->where('policy_code', 'WS')
                ->where('status', 'ACTIVE')
                ->count(),
            'watchlist_plan_runs' => Schema::hasTable('watchlist_plan_runs')
                ? DB::table('watchlist_plan_runs')->count()
                : 0,
        ];
    }

    private function writeJson(string $path, array $payload, bool $overwrite): array
    {
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ).PHP_EOL;
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_OUTPUT_DIRECTORY_FAILED'
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
                    'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_OUTPUT_EXISTS'
                );
            }
        }
        $temporary = $path.'.tmp.'.getmypid();
        if (file_put_contents($temporary, $json, LOCK_EX) === false
            || ! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_OUTPUT_WRITE_FAILED'
            );
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
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'identity_repair_only' => true,
            'strategy_semantics_changed' => false,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
