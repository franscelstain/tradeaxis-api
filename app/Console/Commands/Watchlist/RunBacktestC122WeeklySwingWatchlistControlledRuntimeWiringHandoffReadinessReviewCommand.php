<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewService;
use Illuminate\Console\Command;

class RunBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review
        {--c121-artifact=storage/app/watchlist/backtest/c121-weekly-swing-watchlist-controlled-runtime-wiring-completion-boundary-review.json : Locked C121 controlled runtime wiring completion boundary review artifact path}
        {--expected-c121-hash=54c19fc3235d62f07b3d57b3faac96f09afeb616 : Expected C121 artifact_hash}
        {--expected-c121-file-sha1=AF4AF4C557F57D1435AC226311E8F49E509C4BA8 : Expected C121 artifact file SHA1}
        {--approval-reference= : Operator approval reference for C122 controlled runtime wiring handoff readiness review}
        {--output=storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json : Output artifact path}
        {--operator-approved : Confirms operator approved this review-only C122 run}
        {--handoff-readiness-confirmed : Confirms the C121 boundary clearance is ready for C122 handoff readiness}
        {--overwrite : Overwrite output artifact if present}
        {--progress : Print progress lines}';

    protected $description = 'Run C122 / PR-10 weekly swing watchlist controlled runtime wiring handoff readiness review in artifact-only, non-live, non-mutating context.';

    public function handle(WatchlistBacktestC122WeeklySwingWatchlistControlledRuntimeWiringHandoffReadinessReviewService $service): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C122 weekly swing watchlist controlled runtime wiring handoff readiness review started');
        }

        $result = $service->execute(
            (string) $this->option('c121-artifact'),
            (string) $this->option('expected-c121-hash'),
            (string) $this->option('expected-c121-file-sha1'),
            (string) $this->option('output'),
            [
                'operator_approved' => (bool) $this->option('operator-approved'),
                'handoff_readiness_confirmed' => (bool) $this->option('handoff-readiness-confirmed'),
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
            'handoff_ready',
            'handoff_readiness_confirmed',
            'handoff_readiness_go_decision',
            'completion_boundary_cleared',
            'completion_boundary_confirmed',
            'temporary_negative_artifacts_remaining',
            'temporary_negative_artifact_cleanup_confirmed',
            'temporary_negative_artifact_paths',
            'production_ready',
            'production_catalog_runtime_wired',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
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
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
            'handoff_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
            'expected_c121_hash',
            'actual_c121_hash',
            'c121_hash_match',
            'expected_c121_file_sha1',
            'actual_c121_file_sha1',
            'c121_file_sha1_match',
            'c121_convert_from_json_pass',
            'c121_lock_valid',
            'c121_completion_boundary_valid',
            'c120_hash_match',
            'c120_file_sha1_match',
            'c120_convert_from_json_pass',
            'c120_go_decision_finalization_valid',
            'c119_hash_match',
            'c119_file_sha1_match',
            'c119_convert_from_json_pass',
            'c119_operator_go_no_go_valid',
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

        foreach ((array) ($result['next_handoff_finalization_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C122_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_READINESS_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C122 weekly swing watchlist controlled runtime wiring handoff readiness review completed');
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
