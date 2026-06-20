<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC34BadMonthRobustnessDiagnosticService;
use Illuminate\Console\Command;

class RunBacktestC34BadMonthRobustnessDiagnosticCommand extends Command
{
    protected $signature = 'watchlist:backtest-c34-bad-month-robustness-diagnostic
        {--c33-artifact= : Locked C33 data-path replay proof artifact path.}
        {--expected-c33-hash= : Expected locked C33 artifact stable hash.}
        {--c32-artifact= : Optional locked C32 bad-month source artifact path override.}
        {--expected-c32-hash= : Optional expected locked C32 artifact stable hash override.}
        {--output= : Output C34 bad-month robustness diagnostic artifact path.}
        {--progress : Print coarse progress markers.}
        {--overwrite : Replace existing output artifact.}';

    protected $description = 'Run C34 bad-month robustness diagnostic after C33 data-path replay proof without OOS tuning or production promotion.';

    private WatchlistBacktestC34BadMonthRobustnessDiagnosticService $service;

    public function __construct(WatchlistBacktestC34BadMonthRobustnessDiagnosticService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC34BadMonthRobustnessDiagnosticService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C34 bad-month robustness diagnostic started');
        }

        $options = [
            'overwrite' => (bool) $this->option('overwrite'),
        ];
        if ($this->option('c32-artifact')) {
            $options['c32_artifact'] = (string) $this->option('c32-artifact');
        }
        if ($this->option('expected-c32-hash')) {
            $options['expected_c32_hash'] = (string) $this->option('expected-c32-hash');
        }

        $result = $this->service->execute(
            (string) ($this->option('c33-artifact') ?: WatchlistBacktestC34BadMonthRobustnessDiagnosticService::DEFAULT_C33_ARTIFACT),
            (string) ($this->option('expected-c33-hash') ?: WatchlistBacktestC34BadMonthRobustnessDiagnosticService::DEFAULT_EXPECTED_C33_HASH),
            (string) ($this->option('output') ?: WatchlistBacktestC34BadMonthRobustnessDiagnosticService::DEFAULT_OUTPUT_PATH),
            $options
        );

        foreach ([
            'status',
            'reason_code',
            'artifact_path',
            'artifact_hash',
            'production_ready',
            'expected_c33_hash',
            'actual_c33_hash',
            'c33_hash_match',
            'c33_status',
            'c33_data_path_replay_status',
            'expected_c32_hash',
            'actual_c32_hash',
            'c32_hash_match',
            'c32_status',
            'bad_month_robustness_status',
            'bad_month_failure_count',
            'branch_robustness_flag_count',
            'strategy_robustness_redesign_required',
            'diagnostic_conclusion',
            'next_step',
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (($result['status'] ?? null) === 'C34_BAD_MONTH_ROBUSTNESS_DIAGNOSTIC_COMPLETED') {
            if ((bool) $this->option('progress')) {
                $this->line('C34 bad-month robustness diagnostic completed');
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
