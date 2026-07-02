<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewService;
use Illuminate\Console\Command;

class RunBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review
        {--c113-artifact=storage/app/watchlist/backtest/c113-weekly-swing-watchlist-production-readiness-review.json : Locked C113 production readiness review artifact path}
        {--expected-c113-hash=8eb4d4853c6e8618d7506da61d228c4a9c8b722a : Expected C113 artifact_hash}
        {--expected-c113-file-sha1=2D4A23E44CF14024447F6BF749749C3592CFF194 : Expected C113 artifact file SHA1}
        {--approval-reference= : Operator approval reference for C114 review-only runtime wiring readiness review}
        {--output=storage/app/watchlist/backtest/c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review.json : Output artifact path}
        {--operator-approved : Confirms operator approved this review-only C114 run}
        {--overwrite : Overwrite output artifact if present}
        {--progress : Print progress lines}';

    protected $description = 'Run C114 / PR-02 weekly swing watchlist production runtime wiring readiness review in review-only, non-live, non-mutating context.';

    public function handle(WatchlistBacktestC114WeeklySwingWatchlistProductionRuntimeWiringReadinessReviewService $service): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C114 weekly swing watchlist production runtime wiring readiness review started');
        }

        $result = $service->execute(
            (string) $this->option('c113-artifact'),
            (string) $this->option('expected-c113-hash'),
            (string) $this->option('expected-c113-file-sha1'),
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
            'weekly_swing_watchlist_production_runtime_wiring_readiness_context_persisted_to_live_runtime',
            'production_runtime_wiring_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_production_runtime_wiring_context_persisted_to_live_runtime',
            'production_runtime_wiring_context_persisted_to_live_runtime',
            'expected_c113_hash',
            'actual_c113_hash',
            'c113_hash_match',
            'expected_c113_file_sha1',
            'actual_c113_file_sha1',
            'c113_file_sha1_match',
            'c113_convert_from_json_pass',
            'c113_production_readiness_valid',
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

        foreach ((array) ($result['next_runtime_wiring_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C114_WEEKLY_SWING_WATCHLIST_PRODUCTION_RUNTIME_WIRING_READINESS_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C114 weekly swing watchlist production runtime wiring readiness review completed');
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
