<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-boundary-review
        {--c162-handoff-audit-archive-final-closure-artifact=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-final-closure-review.json}
        {--expected-c162-handoff-audit-archive-final-closure-hash=4de6d670e5e6d6990dd618e0e818e57a7f79716e}
        {--expected-c162-handoff-audit-archive-final-closure-file-sha1=97E9057EE0E7A71BC7F74B019F16FE1D251A3157}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c163-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-post-handoff-boundary-review.json}
        {--operator-approved}
        {--post-handoff-boundary-confirmed}
        {--c162-handoff-audit-archive-chain-closed-confirmed}
        {--c162-terminal-no-next-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff boundary review.';

    private WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC163WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionPostHandoffBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c162-handoff-audit-archive-final-closure-artifact'),
            (string) $this->option('expected-c162-handoff-audit-archive-final-closure-hash'),
            (string) $this->option('expected-c162-handoff-audit-archive-final-closure-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'post_handoff_boundary_confirmed' => (bool) $this->option('post-handoff-boundary-confirmed'),
                'c162_handoff_audit_archive_chain_closed_confirmed' => (bool) $this->option('c162-handoff-audit-archive-chain-closed-confirmed'),
                'c162_terminal_no_next_confirmed' => (bool) $this->option('c162-terminal-no-next-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass',
            'production_live_runtime_plan_confirm_completion_post_handoff_boundary_review_pass',
            'post_handoff_boundary_confirmed',
            'c162_handoff_audit_archive_chain_closed_confirmed',
            'c162_terminal_no_next_confirmed',
            'plan_confirm_unchanged_confirmed',
            'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'c162_handoff_audit_archive_final_closure_lock_valid',
            'c162_plan_confirm_completion_post_handoff_boundary_valid',
            'c162_handoff_audit_archive_final_closure_convert_from_json_pass',
            'c162_handoff_audit_archive_final_closure_complete',
            'no_next_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_review_required',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_review',
            'production_live_runtime_plan_confirm_completion_post_handoff_activation_readiness_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_post_handoff_activation_readiness_review_allowed_next',
            'controlled_completion_path', 'controlled_completion_hash', 'controlled_completion_file_sha1', 'controlled_completion_record_count',
            'weekly_swing_watchlist_plan_confirm_completion_result_reviewed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_completion_controlled_only',
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
            'primary_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review',
            'backup_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review',
            'comparator_candidate_ready_for_plan_confirm_completion_post_handoff_activation_readiness_review',
            'a01_remains_comparator_only',
            'c163_is_new_post_handoff_contract',
            'c163_not_c162_handoff_audit_archive_continuation',
            'c163_post_handoff_boundary_review_only',
            'c163_controlled_completion_only',
            'c163_not_publication', 'c163_not_unrestricted_publication', 'c163_not_plan_confirm_mutation', 'c163_not_live_plan_confirm_rollout',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c162_handoff_audit_archive_final_closure_hash', 'actual_c162_handoff_audit_archive_final_closure_hash', 'c162_handoff_audit_archive_final_closure_hash_match',
            'expected_c162_handoff_audit_archive_final_closure_file_sha1', 'actual_c162_handoff_audit_archive_final_closure_file_sha1', 'c162_handoff_audit_archive_final_closure_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_post_handoff_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C163_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_POST_HANDOFF_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C163 weekly swing watchlist production/live runtime PLAN/CONFIRM completion post-handoff boundary review completed');
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
