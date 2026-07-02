<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewService;
use Illuminate\Console\Command;

class RunBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review
        {--c114-artifact=storage/app/watchlist/backtest/c114-weekly-swing-watchlist-production-runtime-wiring-readiness-review.json : Locked C114 production runtime wiring readiness review artifact path}
        {--expected-c114-hash=f66f44216218ae5360e7920ef20f0ff051f8f987 : Expected C114 artifact_hash}
        {--expected-c114-file-sha1=51590143E73A77EB33F6ED67065CAE6ADF30D778 : Expected C114 artifact file SHA1}
        {--approval-reference= : Operator approval reference for C115 review-only controlled runtime wiring execution approval review}
        {--output=storage/app/watchlist/backtest/c115-weekly-swing-watchlist-controlled-runtime-wiring-execution-approval-review.json : Output artifact path}
        {--operator-approved : Confirms operator approved this review-only C115 run}
        {--overwrite : Overwrite output artifact if present}
        {--progress : Print progress lines}';

    protected $description = 'Run C115 / PR-03 weekly swing watchlist controlled runtime wiring execution approval review in review-only, non-live, non-mutating context.';

    public function handle(WatchlistBacktestC115WeeklySwingWatchlistControlledRuntimeWiringExecutionApprovalReviewService $service): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C115 weekly swing watchlist controlled runtime wiring execution approval review started');
        }

        $result = $service->execute(
            (string) $this->option('c114-artifact'),
            (string) $this->option('expected-c114-hash'),
            (string) $this->option('expected-c114-file-sha1'),
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
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_execution_approval_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
            'expected_c114_hash',
            'actual_c114_hash',
            'c114_hash_match',
            'expected_c114_file_sha1',
            'actual_c114_file_sha1',
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

        foreach ((array) ($result['next_execution_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C115_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_EXECUTION_APPROVAL_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C115 weekly swing watchlist controlled runtime wiring execution approval review completed');
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
