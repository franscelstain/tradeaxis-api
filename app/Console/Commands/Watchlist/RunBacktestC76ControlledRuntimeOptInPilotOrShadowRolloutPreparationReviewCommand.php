<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService;
use Illuminate\Console\Command;

class RunBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review
        {--c75-artifact=storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json}
        {--expected-c75-hash=cd1346cd05ab5471a947fcb5304e0f347a4881eb}
        {--expected-c75-file-sha1=668043836BA1DB8FF50EC69DF0560988E633CF75}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c76-controlled-runtime-opt-in-pilot-or-shadow-rollout-preparation-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C76 controlled runtime opt-in pilot / shadow rollout preparation review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService $service;

    public function __construct(?WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC76ControlledRuntimeOptInPilotOrShadowRolloutPreparationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C76 controlled runtime opt-in pilot / shadow rollout preparation review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c75-artifact'),
            (string) $this->option('expected-c75-hash'),
            (string) $this->option('expected-c75-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_runtime_opt_in_pilot_preparation_review_executed',
            'controlled_runtime_opt_in_pilot_preparation_review_allowed',
            'controlled_runtime_opt_in_pilot_preparation_review_pass',
            'controlled_shadow_rollout_preparation_review_executed',
            'controlled_shadow_rollout_preparation_review_allowed',
            'controlled_shadow_rollout_preparation_review_pass',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'controlled_pilot_context_persisted_to_live_runtime',
            'controlled_shadow_context_persisted_to_live_runtime', 'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c75_hash', 'actual_c75_hash', 'c75_hash_match',
            'expected_c75_file_sha1', 'actual_c75_file_sha1', 'c75_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C76_CONTROLLED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_PREPARATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C76 controlled runtime opt-in pilot / shadow rollout preparation review completed');
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
