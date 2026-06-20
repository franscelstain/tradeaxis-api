<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC32DataPathAndBadMonthDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC32DataPathAndBadMonthDiagnosticCommand extends Command
{
    protected $signature = 'watchlist:backtest-c32-data-path-and-bad-month-diagnostic
        {--c31-artifact= : Locked C31 controlled gate reclassification artifact path.}
        {--expected-c31-hash= : Expected locked C31 artifact stable hash.}
        {--output= : Output C32 data-path and bad-month diagnostic artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C32 diagnostic split for data-path remediation scope and bad-month robustness without tuning or production promotion.';

    private WatchlistBacktestC32DataPathAndBadMonthDiagnosticService $service;

    public function __construct(WatchlistBacktestC32DataPathAndBadMonthDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC32DataPathAndBadMonthDiagnosticService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C32 data-path and bad-month diagnostic started');
        }

        $result = $this->service->execute(
            (string) ($this->option('c31-artifact') ?: WatchlistBacktestC32DataPathAndBadMonthDiagnosticService::DEFAULT_C31_ARTIFACT),
            (string) ($this->option('expected-c31-hash') ?: WatchlistBacktestC32DataPathAndBadMonthDiagnosticService::DEFAULT_EXPECTED_C31_HASH),
            (string) ($this->option('output') ?: WatchlistBacktestC32DataPathAndBadMonthDiagnosticService::DEFAULT_OUTPUT_PATH),
            [
                'overwrite' => (bool) $this->option('overwrite'),
            ]
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c31_hash',
            'actual_c31_hash',
            'c31_hash_match',
            'c31_status',
            'c31_reclassification_conclusion',
            'c31_controlled_proof_status',
            'data_path_remediation_status',
            'bad_month_robustness_status',
            'diagnostic_conclusion',
            'next_step',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (isset($result['split_decision']) && is_array($result['split_decision'])) {
            foreach ($result['split_decision'] as $key => $value) {
                $this->line('split_'.$key.'='.$this->scalar($value));
            }
        }

        if (($result['status'] ?? null) === 'C32_DATA_PATH_AND_BAD_MONTH_DIAGNOSTIC_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C32 data-path and bad-month diagnostic completed');
            }
            return 0;
        }

        if (isset($result['message'])) {
            $this->error((string) $result['message']);
        }
        return 1;
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
