<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewService;
use Illuminate\Console\Command;

class RunBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c98-weekly-swing-watchlist-non-live-rehearsal-review
        {--c97-artifact=storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json}
        {--expected-c97-hash=5898b6eaa0b537006ba249339c21b5038c8cb6fc}
        {--expected-c97-file-sha1=620FF85234701FD72FC40BB661F068308751C2E4}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c98-weekly-swing-watchlist-non-live-rehearsal-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C98 weekly swing watchlist non-live rehearsal review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewService $service;

    public function __construct(?WatchlistBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC98WeeklySwingWatchlistNonLiveRehearsalReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C98 weekly swing watchlist non-live rehearsal review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c97-artifact'),
            (string) $this->option('expected-c97-hash'),
            (string) $this->option('expected-c97-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_non_live_rehearsal_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_review_pass',
            'weekly_swing_watchlist_non_live_rehearsal_ready',
            'weekly_swing_watchlist_non_live_rehearsal_manifest_created',
            'primary_candidate_weekly_swing_non_live_rehearsal_ready',
            'backup_candidate_weekly_swing_non_live_rehearsal_ready',
            'comparator_candidate_weekly_swing_non_live_rehearsal_ready',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c97_hash', 'actual_c97_hash', 'c97_hash_match', 'expected_c97_file_sha1', 'actual_c97_file_sha1', 'c97_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C98_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C98 weekly swing watchlist non-live rehearsal review completed');
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
