<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review
        {--c79-artifact=storage/app/watchlist/backtest/c79-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-observation-result-review.json}
        {--expected-c79-hash=0ad7924e75a4627475600567fc6f6ad839a83961}
        {--expected-c79-file-sha1=94A900AFD592C2756E2D8165B043F25191F1ACAF}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c80-controlled-limited-runtime-opt-in-pilot-or-shadow-rollout-operator-go-no-go-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C80 controlled limited runtime opt-in pilot / shadow rollout operator go/no-go review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC80ControlledLimitedRuntimeOptInPilotOrShadowRolloutOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C80 controlled limited runtime opt-in pilot / shadow rollout operator go/no-go review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c79-artifact'),
            (string) $this->option('expected-c79-hash'),
            (string) $this->option('expected-c79-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'operator_go_no_go_review_executed', 'operator_go_no_go_review_allowed', 'operator_go_no_go_review_pass',
            'operator_go_decision', 'primary_candidate_operator_go', 'backup_candidate_operator_go',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'operator_go_no_go_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed', 'expected_c79_hash', 'actual_c79_hash', 'c79_hash_match',
            'expected_c79_file_sha1', 'actual_c79_file_sha1', 'c79_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C80_CONTROLLED_LIMITED_RUNTIME_OPT_IN_PILOT_OR_SHADOW_ROLLOUT_OPERATOR_GO_NO_GO_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C80 controlled limited runtime opt-in pilot / shadow rollout operator go/no-go review completed');
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
