<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewService;
use Illuminate\Console\Command;

class RunBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c116-weekly-swing-watchlist-controlled-runtime-wiring-execution-review
        {--c115-artifact=storage/app/watchlist/backtest/c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review.json : Locked C115 controlled runtime wiring execution approval review artifact path}
        {--expected-c115-hash=0e28d161447332d62df603edd7ba666b37e8dd04 : Expected C115 artifact_hash}
        {--expected-c115-file-sha1=82E446F553FD0384FDD6E0BE25AE80F8E4FEA949 : Expected C115 artifact file SHA1}
        {--approval-reference= : Operator approval reference for C116 review-only controlled runtime wiring execution review}
        {--output=storage/app/watchlist/backtest/c116-weekly-swing-watchlist-controlled-runtime-wiring-execution-review.json : Output artifact path}
        {--operator-approved : Confirms operator approved this review-only C116 run}
        {--overwrite : Overwrite output artifact if present}
        {--progress : Print progress lines}';

    protected $description = 'Run C116 / PR-04 weekly swing watchlist controlled runtime wiring execution review in artifact-only, non-live, non-mutating context.';

    public function handle(WatchlistBacktestC116WeeklySwingWatchlistControlledRuntimeWiringExecutionReviewService $service): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C116 weekly swing watchlist controlled runtime wiring execution review started');
        }

        $result = $service->execute(
            (string) $this->option('c115-artifact'),
            (string) $this->option('expected-c115-hash'),
            (string) $this->option('expected-c115-file-sha1'),
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
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_execution_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
            'expected_c115_hash',
            'actual_c115_hash',
            'c115_hash_match',
            'expected_c115_file_sha1',
            'actual_c115_file_sha1',
            'c115_file_sha1_match',
            'c115_convert_from_json_pass',
            'c115_execution_approval_valid',
            'c114_hash_match',
            'c114_file_sha1_match',
            'c114_convert_from_json_pass',
            'c114_runtime_wiring_readiness_valid',
            'c111_final_closure_valid',
            'c111_non_live_audit_archive_terminal',
            'c112_not_audit_archive_continuation',
            'c112_does_not_reopen_c111_final_closure',
            'c113_production_readiness_valid',
            'diagnostic_conclusion',
            'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_execution_observation_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C116_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C116 weekly swing watchlist controlled runtime wiring execution review completed');
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
