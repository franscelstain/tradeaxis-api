<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService;
use Illuminate\Console\Command;

class RunBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review
        {--c73-artifact=storage/app/watchlist/backtest/c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation.json}
        {--expected-c73-hash=34f1f84a4261da7ce1cb9d17a1bf33dfb1458281}
        {--expected-c73-file-sha1=BF18CAA2654D5A7DE5419DE5DAF42E0B55D73CC9}
        {--output=storage/app/watchlist/backtest/c74-controlled-operator-reviewed-rollout-gate-or-deployment-readiness-review.json}
        {--operator-reviewed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C74 controlled operator-reviewed rollout gate / deployment readiness review without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService $service;

    public function __construct(?WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC74ControlledOperatorReviewedRolloutGateOrDeploymentReadinessReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C74 controlled operator-reviewed rollout gate / deployment readiness review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c73-artifact'),
            (string) $this->option('expected-c73-hash'),
            (string) $this->option('expected-c73-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_reviewed' => (bool) $this->option('operator-reviewed'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_operator_reviewed_rollout_gate_validation_executed',
            'controlled_operator_reviewed_rollout_gate_validation_allowed',
            'controlled_operator_reviewed_rollout_gate_validation_pass',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'controlled_rollout_active', 'production_deployment_allowed',
            'production_deployment_executed', 'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog', 'live_plan_confirm_rollout_allowed',
            'live_plan_confirm_rollout_executed', 'expected_c73_hash', 'actual_c73_hash', 'c73_hash_match',
            'expected_c73_file_sha1', 'actual_c73_file_sha1', 'c73_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['c75_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c75_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C74_CONTROLLED_OPERATOR_REVIEWED_ROLLOUT_GATE_OR_DEPLOYMENT_READINESS_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C74 controlled operator-reviewed rollout gate / deployment readiness review completed');
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
