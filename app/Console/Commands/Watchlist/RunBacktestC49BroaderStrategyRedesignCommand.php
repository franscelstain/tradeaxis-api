<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC49BroaderStrategyRedesignService;
use Illuminate\Console\Command;

class RunBacktestC49BroaderStrategyRedesignCommand extends Command
{
    protected $signature = 'watchlist:backtest-c49-broader-strategy-redesign
        {--c48-artifact=storage/app/watchlist/backtest/c48-oos-failure-attribution.json}
        {--expected-c48-hash=1d6ac8e56aa7449877f95fe4fdbb845810bfb5b7}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--source-evidence-artifact=}
        {--output=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Build C49 IS-only broader strategy redesign candidates from C48 failure attribution without OOS tuning or OOS proof.';

    private WatchlistBacktestC49BroaderStrategyRedesignService $service;

    public function __construct(WatchlistBacktestC49BroaderStrategyRedesignService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC49BroaderStrategyRedesignService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C49 IS broader strategy redesign started');
        }

        $options = ['overwrite' => (bool) $this->option('overwrite')];
        if ($this->option('source-evidence-artifact')) {
            $options['source_evidence_artifact'] = (string) $this->option('source-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) $this->option('c48-artifact'),
            (string) $this->option('expected-c48-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            $options
        );

        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c48_hash', 'actual_c48_hash', 'c48_hash_match', 'c48_status', 'c48_diagnostic_conclusion', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }
        foreach ((array) ($result['source_universe_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('source_'.$key.'='.$this->scalar($value));
            }
        }
        foreach ((array) ($result['selected_c49_candidates_for_c50'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('selected_'.$key.'='.$this->scalar($value));
            }
        }
        foreach ((array) ($result['c50_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c50_'.$key.'='.$this->scalar($value));
            }
        }

        if (($result['status'] ?? null) === 'C49_BROADER_STRATEGY_REDESIGN_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C49 IS broader strategy redesign completed');
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
        if (is_bool($value)) { return $value ? '1' : '0'; }
        return $value === null ? '' : (string) $value;
    }
}
