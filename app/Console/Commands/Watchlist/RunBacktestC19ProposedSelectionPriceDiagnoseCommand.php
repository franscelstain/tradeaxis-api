<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC19ProposedSelectionPriceDiagnosticService;
use App\Application\Watchlist\Services\WatchlistBacktestIsCalibrationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC19ProposedSelectionPriceDiagnoseCommand extends Command
{
    protected $signature = 'watchlist:backtest-c19-proposed-selection-price-diagnose
        {--catalog-code= : Source immutable catalog code. Defaults to C17.}
        {--from= : IS window start date. Must be 2023-01-02.}
        {--to= : IS window end date. Must be 2025-05-21.}
        {--param-ids= : Optional comma-separated param ids.}
        {--output= : Output artifact path.}
        {--selection-output= : Optional intermediate C19 selection artifact path.}
        {--quality-profile= : Optional Tahap 5 quality profile code for single-profile price diagnostic.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C19 IS-only proposed-selection price diagnostic without creating catalog, OOS, or production artifacts.';

    private WatchlistBacktestC19ProposedSelectionPriceDiagnosticService $service;

    public function __construct(WatchlistBacktestC19ProposedSelectionPriceDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC19ProposedSelectionPriceDiagnosticService();
    }

    public function handle(): int
    {
        $catalogCode = (string) ($this->option('catalog-code') ?: WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::DEFAULT_SOURCE_CATALOG_CODE);
        $from = (string) ($this->option('from') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MIN_IS_DATE);
        $to = (string) ($this->option('to') ?: WatchlistBacktestIsCalibrationExecutionService::R2_MAX_IS_DATE);
        $output = (string) ($this->option('output') ?: WatchlistBacktestC19ProposedSelectionPriceDiagnosticService::DEFAULT_OUTPUT_PATH);
        $paramIds = (string) ($this->option('param-ids') ?: '');
        $selectionOutput = (string) ($this->option('selection-output') ?: '');
        $qualityProfile = (string) ($this->option('quality-profile') ?: '');

        $options = [
            'param_ids' => $paramIds,
            'overwrite' => (bool) $this->option('overwrite'),
        ];
        if ($selectionOutput !== '') {
            $options['selection_output_path'] = $selectionOutput;
        }
        if ($qualityProfile !== '') {
            $options['quality_profile'] = $qualityProfile;
        }

        $result = $this->service->execute($catalogCode, $from, $to, $output, $options);

        foreach ([
            'status',
            'reason_code',
            'scope',
            'artifact_path',
            'artifact_hash',
            'quality_profile',
            'diagnostic_param_count',
            'max_proposed_recommended_count',
            'max_requested_pairs_count',
            'max_evaluated_picks_count',
            'max_price_missing_count',
            'params_with_evaluated_sample_target_reached',
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
