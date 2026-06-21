<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService;
use Illuminate\Console\Command;

class RunBacktestC45IsValidationAntiOverfitCheckForC44RefinementCommand extends Command
{
    protected $signature = 'watchlist:backtest-c45-is-validation-anti-overfit-check-for-c44-refinement
        {--c44-artifact=storage/app/watchlist/backtest/c44-is-guard-refinement-candidate-formation.json}
        {--expected-c44-hash=606cd3109371b0d99419082daee18ff65f1cd99b}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--is-evidence-artifact=}
        {--output=storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Validate the locked C44 refinement across independent IS anti-overfit layers; never runs OOS or production promotion.';

    private WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService $service;

    public function __construct(WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC45IsValidationAntiOverfitCheckForC44RefinementService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C45 IS validation and anti-overfit check started');
        }
        $options = ['overwrite' => (bool) $this->option('overwrite')];
        if ($this->option('is-evidence-artifact')) {
            $options['source_evidence_artifact'] = (string) $this->option('is-evidence-artifact');
        }
        $result = $this->service->execute(
            (string) $this->option('c44-artifact'),
            (string) $this->option('expected-c44-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            $options
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c44_hash', 'actual_c44_hash', 'c44_hash_match', 'c44_status', 'c44_diagnostic_conclusion', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }
        foreach (($result['validation_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line($key.'='.$this->scalar($value));
            }
        }
        foreach (($result['anti_overfit_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line($key.'='.$this->scalar($value));
            }
        }
        if (($result['status'] ?? null) === 'C45_IS_VALIDATION_AND_ANTI_OVERFIT_CHECK_FOR_C44_REFINEMENT_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C45 IS validation and anti-overfit check completed');
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
