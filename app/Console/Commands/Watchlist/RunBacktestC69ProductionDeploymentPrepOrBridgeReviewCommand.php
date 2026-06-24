<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService;
use Illuminate\Console\Command;

class RunBacktestC69ProductionDeploymentPrepOrBridgeReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c69-production-deployment-prep-or-bridge-review
        {--c68-artifact=storage/app/watchlist/backtest/c68-production-catalog-activation-execution-review.json}
        {--expected-c68-hash=54145854758e22115e4b65a297e4c157d94c638d}
        {--expected-c68-file-sha1=209E3225F37015DA348EC2DA9A0D6A3FFCC6E4F7}
        {--c67-artifact=storage/app/watchlist/backtest/c67-production-catalog-activation-review.json}
        {--expected-c67-hash=5e3ba8ac20c810a36a7928ad1f201c82143ac72f}
        {--expected-c67-file-sha1=CB98A7B5B4B5F0CCCEDEF0C7B5BDC8CB3FE940E6}
        {--c66-artifact=storage/app/watchlist/backtest/c66-production-lock-review.json}
        {--expected-c66-hash=9ef0c2eed94f2ac9e6e8e348e93774c563f8e6d4}
        {--expected-c66-file-sha1=11936FC807140E9B0A18FD00B543B03C8AE2950C}
        {--c65-artifact=storage/app/watchlist/backtest/c65-production-pre-lock-review.json}
        {--expected-c65-hash=f08da5acc87ccbe0d88c39423c4321496230b01b}
        {--expected-c65-file-sha1=115201C1F44C7C420ABA3251435F21B870EF9AE6}
        {--c64-artifact=storage/app/watchlist/backtest/c64-pre-oos-or-oos-proof-execution.json}
        {--expected-c64-hash=767d860956e0f27eeedccdc30f73aa1d0e5a415b}
        {--expected-c64-file-sha1=032C7BA7435799D83CC06EEDBC463A9AF2B123B3}
        {--c63-artifact=storage/app/watchlist/backtest/c63-pre-oos-unlock-review-is-only.json}
        {--expected-c63-hash=e98f1386928b36ee367728ceeec4de4344e1f3be}
        {--expected-c63-file-sha1=24C7EE585A165DA41E8FC22538A68145247C68B4}
        {--c62-artifact=storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json}
        {--expected-c62-hash=d3a089b9b986838764d517682035d76e0bb4112d}
        {--expected-c62-file-sha1=8DF1649BC72233D119581A802F9E41BA9BEBF12E}
        {--c61-artifact=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json}
        {--expected-c61-hash=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8}
        {--expected-c61-file-sha1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6}
        {--c60-artifact=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json}
        {--expected-c60-hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705}
        {--expected-c60-file-sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F}
        {--output=storage/app/watchlist/backtest/c69-production-deployment-prep-or-bridge-review.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C69 production deployment prep / bridge review without deployment or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService $service;

    public function __construct(?WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC69ProductionDeploymentPrepOrBridgeReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C69 production deployment prep / bridge review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c68-artifact'),
            (string) $this->option('expected-c68-hash'),
            (string) $this->option('expected-c68-file-sha1'),
            (string) $this->option('c67-artifact'),
            (string) $this->option('expected-c67-hash'),
            (string) $this->option('expected-c67-file-sha1'),
            (string) $this->option('c66-artifact'),
            (string) $this->option('expected-c66-hash'),
            (string) $this->option('expected-c66-file-sha1'),
            (string) $this->option('c65-artifact'),
            (string) $this->option('expected-c65-hash'),
            (string) $this->option('expected-c65-file-sha1'),
            (string) $this->option('c64-artifact'),
            (string) $this->option('expected-c64-hash'),
            (string) $this->option('expected-c64-file-sha1'),
            (string) $this->option('c63-artifact'),
            (string) $this->option('expected-c63-hash'),
            (string) $this->option('expected-c63-file-sha1'),
            (string) $this->option('c62-artifact'),
            (string) $this->option('expected-c62-hash'),
            (string) $this->option('expected-c62-file-sha1'),
            (string) $this->option('c61-artifact'),
            (string) $this->option('expected-c61-hash'),
            (string) $this->option('expected-c61-file-sha1'),
            (string) $this->option('c60-artifact'),
            (string) $this->option('expected-c60-hash'),
            (string) $this->option('expected-c60-file-sha1'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready',
            'production_deployment_prep_or_bridge_review_executed', 'production_deployment_prep_or_bridge_review_pass',
            'production_catalog_lock_allowed', 'production_catalog_activation_allowed',
            'production_catalog_activation_execution_allowed', 'production_catalog_activation_execution_performed',
            'production_catalog_activated', 'production_catalog_runtime_wired',
            'production_deployment_prep_allowed', 'production_deployment_execution_review_allowed',
            'production_deployment_allowed', 'production_deployment_executed',
            'plan_confirm_wiring_prep_allowed', 'plan_confirm_mutation_allowed',
            'plan_confirm_mutated', 'plan_confirm_runtime_reads_activated_catalog',
            'expected_c68_hash', 'actual_c68_hash', 'c68_hash_match',
            'expected_c68_file_sha1', 'actual_c68_file_sha1', 'c68_file_sha1_match',
            'expected_c67_hash', 'actual_c67_hash', 'c67_hash_match',
            'expected_c67_file_sha1', 'actual_c67_file_sha1', 'c67_file_sha1_match',
            'expected_c66_hash', 'actual_c66_hash', 'c66_hash_match',
            'expected_c66_file_sha1', 'actual_c66_file_sha1', 'c66_file_sha1_match',
            'expected_c65_hash', 'actual_c65_hash', 'c65_hash_match',
            'expected_c65_file_sha1', 'actual_c65_file_sha1', 'c65_file_sha1_match',
            'expected_c64_hash', 'actual_c64_hash', 'c64_hash_match',
            'expected_c64_file_sha1', 'actual_c64_file_sha1', 'c64_file_sha1_match',
            'expected_c63_hash', 'actual_c63_hash', 'c63_hash_match',
            'expected_c63_file_sha1', 'actual_c63_file_sha1', 'c63_file_sha1_match',
            'expected_c62_hash', 'actual_c62_hash', 'c62_hash_match',
            'expected_c62_file_sha1', 'actual_c62_file_sha1', 'c62_file_sha1_match',
            'expected_c61_hash', 'actual_c61_hash', 'c61_hash_match',
            'expected_c61_file_sha1', 'actual_c61_file_sha1', 'c61_file_sha1_match',
            'expected_c60_hash', 'actual_c60_hash', 'c60_hash_match',
            'expected_c60_file_sha1', 'actual_c60_file_sha1', 'c60_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['deployment_bridge_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('deployment_bridge_'.$key.'='.$this->scalar($value));
            }
        }

        foreach ((array) ($result['c70_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c70_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C69_PRODUCTION_DEPLOYMENT_PREP_OR_BRIDGE_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C69 production deployment prep / bridge review completed');
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
        return $value === null ? '' : (string) $value;
    }
}
