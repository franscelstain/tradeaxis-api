<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOfficialEvidenceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
{
    public const RUN_CODE =
        'WS_BREAKOUT_INTEGRITY_B01_IS_WINNER_IDENTITY_REVIEW';
    public const APPROVAL_REFERENCE =
        'WS_BREAKOUT_INTEGRITY_B01_OPERATOR_APPROVED_IS_IDENTITY_REVIEW_ONLY';
    public const SUCCESS_STATUS =
        'WS_BREAKOUT_INTEGRITY_B01_IS_IDENTITY_VERIFIED_SINGLE_OFFICIAL_OOS_ALLOWED';
    public const EXPECTED_SOURCE_ARTIFACT_HASH =
        'adf7ec1ba705a4823f4c8590967ffba08fcbd5d8';
    public const EXPECTED_SOURCE_FILE_SHA1 =
        '9d36e816b5b2ed31c7c3d087954d7cf47b476ef3';
    public const EXPECTED_PARAM_SET_ID = 29;
    public const EXPECTED_BT_PARAM_ID = 181;
    public const EXPECTED_IS_EVAL_ID = 220;
    public const EXPECTED_PARAMS_HASH =
        'ff14df49c1a5b3da997dafbea163a51e008314fd';
    public const EXPECTED_EVAL_MODEL_HASH =
        'd0e6f180b85edda2c3785460cd958581684102f1';
    public const EXPECTED_IMPLEMENTATION_HASH =
        '9f9ac615f2c9506bbebee5fb60a2038aa3a42c25';
    public const EXPECTED_EVIDENCE_PIPELINE_HASH =
        '9e9933b363026623b7ab5629f3281fa680a53a2e';
    public const EXPECTED_EVIDENCE_MANIFEST_HASH =
        'e413a21f8951722e113a99cb6c60691d8b289750';

    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier;
    private WatchlistBacktestOfficialEvidenceRepository $officialEvidence;

    public function __construct(
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier = null,
        WatchlistBacktestOfficialEvidenceRepository $officialEvidence = null
    ) {
        $this->identity = $identity
            ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->bindingVerifier = $bindingVerifier
            ?: new WeeklySwingParamsetBacktestBindingVerifier();
        $this->officialEvidence = $officialEvidence
            ?: new WatchlistBacktestOfficialEvidenceRepository();
    }

    public function execute(
        string $sourceArtifactPath,
        string $approvalReference,
        bool $operatorApproved,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved
            || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_REVIEW_APPROVAL_MISSING'
            );
        }
        if (! is_file($sourceArtifactPath)
            || ! is_readable($sourceArtifactPath)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_IS_ARTIFACT_MISSING'
            );
        }
        $sourceJson = file_get_contents($sourceArtifactPath);
        $source = json_decode((string) $sourceJson, true);
        if (! is_array($source)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_IS_ARTIFACT_INVALID'
            );
        }
        $sourceFileSha1 = sha1_file($sourceArtifactPath);
        $hashPayload = $source;
        unset($hashPayload['artifact_hash'], $hashPayload['write']);
        $computedSourceArtifactHash = $this->identity->stableHash($hashPayload);
        if ($sourceFileSha1 !== self::EXPECTED_SOURCE_FILE_SHA1
            || (string) ($source['artifact_hash'] ?? '')
                !== self::EXPECTED_SOURCE_ARTIFACT_HASH
            || $computedSourceArtifactHash
                !== self::EXPECTED_SOURCE_ARTIFACT_HASH) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_IS_ARTIFACT_IDENTITY_MISMATCH',
                [
                    'source_file_sha1' => $sourceFileSha1,
                    'computed_source_artifact_hash' =>
                        $computedSourceArtifactHash,
                ]
            );
        }

        $evaluation = $source['is_calibration']['evaluations'][0] ?? null;
        if (! is_array($evaluation)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_IS_EVALUATION_MISSING'
            );
        }
        $sourceChecks = [
            'official_is_status_pass' =>
                ($source['status'] ?? '') ===
                    'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_IS_PASSED_OOS_IDENTITY_REVIEW_ALLOWED',
            'official_is_reason_pass' =>
                ($source['reason_code'] ?? '') ===
                    'WS_BREAKOUT_INTEGRITY_B01_PASSED_ALL_CANONICAL_IS_GATES',
            'separate_new_strategy_scope' =>
                ($source['separate_new_strategy_scope'] ?? false) === true,
            'p01_not_reopened' => ($source['p01_reopened'] ?? true) === false,
            'param_set_id_exact' =>
                (int) ($source['param_set_id'] ?? 0)
                    === self::EXPECTED_PARAM_SET_ID,
            'eval_id_exact' =>
                (int) ($evaluation['eval_id'] ?? 0)
                    === self::EXPECTED_IS_EVAL_ID,
            'canonical_window_exact' =>
                ($source['is_from'] ?? '') ===
                    WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                        ::CANONICAL_IS_FROM
                && ($source['is_to'] ?? '') ===
                    WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                        ::CANONICAL_IS_TO,
            'canonical_gates_pass' =>
                ($source['canonical_is_gates_pass'] ?? false) === true
                && ($evaluation['calibration_valid'] ?? false) === true,
            'route_proof_pass' =>
                ($source['execution_route_proof']['pass'] ?? false) === true,
            'oos_not_invoked' =>
                ($source['oos_runtime_invoked'] ?? true) === false,
            'oos_not_read' => ($source['oos_table_read'] ?? true) === false,
            'not_promoted' =>
                ($source['paramset_promoted'] ?? true) === false,
            'plan_not_created' =>
                ($source['plan_run_created'] ?? true) === false,
        ];
        foreach (($evaluation['gates'] ?? []) as $gate => $pass) {
            $sourceChecks['gate_'.$gate] = $pass === true;
        }
        if (count($evaluation['gates'] ?? []) !== 7
            || in_array(false, $sourceChecks, true)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_IS_SOURCE_NOT_ELIGIBLE',
                ['source_checks' => $sourceChecks]
            );
        }

        $draft = DB::table('watchlist_param_sets')
            ->where('param_set_id', self::EXPECTED_PARAM_SET_ID)
            ->first();
        $isEval = DB::table('watchlist_bt_eval')
            ->where('eval_id', self::EXPECTED_IS_EVAL_ID)
            ->first();
        if (! $draft || ! $isEval) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_IS_DATABASE_IDENTITY_MISSING'
            );
        }
        $payload = json_decode((string) $draft->params_json, true);
        $provenance = json_decode((string) $draft->provenance_json, true);
        if (! is_array($payload) || ! is_array($provenance)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_DRAFT_PAYLOAD_INVALID'
            );
        }
        $validation = $this->validator->validate($payload);
        $binding = is_array($provenance['bt_binding'] ?? null)
            ? $provenance['bt_binding']
            : [];
        $bindingVerification = $this->bindingVerifier->verify(
            $validation['canonical_payload'] ?? [],
            self::EXPECTED_BT_PARAM_ID,
            WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::CATALOG_CODE
        );

        $expectedManifest = [
            'schema_version' => 'WS_OFFICIAL_IS_EVIDENCE_C171_V1',
            'picks_count' => (int) $isEval->picks_count,
            'picks_hash' => (string) $isEval->picks_hash,
            'universe_count' => (int) $isEval->universe_count,
            'universe_hash' => (string) $isEval->universe_hash,
            'cutoff_count' => (int) $isEval->cutoff_count,
            'cutoffs_hash' => (string) $isEval->cutoffs_hash,
            'market_data_lineage_hash' =>
                (string) $isEval->market_data_lineage_hash,
            'evidence_manifest_hash' =>
                (string) $isEval->evidence_manifest_hash,
        ];
        $boundariesBefore = $this->forbiddenBoundaryCounts();
        $actualManifest = $this->officialEvidence->databaseManifest(
            self::EXPECTED_IS_EVAL_ID
        );
        $boundariesAfter = $this->forbiddenBoundaryCounts();

        $dbChecks = [
            'draft_status_exact' => (string) $draft->status === 'DRAFT',
            'draft_policy_exact' => (string) $draft->policy_code === 'WS',
            'draft_params_hash_exact' =>
                (string) $draft->params_hash === self::EXPECTED_PARAMS_HASH,
            'draft_eval_model_hash_exact' =>
                (string) $draft->eval_model_hash
                    === self::EXPECTED_EVAL_MODEL_HASH,
            'draft_implementation_hash_exact' =>
                (string) $draft->implementation_hash
                    === self::EXPECTED_IMPLEMENTATION_HASH,
            'payload_valid' => ($validation['valid'] ?? false) === true,
            'payload_canonical_hash_exact' =>
                (string) ($validation['canonical_hash'] ?? '')
                    === self::EXPECTED_PARAMS_HASH,
            'binding_valid' =>
                ($bindingVerification['valid'] ?? false) === true,
            'binding_param_id_exact' =>
                (int) ($binding['bt_param_id'] ?? 0)
                    === self::EXPECTED_BT_PARAM_ID,
            'binding_catalog_exact' =>
                ($binding['catalog_code'] ?? '') ===
                    WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                        ::CATALOG_CODE,
            'binding_catalog_hash_exact' =>
                ($binding['catalog_hash'] ?? '') ===
                    WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::hash(),
            'eval_param_id_exact' =>
                (int) $isEval->param_id === self::EXPECTED_BT_PARAM_ID,
            'eval_catalog_exact' =>
                (string) $isEval->catalog_code ===
                    WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                        ::CATALOG_CODE,
            'eval_params_hash_exact' =>
                (string) $isEval->paramset_hash
                    === self::EXPECTED_PARAMS_HASH,
            'eval_model_hash_exact' =>
                (string) $isEval->eval_model_hash
                    === self::EXPECTED_EVAL_MODEL_HASH,
            'eval_implementation_hash_exact' =>
                (string) $isEval->implementation_hash
                    === self::EXPECTED_IMPLEMENTATION_HASH,
            'eval_evidence_pipeline_hash_exact' =>
                (string) $isEval->evidence_pipeline_hash
                    === self::EXPECTED_EVIDENCE_PIPELINE_HASH,
            'eval_evidence_manifest_hash_exact' =>
                (string) $isEval->evidence_manifest_hash
                    === self::EXPECTED_EVIDENCE_MANIFEST_HASH,
            'source_manifest_exact' =>
                ($source['official_evidence_manifest'] ?? null)
                    === $expectedManifest,
            'database_manifest_exact' => $actualManifest === $expectedManifest,
            'metrics_match_persisted_six_decimals' =>
                $this->metricsMatch($evaluation['metrics'] ?? [], $isEval),
            'database_boundaries_unchanged' =>
                $boundariesBefore === $boundariesAfter,
        ];
        if (in_array(false, $dbChecks, true)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_IS_IDENTITY_REVIEW_FAILED',
                [
                    'source_checks' => $sourceChecks,
                    'database_checks' => $dbChecks,
                    'expected_manifest' => $expectedManifest,
                    'actual_manifest' => $actualManifest,
                ]
            );
        }

        $artifact = [
            'schema_version' => 'WS_BREAKOUT_INTEGRITY_B01_IS_IDENTITY_REVIEW_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => self::SUCCESS_STATUS,
            'reason_code' =>
                'WS_BREAKOUT_INTEGRITY_B01_IS_IDENTITY_AND_MANIFEST_VERIFIED',
            'approval_reference' => $approvalReference,
            'source_artifact_path' => $sourceArtifactPath,
            'source_artifact_hash' => self::EXPECTED_SOURCE_ARTIFACT_HASH,
            'source_file_sha1' => self::EXPECTED_SOURCE_FILE_SHA1,
            'param_set_id' => self::EXPECTED_PARAM_SET_ID,
            'bt_param_id' => self::EXPECTED_BT_PARAM_ID,
            'is_eval_id' => self::EXPECTED_IS_EVAL_ID,
            'params_hash' => self::EXPECTED_PARAMS_HASH,
            'eval_model' => (string) $draft->eval_model,
            'eval_model_hash' => self::EXPECTED_EVAL_MODEL_HASH,
            'implementation_version' =>
                (string) $draft->implementation_version,
            'implementation_hash' => self::EXPECTED_IMPLEMENTATION_HASH,
            'evidence_pipeline_hash' =>
                self::EXPECTED_EVIDENCE_PIPELINE_HASH,
            'is_evidence_manifest_hash' =>
                self::EXPECTED_EVIDENCE_MANIFEST_HASH,
            'canonical_is_window' => [
                'from' =>
                    WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                        ::CANONICAL_IS_FROM,
                'to' =>
                    WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                        ::CANONICAL_IS_TO,
            ],
            'canonical_is_metrics' => $evaluation['metrics'],
            'canonical_is_gates' => $evaluation['gates'],
            'source_checks' => $sourceChecks,
            'database_checks' => $dbChecks,
            'official_evidence_manifest' => $actualManifest,
            'database_boundaries_before' => $boundariesBefore,
            'database_boundaries_after' => $boundariesAfter,
            'identity_review_pass' => true,
            'single_official_oos_authorized' => true,
            'authorized_oos_window' => [
                'from' => '2025-05-22',
                'to' => '2026-05-29',
            ],
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_table_read' => false,
            'oos_mutated' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' =>
                'WS_BREAKOUT_INTEGRITY_B01_RUN_SINGLE_LOCKED_OFFICIAL_OOS',
        ];
        $artifact['artifact_hash'] = $this->identity->stableHash($artifact);
        $artifact['write'] = $this->writeArtifact(
            $artifact,
            $outputPath,
            (bool) ($options['overwrite'] ?? false)
        );

        return $artifact;
    }

    private function metricsMatch(array $metrics, object $eval): bool
    {
        $mapping = [
            'picks_count' => 'picks_count',
            'days_covered' => 'days_covered',
            'avg_ret_net_top' => 'avg_ret_net_top',
            'win_rate_top' => 'win_rate_top',
            'median_ret_net_top' => 'median_ret_net_top',
            'p25_ret_net_top' => 'p25_ret_net_top',
            'periods_count' => 'periods_count',
            'period_fail_count' => 'period_fail_count',
            'month_win_rate_min' => 'month_win_rate_min',
            'month_avg_ret_net_min' => 'month_avg_ret_net_min',
        ];
        foreach ($mapping as $metric => $column) {
            if (! array_key_exists($metric, $metrics)) {
                return false;
            }
            if (in_array($metric, [
                'picks_count', 'days_covered', 'periods_count',
                'period_fail_count',
            ], true)) {
                if ((int) $metrics[$metric] !== (int) $eval->{$column}) {
                    return false;
                }
                continue;
            }
            if (round((float) $metrics[$metric], 6)
                !== round((float) $eval->{$column}, 6)) {
                return false;
            }
        }

        return true;
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

    private function writeArtifact(
        array $artifact,
        string $path,
        bool $overwrite
    ): array {
        if ($path === '') {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_OUTPUT_PATH_REQUIRED'
            );
        }
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_OUTPUT_EXISTS_USE_OVERWRITE'
            );
        }
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_OUTPUT_DIRECTORY_CREATE_FAILED'
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
                'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_OUTPUT_WRITE_FAILED'
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
            'identity_review_pass' => false,
            'single_official_oos_authorized' => false,
            'oos_runtime_invoked' => false,
            'oos_repository_invoked' => false,
            'oos_table_read' => false,
            'oos_mutated' => false,
            'paramset_promoted' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
