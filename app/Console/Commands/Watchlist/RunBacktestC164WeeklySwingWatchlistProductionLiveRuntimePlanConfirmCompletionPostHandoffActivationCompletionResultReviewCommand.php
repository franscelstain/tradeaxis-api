<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-result-review
        {--c164-execution-artifact=storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-execution.json}
        {--expected-c164-execution-hash=78066e88b917b317ba6af5777b0ddc98b04bc29a}
        {--expected-c164-execution-file-sha1=EEBF3B6A4D12203FB1860CFC1E60DF72C057E815}
        {--controlled-completion=storage/app/watchlist/output/c161-weekly-swing-watchlist-controlled-plan-confirm-completion.json}
        {--expected-controlled-completion-hash=e9862d9e7738d0558f107d978f329f97f14b3520}
        {--expected-controlled-completion-file-sha1=AB9FC9F714339B78D68132222AC8C398BE7EE1B3}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-result-review.json}
        {--operator-approved}
        {--result-review-confirmed}
        {--completion-execution-result-confirmed}
        {--controlled-completion-result-confirmed}
        {--controlled-completion-only-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion result review.';

    private WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewService $service;

    public function __construct(?WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC164WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationCompletionResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c164-execution-artifact'),
            (string) $this->option('expected-c164-execution-hash'),
            (string) $this->option('expected-c164-execution-file-sha1'),
            (string) $this->option('controlled-completion'),
            (string) $this->option('expected-controlled-completion-hash'),
            (string) $this->option('expected-controlled-completion-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'result_review_confirmed' => (bool) $this->option('result-review-confirmed'),
                'completion_execution_result_confirmed' => (bool) $this->option('completion-execution-result-confirmed'),
                'controlled_completion_result_confirmed' => (bool) $this->option('controlled-completion-result-confirmed'),
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
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_result_review_pass',
            'post_handoff_activation_completion_result_reviewed',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_result_review_manifest_created',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_review_allowed_next',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_execution_pass',
            'post_handoff_activation_completion_execution_completed',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_execution_manifest_created',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
            'weekly_swing_watchlist_controlled_publication_allowed',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c164_execution_lock_valid', 'c164_completion_execution_valid', 'c164_execution_convert_from_json_pass',
            'controlled_completion_lock_valid', 'controlled_completion_integrity_valid', 'controlled_completion_convert_from_json_pass',
            'watchlist_function_used', 'watchlist_function_runtime_mode', 'watchlist_function_source_artifact',
            'watchlist_function_primary_candidate_observed', 'watchlist_function_backup_candidate_observed', 'watchlist_function_comparator_candidate_observed',
            'operator_approved', 'approval_reference',
            'result_review_confirmed', 'completion_execution_result_confirmed', 'controlled_completion_result_confirmed', 'controlled_completion_only_confirmed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed', 'free_publication_locked_confirmed',
            'primary_candidate_completion_result_reviewed',
            'backup_candidate_completion_result_reviewed',
            'comparator_candidate_completion_result_reviewed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'c164_completion_result_review_only', 'c164_controlled_completion_only',
            'c164_not_plan_confirm_mutation', 'c164_not_live_plan_confirm_rollout', 'c164_not_publication',
            'c164_topic_number_retained_for_operator_go_no_go',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c164_execution_hash', 'actual_c164_execution_hash', 'c164_execution_hash_match',
            'expected_c164_execution_file_sha1', 'actual_c164_execution_file_sha1', 'c164_execution_file_sha1_match',
            'expected_controlled_completion_hash', 'actual_controlled_completion_hash', 'controlled_completion_hash_match',
            'expected_controlled_completion_file_sha1', 'actual_controlled_completion_file_sha1', 'controlled_completion_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_post_handoff_activation_completion_operator_go_no_go_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C164_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_COMPLETION_RESULT_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C164 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation completion result review completed');
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
