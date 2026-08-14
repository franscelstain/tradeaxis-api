<?php

namespace App\Application\Watchlist\Services;

use App\Application\MarketData\Services\MarketDataTradingCalendarReadService;
use App\Infrastructure\Persistence\Watchlist\WatchlistBacktestOosEvaluationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklySwingBreakoutIntegrityB01OfficialOosEvidenceService
{
    public const RUN_CODE =
        'WS_BREAKOUT_INTEGRITY_B01_SINGLE_LOCKED_OFFICIAL_OOS';
    public const APPROVAL_REFERENCE =
        'WS_BREAKOUT_INTEGRITY_B01_OPERATOR_APPROVED_SINGLE_OFFICIAL_OOS';
    public const OOS_FROM = '2025-05-22';
    public const OOS_TO = '2026-05-29';
    public const EXPECTED_IDENTITY_REVIEW_ARTIFACT_HASH =
        'ca65ca2e25db2929f047f7baec6fc0891d90e7c0';
    public const EXPECTED_IDENTITY_REVIEW_FILE_SHA1 =
        '462c8dd9e1fe21ae624b78fafb0aaea14f8437d0';

    private WeeklySwingBacktestEvidenceIdentityService $identity;
    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier;
    private WeeklySwingParamsetRuntimeAdapter $runtimeAdapter;
    private MarketDataTradingCalendarReadService $calendar;
    private WatchlistBacktestPublishedPriceRuntimeService $runtime;
    private WatchlistBacktestOosEvaluationRepository $oosRepository;

    public function __construct(
        WeeklySwingBacktestEvidenceIdentityService $identity = null,
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier = null,
        WeeklySwingParamsetRuntimeAdapter $runtimeAdapter = null,
        MarketDataTradingCalendarReadService $calendar = null,
        WatchlistBacktestPublishedPriceRuntimeService $runtime = null,
        WatchlistBacktestOosEvaluationRepository $oosRepository = null
    ) {
        $this->identity = $identity
            ?: new WeeklySwingBacktestEvidenceIdentityService();
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->bindingVerifier = $bindingVerifier
            ?: new WeeklySwingParamsetBacktestBindingVerifier();
        $this->runtimeAdapter = $runtimeAdapter
            ?: new WeeklySwingParamsetRuntimeAdapter();
        $this->calendar = $calendar
            ?: new MarketDataTradingCalendarReadService();
        $this->runtime = $runtime
            ?: new WatchlistBacktestPublishedPriceRuntimeService();
        $this->oosRepository = $oosRepository
            ?: new WatchlistBacktestOosEvaluationRepository();
    }

    public function execute(
        string $identityReviewArtifactPath,
        string $fromDate,
        string $toDate,
        string $approvalReference,
        bool $operatorApproved,
        string $outputPath,
        array $options = []
    ): array {
        if (! $operatorApproved
            || $approvalReference !== self::APPROVAL_REFERENCE) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_APPROVAL_MISSING'
            );
        }
        if ($fromDate !== self::OOS_FROM || $toDate !== self::OOS_TO) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_WINDOW_MISMATCH'
            );
        }
        $review = $this->loadIdentityReview($identityReviewArtifactPath);
        if (! ($review['pass'] ?? false)) {
            return $this->blocked(
                (string) ($review['reason_code']
                    ?? 'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_REVIEW_INVALID'),
                ['identity_review_validation' => $review]
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
        if (! $draft || ! $isEval
            || (string) $draft->status !== 'DRAFT'
            || (string) $draft->policy_code !== 'WS') {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_SOURCE_IDENTITY_MISSING'
            );
        }
        $payload = json_decode((string) $draft->params_json, true);
        $provenance = json_decode((string) $draft->provenance_json, true);
        if (! is_array($payload) || ! is_array($provenance)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_DRAFT_INVALID'
            );
        }
        $validation = $this->validator->validate($payload);
        if (! ($validation['valid'] ?? false)
            || (string) ($validation['canonical_hash'] ?? '')
                !== WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_PARAMS_HASH) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_PARAMSET_INVALID'
            );
        }
        $binding = is_array($provenance['bt_binding'] ?? null)
            ? $provenance['bt_binding']
            : [];
        $bindingVerification = $this->bindingVerifier->verify(
            $validation['canonical_payload'],
            WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                ::EXPECTED_BT_PARAM_ID,
            WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog::CATALOG_CODE
        );
        if (! ($bindingVerification['valid'] ?? false)
            || (int) ($binding['bt_param_id'] ?? 0)
                !== WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_BT_PARAM_ID
            || (string) ($binding['catalog_hash'] ?? '')
                !== WatchlistBacktestBreakoutIntegrityB01ParamGridCatalog
                    ::hash()) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_BINDING_INVALID'
            );
        }
        $identityChecks = [
            'draft_params_hash' =>
                (string) $draft->params_hash
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAMS_HASH,
            'is_params_hash' =>
                (string) $isEval->paramset_hash
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_PARAMS_HASH,
            'draft_eval_model_hash' =>
                (string) $draft->eval_model_hash
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVAL_MODEL_HASH,
            'is_eval_model_hash' =>
                (string) $isEval->eval_model_hash
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVAL_MODEL_HASH,
            'draft_implementation_hash' =>
                (string) $draft->implementation_hash
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IMPLEMENTATION_HASH,
            'is_implementation_hash' =>
                (string) $isEval->implementation_hash
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_IMPLEMENTATION_HASH,
            'is_manifest_hash' =>
                (string) $isEval->evidence_manifest_hash
                    === WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                        ::EXPECTED_EVIDENCE_MANIFEST_HASH,
        ];
        if (in_array(false, $identityChecks, true)) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_IDENTITY_DRIFT',
                ['identity_checks' => $identityChecks]
            );
        }

        $runtimeParamset = $this->runtimeAdapter->adapt(
            $validation['canonical_payload']
        );
        $evalModel =
            WatchlistBacktestStrategyService::canonicalEvalModel(
                $runtimeParamset
            );
        $runtimeIdentity = $this->identity->identity(
            $validation['canonical_payload'],
            $evalModel
        );
        if ($evalModel !== (string) $draft->eval_model
            || (string) $runtimeIdentity['eval_model_hash']
                !== (string) $draft->eval_model_hash
            || (string) $runtimeIdentity['implementation_hash']
                !== (string) $draft->implementation_hash) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_RUNTIME_IDENTITY_DRIFT'
            );
        }

        $calendar = $this->calendar->resolveReplayWindow(
            $fromDate,
            $toDate,
            5
        );
        if (! ($calendar['is_ready'] ?? false)
            || ($calendar['trade_dates'][0] ?? null) !== $fromDate
            || ($calendar['trade_dates'][
                count($calendar['trade_dates'] ?? []) - 1
            ] ?? null) !== $toDate) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_CALENDAR_INVALID',
                ['calendar' => $calendar]
            );
        }

        $boundariesBefore = $this->boundaryCounts();
        $oos = $this->runtime->evaluateWindow(
            $fromDate,
            $toDate,
            [
                'paramset' => $runtimeParamset,
                'compact_replay_items' => true,
                'executed_at' => (string) ($options['executed_at']
                    ?? $toDate.'T23:59:59+07:00'),
            ]
        );
        if (! ($oos['is_ready'] ?? false)) {
            return $this->blocked(
                (string) ($oos['reason_code']
                    ?? 'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_RUNTIME_FAILED'),
                ['oos_runtime' => $oos]
            );
        }
        $runtimeExecution = $oos['artifact']['runtime_execution'] ?? [];
        $routeProof = [
            'trade_candidates_frozen_before_price_read' =>
                ($runtimeExecution[
                    'trade_candidates_frozen_before_price_read'
                ] ?? false) === true,
            'future_price_used_for_evaluation_only' =>
                ($runtimeExecution[
                    'future_price_used_for_evaluation_only'
                ] ?? false) === true,
            'strategy_payload_immutable' =>
                ($runtimeExecution['strategy_payload_immutable'] ?? false)
                    === true,
            'selection_oos_not_used' =>
                ($validation['canonical_payload']['research_selection'][
                    'oos_used'
                ] ?? true) === false,
            'execution_oos_not_used_for_routing' =>
                ($validation['canonical_payload']['research_execution'][
                    'oos_used'
                ] ?? true) === false,
            'execution_future_route_not_used' =>
                ($validation['canonical_payload']['research_execution'][
                    'future_derived_route_used'
                ] ?? true) === false,
        ];
        $routeProof['pass'] = ! in_array(false, $routeProof, true);
        if (! $routeProof['pass']) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_ROUTE_PROOF_FAILED',
                ['execution_route_proof' => $routeProof]
            );
        }

        $metrics = $oos['artifact']['metrics']['canonical_eval_metrics'] ?? [];
        $acceptance = $this->acceptance($metrics, $payload);
        if ($acceptance['missing_metrics'] !== []) {
            return $this->blocked(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_METRICS_MISSING',
                ['oos_acceptance' => $acceptance]
            );
        }
        $oosRow = [
            'policy_code' => 'WS',
            'policy_version' => (string) $draft->policy_version,
            'eval_model' => (string) $draft->eval_model,
            'param_id_best_is' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_BT_PARAM_ID,
            'is_eval_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_IS_EVAL_ID,
            'from_date_is' =>
                WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                    ::CANONICAL_IS_FROM,
            'to_date_is' =>
                WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                    ::CANONICAL_IS_TO,
            'from_date_oos' => $fromDate,
            'to_date_oos' => $toDate,
            'days_covered_oos' => (int) ($metrics['days_covered'] ?? 0),
            'picks_count_oos' => (int) ($metrics['picks_count'] ?? 0),
            'avg_ret_net_top_oos' =>
                (float) ($metrics['avg_ret_net_top'] ?? 0),
            'win_rate_top_oos' => (float) ($metrics['win_rate_top'] ?? 0),
            'median_ret_net_top_oos' =>
                (float) ($metrics['median_ret_net_top'] ?? 0),
            'p25_ret_net_top_oos' =>
                (float) ($metrics['p25_ret_net_top'] ?? 0),
            'month_win_rate_min_oos' =>
                (float) ($metrics['month_win_rate_min'] ?? 0),
            'paramset_hash' => (string) $draft->params_hash,
            'eval_model_hash' => (string) $draft->eval_model_hash,
            'implementation_version' =>
                (string) $draft->implementation_version,
            'implementation_hash' =>
                (string) $draft->implementation_hash,
            'is_evidence_manifest_hash' =>
                (string) $isEval->evidence_manifest_hash,
        ];
        $persistence = $this->oosRepository->persist($oosRow);
        $boundariesAfter = $this->boundaryCounts();
        $expectedOosCount = $boundariesBefore['official_oos_rows']
            + (($persistence['status'] ?? '') === 'INSERTED' ? 1 : 0);
        $boundaryProof = [
            'active_paramsets_unchanged' =>
                $boundariesAfter['active_paramsets']
                    === $boundariesBefore['active_paramsets'],
            'plan_runs_unchanged' =>
                $boundariesAfter['watchlist_plan_runs']
                    === $boundariesBefore['watchlist_plan_runs'],
            'only_expected_oos_persistence' =>
                $boundariesAfter['official_oos_rows'] === $expectedOosCount,
        ];
        $boundaryProof['pass'] = ! in_array(false, $boundaryProof, true);
        if (! $boundaryProof['pass']) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_FORBIDDEN_MUTATION_DETECTED'
            );
        }

        $pass = $acceptance['pass'];
        $artifact = [
            'schema_version' => 'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_V1',
            'run_code' => self::RUN_CODE,
            'phase_label' => self::RUN_CODE,
            'status' => $pass
                ? 'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_PASSED_PROMOTION_REVIEW_ALLOWED'
                : 'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_FAILED_STRATEGY_NOT_READY',
            'reason_code' => $pass
                ? 'WS_BREAKOUT_INTEGRITY_B01_PASSED_ALL_OFFICIAL_OOS_GATES'
                : (string) $acceptance['reason_code'],
            'approval_reference' => $approvalReference,
            'identity_review_artifact_path' => $identityReviewArtifactPath,
            'identity_review_artifact_hash' =>
                self::EXPECTED_IDENTITY_REVIEW_ARTIFACT_HASH,
            'identity_review_file_sha1' =>
                self::EXPECTED_IDENTITY_REVIEW_FILE_SHA1,
            'param_set_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_PARAM_SET_ID,
            'bt_param_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_BT_PARAM_ID,
            'is_eval_id' =>
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::EXPECTED_IS_EVAL_ID,
            'oos_id' => (int) $persistence['oos_id'],
            'params_hash' => (string) $draft->params_hash,
            'eval_model' => (string) $draft->eval_model,
            'eval_model_hash' => (string) $draft->eval_model_hash,
            'implementation_version' =>
                (string) $draft->implementation_version,
            'implementation_hash' =>
                (string) $draft->implementation_hash,
            'is_evidence_manifest_hash' =>
                (string) $isEval->evidence_manifest_hash,
            'is_window' => [
                'from' =>
                    WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                        ::CANONICAL_IS_FROM,
                'to' =>
                    WeeklySwingBreakoutIntegrityB01OfficialIsEvidenceService
                        ::CANONICAL_IS_TO,
            ],
            'oos_window' => ['from' => $fromDate, 'to' => $toDate],
            'oos_calendar' => [
                'trade_date_count' =>
                    count($calendar['trade_dates'] ?? []),
                'calendar_hash' => $calendar['calendar_hash'] ?? null,
                'max_requested_market_data_date' =>
                    $runtimeExecution['max_requested_market_data_date'] ?? null,
            ],
            'oos_metrics' => $metrics,
            'oos_acceptance' => $acceptance,
            'oos_persistence' => $persistence,
            'execution_route_proof' => $routeProof,
            'database_boundaries_before' => $boundariesBefore,
            'database_boundaries_after' => $boundariesAfter,
            'boundary_proof' => $boundaryProof,
            'retuning_performed' => false,
            'oos_used_for_selection' => false,
            'oos_runtime_invoked' => true,
            'oos_repository_invoked' => true,
            'oos_table_read' => true,
            'oos_mutated' =>
                ($persistence['status'] ?? '') === 'INSERTED',
            'official_oos_gates_pass' => $pass,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
            'next_recommendation' => $pass
                ? 'WS_BREAKOUT_INTEGRITY_B01_REVIEW_EXACT_OOS_IDENTITY_BEFORE_PROMOTION'
                : 'WS_BREAKOUT_INTEGRITY_B01_CLOSE_FAILED_NO_OOS_RETUNING',
        ];
        $artifact['artifact_hash'] = $this->identity->stableHash($artifact);
        $artifact['write'] = $this->writeArtifact(
            $artifact,
            $outputPath,
            (bool) ($options['overwrite'] ?? false)
        );

        return $artifact;
    }

    private function loadIdentityReview(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            return [
                'pass' => false,
                'reason_code' =>
                    'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_REVIEW_MISSING',
            ];
        }
        $payload = json_decode((string) file_get_contents($path), true);
        if (! is_array($payload)) {
            return [
                'pass' => false,
                'reason_code' =>
                    'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_REVIEW_INVALID',
            ];
        }
        $hashPayload = $payload;
        unset($hashPayload['artifact_hash'], $hashPayload['write']);
        $computed = $this->identity->stableHash($hashPayload);
        $pass = sha1_file($path) === self::EXPECTED_IDENTITY_REVIEW_FILE_SHA1
            && ($payload['artifact_hash'] ?? '')
                === self::EXPECTED_IDENTITY_REVIEW_ARTIFACT_HASH
            && $computed === self::EXPECTED_IDENTITY_REVIEW_ARTIFACT_HASH
            && ($payload['status'] ?? '') ===
                WeeklySwingBreakoutIntegrityB01IsIdentityReviewService
                    ::SUCCESS_STATUS
            && ($payload['identity_review_pass'] ?? false) === true
            && ($payload['single_official_oos_authorized'] ?? false) === true
            && ($payload['authorized_oos_window']['from'] ?? '')
                === self::OOS_FROM
            && ($payload['authorized_oos_window']['to'] ?? '')
                === self::OOS_TO
            && ($payload['oos_table_read'] ?? true) === false;

        return [
            'pass' => $pass,
            'reason_code' => $pass
                ? 'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_REVIEW_VALID'
                : 'WS_BREAKOUT_INTEGRITY_B01_IDENTITY_REVIEW_MISMATCH',
            'computed_artifact_hash' => $computed,
            'file_sha1' => sha1_file($path),
        ];
    }

    private function acceptance(array $metrics, array $payload): array
    {
        $required = [
            'days_covered', 'picks_count', 'avg_ret_net_top', 'win_rate_top',
            'median_ret_net_top', 'p25_ret_net_top', 'month_win_rate_min',
        ];
        $missing = array_values(array_filter(
            $required,
            function (string $field) use ($metrics): bool {
                return ! array_key_exists($field, $metrics)
                    || $metrics[$field] === null;
            }
        ));
        $minTrades =
            (int) ($payload['eval']['min_trades_oos']['value'] ?? 40);
        $gates = [
            'minimum_oos_trades' => $missing === []
                ? (int) $metrics['picks_count'] >= $minTrades
                : null,
            'average_return_positive' => $missing === []
                ? (float) $metrics['avg_ret_net_top'] > 0
                : null,
            'median_return_non_negative' => $missing === []
                ? (float) $metrics['median_ret_net_top'] >= 0
                : null,
            'monthly_win_rate_floor' => $missing === []
                ? (float) $metrics['month_win_rate_min'] >= 0.45
                : null,
            'p25_downside_bound' => $missing === []
                ? (float) $metrics['p25_ret_net_top'] >= -0.03
                : null,
        ];
        $failed = array_keys(array_filter(
            $gates,
            fn ($value): bool => $value !== true
        ));
        $pass = $missing === [] && $failed === [];
        $reasonCodes = [];
        if ($missing !== []) {
            $reasonCodes[] =
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_METRICS_MISSING';
        } else {
            if ($gates['minimum_oos_trades'] === false) {
                $reasonCodes[] = 'WS_BT_OOS_WINDOW_INSUFFICIENT';
            }
            if ($gates['average_return_positive'] === false
                || $gates['median_return_non_negative'] === false) {
                $reasonCodes[] = 'WS_BT_OOS_METRICS_FAIL';
            }
            if ($gates['monthly_win_rate_floor'] === false) {
                $reasonCodes[] = 'WS_BT_OOS_STABILITY_FAIL';
            }
            if ($gates['p25_downside_bound'] === false) {
                $reasonCodes[] = 'WS_BT_OOS_DRAWDOWN_FAIL';
            }
        }

        return [
            'pass' => $pass,
            'reason_code' => $pass
                ? 'WS_BREAKOUT_INTEGRITY_B01_PASSED_ALL_OFFICIAL_OOS_GATES'
                : ($reasonCodes[0] ?? 'WS_BT_OOS_METRICS_FAIL'),
            'reason_codes' => $reasonCodes,
            'failed_gates' => $failed,
            'missing_metrics' => $missing,
            'thresholds' => [
                'min_trades_oos' => $minTrades,
                'avg_ret_net_top_oos_gt' => 0,
                'median_ret_net_top_oos_gte' => 0,
                'month_win_rate_min_oos_gte' => 0.45,
                'p25_ret_net_top_oos_gte' => -0.03,
            ],
            'gates' => $gates,
        ];
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
            'official_oos_rows' => DB::table(
                'watchlist_bt_oos_eval_ws'
            )->count(),
        ];
    }

    private function writeArtifact(
        array $artifact,
        string $path,
        bool $overwrite
    ): array {
        if ($path === '') {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_OUTPUT_PATH_REQUIRED'
            );
        }
        if (is_file($path) && ! $overwrite) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_OUTPUT_EXISTS_USE_OVERWRITE'
            );
        }
        $directory = dirname($path);
        if (! is_dir($directory)
            && ! mkdir($directory, 0775, true)
            && ! is_dir($directory)) {
            throw new \RuntimeException(
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_OUTPUT_DIRECTORY_CREATE_FAILED'
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
                'WS_BREAKOUT_INTEGRITY_B01_OFFICIAL_OOS_OUTPUT_WRITE_FAILED'
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
            'official_oos_gates_pass' => false,
            'paramset_promoted' => false,
            'active_paramset_created' => false,
            'plan_run_created' => false,
            'production_ready' => false,
        ], $context);
    }
}
