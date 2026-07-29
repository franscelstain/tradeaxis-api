<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingNewStrategyR02OfficialIsEvidenceService
{
    public const RUN_CODE = 'WS_NEW_STRATEGY_R02_VERSIONED_OFFICIAL_IS_EVIDENCE';
    public const APPROVAL_REFERENCE = 'WS_NEW_STRATEGY_R02_OPERATOR_APPROVED_OFFICIAL_IS_ONLY';
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
        $this->bindingVerifier = $bindingVerifier ?: new WeeklySwingParamsetBacktestBindingVerifier();
        $this->runtimeAdapter = $runtimeAdapter ?: new WeeklySwingParamsetRuntimeAdapter();
        $this->identity = $identity ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->calibration = $calibration ?: new WatchlistBacktestIsCalibrationService();
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
            return $this->blocked('WS_NEW_STRATEGY_R02_OPERATOR_APPROVAL_MISSING');
        }
        if ($fromDate !== self::CANONICAL_IS_FROM || $toDate !== self::CANONICAL_IS_TO) {
            return $this->blocked('WS_NEW_STRATEGY_R02_CANONICAL_IS_WINDOW_MISMATCH', [
                'expected_is_from' => self::CANONICAL_IS_FROM,
                'expected_is_to' => self::CANONICAL_IS_TO,
                'actual_is_from' => $fromDate,
                'actual_is_to' => $toDate,
            ]);
        }
        $schema = $this->schemaReadiness();
        if (! $schema['pass']) {
            return $this->blocked('WS_NEW_STRATEGY_R02_OFFICIAL_EVIDENCE_SCHEMA_NOT_READY', [
                'schema_readiness' => $schema,
            ]);
        }

        $draft = DB::table('watchlist_param_sets')->where('param_set_id', $paramSetId)->first();
        if (! $draft || (string) $draft->status !== 'DRAFT' || (string) $draft->policy_code !== 'WS') {
            return $this->blocked('WS_NEW_STRATEGY_R02_DRAFT_NOT_FOUND');
        }
        $canonicalPayload = json_decode((string) $draft->params_json, true);
        $provenance = json_decode((string) $draft->provenance_json, true);
        if (! is_array($canonicalPayload) || ! is_array($provenance)) {
            return $this->blocked('WS_NEW_STRATEGY_R02_DRAFT_PAYLOAD_INVALID');
        }
        $source = is_array($provenance['source'] ?? null) ? $provenance['source'] : [];
        $isRemediation = ($source['stage'] ?? '')
            === WeeklySwingNewStrategyR02RemediationDraftService::RUN_CODE;
        $isInitialCandidate = ($source['stage'] ?? '')
            === WeeklySwingNewStrategyR02DraftCatalogService::RUN_CODE;
        if ((! $isInitialCandidate && ! $isRemediation)
            || ($source['separate_new_strategy_scope'] ?? false) !== true
            || ($source['r01_artifact_hash'] ?? '') !== WeeklySwingNewStrategyR02DraftCatalogService::R01_ARTIFACT_HASH
            || ($source['one_primary_idea'] ?? false) !== true
            || ($source['oos_used'] ?? true) !== false
            || ($source['canonical_gates_changed'] ?? true) !== false) {
            return $this->blocked('WS_NEW_STRATEGY_R02_DRAFT_PROVENANCE_INVALID');
        }
        if ($isRemediation
            && (($source['single_allowed_remediation'] ?? false) !== true
                || ($source['source_param_set_id'] ?? null)
                    !== WeeklySwingNewStrategyR02RemediationDraftService::SOURCE_PARAM_SET_ID
                || ($source['source_eval_id'] ?? null)
                    !== WeeklySwingNewStrategyR02RemediationDraftService::SOURCE_EVAL_ID
                || ($source['source_official_is_artifact_hash'] ?? '')
                    !== WeeklySwingNewStrategyR02RemediationDraftService::SOURCE_OFFICIAL_IS_ARTIFACT_HASH
                || ($source['fixed_execution_before_entry'] ?? false) !== true
                || ($source['future_derived_route_used'] ?? true) !== false)) {
            return $this->blocked('WS_NEW_STRATEGY_R02_REMEDIATION_PROVENANCE_INVALID');
        }

        $validation = $this->validator->validate($canonicalPayload);
        if (! ($validation['valid'] ?? false)
            || (string) $draft->params_hash !== (string) ($validation['canonical_hash'] ?? '')) {
            return $this->blocked('WS_NEW_STRATEGY_R02_DRAFT_VALIDATION_FAILED', [
                'validation' => $validation,
            ]);
        }
        $selection = $validation['canonical_payload']['research_selection'] ?? null;
        if (! is_array($selection)
            || ($selection['signal_date_only'] ?? false) !== true
            || ($selection['oos_used'] ?? true) !== false
            || (string) ($selection['hypothesis_code'] ?? '') !== (string) ($source['hypothesis_code'] ?? '')
            || (string) ($selection['rule_code'] ?? '') !== (string) ($source['research_rule_code'] ?? '')) {
            return $this->blocked('WS_NEW_STRATEGY_R02_RESEARCH_SELECTION_IDENTITY_INVALID');
        }

        $binding = is_array($provenance['bt_binding'] ?? null) ? $provenance['bt_binding'] : [];
        $btParamId = (int) ($binding['bt_param_id'] ?? 0);
        $catalogCode = (string) ($binding['catalog_code'] ?? '');
        $expectedCatalogCode = $isRemediation
            ? WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::CATALOG_CODE
            : WatchlistBacktestNewStrategyR02ParamGridCatalog::CATALOG_CODE;
        if ($catalogCode !== $expectedCatalogCode) {
            return $this->blocked('WS_NEW_STRATEGY_R02_CATALOG_BINDING_INVALID');
        }
        $bindingVerification = $this->bindingVerifier->verify(
            $validation['canonical_payload'],
            $btParamId,
            $catalogCode
        );
        if (! ($bindingVerification['valid'] ?? false)) {
            return $this->blocked('WS_NEW_STRATEGY_R02_PARAMSET_BINDING_INVALID', [
                'binding_verification' => $bindingVerification,
            ]);
        }
        foreach (['catalog_version', 'catalog_hash', 'row_code', 'row_hash'] as $field) {
            if ((string) ($binding[$field] ?? '') !== (string) ($bindingVerification[$field] ?? '')) {
                return $this->blocked('WS_NEW_STRATEGY_R02_PARAMSET_BINDING_DRIFT', [
                    'binding_field' => $field,
                ]);
            }
        }
        $expectedSelection = $isRemediation
            ? WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchSelection()
            : WatchlistBacktestNewStrategyR02ParamGridCatalog::researchSelectionForRow(
                (string) $bindingVerification['row_code']
            );
        if ($this->identity->stableHash($selection) !== $this->identity->stableHash($expectedSelection)) {
            return $this->blocked('WS_NEW_STRATEGY_R02_RESEARCH_SELECTION_CATALOG_MISMATCH');
        }
        $researchExecution = $validation['canonical_payload']['research_execution'] ?? null;
        if ($isRemediation
            && (! is_array($researchExecution)
                || $this->identity->stableHash($researchExecution)
                    !== $this->identity->stableHash(
                        WatchlistBacktestNewStrategyR02RemediationParamGridCatalog::researchExecution()
                    ))) {
            return $this->blocked('WS_NEW_STRATEGY_R02_RESEARCH_EXECUTION_CATALOG_MISMATCH');
        }
        if (! $isRemediation && $researchExecution !== null) {
            return $this->blocked('WS_NEW_STRATEGY_R02_UNEXPECTED_RESEARCH_EXECUTION');
        }

        $runtimeParamset = $this->runtimeAdapter->adapt($validation['canonical_payload']);
        $evalModel = WatchlistBacktestStrategyService::canonicalEvalModel($runtimeParamset);
        $expectedIdentity = $this->identity->identity($validation['canonical_payload'], $evalModel);
        foreach (['eval_model', 'eval_model_hash', 'implementation_version', 'implementation_hash'] as $field) {
            if ((string) ($draft->{$field} ?? '') !== (string) $expectedIdentity[$field]) {
                return $this->blocked('WS_NEW_STRATEGY_R02_EXECUTION_IDENTITY_MISMATCH', [
                    'identity_field' => $field,
                ]);
            }
        }

        $calendar = (new \App\Application\MarketData\Services\MarketDataTradingCalendarReadService())
            ->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked('WS_NEW_STRATEGY_R02_IS_CALENDAR_UNAVAILABLE', ['calendar' => $calendar]);
        }
        $oosBefore = DB::table('watchlist_bt_oos_eval_ws')->count();
        $activeBefore = DB::table('watchlist_param_sets')
            ->where('policy_code', 'WS')
            ->where('status', 'ACTIVE')
            ->count();
        $planBefore = Schema::hasTable('watchlist_plan_runs')
            ? DB::table('watchlist_plan_runs')->count()
            : 0;

        $spoolDirectory = dirname($outputPath).DIRECTORY_SEPARATOR.'.ws-r02-official-evidence-spool';
        $spoolRunKey = 'ws-r02-'.sha1(implode('|', [
            (string) $draft->params_hash,
            (string) $btParamId,
            $fromDate,
            $toDate,
        ]));
        $this->clearPreviousSpoolRun($spoolDirectory, $spoolRunKey);
        $calibration = $this->calibration->calibrate($calendar['trade_dates'], [
            'policy_code' => 'WS',
            'catalog_code' => $catalogCode,
            'only_param_id' => $btParamId,
            'paramset_overrides_by_param_id' => [$btParamId => $runtimeParamset],
            'identity_paramset_hash_by_param_id' => [$btParamId => (string) $draft->params_hash],
            'require_official_evidence' => true,
            'strict_is_boundary' => true,
            'official_evidence_spool' => [
                'enabled' => true,
                'directory' => $spoolDirectory,
                'run_key' => $spoolRunKey,
            ],
            'compact_replay_items' => true,
            'executed_at' => (string) ($options['executed_at'] ?? $toDate.'T23:59:59+07:00'),
        ]);

        $oosAfter = DB::table('watchlist_bt_oos_eval_ws')->count();
        $activeAfter = DB::table('watchlist_param_sets')
            ->where('policy_code', 'WS')
            ->where('status', 'ACTIVE')
            ->count();
        $planAfter = Schema::hasTable('watchlist_plan_runs')
            ? DB::table('watchlist_plan_runs')->count()
            : 0;
        if ($oosAfter !== $oosBefore || $activeAfter !== $activeBefore || $planAfter !== $planBefore) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_FORBIDDEN_MUTATION_DETECTED');
        }

        $evaluation = $calibration['evaluations'][0] ?? [];
        if ((int) ($evaluation['eval_id'] ?? 0) < 1
            || ! is_array($evaluation['official_evidence_manifest'] ?? null)
            || ! in_array(
                (string) ($evaluation['official_evidence_persistence_status'] ?? ''),
                ['INSERTED', 'IDEMPOTENT'],
                true
            )) {
            return $this->blocked('WS_NEW_STRATEGY_R02_OFFICIAL_IS_EVIDENCE_NOT_PERSISTED', [
                'calibration_status' => $calibration['status'] ?? null,
                'calibration_reason_code' => $calibration['reason_code'] ?? null,
                'evaluation' => $evaluation,
            ]);
        }
        if ((string) ($evaluation['paramset_hash'] ?? '') !== (string) $draft->params_hash
            || (string) ($evaluation['eval_model_hash'] ?? '') !== (string) $draft->eval_model_hash
            || (string) ($evaluation['implementation_hash'] ?? '') !== (string) $draft->implementation_hash
            || (string) ($evaluation['evidence_pipeline_hash'] ?? '')
                !== WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash()
            || ($evaluation['strict_is_boundary'] ?? false) !== true
            || (string) ($evaluation['hard_market_data_to_date'] ?? '') !== self::CANONICAL_IS_TO
            || strcmp((string) ($evaluation['max_requested_market_data_date'] ?? ''), self::CANONICAL_IS_TO) > 0) {
            return $this->blocked('WS_NEW_STRATEGY_R02_PERSISTED_IS_IDENTITY_MISMATCH', [
                'evaluation' => $evaluation,
            ]);
        }

        $executionRouteProof = [
            'fixed_eval_model' => (string) ($evaluation['eval_model'] ?? '') === (string) $draft->eval_model,
            'trade_candidates_frozen_before_price_read' =>
                ($evaluation['trade_candidates_frozen_before_price_read'] ?? false) === true,
            'future_price_used_for_evaluation_only' =>
                ($evaluation['future_price_used_for_evaluation_only'] ?? false) === true,
            'strategy_payload_immutable' => ($evaluation['strategy_payload_immutable'] ?? false) === true,
            'research_selection_signal_date_only' => ($selection['signal_date_only'] ?? false) === true,
            'research_selection_oos_not_used' => ($selection['oos_used'] ?? true) === false,
            'research_execution_fixed_before_entry' => ! $isRemediation
                || (($researchExecution['fixed_before_entry'] ?? false) === true),
            'research_execution_future_route_not_used' => ! $isRemediation
                || (($researchExecution['future_derived_route_used'] ?? true) === false),
            'research_execution_oos_not_used' => ! $isRemediation
                || (($researchExecution['oos_used'] ?? true) === false),
        ];
        $executionRouteProof['pass'] = ! in_array(false, $executionRouteProof, true);
        if (! $executionRouteProof['pass']) {
            return $this->blocked('WS_NEW_STRATEGY_R02_EXECUTION_ROUTE_NOT_PROVEN', [
                'execution_route_proof' => $executionRouteProof,
            ]);
        }

        $isPass = (bool) ($evaluation['calibration_valid'] ?? false);
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => $isPass
                ? ($isRemediation
                    ? 'WS_NEW_STRATEGY_R02_SINGLE_REMEDIATION_OFFICIAL_IS_PASSED_OOS_LOCK_REVIEW_REQUIRED'
                    : 'WS_NEW_STRATEGY_R02_OFFICIAL_IS_PASSED_OOS_LOCK_REVIEW_REQUIRED')
                : ($isRemediation
                    ? 'WS_NEW_STRATEGY_R02_SINGLE_REMEDIATION_OFFICIAL_IS_FAILED_R02_CLOSED'
                    : 'WS_NEW_STRATEGY_R02_OFFICIAL_IS_FAILED_OOS_NOT_RUN'),
            'reason_code' => $isPass
                ? ($isRemediation
                    ? 'WS_NEW_STRATEGY_R02_SINGLE_REMEDIATION_PASSED_ALL_CANONICAL_IS_GATES'
                    : 'WS_NEW_STRATEGY_R02_CANDIDATE_PASSED_ALL_CANONICAL_IS_GATES')
                : ($isRemediation
                    ? 'WS_NEW_STRATEGY_R02_SINGLE_REMEDIATION_FAILED_NO_MORE_REMEDIATION'
                    : 'WS_NEW_STRATEGY_R02_CANDIDATE_FAILED_CANONICAL_IS_GATES'),
            'approval_reference' => $approvalReference,
            'separate_new_strategy_scope' => true,
            'param_set_id' => $paramSetId,
            'paramset_status' => (string) $draft->status,
            'params_hash' => (string) $draft->params_hash,
            'hypothesis_code' => (string) $selection['hypothesis_code'],
            'research_rule_code' => (string) $selection['rule_code'],
            'research_selection_hash' => $this->identity->stableHash($selection),
            'single_allowed_remediation' => $isRemediation,
            'research_execution_rule_code' => $isRemediation
                ? (string) ($researchExecution['rule_code'] ?? '')
                : null,
            'research_execution_hash' => $isRemediation
                ? $this->identity->stableHash($researchExecution)
                : null,
            'eval_model' => (string) $draft->eval_model,
            'eval_model_hash' => (string) $draft->eval_model_hash,
            'implementation_version' => (string) $draft->implementation_version,
            'implementation_hash' => (string) $draft->implementation_hash,
            'evidence_pipeline_version' => WeeklySwingBacktestEvidenceIdentityService::EVIDENCE_PIPELINE_VERSION,
            'evidence_pipeline_hash' => WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash(),
            'bt_binding' => $bindingVerification,
            'is_from' => $fromDate,
            'is_to' => $toDate,
            'canonical_is_window_match' => true,
            'strict_is_boundary' => true,
            'hard_market_data_to_date' => self::CANONICAL_IS_TO,
            'max_requested_market_data_date' => $evaluation['max_requested_market_data_date'] ?? null,
            'is_calibration' => $calibration,
            'official_evidence_storage_mode' => 'JSONL_SPOOL',
            'official_evidence_manifest' => $evaluation['official_evidence_manifest'] ?? null,
            'official_evidence_persistence_status' => $evaluation['official_evidence_persistence_status'] ?? null,
            'canonical_is_gates_pass' => $isPass,
            'execution_route_proof' => $executionRouteProof,
            'future_derived_route_used' => false,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_rows_before' => $oosBefore,
            'oos_rows_after' => $oosAfter,
            'oos_mutated' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' => $isPass
                ? 'WS_NEW_STRATEGY_R03_REVIEW_IS_IDENTITY_BEFORE_OFFICIAL_OOS'
                : ($isRemediation
                    ? 'WS_NEW_STRATEGY_R02_CLOSE_FAILED_NO_MORE_REMEDIATION_OOS_FORBIDDEN'
                    : 'WS_NEW_STRATEGY_R02_EVALUATE_SINGLE_ALLOWED_REMEDIATION'),
        ];
        $artifact['artifact_hash'] = $this->identity->stableHash($artifact);
        $artifact['write'] = $this->writeArtifact(
            $artifact,
            $outputPath,
            (bool) ($options['overwrite'] ?? false)
        );

        return $artifact;
    }

    private function clearPreviousSpoolRun(string $directory, string $runKey): void
    {
        if (! is_dir($directory)) {
            return;
        }
        foreach (glob($directory.DIRECTORY_SEPARATOR.$runKey.'.*') ?: [] as $path) {
            if (is_file($path) && ! @unlink($path)) {
                throw new \RuntimeException('WS_NEW_STRATEGY_R02_PREVIOUS_SPOOL_CLEANUP_FAILED: '.$path);
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
                'eval_id', 'paramset_hash', 'eval_model_hash', 'implementation_version',
                'implementation_hash', 'evidence_pipeline_version', 'evidence_pipeline_hash',
                'picks_hash', 'universe_hash', 'cutoffs_hash', 'evidence_manifest_hash',
            ],
            'watchlist_bt_picks_ws' => ['eval_id', 'row_hash'],
            'watchlist_bt_universe_ws' => ['eval_id', 'policy_code', 'param_id', 'row_hash'],
            'watchlist_bt_cutoffs_ws' => ['eval_id', 'row_hash'],
            'watchlist_bt_oos_eval_ws' => [
                'paramset_hash', 'eval_model_hash', 'implementation_hash',
                'is_evidence_manifest_hash',
            ],
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

    private function writeArtifact(array $artifact, string $path, bool $overwrite): array
    {
        if ($path === '') {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_OUTPUT_PATH_REQUIRED');
        }
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_OUTPUT_EXISTS_USE_OVERWRITE');
        }
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_OUTPUT_DIRECTORY_CREATE_FAILED');
        }
        $json = json_encode(
            $artifact,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ).PHP_EOL;
        $temp = $path.'.tmp.'.getmypid();
        if (file_put_contents($temp, $json, LOCK_EX) === false || ! rename($temp, $path)) {
            @unlink($temp);
            throw new \RuntimeException('WS_NEW_STRATEGY_R02_ARTIFACT_WRITE_FAILED');
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
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
