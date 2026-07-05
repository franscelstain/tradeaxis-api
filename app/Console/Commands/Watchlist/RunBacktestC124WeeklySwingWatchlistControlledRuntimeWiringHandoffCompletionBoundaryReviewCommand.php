<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC124WeeklySwingWatchlistControlledRuntimeWiringHandoffCompletionBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC124WeeklySwingWatchlistControlledRuntimeWiringHandoffCompletionBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c124-weekly-swing-watchlist-controlled-runtime-wiring-handoff-completion-boundary-review
        {--c123-artifact=storage/app/watchlist/backtest/c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review.json}
        {--expected-c123-hash=802f76794be7b4478ece5e9587c7d5e8635ff88d}
        {--expected-c123-file-sha1=9880DB3FDDCBFBA7FA325E8956F523A850605B4D}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c124-weekly-swing-watchlist-controlled-runtime-wiring-handoff-completion-boundary-review.json}
        {--operator-approved}
        {--handoff-completion-boundary-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C124 weekly swing watchlist controlled runtime wiring handoff completion boundary review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC124WeeklySwingWatchlistControlledRuntimeWiringHandoffCompletionBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC124WeeklySwingWatchlistControlledRuntimeWiringHandoffCompletionBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC124WeeklySwingWatchlistControlledRuntimeWiringHandoffCompletionBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C124 weekly swing watchlist controlled runtime wiring handoff completion boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c123-artifact'),
            (string) $this->option('expected-c123-hash'),
            (string) $this->option('expected-c123-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'handoff_completion_boundary_confirmed' => (bool) $this->option('handoff-completion-boundary-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_executed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_allowed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared',
            'handoff_completion_boundary_cleared',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_confirmed',
            'handoff_completion_boundary_confirmed',
            'handoff_completion_boundary_go_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized',
            'controlled_runtime_wiring_handoff_finalized',
            'handoff_finalized',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready',
            'controlled_runtime_wiring_handoff_ready',
            'handoff_ready',
            'ready_for_controlled_runtime_wiring_handoff_closure_seal_review',
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_closure_seal_review',
            'controlled_runtime_wiring_handoff_completion_boundary_manifest_created',
            'controlled_runtime_wiring_handoff_closure_seal_review_allowed_next',
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_cleared',
            'completion_boundary_cleared',
            'boundary_go_decision',
            'operator_go_decision',
            'go_decision_finalized',
            'c123_handoff_finalized',
            'c122_handoff_ready',
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared',
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared',
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_completion_boundary_cleared',
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review',
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review',
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_closure_seal_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active',
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_execution_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_readiness_context_persisted_to_live_runtime',
            'handoff_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_finalization_context_persisted_to_live_runtime',
            'handoff_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_completion_boundary_context_persisted_to_live_runtime',
            'handoff_completion_boundary_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c123_hash', 'actual_c123_hash', 'c123_hash_match', 'expected_c123_file_sha1', 'actual_c123_file_sha1', 'c123_file_sha1_match', 'c123_convert_from_json_pass',
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

        if (strpos((string) ($result['status'] ?? ''), 'C124_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C124 weekly swing watchlist controlled runtime wiring handoff completion boundary review completed');
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
