<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-go-decision-finalization-review
        {--c160-operator-artifact=storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-operator-go-no-go-review.json}
        {--expected-c160-operator-hash=7f5f64e6e44973096161a4a4b42b52a725f6f863}
        {--expected-c160-operator-file-sha1=E91456245220FC28FC980D03AE35739E39257B59}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-go-decision-finalization-review.json}
        {--operator-approved}
        {--go-decision-finalization-confirmed}
        {--plan-confirm-finalization-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C160 weekly swing watchlist production/live runtime PLAN/CONFIRM GO decision finalization review.';

    private WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C160 weekly swing watchlist production/live runtime PLAN/CONFIRM GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c160-operator-artifact'),
            (string) $this->option('expected-c160-operator-hash'),
            (string) $this->option('expected-c160-operator-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
                'plan_confirm_finalization_confirmed' => (bool) $this->option('plan-confirm-finalization-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_go_decision_finalization_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_go_decision_finalization_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_go_decision_finalization_review_pass',
            'production_live_runtime_plan_confirm_go_decision_finalization_review_pass',
            'operator_decision', 'operator_go_decision', 'operator_go_decision_confirmed',
            'go_decision_finalized', 'go_decision_finalization_confirmed',
            'plan_confirm_finalization_confirmed', 'plan_confirm_closed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_boundary_review',
            'production_live_runtime_plan_confirm_completion_boundary_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_boundary_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_go_decision_finalization_manifest_created',
            'weekly_swing_watchlist_plan_confirm_result_reviewed',
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_controlled_only',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_controlled_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c160_operator_go_no_go_lock_valid', 'c160_operator_go_no_go_review_valid', 'c160_operator_go_no_go_convert_from_json_pass',
            'c160_result_review_lock_valid', 'c160_plan_confirm_result_review_valid',
            'controlled_plan_confirm_lock_valid', 'controlled_plan_confirm_integrity_valid',
            'primary_candidate_ready_for_plan_confirm_completion_boundary_review',
            'backup_candidate_ready_for_plan_confirm_completion_boundary_review',
            'comparator_candidate_ready_for_plan_confirm_completion_boundary_review',
            'a01_remains_comparator_only',
            'c160_plan_confirm_go_decision_finalization_review_only',
            'c160_controlled_plan_confirm_only',
            'c160_not_publication', 'c160_not_unrestricted_publication', 'c160_not_plan_confirm_mutation', 'c160_not_live_plan_confirm_rollout',
            'c160_topic_complete_after_finalization',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c160_operator_go_no_go_hash', 'actual_c160_operator_go_no_go_hash', 'c160_operator_go_no_go_hash_match',
            'expected_c160_operator_go_no_go_file_sha1', 'actual_c160_operator_go_no_go_file_sha1', 'c160_operator_go_no_go_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_boundary_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C160 weekly swing watchlist production/live runtime PLAN/CONFIRM GO decision finalization review completed');
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
