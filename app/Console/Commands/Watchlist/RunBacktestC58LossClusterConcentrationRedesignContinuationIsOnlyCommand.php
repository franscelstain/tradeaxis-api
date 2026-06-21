<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService;
use Illuminate\Console\Command;

class RunBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyCommand extends Command
{
    protected $signature = 'watchlist:backtest-c58-loss-cluster-concentration-redesign-continuation-is-only
        {--c57-artifact=storage/app/watchlist/backtest/c57-regime-field-reconstruction-continuation-is-only.json}
        {--expected-c57-hash=71230896c2121fcfedddf36dd54c9c03ad462b4d}
        {--expected-c57-file-sha1=50272917A107E304F8EEEB874DBC02A881DB0C31}
        {--from=2023-01-02}
        {--to=2025-05-21}
        {--output=storage/app/watchlist/backtest/c58-loss-cluster-concentration-redesign-continuation-is-only.json}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C58 IS-only loss-cluster/concentration redesign continuation from locked C57 evidence without OOS access.';

    private WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService $service;

    public function __construct(?WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC58LossClusterConcentrationRedesignContinuationIsOnlyService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) { $this->line('C58 IS-only loss-cluster/concentration redesign continuation started'); }
        $result = $this->service->execute(
            (string) $this->option('c57-artifact'),
            (string) $this->option('expected-c57-hash'),
            (string) $this->option('expected-c57-file-sha1'),
            (string) $this->option('from'),
            (string) $this->option('to'),
            (string) $this->option('output'),
            ['overwrite' => (bool) $this->option('overwrite')]
        );
        foreach (['status', 'reason_code', 'artifact_path', 'artifact_hash', 'production_ready', 'expected_c57_hash', 'actual_c57_hash', 'c57_hash_match', 'expected_c57_file_sha1', 'actual_c57_file_sha1', 'c57_file_sha1_match', 'c57_status', 'c57_diagnostic_conclusion', 'c57_next_step_recommendation', 'diagnostic_conclusion', 'next_step_recommendation'] as $key) {
            if (array_key_exists($key, $result)) { $this->line($key.'='.$this->scalar($result[$key])); }
        }
        foreach ((array) ($result['c59_readiness_decision'] ?? []) as $key => $value) { if (! is_array($value)) { $this->line('c59_'.$key.'='.$this->scalar($value)); } }
        if (($result['status'] ?? null) === 'C58_LOSS_CLUSTER_CONCENTRATION_REDESIGN_CONTINUATION_IS_ONLY_COMPLETED') {
            if ((bool) $this->option('progress')) { $this->line('C58 IS-only loss-cluster/concentration redesign continuation completed'); }
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
