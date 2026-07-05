<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewService;
use Illuminate\Console\Command;

class RunBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review
        {--c128-artifact=storage/app/watchlist/backtest/c128-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-completion-seal-review.json}
        {--expected-c128-hash=6ef4c4f7868f71fa3855c3db3a2e1372af201f68}
        {--expected-c128-file-sha1=33C094BFA0FF23952E68EB0E45A7C9AE092F9A82}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review.json}
        {--operator-approved}
        {--handoff-audit-archive-final-closure-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C129 weekly swing watchlist controlled runtime wiring handoff audit archive final closure review without production deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewService $service;

    public function __construct(?WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC129WeeklySwingWatchlistControlledRuntimeWiringHandoffAuditArchiveFinalClosureReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C129 weekly swing watchlist controlled runtime wiring handoff audit archive final closure review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c128-artifact'),
            (string) $this->option('expected-c128-hash'),
            (string) $this->option('expected-c128-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'handoff_audit_archive_final_closure_confirmed' => (bool) $this->option('handoff-audit-archive-final-closure-confirmed'),
                'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed' => (bool) $this->option('handoff-audit-archive-final-closure-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_ready',
            'controlled_runtime_wiring_handoff_audit_archive_completion_ready',
            'handoff_audit_archive_completion_ready',
            'audit_archive_completion_ready',
            'completion_manifest_created',
            'handoff_audit_archive_completion_confirmed',
            'handoff_audit_archive_completion_go_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_seal_review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_completion_sealed',
            'controlled_runtime_wiring_handoff_audit_archive_completion_sealed',
            'handoff_audit_archive_completion_sealed',
            'audit_archive_completion_sealed',
            'completion_seal_manifest_created',
            'handoff_audit_archive_completion_seal_confirmed',
            'handoff_audit_archive_completion_seal_go_decision',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_executed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_allowed',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_review_pass',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closed',
            'controlled_runtime_wiring_handoff_audit_archive_final_closed',
            'handoff_audit_archive_final_closed',
            'audit_archive_final_closed',
            'final_closure_manifest_created',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed',
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_confirmed',
            'handoff_audit_archive_final_closure_confirmed',
            'handoff_audit_archive_final_closure_go_decision',
            'primary_candidate_handoff_audit_archive_final_closed',
            'backup_candidate_handoff_audit_archive_final_closed',
            'comparator_candidate_handoff_audit_archive_final_closed',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'controlled_opt_in_runtime_bridge_active', 'controlled_parallel_run_active', 'controlled_rollout_active',
            'weekly_swing_watchlist_controlled_runtime_wiring_handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
            'controlled_runtime_wiring_handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
            'handoff_audit_archive_final_closure_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled', 'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published', 'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c128_hash', 'actual_c128_hash', 'c128_hash_match', 'expected_c128_file_sha1', 'actual_c128_file_sha1', 'c128_file_sha1_match', 'c128_convert_from_json_pass',
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

        if (strpos((string) ($result['status'] ?? ''), 'C129_WEEKLY_SWING_WATCHLIST_CONTROLLED_RUNTIME_WIRING_HANDOFF_AUDIT_ARCHIVE_FINAL_CLOSURE_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C129 weekly swing watchlist controlled runtime wiring handoff audit archive final closure review completed');
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
