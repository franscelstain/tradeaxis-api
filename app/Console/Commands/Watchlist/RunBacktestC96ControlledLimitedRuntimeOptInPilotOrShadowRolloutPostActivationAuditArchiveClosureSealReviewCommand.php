<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewService;
use Illuminate\Console\Command;

class RunBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review
        {--c95-artifact=storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json}
        {--expected-c95-hash=a8923e58e35126741226eab29cc07c88a2a721f8}
        {--expected-c95-file-sha1=AEF14CC999F8050DADC8E451E9116C59FD1C2534}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c96-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-closure-seal-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C96 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive closure seal review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewService $service;

    public function __construct(?WatchlistBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC96ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveClosureSealReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C96 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive closure seal review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c95-artifact'),
            (string) $this->option('expected-c95-hash'),
            (string) $this->option('expected-c95-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_audit_archive_closure_seal_review_executed', 'post_activation_audit_archive_closure_seal_review_allowed', 'post_activation_audit_archive_closure_seal_review_pass',
            'post_activation_audit_archive_closure_sealed', 'audit_archive_closure_sealed', 'c95_audit_archive_completed', 'closure_seal_manifest_created',
            'primary_candidate_audit_archive_closure_sealed', 'backup_candidate_audit_archive_closure_sealed', 'comparator_candidate_audit_archive_closure_sealed', 'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_audit_archive_context_persisted_to_live_runtime', 'post_activation_audit_archive_completion_context_persisted_to_live_runtime', 'post_activation_audit_archive_closure_seal_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'expected_c95_hash', 'actual_c95_hash', 'c95_hash_match', 'expected_c95_file_sha1', 'actual_c95_file_sha1', 'c95_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C96_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_CLOSURE_SEAL_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C96 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive closure seal review completed');
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
