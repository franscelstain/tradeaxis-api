<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review
        {--c166-observation-artifact=storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-review.json}
        {--expected-c166-observation-hash=9ffec96e1a08e927c5ad14445d6e6d038528a7f2}
        {--expected-c166-observation-file-sha1=D9AF66D1488F3BA14134820647E8C1A288C75525}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review.json}
        {--operator-approved}
        {--result-review-confirmed}
        {--post-rollout-observation-result-confirmed}
        {--observation-artifact-locked-confirmed}
        {--control-plane-snapshot-confirmed}
        {--candidate-scope-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--production-config-unchanged-confirmed}
        {--free-publication-locked-confirmed}
        {--market-metrics-not-inferred-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C166 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout post-rollout observation result review.';

    private WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewService $service;

    public function __construct(?WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C166 controlled rollout post-rollout observation result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c166-observation-artifact'),
            (string) $this->option('expected-c166-observation-hash'),
            (string) $this->option('expected-c166-observation-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'result_review_confirmed' => (bool) $this->option('result-review-confirmed'),
                'post_rollout_observation_result_confirmed' => (bool) $this->option('post-rollout-observation-result-confirmed'),
                'observation_artifact_locked_confirmed' => (bool) $this->option('observation-artifact-locked-confirmed'),
                'control_plane_snapshot_confirmed' => (bool) $this->option('control-plane-snapshot-confirmed'),
                'candidate_scope_confirmed' => (bool) $this->option('candidate-scope-confirmed'),
                'kill_switch_confirmed' => (bool) $this->option('kill-switch-confirmed'),
                'rollback_confirmed' => (bool) $this->option('rollback-confirmed'),
                'production_config_unchanged_confirmed' => (bool) $this->option('production-config-unchanged-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'market_metrics_not_inferred_confirmed' => (bool) $this->option('market-metrics-not-inferred-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'actual_c166_observation_hash', 'actual_c166_observation_file_sha1', 'c166_observation_hash_match', 'c166_observation_file_sha1_match',
            'post_rollout_observation_result_reviewed', 'post_rollout_observation_result_valid', 'control_plane_observation_result_stable',
            'c166_observation_lock_valid', 'c166_observation_result_valid', 'all_required_source_locks_valid',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review',
            'operator_go_no_go_review_required_next', 'c166_topic_number_retained_for_operator_go_no_go_review',
            'controlled_rollout_executed', 'controlled_rollout_active', 'controlled_rollout_only',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed',
            'new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed',
            'production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed',
            'market_outcome_metrics_available', 'price_performance_evaluated', 'recommendation_quality_evaluated',
            'market_metrics_inferred_by_result_review', 'kill_switch_confirmed', 'rollback_confirmed', 'rollout_state_record_count',
            'watchlist_function_used', 'watchlist_function_runtime_mode', 'watchlist_function_invoked_by_observation_review',
            'watchlist_function_invoked_by_result_review', 'watchlist_function_primary_candidate_observed',
            'watchlist_function_backup_candidate_observed', 'watchlist_function_comparator_candidate_observed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_post_rollout_observation_operator_go_no_go_review',
            'operator_approved', 'approval_reference', 'result_review_confirmed', 'post_rollout_observation_result_confirmed',
            'observation_artifact_locked_confirmed', 'control_plane_snapshot_confirmed', 'candidate_scope_confirmed',
            'result_review_kill_switch_confirmed', 'result_review_rollback_confirmed', 'production_config_unchanged_confirmed',
            'free_publication_locked_confirmed', 'market_metrics_not_inferred_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed',
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
