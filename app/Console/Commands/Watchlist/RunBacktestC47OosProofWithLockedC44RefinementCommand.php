<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC47OosProofWithLockedC44RefinementService;
use Illuminate\Console\Command;

class RunBacktestC47OosProofWithLockedC44RefinementCommand extends Command
{
    protected $signature = 'watchlist:backtest-c47-oos-proof-with-locked-c44-refinement
        {--c46-artifact=storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json}
        {--expected-c46-hash=d531dd5b911f55d8824ac514ccc7600470a076bd}
        {--c44-artifact=storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json}
        {--expected-c44-hash=606cd3109371b0d99419082daee18ff65f1cd99b}
        {--oos-source-artifact=storage/app/watchlist/backtest/c29-oos-proof-c28-g05.json}
        {--expected-oos-source-hash=c02add8f2cc8af53bdb3f0cf9d0c7d90d63e1dd9}
        {--from=2025-05-22}
        {--to=2026-05-29}
        {--output=storage/app/watchlist/backtest/c47-oos-proof-with-locked-c44-refinement.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run the one-shot reserved-window OOS proof for the locked C44 refinement without tuning, reselection, or promotion.';

    private WatchlistBacktestC47OosProofWithLockedC44RefinementService $service;

    public function __construct(WatchlistBacktestC47OosProofWithLockedC44RefinementService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC47OosProofWithLockedC44RefinementService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C47 locked one-shot OOS proof started');
        }
        $result = $this->service->execute(
            (string) $this->option('c46-artifact'),
            (string) $this->option('expected-c46-hash'),
            (string) $this->option('c44-artifact'),
            (string) $this->option('expected-c44-hash'),
            (string) $this->option('oos-source-artifact'),
            (string) $this->option('expected-oos-source-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c46_hash', 'actual_c46_hash', 'c46_hash_match', 'expected_c44_hash', 'actual_c44_hash', 'c44_hash_match', 'expected_oos_source_hash', 'actual_oos_source_hash', 'oos_source_hash_match', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }
        foreach (($result['target_oos_result'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line($key.'='.$this->scalar($value));
            }
        }
        foreach (($result['comparison_vs_baseline'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line($key.'='.$this->scalar($value));
            }
        }
        foreach (($result['oos_gate'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line($key.'='.$this->scalar($value));
            }
        }
        if (in_array(($result['status'] ?? null), ['C47_OOS_PROOF_PASSED_NOT_PRODUCTION_READY', 'C47_OOS_PROOF_FAILED'], true)) {
            if ((bool) $this->option('progress')) {
                $this->line('C47 locked one-shot OOS proof completed');
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
