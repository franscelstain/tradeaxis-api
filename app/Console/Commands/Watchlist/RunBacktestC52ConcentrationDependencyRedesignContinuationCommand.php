<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService;
use Illuminate\Console\Command;

class RunBacktestC52ConcentrationDependencyRedesignContinuationCommand extends Command
{
    protected $signature = 'watchlist:backtest-c52-concentration-dependency-redesign-continuation
        {--c51-artifact=storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json}
        {--expected-c51-hash=a786034b8e344207592e58efe262287102b0ef36}
        {--c50-artifact=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json}
        {--expected-c50-hash=1f2b919662a395444f43403e8f7f4d0b91e146aa}
        {--c49-artifact=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json}
        {--expected-c49-hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--source-evidence-artifact=}
        {--output=storage/app/watchlist/backtest/c52-concentration-dependency-redesign-continuation.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C52 IS-only sector metadata reconstruction and second-pass concentration/dependency redesign continuation.';

    private WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService $service;

    public function __construct(WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC52ConcentrationDependencyRedesignContinuationService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) { $this->line('C52 concentration dependency redesign continuation started'); }
        $options = ['overwrite' => (bool) $this->option('overwrite')];
        if ($this->option('source-evidence-artifact')) { $options['source_evidence_artifact'] = (string) $this->option('source-evidence-artifact'); }

        $result = $this->service->execute(
            (string) $this->option('c51-artifact'), (string) $this->option('expected-c51-hash'),
            (string) $this->option('c50-artifact'), (string) $this->option('expected-c50-hash'),
            (string) $this->option('c49-artifact'), (string) $this->option('expected-c49-hash'),
            (string) $this->option('from'), (string) $this->option('to'), (string) $this->option('output'), $options
        );

        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c51_hash', 'actual_c51_hash', 'c51_hash_match', 'c51_status', 'c51_diagnostic_conclusion', 'c51_next_step_recommendation', 'expected_c50_hash', 'actual_c50_hash', 'c50_hash_match', 'expected_c49_hash', 'actual_c49_hash', 'c49_hash_match', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) { $this->line($key.'='.$this->scalar($result[$key])); }
        }
        foreach ((array) ($result['sector_metadata_reconstruction_summary'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line('sector_'.$key.'='.$this->scalar($value)); } }
        foreach ((array) ($result['selected_c52_candidates_for_c53'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line('selected_'.$key.'='.$this->scalar($value)); } }
        foreach ((array) ($result['c53_readiness_decision'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line('c53_'.$key.'='.$this->scalar($value)); } }

        if (in_array($result['status'] ?? null, ['C52_CONCENTRATION_DEPENDENCY_REDESIGN_CONTINUATION_COMPLETED', 'C52_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED_WITH_SECTOR_NOT_EVALUABLE', 'C52_SOURCE_ROWS_NOT_EVALUABLE'], true)) {
            if ((bool) $this->option('progress')) { $this->line('C52 concentration dependency redesign continuation completed'); }
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
