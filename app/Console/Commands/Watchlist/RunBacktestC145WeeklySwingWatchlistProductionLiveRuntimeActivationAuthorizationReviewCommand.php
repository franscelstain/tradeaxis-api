<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC145WeeklySwingWatchlistProductionLiveRuntimeActivationAuthorizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC145WeeklySwingWatchlistProductionLiveRuntimeActivationAuthorizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review
        {--c144-artifact=storage/app/watchlist/backtest/c144-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json}
        {--expected-c144-hash=68d5bb7d096b09d1defa3a655313ff0a7f658e84}
        {--expected-c144-file-sha1=FBC618728E9A8B49A5FBD5CE273EF2159705C816}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c145-weekly-swing-watchlist-production-live-runtime-activation-authorization-review.json}
        {--operator-approved}
        {--activation-authorization-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C145 weekly swing watchlist production/live runtime activation authorization review without activation execution, runtime bridge activation, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC145WeeklySwingWatchlistProductionLiveRuntimeActivationAuthorizationReviewService $service;

    public function __construct(?WatchlistBacktestC145WeeklySwingWatchlistProductionLiveRuntimeActivationAuthorizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC145WeeklySwingWatchlistProductionLiveRuntimeActivationAuthorizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C145 weekly swing watchlist production/live runtime activation authorization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c144-artifact'),
            (string) $this->option('expected-c144-hash'),
            (string) $this->option('expected-c144-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'activation_authorization_confirmed' => (bool) $this->option('activation-authorization-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_authorization_review_pass',
            'production_live_runtime_activation_authorization_review_pass',
            'activation_authorization_confirmed', 'activation_authorized',
            'primary_candidate_activation_authorized', 'backup_candidate_activation_authorized',
            'comparator_candidate_activation_authorized',
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_execution_review',
            'ready_for_production_live_runtime_activation_execution_review',
            'production_live_runtime_activation_authorization_manifest_created',
            'production_live_runtime_activation_execution_review_allowed_next',
            'production_live_runtime_activation_executed',
            'c144_lock_valid', 'c144_pre_activation_boundary_valid', 'c144_convert_from_json_pass',
            'c143_lock_valid', 'c143_go_decision_finalization_valid', 'c143_convert_from_json_pass',
            'c142_lock_valid', 'c142_activation_operator_go_no_go_valid', 'c142_convert_from_json_pass',
            'c141_activation_observation_result_review_valid', 'c140_activation_observation_review_valid',
            'c139_activation_execution_review_valid', 'c138_activation_authorization_valid',
            'c137_pre_activation_boundary_valid', 'c136_go_decision_finalization_valid',
            'c135_activation_operator_go_no_go_valid',
            'c134_activation_observation_result_review_valid', 'c133_activation_observation_review_valid',
            'c132_activation_execution_review_valid', 'c131_activation_approval_valid',
            'c130_activation_readiness_valid', 'c129_final_closure_valid',
            'c138_activation_authorization_review_only', 'c138_not_activation_execution',
            'c138_not_live_runtime_state_change', 'c139_execution_review_only',
            'c139_not_live_runtime_state_change', 'c140_observation_review_only',
            'c140_not_live_runtime_state_change', 'c141_observation_result_review_only',
            'c141_not_live_runtime_state_change', 'c142_operator_go_no_go_review_only',
            'c142_not_live_runtime_state_change', 'c143_go_decision_finalization_review_only',
            'c144_pre_activation_boundary_review_only', 'c144_not_activation_authorization',
            'c145_activation_authorization_review_only', 'c145_not_activation_execution',
            'c145_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_execution_review',
            'backup_candidate_ready_for_production_live_runtime_activation_execution_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_execution_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c144_hash', 'actual_c144_hash', 'c144_hash_match',
            'expected_c144_file_sha1', 'actual_c144_file_sha1', 'c144_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_activation_execution_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C145_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_AUTHORIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C145 weekly swing watchlist production/live runtime activation authorization review completed');
            }

            return 0;
        }

        if (($result['message'] ?? null) !== null) {
            $this->error((string) $result['message']);
        }

        return 1;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $value === null ? '' : (string) $value;
    }
}
