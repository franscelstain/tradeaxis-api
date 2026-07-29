<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestParamGridRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingPriceQualityP01RemediationDraftService
{
    public const RUN_CODE =
        'WS_PRICE_QUALITY_P01_SINGLE_ALLOWED_REMEDIATION_DRAFT';
    public const APPROVAL_REFERENCE =
        'WS_PRICE_QUALITY_P01_OPERATOR_APPROVED_SINGLE_REMEDIATION_DRAFT';
    public const SOURCE_PARAM_SET_ID = 25;
    public const SOURCE_BT_PARAM_ID = 178;
    public const SOURCE_EVAL_ID = 216;
    public const SOURCE_PARAMS_HASH =
        '2fb258a0e5c77ff9ee0347a9656e8ff77f3ae53c';
    public const SOURCE_OFFICIAL_IS_ARTIFACT_HASH =
        '68e23dbcb942aab5e53fb00c58e371d76e4fa6a0';
    public const SOURCE_OFFICIAL_IS_FILE_SHA1 =
        '0a6c3611fed404887ff1be66ef20201d4fbf266b';
    public const SOURCE_EVIDENCE_MANIFEST_HASH =
        '01b398612ee5add8b757c468f495dd37427775be';

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
                'WS_PRICE_QUALITY_P01_REMEDIATION_OPERATOR_APPROVAL_MISSING'
            );
        }
        foreach ([
            'watchlist_param_sets', 'watchlist_bt_eval',
            'watchlist_bt_param_grid',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                return $this->blocked(
                    'WS_PRICE_QUALITY_P01_REMEDIATION_SCHEMA_NOT_READY',
                    ['missing_table' => $table]
                );
            }
        }
        $sourceArtifact = $this->verifySourceArtifact($sourceArtifactPath);
        if (! ($sourceArtifact['valid'] ?? false)) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_REMEDIATION_SOURCE_ARTIFACT_INVALID',
                $sourceArtifact
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
                'WS_PRICE_QUALITY_P01_REMEDIATION_SOURCE_IDENTITY_INVALID'
            );
        }
        $sourcePayload = json_decode((string) $sourceDraft->params_json, true);
        $sourceValidation = is_array($sourcePayload)
            ? $this->validator->validate($sourcePayload)
            : ['valid' => false];
        if (! ($sourceValidation['valid'] ?? false)
            || ($sourceValidation['canonical_hash'] ?? '')
                !== self::SOURCE_PARAMS_HASH) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_REMEDIATION_SOURCE_PARAMSET_INVALID'
            );
        }
        if ($this->identity->stableHash(
            $sourceValidation['canonical_payload']['research_selection'] ?? []
        ) !== $this->identity->stableHash(
            WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::researchSelection()
        )) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_REMEDIATION_SOURCE_SELECTION_INVALID'
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
            if (count($rows) !== 1) {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_REMEDIATION_CATALOG_COUNT_MISMATCH'
                );
            }
            $row = $rows[0];
            $payload = $sourceValidation['canonical_payload'];
            $payload['paramset_code'] =
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::ROW_CODE;
            $payload['research_selection'] =
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::researchSelection();
            $payload['research_execution'] =
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::researchExecution();
            unset($payload['risk']['max_signal_tick_risk_expansion_pct']);
            $selection = $payload['research_selection'];
            $execution = $payload['research_execution'];
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
                'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
                'source_bt_param_id' => self::SOURCE_BT_PARAM_ID,
                'source_eval_id' => self::SOURCE_EVAL_ID,
                'source_params_hash' => self::SOURCE_PARAMS_HASH,
                'source_official_is_artifact_hash' =>
                    self::SOURCE_OFFICIAL_IS_ARTIFACT_HASH,
                'source_official_is_file_sha1' =>
                    self::SOURCE_OFFICIAL_IS_FILE_SHA1,
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
                'hypothesis_code' => (string) $selection['hypothesis_code'],
                'research_rule_code' => (string) $selection['rule_code'],
                'research_execution_rule_code' => (string) $execution['rule_code'],
                'one_primary_idea' => true,
                'selection_changed_from_c1' => false,
                'new_signal_price_threshold_introduced' => false,
                'loss_threshold_source' =>
                    'UNCHANGED_CANONICAL_MONTHLY_AVERAGE_FLOOR',
                'decision_time_fields_only' => true,
                'fixed_execution_before_entry' => true,
                'future_derived_route_used' => false,
                'oos_used' => false,
                'canonical_gates_changed' => false,
                'ticker_blacklist_used' => false,
                'month_blacklist_used' => false,
                'sector_whitelist_used' => false,
                'entry_gap_as_runtime_input_used' => false,
            ];
            $import = $this->draftImport->execute(
                $payload,
                (int) $row['param_id'],
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::CATALOG_CODE,
                $provenance
            );
            if (($import['status'] ?? '') !== 'DRAFT_PERSISTED'
                || ($import['paramset_status'] ?? '') !== 'DRAFT') {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_REMEDIATION_DRAFT_PERSISTENCE_FAILED: '
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
                'WS_PRICE_QUALITY_P01_REMEDIATION_PERSISTENCE_FAILED',
                ['error' => $exception->getMessage()]
            );
        }
        $after = $this->forbiddenBoundaryCounts();
        if ($before !== $after) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_REMEDIATION_FORBIDDEN_MUTATION'
            );
        }

        $result = [
            'schema_version' => 'WS_PRICE_QUALITY_P01_REMEDIATION_DRAFT_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' =>
                'WS_PRICE_QUALITY_P01_SINGLE_REMEDIATION_DRAFT_PERSISTED',
            'reason_code' =>
                'WS_PRICE_QUALITY_P01_FINAL_BOUNDED_REMEDIATION_LOCKED',
            'approval_reference' => $approvalReference,
            'separate_new_strategy_scope' => true,
            'c171_reopened' => false,
            'r02_reopened' => false,
            's01_reopened' => false,
            'single_allowed_remediation' => true,
            'remediation_rounds_used' => 1,
            'remediation_rounds_remaining' => 0,
            'source_param_set_id' => self::SOURCE_PARAM_SET_ID,
            'source_eval_id' => self::SOURCE_EVAL_ID,
            'source_official_is_artifact_hash' =>
                self::SOURCE_OFFICIAL_IS_ARTIFACT_HASH,
            'catalog_code' =>
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::CATALOG_CODE,
            'catalog_version' =>
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::CATALOG_VERSION,
            'catalog_hash' =>
                WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                    ::hash(),
            'catalog_seed' => $seed,
            'row_code' => (string) $row['row_code'],
            'bt_param_id' => (int) $row['param_id'],
            'param_set_id' => (int) $import['param_set_id'],
            'params_hash' => (string) $import['validation']['canonical_hash'],
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
                'WS_PRICE_QUALITY_P01_RUN_SINGLE_REMEDIATION_OFFICIAL_IS_THEN_CLOSE_IF_FAILED',
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
                !== self::SOURCE_OFFICIAL_IS_FILE_SHA1) {
            return ['valid' => false, 'reason_code' => 'FILE_SHA1_MISMATCH'];
        }
        $artifact = json_decode((string) file_get_contents($path), true);
        if (! is_array($artifact)
            || ($artifact['artifact_hash'] ?? '')
                !== self::SOURCE_OFFICIAL_IS_ARTIFACT_HASH
            || (int) ($artifact['param_set_id'] ?? 0)
                !== self::SOURCE_PARAM_SET_ID
            || (int) ($artifact['is_calibration']['evaluations'][0]['eval_id']
                ?? 0) !== self::SOURCE_EVAL_ID
            || ($artifact['official_evidence_manifest']['evidence_manifest_hash']
                ?? '') !== self::SOURCE_EVIDENCE_MANIFEST_HASH
            || ($artifact['canonical_is_gates_pass'] ?? true) !== false
            || ($artifact['oos_runtime_invoked'] ?? true) !== false
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
                === self::SOURCE_OFFICIAL_IS_ARTIFACT_HASH,
            'reason_code' =>
                'WS_PRICE_QUALITY_P01_SOURCE_OFFICIAL_IS_VERIFIED',
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
                'WS_PRICE_QUALITY_P01_REMEDIATION_OUTPUT_DIRECTORY_FAILED'
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
                    'WS_PRICE_QUALITY_P01_REMEDIATION_OUTPUT_EXISTS'
                );
            }
        }
        $temporary = $path.'.tmp.'.getmypid();
        if (file_put_contents($temporary, $json, LOCK_EX) === false
            || ! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_REMEDIATION_OUTPUT_WRITE_FAILED'
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
            'single_allowed_remediation' => true,
            'oos_runtime_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
