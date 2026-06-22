<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService;
use Illuminate\Console\Command;

class RunBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyCommand extends Command
{
    protected $signature = 'watchlist:backtest-c61-signal-quality-rebuild-for-weak-regime-is-only
        {--c60-artifact=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json}
        {--expected-c60-hash=25a32ee9c4cb77ecc29103c86a1abf0826aea705}
        {--expected-c60-file-sha1=1FA933157B61ECB4554CE6C76B0F2B314F19DB0F}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c61-signal-quality-rebuild-for-weak-regime-is-only.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C61 IS-only signal-quality rebuild for weak regime from locked C60 evidence without OOS access.';

    private WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService $service;

    public function __construct(?WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC61SignalQualityRebuildForWeakRegimeIsOnlyService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C61 IS-only weak-regime signal-quality rebuild started');
        }

        $result = $this->service->execute(
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
            'expected_c60_hash', 'actual_c60_hash', 'c60_hash_match',
            'expected_c60_file_sha1', 'actual_c60_file_sha1', 'c60_file_sha1_match',
            'c60_status', 'c60_reason_code', 'c60_next_step_recommendation',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['c62_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c62_'.$key.'='.$this->scalar($value));
            }
        }

        if (($result['status'] ?? null) === 'C61_SIGNAL_QUALITY_REBUILD_FOR_WEAK_REGIME_IS_ONLY_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C61 IS-only weak-regime signal-quality rebuild completed');
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
