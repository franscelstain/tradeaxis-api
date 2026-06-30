<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService;
use Illuminate\Console\Command;

class RunBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c109-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-review
        {--c108-artifact=storage/app/watchlist/backtest/c108-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-review.json}
        {--expected-c108-hash=e7b6f6f94a40d1fe825bc0224b686d11e7510e94}
        {--expected-c108-file-sha1=591BF25C2A1E7678B2C9335ECBEF1938BDAF990C}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c109-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-completion-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService $service;

    public function __construct(?WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC109WeeklySwingWatchlistNonLiveRehearsalHandoffAuditArchiveCompletionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c108-artifact'),
            (string) $this->option('expected-c108-hash'),
            (string) $this->option('expected-c108-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_executed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_allowed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_review_pass',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_ready',
            'handoff_audit_archive_completion_ready',
            'audit_archive_completion_ready',
            'completion_manifest_created',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archived',
            'handoff_audit_archived',
            'audit_archived',
            'archive_manifest_created',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_sealed',
            'handoff_closure_sealed',
            'closure_sealed',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_cleared',
            'handoff_completion_boundary_cleared',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalized',
            'handoff_finalized',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_ready',
            'handoff_ready',
            'c108_handoff_audit_archived',
            'c107_handoff_closure_sealed',
            'c106_handoff_completion_boundary_cleared',
            'c105_handoff_finalized',
            'c104_handoff_ready',
            'primary_candidate_handoff_audit_archive_completion_ready',
            'backup_candidate_handoff_audit_archive_completion_ready',
            'comparator_candidate_handoff_audit_archive_completion_ready',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active',
            'weekly_swing_watchlist_rehearsal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_execution_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_result_review_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_operator_go_no_go_context_persisted_to_live_runtime',
            'operator_go_no_go_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_go_decision_finalization_context_persisted_to_live_runtime',
            'go_decision_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_completion_boundary_context_persisted_to_live_runtime',
            'completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_readiness_context_persisted_to_live_runtime',
            'handoff_readiness_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_finalization_context_persisted_to_live_runtime',
            'handoff_finalization_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_completion_boundary_context_persisted_to_live_runtime',
            'handoff_completion_boundary_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_closure_seal_context_persisted_to_live_runtime',
            'handoff_closure_seal_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_context_persisted_to_live_runtime',
            'handoff_audit_archive_context_persisted_to_live_runtime',
            'weekly_swing_watchlist_non_live_rehearsal_handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c108_hash', 'actual_c108_hash', 'c108_hash_match', 'expected_c108_file_sha1', 'actual_c108_file_sha1', 'c108_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C109_WEEKLY_SWING_WATCHLIST_NON_LIVE_REHEARSAL_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C109 weekly swing watchlist non-live rehearsal handoff audit archive completion review completed');
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
