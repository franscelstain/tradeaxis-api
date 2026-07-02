<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review
        {--c118-artifact=storage/app/watchlist/backtest/c118-weekly-swing-watchlist-controlled-runtime-wiring-observation-result-review.json : Locked C118 controlled runtime wiring observation result review artifact path}
        {--expected-c118-hash=fff0b2461783386f897971a55621e265f4f1498f : Expected C118 artifact_hash}
        {--expected-c118-file-sha1=1D81849D13F815900D56FE450BF69991904EA760 : Expected C118 artifact file SHA1}
        {--approval-reference= : Operator approval reference for C119 controlled runtime wiring operator go/no-go review}
        {--output=storage/app/watchlist/backtest/c119-weekly-swing-watchlist-controlled-runtime-wiring-operator-go-no-go-review.json : Output artifact path}
        {--operator-approved : Confirms operator approved this review-only C119 run}
        {--operator-go-decision-confirmed : Confirms operator GO decision is explicitly recorded}
        {--overwrite : Overwrite output artifact if present}
        {--progress : Print progress lines}';

    protected $description = 'Run C119 / PR-07 weekly swing watchlist controlled runtime wiring operator go/no-go review in artifact-only, non-live, non-mutating context.';

    public function handle(WatchlistBacktestC119WeeklySwingWatchlistControlledRuntimeWiringOperatorGoNoGoReviewService $service): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C119 weekly swing watchlist controlled runtime wiring operator go/no-go review started');
        }

        $result = $service->execute(
            (string) $this->option('c118-artifact'),
            (string) $this->option('expected-c118-hash'),
            (string) $this->option('expected-c118-file-sha1'),
            (string) $this->option('output'),
            [
                'operator_approved' => (bool) $this->option('operator-approved'),
                'operator_go_decision_confirmed' => (bool) $this->option('operator-go-decision-confirmed'),
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
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_observation_result_review_context_persisted_to_live_runtime',
            'expected_c118_hash',
            'actual_c118_hash',
            'c118_hash_match',
            'expected_c118_file_sha1',
            'actual_c118_file_sha1',
            'c118_file_sha1_match',
            'c118_convert_from_json_pass',
            'c118_observation_result_review_valid',
            'c117_hash_match',
            'c117_file_sha1_match',
            'c117_convert_from_json_pass',
            'c117_observation_review_valid',
            'c115_hash_match',
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

        foreach ((array) ($result['next_go_decision_finalization_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C119_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_OPERATOR_GO_NO_GO_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C119 weekly swing watchlist controlled runtime wiring operator go/no-go review completed');
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
