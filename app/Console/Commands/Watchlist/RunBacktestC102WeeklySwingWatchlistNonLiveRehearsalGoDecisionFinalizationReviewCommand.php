<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review
        {--c101-artifact=storage/app/watchlist/backtest/c101-weekly-swing-watchlist-non-live-rehearsal-operator-go-no-go-review.json}
        {--expected-c101-hash=f8a339760d94d230e184dc6f6b3016731ba72379}
        {--expected-c101-file-sha1=B12CF95D02172659B51B215E567D0B31C6F891F7}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c102-weekly-swing-watchlist-non-live-rehearsal-go-decision-finalization-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C102 weekly swing watchlist non-live rehearsal GO decision finalization review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC102WeeklySwingWatchlistNonLiveRehearsalGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C102 weekly swing watchlist non-live rehearsal GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c101-artifact'),
            (string) $this->option('expected-c101-hash'),
            (string) $this->option('expected-c101-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_review_pass',
            'operator_go_decision', 'operator_go_decision_confirmed',
            'go_decision_finalized', 'go_decision_finalization_confirmed',
            'primary_candidate_weekly_swing_non_live_rehearsal_operator_go',
            'backup_candidate_weekly_swing_non_live_rehearsal_operator_go',
            'comparator_candidate_weekly_swing_non_live_rehearsal_operator_go',
            'primary_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized',
            'backup_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized',
            'comparator_candidate_weekly_swing_non_live_rehearsal_go_decision_finalized',
            'a01_remains_comparator_only',
            'c101_operator_go_no_go_passed',
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
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c101_hash', 'actual_c101_hash', 'c101_hash_match', 'expected_c101_file_sha1', 'actual_c101_file_sha1', 'c101_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C102_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C102 weekly swing watchlist non-live rehearsal GO decision finalization review completed');
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
