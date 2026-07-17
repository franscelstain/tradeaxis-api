<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-boundary-review
        {--c160-finalization-artifact=storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-go-decision-finalization-review.json}
        {--expected-c160-finalization-hash=f6d2ca065099a5f07d7e6f53a3263b7b75293b2c}
        {--expected-c160-finalization-file-sha1=B7F94670FC798F62B129AF76D87C1EAE9813B241}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-boundary-review.json}
        {--operator-approved}
        {--completion-boundary-confirmed}
        {--c160-topic-complete-confirmed}
        {--plan-confirm-closed-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C161 weekly swing watchlist production/live runtime PLAN/CONFIRM completion boundary review.';

    private WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC161WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C161 weekly swing watchlist production/live runtime PLAN/CONFIRM completion boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c160-finalization-artifact'),
            (string) $this->option('expected-c160-finalization-hash'),
            (string) $this->option('expected-c160-finalization-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'completion_boundary_confirmed' => (bool) $this->option('completion-boundary-confirmed'),
                'c160_topic_complete_confirmed' => (bool) $this->option('c160-topic-complete-confirmed'),
                'plan_confirm_closed_confirmed' => (bool) $this->option('plan-confirm-closed-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_boundary_review_pass',
            'production_live_runtime_plan_confirm_completion_boundary_review_pass',
            'completion_boundary_cleared', 'completion_boundary_confirmed',
            'c160_topic_complete_confirmed', 'plan_confirm_closed_confirmed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'boundary_go_decision',
            'operator_decision', 'operator_go_decision', 'operator_go_decision_confirmed',
            'go_decision_finalized', 'plan_confirm_closed', 'c160_topic_complete_after_finalization',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_execution',
            'production_live_runtime_plan_confirm_completion_execution_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_execution_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_boundary_manifest_created',
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
            'c160_finalization_lock_valid', 'c160_go_decision_finalization_valid', 'c160_finalization_convert_from_json_pass',
            'primary_candidate_ready_for_plan_confirm_completion_execution',
            'backup_candidate_ready_for_plan_confirm_completion_execution',
            'comparator_candidate_ready_for_plan_confirm_completion_execution',
            'a01_remains_comparator_only',
            'c161_plan_confirm_completion_boundary_review_only',
            'c161_controlled_plan_confirm_completion_only',
            'c161_not_publication', 'c161_not_unrestricted_publication', 'c161_not_plan_confirm_mutation', 'c161_not_live_plan_confirm_rollout',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c160_finalization_hash', 'actual_c160_finalization_hash', 'c160_finalization_hash_match',
            'expected_c160_finalization_file_sha1', 'actual_c160_finalization_file_sha1', 'c160_finalization_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_execution_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C161_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C161 weekly swing watchlist production/live runtime PLAN/CONFIRM completion boundary review completed');
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
