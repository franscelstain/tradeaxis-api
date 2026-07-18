<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveCompletionSealReviewService;
use Illuminate\Console\Command;

class RunBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveCompletionSealReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-completion-seal-review
        {--c162-handoff-audit-archive-completion-artifact=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-completion-review.json}
        {--expected-c162-handoff-audit-archive-completion-hash=77f23211f2c59c9d23d13e5231b56a3869a0dd00}
        {--expected-c162-handoff-audit-archive-completion-file-sha1=5A9CF8A070E19747E6BEB885D7E5057D5900E8EC}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c162-weekly-swing-watchlist-production-live-runtime-plan-confirm-completion-handoff-audit-archive-completion-seal-review.json}
        {--operator-approved}
        {--handoff-audit-archive-completion-seal-confirmed}
        {--c162-handoff-audit-archive-completion-complete-confirmed}
        {--handoff-audit-archive-completion-ready-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--free-publication-locked-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff audit archive completion seal review.';

    private WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveCompletionSealReviewService $service;

    public function __construct(?WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveCompletionSealReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC162WeeklySwingWatchlistProductionLiveRuntimePlanConfirmCompletionHandoffAuditArchiveCompletionSealReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff audit archive completion seal review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c162-handoff-audit-archive-completion-artifact'),
            (string) $this->option('expected-c162-handoff-audit-archive-completion-hash'),
            (string) $this->option('expected-c162-handoff-audit-archive-completion-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'handoff_audit_archive_completion_seal_confirmed' => (bool) $this->option('handoff-audit-archive-completion-seal-confirmed'),
                'c162_handoff_audit_archive_completion_complete_confirmed' => (bool) $this->option('c162-handoff-audit-archive-completion-complete-confirmed'),
                'handoff_audit_archive_completion_ready_confirmed' => (bool) $this->option('handoff-audit-archive-completion-ready-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'free_publication_locked_confirmed' => (bool) $this->option('free-publication-locked-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_pass',
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_completion_seal_review_pass',
            'handoff_ready', 'handoff_finalized', 'handoff_completion_boundary_cleared',
            'handoff_closure_sealed', 'handoff_audit_archived', 'handoff_audit_archive_completion_ready',
            'handoff_audit_archive_completion_sealed', 'handoff_audit_archive_completion_seal_confirmed',
            'handoff_audit_archive_completion_seal_go_decision',
            'c162_handoff_audit_archive_completion_complete_confirmed', 'handoff_audit_archive_completion_ready_confirmed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed',
            'free_publication_locked_confirmed',
            'c162_handoff_audit_archive_completion_lock_valid', 'c162_plan_confirm_completion_handoff_audit_archive_completion_valid',
            'c162_handoff_audit_archive_completion_convert_from_json_pass',
            'ready_for_weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_review',
            'production_live_runtime_plan_confirm_completion_handoff_audit_archive_final_closure_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_final_closure_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_completion_handoff_audit_archive_completion_seal_manifest_created',
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
            'primary_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review',
            'backup_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review',
            'comparator_candidate_ready_for_plan_confirm_completion_handoff_audit_archive_final_closure_review',
            'a01_remains_comparator_only',
            'c162_plan_confirm_completion_handoff_audit_archive_completion_seal_review_only',
            'c162_controlled_completion_only',
            'c162_not_publication', 'c162_not_unrestricted_publication', 'c162_not_plan_confirm_mutation', 'c162_not_live_plan_confirm_rollout',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c162_handoff_audit_archive_completion_hash', 'actual_c162_handoff_audit_archive_completion_hash', 'c162_handoff_audit_archive_completion_hash_match',
            'expected_c162_handoff_audit_archive_completion_file_sha1', 'actual_c162_handoff_audit_archive_completion_file_sha1', 'c162_handoff_audit_archive_completion_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_plan_confirm_completion_handoff_audit_archive_final_closure_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C162_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_COMPLETION_HANDOFF_AUDIT_ARCHIVE_COMPLETION_SEAL_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C162 weekly swing watchlist production/live runtime PLAN/CONFIRM completion handoff audit archive completion seal review completed');
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
