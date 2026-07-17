<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionService;
use Illuminate\Console\Command;

class RunBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-execution
        {--c161-boundary-artifact=storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-boundary-review.json}
        {--expected-c161-boundary-hash=fe92324430bbad2f9caa74538976a9225a4a2807}
        {--expected-c161-boundary-file-sha1=8BEEA9838E6C22646331A151A38404A7FE2E4CC5}
        {--controlled-completion=storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-execution.json}
        {--operator-approved}
        {--completion-execution-confirmed}
        {--controlled-completion-only-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C161 weekly swing watchlist production/live runtime PLAN/CONFIRM completion execution.';

    private WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionService $service;

    public function __construct(?WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionExecutionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C161 weekly swing watchlist production/live runtime PLAN/CONFIRM completion execution started');
        }

        $result = $this->service->execute(
            (string) $this->option('c161-boundary-artifact'),
            (string) $this->option('expected-c161-boundary-hash'),
            (string) $this->option('expected-c161-boundary-file-sha1'),
            (string) $this->option('output'),
            (string) $this->option('controlled-completion'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'completion_execution_confirmed' => (bool) $this->option('completion-execution-confirmed'),
                'controlled_completion_only_confirmed' => (bool) $this->option('controlled-completion-only-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_completion_path', 'controlled_completion_hash', 'controlled_completion_file_sha1', 'controlled_completion_record_count',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_execution_pass',
            'production_live_runtime_plan_confirm_completion_execution_pass',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_result_review',
            'production_live_runtime_plan_confirm_completion_result_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_result_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
            'completion_execution_confirmed', 'controlled_completion_only_confirmed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'completion_boundary_cleared', 'plan_confirm_closed',
            'weekly_swing_watchlist_plan_confirm_result_reviewed',
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_controlled_only',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_controlled_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c161_boundary_lock_valid', 'c161_completion_boundary_valid', 'c161_boundary_convert_from_json_pass',
            'operator_approved', 'approval_reference',
            'primary_candidate_completion_controlled_executed',
            'backup_candidate_completion_controlled_executed',
            'comparator_candidate_completion_controlled_executed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'c161_plan_confirm_completion_execution_only', 'c161_controlled_completion_only',
            'c161_not_plan_confirm_mutation', 'c161_not_live_plan_confirm_rollout', 'c161_not_publication',
            'c161_topic_number_retained_for_result_review',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c161_boundary_hash', 'actual_c161_boundary_hash', 'c161_boundary_hash_match',
            'expected_c161_boundary_file_sha1', 'actual_c161_boundary_file_sha1', 'c161_boundary_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_result_review_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_EXECUTION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C161 weekly swing watchlist production/live runtime PLAN/CONFIRM completion execution completed');
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
