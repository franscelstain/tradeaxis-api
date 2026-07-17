<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffCompletionBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffCompletionBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-completion-boundary-review
        {--c162-handoff-finalization-artifact=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-finalization-review.json}
        {--expected-c162-handoff-finalization-hash=59f78ba6da2c7302246a79e412c27e025ef545c3}
        {--expected-c162-handoff-finalization-file-sha1=E7F8D7441F028E5498D4CC8DCC0E24E25FB47FCB}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-completion-boundary-review.json}
        {--operator-approved}
        {--handoff-completion-boundary-confirmed}
        {--c162-handoff-finalization-complete-confirmed}
        {--handoff-finalized-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff completion boundary review.';

    private WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffCompletionBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffCompletionBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffCompletionBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff completion boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c162-handoff-finalization-artifact'),
            (string) $this->option('expected-c162-handoff-finalization-hash'),
            (string) $this->option('expected-c162-handoff-finalization-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'handoff_completion_boundary_confirmed' => (bool) $this->option('handoff-completion-boundary-confirmed'),
                'c162_handoff_finalization_complete_confirmed' => (bool) $this->option('c162-handoff-finalization-complete-confirmed'),
                'handoff_finalized_confirmed' => (bool) $this->option('handoff-finalized-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_pass',
            'production_live_runtime_plan_confirm_completion_handoff_completion_boundary_review_pass',
            'handoff_ready', 'handoff_finalized', 'handoff_completion_boundary_cleared',
            'handoff_completion_boundary_confirmed', 'handoff_completion_boundary_go_decision',
            'c162_handoff_finalization_complete_confirmed', 'handoff_finalized_confirmed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'c162_handoff_finalization_lock_valid', 'c162_plan_confirm_completion_handoff_finalization_valid', 'c162_handoff_finalization_convert_from_json_pass',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_review',
            'production_live_runtime_plan_confirm_completion_handoff_closure_seal_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_closure_seal_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_completion_boundary_manifest_created',
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
            'controlled_completion_path', 'controlled_completion_hash', 'controlled_completion_file_sha1', 'controlled_completion_record_count',
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
            'primary_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review',
            'backup_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review',
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_closure_seal_review',
            'a01_remains_comparator_only',
            'c162_plan_confirm_completion_handoff_completion_boundary_review_only',
            'c162_controlled_completion_only',
            'c162_not_publication', 'c162_not_unrestricted_publication', 'c162_not_plan_confirm_mutation', 'c162_not_live_plan_confirm_rollout',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c162_handoff_finalization_hash', 'actual_c162_handoff_finalization_hash', 'c162_handoff_finalization_hash_match',
            'expected_c162_handoff_finalization_file_sha1', 'actual_c162_handoff_finalization_file_sha1', 'c162_handoff_finalization_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_handoff_closure_seal_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff completion boundary review completed');
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
