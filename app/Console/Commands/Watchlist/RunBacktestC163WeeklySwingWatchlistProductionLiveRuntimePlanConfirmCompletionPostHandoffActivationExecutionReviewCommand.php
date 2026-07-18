<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationExecutionReviewService;
use Illuminate\Console\Command;

class RunBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationExecutionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-execution-review
        {--c163-post-handoff-activation-approval-artifact=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-approval-review.json}
        {--expected-c163-post-handoff-activation-approval-hash=9bcccdf3949205a5ab1a003d3767566cc4a5c004}
        {--expected-c163-post-handoff-activation-approval-file-sha1=A21BFA483E2B5BDDA74A40ACF2B7A51549A9B0CE}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-execution-review.json}
        {--operator-approved}
        {--post-handoff-activation-execution-confirmed}
        {--c163-post-handoff-activation-approval-complete-confirmed}
        {--post-handoff-activation-approval-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation execution review.';

    private WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationExecutionReviewService $service;

    public function __construct(?WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationExecutionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationExecutionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation execution review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c163-post-handoff-activation-approval-artifact'),
            (string) $this->option('expected-c163-post-handoff-activation-approval-hash'),
            (string) $this->option('expected-c163-post-handoff-activation-approval-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'post_handoff_activation_execution_confirmed' => (bool) $this->option('post-handoff-activation-execution-confirmed'),
                'c163_post_handoff_activation_approval_complete_confirmed' => (bool) $this->option('c163-post-handoff-activation-approval-complete-confirmed'),
                'post_handoff_activation_approval_confirmed' => (bool) $this->option('post-handoff-activation-approval-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_pass',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_execution_review_pass',
            'post_handoff_activation_execution_confirmed',
            'post_handoff_activation_executed',
            'controlled_post_handoff_activation_execution_executed',
            'c163_post_handoff_activation_approval_complete_confirmed',
            'post_handoff_activation_approval_confirmed',
            'post_handoff_activation_approval_granted',
            'plan_confirm_unchanged_confirmed',
            'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'c163_post_handoff_activation_approval_lock_valid',
            'c163_plan_confirm_completion_post_handoff_activation_execution_valid',
            'c163_post_handoff_activation_approval_convert_from_json_pass',
            'c163_post_handoff_activation_approval_complete',
            'controlled_completion_lock_valid',
            'controlled_completion_path', 'controlled_completion_hash', 'controlled_completion_file_sha1', 'controlled_completion_record_count',
            'watchlist_function_used', 'watchlist_function_runtime_mode', 'watchlist_function_source_artifact',
            'watchlist_function_primary_candidate_enabled', 'watchlist_function_backup_candidate_enabled', 'watchlist_function_comparator_candidate_enabled',
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
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_review',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_observation_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_review_allowed_next',
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review',
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review',
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_observation_review',
            'a01_remains_comparator_only',
            'c163_is_same_post_handoff_contract',
            'c163_activation_execution_review_only',
            'c163_controlled_completion_only',
            'c163_not_publication', 'c163_not_unrestricted_publication', 'c163_not_plan_confirm_mutation', 'c163_not_live_plan_confirm_rollout',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c163_post_handoff_activation_approval_hash', 'actual_c163_post_handoff_activation_approval_hash', 'c163_post_handoff_activation_approval_hash_match',
            'expected_c163_post_handoff_activation_approval_file_sha1', 'actual_c163_post_handoff_activation_approval_file_sha1', 'c163_post_handoff_activation_approval_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_post_handoff_activation_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_EXECUTION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation execution review completed');
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
