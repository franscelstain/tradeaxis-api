<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review
        {--c164-finalization-artifact=storage/app/watchlist/backtest/c164-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-activation-completion-go-decision-finalization-review.json}
        {--expected-c164-finalization-hash=63c7512cb6d395bc6268dae385a10ae703e4aa3d}
        {--expected-c164-finalization-file-sha1=9CA9F2F36F15F17C15301E9F119C303088EDD163}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c165-weekly-swing-watchlist-production-live-runtime-plan-confirm-controlled-rollout-boundary-review.json}
        {--operator-approved}
        {--controlled-rollout-boundary-confirmed}
        {--c164-finalization-locked-confirmed}
        {--controlled-rollout-only-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-rollout-executed-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run the C165 PLAN/CONFIRM controlled rollout boundary review.';

    private WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC165WeeklySwingWatchlistProductionLiveRuntimePlanConfirmControlledRolloutBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C165 PLAN/CONFIRM controlled rollout boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c164-finalization-artifact'),
            (string) $this->option('expected-c164-finalization-hash'),
            (string) $this->option('expected-c164-finalization-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'controlled_rollout_boundary_confirmed' => (bool) $this->option('controlled-rollout-boundary-confirmed'),
                'c164_finalization_locked_confirmed' => (bool) $this->option('c164-finalization-locked-confirmed'),
                'controlled_rollout_only_confirmed' => (bool) $this->option('controlled-rollout-only-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_rollout_executed_confirmed' => (bool) $this->option('no-rollout-executed-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_controlled_rollout_boundary_review_pass',
            'production_live_runtime_plan_confirm_controlled_rollout_boundary_review_pass',
            'controlled_rollout_boundary_confirmed', 'controlled_rollout_boundary_open',
            'c164_finalization_locked_confirmed', 'c164_finalization_lock_valid', 'c164_finalization_state_valid',
            'controlled_rollout_only_confirmed', 'plan_confirm_unchanged_confirmed',
            'no_rollout_executed_confirmed', 'free_publication_locked_confirmed',
            'ready_for_weekly_swing_watchlist_plan_confirm_controlled_rollout_execution',
            'controlled_plan_confirm_rollout_execution_allowed_next',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'watchlist_function_used', 'watchlist_function_runtime_mode',
            'primary_candidate_ready_for_plan_confirm_controlled_rollout_execution',
            'backup_candidate_ready_for_plan_confirm_controlled_rollout_execution',
            'comparator_candidate_ready_for_plan_confirm_controlled_rollout_execution',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code',
            'a01_remains_comparator_only', 'c165_is_distinct_controlled_rollout_topic',
            'c165_not_c164_completion_repeat', 'c165_boundary_review_only',
            'operator_approved', 'approval_reference',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed',
            'expected_c164_finalization_hash', 'actual_c164_finalization_hash', 'c164_finalization_hash_match',
            'expected_c164_finalization_file_sha1', 'actual_c164_finalization_file_sha1', 'c164_finalization_file_sha1_match',
            'c164_finalization_convert_from_json_pass', 'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_controlled_rollout_execution_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (str_starts_with((string) ($result['status'] ?? ''), self::passPrefix())) {
            if ((bool) $this->option('progress')) {
                $this->line('C165 PLAN/CONFIRM controlled rollout boundary review completed');
            }

            return 0;
        }

        $this->error((string) ($result['message'] ?? 'C165 boundary review failed.'));

        return 1;
    }

    private static function passPrefix(): string
    {
        return 'C165_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_CONTROLLED_ROLLOUT_BOUNDARY_REVIEW_PASSED';
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
