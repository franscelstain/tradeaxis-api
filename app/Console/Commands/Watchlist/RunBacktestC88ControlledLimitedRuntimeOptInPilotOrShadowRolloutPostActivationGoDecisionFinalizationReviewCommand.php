<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review
        {--c87-artifact=storage/app/watchlist/backtest/c87-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-operator-go-no-go-review.json}
        {--expected-c87-hash=4c319158e1e90bc7e491636361551ed212848c5d}
        {--expected-c87-file-sha1=EBEA22AD5E07792D0D5EE6F71A317966EFF546D8}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c88-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-go-decision-finalization-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C88 controlled limited runtime opt-in pilot / shadow rollout post-activation GO decision finalization review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC88ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C88 controlled limited runtime opt-in pilot / shadow rollout post-activation GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c87-artifact'),
            (string) $this->option('expected-c87-hash'),
            (string) $this->option('expected-c87-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_go_decision_finalization_review_executed', 'post_activation_go_decision_finalization_review_allowed', 'post_activation_go_decision_finalization_review_pass',
            'post_activation_go_decision_finalized', 'finalized_post_activation_go_decision', 'operator_go_decision',
            'primary_candidate_post_activation_go_finalized', 'backup_candidate_post_activation_go_finalized',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_go_decision_finalization_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c87_hash', 'actual_c87_hash', 'c87_hash_match',
            'expected_c87_file_sha1', 'actual_c87_file_sha1', 'c87_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C88_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C88 controlled limited runtime opt-in pilot / shadow rollout post-activation GO decision finalization review completed');
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
