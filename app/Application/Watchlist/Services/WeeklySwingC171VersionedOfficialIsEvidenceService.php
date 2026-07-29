<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingC171VersionedOfficialIsEvidenceService
{
    public const RUN_CODE = 'C171_WEEKLY_SWING_VERSIONED_OFFICIAL_BACKTEST_EVIDENCE_AND_EXECUTABLE_IS_STRATEGY_REMEDIATION';
    public const PHASE_LABEL = self::RUN_CODE;
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
        if (! $operatorApproved || trim($approvalReference) === '') {
            return $this->blocked('C171_REJECTED_OPERATOR_APPROVAL_MISSING');
        }
        if ($fromDate !== self::CANONICAL_IS_FROM || $toDate !== self::CANONICAL_IS_TO) {
            return $this->blocked('C171_REJECTED_CANONICAL_IS_WINDOW_MISMATCH', [
                'expected_is_from' => self::CANONICAL_IS_FROM,
                'expected_is_to' => self::CANONICAL_IS_TO,
                'actual_is_from' => $fromDate,
                'actual_is_to' => $toDate,
            ]);
        }
        $schema = $this->schemaReadiness();
        if (! $schema['pass']) {
            return $this->blocked('C171_REJECTED_OFFICIAL_EVIDENCE_SCHEMA_UNVERSIONED', ['schema_readiness' => $schema]);
        }
        $draft = DB::table('watchlist_param_sets')->where('param_set_id', $paramSetId)->first();
        if (! $draft || (string) $draft->status !== 'DRAFT' || (string) $draft->policy_code !== 'WS') {
            return $this->blocked('C171_REJECTED_PARAMSET_DRAFT_MISSING');
        }
        $canonicalPayload = json_decode((string) $draft->params_json, true);
        $provenance = json_decode((string) $draft->provenance_json, true);
        if (! is_array($canonicalPayload) || ! is_array($provenance)) {
            return $this->blocked('C171_REJECTED_PARAMSET_PAYLOAD_INVALID');
        }
        $validation = $this->validator->validate($canonicalPayload);
        if (! ($validation['valid'] ?? false)) {
            return $this->blocked('C171_REJECTED_PARAMSET_VALIDATION_FAILED', ['validation' => $validation]);
        }
        if ((string) ($draft->params_hash ?? '') !== (string) $validation['canonical_hash']) {
            return $this->blocked('C171_REJECTED_PARAMSET_HASH_MISMATCH');
        }
        $binding = is_array($provenance['bt_binding'] ?? null) ? $provenance['bt_binding'] : [];
        $btParamId = (int) ($binding['bt_param_id'] ?? 0);
        $catalogCode = (string) ($binding['catalog_code'] ?? '');
        $bindingVerification = $this->bindingVerifier->verify($validation['canonical_payload'], $btParamId, $catalogCode);
        if (! ($bindingVerification['valid'] ?? false)) {
            return $this->blocked('C171_REJECTED_PARAMSET_BINDING_INVALID', ['binding_verification' => $bindingVerification]);
        }
        foreach (['catalog_version','catalog_hash','row_code','row_hash'] as $field) {
            if ((string) ($binding[$field] ?? '') !== (string) ($bindingVerification[$field] ?? '')) {
                return $this->blocked('C171_REJECTED_PARAMSET_BINDING_DRIFT', ['binding_field' => $field]);
            }
        }

        $runtimeParamset = $this->runtimeAdapter->adapt($validation['canonical_payload']);
        $evalModel = WatchlistBacktestStrategyService::canonicalEvalModel($runtimeParamset);
        $expectedIdentity = $this->identity->identity($validation['canonical_payload'], $evalModel);
        foreach (['eval_model','eval_model_hash','implementation_version','implementation_hash'] as $field) {
            if ((string) ($draft->{$field} ?? '') !== (string) $expectedIdentity[$field]) {
                return $this->blocked('C171_REJECTED_PARAMSET_EXECUTION_IDENTITY_MISMATCH', [
                    'identity_field' => $field,
                    'expected' => $expectedIdentity[$field],
                    'actual' => $draft->{$field} ?? null,
                ]);
            }
        }

        $oosBefore = Schema::hasTable('watchlist_bt_oos_eval_ws') ? DB::table('watchlist_bt_oos_eval_ws')->count() : 0;
        $calendar = (new \App\Application\MarketData\Services\MarketDataTradingCalendarReadService())
            ->resolveTradingDates($fromDate, $toDate);
        if (! ($calendar['is_ready'] ?? false)) {
            return $this->blocked('C171_REJECTED_IS_CALENDAR_UNAVAILABLE', ['calendar' => $calendar]);
        }

        $spoolDirectory = dirname($outputPath).DIRECTORY_SEPARATOR.'.c171-official-evidence-spool';
        $spoolRunKey = 'c171-'.sha1(implode('|', [
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
        if ($oosAfter !== $oosBefore) {
            throw new \RuntimeException('C171_OOS_MUTATION_FORBIDDEN: C171 must not read-write or execute OOS.');
        }

        $evaluation = $calibration['evaluations'][0] ?? [];
        if ((int) ($evaluation['eval_id'] ?? 0) < 1
            || ! is_array($evaluation['official_evidence_manifest'] ?? null)
            || ! in_array((string) ($evaluation['official_evidence_persistence_status'] ?? ''), ['INSERTED', 'IDEMPOTENT'], true)) {
            return $this->blocked('C171_REJECTED_OFFICIAL_IS_EVIDENCE_NOT_PERSISTED', [
                'calibration_status' => $calibration['status'] ?? null,
                'calibration_reason_code' => $calibration['reason_code'] ?? null,
                'evaluation_status' => $evaluation['status'] ?? null,
                'evaluation_reason_code' => $evaluation['reason_code'] ?? null,
                'evaluation' => $evaluation,
            ]);
        }
        if ((string) ($evaluation['paramset_hash'] ?? '') !== (string) $draft->params_hash
            || (string) ($evaluation['eval_model_hash'] ?? '') !== (string) $draft->eval_model_hash
            || (string) ($evaluation['implementation_hash'] ?? '') !== (string) $draft->implementation_hash
            || (string) ($evaluation['evidence_pipeline_version'] ?? '') !== WeeklySwingBacktestEvidenceIdentityService::EVIDENCE_PIPELINE_VERSION
            || (string) ($evaluation['evidence_pipeline_hash'] ?? '') !== WeeklySwingBacktestEvidenceIdentityService::evidencePipelineHash()) {
            return $this->blocked('C171_REJECTED_PERSISTED_IS_IDENTITY_MISMATCH', ['evaluation' => $evaluation]);
        }
        if (($evaluation['strict_is_boundary'] ?? false) !== true
            || (string) ($evaluation['hard_market_data_to_date'] ?? '') !== self::CANONICAL_IS_TO
            || strcmp((string) ($evaluation['max_requested_market_data_date'] ?? ''), self::CANONICAL_IS_TO) > 0) {
            return $this->blocked('C171_REJECTED_STRICT_IS_BOUNDARY_NOT_PROVEN', ['evaluation' => $evaluation]);
        }
        $executionRouteProof = [
            'fixed_eval_model' => (string) ($evaluation['eval_model'] ?? '') === (string) $draft->eval_model,
            'trade_candidates_frozen_before_price_read' => ($evaluation['trade_candidates_frozen_before_price_read'] ?? false) === true,
            'future_price_used_for_evaluation_only' => ($evaluation['future_price_used_for_evaluation_only'] ?? false) === true,
            'strategy_payload_immutable' => ($evaluation['strategy_payload_immutable'] ?? false) === true,
        ];
        $executionRouteProof['pass'] = ! in_array(false, $executionRouteProof, true);
        if (! $executionRouteProof['pass']) {
            return $this->blocked('C171_REJECTED_EXECUTION_TIME_STRATEGY_ROUTE_NOT_PROVEN', [
                'execution_route_proof' => $executionRouteProof,
                'evaluation' => $evaluation,
            ]);
        }
        $tickRiskThreshold = $runtimeParamset['risk']['max_signal_tick_risk_expansion_pct'] ?? null;
        $tickRiskAudit = is_array($evaluation['tick_risk_guard_audit'] ?? null)
            ? $evaluation['tick_risk_guard_audit']
            : null;
        if ($tickRiskThreshold !== null) {
            if ($tickRiskAudit === null
                || ($tickRiskAudit['status'] ?? '') !== 'PASS'
                || ($tickRiskAudit['pass'] ?? false) !== true
                || abs((float) ($tickRiskAudit['threshold'] ?? -1) - (float) $tickRiskThreshold) > 0.0000000001
                || ($tickRiskAudit['tick_risk_metric_propagated_to_scored_candidates'] ?? false) !== true
                || ($tickRiskAudit['tick_risk_metric_propagated_to_official_picks'] ?? false) !== true
                || ($tickRiskAudit['threshold_enforced_for_all_evidence_rows'] ?? false) !== true
                || (int) ($tickRiskAudit['eligible_above_threshold_after_guard_count'] ?? -1) !== 0
                || (int) ($tickRiskAudit['above_threshold_without_tick_reason_count'] ?? -1) !== 0) {
                return $this->blocked('C171_REJECTED_TICK_RISK_GUARD_EXECUTION_OR_EVIDENCE_PROPAGATION_NOT_PROVEN', [
                    'tick_risk_threshold' => $tickRiskThreshold,
                    'tick_risk_guard_audit' => $tickRiskAudit,
                ]);
            }
        }

        $isPass = (bool) ($evaluation['calibration_valid'] ?? false);
        $artifact = [
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'status' => $isPass
                ? 'C171_VERSIONED_OFFICIAL_IS_EVIDENCE_PERSISTED_CANONICAL_GATES_PASSED_OOS_NOT_RUN'
                : 'C171_VERSIONED_OFFICIAL_IS_EVIDENCE_PERSISTED_CANONICAL_GATES_FAILED_OOS_NOT_RUN',
            'reason_code' => $isPass
                ? 'C171_EXECUTABLE_IS_CANDIDATE_READY_FOR_C172_OFFICIAL_OOS_REVIEW'
                : 'C171_NO_EXECUTABLE_IS_CANDIDATE_CANONICAL_GATES_FAILED',
            'approval_reference' => $approvalReference,
            'param_set_id' => $paramSetId,
            'paramset_status' => (string) $draft->status,
            'params_hash' => (string) $draft->params_hash,
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
            'boundary_censored_trade_date_count' => (int) ($evaluation['boundary_censored_trade_date_count'] ?? 0),
            'is_calibration' => $calibration,
            'official_evidence_storage_mode' => 'JSONL_SPOOL',
            'compact_replay_items' => true,
            'official_evidence_manifest' => $evaluation['official_evidence_manifest'] ?? null,
            'official_evidence_persistence_status' => $evaluation['official_evidence_persistence_status'] ?? null,
            'tick_risk_guard_audit' => $tickRiskAudit,
            'canonical_is_gates_pass' => $isPass,
            'executable_is_strategy' => true,
            'execution_route_proof' => $executionRouteProof,
            'future_derived_route_used' => ! $executionRouteProof['pass'],
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_rows_before' => $oosBefore,
            'oos_rows_after' => $oosAfter,
            'oos_mutated' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'recommendation_persisted' => false,
            'confirm_mutated' => false,
            'production_activation_executed' => false,
            'controlled_rollout_executed' => false,
            'official_publication_executed' => false,
            'production_ready' => false,
            'next_recommendation' => $isPass
                ? 'C172_WEEKLY_SWING_OFFICIAL_OOS_EXECUTION_AND_PERSISTENCE'
                : 'C171_TARGETED_EXECUTABLE_IS_STRATEGY_REMEDIATION',
        ];
        $artifact['artifact_hash'] = $this->identity->stableHash($artifact);
        $write = $this->writeArtifact($artifact, $outputPath, (bool) ($options['overwrite'] ?? false));
        $artifact['write'] = $write;

        return $artifact;
    }

    private function clearPreviousSpoolRun(string $directory, string $runKey): void
    {
        if (! is_dir($directory)) {
            return;
        }
        foreach (glob($directory.DIRECTORY_SEPARATOR.$runKey.'.*') ?: [] as $path) {
            if (is_file($path) && ! @unlink($path)) {
                throw new \RuntimeException('C171_PREVIOUS_SPOOL_CLEANUP_FAILED: '.$path);
            }
        }
    }

    private function schemaReadiness(): array
    {
        $requirements = [
            'watchlist_param_sets' => ['params_hash','eval_model','eval_model_hash','implementation_version','implementation_hash'],
            'watchlist_bt_eval' => ['eval_id','paramset_hash','eval_model_hash','implementation_version','implementation_hash','evidence_pipeline_version','evidence_pipeline_hash','picks_hash','universe_hash','cutoffs_hash','evidence_manifest_hash'],
            'watchlist_bt_picks_ws' => ['eval_id','row_hash'],
            'watchlist_bt_universe_ws' => ['eval_id','policy_code','param_id','row_hash'],
            'watchlist_bt_cutoffs_ws' => ['eval_id','row_hash'],
            'watchlist_bt_oos_eval_ws' => ['paramset_hash','eval_model_hash','implementation_hash','is_evidence_manifest_hash'],
        ];
        $missing = [];
        foreach ($requirements as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $missing[] = $table;
                continue;
            }
            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) $missing[] = $table.'.'.$column;
            }
        }
        return ['pass' => $missing === [], 'missing' => $missing];
    }

    private function writeArtifact(array $artifact, string $path, bool $overwrite): array
    {
        if ($path === '') throw new \RuntimeException('C171_OUTPUT_PATH_REQUIRED');
        if (is_file($path) && ! $overwrite) throw new \RuntimeException('C171_OUTPUT_EXISTS_USE_OVERWRITE');
        $directory = dirname($path);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('C171_OUTPUT_DIRECTORY_CREATE_FAILED');
        }
        $json = json_encode($artifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
        $temp = $path.'.tmp.'.getmypid();
        if (file_put_contents($temp, $json, LOCK_EX) === false || ! rename($temp, $path)) {
            @unlink($temp);
            throw new \RuntimeException('C171_ARTIFACT_WRITE_FAILED');
        }
        return ['status' => 'WRITTEN', 'path' => $path, 'file_sha1' => sha1_file($path)];
    }

    private function blocked(string $reasonCode, array $context = []): array
    {
        return array_merge([
            'run_code' => self::RUN_CODE,
            'phase_label' => self::PHASE_LABEL,
            'status' => 'BLOCKED',
            'reason_code' => $reasonCode,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'recommendation_persisted' => false,
            'confirm_mutated' => false,
            'production_activation_executed' => false,
            'controlled_rollout_executed' => false,
            'official_publication_executed' => false,
            'production_ready' => false,
        ], $context);
    }
}
