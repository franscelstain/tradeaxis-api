<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService;
use Illuminate\Console\Command;

class RunBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c99-weekly-swing-watchlist-non-live-rehearsal-execution-review
        {--c98-artifact=storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json}
        {--expected-c98-hash=269eb05141a2acf28925fdef51df9263955b0143}
        {--expected-c98-file-sha1=762BAFFCFCB104E10C9D8C6F6CCBD4E990766702}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c99-weekly-swing-watchlist-non-live-rehearsal-execution-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C99 weekly swing watchlist non-live rehearsal execution review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService $service;

    public function __construct(?WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC99WeeklySwingWatchlistNonLiveRehearsalExecutionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C99 weekly swing watchlist non-live rehearsal execution review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c98-artifact'),
            (string) $this->option('expected-c98-hash'),
            (string) $this->option('expected-c98-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_execution_review_pass',
            'weekly_swing_watchlist_non_live_rehearsal_executed',
            'weekly_swing_watchlist_non_live_rehearsal_execution_manifest_created',
            'primary_candidate_weekly_swing_non_live_rehearsal_executed',
            'backup_candidate_weekly_swing_non_live_rehearsal_executed',
            'comparator_candidate_weekly_swing_non_live_rehearsal_executed',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active',
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c98_hash', 'actual_c98_hash', 'c98_hash_match', 'expected_c98_file_sha1', 'actual_c98_file_sha1', 'c98_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C99_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_EXECUTION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C99 weekly swing watchlist non-live rehearsal execution review completed');
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
