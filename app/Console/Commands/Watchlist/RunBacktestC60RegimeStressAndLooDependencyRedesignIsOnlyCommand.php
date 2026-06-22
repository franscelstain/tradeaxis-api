<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService;
use Illuminate\Console\Command;

class RunBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyCommand extends Command
{
    protected $signature = 'watchlist:backtest-c60-regime-stress-and-loo-dependency-redesign-is-only
        {--c59-artifact=storage/app/watchlist/backtest/c59-loss-cluster-or-branch-bucket-redesign-continuation-is-only.json}
        {--expected-c59-hash=7ebd6f74bc90ffac358b410244d90b3c7c3c5456}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c60-regime-stress-and-loo-dependency-redesign-is-only.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C60 IS-only regime stress and LOO dependency redesign from locked C59 evidence without OOS access.';

    private WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService $service;

    public function __construct(?WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC60RegimeStressAndLooDependencyRedesignIsOnlyService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) { $this->line('C60 IS-only regime stress and LOO dependency redesign started'); }
        $result = $this->service->execute(
            (string) $this->option('c59-artifact'),
            (string) $this->option('expected-c59-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c59_hash', 'actual_c59_hash', 'actual_c59_stable_hash', 'actual_c59_payload_hash', 'actual_c59_documented_hash', 'c59_hash_match', 'c59_status', 'c59_diagnostic_conclusion', 'c59_next_step_recommendation', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) { $this->line($key.'='.$this->scalar($result[$key])); }
        }
        foreach ((array) ($result['c61_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) { $this->line('c61_'.$key.'='.$this->scalar($value)); }
        }
        if (($result['status'] ?? null) === 'C60_REGIME_STRESS_AND_LOO_DEPENDENCY_REDESIGN_IS_ONLY_COMPLETED') {
            if ((bool) $this->option('progress')) { $this->line('C60 IS-only regime stress and LOO dependency redesign completed'); }
            return 0;
        }
        if (($result['message'] ?? null) !== null) { $this->error((string) $result['message']); }
        return 1;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) { return $value ? '1' : '0'; }
        return $value === null ? '' : (string) $value;
    }
}
