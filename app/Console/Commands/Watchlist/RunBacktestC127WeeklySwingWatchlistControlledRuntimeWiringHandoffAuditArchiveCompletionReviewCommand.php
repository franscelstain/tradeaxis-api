<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService;
use Illuminate\Console\Command;

class RunBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review
        {--c126-artifact=storage/app/watchlist/backtest/c126-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-review.json}
        {--expected-c126-hash=3f990d65414dd754ac4cd7a257ade44d52c89b67}
        {--expected-c126-file-sha1=16B4F020A06459B46CD5ECDAAEDAC1DC2829561E}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c127-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-review.json}
        {--operator-approved}
        {--handoff-audit-archive-completion-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C127 weekly swing watchlist controlled runtime wiring handoff audit archive completion review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService $service;

    public function __construct(?WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC127WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveCompletionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C127 weekly swing watchlist controlled runtime wiring handoff audit archive completion review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c126-artifact'),
            (string) $this->option('expected-c126-hash'),
            (string) $this->option('expected-c126-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'handoff_audit_archive_completion_confirmed' => (bool) $this->option('handoff-audit-archive-completion-confirmed'),
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_confirmed' => (bool) $this->option('handoff-audit-archive-completion-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_executed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_allowed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
            'controlled_runtime_wiring_handoff_audit_archive_completion_ready',
            'handoff_audit_archive_completion_ready',
            'audit_archive_completion_ready',
            'completion_manifest_created',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_confirmed',
            'controlled_runtime_wiring_handoff_audit_archive_completion_confirmed',
            'handoff_audit_archive_completion_confirmed',
            'handoff_audit_archive_completion_go_decision',
            'ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review',
            'weekly_swing_watchlist_ready_for_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review',
            'controlled_runtime_wiring_handoff_audit_archive_completion_manifest_created',
            'controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_allowed_next',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archived',
            'controlled_runtime_wiring_handoff_audit_archived',
            'handoff_audit_archived',
            'audit_archived',
            'archive_manifest_created',
            'handoff_audit_archive_confirmed',
            'handoff_audit_archive_go_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_closure_sealed',
            'handoff_closure_sealed',
            'closure_sealed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_completion_boundary_cleared',
            'handoff_completion_boundary_cleared',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_finalized',
            'handoff_finalized',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_ready',
            'handoff_ready',
            'operator_go_decision',
            'go_decision_finalized',
            'c126_handoff_audit_archived',
            'c125_handoff_closure_sealed',
            'c124_handoff_completion_boundary_cleared',
            'c123_handoff_finalized',
            'c122_handoff_ready',
            'primary_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
            'backup_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
            'comparator_candidate_weekly_swing_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'handoff_audit_archive_completion_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c126_hash', 'actual_c126_hash', 'c126_hash_match', 'expected_c126_file_sha1', 'actual_c126_file_sha1', 'c126_file_sha1_match', 'c126_convert_from_json_pass',
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

        if (strpos((string) ($result['status'] ?? ''), 'C127_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C127 weekly swing watchlist controlled runtime wiring handoff audit archive completion review completed');
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
