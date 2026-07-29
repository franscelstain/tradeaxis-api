<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingPriceQualityP01OfficialIsEvidenceService
{
    public const RUN_CODE = 'WS_PRICE_QUALITY_P01_VERSIONED_OFFICIAL_IS_EVIDENCE';
    public const APPROVAL_REFERENCE =
        'WS_PRICE_QUALITY_P01_OPERATOR_APPROVED_OFFICIAL_IS_ONLY';
    public const CANONICAL_IS_FROM = '2023-01-02';
    public const CANONICAL_IS_TO = '2025-05-21';

    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier;
    private WeeklySwingParamsetRuntimeAdapter $runtimeAdapter;
    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WatchlistBacktestIsCalibrationService $calibration;

    public function __construct(
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier = null,
        WeeklySwingParamsetRuntimeAdapter $runtimeAdapter = null,
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WatchlistBacktestIsCalibrationService $calibration = null
    ) {
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->bindingVerifier = $bindingVerifier
            ?: new WeeklySwingParamsetBacktestBindingVerifier();
        $this->runtimeAdapter = $runtimeAdapter
            ?: new WeeklySwingParamsetRuntimeAdapter();
        $this->identity = $identity
            ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->calibration = $calibration
            ?: new WatchlistBacktestIsCalibrationService();
    }

    public function execute(
        int $paramSetId,
        string $fromDate,
        string $toDate,
        string $approvalReference,
        bool $operatorApproved,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked('WS_PRICE_QUALITY_P01_OPERATOR_APPROVAL_MISSING');
        }
        if ($fromDate !== self::CANONICAL_IS_FROM
            || $toDate !== self::CANONICAL_IS_TO) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_CANONICAL_IS_WINDOW_MISMATCH'
            );
        }
        $schema = $this->schemaReadiness();
        if (! $schema['pass']) {
            return $this->blocked('WS_PRICE_QUALITY_P01_SCHEMA_NOT_READY', [
                'schema_readiness' => $schema,
            ]);
        }

        $draft = DB::table('watchlist_param_sets')
            ->where('param_set_id', $paramSetId)
            ->first();
        if (! $draft
            || (string) $draft->status !== 'DRAFT'
            || (string) $draft->policy_code !== 'WS') {
            return $this->blocked('WS_PRICE_QUALITY_P01_DRAFT_NOT_FOUND');
        }
        $payload = json_decode((string) $draft->params_json, true);
        $provenance = json_decode((string) $draft->provenance_json, true);
        if (! is_array($payload) || ! is_array($provenance)) {
            return $this->blocked('WS_PRICE_QUALITY_P01_DRAFT_PAYLOAD_INVALID');
        }
        $source = is_array($provenance['source'] ?? null)
            ? $provenance['source']
            : [];
        $isInitial = ($source['stage'] ?? '')
            === WeeklySwingPriceQualityP01DraftCatalogService::RUN_CODE;
        $isDirectRemediation = ($source['stage'] ?? '')
            === WeeklySwingPriceQualityP01RemediationDraftService::RUN_CODE;
        $isIdentityRepair = ($source['stage'] ?? '')
            === WeeklySwingPriceQualityP01IdentityRepairDraftService::RUN_CODE;
        $isRemediation = $isDirectRemediation || $isIdentityRepair;
        if ((! $isInitial && ! $isRemediation)
            || ($source['separate_new_strategy_scope'] ?? false) !== true
            || ($source['c171_reopened'] ?? true) !== false
            || ($source['r02_reopened'] ?? true) !== false
            || ($source['s01_reopened'] ?? true) !== false
            || ($source['source_anchor_is_best_of_failed_binding'] ?? true) !== false
            || ($source['one_primary_idea'] ?? false) !== true
            || ($source['decision_time_fields_only'] ?? false) !== true
            || ($source['fixed_execution_before_entry'] ?? false) !== true
            || ($source['future_derived_route_used'] ?? true) !== false
            || ($source['oos_used'] ?? true) !== false
            || ($source['canonical_gates_changed'] ?? true) !== false
            || ($source['ticker_blacklist_used'] ?? true) !== false
            || ($source['month_blacklist_used'] ?? true) !== false
            || ($source['sector_whitelist_used'] ?? true) !== false
            || ($source['entry_gap_as_runtime_input_used'] ?? true) !== false
            || ($source['primary_hypothesis_code'] ?? '')
                !== WatchlistBacktestPriceQualityP01ParamGridCatalog
                    ::PRIMARY_HYPOTHESIS_CODE) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_DRAFT_PROVENANCE_INVALID'
            );
        }
        if ($isInitial
            && ($source['diagnostic_artifact_hash'] ?? '')
                !== WeeklySwingPriceQualityP01DraftCatalogService
                    ::DIAGNOSTIC_ARTIFACT_HASH) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_INITIAL_PROVENANCE_INVALID'
            );
        }
        if ($isDirectRemediation
            && (($source['single_allowed_remediation'] ?? false) !== true
                || (int) ($source['remediation_round'] ?? 0) !== 1
                || (int) ($source['max_remediation_rounds'] ?? 0) !== 1
                || (int) ($source['source_param_set_id'] ?? 0)
                    !== WeeklySwingPriceQualityP01RemediationDraftService
                        ::SOURCE_PARAM_SET_ID
                || (int) ($source['source_eval_id'] ?? 0)
                    !== WeeklySwingPriceQualityP01RemediationDraftService
                        ::SOURCE_EVAL_ID
                || ($source['source_official_is_artifact_hash'] ?? '')
                    !== WeeklySwingPriceQualityP01RemediationDraftService
                        ::SOURCE_OFFICIAL_IS_ARTIFACT_HASH
                || ($source['selection_changed_from_c1'] ?? true) !== false
                || ($source['new_signal_price_threshold_introduced'] ?? true)
                    !== false)) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_REMEDIATION_PROVENANCE_INVALID'
            );
        }
        if ($isIdentityRepair
            && (($source['single_allowed_remediation'] ?? false) !== true
                || (int) ($source['remediation_round'] ?? 0) !== 1
                || (int) ($source['max_remediation_rounds'] ?? 0) !== 1
                || ($source['identity_repair_only'] ?? false) !== true
                || ($source['strategy_semantics_changed'] ?? true) !== false
                || ($source['remediation_round_incremented'] ?? true) !== false
                || ($source['second_remediation_created'] ?? true) !== false
                || (int) ($source['invalidated_eval_id'] ?? 0)
                    !== WeeklySwingPriceQualityP01IdentityRepairDraftService
                        ::SOURCE_EVAL_ID
                || (int) ($source['source_param_set_id'] ?? 0)
                    !== WeeklySwingPriceQualityP01IdentityRepairDraftService
                        ::SOURCE_PARAM_SET_ID
                || ($source['source_official_is_artifact_hash'] ?? '')
                    !== WeeklySwingPriceQualityP01IdentityRepairDraftService
                        ::SOURCE_ARTIFACT_HASH
                || ($source['expected_eval_model'] ?? '')
                    !== WeeklySwingPriceQualityP01IdentityRepairDraftService
                        ::EXPECTED_EVAL_MODEL
                || ($source['selection_changed_from_c1'] ?? true) !== false
                || ($source['new_signal_price_threshold_introduced'] ?? true)
                    !== false)) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_IDENTITY_REPAIR_PROVENANCE_INVALID'
            );
        }

        $validation = $this->validator->validate($payload);
        if (! ($validation['valid'] ?? false)
            || (string) $draft->params_hash
                !== (string) ($validation['canonical_hash'] ?? '')) {
            return $this->blocked('WS_PRICE_QUALITY_P01_DRAFT_VALIDATION_FAILED', [
                'validation' => $validation,
            ]);
        }
        $selection =
            $validation['canonical_payload']['research_selection'] ?? null;
        $execution =
            $validation['canonical_payload']['research_execution'] ?? null;
        if (! is_array($selection)
            || ! is_array($execution)
            || ! WatchlistBacktestPriceQualityP01ParamGridCatalog::isKnownRow(
                (string) ($selection['hypothesis_code'] ?? '')
            )
            || ($selection['rule_code'] ?? '')
                !== WatchlistBacktestPriceQualityP01ParamGridCatalog::RULE_CODE
            || ($selection['signal_date_only'] ?? false) !== true
            || ($selection['oos_used'] ?? true) !== false
            || ($execution['fixed_before_entry'] ?? false) !== true
            || ($execution['future_derived_route_used'] ?? true) !== false
            || ($execution['oos_used'] ?? true) !== false) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_RESEARCH_CONTRACT_INVALID'
            );
        }

        $binding = is_array($provenance['bt_binding'] ?? null)
            ? $provenance['bt_binding']
            : [];
        $btParamId = (int) ($binding['bt_param_id'] ?? 0);
        $expectedCatalogCode = $isRemediation
            ? WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::CATALOG_CODE
            : WatchlistBacktestPriceQualityP01ParamGridCatalog::CATALOG_CODE;
        if (($binding['catalog_code'] ?? '') !== $expectedCatalogCode) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_CATALOG_BINDING_INVALID'
            );
        }
        $bindingVerification = $this->bindingVerifier->verify(
            $validation['canonical_payload'],
            $btParamId,
            $expectedCatalogCode
        );
        if (! ($bindingVerification['valid'] ?? false)) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_PARAMSET_BINDING_INVALID',
                ['binding_verification' => $bindingVerification]
            );
        }
        foreach (['catalog_version', 'catalog_hash', 'row_code', 'row_hash'] as $field) {
            if ((string) ($binding[$field] ?? '')
                !== (string) ($bindingVerification[$field] ?? '')) {
                return $this->blocked(
                    'WS_PRICE_QUALITY_P01_PARAMSET_BINDING_DRIFT',
                    ['binding_field' => $field]
                );
            }
        }
        $rowCode = (string) $bindingVerification['row_code'];
        $expectedSelection = $isRemediation
            ? WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::researchSelection()
            : WatchlistBacktestPriceQualityP01ParamGridCatalog
                ::researchSelectionForRow($rowCode);
        $expectedExecution = $isRemediation
            ? WatchlistBacktestPriceQualityP01RemediationParamGridCatalog
                ::researchExecution()
            : WatchlistBacktestPriceQualityP01ParamGridCatalog
                ::researchExecution();
        if ($this->identity->stableHash($selection)
                !== $this->identity->stableHash($expectedSelection)
            || $this->identity->stableHash($execution)
                !== $this->identity->stableHash($expectedExecution)) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_CATALOG_CONTRACT_MISMATCH'
            );
        }

        $runtime = $this->runtimeAdapter->adapt(
            $validation['canonical_payload']
        );
        $evalModel = WatchlistBacktestStrategyService::canonicalEvalModel($runtime);
        $expectedIdentity = $this->identity->identity(
            $validation['canonical_payload'],
            $evalModel
        );
        foreach ([
            'eval_model', 'eval_model_hash', 'implementation_version',
            'implementation_hash',
        ] as $field) {
            if ((string) ($draft->{$field} ?? '')
                !== (string) ($expectedIdentity[$field] ?? '')) {
                return $this->blocked(
                    'WS_PRICE_QUALITY_P01_EXECUTION_IDENTITY_MISMATCH',
                    ['identity_field' => $field]
                );
            }
        }

        $calendar =
            (new \App\Application\MarketData\Services\MarketDataTradingCalendarReadService())
                ->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_CALENDAR_UNAVAILABLE',
                ['calendar' => $calendar]
            );
        }

        $boundariesBefore = $this->forbiddenBoundaryCounts();
        $spoolDirectory = dirname($outputPath)
            .DIRECTORY_SEPARATOR.'.ws-p01-official-evidence-spool';
        $spoolRunKey = 'ws-p01-'.sha1(implode('|', [
            (string) $draft->params_hash,
            (string) $btParamId,
            $fromDate,
            $toDate,
        ]));
        $this->clearPreviousSpoolRun($spoolDirectory, $spoolRunKey);
        $calibration = $this->calibration->calibrate(
            $calendar['trade_dates'],
            [
                'policy_code' => 'WS',
                'catalog_code' => $expectedCatalogCode,
                'only_param_id' => $btParamId,
                'paramset_overrides_by_param_id' => [$btParamId => $runtime],
                'identity_paramset_hash_by_param_id' => [
                    $btParamId => (string) $draft->params_hash,
                ],
                'require_official_evidence' => true,
                'strict_is_boundary' => true,
                'official_evidence_spool' => [
                    'enabled' => true,
                    'directory' => $spoolDirectory,
                    'run_key' => $spoolRunKey,
                ],
                'compact_replay_items' => true,
                'executed_at' => (string) ($options['executed_at']
                    ?? $toDate.'T23:59:59+07:00'),
            ]
        );
        $boundariesAfter = $this->forbiddenBoundaryCounts();
        if ($boundariesBefore !== $boundariesAfter) {
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_FORBIDDEN_MUTATION_DETECTED'
            );
        }

        $evaluation = $calibration['evaluations'][0] ?? [];
        if ((int) ($evaluation['eval_id'] ?? 0) < 1
            || ! is_array($evaluation['official_evidence_manifest'] ?? null)
            || ! in_array(
                (string) ($evaluation['official_evidence_persistence_status'] ?? ''),
                ['INSERTED', 'IDEMPOTENT'],
                true
            )) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_OFFICIAL_EVIDENCE_NOT_PERSISTED',
                [
                    'calibration_status' => $calibration['status'] ?? null,
                    'calibration_reason_code' =>
                        $calibration['reason_code'] ?? null,
                    'evaluation' => $evaluation,
                ]
            );
        }
        if ((string) ($evaluation['paramset_hash'] ?? '')
                !== (string) $draft->params_hash
            || (string) ($evaluation['eval_model_hash'] ?? '')
                !== (string) $draft->eval_model_hash
            || (string) ($evaluation['implementation_hash'] ?? '')
                !== (string) $draft->implementation_hash
            || ($evaluation['strict_is_boundary'] ?? false) !== true
            || (string) ($evaluation['hard_market_data_to_date'] ?? '')
                !== self::CANONICAL_IS_TO
            || strcmp(
                (string) ($evaluation['max_requested_market_data_date'] ?? ''),
                self::CANONICAL_IS_TO
            ) > 0) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_PERSISTED_IS_IDENTITY_MISMATCH',
                ['evaluation' => $evaluation]
            );
        }

        $routeProof = [
            'fixed_eval_model' => (string) ($evaluation['eval_model'] ?? '')
                === (string) $draft->eval_model,
            'trade_candidates_frozen_before_price_read' =>
                ($evaluation['trade_candidates_frozen_before_price_read'] ?? false)
                    === true,
            'future_price_used_for_evaluation_only' =>
                ($evaluation['future_price_used_for_evaluation_only'] ?? false)
                    === true,
            'strategy_payload_immutable' =>
                ($evaluation['strategy_payload_immutable'] ?? false) === true,
            'selection_signal_date_only' =>
                ($selection['signal_date_only'] ?? false) === true,
            'selection_oos_not_used' =>
                ($selection['oos_used'] ?? true) === false,
            'selection_uses_exact_signal_close_only' =>
                is_numeric($selection['thresholds']['min_signal_close_price'] ?? null),
            'execution_fixed_before_entry' =>
                ($execution['fixed_before_entry'] ?? false) === true,
            'execution_future_route_not_used' =>
                ($execution['future_derived_route_used'] ?? true) === false,
            'execution_oos_not_used' =>
                ($execution['oos_used'] ?? true) === false,
        ];
        $routeProof['pass'] = ! in_array(false, $routeProof, true);
        if (! $routeProof['pass']) {
            return $this->blocked(
                'WS_PRICE_QUALITY_P01_EXECUTION_ROUTE_NOT_PROVEN',
                ['execution_route_proof' => $routeProof]
            );
        }

        $isPass = (bool) ($evaluation['calibration_valid'] ?? false);
        $artifact = [
            'schema_version' => 'WS_PRICE_QUALITY_P01_OFFICIAL_IS_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => $isPass
                ? ($isRemediation
                    ? 'WS_PRICE_QUALITY_P01_REMEDIATION_OFFICIAL_IS_PASSED_OOS_IDENTITY_REVIEW_ALLOWED'
                    : 'WS_PRICE_QUALITY_P01_OFFICIAL_IS_PASSED_OOS_IDENTITY_REVIEW_ALLOWED')
                : ($isRemediation
                    ? 'WS_PRICE_QUALITY_P01_REMEDIATION_OFFICIAL_IS_FAILED_P01_CLOSED'
                    : 'WS_PRICE_QUALITY_P01_OFFICIAL_IS_FAILED_OOS_FORBIDDEN'),
            'reason_code' => $isPass
                ? ($isRemediation
                    ? 'WS_PRICE_QUALITY_P01_REMEDIATION_PASSED_ALL_CANONICAL_IS_GATES'
                    : 'WS_PRICE_QUALITY_P01_CANDIDATE_PASSED_ALL_CANONICAL_IS_GATES')
                : ($isRemediation
                    ? 'WS_PRICE_QUALITY_P01_REMEDIATION_FAILED_NO_MORE_REMEDIATION'
                    : 'WS_PRICE_QUALITY_P01_CANDIDATE_FAILED_CANONICAL_IS_GATES'),
            'approval_reference' => $approvalReference,
            'separate_new_strategy_scope' => true,
            'c171_reopened' => false,
            'r02_reopened' => false,
            's01_reopened' => false,
            'source_anchor_is_best_of_failed_binding' => false,
            'param_set_id' => $paramSetId,
            'paramset_status' => (string) $draft->status,
            'params_hash' => (string) $draft->params_hash,
            'row_code' => $rowCode,
            'single_allowed_remediation' => $isRemediation,
            'identity_repair_only' => $isIdentityRepair,
            'invalidated_eval_id' => $isIdentityRepair
                ? WeeklySwingPriceQualityP01IdentityRepairDraftService
                    ::SOURCE_EVAL_ID
                : null,
            'minimum_signal_close_price' =>
                (float) $selection['thresholds']['min_signal_close_price'],
            'primary_hypothesis_code' =>
                WatchlistBacktestPriceQualityP01ParamGridCatalog
                    ::PRIMARY_HYPOTHESIS_CODE,
            'candidate_code' => (string) $selection['hypothesis_code'],
            'research_rule_code' => (string) $selection['rule_code'],
            'research_selection_hash' =>
                $this->identity->stableHash($selection),
            'research_execution_rule_code' => (string) $execution['rule_code'],
            'research_execution_hash' =>
                $this->identity->stableHash($execution),
            'eval_model' => (string) $draft->eval_model,
            'eval_model_hash' => (string) $draft->eval_model_hash,
            'implementation_version' => (string) $draft->implementation_version,
            'implementation_hash' => (string) $draft->implementation_hash,
            'evidence_pipeline_version' =>
                WeeklySwingBacktestEvidenceIdentityService
                    ::EVIDENCE_PIPELINE_VERSION,
            'evidence_pipeline_hash' =>
                WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash(),
            'bt_binding' => $bindingVerification,
            'is_from' => $fromDate,
            'is_to' => $toDate,
            'canonical_is_window_match' => true,
            'strict_is_boundary' => true,
            'hard_market_data_to_date' => self::CANONICAL_IS_TO,
            'max_requested_market_data_date' =>
                $evaluation['max_requested_market_data_date'] ?? null,
            'is_calibration' => $calibration,
            'official_evidence_storage_mode' => 'JSONL_SPOOL',
            'official_evidence_manifest' =>
                $evaluation['official_evidence_manifest'] ?? null,
            'official_evidence_persistence_status' =>
                $evaluation['official_evidence_persistence_status'] ?? null,
            'canonical_is_gates_pass' => $isPass,
            'execution_route_proof' => $routeProof,
            'future_derived_route_used' => false,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_table_read' => false,
            'oos_mutated' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' => $isPass
                ? 'WS_PRICE_QUALITY_P01_REVIEW_WINNER_IS_IDENTITY_BEFORE_SINGLE_OFFICIAL_OOS'
                : ($isRemediation
                    ? 'WS_PRICE_QUALITY_P01_CLOSE_FAILED_NO_MORE_REMEDIATION_OOS_FORBIDDEN'
                    : 'WS_PRICE_QUALITY_P01_WAIT_FOR_BOTH_RESULTS_THEN_AT_MOST_ONE_REMEDIATION'),
        ];
        $artifact['artifact_hash'] = $this->identity->stableHash($artifact);
        $artifact['write'] = $this->writeArtifact(
            $artifact,
            $outputPath,
            (bool) ($options['overwrite'] ?? false)
        );

        return $artifact;
    }

    private function forbiddenBoundaryCounts(): array
    {
        return [
            'active_paramsets' => DB::table('watchlist_param_sets')
                ->where('policy_code', 'WS')
                ->where('status', 'ACTIVE')
                ->count(),
            'watchlist_plan_runs' => Schema::hasTable('watchlist_plan_runs')
                ? DB::table('watchlist_plan_runs')->count()
                : 0,
        ];
    }

    private function clearPreviousSpoolRun(string $directory, string $runKey): void
    {
        if (! is_dir($directory)) {
            return;
        }
        foreach (glob($directory.DIRECTORY_SEPARATOR.$runKey.'.*') ?: [] as $path) {
            if (is_file($path) && ! @unlink($path)) {
                throw new \RuntimeException(
                    'WS_PRICE_QUALITY_P01_PREVIOUS_SPOOL_CLEANUP_FAILED: '.$path
                );
            }
        }
    }

    private function schemaReadiness(): array
    {
        $requirements = [
            'watchlist_param_sets' => [
                'params_hash', 'eval_model', 'eval_model_hash',
                'implementation_version', 'implementation_hash',
            ],
            'watchlist_bt_eval' => [
                'eval_id', 'paramset_hash', 'eval_model_hash',
                'implementation_version', 'implementation_hash',
                'evidence_pipeline_version', 'evidence_pipeline_hash',
                'picks_hash', 'universe_hash', 'cutoffs_hash',
                'evidence_manifest_hash',
            ],
            'watchlist_bt_picks_ws' => ['eval_id', 'row_hash'],
            'watchlist_bt_universe_ws' => [
                'eval_id', 'policy_code', 'param_id', 'row_hash',
                'signal_close_price',
            ],
            'watchlist_bt_cutoffs_ws' => ['eval_id', 'row_hash'],
        ];
        $missing = [];
        foreach ($requirements as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $missing[] = $table;
                continue;
            }
            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $missing[] = $table.'.'.$column;
                }
            }
        }

        return ['pass' => $missing === [], 'missing' => $missing];
    }

    private function writeArtifact(
        array $artifact,
        string $path,
        bool $overwrite
    ): array {
        if ($path === '') {
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_OUTPUT_PATH_REQUIRED'
            );
        }
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_OUTPUT_EXISTS_USE_OVERWRITE'
            );
        }
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_OUTPUT_DIRECTORY_CREATE_FAILED'
            );
        }
        $json = json_encode(
            $artifact,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ).PHP_EOL;
        $temporary = $path.'.tmp.'.getmypid();
        if (file_put_contents($temporary, $json, LOCK_EX) === false
            || ! rename($temporary, $path)) {
            @unlink($temporary);
            throw new \RuntimeException(
                'WS_PRICE_QUALITY_P01_OUTPUT_WRITE_FAILED'
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
            'phase_label' => self::RUN_CODE,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'separate_new_strategy_scope' => true,
            'c171_reopened' => false,
            'r02_reopened' => false,
            's01_reopened' => false,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_table_read' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
