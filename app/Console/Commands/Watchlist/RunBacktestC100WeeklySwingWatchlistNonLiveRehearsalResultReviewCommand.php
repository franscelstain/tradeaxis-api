<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC100WeeklySwingWatchlistNonLiveRehearsalResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC100WeeklySwingWatchlistNonLiveRehearsalResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c100-weekly-swing-watchlist-non-live-rehearsal-result-review
        {--c99-artifact=storage/app/watchlist/backtest/c99-weekly-swing-watchlist-non-live-rehearsal-execution-review.json}
        {--expected-c99-hash=33d63c80f88c00e704b54d923ac511492994d34c}
        {--expected-c99-file-sha1=0C43E16B1C6FB6338343DD12BCA5E04A43BDBB41}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c100-weekly-swing-watchlist-non-live-rehearsal-result-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C100 weekly swing watchlist non-live rehearsal result review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC100WeeklySwingWatchlistNonLiveRehearsalResultReviewService $service;

    public function __construct(?WatchlistBacktestC100WeeklySwingWatchlistNonLiveRehearsalResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC100WeeklySwingWatchlistNonLiveRehearsalResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C100 weekly swing watchlist non-live rehearsal result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c99-artifact'),
            (string) $this->option('expected-c99-hash'),
            (string) $this->option('expected-c99-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_pass',
            'weekly_swing_watchlist_non_live_rehearsal_result_reviewed',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_manifest_created',
            'primary_candidate_weekly_swing_non_live_rehearsal_result_reviewed',
            'backup_candidate_weekly_swing_non_live_rehearsal_result_reviewed',
            'comparator_candidate_weekly_swing_non_live_rehearsal_result_reviewed',
            'a01_remains_comparator_only',
            'c99_non_live_rehearsal_executed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active',
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c99_hash', 'actual_c99_hash', 'c99_hash_match', 'expected_c99_file_sha1', 'actual_c99_file_sha1', 'c99_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C100_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_RESULT_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C100 weekly swing watchlist non-live rehearsal result review completed');
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
