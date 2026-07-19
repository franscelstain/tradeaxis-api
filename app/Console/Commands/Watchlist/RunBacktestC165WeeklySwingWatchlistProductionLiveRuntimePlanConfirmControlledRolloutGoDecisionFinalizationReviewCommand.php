<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review
        {--c165-operator-artifact=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review.json}
        {--expected-c165-operator-hash=48cd9784bb9df5ceef8b47ca970996398d104f54}
        {--expected-c165-operator-file-sha1=5457B6DDA328EF4FD1B0157E5857968D01965381}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-go-decision-finalization-review.json}
        {--operator-approved}
        {--go-decision-finalization-confirmed}
        {--controlled-rollout-topic-closure-confirmed}
        {--operator-go-locked-confirmed}
        {--controlled-rollout-result-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--production-config-unchanged-confirmed}
        {--free-publication-locked-confirmed}
        {--post-rollout-observation-required-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C165 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout GO decision finalization review.';

    private WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C165 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout GO decision finalization started');
        }

        $result = $this->service->execute(
            (string) $this->option('c165-operator-artifact'),
            (string) $this->option('expected-c165-operator-hash'),
            (string) $this->option('expected-c165-operator-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
                'controlled_rollout_topic_closure_confirmed' => (bool) $this->option('controlled-rollout-topic-closure-confirmed'),
                'operator_go_locked_confirmed' => (bool) $this->option('operator-go-locked-confirmed'),
                'controlled_rollout_result_confirmed' => (bool) $this->option('controlled-rollout-result-confirmed'),
                'kill_switch_confirmed' => (bool) $this->option('kill-switch-confirmed'),
                'rollback_confirmed' => (bool) $this->option('rollback-confirmed'),
                'production_config_unchanged_confirmed' => (bool) $this->option('production-config-unchanged-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'post_rollout_observation_required_confirmed' => (bool) $this->option('post-rollout-observation-required-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'go_decision_finalized', 'controlled_rollout_go_finalized', 'controlled_rollout_topic_closed',
            'c165_topic_complete', 'c165_topic_complete_after_finalization', 'c166_post_rollout_observation_required_next',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_review',
            'c165_operator_artifact_lock_valid', 'c165_operator_go_valid', 'operator_decision', 'operator_go_decision',
            'controlled_rollout_result_valid', 'rollout_state_result_valid', 'execution_rollout_state_integrity_valid',
            'controlled_rollout_executed', 'controlled_rollout_active', 'controlled_rollout_only',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed',
            'new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed',
            'production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed',
            'kill_switch_confirmed', 'rollback_confirmed', 'watchlist_function_used', 'watchlist_function_runtime_mode',
            'watchlist_function_invoked_during_execution', 'watchlist_function_invoked_by_finalization',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed', 'watchlist_function_comparator_candidate_observed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_review',
            'operator_approved', 'approval_reference', 'go_decision_finalization_confirmed',
            'controlled_rollout_topic_closure_confirmed', 'operator_go_locked_confirmed', 'controlled_rollout_result_confirmed',
            'finalization_kill_switch_confirmed', 'finalization_rollback_confirmed',
            'production_config_unchanged_confirmed', 'free_publication_locked_confirmed', 'post_rollout_observation_required_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed',
            'expected_c165_operator_hash', 'actual_c165_operator_hash', 'c165_operator_hash_match',
            'expected_c165_operator_file_sha1', 'actual_c165_operator_file_sha1', 'c165_operator_file_sha1_match',
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
