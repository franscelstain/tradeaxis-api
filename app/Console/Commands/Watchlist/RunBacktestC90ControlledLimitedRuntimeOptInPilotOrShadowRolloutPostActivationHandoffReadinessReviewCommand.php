<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC90ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffReadinessReviewService;
use Illuminate\Console\Command;

class RunBacktestC90ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffReadinessReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review
        {--c89-artifact=storage/app/watchlist/backtest/c89-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-completion-boundary-review.json}
        {--expected-c89-hash=11ce5f21fcc027171d8073babc51212565859631}
        {--expected-c89-file-sha1=1D709D0D06F465F1F2033D4FD15DA489A5245C78}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C90 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff readiness review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC90ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffReadinessReviewService $service;

    public function __construct(?WatchlistBacktestC90ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffReadinessReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC90ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffReadinessReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C90 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff readiness review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c89-artifact'),
            (string) $this->option('expected-c89-hash'),
            (string) $this->option('expected-c89-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_handoff_readiness_review_executed', 'post_activation_handoff_readiness_review_allowed', 'post_activation_handoff_readiness_review_pass',
            'post_activation_handoff_ready', 'post_activation_completion_boundary_cleared', 'finalized_post_activation_go_decision', 'operator_go_decision',
            'primary_candidate_post_activation_handoff_ready', 'backup_candidate_post_activation_handoff_ready',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_handoff_readiness_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c89_hash', 'actual_c89_hash', 'c89_hash_match',
            'expected_c89_file_sha1', 'actual_c89_file_sha1', 'c89_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C90_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_READINESS_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C90 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff readiness review completed');
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
