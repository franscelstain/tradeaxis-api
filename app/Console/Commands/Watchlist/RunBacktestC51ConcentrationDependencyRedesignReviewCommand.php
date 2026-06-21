<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC51ConcentrationDependencyRedesignReviewService;
use Illuminate\Console\Command;

class RunBacktestC51ConcentrationDependencyRedesignReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c51-concentration-dependency-redesign-review
        {--c50-artifact=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json}
        {--expected-c50-hash=1f2b919662a395444f43403e8f7f4d0b91e146aa}
        {--c49-artifact=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json}
        {--expected-c49-hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--source-evidence-artifact=}
        {--output=storage/app/watchlist/backtest/c51-concentration-dependency-redesign-review.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C51 IS-only concentration/dependency redesign review for locked C49/C50 lineage without OOS proof or production promotion.';

    private WatchlistBacktestC51ConcentrationDependencyRedesignReviewService $service;

    public function __construct(WatchlistBacktestC51ConcentrationDependencyRedesignReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC51ConcentrationDependencyRedesignReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C51 concentration dependency redesign review started');
        }

        $options = ['overwrite' => (bool) $this->option('overwrite')];
        if ($this->option('source-evidence-artifact')) {
            $options['source_evidence_artifact'] = (string) $this->option('source-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) $this->option('c50-artifact'),
            (string) $this->option('expected-c50-hash'),
            (string) $this->option('c49-artifact'),
            (string) $this->option('expected-c49-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            $options
        );

        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c50_hash', 'actual_c50_hash', 'c50_hash_match', 'c50_status', 'c50_diagnostic_conclusion', 'c50_next_step_recommendation', 'expected_c49_hash', 'actual_c49_hash', 'c49_hash_match', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }
        foreach ((array) ($result['source_reconstruction_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('source_'.$key.'='.$this->scalar($value));
            }
        }
        foreach ((array) ($result['selected_c51_candidates_for_c52'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('selected_'.$key.'='.$this->scalar($value));
            }
        }
        foreach ((array) ($result['c52_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c52_'.$key.'='.$this->scalar($value));
            }
        }

        if (($result['status'] ?? null) === 'C51_CONCENTRATION_DEPENDENCY_REDESIGN_COMPLETED' || ($result['status'] ?? null) === 'C51_SOURCE_ROWS_NOT_EVALUABLE') {
            if ((bool) $this->option('progress')) {
                $this->line('C51 concentration dependency redesign review completed');
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
