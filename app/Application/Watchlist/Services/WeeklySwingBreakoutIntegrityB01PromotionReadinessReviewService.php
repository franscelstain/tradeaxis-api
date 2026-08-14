<?php

namespace App\Application\Watchlist\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingBreakoutIntegrityB01PromotionReadinessReviewService
{
    public const RUN_CODE =
        'WS_BREAKOUT_INTEGRITY_B01_OOS_IDENTITY_PROMOTION_READINESS_REVIEW';
    public const APPROVAL_REFERENCE =
        'WS_BREAKOUT_INTEGRITY_B01_OPERATOR_APPROVED_PROMOTION_READINESS_REVIEW_ONLY';
    public const SUCCESS_STATUS =
        'WS_BREAKOUT_INTEGRITY_B01_OOS_IDENTITY_VERIFIED_CANONICAL_PROMOTION_ALLOWED';
    public const EXPECTED_OOS_ARTIFACT_HASH =
        '0be1ef09abfb4ba332dc3f0605af90a5d3a565df';
    public const EXPECTED_OOS_FILE_SHA1 =
        'e6caa3390104b36598e97a5dd4ceaf740edc14fa';
    public const EXPECTED_OOS_ID = 1;

    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier;

    public function __construct(
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier = null
    ) {
        $this->identity = $identity
            ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->bindingVerifier = $bindingVerifier
            ?: new WeeklySwingParamsetBacktestBindingVerifier();
    }

    public function execute(
        string $oosArtifactPath,
        string $approvalReference,
        bool $operatorApproved,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved
            || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_REVIEW_APPROVAL_MISSING'
            );
        }
        $source = $this->loadArtifact(
            $oosArtifactPath,
            self::EXPECTED_OOS_ARTIFACT_HASH,
            self::EXPECTED_OOS_FILE_SHA1
        );
        if (! ($source['pass'] ?? false)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_ARTIFACT_MISMATCH',
                ['source_validation' => $source]
            );
        }
        $oosArtifact = $source['payload'];
        $sourceChecks = [
            'oos_status_pass' =>
                ($oosArtifact['status'] ?? '') ===
                    'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_PASSED_PROMOTION_REVIEW_ALLOWED',
            'oos_reason_pass' =>
                ($oosArtifact['reason_code'] ?? '') ===
                    'WS_BREAKOUT_INTEGRITY_B01_PASSED_ALL_OFFICIAL_OOS_GATES',
            'oos_gates_pass' =>
                ($oosArtifact['official_oos_gates_pass'] ?? false) === true,
            'oos_identity_review_exact' =>
                ($oosArtifact['identity_review_artifact_hash'] ?? '') ===
                    WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService
                        ::EXPECTED_IDENTITY_REVIEW_ARTIFACT_HASH,
            'oos_window_exact' =>
                ($oosArtifact['oos_window']['from'] ?? '') ===
                    WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService
                        ::OOS_FROM
                && ($oosArtifact['oos_window']['to'] ?? '') ===
                    WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService
                        ::OOS_TO,
            'oos_id_exact' =>
                (int) ($oosArtifact['oos_id'] ?? 0)
                    === self::EXPECTED_OOS_ID,
            'route_proof_pass' =>
                ($oosArtifact['execution_route_proof']['pass'] ?? false)
                    === true,
            'boundary_proof_pass' =>
                ($oosArtifact['boundary_proof']['pass'] ?? false) === true,
            'retuning_not_performed' =>
                ($oosArtifact['retuning_performed'] ?? true) === false,
            'oos_not_used_for_selection' =>
                ($oosArtifact['oos_used_for_selection'] ?? true) === false,
            'not_promoted' =>
                ($oosArtifact['paramset_promoted'] ?? true) === false,
            'plan_not_created' =>
                ($oosArtifact['plan_run_created'] ?? true) === false,
        ];
        foreach (($oosArtifact['oos_acceptance']['gates'] ?? [])
            as $gate => $pass) {
            $sourceChecks['gate_'.$gate] = $pass === true;
        }
        if (count($oosArtifact['oos_acceptance']['gates'] ?? []) !== 5
            || in_array(false, $sourceChecks, true)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_SOURCE_NOT_ELIGIBLE',
                ['source_checks' => $sourceChecks]
            );
        }

        $draft = DB::table('watchlist_param_sets')
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
            ->where('oos_id', self::EXPECTED_OOS_ID)
            ->first();
        if (! $draft || ! $isEval || ! $oos) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_DATABASE_IDENTITY_MISSING'
            );
        }
        $payload = json_decode((string) $draft->params_json, true);
        $provenance = json_decode((string) $draft->provenance_json, true);
        $validation = is_array($payload)
            ? $this->validator->validate($payload)
            : ['valid' => false];
        $binding = is_array($provenance)
            && is_array($provenance['bt_binding'] ?? null)
            ? $provenance['bt_binding']
            : [];
        $bindingVerification = $this->bindingVerifier->verify(
            $validation['canonical_payload'] ?? [],
            WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                ::EXPECTED_BT_PARAM_ID,
            WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::CATALOG_CODE
        );
        $metrics = is_array($oosArtifact['oos_metrics'] ?? null)
            ? $oosArtifact['oos_metrics']
            : [];
        $boundariesBefore = $this->boundaryCounts();
        $dbChecks = [
            'draft_still_draft' => (string) $draft->status === 'DRAFT',
            'draft_policy_exact' => (string) $draft->policy_code === 'WS',
            'payload_valid' => ($validation['valid'] ?? false) === true,
            'binding_valid' =>
                ($bindingVerification['valid'] ?? false) === true,
            'binding_param_id_exact' =>
                (int) ($binding['bt_param_id'] ?? 0)
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_BT_PARAM_ID,
            'is_eval_identity_exact' =>
                (int) $isEval->eval_id ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IS_EVAL_ID
                && (int) $isEval->param_id ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_BT_PARAM_ID,
            'oos_is_binding_exact' =>
                (int) $oos->is_eval_id ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IS_EVAL_ID
                && (int) $oos->param_id_best_is ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_BT_PARAM_ID,
            'windows_exact' =>
                (string) $oos->from_date_is ===
                    WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                        ::CANONICAL_IS_FROM
                && (string) $oos->to_date_is ===
                    WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                        ::CANONICAL_IS_TO
                && (string) $oos->from_date_oos ===
                    WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService
                        ::OOS_FROM
                && (string) $oos->to_date_oos ===
                    WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService
                        ::OOS_TO,
            'params_hash_chain_exact' =>
                (string) $draft->params_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAMS_HASH
                && (string) $isEval->paramset_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAMS_HASH
                && (string) $oos->paramset_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAMS_HASH,
            'eval_model_hash_chain_exact' =>
                (string) $draft->eval_model_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVAL_MODEL_HASH
                && (string) $isEval->eval_model_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVAL_MODEL_HASH
                && (string) $oos->eval_model_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVAL_MODEL_HASH,
            'implementation_hash_chain_exact' =>
                (string) $draft->implementation_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IMPLEMENTATION_HASH
                && (string) $isEval->implementation_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IMPLEMENTATION_HASH
                && (string) $oos->implementation_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IMPLEMENTATION_HASH,
            'implementation_version_chain_exact' =>
                (string) $draft->implementation_version ===
                    (string) $isEval->implementation_version
                && (string) $isEval->implementation_version ===
                    (string) $oos->implementation_version,
            'manifest_hash_chain_exact' =>
                (string) $isEval->evidence_manifest_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVIDENCE_MANIFEST_HASH
                && (string) $oos->is_evidence_manifest_hash ===
                    WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVIDENCE_MANIFEST_HASH,
            'oos_metrics_match_persisted' =>
                $this->metricsMatch($metrics, $oos),
            'single_official_oos_row' =>
                $boundariesBefore['official_oos_rows'] === 1,
            'no_active_paramset_before_promotion' =>
                $boundariesBefore['active_paramsets'] === 0,
            'no_plan_before_promotion' =>
                $boundariesBefore['watchlist_plan_runs'] === 0,
        ];
        $boundariesAfter = $this->boundaryCounts();
        $dbChecks['database_boundaries_unchanged'] =
            $boundariesBefore === $boundariesAfter;
        if (in_array(false, $dbChecks, true)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_READINESS_REVIEW_FAILED',
                [
                    'source_checks' => $sourceChecks,
                    'database_checks' => $dbChecks,
                ]
            );
        }

        $artifact = [
            'schema_version' =>
                'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_READINESS_REVIEW_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => self::SUCCESS_STATUS,
            'reason_code' =>
                'WS_BREAKOUT_INTEGRITY_B01_EXACT_IS_OOS_IDENTITY_AND_GATES_VERIFIED',
            'approval_reference' => $approvalReference,
            'oos_artifact_path' => $oosArtifactPath,
            'oos_artifact_hash' => self::EXPECTED_OOS_ARTIFACT_HASH,
            'oos_file_sha1' => self::EXPECTED_OOS_FILE_SHA1,
            'param_set_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_PARAM_SET_ID,
            'bt_param_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_BT_PARAM_ID,
            'is_eval_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_IS_EVAL_ID,
            'oos_id' => self::EXPECTED_OOS_ID,
            'params_hash' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_PARAMS_HASH,
            'eval_model_hash' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_EVAL_MODEL_HASH,
            'implementation_version' =>
                (string) $draft->implementation_version,
            'implementation_hash' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_IMPLEMENTATION_HASH,
            'is_evidence_manifest_hash' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_EVIDENCE_MANIFEST_HASH,
            'oos_metrics' => $metrics,
            'oos_acceptance' => $oosArtifact['oos_acceptance'],
            'source_checks' => $sourceChecks,
            'database_checks' => $dbChecks,
            'database_boundaries_before' => $boundariesBefore,
            'database_boundaries_after' => $boundariesAfter,
            'promotion_readiness_review_pass' => true,
            'canonical_promotion_authorized' => true,
            'promotion_executed' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' =>
                'WS_BREAKOUT_INTEGRITY_B01_EXECUTE_CANONICAL_DRAFT_TO_ACTIVE_PROMOTION',
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
        $sha1 = sha1_file($path);
        $pass = $sha1 === $expectedSha1
            && ($payload['artifact_hash'] ?? '') === $expectedHash
            && $computed === $expectedHash;

        return [
            'pass' => $pass,
            'reason_code' => $pass ? 'ARTIFACT_VALID' : 'ARTIFACT_MISMATCH',
            'computed_artifact_hash' => $computed,
            'file_sha1' => $sha1,
            'payload' => $payload,
        ];
    }

    private function metricsMatch(array $metrics, object $oos): bool
    {
        $mapping = [
            'picks_count' => 'picks_count_oos',
            'days_covered' => 'days_covered_oos',
            'avg_ret_net_top' => 'avg_ret_net_top_oos',
            'win_rate_top' => 'win_rate_top_oos',
            'median_ret_net_top' => 'median_ret_net_top_oos',
            'p25_ret_net_top' => 'p25_ret_net_top_oos',
            'month_win_rate_min' => 'month_win_rate_min_oos',
        ];
        foreach ($mapping as $metric => $column) {
            if (! array_key_exists($metric, $metrics)) {
                return false;
            }
            if (in_array($metric, ['picks_count', 'days_covered'], true)) {
                if ((int) $metrics[$metric] !== (int) $oos->{$column}) {
                    return false;
                }
                continue;
            }
            if (round((float) $metrics[$metric], 6)
                !== round((float) $oos->{$column}, 6)) {
                return false;
            }
        }

        return true;
    }

    private function boundaryCounts(): array
    {
        return [
            'active_paramsets' => DB::table('watchlist_param_sets')
                ->where('policy_code', 'WS')
                ->where('status', 'ACTIVE')
                ->count(),
            'watchlist_plan_runs' => Schema::hasTable('watchlist_plan_runs')
                ? DB::table('watchlist_plan_runs')->count()
                : 0,
            'official_oos_rows' =>
                DB::table('watchlist_bt_oos_eval_ws')->count(),
        ];
    }

    private function writeArtifact(
        array $artifact,
        string $path,
        bool $overwrite
    ): array {
        if ($path === '') {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_REVIEW_OUTPUT_REQUIRED'
            );
        }
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_REVIEW_OUTPUT_EXISTS'
            );
        }
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_REVIEW_DIRECTORY_FAILED'
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
                'WS_BREAKOUT_INTEGRITY_B01_PROMOTION_REVIEW_WRITE_FAILED'
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
            'promotion_readiness_review_pass' => false,
            'canonical_promotion_authorized' => false,
            'promotion_executed' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
