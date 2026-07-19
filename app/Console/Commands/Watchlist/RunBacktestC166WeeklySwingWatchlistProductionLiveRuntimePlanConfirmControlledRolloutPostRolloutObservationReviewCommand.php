<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewService;
use Illuminate\Console\Command;

class RunBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review
        {--c165-finalization-artifact=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review.json}
        {--expected-c165-finalization-hash=618a09a64ba295aee023edc8131452782e184a9f}
        {--expected-c165-finalization-file-sha1=8EBDA0F4267597ED04F7AB798A1B1A227ACE4B9A}
        {--rollout-state=storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json}
        {--expected-rollout-state-hash=3a8350955f6a1396f5225af3fddcfa31fa622904}
        {--expected-rollout-state-file-sha1=4B58D3A17B56136CF02BE1635FB2F16F12831722}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review.json}
        {--operator-approved}
        {--post-rollout-observation-confirmed}
        {--controlled-rollout-state-observation-confirmed}
        {--observation-window-confirmed}
        {--candidate-scope-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--production-config-unchanged-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C166 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout post-rollout observation review.';

    private WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewService $service;

    public function __construct(?WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C166 controlled rollout post-rollout observation review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c165-finalization-artifact'),
            (string) $this->option('expected-c165-finalization-hash'),
            (string) $this->option('expected-c165-finalization-file-sha1'),
            (string) $this->option('rollout-state'),
            (string) $this->option('expected-rollout-state-hash'),
            (string) $this->option('expected-rollout-state-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'post_rollout_observation_confirmed' => (bool) $this->option('post-rollout-observation-confirmed'),
                'controlled_rollout_state_observation_confirmed' => (bool) $this->option('controlled-rollout-state-observation-confirmed'),
                'observation_window_confirmed' => (bool) $this->option('observation-window-confirmed'),
                'candidate_scope_confirmed' => (bool) $this->option('candidate-scope-confirmed'),
                'kill_switch_confirmed' => (bool) $this->option('kill-switch-confirmed'),
                'rollback_confirmed' => (bool) $this->option('rollback-confirmed'),
                'production_config_unchanged_confirmed' => (bool) $this->option('production-config-unchanged-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_rollout_observation_started', 'post_rollout_control_plane_snapshot_captured', 'controlled_rollout_observed',
            'controlled_rollout_observation_stable', 'market_outcome_metrics_available', 'price_performance_evaluated',
            'recommendation_quality_evaluated', 'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_result_review',
            'c165_finalization_lock_valid', 'c165_finalization_observation_ready', 'rollout_state_lock_valid',
            'rollout_state_observation_valid', 'finalization_rollout_state_scope_valid', 'all_required_source_locks_valid',
            'controlled_rollout_executed', 'controlled_rollout_active', 'controlled_rollout_only',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed',
            'new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed',
            'production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed',
            'kill_switch_confirmed', 'rollback_confirmed', 'rollout_state_record_count',
            'watchlist_function_used', 'watchlist_function_runtime_mode', 'watchlist_function_invoked_by_observation_review',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed', 'watchlist_function_comparator_candidate_observed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_result_review',
            'operator_approved', 'approval_reference', 'post_rollout_observation_confirmed',
            'controlled_rollout_state_observation_confirmed', 'observation_window_confirmed', 'candidate_scope_confirmed',
            'observation_kill_switch_confirmed', 'observation_rollback_confirmed', 'production_config_unchanged_confirmed',
            'free_publication_locked_confirmed', 'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed',
            'actual_c165_finalization_hash', 'actual_c165_finalization_file_sha1', 'actual_rollout_state_hash', 'actual_rollout_state_file_sha1',
            'diagnostic_conclusion', 'next_step_recommendation', 'message',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $value = $result[$key];
                $this->line($key.'='.(is_bool($value) ? ($value ? '1' : '0') : (string) $value));
            }
        }

        return str_contains((string) ($result['status'] ?? ''), '_PASSED_') ? self::SUCCESS : self::FAILURE;
    }
}
