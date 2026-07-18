<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionService;
use Illuminate\Console\Command;

class RunBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution
        {--c164-boundary-artifact=storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review.json}
        {--expected-c164-boundary-hash=997bb3cc6f5565da92438a2afaca441bb50977b4}
        {--expected-c164-boundary-file-sha1=2EBE74B5E40E53C60456A4110DF41A29B1D3E1A6}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution.json}
        {--operator-approved}
        {--completion-execution-confirmed}
        {--c164-boundary-cleared-confirmed}
        {--post-handoff-activation-completion-boundary-confirmed}
        {--controlled-completion-only-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion execution.';

    private WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionService $service;

    public function __construct(?WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionExecutionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion execution started');
        }

        $result = $this->service->execute(
            (string) $this->option('c164-boundary-artifact'),
            (string) $this->option('expected-c164-boundary-hash'),
            (string) $this->option('expected-c164-boundary-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'completion_execution_confirmed' => (bool) $this->option('completion-execution-confirmed'),
                'c164_boundary_cleared_confirmed' => (bool) $this->option('c164-boundary-cleared-confirmed'),
                'post_handoff_activation_completion_boundary_confirmed' => (bool) $this->option('post-handoff-activation-completion-boundary-confirmed'),
                'controlled_completion_only_confirmed' => (bool) $this->option('controlled-completion-only-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass',
            'post_handoff_activation_completion_execution_completed',
            'completion_execution_confirmed',
            'c164_boundary_cleared_confirmed',
            'post_handoff_activation_completion_boundary_confirmed',
            'controlled_completion_only_confirmed',
            'plan_confirm_unchanged_confirmed',
            'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'operator_decision', 'operator_approved', 'approval_reference',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created',
            'c164_completion_boundary_lock_valid',
            'c164_completion_boundary_review_valid',
            'c164_completion_boundary_convert_from_json_pass',
            'controlled_completion_lock_valid',
            'controlled_completion_path', 'controlled_completion_hash', 'controlled_completion_file_sha1', 'controlled_completion_record_count',
            'watchlist_function_used', 'watchlist_function_runtime_mode', 'watchlist_function_source_artifact',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed', 'watchlist_function_comparator_candidate_observed',
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
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
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review',
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review',
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_result_review',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'c164_is_post_handoff_activation_completion_contract',
            'c164_not_c163_activation_repeat',
            'c164_completion_execution_only',
            'c164_controlled_completion_only',
            'c164_not_publication', 'c164_not_unrestricted_publication', 'c164_not_plan_confirm_mutation', 'c164_not_live_plan_confirm_rollout',
            'c164_topic_number_retained_for_result_review',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c164_completion_boundary_hash', 'actual_c164_completion_boundary_hash', 'c164_completion_boundary_hash_match',
            'expected_c164_completion_boundary_file_sha1', 'actual_c164_completion_boundary_file_sha1', 'c164_completion_boundary_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_post_handoff_activation_completion_result_review_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_EXECUTION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion execution completed');
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
