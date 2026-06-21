<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService;
use Illuminate\Console\Command;

class RunBacktestC56RollingStabilityRedesignContinuationIsOnlyCommand extends Command
{
    protected $signature = 'watchlist:backtest-c56-rolling-stability-redesign-continuation-is-only
        {--c55-artifact=storage/app/watchlist/backtest/c55-rolling-stability-redesign-continuation-is-only.json}
        {--expected-c55-hash=a4145d6f356e678d0dadf95be5d356198ebfed79}
        {--expected-c55-file-sha1=18875FCAD7FD7CDA6607BB09A60917E853E68D2B}
        {--c54-artifact=storage/app/watchlist/backtest/c54-rolling-stability-redesign-or-recalibration-is-only.json}
        {--expected-c54-hash=8c71a4352a1024dbe985e0f0bb6329f5e1545150}
        {--expected-c54-file-sha1=75410BB1A30A32FFFF9661CAD6818C13E044F7E5}
        {--c53-artifact=storage/app/watchlist/backtest/c53-is-evidence-expansion-for-c52-redesign.json}
        {--expected-c53-hash=6a1749d723e16b7efdb8aa1d7510388a9475d12c}
        {--expected-c53-file-sha1=E35FEFB78B6F1931E54169BD8AABE286CB6F08C2}
        {--c52-artifact=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json}
        {--expected-c52-hash=5dbe51c9d18b175e65cddb60336baf43d6833b72}
        {--expected-c52-file-sha1=DADE6518BFF3912D8A43D7C67073FB803F7CF878}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c56-rolling-stability-redesign-continuation-is-only.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C56 IS-only rolling-stability redesign continuation from locked C55/C54/C53/C52 lineage without OOS access.';

    private WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService $service;

    public function __construct(?WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC56RollingStabilityRedesignContinuationIsOnlyService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) { $this->line('C56 IS-only rolling-stability redesign continuation started'); }
        $result = $this->service->execute(
            (string) $this->option('c55-artifact'), (string) $this->option('expected-c55-hash'), (string) $this->option('expected-c55-file-sha1'),
            (string) $this->option('c54-artifact'), (string) $this->option('expected-c54-hash'), (string) $this->option('expected-c54-file-sha1'),
            (string) $this->option('c53-artifact'), (string) $this->option('expected-c53-hash'), (string) $this->option('expected-c53-file-sha1'),
            (string) $this->option('c52-artifact'), (string) $this->option('expected-c52-hash'), (string) $this->option('expected-c52-file-sha1'),
            (string) $this->option('from'), (string) $this->option('to'), (string) $this->option('output'), ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c55_hash', 'actual_c55_hash', 'c55_hash_match', 'expected_c55_file_sha1', 'actual_c55_file_sha1', 'c55_file_sha1_match', 'expected_c54_hash', 'actual_c54_hash', 'c54_hash_match', 'expected_c54_file_sha1', 'actual_c54_file_sha1', 'c54_file_sha1_match', 'expected_c53_hash', 'actual_c53_hash', 'c53_hash_match', 'expected_c53_file_sha1', 'actual_c53_file_sha1', 'c53_file_sha1_match', 'expected_c52_hash', 'actual_c52_hash', 'c52_hash_match', 'expected_c52_file_sha1', 'actual_c52_file_sha1', 'c52_file_sha1_match', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) { $this->line($key.'='.$this->scalar($result[$key])); }
        }
        foreach ((array) ($result['c57_readiness_decision'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line('c57_'.$key.'='.$this->scalar($value)); } }
        if (($result['status'] ?? null) === 'C56_ROLLING_STABILITY_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED') {
            if ((bool) $this->option('progress')) { $this->line('C56 IS-only rolling-stability redesign continuation completed'); }
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
