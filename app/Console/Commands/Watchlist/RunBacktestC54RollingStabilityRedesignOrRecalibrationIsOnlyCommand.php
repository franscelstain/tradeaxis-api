<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService;
use Illuminate\Console\Command;

class RunBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyCommand extends Command
{
    protected $signature = 'watchlist:backtest-c54-rolling-stability-redesign-or-recalibration-is-only
        {--c53-artifact=storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json}
        {--expected-c53-hash=6a1749d723e16b7efdb8aa1d7510388a9475d12c}
        {--expected-c53-file-sha1=E35FEFB78B6F1931E54169BD8AABE286CB6F08C2}
        {--c52-artifact=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json}
        {--expected-c52-hash=5dbe51c9d18b175e65cddb60336baf43d6833b72}
        {--expected-c52-file-sha1=DADE6518BFF3912D8A43D7C67073FB803F7CF878}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run the C54 IS-only rolling-stability redesign without gate relaxation, adverse-month exclusions, or OOS access.';

    private WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService $service;

    public function __construct(?WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService $service = null)
    {
        parent::__construct(); $this->service = $service ?: new WatchlistBacktestC54RollingStabilityRedesignOrRecalibrationIsOnlyService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) { $this->line('C54 IS-only rolling-stability redesign started'); }
        $result = $this->service->execute(
            (string) $this->option('c53-artifact'), (string) $this->option('expected-c53-hash'), (string) $this->option('expected-c53-file-sha1'),
            (string) $this->option('c52-artifact'), (string) $this->option('expected-c52-hash'), (string) $this->option('expected-c52-file-sha1'),
            (string) $this->option('from'), (string) $this->option('to'), (string) $this->option('output'), ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'actual_c53_hash', 'c53_hash_match', 'actual_c53_file_sha1', 'c53_file_sha1_match', 'actual_c52_hash', 'c52_hash_match', 'actual_c52_file_sha1', 'c52_file_sha1_match', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) { if (array_key_exists($key, $result)) { $this->line($key.'='.$this->scalar($result[$key])); } }
        foreach ((array) ($result['rolling_stability_redesign_summary'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line('redesign_'.$key.'='.$this->scalar($value)); } }
        if (($result['status'] ?? null) === 'C54_ROLLING_STABILITY_REDESIGN_OR_RECALIBRATION_IS_ONLY_COMPLETED') { if ((bool) $this->option('progress')) { $this->line('C54 IS-only rolling-stability redesign completed'); } return 0; }
        if (($result['message'] ?? null) !== null) { $this->error((string) $result['message']); } return 1;
    }

    private function scalar($value): string { if (is_bool($value)) { return $value ? '1' : '0'; } return $value === null ? '' : (string) $value; }
}
