<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review
        {--c122-artifact=storage/app/watchlist/backtest/c122-weekly-swing-watchlist-controlled-runtime-wiring-handoff-readiness-review.json}
        {--expected-c122-hash=0edfa166bfa8f195db6dfd09f318b6e0515cc5c7}
        {--expected-c122-file-sha1=FF830FE04623A636F86E514120575BD57A98EEB4}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c123-weekly-swing-watchlist-controlled-runtime-wiring-handoff-finalization-review.json}
        {--operator-approved}
        {--handoff-finalization-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C123 weekly swing watchlist controlled runtime wiring handoff finalization review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC123WeeklySwingWatchlistControlledRuntimeWiringHandoffFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C123 weekly swing watchlist controlled runtime wiring handoff finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c122-artifact'),
            (string) $this->option('expected-c122-hash'),
            (string) $this->option('expected-c122-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'handoff_finalization_confirmed' => (bool) $this->option('handoff-finalization-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_executed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_allowed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalization_review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized',
            'controlled_runtime_wiring_handoff_finalized',
            'handoff_finalized',
            'handoff_finalization_confirmed',
            'handoff_finalization_go_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready',
            'controlled_runtime_wiring_handoff_ready',
            'handoff_ready',
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review',
            'ready_for_controlled_runtime_wiring_handoff_completion_boundary_review',
            'controlled_runtime_wiring_handoff_finalization_manifest_created',
            'controlled_runtime_wiring_handoff_completion_boundary_review_allowed_next',
            'weekly_swing_watchlist_controlled_runtime_wiring_completion_boundary_cleared',
            'completion_boundary_cleared', 'boundary_go_decision',
            'operator_go_decision', 'go_decision_finalized', 'c122_handoff_ready',
            'primary_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review',
            'backup_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review',
            'comparator_candidate_ready_for_controlled_runtime_wiring_handoff_completion_boundary_review',
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized',
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized',
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_finalized',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active',
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
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c122_hash', 'actual_c122_hash', 'c122_hash_match', 'expected_c122_file_sha1', 'actual_c122_file_sha1', 'c122_file_sha1_match', 'c122_convert_from_json_pass',
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

        if (strpos((string) ($result['status'] ?? ''), 'C123_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C123 weekly swing watchlist controlled runtime wiring handoff finalization review completed');
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
