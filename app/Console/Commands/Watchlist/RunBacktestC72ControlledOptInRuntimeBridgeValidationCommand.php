<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService;
use Illuminate\Console\Command;

class RunBacktestC72ControlledOptInRuntimeBridgeValidationCommand extends Command
{
    protected $signature = 'watchlist:backtest-c72-controlled-opt-in-runtime-bridge-validation
        {--c71-artifact=storage/app/watchlist/backtest/c71-shadow-read-or-dry-run-runtime-validation.json}
        {--expected-c71-hash=dee0b4e6a5a17dcb7c99eccf6f54832f88aefa1f}
        {--expected-c71-file-sha1=4F2D3C8AE01F3EB0CE60D820FA78BDBD2CA2ABDB}
        {--output=storage/app/watchlist/backtest/c72-controlled-opt-in-runtime-bridge-validation.json}
        {--controlled-opt-in}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C72 controlled opt-in runtime bridge validation without live deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService $service;

    public function __construct(?WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC72ControlledOptInRuntimeBridgeValidationService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C72 controlled opt-in runtime bridge validation started');
        }

        $result = $this->service->execute(
            (string) $this->option('c71-artifact'),
            (string) $this->option('expected-c71-hash'),
            (string) $this->option('expected-c71-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'controlled_opt_in' => (bool) $this->option('controlled-opt-in'),
            ]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_opt_in_runtime_bridge_validation_executed',
            'controlled_opt_in_runtime_bridge_validation_allowed',
            'controlled_opt_in_runtime_bridge_validation_pass',
            'production_ready', 'production_catalog_runtime_wired', 'controlled_opt_in_runtime_bridge_active',
            'production_deployment_allowed', 'production_deployment_executed', 'plan_confirm_mutation_allowed',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'expected_c71_hash', 'actual_c71_hash', 'c71_hash_match',
            'expected_c71_file_sha1', 'actual_c71_file_sha1', 'c71_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['c73_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c73_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C72_CONTROLLED_OPT_IN_RUNTIME_BRIDGE_VALIDATION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C72 controlled opt-in runtime bridge validation completed');
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
