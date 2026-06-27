<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review
        {--c91-artifact=storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json}
        {--expected-c91-hash=17731873369cf69b5083b2f80b15101de71851f2}
        {--expected-c91-file-sha1=D6306D0EB132FEEA5535B99AD4A4BA9099D80DF6}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c92-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-completion-boundary-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C92 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff completion boundary review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC92ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffCompletionBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C92 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff completion boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c91-artifact'),
            (string) $this->option('expected-c91-hash'),
            (string) $this->option('expected-c91-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_handoff_completion_boundary_review_executed', 'post_activation_handoff_completion_boundary_review_allowed', 'post_activation_handoff_completion_boundary_review_pass',
            'post_activation_handoff_completion_boundary_cleared', 'boundary_cleared', 'post_activation_handoff_finalized', 'post_activation_handoff_ready', 'boundary_go_decision', 'operator_go_decision',
            'primary_candidate_boundary_cleared', 'backup_candidate_boundary_cleared', 'comparator_candidate_boundary_cleared', 'a01_remains_comparator_only',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_handoff_completion_boundary_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c91_hash', 'actual_c91_hash', 'c91_hash_match',
            'expected_c91_file_sha1', 'actual_c91_file_sha1', 'c91_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C92_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_COMPLETION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C92 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff completion boundary review completed');
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
