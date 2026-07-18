<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-go-decision-finalization-review
        {--c163-operator-artifact=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-operator-go-no-go-review.json}
        {--expected-c163-operator-hash=8510cda284241de5118bd15aad09c4496529958e}
        {--expected-c163-operator-file-sha1=F09E1066506CD85D3B0675504D5E27D72FA46690}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-go-decision-finalization-review.json}
        {--operator-approved}
        {--go-decision-finalization-confirmed}
        {--post-handoff-activation-finalization-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation GO decision finalization review.';

    private WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffActivationGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c163-operator-artifact'),
            (string) $this->option('expected-c163-operator-hash'),
            (string) $this->option('expected-c163-operator-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
                'post_handoff_activation_finalization_confirmed' => (bool) $this->option('post-handoff-activation-finalization-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_go_decision_finalization_review_pass',
            'operator_decision', 'operator_go_decision', 'operator_go_decision_confirmed',
            'go_decision_finalized', 'go_decision_finalization_confirmed',
            'post_handoff_activation_finalization_confirmed', 'post_handoff_activation_closed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_completion_boundary_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_go_decision_finalization_manifest_created',
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
            'c163_operator_go_no_go_lock_valid', 'c163_operator_go_no_go_review_valid', 'c163_operator_go_no_go_convert_from_json_pass',
            'c163_observation_result_review_lock_valid', 'c163_post_handoff_activation_observation_result_review_valid',
            'c163_observation_result_review_convert_from_json_pass',
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review',
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review',
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_completion_boundary_review',
            'a01_remains_comparator_only',
            'c163_is_same_post_handoff_contract',
            'c163_activation_go_decision_finalization_review_only',
            'c163_controlled_completion_only',
            'c163_not_publication', 'c163_not_unrestricted_publication', 'c163_not_plan_confirm_mutation', 'c163_not_live_plan_confirm_rollout',
            'c163_topic_complete_after_finalization',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c163_operator_go_no_go_hash', 'actual_c163_operator_go_no_go_hash', 'c163_operator_go_no_go_hash_match',
            'expected_c163_operator_go_no_go_file_sha1', 'actual_c163_operator_go_no_go_file_sha1', 'c163_operator_go_no_go_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_post_handoff_activation_completion_boundary_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff activation GO decision finalization review completed');
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
