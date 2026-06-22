<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService;
use Illuminate\Console\Command;

class RunBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyCommand extends Command
{
    protected $signature = 'watchlist:backtest-c62-pre-lock-review-for-c61-signal-quality-candidates-is-only
        {--c61-artifact=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json}
        {--expected-c61-hash=40d2c4a4f9f1310f9165cdfb4abdd45ff94cb0c8}
        {--expected-c61-file-sha1=DEA3C807813DE81DB6776AB2C441C945D4E98EC6}
        {--c60-artifact=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json}
        {--expected-c60-hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705}
        {--expected-c60-file-sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c62-pre-lock-review-for-c61-signal-quality-candidates-is-only.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C62 IS-only pre-lock review for C61 signal-quality candidates without OOS access.';

    private WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService $service;

    public function __construct(?WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC62PreLockReviewForC61SignalQualityCandidatesIsOnlyService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C62 IS-only pre-lock review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c61-artifact'),
            (string) $this->option('expected-c61-hash'),
            (string) $this->option('expected-c61-file-sha1'),
            (string) $this->option('c60-artifact'),
            (string) $this->option('expected-c60-hash'),
            (string) $this->option('expected-c60-file-sha1'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready',
            'direct_oos_proof_recommended', 'oos_proof_unlocked', 'pre_oos_unlocked',
            'expected_c61_hash', 'actual_c61_hash', 'c61_hash_match',
            'expected_c61_file_sha1', 'actual_c61_file_sha1', 'c61_file_sha1_match',
            'expected_c60_hash', 'actual_c60_hash', 'c60_hash_match',
            'expected_c60_file_sha1', 'actual_c60_file_sha1', 'c60_file_sha1_match',
            'c61_status', 'c61_reason_code', 'c60_status', 'c60_reason_code',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['pre_lock_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('pre_lock_'.$key.'='.$this->scalar($value));
            }
        }

        foreach ((array) ($result['c63_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c63_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C62_PRE_LOCK_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C62 IS-only pre-lock review completed');
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
