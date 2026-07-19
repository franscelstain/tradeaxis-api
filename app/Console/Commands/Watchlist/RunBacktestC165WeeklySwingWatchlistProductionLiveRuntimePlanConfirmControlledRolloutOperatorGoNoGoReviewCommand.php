<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review
        {--c165-result-review-artifact=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-result-review.json}
        {--expected-c165-result-review-hash=a30b5b0eeab344e0d0283cb4164fd2a27b234802}
        {--expected-c165-result-review-file-sha1=664A639A2C8338F407BB0B34B9648733A0F6C94E}
        {--approval-reference=}
        {--operator-decision=}
        {--decision-reason=}
        {--output=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-operator-go-no-go-review.json}
        {--operator-approved}
        {--operator-decision-confirmed}
        {--result-review-locked-confirmed}
        {--controlled-rollout-result-confirmed}
        {--controlled-rollout-only-confirmed}
        {--candidate-scope-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--production-config-unchanged-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C165 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout operator GO/NO-GO/HOLD review.';

    private WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C165 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout operator GO/NO-GO/HOLD review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c165-result-review-artifact'),
            (string) $this->option('expected-c165-result-review-hash'),
            (string) $this->option('expected-c165-result-review-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'operator_decision_confirmed' => (bool) $this->option('operator-decision-confirmed'),
                'operator_decision' => (string) $this->option('operator-decision'),
                'decision_reason' => (string) $this->option('decision-reason'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'result_review_locked_confirmed' => (bool) $this->option('result-review-locked-confirmed'),
                'controlled_rollout_result_confirmed' => (bool) $this->option('controlled-rollout-result-confirmed'),
                'controlled_rollout_only_confirmed' => (bool) $this->option('controlled-rollout-only-confirmed'),
                'candidate_scope_confirmed' => (bool) $this->option('candidate-scope-confirmed'),
                'kill_switch_confirmed' => (bool) $this->option('kill-switch-confirmed'),
                'rollback_confirmed' => (bool) $this->option('rollback-confirmed'),
                'production_config_unchanged_confirmed' => (bool) $this->option('production-config-unchanged-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'operator_go_no_go_review_completed', 'operator_decision_recorded', 'operator_decision',
            'operator_go_decision', 'operator_no_go_decision', 'operator_hold_decision', 'operator_decision_confirmed',
            'operator_decision_reason', 'go_decision_finalized',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_go_decision_finalization_review',
            'controlled_rollout_stopped_no_go', 'controlled_rollout_deferred_hold',
            'c165_result_review_lock_valid', 'c165_controlled_rollout_result_review_valid',
            'controlled_rollout_result_valid', 'rollout_state_result_valid', 'execution_rollout_state_integrity_valid',
            'controlled_rollout_executed', 'controlled_rollout_active', 'controlled_rollout_only',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed',
            'new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed',
            'production_config_mutated', 'unrestricted_rollout_allowed',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed', 'kill_switch_confirmed', 'rollback_confirmed',
            'watchlist_function_used', 'watchlist_function_runtime_mode',
            'watchlist_function_invoked_during_execution', 'watchlist_function_invoked_by_operator_review',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed', 'watchlist_function_comparator_candidate_observed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_go_decision_finalization_review',
            'operator_approved', 'approval_reference', 'result_review_locked_confirmed',
            'controlled_rollout_result_confirmed', 'controlled_rollout_only_confirmed', 'candidate_scope_confirmed',
            'operator_kill_switch_confirmed', 'operator_rollback_confirmed',
            'production_config_unchanged_confirmed', 'free_publication_locked_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed',
            'expected_c165_result_review_hash', 'actual_c165_result_review_hash', 'c165_result_review_hash_match',
            'expected_c165_result_review_file_sha1', 'actual_c165_result_review_file_sha1', 'c165_result_review_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation', 'message',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if ($this->isCompletedDecisionStatus((string) ($result['status'] ?? ''))) {
            if ((bool) $this->option('progress')) {
                $this->line('C165 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout operator GO/NO-GO/HOLD review completed');
            }

            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    private function isCompletedDecisionStatus(string $status): bool
    {
        foreach ([
            'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO',
            'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO',
            'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD',
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
