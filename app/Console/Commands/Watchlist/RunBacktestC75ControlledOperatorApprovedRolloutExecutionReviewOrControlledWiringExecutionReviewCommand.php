<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService;
use Illuminate\Console\Command;

class RunBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review
        {--c74-artifact=storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json}
        {--expected-c74-hash=8958e1fcec798fbd364642864b0a9d0c21bd8f93}
        {--expected-c74-file-sha1=D4C2EF90B533BED11F6902E75141BE5774E947BE}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c75-controlled-operator-approved-rollout-execution-review-or-controlled-wiring-execution-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C75 controlled operator-approved rollout execution / controlled wiring execution review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService $service;

    public function __construct(?WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC75ControlledOperatorApprovedRolloutExecutionReviewOrControlledWiringExecutionReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C75 controlled operator-approved rollout execution / controlled wiring execution review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c74-artifact'),
            (string) $this->option('expected-c74-hash'),
            (string) $this->option('expected-c74-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_operator_approved_rollout_execution_review_executed',
            'controlled_operator_approved_rollout_execution_review_allowed',
            'controlled_operator_approved_rollout_execution_review_pass',
            'controlled_wiring_execution_review_executed',
            'controlled_wiring_execution_review_allowed',
            'controlled_wiring_execution_review_pass',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'controlled_wiring_context_persisted_to_live_runtime',
            'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed', 'expected_c74_hash', 'actual_c74_hash', 'c74_hash_match',
            'expected_c74_file_sha1', 'actual_c74_file_sha1', 'c74_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C75_CONTROLLED_OPERATOR_APPROVED_ROLLOUT_EXECUTION_REVIEW_OR_CONTROLLED_WIRING_EXECUTION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C75 controlled operator-approved rollout execution / controlled wiring execution review completed');
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
