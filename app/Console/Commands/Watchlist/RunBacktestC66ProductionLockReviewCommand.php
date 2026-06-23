<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC66ProductionLockReviewService;
use Illuminate\Console\Command;

class RunBacktestC66ProductionLockReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c66-production-lock-review
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
        {--output=storage/app/watchlist/backtest/c66-production-lock-review.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C66 production lock review from locked C65 evidence without redesign, activation, deployment, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC66ProductionLockReviewService $service;

    public function __construct(?WatchlistBacktestC66ProductionLockReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC66ProductionLockReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C66 production lock review started');
        }

        $result = $this->service->execute(
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
            'production_lock_review_executed', 'production_lock_review_pass',
            'production_catalog_lock_allowed', 'production_catalog_activation_allowed',
            'production_deployment_allowed', 'plan_confirm_mutation_allowed',
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

        foreach ((array) ($result['production_lock_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('production_lock_'.$key.'='.$this->scalar($value));
            }
        }

        foreach ((array) ($result['c67_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c67_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C66_PRODUCTION_LOCK_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C66 production lock review completed');
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
