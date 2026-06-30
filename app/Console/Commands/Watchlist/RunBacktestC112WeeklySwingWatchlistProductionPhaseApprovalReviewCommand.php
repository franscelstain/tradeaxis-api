<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService;
use Illuminate\Console\Command;

class RunBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c112-weekly-swing-watchlist-production-phase-approval-review
        {--c111-artifact=storage/app/watchlist/backtest/c111-weekly-swing-watchlist-non-live-rehearsal-handoff-audit-archive-final-closure-review.json}
        {--expected-c111-hash=8f7c8b81eb401bfdd70f62f90779db63fc4af56d}
        {--expected-c111-file-sha1=D58C10185970C9344F6EB3818A5A31C75C876842}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c112-weekly-swing-watchlist-production-phase-approval-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C112 weekly swing watchlist new production phase approval review without live runtime wiring, deployment, weekly live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService $service;

    public function __construct(?WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC112WeeklySwingWatchlistProductionPhaseApprovalReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C112 weekly swing watchlist production phase approval review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c111-artifact'),
            (string) $this->option('expected-c111-hash'),
            (string) $this->option('expected-c111-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_phase_approval_review_executed',
            'weekly_swing_watchlist_production_phase_approval_review_allowed',
            'weekly_swing_watchlist_production_phase_approval_review_pass',
            'weekly_swing_watchlist_production_phase_opened',
            'production_phase_approval_granted',
            'production_readiness_review_allowed',
            'primary_candidate_production_phase_approval_granted',
            'backup_candidate_production_phase_approval_granted',
            'comparator_candidate_production_phase_approval_granted',
            'c111_handoff_audit_archive_final_closed',
            'c111_audit_archive_final_closed',
            'c111_final_closure_manifest_created',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining',
            'temporary_negative_artifact_cleanup_confirmed',
            'temporary_negative_artifact_paths',
            'production_ready',
            'production_catalog_runtime_wired',
            'production_runtime_wiring_allowed',
            'production_runtime_wiring_executed',
            'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active',
            'controlled_rollout_active',
            'weekly_swing_watchlist_production_phase_approval_context_persisted_to_live_runtime',
            'production_phase_approval_context_persisted_to_live_runtime',
            'production_deployment_allowed',
            'production_deployment_executed',
            'plan_confirm_mutation_allowed',
            'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed',
            'pilot_runtime_active',
            'shadow_runtime_active',
            'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_plan_confirm_mutation_allowed',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c111_hash',
            'actual_c111_hash',
            'c111_hash_match',
            'expected_c111_file_sha1',
            'actual_c111_file_sha1',
            'c111_file_sha1_match',
            'diagnostic_conclusion',
            'next_step_recommendation',
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

        if (strpos((string) ($result['status'] ?? ''), 'C112_WEEKLY_SWING_WATCHLIST_PRODUCTION_PHASE_APPROVAL_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C112 weekly swing watchlist production phase approval review completed');
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
