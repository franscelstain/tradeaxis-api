<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c167-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-completion-boundary-review
        {--c166-finalization-artifact=storage/app/watchlist/backtest/c166-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-post-rollout-observation-go-decision-finalization-review.json}
        {--expected-c166-finalization-hash=299eb7f2978b8755351d28bb299249f0cb0d818f}
        {--expected-c166-finalization-file-sha1=3E2CF7C226756EFD9F3AADBDDCAE3BD133D174BA}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c167-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-completion-boundary-review.json}
        {--operator-approved}
        {--controlled-rollout-completion-boundary-confirmed}
        {--c166-finalization-locked-confirmed}
        {--controlled-rollout-evidence-chain-complete-confirmed}
        {--completion-execution-required-confirmed}
        {--market-metrics-not-inferred-confirmed}
        {--candidate-scope-confirmed}
        {--kill-switch-confirmed}
        {--rollback-confirmed}
        {--production-config-unchanged-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run the C167 PLAN/CONFIRM controlled rollout completion boundary review.';

    private WatchlistBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC167WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutCompletionBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C167 PLAN/CONFIRM controlled rollout completion boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c166-finalization-artifact'),
            (string) $this->option('expected-c166-finalization-hash'),
            (string) $this->option('expected-c166-finalization-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'controlled_rollout_completion_boundary_confirmed' => (bool) $this->option('controlled-rollout-completion-boundary-confirmed'),
                'c166_finalization_locked_confirmed' => (bool) $this->option('c166-finalization-locked-confirmed'),
                'controlled_rollout_evidence_chain_complete_confirmed' => (bool) $this->option('controlled-rollout-evidence-chain-complete-confirmed'),
                'completion_execution_required_confirmed' => (bool) $this->option('completion-execution-required-confirmed'),
                'market_metrics_not_inferred_confirmed' => (bool) $this->option('market-metrics-not-inferred-confirmed'),
                'candidate_scope_confirmed' => (bool) $this->option('candidate-scope-confirmed'),
                'kill_switch_confirmed' => (bool) $this->option('kill-switch-confirmed'),
                'rollback_confirmed' => (bool) $this->option('rollback-confirmed'),
                'production_config_unchanged_confirmed' => (bool) $this->option('production-config-unchanged-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_pass',
            'production_live_runtime_plan_confirm_controlled_rollout_completion_boundary_review_pass',
            'controlled_rollout_completion_boundary_confirmed', 'controlled_rollout_completion_boundary_open',
            'c166_finalization_locked_confirmed', 'c166_finalization_lock_valid', 'c166_finalization_state_valid',
            'controlled_rollout_evidence_chain_complete_confirmed', 'completion_execution_required_confirmed',
            'market_metrics_not_inferred_confirmed', 'candidate_scope_confirmed',
            'kill_switch_confirmed', 'rollback_confirmed', 'production_config_unchanged_confirmed',
            'free_publication_locked_confirmed',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_completion_execution',
            'controlled_plan_confirm_rollout_completion_execution_allowed_next',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'new_rollout_executed', 'new_plan_confirm_mutation_executed', 'new_catalog_read_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'production_config_mutated', 'watchlist_function_invoked_by_boundary',
            'watchlist_function_used', 'watchlist_function_runtime_mode',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_completion_execution',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_completion_execution',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_completion_execution',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code',
            'a01_remains_comparator_only', 'c167_is_distinct_controlled_rollout_completion_topic',
            'c167_not_c166_observation_repeat', 'c167_boundary_review_only', 'c167_topic_open', 'c167_topic_complete',
            'operator_approved', 'approval_reference',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed',
            'expected_c166_finalization_hash', 'actual_c166_finalization_hash', 'c166_finalization_hash_match',
            'expected_c166_finalization_file_sha1', 'actual_c166_finalization_file_sha1', 'c166_finalization_file_sha1_match',
            'c166_finalization_convert_from_json_pass', 'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_controlled_rollout_completion_execution_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (str_starts_with((string) ($result['status'] ?? ''), self::passPrefix())) {
            if ((bool) $this->option('progress')) {
                $this->line('C167 PLAN/CONFIRM controlled rollout completion boundary review completed');
            }

            return 0;
        }

        $this->error((string) ($result['message'] ?? 'C167 boundary review failed.'));

        return 1;
    }

    private static function passPrefix(): string
    {
        return 'C167_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_COMPLETION_BOUNDARY_REVIEW_PASSED';
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
