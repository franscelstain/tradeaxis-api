<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review
        {--c119-artifact=storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json : Locked C119 controlled runtime wiring operator GO/NO-GO review artifact path}
        {--expected-c119-hash=132ebe9778dd6d8e04834ff6174bdeec10e2e8f5 : Expected C119 artifact_hash}
        {--expected-c119-file-sha1=8ED2AFFAB95C75099E9365A2D959154F67FF9044 : Expected C119 artifact file SHA1}
        {--approval-reference= : Operator approval reference for C120 controlled runtime wiring GO decision finalization review}
        {--output=storage/app/watchlist/backtest/c120-weekly-swing-watchlist-controlled-runtime-wiring-go-decision-finalization-review.json : Output artifact path}
        {--operator-approved : Confirms operator approved this review-only C120 run}
        {--go-decision-finalization-confirmed : Confirms the C119 GO decision is finalized as C120 artifact evidence}
        {--overwrite : Overwrite output artifact if present}
        {--progress : Print progress lines}';

    protected $description = 'Run C120 / PR-08 weekly swing watchlist controlled runtime wiring GO decision finalization review in artifact-only, non-live, non-mutating context.';

    public function handle(WatchlistBacktestC120WeeklySwingWatchlistControlledRuntimeWiringGoDecisionFinalizationReviewService $service): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C120 weekly swing watchlist controlled runtime wiring GO decision finalization review started');
        }

        $result = $service->execute(
            (string) $this->option('c119-artifact'),
            (string) $this->option('expected-c119-hash'),
            (string) $this->option('expected-c119-file-sha1'),
            (string) $this->option('output'),
            [
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
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
            'operator_go_decision',
            'operator_go_decision_confirmed',
            'go_decision_finalized',
            'go_decision_finalization_confirmed',
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
            'weekly_swing_watchlist_controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
            'expected_c119_hash',
            'actual_c119_hash',
            'c119_hash_match',
            'expected_c119_file_sha1',
            'actual_c119_file_sha1',
            'c119_file_sha1_match',
            'c119_convert_from_json_pass',
            'c119_lock_valid',
            'c119_operator_go_no_go_valid',
            'c118_hash_match',
            'c118_file_sha1_match',
            'c118_convert_from_json_pass',
            'c118_observation_result_review_valid',
            'c117_hash_match',
            'c117_file_sha1_match',
            'c117_convert_from_json_pass',
            'c117_observation_review_valid',
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

        foreach ((array) ($result['next_completion_boundary_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C120_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C120 weekly swing watchlist controlled runtime wiring GO decision finalization review completed');
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
