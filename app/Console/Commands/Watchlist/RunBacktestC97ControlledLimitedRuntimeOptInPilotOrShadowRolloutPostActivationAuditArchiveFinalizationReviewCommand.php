<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review
        {--c96-artifact=storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json}
        {--expected-c96-hash=970152d11467ea83c80eca83081d6ae81beec38b}
        {--expected-c96-file-sha1=CCD6B92B52745B928C48BF349BC7004E755B1EB6}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c97-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-finalization-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C97 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive finalization review without live deployment, weekly output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC97ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C97 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c96-artifact'),
            (string) $this->option('expected-c96-hash'),
            (string) $this->option('expected-c96-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'audit_archive_finalization_review_executed', 'audit_archive_finalization_review_allowed', 'audit_archive_finalization_review_pass',
            'audit_archive_finalized', 'c96_audit_archive_closure_sealed', 'audit_archive_finalization_manifest_created',
            'primary_candidate_audit_archive_finalized', 'backup_candidate_audit_archive_finalized', 'comparator_candidate_audit_archive_finalized', 'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'audit_archive_finalization_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_plan_confirm_mutation_allowed', 'weekly_swing_watchlist_live_output_enabled',
            'expected_c96_hash', 'actual_c96_hash', 'c96_hash_match', 'expected_c96_file_sha1', 'actual_c96_file_sha1', 'c96_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C97_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C97 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive finalization review completed');
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
