<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-result-review
        {--c165-execution-artifact=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-execution.json}
        {--expected-c165-execution-hash=73dc9758d1baad52e7a8e56f6e0058e99b9f71f7}
        {--expected-c165-execution-file-sha1=10B76E055119D1A9049F2D9EBA858E1B71A552BE}
        {--rollout-state=storage/app/watchlist/runtime/c165-weekly-swing-watchlist-plan-confirm-controlled-rollout-state.json}
        {--expected-rollout-state-hash=3a8350955f6a1396f5225af3fddcfa31fa622904}
        {--expected-rollout-state-file-sha1=4B58D3A17B56136CF02BE1635FB2F16F12831722}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-result-review.json}
        {--operator-approved}
        {--result-review-confirmed}
        {--controlled-rollout-execution-result-confirmed}
        {--rollout-state-locked-confirmed}
        {--controlled-rollout-only-confirmed}
        {--candidate-scope-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--production-config-unchanged-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C165 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout result review.';

    private WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewService $service;

    public function __construct(?WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C165 weekly swing watchlist production/live runtime PLAN/CONFIRM controlled rollout result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c165-execution-artifact'),
            (string) $this->option('expected-c165-execution-hash'),
            (string) $this->option('expected-c165-execution-file-sha1'),
            (string) $this->option('rollout-state'),
            (string) $this->option('expected-rollout-state-hash'),
            (string) $this->option('expected-rollout-state-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'result_review_confirmed' => (bool) $this->option('result-review-confirmed'),
                'controlled_rollout_execution_result_confirmed' => (bool) $this->option('controlled-rollout-execution-result-confirmed'),
                'rollout_state_locked_confirmed' => (bool) $this->option('rollout-state-locked-confirmed'),
                'controlled_rollout_only_confirmed' => (bool) $this->option('controlled-rollout-only-confirmed'),
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
            'actual_c165_execution_hash', 'actual_c165_execution_file_sha1', 'c165_execution_hash_match', 'c165_execution_file_sha1_match',
            'actual_rollout_state_hash', 'actual_rollout_state_file_sha1', 'rollout_state_hash_match', 'rollout_state_file_sha1_match',
            'controlled_rollout_result_reviewed', 'controlled_rollout_result_valid', 'rollout_state_result_valid', 'execution_rollout_state_integrity_valid',
            'c165_execution_lock_valid', 'c165_execution_result_valid', 'rollout_state_lock_valid', 'rollout_state_integrity_valid', 'all_required_source_locks_valid',
            'controlled_rollout_executed', 'controlled_rollout_active', 'controlled_rollout_only',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_executed',
            'new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed',
            'production_config_mutated', 'unrestricted_rollout_allowed', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed', 'weekly_swing_watchlist_unrestricted_publication_allowed',
            'kill_switch_confirmed', 'rollback_confirmed', 'rollout_state_record_count',
            'watchlist_function_used', 'watchlist_function_runtime_mode',
            'watchlist_function_invoked_during_execution', 'watchlist_function_invoked_by_result_review',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed', 'watchlist_function_comparator_candidate_observed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_operator_go_no_go_review',
            'weekly_swing_watchlist_plan_confirm_controlled_rollout_result_review_manifest_created',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_operator_go_no_go_review',
            'operator_go_no_go_review_required_next', 'c165_topic_number_retained_for_operator_go_no_go_review',
            'operator_approved', 'approval_reference', 'result_review_confirmed',
            'controlled_rollout_execution_result_confirmed', 'rollout_state_locked_confirmed', 'controlled_rollout_only_confirmed',
            'candidate_scope_confirmed', 'production_config_unchanged_confirmed', 'free_publication_locked_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed',
            'diagnostic_conclusion', 'next_step_recommendation', 'message',
        ] as $key) {
            $value = $result[$key] ?? null;
            $this->line($key.'='.(is_bool($value) ? ($value ? '1' : '0') : (string) $value));
        }

        return str_contains((string) ($result['status'] ?? ''), '_PASSED_') ? self::SUCCESS : self::FAILURE;
    }
}
