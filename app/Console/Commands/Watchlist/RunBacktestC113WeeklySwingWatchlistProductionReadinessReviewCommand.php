<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewService;
use Illuminate\Console\Command;

class RunBacktestC113WeeklySwingWatchlistProductionReadinessReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c113-weekly-swing-watchlist-production-readiness-review
        {--c112-artifact=storage/app/watchlist/backtest/c112-weekly-swing-watchlist-production-phase-approval-review.json : Locked C112 production phase approval review artifact path}
        {--expected-c112-hash=5c6b4bb2cd7751e4b8b838e31f0a6aecdad67e04 : Expected C112 artifact_hash}
        {--expected-c112-file-sha1=9DAE4191A2243A660963BF5D9709B6E79F7E1998 : Expected C112 artifact file SHA1}
        {--approval-reference= : Operator approval reference for C113 review-only production readiness review}
        {--output=storage/app/watchlist/backtest/c113-weekly-swing-watchlist-production-readiness-review.json : Output artifact path}
        {--operator-approved : Confirms operator approved this review-only C113 run}
        {--overwrite : Overwrite output artifact if present}
        {--progress : Print progress lines}';

    protected $description = 'Run C113 / PR-01 weekly swing watchlist production readiness review in review-only, non-live, non-mutating context.';

    public function handle(WatchlistBacktestC113WeeklySwingWatchlistProductionReadinessReviewService $service): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C113 weekly swing watchlist production readiness review started');
        }

        $result = $service->execute(
            (string) $this->option('c112-artifact'),
            (string) $this->option('expected-c112-hash'),
            (string) $this->option('expected-c112-file-sha1'),
            (string) $this->option('output'),
            [
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) ($this->option('approval-reference') ?? ''),
                'overwrite' => (bool) $this->option('overwrite'),
            ]
        );

        foreach ([
            'run_code',
            'phase_label',
            'status',
            'reason_code',
            'artifact_hash',
            'temporary_negative_artifacts_remaining',
            'temporary_negative_artifact_cleanup_confirmed',
            'temporary_negative_artifact_paths',
            'production_ready',
            'production_catalog_runtime_wired',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
            'pilot_runtime_active',
            'shadow_runtime_active',
            'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'weekly_swing_watchlist_production_readiness_context_persisted_to_live_runtime',
            'production_readiness_context_persisted_to_live_runtime',
            'expected_c112_hash',
            'actual_c112_hash',
            'c112_hash_match',
            'expected_c112_file_sha1',
            'actual_c112_file_sha1',
            'c112_file_sha1_match',
            'c111_final_closure_valid',
            'c111_non_live_audit_archive_terminal',
            'c112_not_audit_archive_continuation',
            'c112_does_not_reopen_c111_final_closure',
            'diagnostic_conclusion',
            'next_step_recommendation',
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

        if (strpos((string) ($result['status'] ?? ''), 'C113_WEEKLY_SWING_WATCHLIST_PRODUCTION_READINESS_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C113 weekly swing watchlist production readiness review completed');
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
