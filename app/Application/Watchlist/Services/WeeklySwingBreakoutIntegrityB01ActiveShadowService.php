<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingBreakoutIntegrityB01ActiveShadowService
{
    public const RUN_CODE =
        'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_DRY_RUN';
    public const APPROVAL_REFERENCE =
        'WS_BREAKOUT_INTEGRITY_B01_OPERATOR_APPROVED_ACTIVE_SHADOW_DRY_RUN_ONLY';
    public const SUCCESS_STATUS =
        'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_DRY_RUN_PASSED';
    public const SHADOW_TRADE_DATE = '2026-07-28';
    public const EXPECTED_PROMOTION_REVIEW_ARTIFACT_HASH =
        'd71e7287f86bd3fcccf8db0ae01486fbaae0f4d7';
    public const EXPECTED_PROMOTION_REVIEW_FILE_SHA1 =
        '250eea5203154adcf55f06e1adfda587bc74d358';
    public const EXPECTED_PROMOTION_LOG_FILE_SHA1 =
        'd118170b27ce49c95c39d0f28de15351c7e8236a';

    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetRuntimeAdapter $runtimeAdapter;
    private WeeklySwingWatchlistRuntimeService $runtime;

    public function __construct(
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetRuntimeAdapter $runtimeAdapter = null,
        WeeklySwingWatchlistRuntimeService $runtime = null
    ) {
        $this->identity = $identity
            ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->runtimeAdapter = $runtimeAdapter
            ?: new WeeklySwingParamsetRuntimeAdapter();
        $this->runtime = $runtime ?: new WeeklySwingWatchlistRuntimeService();
    }

    public function execute(
        string $tradeDate,
        string $promotionReviewArtifactPath,
        string $promotionLogPath,
        string $approvalReference,
        bool $operatorApproved,
        string $runtimeOutputPath,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved
            || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_APPROVAL_MISSING'
            );
        }
        if ($tradeDate !== self::SHADOW_TRADE_DATE) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_TRADE_DATE_MISMATCH',
                [
                    'expected_trade_date' => self::SHADOW_TRADE_DATE,
                    'actual_trade_date' => $tradeDate,
                ]
            );
        }

        $promotionReview = $this->loadArtifact(
            $promotionReviewArtifactPath,
            self::EXPECTED_PROMOTION_REVIEW_ARTIFACT_HASH,
            self::EXPECTED_PROMOTION_REVIEW_FILE_SHA1
        );
        $promotionLog = $this->loadPromotionLog($promotionLogPath);
        if (! ($promotionReview['pass'] ?? false)
            || ! ($promotionLog['pass'] ?? false)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_PROMOTION_SOURCE_MISMATCH',
                [
                    'promotion_review_validation' => $promotionReview,
                    'promotion_log_validation' => $promotionLog,
                ]
            );
        }

        $reviewArtifact = $promotionReview['payload'];
        $sourceChecks = [
            'promotion_review_pass' =>
                ($reviewArtifact['promotion_readiness_review_pass'] ?? false)
                    === true,
            'canonical_promotion_authorized' =>
                ($reviewArtifact['canonical_promotion_authorized'] ?? false)
                    === true,
            'promotion_review_param_set_exact' =>
                (int) ($reviewArtifact['param_set_id'] ?? 0)
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAM_SET_ID,
            'promotion_review_bt_param_exact' =>
                (int) ($reviewArtifact['bt_param_id'] ?? 0)
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_BT_PARAM_ID,
            'promotion_review_is_eval_exact' =>
                (int) ($reviewArtifact['is_eval_id'] ?? 0)
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IS_EVAL_ID,
            'promotion_review_oos_exact' =>
                (int) ($reviewArtifact['oos_id'] ?? 0)
                    === WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService
                        ::EXPECTED_OOS_ID,
            'promotion_review_params_hash_exact' =>
                (string) ($reviewArtifact['params_hash'] ?? '')
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAMS_HASH,
            'promotion_log_exact' => ($promotionLog['pass'] ?? false) === true,
        ];
        if (in_array(false, $sourceChecks, true)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_SOURCE_NOT_ELIGIBLE',
                ['source_checks' => $sourceChecks]
            );
        }

        $paramset = DB::table('watchlist_param_sets')
            ->where(
                'param_set_id',
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_PARAM_SET_ID
            )
            ->first();
        $isEval = DB::table('watchlist_bt_eval')
            ->where(
                'eval_id',
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_IS_EVAL_ID
            )
            ->first();
        $oos = DB::table('watchlist_bt_oos_eval_ws')
            ->where(
                'oos_id',
                WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService
                    ::EXPECTED_OOS_ID
            )
            ->first();
        if (! $paramset || ! $isEval || ! $oos) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_DATABASE_IDENTITY_MISSING'
            );
        }

        $payload = json_decode((string) $paramset->params_json, true);
        $validation = is_array($payload)
            ? $this->validator->validate($payload)
            : ['valid' => false];
        if (($validation['valid'] ?? false) !== true) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_PARAMSET_INVALID',
                ['validation' => $validation]
            );
        }
        $canonicalPayload = $validation['canonical_payload'];
        $executableParamset = $this->runtimeAdapter->adapt($canonicalPayload);
        $resolvedRuntimeParamset = array_replace_recursive(
            $this->runtime->defaultParamset(),
            $executableParamset
        );
        $canonicalHash = $this->identity->stableHash($canonicalPayload);
        $executableHash = $this->identity->stableHash(
            $resolvedRuntimeParamset
        );
        $boundariesBefore = $this->boundaryCounts();
        $featureFlagsBefore = $this->featureFlags();

        $databaseChecks = [
            'single_active_paramset' =>
                $boundariesBefore['active_paramsets'] === 1,
            'active_paramset_id_exact' =>
                $boundariesBefore['active_param_set_ids'] === [
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAM_SET_ID,
                ],
            'target_is_active' => (string) $paramset->status === 'ACTIVE',
            'canonical_payload_valid' =>
                ($validation['valid'] ?? false) === true,
            'canonical_hash_exact' =>
                $canonicalHash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAMS_HASH
                && (string) $paramset->params_hash === $canonicalHash,
            'eval_model_hash_exact' =>
                (string) $paramset->eval_model_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVAL_MODEL_HASH
                && (string) $isEval->eval_model_hash ===
                    (string) $paramset->eval_model_hash
                && (string) $oos->eval_model_hash ===
                    (string) $paramset->eval_model_hash,
            'implementation_hash_exact' =>
                (string) $paramset->implementation_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IMPLEMENTATION_HASH
                && (string) $isEval->implementation_hash ===
                    (string) $paramset->implementation_hash
                && (string) $oos->implementation_hash ===
                    (string) $paramset->implementation_hash,
            'is_oos_binding_exact' =>
                (int) $isEval->param_id ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_BT_PARAM_ID
                && (int) $oos->is_eval_id === (int) $isEval->eval_id
                && (int) $oos->param_id_best_is === (int) $isEval->param_id,
            'no_plan_before_shadow' =>
                $boundariesBefore['watchlist_plan_runs'] === 0
                && $boundariesBefore['watchlist_plan_items'] === 0,
            'single_official_oos_row' =>
                $boundariesBefore['official_oos_rows'] === 1,
            'all_production_feature_flags_disabled' =>
                ! in_array(true, $featureFlagsBefore, true),
            'b01_rule_exact' =>
                (string) (
                    $resolvedRuntimeParamset['research_selection']['rule_code']
                        ?? ''
                ) === WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                    ::RULE_CODE,
            'b01_breakout_floor_exact' =>
                (float) (
                    $resolvedRuntimeParamset['research_selection']
                        ['thresholds']['min_close_to_hh20_pct'] ?? 0.0
                ) === -0.05,
        ];
        if (in_array(false, $databaseChecks, true)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_PRECONDITION_FAILED',
                [
                    'source_checks' => $sourceChecks,
                    'database_checks' => $databaseChecks,
                    'database_boundaries_before' => $boundariesBefore,
                    'production_feature_flags_before' => $featureFlagsBefore,
                ]
            );
        }

        $runtimeResult = $this->runtime->execute(
            $tradeDate,
            $runtimeOutputPath,
            [
                'overwrite' => (bool) ($options['overwrite_runtime'] ?? false),
                'paramset' => $executableParamset,
                'paramset_source' =>
                    'watchlist_param_sets:'
                    .WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAM_SET_ID.':ACTIVE',
                'capital_input' => [],
            ]
        );
        $boundariesAfter = $this->boundaryCounts();
        $featureFlagsAfter = $this->featureFlags();
        $runtimeFile = $this->validateRuntimeFile(
            $runtimeOutputPath,
            $runtimeResult
        );
        $runtimeChecks = [
            'pipeline_pass' =>
                ($runtimeResult['pipeline_pass'] ?? false) === true,
            'runtime_executed' =>
                ($runtimeResult['real_runtime_integration_executed'] ?? false)
                    === true,
            'real_market_data_consumed' =>
                ($runtimeResult['real_market_data_consumed'] ?? false) === true,
            'controlled_output_generated' =>
                ($runtimeResult['controlled_runtime_output_generated'] ?? false)
                    === true,
            'trade_date_exact' =>
                ($runtimeResult['trade_date'] ?? '') === self::SHADOW_TRADE_DATE
                && ($runtimeResult['trade_date_effective'] ?? '')
                    === self::SHADOW_TRADE_DATE,
            'publication_pointer_resolved' =>
                ($runtimeResult['source_lineage']['pointer_resolve_status']
                    ?? '') === 'RESOLVED_READABLE_CURRENT',
            'active_paramset_source_exact' =>
                ($runtimeResult['paramset_source'] ?? '') ===
                    'watchlist_param_sets:'
                    .WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAM_SET_ID.':ACTIVE',
            'executable_paramset_hash_exact' =>
                ($runtimeResult['paramset_hash'] ?? '') === $executableHash,
            'executable_paramset_snapshot_exact' =>
                ($runtimeResult['paramset_snapshot'] ?? [])
                    === $resolvedRuntimeParamset,
            'b01_runtime_rule_exact' =>
                ($runtimeResult['paramset_snapshot']['research_selection']
                    ['rule_code'] ?? '') ===
                    WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                        ::RULE_CODE,
            'b01_runtime_breakout_floor_exact' =>
                (float) (
                    $runtimeResult['paramset_snapshot']['research_selection']
                        ['thresholds']['min_close_to_hh20_pct'] ?? 0.0
                ) === -0.05,
            'invalid_output_rows_empty' =>
                ($runtimeResult['invalid_output_rows'] ?? []) === [],
            'production_runtime_not_activated' =>
                ($runtimeResult['production_runtime_activated'] ?? true)
                    === false,
            'plan_confirm_mutation_forbidden' =>
                ($runtimeResult['plan_confirm_mutation_allowed'] ?? true)
                    === false
                && ($runtimeResult['plan_confirm_mutated'] ?? true) === false,
            'controlled_rollout_not_executed' =>
                ($runtimeResult['controlled_rollout_allowed'] ?? true) === false
                && ($runtimeResult['controlled_rollout_executed'] ?? true)
                    === false,
            'official_output_not_published' =>
                ($runtimeResult['official_output_published'] ?? true) === false
                && ($runtimeResult['free_publication_allowed'] ?? true) === false,
            'runtime_file_valid' => ($runtimeFile['pass'] ?? false) === true,
            'database_boundaries_unchanged' =>
                $boundariesBefore === $boundariesAfter,
            'production_feature_flags_unchanged' =>
                $featureFlagsBefore === $featureFlagsAfter
                && ! in_array(true, $featureFlagsAfter, true),
        ];
        $pass = ! in_array(false, $runtimeChecks, true);
        $artifact = [
            'schema_version' =>
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_REVIEW_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => $pass
                ? self::SUCCESS_STATUS
                : 'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_DRY_RUN_FAILED',
            'reason_code' => $pass
                ? 'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_PARAMSET_EXECUTED_WITHOUT_PRODUCTION_MUTATION'
                : 'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_RUNTIME_CHECK_FAILED',
            'approval_reference' => $approvalReference,
            'trade_date_selection' => [
                'trade_date' => self::SHADOW_TRADE_DATE,
                'selection_basis' =>
                    'LATEST_EXPLICIT_OPERATOR_VERIFIED_READABLE_DATE_BEFORE_RUN',
                'strategy_return_used_for_date_selection' => false,
                'retuning_performed' => false,
            ],
            'engineering_retry' => [
                'performed' =>
                    trim((string) ($options['engineering_retry_reference']
                        ?? '')) !== '',
                'reference' => trim((string) (
                    $options['engineering_retry_reference'] ?? ''
                )),
                'strategy_identity_changed' => false,
                'trade_date_changed' => false,
                'runtime_threshold_changed' => false,
            ],
            'promotion_review_artifact_path' =>
                $promotionReviewArtifactPath,
            'promotion_review_artifact_hash' =>
                self::EXPECTED_PROMOTION_REVIEW_ARTIFACT_HASH,
            'promotion_review_file_sha1' =>
                self::EXPECTED_PROMOTION_REVIEW_FILE_SHA1,
            'promotion_log_path' => $promotionLogPath,
            'promotion_log_file_sha1' =>
                self::EXPECTED_PROMOTION_LOG_FILE_SHA1,
            'param_set_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_PARAM_SET_ID,
            'paramset_status' => (string) $paramset->status,
            'bt_param_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_BT_PARAM_ID,
            'is_eval_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_IS_EVAL_ID,
            'oos_id' =>
                WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService
                    ::EXPECTED_OOS_ID,
            'canonical_params_hash' => $canonicalHash,
            'executable_runtime_paramset_hash' => $executableHash,
            'source_checks' => $sourceChecks,
            'database_checks' => $databaseChecks,
            'runtime_checks' => $runtimeChecks,
            'runtime_output' => [
                'path' => $runtimeOutputPath,
                'file_sha1' => $runtimeFile['file_sha1'] ?? null,
                'output_hash' => $runtimeResult['output_hash'] ?? null,
                'status' => $runtimeResult['status'] ?? null,
                'reason_code' => $runtimeResult['reason_code'] ?? null,
                'source_lineage' =>
                    $runtimeResult['source_lineage'] ?? [],
                'summary' => $runtimeResult['summary'] ?? [],
                'watchlist_tickers' =>
                    $runtimeResult['watchlist_tickers'] ?? [],
            ],
            'database_boundaries_before' => $boundariesBefore,
            'database_boundaries_after' => $boundariesAfter,
            'production_feature_flags_before' => $featureFlagsBefore,
            'production_feature_flags_after' => $featureFlagsAfter,
            'active_shadow_pass' => $pass,
            'active_paramset_consumed' => $pass,
            'plan_run_created' => false,
            'plan_item_created' => false,
            'confirm_mutated' => false,
            'official_output_published' => false,
            'production_ready' => false,
            'next_recommendation' => $pass
                ? 'WS_BREAKOUT_INTEGRITY_B01_OPERATOR_REVIEW_SHADOW_OUTPUT_BEFORE_ANY_CONTROLLED_PLAN_STAGE'
                : 'WS_BREAKOUT_INTEGRITY_B01_STOP_AND_REVIEW_SHADOW_FAILURE',
        ];
        $artifact['artifact_hash'] = $this->identity->stableHash($artifact);
        $artifact['write'] = $this->writeArtifact(
            $artifact,
            $outputPath,
            (bool) ($options['overwrite'] ?? false)
        );

        return $artifact;
    }

    private function loadArtifact(
        string $path,
        string $expectedHash,
        string $expectedSha1
    ): array {
        if (! is_file($path) || ! is_readable($path)) {
            return ['pass' => false, 'reason_code' => 'ARTIFACT_MISSING'];
        }
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) {
            return ['pass' => false, 'reason_code' => 'ARTIFACT_INVALID'];
        }
        $hashPayload = $payload;
        unset($hashPayload['artifact_hash'], $hashPayload['write']);
        $computed = $this->identity->stableHash($hashPayload);
        $fileSha1 = sha1_file($path);
        $pass = $fileSha1 === $expectedSha1
            && ($payload['artifact_hash'] ?? '') === $expectedHash
            && $computed === $expectedHash;

        return [
            'pass' => $pass,
            'reason_code' => $pass ? 'ARTIFACT_VALID' : 'ARTIFACT_MISMATCH',
            'computed_artifact_hash' => $computed,
            'file_sha1' => $fileSha1,
            'payload' => $payload,
        ];
    }

    private function loadPromotionLog(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return ['pass' => false, 'reason_code' => 'PROMOTION_LOG_MISSING'];
        }
        $raw = str_replace("\r\n", "\n", (string) file_get_contents($path));
        $fileSha1 = sha1_file($path);
        $required = [
            'status=PROMOTED',
            'reason_code=WS_PARAMSET_PROMOTED_ACTIVE',
            'param_set_id=29',
            'bt_param_id=181',
            'oos_id=1',
            'production_ready=0',
        ];
        $pass = $fileSha1 === self::EXPECTED_PROMOTION_LOG_FILE_SHA1;
        foreach ($required as $line) {
            $pass = $pass && in_array($line, explode("\n", trim($raw)), true);
        }

        return [
            'pass' => $pass,
            'reason_code' => $pass
                ? 'PROMOTION_LOG_VALID'
                : 'PROMOTION_LOG_MISMATCH',
            'file_sha1' => $fileSha1,
        ];
    }

    private function validateRuntimeFile(string $path, array $result): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return ['pass' => false, 'reason_code' => 'RUNTIME_FILE_MISSING'];
        }
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) {
            return ['pass' => false, 'reason_code' => 'RUNTIME_FILE_INVALID'];
        }
        $hashPayload = $payload;
        $expectedHash = (string) ($payload['output_hash'] ?? '');
        $hashPayload['output_hash'] = null;
        unset(
            $hashPayload['output_path'],
            $hashPayload['write_skipped_existing_output']
        );
        $computed = $this->identity->stableHash($hashPayload);
        $payloadIdentity = $this->identity->stableHash($payload);
        $resultIdentity = $this->identity->stableHash($result);
        $pass = $payloadIdentity === $resultIdentity
            && $expectedHash !== ''
            && $computed === $expectedHash;

        return [
            'pass' => $pass,
            'reason_code' => $pass
                ? 'RUNTIME_FILE_VALID'
                : 'RUNTIME_FILE_MISMATCH',
            'computed_output_hash' => $computed,
            'payload_identity_hash' => $payloadIdentity,
            'result_identity_hash' => $resultIdentity,
            'file_sha1' => sha1_file($path),
        ];
    }

    private function boundaryCounts(): array
    {
        $activeIds = DB::table('watchlist_param_sets')
            ->where('policy_code', 'WS')
            ->where('status', 'ACTIVE')
            ->orderBy('param_set_id')
            ->pluck('param_set_id')
            ->map(function ($id): int {
                return (int) $id;
            })
            ->all();

        return [
            'paramset_rows' => DB::table('watchlist_param_sets')->count(),
            'active_paramsets' => count($activeIds),
            'active_param_set_ids' => $activeIds,
            'watchlist_plan_runs' => $this->tableCount(
                'watchlist_plan_runs'
            ),
            'watchlist_plan_items' => $this->tableCount(
                'watchlist_plan_items'
            ),
            'watchlist_recommendations' => $this->tableCount(
                'watchlist_recommendations'
            ),
            'watchlist_confirm_checks' => $this->tableCount(
                'watchlist_confirm_checks'
            ),
            'watchlist_confirm_items' => $this->tableCount(
                'watchlist_confirm_items'
            ),
            'watchlist_confirm_snapshots' => $this->tableCount(
                'watchlist_confirm_snapshots'
            ),
            'watchlist_confirm_snapshot_items' => $this->tableCount(
                'watchlist_confirm_snapshot_items'
            ),
            'official_oos_rows' =>
                DB::table('watchlist_bt_oos_eval_ws')->count(),
        ];
    }

    private function tableCount(string $table): int
    {
        return Schema::hasTable($table) ? DB::table($table)->count() : 0;
    }

    private function featureFlags(): array
    {
        $keys = [
            'production_catalog_runtime_bridge_enabled',
            'production_catalog_runtime_bridge_kill_switch',
            'production_catalog_controlled_opt_in_runtime_bridge_enabled',
            'production_catalog_controlled_runtime_opt_in_pilot_enabled',
            'production_catalog_controlled_shadow_rollout_enabled',
            'production_catalog_controlled_parallel_run_enabled',
            'production_catalog_controlled_rollout_enabled',
            'production_catalog_shadow_read_enabled',
            'production_catalog_dry_run_enabled',
        ];
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = config('watchlist.'.$key) === true;
        }

        return $result;
    }

    private function writeArtifact(
        array $artifact,
        string $path,
        bool $overwrite
    ): array {
        if ($path === '') {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_OUTPUT_REQUIRED'
            );
        }
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_OUTPUT_EXISTS'
            );
        }
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_DIRECTORY_FAILED'
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
                'WS_BREAKOUT_INTEGRITY_B01_ACTIVE_SHADOW_WRITE_FAILED'
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
            'active_shadow_pass' => false,
            'active_paramset_consumed' => false,
            'plan_run_created' => false,
            'plan_item_created' => false,
            'confirm_mutated' => false,
            'official_output_published' => false,
            'production_ready' => false,
        ], $context);
    }
}
