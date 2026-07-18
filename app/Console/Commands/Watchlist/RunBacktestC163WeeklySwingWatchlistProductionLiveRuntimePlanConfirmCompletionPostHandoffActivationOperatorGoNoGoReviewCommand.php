<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-operator-go-no-go-review
        {--c163-post-handoff-activation-observation-result-artifact=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-observation-result-review.json}
        {--expected-c163-post-handoff-activation-observation-result-hash=59783060cce101a3c7faa39558ebaef62fcb72c9}
        {--expected-c163-post-handoff-activation-observation-result-file-sha1=F0A2B58E19E72FEBC5CEF9843B59B628EE3CBD64}
        {--approval-reference=}
        {--operator-decision=}
        {--decision-reason=}
        {--output=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-operator-go-no-go-review.json}
        {--operator-approved}
        {--operator-decision-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation operator GO/NO-GO/HOLD review.';

    private WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation operator GO/NO-GO/HOLD review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c163-post-handoff-activation-observation-result-artifact'),
            (string) $this->option('expected-c163-post-handoff-activation-observation-result-hash'),
            (string) $this->option('expected-c163-post-handoff-activation-observation-result-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'operator_decision_confirmed' => (bool) $this->option('operator-decision-confirmed'),
                'operator_decision' => (string) $this->option('operator-decision'),
                'decision_reason' => (string) $this->option('decision-reason'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_pass',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_operator_go_no_go_review_pass',
            'operator_decision_recorded', 'operator_decision', 'operator_go_decision',
            'operator_no_go_decision', 'operator_hold_decision', 'operator_decision_confirmed',
            'operator_decision_reason',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_operator_go_no_go_manifest_created',
            'post_handoff_activation_stopped_no_go',
            'post_handoff_activation_deferred_hold',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_observation_result_reviewed',
            'post_handoff_activation_observation_result_stable',
            'controlled_watchlist_function_observation_result_reviewed',
            'post_handoff_activation_observation_result_confirmed',
            'post_handoff_activation_observed',
            'controlled_watchlist_function_observed',
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
            'c163_observation_result_review_lock_valid',
            'c163_post_handoff_activation_observation_result_review_valid',
            'c163_observation_result_review_convert_from_json_pass',
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code',
            'a01_remains_comparator_only',
            'c163_is_same_post_handoff_contract',
            'c163_activation_operator_go_no_go_review_only',
            'c163_controlled_completion_only',
            'c163_not_publication', 'c163_not_unrestricted_publication', 'c163_not_plan_confirm_mutation', 'c163_not_live_plan_confirm_rollout',
            'operator_approved', 'approval_reference',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c163_post_handoff_activation_observation_result_hash', 'actual_c163_post_handoff_activation_observation_result_hash', 'c163_post_handoff_activation_observation_result_hash_match',
            'expected_c163_post_handoff_activation_observation_result_file_sha1', 'actual_c163_post_handoff_activation_observation_result_file_sha1', 'c163_post_handoff_activation_observation_result_file_sha1_match',
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

        if ($this->isCompletedDecisionStatus((string) ($result['status'] ?? ''))) {
            if ((bool) $this->option('progress')) {
                $this->line('C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation operator GO/NO-GO/HOLD review completed');
            }

            return 0;
        }

        if (($result['message'] ?? null) !== null) {
            $this->error((string) $result['message']);
        }

        return 1;
    }

    private function isCompletedDecisionStatus(string $status): bool
    {
        foreach ([
            'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO',
            'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO',
            'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD',
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
