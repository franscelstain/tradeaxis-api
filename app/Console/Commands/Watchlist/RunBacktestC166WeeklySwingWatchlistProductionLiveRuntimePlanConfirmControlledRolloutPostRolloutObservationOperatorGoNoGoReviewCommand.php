<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review
        {--c166-result-review-artifact=storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-result-review.json}
        {--expected-c166-result-review-hash=1dbd61b08afb2d45918cc66a16c782983cfd6666}
        {--expected-c166-result-review-file-sha1=2555E1C7612C066FBF60342D0235AE399CB23253}
        {--operator-decision=}
        {--decision-reason=}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-operator-go-no-go-review.json}
        {--operator-approved}
        {--operator-decision-confirmed}
        {--result-review-locked-confirmed}
        {--post-rollout-observation-result-confirmed}
        {--control-plane-result-confirmed}
        {--market-metrics-not-inferred-confirmed}
        {--candidate-scope-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--production-config-unchanged-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C166 weekly swing watchlist post-rollout observation operator GO/NO-GO/HOLD review.';

    private WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC166WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutPostRolloutObservationOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C166 post-rollout observation operator GO/NO-GO/HOLD review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c166-result-review-artifact'),
            (string) $this->option('expected-c166-result-review-hash'),
            (string) $this->option('expected-c166-result-review-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'operator_decision_confirmed' => (bool) $this->option('operator-decision-confirmed'),
                'operator_decision' => (string) $this->option('operator-decision'),
                'decision_reason' => (string) $this->option('decision-reason'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'result_review_locked_confirmed' => (bool) $this->option('result-review-locked-confirmed'),
                'post_rollout_observation_result_confirmed' => (bool) $this->option('post-rollout-observation-result-confirmed'),
                'control_plane_result_confirmed' => (bool) $this->option('control-plane-result-confirmed'),
                'market_metrics_not_inferred_confirmed' => (bool) $this->option('market-metrics-not-inferred-confirmed'),
                'candidate_scope_confirmed' => (bool) $this->option('candidate-scope-confirmed'),
                'kill_switch_confirmed' => (bool) $this->option('kill-switch-confirmed'),
                'rollback_confirmed' => (bool) $this->option('rollback-confirmed'),
                'production_config_unchanged_confirmed' => (bool) $this->option('production-config-unchanged-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'actual_c166_result_review_hash', 'actual_c166_result_review_file_sha1', 'c166_result_review_hash_match',
            'c166_result_review_file_sha1_match', 'c166_result_review_lock_valid', 'c166_post_rollout_observation_result_review_valid',
            'operator_go_no_go_review_completed', 'operator_decision_recorded', 'operator_decision',
            'operator_go_decision', 'operator_no_go_decision', 'operator_hold_decision', 'operator_decision_confirmed',
            'operator_decision_reason', 'go_decision_finalized', 'c166_topic_complete',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_post_rollout_observation_go_decision_finalization_review',
            'controlled_rollout_post_rollout_observation_stopped_no_go', 'controlled_rollout_post_rollout_observation_deferred_hold',
            'controlled_rollout_executed', 'controlled_rollout_active', 'controlled_rollout_only',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed',
            'new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed',
            'production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed',
            'market_outcome_metrics_available', 'price_performance_evaluated', 'recommendation_quality_evaluated',
            'market_metrics_inferred_by_operator_review', 'kill_switch_confirmed', 'rollback_confirmed', 'rollout_state_record_count',
            'watchlist_function_used', 'watchlist_function_runtime_mode', 'watchlist_function_invoked_by_operator_review',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed',
            'watchlist_function_comparator_candidate_observed', 'primary_candidate_code', 'backup_candidate_code',
            'comparator_candidate_code', 'a01_remains_comparator_only', 'primary_candidate_ready_for_go_decision_finalization',
            'backup_candidate_ready_for_go_decision_finalization', 'comparator_candidate_ready_for_go_decision_finalization',
            'operator_approved', 'approval_reference', 'result_review_locked_confirmed',
            'post_rollout_observation_result_confirmed', 'control_plane_result_confirmed', 'market_metrics_not_inferred_confirmed',
            'candidate_scope_confirmed', 'operator_kill_switch_confirmed', 'operator_rollback_confirmed',
            'production_config_unchanged_confirmed', 'free_publication_locked_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed',
            'diagnostic_conclusion', 'next_step_recommendation', 'message',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        return $this->isCompletedDecisionStatus((string) ($result['status'] ?? '')) ? self::SUCCESS : self::FAILURE;
    }

    private function isCompletedDecisionStatus(string $status): bool
    {
        foreach ([
            'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO',
            'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO',
            'C166_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_POST_ROLLOUT_OBSERVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD',
        ] as $prefix) {
            if (strpos($status, $prefix) === 0) {
                return true;
            }
        }

        return false;
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
