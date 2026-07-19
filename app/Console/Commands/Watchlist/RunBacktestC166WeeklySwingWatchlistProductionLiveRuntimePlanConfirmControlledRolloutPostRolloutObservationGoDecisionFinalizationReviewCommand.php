<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review
        {--c166-operator-artifact=storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review.json}
        {--expected-c166-operator-hash=20b00b9c2c53e33eee4f1501e8fddc7c8c379dda}
        {--expected-c166-operator-file-sha1=3158EDB0120527909C12A557C36C2EC28C91B209}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review.json}
        {--operator-approved}
        {--go-decision-finalization-confirmed}
        {--post-rollout-observation-topic-closure-confirmed}
        {--operator-go-locked-confirmed}
        {--post-rollout-observation-result-confirmed}
        {--control-plane-result-confirmed}
        {--market-metrics-not-inferred-confirmed}
        {--candidate-scope-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--production-config-unchanged-confirmed}
        {--free-publication-locked-confirmed}
        {--controlled-rollout-completion-boundary-required-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Finalize the C166 post-rollout observation operator GO decision and close the C166 topic.';

    private WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C166 post-rollout observation GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c166-operator-artifact'),
            (string) $this->option('expected-c166-operator-hash'),
            (string) $this->option('expected-c166-operator-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
                'post_rollout_observation_topic_closure_confirmed' => (bool) $this->option('post-rollout-observation-topic-closure-confirmed'),
                'operator_go_locked_confirmed' => (bool) $this->option('operator-go-locked-confirmed'),
                'post_rollout_observation_result_confirmed' => (bool) $this->option('post-rollout-observation-result-confirmed'),
                'control_plane_result_confirmed' => (bool) $this->option('control-plane-result-confirmed'),
                'market_metrics_not_inferred_confirmed' => (bool) $this->option('market-metrics-not-inferred-confirmed'),
                'candidate_scope_confirmed' => (bool) $this->option('candidate-scope-confirmed'),
                'kill_switch_confirmed' => (bool) $this->option('kill-switch-confirmed'),
                'rollback_confirmed' => (bool) $this->option('rollback-confirmed'),
                'production_config_unchanged_confirmed' => (bool) $this->option('production-config-unchanged-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'controlled_rollout_completion_boundary_required_confirmed' => (bool) $this->option('controlled-rollout-completion-boundary-required-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'actual_c166_operator_hash', 'actual_c166_operator_file_sha1', 'c166_operator_hash_match',
            'c166_operator_file_sha1_match', 'c166_operator_artifact_lock_valid', 'c166_operator_go_valid',
            'go_decision_finalized', 'post_rollout_observation_go_finalized', 'post_rollout_observation_topic_closed',
            'c166_topic_complete', 'c166_topic_complete_after_finalization',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_boundary_review',
            'c167_controlled_rollout_completion_boundary_required_next', 'operator_decision', 'operator_go_decision',
            'operator_decision_confirmed', 'operator_decision_reason', 'post_rollout_observation_result_valid',
            'control_plane_observation_result_stable', 'controlled_rollout_executed', 'controlled_rollout_active',
            'controlled_rollout_only', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_executed', 'new_rollout_executed', 'new_plan_confirm_mutation_executed',
            'new_catalog_read_executed', 'production_config_mutated', 'unrestricted_rollout_allowed',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed', 'market_outcome_metrics_available',
            'price_performance_evaluated', 'recommendation_quality_evaluated', 'market_metrics_inferred_by_finalization',
            'kill_switch_confirmed', 'rollback_confirmed', 'rollout_state_record_count', 'watchlist_function_used',
            'watchlist_function_runtime_mode', 'watchlist_function_invoked_by_finalization',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed',
            'watchlist_function_comparator_candidate_observed', 'primary_candidate_code', 'backup_candidate_code',
            'comparator_candidate_code', 'a01_remains_comparator_only',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_completion_boundary_review',
            'operator_approved', 'approval_reference', 'go_decision_finalization_confirmed',
            'post_rollout_observation_topic_closure_confirmed', 'operator_go_locked_confirmed',
            'post_rollout_observation_result_confirmed', 'control_plane_result_confirmed',
            'market_metrics_not_inferred_confirmed', 'candidate_scope_confirmed', 'finalization_kill_switch_confirmed',
            'finalization_rollback_confirmed', 'production_config_unchanged_confirmed', 'free_publication_locked_confirmed',
            'controlled_rollout_completion_boundary_required_confirmed', 'temporary_negative_artifacts_remaining',
            'temporary_negative_artifact_cleanup_confirmed', 'diagnostic_conclusion', 'next_step_recommendation', 'message',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $value = $result[$key];
                $this->line($key.'='.(is_bool($value) ? ($value ? '1' : '0') : (string) $value));
            }
        }

        return str_contains((string) ($result['status'] ?? ''), '_PASSED_') ? self::SUCCESS : self::FAILURE;
    }
}
