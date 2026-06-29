<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewService;
use Illuminate\Console\Command;

class RunBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review
        {--c103-artifact=storage/app/watchlist/backtest/c103-weekly-swing-watchlist-non-live-rehearsal-completion-boundary-review.json}
        {--expected-c103-hash=60954783fd524694581bd1b4cdb47a71bdcd7bcb}
        {--expected-c103-file-sha1=F61E6BAF148D974CEE483D45164E0D5F6BD51376}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C104 weekly swing watchlist non-live rehearsal handoff readiness review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewService $service;

    public function __construct(?WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC104WeeklySwingWatchlistNonLiveRehearsalHandoffReadinessReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C104 weekly swing watchlist non-live rehearsal handoff readiness review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c103-artifact'),
            (string) $this->option('expected-c103-hash'),
            (string) $this->option('expected-c103-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_review_pass',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready',
            'handoff_ready',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared',
            'completion_boundary_cleared', 'boundary_go_decision',
            'operator_go_decision', 'go_decision_finalized', 'c103_completion_boundary_cleared',
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_ready',
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_ready',
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_ready',
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
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c103_hash', 'actual_c103_hash', 'c103_hash_match', 'expected_c103_file_sha1', 'actual_c103_file_sha1', 'c103_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C104_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_READINESS_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C104 weekly swing watchlist non-live rehearsal handoff readiness review completed');
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
