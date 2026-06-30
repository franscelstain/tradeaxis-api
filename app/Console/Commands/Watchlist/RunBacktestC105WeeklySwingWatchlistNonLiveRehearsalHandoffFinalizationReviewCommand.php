<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC105WeeklySwingWatchlistNonLiveRehearsalHandoffFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC105WeeklySwingWatchlistNonLiveRehearsalHandoffFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review
        {--c104-artifact=storage/app/watchlist/backtest/c104-weekly-swing-watchlist-non-live-rehearsal-handoff-readiness-review.json}
        {--expected-c104-hash=9949422cda0ff224c7b441cdd0dd02bfb6c694a4}
        {--expected-c104-file-sha1=08F7A41BDB04E4B40562C855230FDC170E8A2335}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c105-weekly-swing-watchlist-non-live-rehearsal-handoff-finalization-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C105 weekly swing watchlist non-live rehearsal handoff finalization review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC105WeeklySwingWatchlistNonLiveRehearsalHandoffFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC105WeeklySwingWatchlistNonLiveRehearsalHandoffFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC105WeeklySwingWatchlistNonLiveRehearsalHandoffFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C105 weekly swing watchlist non-live rehearsal handoff finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c104-artifact'),
            (string) $this->option('expected-c104-hash'),
            (string) $this->option('expected-c104-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_review_pass',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalized',
            'handoff_finalized',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready',
            'handoff_ready',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_cleared',
            'completion_boundary_cleared', 'boundary_go_decision',
            'operator_go_decision', 'go_decision_finalized', 'c104_handoff_ready',
            'primary_candidate_weekly_swing_non_live_rehearsal_handoff_finalized',
            'backup_candidate_weekly_swing_non_live_rehearsal_handoff_finalized',
            'comparator_candidate_weekly_swing_non_live_rehearsal_handoff_finalized',
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
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c104_hash', 'actual_c104_hash', 'c104_hash_match', 'expected_c104_file_sha1', 'actual_c104_file_sha1', 'c104_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C105_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C105 weekly swing watchlist non-live rehearsal handoff finalization review completed');
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
