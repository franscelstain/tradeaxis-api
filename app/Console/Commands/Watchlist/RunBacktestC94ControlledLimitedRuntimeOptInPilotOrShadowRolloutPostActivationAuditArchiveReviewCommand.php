<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewService;
use Illuminate\Console\Command;

class RunBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review
        {--c93-artifact=storage/app/watchlist/backtest/c93-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-closure-seal-review.json}
        {--expected-c93-hash=bd19ac672c30ea183fc46534acd6e976515c3453}
        {--expected-c93-file-sha1=F71799E201B9C71A79094D81AFF786FCACDF9E1D}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C94 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewService $service;

    public function __construct(?WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC94ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C94 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c93-artifact'),
            (string) $this->option('expected-c93-hash'),
            (string) $this->option('expected-c93-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_audit_archive_review_executed', 'post_activation_audit_archive_review_allowed', 'post_activation_audit_archive_review_pass',
            'post_activation_audit_archived', 'audit_archived', 'c93_closure_sealed', 'archive_manifest_created',
            'primary_candidate_audit_archived', 'backup_candidate_audit_archived', 'comparator_candidate_audit_archived', 'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_audit_archive_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'expected_c93_hash', 'actual_c93_hash', 'c93_hash_match', 'expected_c93_file_sha1', 'actual_c93_file_sha1', 'c93_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C94_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C94 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive review completed');
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
