<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC43PreTradeFieldExpansionDiagnosticCommand extends Command
{
    protected $signature = 'watchlist:backtest-c43-pre-trade-field-expansion-diagnostic
        {--c42-artifact=storage/app/watchlist/backtest/c42-is-rolling-normal-month-evidence-expansion.json}
        {--expected-c42-hash=939e85f179b3bf5d2511730fafb4271cf7c2ca11}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c43-pre-trade-field-expansion-diagnostic.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run the C43 IS-only pre-trade field expansion diagnostic; never runs OOS proof.';

    private WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService $service;

    public function __construct(WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC43PreTradeFieldExpansionDiagnosticService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C43 IS pre-trade field expansion diagnostic started');
        }
        $result = $this->service->execute(
            (string) $this->option('c42-artifact'),
            (string) $this->option('expected-c42-hash'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach ([
            'status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready',
            'expected_c42_hash', 'actual_c42_hash', 'c42_hash_match', 'c42_status',
            'c42_diagnostic_conclusion', 'diagnostic_conclusion', 'next_step_recommendation',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }
        foreach (($result['c43_decision_summary'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line($key.'='.$this->scalar($value));
            }
        }
        if (($result['status'] ?? null) === 'C43_PRE_TRADE_FIELD_EXPANSION_DIAGNOSTIC_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C43 IS pre-trade field expansion diagnostic completed');
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
