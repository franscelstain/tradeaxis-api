<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review
        {--c163-finalization-artifact=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-go-decision-finalization-review.json}
        {--expected-c163-finalization-hash=e7a4e300eea57aa5f28a87e5cceb297fd92c195a}
        {--expected-c163-finalization-file-sha1=450DC99CAC858CBE08D4E2FB32BC4D9D2F1845B9}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-boundary-review.json}
        {--operator-approved}
        {--completion-boundary-confirmed}
        {--c163-topic-complete-confirmed}
        {--post-handoff-activation-closed-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion boundary review.';

    private WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c163-finalization-artifact'),
            (string) $this->option('expected-c163-finalization-hash'),
            (string) $this->option('expected-c163-finalization-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'completion_boundary_confirmed' => (bool) $this->option('completion-boundary-confirmed'),
                'c163_topic_complete_confirmed' => (bool) $this->option('c163-topic-complete-confirmed'),
                'post_handoff_activation_closed_confirmed' => (bool) $this->option('post-handoff-activation-closed-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_pass',
            'post_handoff_activation_completion_boundary_cleared',
            'completion_boundary_cleared',
            'completion_boundary_confirmed',
            'c163_topic_complete_confirmed',
            'post_handoff_activation_closed_confirmed',
            'plan_confirm_unchanged_confirmed',
            'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'boundary_go_decision',
            'operator_decision',
            'operator_go_decision',
            'operator_go_decision_confirmed',
            'go_decision_finalized',
            'post_handoff_activation_closed',
            'c163_topic_complete_after_finalization',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_manifest_created',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created',
            'c163_go_decision_finalization_lock_valid',
            'c163_post_handoff_activation_go_decision_finalization_valid',
            'c163_go_decision_finalization_convert_from_json_pass',
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
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution',
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution',
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_execution',
            'a01_remains_comparator_only',
            'c164_is_post_handoff_activation_completion_contract',
            'c164_not_c163_activation_repeat',
            'c164_completion_boundary_review_only',
            'c164_controlled_completion_only',
            'c164_not_publication', 'c164_not_unrestricted_publication', 'c164_not_plan_confirm_mutation', 'c164_not_live_plan_confirm_rollout',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c163_go_decision_finalization_hash', 'actual_c163_go_decision_finalization_hash', 'c163_go_decision_finalization_hash_match',
            'expected_c163_go_decision_finalization_file_sha1', 'actual_c163_go_decision_finalization_file_sha1', 'c163_go_decision_finalization_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_post_handoff_activation_completion_execution_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion boundary review completed');
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
