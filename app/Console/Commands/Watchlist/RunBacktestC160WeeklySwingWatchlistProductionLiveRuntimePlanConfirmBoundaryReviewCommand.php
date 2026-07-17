<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-boundary-review
        {--c159-finalization-artifact=storage/app/watchlist/backtest/c159-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-post-publication-observation-go-decision-finalization-review.json}
        {--expected-c159-finalization-hash=1c497836fc6932909c06e62e324f806b07676ab1}
        {--expected-c159-finalization-file-sha1=97D00F48AA0D68853BAA46C36DCC571CFF3CB01F}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-boundary-review.json}
        {--operator-approved}
        {--plan-confirm-boundary-confirmed}
        {--controlled-plan-confirm-only-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C160 weekly swing watchlist production/live runtime PLAN/CONFIRM boundary review.';

    private WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C160 weekly swing watchlist production/live runtime PLAN/CONFIRM boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c159-finalization-artifact'),
            (string) $this->option('expected-c159-finalization-hash'),
            (string) $this->option('expected-c159-finalization-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'plan_confirm_boundary_confirmed' => (bool) $this->option('plan-confirm-boundary-confirmed'),
                'controlled_plan_confirm_only_confirmed' => (bool) $this->option('controlled-plan-confirm-only-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_boundary_review_pass',
            'production_live_runtime_plan_confirm_boundary_review_pass',
            'ready_for_weekly_swing_watchlist_plan_confirm_execution',
            'production_live_runtime_plan_confirm_execution_allowed_next',
            'weekly_swing_watchlist_plan_confirm_execution_allowed_next',
            'weekly_swing_watchlist_controlled_output_publication_observed',
            'weekly_swing_watchlist_controlled_output_publication_observation_stable',
            'weekly_swing_watchlist_controlled_output_publication_executed',
            'weekly_swing_watchlist_controlled_output_published',
            'weekly_swing_watchlist_controlled_publication_allowed',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_boundary_confirmed',
            'controlled_plan_confirm_only_confirmed',
            'plan_confirm_unchanged_confirmed',
            'plan_confirm_execution_allowed_next',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c159_finalization_lock_valid', 'c159_go_decision_finalization_valid', 'c159_finalization_convert_from_json_pass',
            'c159_topic_complete_after_finalization',
            'operator_approved', 'approval_reference',
            'primary_candidate_ready_for_plan_confirm_execution',
            'backup_candidate_ready_for_plan_confirm_execution',
            'comparator_candidate_ready_for_plan_confirm_execution',
            'a01_remains_comparator_only',
            'c160_boundary_review_only', 'c160_topic_number_retained_for_execution',
            'c160_not_plan_confirm_mutation', 'c160_not_live_plan_confirm_rollout', 'c160_not_publication',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c159_finalization_hash', 'actual_c159_finalization_hash', 'c159_finalization_hash_match',
            'expected_c159_finalization_file_sha1', 'actual_c159_finalization_file_sha1', 'c159_finalization_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C160 weekly swing watchlist production/live runtime PLAN/CONFIRM boundary review completed');
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
