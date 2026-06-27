<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService;
use Illuminate\Console\Command;

class RunBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review
        {--c94-artifact=storage/app/watchlist/backtest/c94-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-review.json}
        {--expected-c94-hash=2a17baceb2e899f93fd1d658bd6a7b020ef9b252}
        {--expected-c94-file-sha1=0D81162ED0DF53DC434B2131E34106F7203119D6}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c95-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-audit-archive-completion-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C95 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive completion review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService $service;

    public function __construct(?WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC95ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationAuditArchiveCompletionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C95 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive completion review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c94-artifact'),
            (string) $this->option('expected-c94-hash'),
            (string) $this->option('expected-c94-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_audit_archive_completion_review_executed', 'post_activation_audit_archive_completion_review_allowed', 'post_activation_audit_archive_completion_review_pass',
            'post_activation_audit_archive_completed', 'audit_archive_completed', 'c94_audit_archived', 'archive_completion_manifest_created',
            'primary_candidate_audit_archive_completed', 'backup_candidate_audit_archive_completed', 'comparator_candidate_audit_archive_completed', 'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_audit_archive_context_persisted_to_live_runtime', 'post_activation_audit_archive_completion_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'pilot_runtime_active', 'shadow_runtime_active', 'runtime_bridge_active',
            'expected_c94_hash', 'actual_c94_hash', 'c94_hash_match', 'expected_c94_file_sha1', 'actual_c94_file_sha1', 'c94_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C95_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_AUDIT_ARCHIVE_COMPLETION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C95 controlled limited runtime opt-in pilot / shadow rollout post-activation audit archive completion review completed');
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
