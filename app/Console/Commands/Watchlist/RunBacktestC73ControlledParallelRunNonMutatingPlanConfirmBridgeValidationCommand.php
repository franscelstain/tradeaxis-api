<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService;
use Illuminate\Console\Command;

class RunBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationCommand extends Command
{
    protected $signature = 'watchlist:backtest-c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation
        {--c72-artifact=storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json}
        {--expected-c72-hash=df3ee58a47572900d42b91d8348f0d6ea9ad1965}
        {--expected-c72-file-sha1=1ADF2C81797140A7A756B7A4EB02815AF1CBE75E}
        {--output=storage/app/watchlist/backtest/c73-controlled-parallel-run-non-mutating-plan-confirm-bridge-validation.json}
        {--controlled-parallel-run}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C73 controlled parallel-run non-mutating PLAN/CONFIRM bridge validation without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService $service;

    public function __construct(?WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC73ControlledParallelRunNonMutatingPlanConfirmBridgeValidationService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C73 controlled parallel-run non-mutating PLAN/CONFIRM bridge validation started');
        }

        $result = $this->service->execute(
            (string) $this->option('c72-artifact'),
            (string) $this->option('expected-c72-hash'),
            (string) $this->option('expected-c72-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'controlled_parallel_run' => (bool) $this->option('controlled-parallel-run'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_executed',
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_allowed',
            'controlled_parallel_run_non_mutating_plan_confirm_bridge_validation_pass',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'controlled_parallel_run_active', 'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'expected_c72_hash', 'actual_c72_hash', 'c72_hash_match',
            'expected_c72_file_sha1', 'actual_c72_file_sha1', 'c72_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['c74_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c74_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C73_CONTROLLED_PARALLEL_RUN_NON_MUTATING_PLAN_CONFIRM_BRIDGE_VALIDATION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C73 controlled parallel-run non-mutating PLAN/CONFIRM bridge validation completed');
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
