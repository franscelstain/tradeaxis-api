<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review
        {--c105-artifact=storage/app/watchlist/backtest/c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review.json}
        {--expected-c105-hash=dd53320a0cdaaa2d0c19773a331baa3cae6e29eb}
        {--expected-c105-file-sha1=E2DA749D416094BCE061A38CD6A24C9E34F753CA}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c106-weekly-swing-watchlist-non-live-rehearsal-handoff-completion-boundary-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C106 weekly swing watchlist non-live rehearsal handoff completion boundary review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC106WeeklySwingWatchlistNonLiveRehearsalHandoffCompletionBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C106 weekly swing watchlist non-live rehearsal handoff completion boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c105-artifact'),
            (string) $this->option('expected-c105-hash'),
            (string) $this->option('expected-c105-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_review_pass',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_cleared',
            'handoff_completion_boundary_cleared',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalized',
            'handoff_finalized',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready',
            'handoff_ready',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared',
            'completion_boundary_cleared',
            'boundary_go_decision',
            'operator_go_decision',
            'go_decision_finalized',
            'c105_handoff_finalized',
            'c104_handoff_ready',
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared',
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared',
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_completion_boundary_cleared',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active',
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_persisted_to_live_runtime',
            'handoff_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_context_persisted_to_live_runtime',
            'handoff_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_persisted_to_live_runtime',
            'handoff_completion_boundary_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c105_hash', 'actual_c105_hash', 'c105_hash_match', 'expected_c105_file_sha1', 'actual_c105_file_sha1', 'c105_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C106_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C106 weekly swing watchlist non-live rehearsal handoff completion boundary review completed');
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
