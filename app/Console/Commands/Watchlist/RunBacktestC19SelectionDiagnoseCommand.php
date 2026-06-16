<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC19SelectionModelRedesignAnalysisService;
use Illuminate\Console\Command;

class RunBacktestC19SelectionDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c19-selection-diagnose
        {--catalog-code= : Source immutable catalog code, defaults to C17}
        {--from= : Frozen IS start date, must be 2023-01-02}
        {--to= : Frozen IS end date, must be 2025-05-21}
        {--output= : Output JSON artifact path}
        {--param-ids= : Optional comma-separated source param ids}
        {--progress-every= : Reserved for operator parity; no progress side effect}
        {--overwrite : Overwrite existing artifact path}';

    protected $description = 'Run C19 IS-only selection model redesign diagnostic/prototype without OOS, catalog seed, promotion, or production readiness.';

    private WatchlistBacktestC19SelectionModelRedesignAnalysisService $service;

    public function __construct(WatchlistBacktestC19SelectionModelRedesignAnalysisService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC19SelectionModelRedesignAnalysisService();
    }

    public function handle(): int
    {
        $catalogCode = (string) ($this->option('catalog-code') ?: WatchlistBacktestC19SelectionModelRedesignAnalysisService::DEFAULT_SOURCE_CATALOG_CODE);
        $from = (string) ($this->option('from') ?: '');
        $to = (string) ($this->option('to') ?: '');
        $output = (string) ($this->option('output') ?: WatchlistBacktestC19SelectionModelRedesignAnalysisService::DEFAULT_OUTPUT_PATH);
        $paramIds = (string) ($this->option('param-ids') ?: '');

        $result = $this->service->execute($catalogCode, $from, $to, $output, [
            'param_ids' => $paramIds,
            'overwrite' => (bool) $this->option('overwrite'),
        ]);

        foreach ([
            'status',
            'reason_code',
            'scope',
            'artifact_path',
            'artifact_hash',
            'diagnostic_param_count',
            'max_current_top_count',
            'max_current_secondary_count',
            'max_current_recommended_count',
            'max_proposed_top_count',
            'max_proposed_secondary_count',
            'max_proposed_recommended_count',
            'params_with_proposed_secondary_recovery',
            'params_with_non_unknown_drop_reasons',
            'c19_catalog_implementation_deferred',
            'oos_service_invoked',
            'oos_repository_invoked',
            'oos_executed',
            'production_ready',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (($result['status'] ?? null) !== 'PASS') {
            if (isset($result['message'])) {
                $this->error((string) $result['message']);
            }
            return 1;
        }

        return 0;
    }

    private function scalar($value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value === null) {
            return '';
        }
        return (string) $value;
    }
}
