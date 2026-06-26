<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review
        {--c90-artifact=storage/app/watchlist/backtest/c90-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-readiness-review.json}
        {--expected-c90-hash=a5e4bf444348c4d2e639ff1532ad2ac4b814d4af}
        {--expected-c90-file-sha1=30E924E65D9BE18BA9C55E37869424879C3EB41F}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c91-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-post-activation-handoff-finalization-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C91 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff finalization review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC91ControlledLimitedRuntimeOptInPilotOrShadowRolloutPostActivationHandoffFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C91 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c90-artifact'),
            (string) $this->option('expected-c90-hash'),
            (string) $this->option('expected-c90-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'post_activation_handoff_finalization_review_executed', 'post_activation_handoff_finalization_review_allowed', 'post_activation_handoff_finalization_review_pass',
            'post_activation_handoff_finalized', 'post_activation_handoff_ready', 'finalized_post_activation_go_decision', 'operator_go_decision',
            'primary_candidate_post_activation_handoff_finalized', 'backup_candidate_post_activation_handoff_finalized',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'post_activation_handoff_finalization_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c90_hash', 'actual_c90_hash', 'c90_hash_match',
            'expected_c90_file_sha1', 'actual_c90_file_sha1', 'c90_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C91_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_POST_ACTIVATION_HANDOFF_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C91 controlled limited runtime opt-in pilot / shadow rollout post-activation handoff finalization review completed');
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
