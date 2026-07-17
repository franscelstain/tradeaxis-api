<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewService;
use Illuminate\Console\Command;

class RunBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review
        {--c161-finalization-artifact=storage/app/watchlist/backtest/c161-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-go-decision-finalization-review.json}
        {--expected-c161-finalization-hash=9409df354fc360554d502b4787878c770e806d45}
        {--expected-c161-finalization-file-sha1=06441C61A6A4B1F4BFE4C8398CD0BB4ED1C552EF}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-readiness-review.json}
        {--operator-approved}
        {--handoff-readiness-confirmed}
        {--c161-topic-complete-confirmed}
        {--plan-confirm-completion-closed-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff readiness review.';

    private WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewService $service;

    public function __construct(?WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffReadinessReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff readiness review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c161-finalization-artifact'),
            (string) $this->option('expected-c161-finalization-hash'),
            (string) $this->option('expected-c161-finalization-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'handoff_readiness_confirmed' => (bool) $this->option('handoff-readiness-confirmed'),
                'c161_topic_complete_confirmed' => (bool) $this->option('c161-topic-complete-confirmed'),
                'plan_confirm_completion_closed_confirmed' => (bool) $this->option('plan-confirm-completion-closed-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass',
            'production_live_runtime_plan_confirm_completion_handoff_readiness_review_pass',
            'handoff_ready', 'handoff_readiness_confirmed', 'handoff_readiness_go_decision',
            'c161_topic_complete_confirmed', 'plan_confirm_completion_closed_confirmed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'c161_finalization_lock_valid', 'c161_plan_confirm_completion_finalization_valid', 'c161_finalization_convert_from_json_pass',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review',
            'production_live_runtime_plan_confirm_completion_handoff_finalization_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_finalization_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_readiness_manifest_created',
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
            'controlled_completion_path', 'controlled_completion_hash', 'controlled_completion_file_sha1', 'controlled_completion_record_count',
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
            'primary_candidate_ready_for_plan_confirm_completion_handoff_finalization_review',
            'backup_candidate_ready_for_plan_confirm_completion_handoff_finalization_review',
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_finalization_review',
            'a01_remains_comparator_only',
            'c162_plan_confirm_completion_handoff_readiness_review_only',
            'c162_controlled_completion_only',
            'c162_not_publication', 'c162_not_unrestricted_publication', 'c162_not_plan_confirm_mutation', 'c162_not_live_plan_confirm_rollout',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c161_finalization_hash', 'actual_c161_finalization_hash', 'c161_finalization_hash_match',
            'expected_c161_finalization_file_sha1', 'actual_c161_finalization_file_sha1', 'c161_finalization_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_handoff_finalization_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_READINESS_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff readiness review completed');
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
