<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC50IsValidationAntiOverfitCheckService;
use Illuminate\Console\Command;

class RunBacktestC50IsValidationAntiOverfitCheckCommand extends Command
{
    protected $signature = 'watchlist:backtest-c50-is-validation-anti-overfit-check
        {--c49-artifact=storage/app/watchlist/backtest/c49-broader-strategy-redesign.json}
        {--expected-c49-hash=9266ec2b59a6ea11c21b830cd9b769635afc91a8}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--source-evidence-artifact=}
        {--output=storage/app/watchlist/backtest/c50-is-validation-anti-overfit-check.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Validate locked C49 IS redesign candidates and run anti-overfit checks without OOS tuning, OOS proof, or production promotion.';

    private WatchlistBacktestC50IsValidationAntiOverfitCheckService $service;

    public function __construct(WatchlistBacktestC50IsValidationAntiOverfitCheckService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC50IsValidationAntiOverfitCheckService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C50 IS validation anti-overfit check started');
        }

        $options = ['overwrite' => (bool) $this->option('overwrite')];
        if ($this->option('source-evidence-artifact')) {
            $options['source_evidence_artifact'] = (string) $this->option('source-evidence-artifact');
        }

        $result = $this->service->execute(
            (string) $this->option('c49-artifact'),
            (string) $this->option('expected-c49-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            $options
        );

        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c49_hash', 'actual_c49_hash', 'c49_hash_match', 'c49_status', 'c49_diagnostic_conclusion', 'c49_next_step_recommendation', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }
        foreach ((array) ($result['source_reconstruction_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('source_'.$key.'='.$this->scalar($value));
            }
        }
        foreach ((array) ($result['selected_c50_candidates_for_c51'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('selected_'.$key.'='.$this->scalar($value));
            }
        }
        foreach ((array) ($result['c51_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('c51_'.$key.'='.$this->scalar($value));
            }
        }

        if (($result['status'] ?? null) === 'C50_IS_VALIDATION_COMPLETED' || ($result['status'] ?? null) === 'C50_SOURCE_ROWS_NOT_EVALUABLE') {
            if ((bool) $this->option('progress')) {
                $this->line('C50 IS validation anti-overfit check completed');
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
