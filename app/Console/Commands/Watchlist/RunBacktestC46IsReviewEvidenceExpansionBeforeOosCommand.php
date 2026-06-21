<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService;
use Illuminate\Console\Command;

class RunBacktestC46IsReviewEvidenceExpansionBeforeOosCommand extends Command
{
    protected $signature = 'watchlist:backtest-c46-is-review-evidence-expansion-before-oos
        {--c45-artifact=storage/app/watchlist/backtest/c45-is-validation-and-anti-overfit-check-for-c44-refinement.json}
        {--expected-c45-hash=47970ba6e772bcf7fec68f306883f9f3d6cdd976}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c46-is-review-or-evidence-expansion-before-oos.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Review whether the locked C45 IS warnings are bounded or require evidence expansion; never executes OOS or promotion.';

    private WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService $service;

    public function __construct(WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC46IsReviewEvidenceExpansionBeforeOosService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C46 IS warning review started');
        }
        $result = $this->service->execute(
            (string) $this->option('c45-artifact'),
            (string) $this->option('expected-c45-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c45_hash', 'actual_c45_hash', 'c45_hash_match', 'c45_status', 'c45_diagnostic_conclusion', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }
        foreach (($result['warning_layer_inventory'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line($key.'='.$this->scalar($value));
            }
        }
        foreach (($result['review_decision_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line($key.'='.$this->scalar($value));
            }
        }
        if (($result['status'] ?? null) === 'C46_IS_REVIEW_OR_EVIDENCE_EXPANSION_BEFORE_OOS_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C46 IS warning review completed');
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
