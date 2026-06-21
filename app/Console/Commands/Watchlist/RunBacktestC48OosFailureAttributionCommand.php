<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC48OosFailureAttributionService;
use Illuminate\Console\Command;

class RunBacktestC48OosFailureAttributionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c48-oos-failure-attribution
        {--c47-artifact=storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json}
        {--expected-c47-hash=1c742e257847752def1f582dc24d6061a4c4e735}
        {--from=2025-05-22}
        {--to=2026-05-29}
        {--output=storage/app/watchlist/backtest/c48-oos-failure-attribution.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Attribute the failed C47 locked C44 OOS proof without OOS tuning, proof rerun, reselection, or promotion.';

    private WatchlistBacktestC48OosFailureAttributionService $service;

    public function __construct(WatchlistBacktestC48OosFailureAttributionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC48OosFailureAttributionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C48 OOS failure attribution started');
        }
        $result = $this->service->execute(
            (string) $this->option('c47-artifact'),
            (string) $this->option('expected-c47-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );

        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c47_hash', 'actual_c47_hash', 'c47_hash_match', 'c47_status', 'c47_diagnostic_conclusion', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }
        foreach ((array) ($result['source_c47_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('source_'.$key.'='.$this->scalar($value));
            }
        }
        foreach ((array) ($result['failure_attribution_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('failure_'.$key.'='.$this->scalar($value));
            }
        }
        foreach ((array) ($result['c49_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c49_'.$key.'='.$this->scalar($value));
            }
        }

        if (($result['status'] ?? null) === 'C48_OOS_FAILURE_ATTRIBUTION_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C48 OOS failure attribution completed');
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
